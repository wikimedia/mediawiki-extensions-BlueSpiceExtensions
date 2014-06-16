<?php
/**
 * Adapter that for Solr Server Service for ExtendedSearch
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Core
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v0.1
 * initial commit
 */
// todo: Apache_Solr_Service uses split in Response.php (DEPRECATED)
/**
 * Adapter that for Solr Server Service for ExtendedSearch
 * @package BlueSpice_Core
 * @subpackage ExtendedSearch
 */
abstract class SolrServiceAdapter extends Apache_Solr_Service {

	/**
	 * URL servlet part for morelikethis
	 */
	const MORELIKETHIS_SERVLET = '/mlt';
	/**
	 * URL servlet part for spellchecker
	 */
	const SPELLCHECK_SERVLET = '/spell';

	/**
	 * URL protocol part of solr service.
	 * @var string
	 */
	protected $sProtocol;
	/**
	 * Base URL of solr service.
	 * @var string
	 */
	protected $sUrl;
	/**
	 * Is another protocol used?
	 * @var bool True if not HTTP
	 */
	protected $bUseDifferentProtocolThanHttp;
	/**
	 * Reference to the current corl handler.
	 * @var resource
	 */
	protected $curlHandle = null;
	/**
	 *  Counts the number of open curl connections.
	 * @var int
	 */
	protected $curlConnectionCounter = 0;

	/**
	 * Constructor of BsSolrServiceAdapter class
	 * @param string $sProtocol Protocol of Solr service URL
	 * @param string $sHost Host of Solr service URL
	 * @param string $sPort Port of Solr service URL
	 * @param string $sPath Path of Solr service URL
	 */
	public function __construct( $sProtocol, $sHost, $sPort, $sPath ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->sProtocol = $sProtocol;
		$this->bUseDifferentProtocolThanHttp = (bool)( strtolower( $this->sProtocol ) != 'http' );
		$this->sUrl = "$sProtocol://$sHost:{$sPort}{$sPath}/";
		parent::__construct( $sHost, $sPort, $sPath );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initiate URLs so they use the correct protocol.
	 */
	protected function _initUrls() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->_morelikethisUrl = $this->_constructUrl( BsConfig::get( 'MW::ExtendedSearch::SolrCore' ) . self::MORELIKETHIS_SERVLET );
		$this->_spellcheckUrl = $this->_constructUrl( BsConfig::get( 'MW::ExtendedSearch::SolrCore' ) . self::SPELLCHECK_SERVLET );
		parent::_initUrls();

		if ( $this->bUseDifferentProtocolThanHttp ) {
			$this->_updateUrl = str_ireplace( 'http://', $this->sProtocol.'://', $this->_updateUrl );
			$this->_searchUrl = str_ireplace( 'http://', $this->sProtocol.'://', $this->_searchUrl );
			$this->_threadsUrl = str_ireplace( 'http://', $this->sProtocol.'://', $this->_threadsUrl );
			$this->_spellcheckUrl = str_ireplace( 'http://', $this->sProtocol.'://', $this->_spellcheckUrl );
			$this->_morelikethisUrl = str_ireplace( 'http://', $this->sProtocol.'://', $this->_morelikethisUrl );
		}
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Test if Solr server is reachable within a given period.
	 * @param int $iTimeout Seconds to wait for reply from server.
	 * @return int Time it took to answer in microseconds or false if no answer.
	 */
	public function ping( $iTimeout = 2 ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( $this->bUseDifferentProtocolThanHttp ) {
			$ctx = stream_context_create( array( 'https' => array( 'timeout' => $iTimeout ) ) );
			$start = microtime( true );
			$res = @file_get_contents( $this->sUrl, 0, $ctx );
			wfProfileOut( 'BS::'.__METHOD__ );
			return ( $res ) ? ( microtime( true ) - $start ) : false;
		}
		wfProfileOut( 'BS::'.__METHOD__ );

		return parent::ping( $iTimeout );
	}

	/**
	 * Central method for making a post operation against this Solr Server
	 *
	 * @param string $sUrl The URL to be requested
	 * @param string $sRawPost Workload to be delivered
	 * @param float $iTimeLimit Read timeout in seconds
	 * @param string $sContentType The content type to be included in the http-header
	 * @return Apache_Solr_Response
	 *
	 * @throws Exception If a non 200 response status is returned by cURL
	 */
	protected function _sendRawPost( $sUrl, $sRawPost, $iTimeLimit = false, $sContentType = 'text/xml; charset=UTF-8' ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		try {
			if ( $iTimeLimit === false ) {
				$iTimeLimit = 20;
			}

			$ch = $this->getCurlHandle();
			curl_setopt( $ch, CURLOPT_URL, $sUrl );
			curl_setopt( $ch, CURLOPT_POST, true );
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $sRawPost );
			curl_setopt( $ch, CURLOPT_TIMEOUT, $iTimeLimit ); // maximal exectuion time in seconds

			$data = curl_exec( $ch );
			$this->curlConnectionCounter++;
			$data = str_replace( "\r\n", "\n", $data );
			$responseParts = explode( "\n\n", $data );
			$http_response_header = explode( "\n", $responseParts[0] );
			$responseData = ( isset( $responseParts[1] ) ) ? $responseParts[1] : '';
			$response = new Apache_Solr_Response( $responseData, $http_response_header, $this->_createDocuments, $this->_collapseSingleValueArrays );
		} catch ( Exception $e ) {
			error_log ( 'Exception raised in Search::_sendRawPost: ' . $e->getMessage() );
			//Search::writeLog($mode, 'Exception raised in Search::_sendRawPost: ' . $e->getMessage(), 0);
			wfProfileOut( 'BS::'.__METHOD__ );
			return new Apache_Solr_Response('');
		}

		if ( $response->getHttpStatus() != 200 ) {
			BuildIndexMainControl::getInstance()->logFile( 'write', 'Error in _sendRawPost ' . var_export( $response, true ) );
			wfProfileOut( 'BS::'.__METHOD__ );
			throw new Exception( '"' . $response->getHttpStatus() . '" Status: ' . $response->getHttpStatusMessage(), $response->getHttpStatus() );
		}
		wfProfileOut( 'BS::'.__METHOD__ );

		return $response;
	}

	/**
	 * Overwriting commit() in Service.php to get rid of optimize parameter in the rawPost string
	 * (optimize doesn't look valid in this version of solr)
	 * @param bool $bOptimize Not used currently.
	 * @param bool $bWaitFlush Send commit with waitFlush attribute
	 * @param bool $bWaitSearcher Send commit with waitSearcher attribute
	 * @param int $iTimeout Seconds to wait for server response.
	 * @return Apache_Solr_Response
	 */
	public function commit( $bOptimize = true, $bWaitFlush = true, $bWaitSearcher = true, $iTimeout = 3600 ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		// http://wiki.apache.org/solr/UpdateXmlMessages

		$searcherValue = $bWaitSearcher ? 'true' : 'false';

		$rawPost = '<commit waitSearcher="' . $searcherValue . '" />';
		wfProfileOut( 'BS::'.__METHOD__ );

		return $this->_sendRawPost( $this->_updateUrl, $rawPost, $iTimeout );
	}

}