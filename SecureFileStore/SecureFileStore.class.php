<?php
/**
 * SecureFileStore extension for BlueSpice
 *
 * Prevent unauthorized access to files and images.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * 
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Marc Reymann
 * @version    2.22.0 stable

 * @package    BlueSpice_Extensions
 * @subpackage SecureFileStore
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
  * v1.0.0
  * - Code Review
  * v0.1
  * - initial release
  */

//Last Code Review RBV (30.06.2011)

/**
 * Base class for SecurefileStore extension
 * @package BlueSpice_Extensions
 * @subpackage SecurefileStore
 */
class SecureFileStore extends BsExtensionMW {

	/**
	 * Path to file dispatcher that replaces the standard image path.
	 */
	const PATHTOFILEDISPATCHER = 'index.php?action=ajax&amp;title=-&amp;rs=SecureFileStore::getFile';

	/**
	 * Constructor of SecureFileStore class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'SecureFileStore',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-securefilestore-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser, Marc Reymann',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
							'bluespice'   => '2.22.0'
						)
		);
		$this->mExtensionKey = 'MW::SecureFileStore';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of ExtendedEditBar extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		BsExtensionManager::setContext( 'MW::SecureFileStore::Active' );
		
		BsConfig::registerVar( 'MW::SecureFileStore::Active', true, BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT );
		BsConfig::registerVar( 'MW::SecureFileStore::DefaultDisposition', 'inline', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-securefilestore-pref-defaultdisposition', 'select' );
		BsConfig::registerVar( 'MW::SecureFileStore::DispositionInline', array( 'pdf' ), BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_ARRAY_STRING, 'bs-securefilestore-pref-dispositioninline', 'multiselectplusadd' );
		BsConfig::registerVar( 'MW::SecureFileStore::DispositionAttachment', array( 'doc', 'docx', 'xls', 'xlsx', 'ppt', 'pptx' ), BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_ARRAY_STRING, 'bs-securefilestore-pref-dispositionattachment', 'multiselectplusadd' );
		BsConfig::registerVar( 'MW::SecureFileStore::FileExtensionWhitelist', array(), BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_ARRAY_STRING|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-securefilestore-pref-fileextensionwhitelist', 'multiselectplusadd' );

		$this->setHook( 'SkinTemplateOutputPageBeforeExec', 'secureImages' );
		$this->setHook( 'ExtendedSearchBeforeAjaxResponse', 'secureImages' );
		$this->setHook( 'SiteNoticeAfter', 'onSiteNoticeAfter' );

		$this->mCore->registerPermission( 'viewfiles', array( 'user' ) );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function onSiteNoticeAfter( &$siteNotice ) {
		$siteNotice = SecureFileStore::secureFilesInText( $siteNotice );
		return true;
	}

	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array( 'options' => array( 'inline' => 'inline', 'attachment' => 'attachment' ) );
		return $aPrefs;
	}

	/**
	 * Replaces links to files with links to secure file dispatcher.
	 * @param object $oObject needed by hook
	 * @param object $oText reference to skin template object
	 * @return bool hook must return true 
	 */
	public function secureImages( $oObject, &$oText ) {
		if ( !BsConfig::get( 'MW::SecureFileStore::Active' ) ) return true;
		$oText->data['bodytext'] = self::secureStuff( $oText->data['bodytext'] );
		return true;
	}

	/**
	 * Replaces links to files with links to secure file dispatcher.
	 * @param string $sText HTML source text
	 * @return string HTML with replaced links 
	 */
	public static function secureFilesInText( $sText ) {
		if ( !BsConfig::get( 'MW::SecureFileStore::Active' ) ) return $sText;
		return self::secureStuff( $sText );
	}

	/**
	 * Replaces links to files with links to secure file dispatcher.
	 * @param string $sText HTML source text
	 * @param bool $bIsUrl switches replacement mode
	 * @return string HTML with replaced links 
	 */
	public static function secureStuff( $sText, $bIsUrl = false ) {
		global $wgScriptPath, $wgUploadPath;
		$sSecurePath = $wgScriptPath . "/" . self::PATHTOFILEDISPATCHER . '&amp;f=';
		$sUploadPathPattern = preg_quote( $wgUploadPath, '#' );
		if ( $bIsUrl ) {
			// replace relative link beginning with _/images_ in a URL
			$sPattern = '#^' . $sUploadPathPattern . '#';
			$sReplacement = $sSecurePath;
		} else {
			// replace all occurrences of _"images/_ or _'images/_ in HTML
			$sPattern = '#("|\')' . $sUploadPathPattern . '#';
			$sReplacement = '\1' . $sSecurePath;
		}
		$sText = preg_replace( $sPattern, $sReplacement, $sText );
		return $sText;
	}

