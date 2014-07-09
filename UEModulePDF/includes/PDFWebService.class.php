<?php
/**
 * BsPDFWebService.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage UEModulePDF
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * UniversalExport BsPDFWebService class.
 * @package BlueSpice_Extensions
 * @subpackage UEModulePDF
 * @deprecated since version 2.22. Use BsPDFSevlet instead
 */
class BsPDFWebService {
	/**
	 * The SoapClient object to query the PDF webservice
	 * @var SoapClient 
	 */
	protected $oPdfWebservice;

	/**
	 * Gets a DOMDocument, searches it for files, uploads files and markus to webservice and generated PDF.
	 * @param DOMDocument $oHtmlDOM The source markup
	 * @return Byte[] The resulting PDF
	 */
	public function createPDF( &$oHtmlDOM ) {

		$aOptions = array(
			'verify_peer'          => false,
			'allow_self_signed'    => true,
			'cache_wsdl'           => WSDL_CACHE_NONE
			//'cafile'               => '',
		);

		wfRunHooks( 'BSUEModulePDFCreatePDFBeforeSend', array( $this, &$aOptions, $oHtmlDOM ) );

		$this->oPdfWebservice = new SoapClient(
			$this->aParams['soap-service-url'].'/GeneratePdf?wsdl',
			$aOptions
		);

		$this->findFiles( $oHtmlDOM );
		$this->uploadFiles();

		//HINT: http://www.php.net/manual/en/class.domdocument.php#96055
		$sHtmlDOM = $oHtmlDOM->saveXML( $oHtmlDOM->documentElement );
		
		//Formated Output is evil because is will destroy formatting in <pre> Tags!
		//$oIntermediateDOM = new DOMDocument();
		//$oIntermediateDOM->preserveWhiteSpace = false;
		//$oIntermediateDOM->formatOutput = true;
		//$oIntermediateDOM->loadXML($sHtmlDOM);
		//$sHtmlDOM = $oIntermediateDOM->saveXML( $oIntermediateDOM->documentElement, LIBXML_NOEMPTYTAG );
		
		$aSoapParams = array(
			'documentHTML'  => &$sHtmlDOM,
			'documentToken' => $this->aParams['document-token']
		);

		$oResponse     = $this->oPdfWebservice->createNewPDF( $aSoapParams );
		$vPdfByteArray = $oResponse->return;

		return $vPdfByteArray;
	}

	/**
	 * Uploads all files found in the markup by the "findFiles" method.
	 */
	protected function uploadFiles() {
		$sFilePath = '';
		try {
			foreach( $this->aFiles as $sType => $aFiles ) {
				foreach( $aFiles as $sFileName => $sFilePath ) {
					//Upload File
					//$sFilePath   = urldecode( $sFilePath );
					$sFileB64    = base64_encode( @file_get_contents( $sFilePath ) );
					$aSoapParams = array(
									//'fileName'      => urldecode( $sFileName ),
									'fileName'      => $sFileName,
									'fileB64'		=> $sFileB64,
									'fileType'		=> $sType,
									'documentToken' => $this->aParams['document-token']
						);

					$oResponse = $this->oPdfWebservice->uploadFile( $aSoapParams );
					wfDebugLog( 
						'BS::UEModulePDF',
						'BsPDFWebService::uploadFiles: File "'.$sType.' ('.$sFilePath.', '.strlen($sFileB64).' Bytes) uploaded: '.var_export( $oResponse->return, true )
					);

					unset( $sFileB64 );
				}
			}
		} catch( Exception $e ){
			wfDebugLog(
				'BS::UEModulePDF',
				'BsPDFWebService::uploadFiles: Upload failure ('.$sFilePath.'): '.$e->getMessage()
			);
		}
	}
	
	/**
	 *
	 * @var array 
	 */
	protected $aParams = array();
	
	/**
	 *
	 * @var array 
	 */
	protected $aFiles  = array();

	/**
	 * The contructor method forthis class.
	 * @param array $aParams The params have to contain the key 
	 * 'soap-service-url', with a valid URL to the webservice. They can 
	 * contain a key 'soap-connection-options' for the SoapClient constructor 
	 * and a key 'resources' with al list of files to upload.
	 * @throws UnexpectedValueException If 'soap-service-url' is not set or the Webservice is not available.
	 */
	public function __construct( &$aParams ) {

		$this->aParams = $aParams;
		$this->aFiles =  $aParams['resources'];

		if ( empty( $this->aParams['soap-service-url'] ) ) {
			throw new UnexpectedValueException( 'soap-service-url-not-set' );
		}

		if( !BsConnectionHelper::urlExists( $this->aParams['soap-service-url'] ) ) {
			throw new UnexpectedValueException( 'soap-service-url-not-valid' );
		}
		
		//If a slash is last char, remove it.
		if( substr($this->aParams['soap-service-url'], -1) == '/' ) {
			$this->aParams['soap-service-url'] = substr($this->aParams['soap-service-url'], 0, -1);
		}
	}

	/**
	 * Searches the DOM for <img>-Tags and <a> Tags with class 'internal', 
	 * resolves the local filesystem path and adds it to $aFiles array.
	 * @param DOMDocument $oHtml The markup to be searched.
	 * @return boolean Well, always true.
	 */
	protected function findFiles( &$oHtml ) {
		//Find all images
		$oImageElements = $oHtml->getElementsByTagName( 'img' );
		foreach( $oImageElements as $oImageElement ) {
			$sSrcUrl      = urldecode($oImageElement->getAttribute( 'src' ) );
			$sSrcFilename = basename( $sSrcUrl );

			$bIsThumb = UploadBase::isThumbName($sSrcFilename);
			$sTmpFilename = $sSrcFilename;
			if( $bIsThumb ) {
				//HINT: Thumbname-to-filename-conversion taken from includes/Upload/UploadBase.php
				//Check for filenames like 50px- or 180px-, these are mostly thumbnails
				$sTmpFilename = substr( $sTmpFilename , strpos( $sTmpFilename , '-' ) +1 );
			}
			$oFileTitle = Title::newFromText( $sTmpFilename, NS_FILE );
			$oImage = RepoGroup::singleton()->findFile( $oFileTitle );

			if( $oImage instanceof File && $oImage->exists() ) {
				$oFileRepoLocalRef = $oImage->getRepo()->getLocalReference( $oImage->getPath() );
				if ( !is_null( $oFileRepoLocalRef ) ) {
					$sAbsoluteFileSystemPath = $oFileRepoLocalRef->getPath();
				}
				$sSrcFilename = $oImage->getName();
			}
			else {
				$sAbsoluteFileSystemPath = $this->getFileSystemPath( $sSrcUrl );
			}
			// TODO RBV (05.04.12 11:48): Check if urlencode has side effects
			$oImageElement->setAttribute( 'src', 'images/'.urlencode($sSrcFilename) );
			$sFileName = $sSrcFilename;
			wfRunHooks( 'BSUEModulePDFWebserviceFindFiles', array( $this, $oImageElement, $sAbsoluteFileSystemPath, $sFileName, 'IMAGE' ) );
			$this->aFiles['IMAGE'][$sFileName] =  $sAbsoluteFileSystemPath;
		}
		
		$oDOMXPath = new DOMXPath( $oHtml );
		
		/*
		 * This is now in template
		//Find all CSS files
		$oLinkElements = $oHtml->getElementsByTagName( 'link' ); // TODO RBV (02.02.11 16:48): Limit to rel="stylesheet" and type="text/css"
		foreach( $oLinkElements as $oLinkElement ) {
			$sHrefUrl = $oLinkElement->getAttribute( 'href' );
			$sHrefFilename           = basename( $sHrefUrl );
			$sAbsoluteFileSystemPath = $this->getFileSystemPath( $sHrefUrl );
			$this->aFiles[ $sAbsoluteFileSystemPath ] = array( $sHrefFilename, 'STYLESHEET' );
			$oLinkElement->setAttribute( 'href', 'stylesheets/'.$sHrefFilename );
		}
		 */

		//Find all files for attaching and merging...
		if ( $this->aParams['pdf-merging'] == '1'
			|| $this->aParams['attachments'] == '1' ) {

			$sUploadPath = BsCore::getInstance()->getAdapter()->get( 'UploadPath' );
			
			// TODO RBV (08.02.11 15:15): Necessary to exclude images?
			$oFileAnchorElements = $oDOMXPath->query( "//a[contains(@class,'internal') and not(contains(@class, 'image'))]" );
			foreach( $oFileAnchorElements as $oFileAnchorElement ) {
				$sHref = urldecode( $oFileAnchorElement->getAttribute( 'href' ) );
				$vUploadPathIndex = strpos( $sHref, $sUploadPath );
				if( $vUploadPathIndex !== false ) {
					$sRelativeHref           = substr( $sHref, $vUploadPathIndex );
					$sHrefFilename           = basename( $sRelativeHref );
					$sAbsoluteFileSystemPath = $this->getFileSystemPath( $sRelativeHref );
					if( $this->aParams['attachments'] == '1' ) {
						wfRunHooks( 'BSUEModulePDFWebserviceFindFiles', array( $this, $oFileAnchorElement, $sAbsoluteFileSystemPath, $sHrefFilename, 'ATTACHMENT' ) );
						$this->aFiles['ATTACHMENT'][$sHrefFilename] = $sAbsoluteFileSystemPath;
					}
				}
			}
		}

		return true;
	}

	//<editor-fold desc="Helper Methods" defaultstate="collapsed">
	/**
	 * This helper method resolves the local file system path of a found file
	 * @param string $sUrl
	 * @return string The local file system path
	 */
	protected function getFileSystemPath( $sUrl ) {
		if( $sUrl{0} !== '/' || strpos( $sUrl, $this->aParams['webroot-filesystempath'] ) === 0 ) {
			return $sUrl; //not relative to webroot or absolute filesystempath
		}
		
		$sScriptUrlDir = dirname( $_SERVER['SCRIPT_NAME'] );
		$sScriptFSDir  = dirname( $_SERVER['SCRIPT_FILENAME'] );
		if( strpos( $sScriptFSDir, $sScriptUrlDir) == 0 ){ //detect virtual path (webserver setting)
			$sUrl = '/'.substr( $sUrl, strlen( $sScriptUrlDir ) );
		}
		
		$sNewUrl = $this->aParams['webroot-filesystempath'].$sUrl; // TODO RBV (08.02.11 15:56): What about $wgUploadDirectory?
		return $sNewUrl;
	}
	//</editor-fold>
}