	/**
	 * Send file via HTTP.
	 */
	public static function getFile() {
		global $wgUploadDirectory;
		$sRawFilePath     = RequestContext::getMain()->getRequest()->getVal( 'f' );
		// Some extensions (e.g. Social Profile) add params with ? to filename
		$aRawFilePathPcs  = preg_split( "/\?.*=/", $sRawFilePath );
		$sRawFilePath     = $aRawFilePathPcs[0];
		$sUploadDirectory = realpath( $wgUploadDirectory );
		if ( empty( $sUploadDirectory ) ) throw new MWException( '$wgUploadDirectory is empty. This should never happen!' );

		// Switch between f=File:Foo.png and f=/3/33/Foo.png style requests
		$aFileNamespaceNames = BsNamespaceHelper::getNamespaceNamesAndAliases( NS_FILE );
		if ( preg_match( '#^(.*?):(.*)$#', $sRawFilePath, $aMatch ) && in_array( $aMatch[1], $aFileNamespaceNames ) ) {
			$oTitle = Title::newFromText( $aMatch[2], NS_FILE );
			$oImg = wfLocalFile( $oTitle );
			if ( !is_null( $oImg ) ) {
				$oImgRepoLocalRef = $oImg->getRepo()->getLocalReference( $oImg->getPath() );
				if ( !is_null( $oImgRepoLocalRef ) ) {
					$sFilePath = realpath( $oImgRepoLocalRef->getPath() );
				}
			}
		}
		else {
			$sFilePath = realpath( $sUploadDirectory . $sRawFilePath );
		}

		$aPathParts = pathinfo( $sFilePath );
		$sFileName = $aPathParts['basename'];
		$sFileExt = isset( $aPathParts['extension'] )?strtolower( $aPathParts['extension'] ):'';

		if ( strpos( $sFilePath, $sUploadDirectory ) !== 0 // prevent directory traversal
			|| preg_match( '/^\.ht/', $sFileName )     // don't serve .ht* files
			|| empty( $sFilePath )                     // $sFilePath not being set or realpath() returning false indicates that file doesn't exist
			|| !is_file( $sFilePath )                  // ignore directories
			|| !is_readable( $sFilePath )
			) {
			header( 'HTTP/1.0 404 Not Found' );
			exit;
		}

		// At this point we have a valid and readable file path in $sFilePath.
		// Now create a File object to get some properties

		if ( strstr( $sFilePath, 'thumb' ) ) $sFindFileName = preg_replace( "#(\d*px-)#", '', $sFileName ); 
		else $sFindFileName = $sFileName;

		$aOptions = array( 'time' => false );
		//TODO: maybe check for "/archive" in $sFilePath, too. But this migth be a config setting, so do not hardcode
		$isArchive = preg_match('#^\d{14}!#si', $sFindFileName); //i.e. "20120724112914!Adobe-reader-x-tco-de.pdf"
		if( $isArchive ) {
			$aFilenameParts   = explode( '!', $sFindFileName, 2);
			$sFindFileName    = $aFilenameParts[1];
			$aOptions['time'] = $aFilenameParts[0];
		}
		$oFile = RepoGroup::singleton()->findFile( $sFindFileName, $aOptions );

		// We need to do some additional checks if file extension is not on whitelist
		if ( !in_array( $sFileExt, BsConfig::get( 'MW::SecureFileStore::FileExtensionWhitelist' ) ) ) {

			// Check for MediaWiki right 'viewfiles'
			global $wgUser;
			if ( !$wgUser->isAllowed( 'viewfiles' ) ) {
				header ( 'HTTP/1.0 403 Forbidden' );
				exit;
			}

			// Check if user has access to file's meta page
			if ( $oFile ) {
				if ( !$oFile->getTitle()->userCan( 'read' ) ) {
					header ( 'HTTP/1.0 403 Forbidden' );
					exit;
				}
			}
		}

		// User is allowed to retrieve file. Get things going.
		# If file is not in MW's repo try to guess MIME type
		$sFileMime = ( $oFile ) ? $oFile->getMimeType() : MimeMagic::singleton()->guessMimeType( $sFilePath, false );

		$sFileDispo = BsConfig::get( 'MW::SecureFileStore::DefaultDisposition' );
		if ( in_array( $sFileExt, BsConfig::get( 'MW::SecureFileStore::DispositionAttachment' ) ) ) $sFileDispo = 'attachment';
		if ( in_array( $sFileExt, BsConfig::get( 'MW::SecureFileStore::DispositionInline' ) ) )     $sFileDispo = 'inline';

		$aFileStat = stat( $sFilePath );
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $aFileStat['mtime'] ) . ' GMT' );
		header( "Content-Type: $sFileMime" );
		header( "Content-Disposition: $sFileDispo; filename=\"$sFileName\"" );
		header( "Cache-Control: no-cache,must-revalidate", true ); //Otherwise IE might deliver old version

		if ( !empty( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) ) {
			$sModSince  = preg_replace( '/;.*$/', '', $_SERVER['HTTP_IF_MODIFIED_SINCE'] );
			$sSinceTime = strtotime( $sModSince );
			if ( $aFileStat['mtime'] <= $sSinceTime ) {
				ini_set('zlib.output_compression', 0);
				header( "HTTP/1.0 304 Not Modified" );
				exit;
			}
		}

		// IE6/IE7 cannot handle download of zip-files that are aditionally gzipped by the Apache
		// just put it in the header and tell apache to immediately flush => and gzip is disabled
		if ( $sFileMime == 'application/zip' ) {
			header( 'Content-Length: ' . $aFileStat['size'] );
			flush();
		}

		// Send the file already ;-)
		readfile( $sFilePath );
		exit;
	}
}
