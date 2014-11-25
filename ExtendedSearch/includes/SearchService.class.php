<?php
/**
 * Abstraction layer for search service for ExtendedSearch
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v0.1
 * FIRST CHANGES
 */
/**
 * BsSolrService is the ONLY api between blue spice and a search server.
 * There may be more than one solr server so it is used as n-gleton
 */
/**
 * Abstraction layer for search service for ExtendedSearch
 * @package BlueSpice_Core
 * @subpackage ExtendedSearch
 */

class SearchService extends SolrServiceAdapter {

	/**
	 * Curl handle for text extraction
	 * @var resource
	 */
	protected $oGetFileTextCurlHandle = null;
	/**
	 * Counts number of connections to text extraction
	 * @var int
	 */
	protected $iGetFileTextConnectionCounter;
	/**
	 * Age of text extraction curl handler
	 * @var int miiliseconds.
	 */
	protected $iGetFileTextCurlAge;
	/**
	 * Instance of search service
	 * @var object of search service.
	 */
	protected static $oInstance = null;

	/**
	 * Constructor for BsSearchService class
	 * @param string $sProtocol Protocol of Solr service URL
	 * @param string $sHost Host of Solr service URL
	 * @param string $sPort Port of Solr service URL
	 * @param string $sPath Path of Solr service URL
	 */
	public function __construct( $sProtocol, $sHost, $sPort, $sPath ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		parent::__construct( $sProtocol, $sHost, $sPort, $sPath );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Return a instance of SearchService.
	 * @return SearchService Instance of SearchService
	 */
	public static function getInstance() {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( self::$oInstance === null ) {
			if ( PHP_SAPI === 'cli' ) {
				$oDbr = wfGetDB( DB_SLAVE );
				if ( $oDbr->tableExists( 'bs_settings' ) ) {
					BsConfig::loadSettings();
				}
			}
			$aUrl = parse_url( BsConfig::get( 'MW::ExtendedSearch::SolrServiceUrl' ) );

			if ( empty( $aUrl['host'] ) || empty( $aUrl['port'] ) || empty( $aUrl['path'] ) ) {
				wfProfileOut( 'BS::'.__METHOD__ );
				throw new Exception( 'Creating instance of ' . __CLASS__ . ' not possible with these params:'
					.', $host='.( isset( $aUrl['host'] ) ? $aUrl['host'] : '' )
					.', $port='.( isset( $aUrl['port'] ) ? $aUrl['port'] : '' )
					.', $path='.( isset( $aUrl['path'] ) ? $aUrl['path'] : '' )
				);
			}

			$oServer = new self( $aUrl['scheme'], $aUrl['host'], $aUrl['port'], $aUrl['path'] );

			self::$oInstance = $oServer;
			wfProfileOut( 'BS::'.__METHOD__ );

			return $oServer;
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return self::$oInstance;
	}

	/**
	 * Check for unescaped characters
	 * @param string $sString String to check
	 * @param string $sChar Character to look for
	 * @param string $sEscapeChar Character that escapes the character in question
	 * @return bool True if there are unescaped instances.
	 */
	protected static function containsStringUnescapedCharsOf( $sString, $sChar, $sEscapeChar = '\\' ) {
		// at $pos the $char occurs in $sString.
		$pos = mb_stripos( $sString, $sChar );
		//  If $sChar not comprised in $sString
		if ( $pos === false ) {
			return false;
		}
		// So, $sChar ist comprised in $sString, but where?
		// ... at first position?
		if ( $pos === 0 ) {
			return true;
		}
		// Ok, comprised but somewhere inside. Escape chars get important.
		// Is the character before $pos an $sEscapeChar?
		if ( $sString[$pos - 1] !== $sEscapeChar ) {
			return true;
		}
		// $pos-1 IS a $sEscapeChar
		// But the $sEscapeChar might have been escaped itself, if preceded with a second $sEscapeChar
		if ( ( $pos > 1 ) && ( $sString[$pos - 2] === $sEscapeChar ) ) {
			return true;
		}

		// Finally! $sChar contained in $sString, with single $sEscapeChar directly
		// preceded that is not escaped by another $sEscapeChar itself.
		// Use recursion to see, if there is a second $sChar after first occurrence
		return self::containsStringUnescapedCharsOf( substr( $sString, $pos + 1 ), $sChar );
	}

	/**
	 * Append wildcard character to search string if possible
	 * @param string $sSearchString Raw search string
	 * @return string (Possibly) wildcarded search string.
	 */
	public static function wildcardSearchstring( $sSearchString ) {
		// remove beginning
		$sSearchString = trim( $sSearchString );
		if ( empty( $sSearchString ) ) {
			return $sSearchString;
		}

		if ( self::containsStringUnescapedCharsOf( $sSearchString, '~' ) ) {
			return $sSearchString;
		}
		if ( self::containsStringUnescapedCharsOf( $sSearchString, '"' ) ) {
			return $sSearchString;
		}
		if ( self::containsStringUnescapedCharsOf( $sSearchString, '^' ) ) {
			return $sSearchString;
		}
		if ( self::containsStringUnescapedCharsOf( $sSearchString, '*' ) ) {
			return $sSearchString;
		}

		if ( strpos( $sSearchString, ' ' ) !== false ) {
			$sSearchString = str_replace( ' ', '*', $sSearchString );
		}

		return '*' . $sSearchString . '*';
	}

	/**
	 * Simple Search interface for more like this query
	 *
	 * @param string $query The raw query string
	 * @param int $offset The starting offset for result documents
	 * @param int $limit The maximum number of result documents to return
	 * @param array $aParams key / value pairs for other query parameters (see Solr documentation), use arrays for parameter keys used more than once (e.g. facet.field)
	 * @return Apache_Solr_Response
	 */
	public function mlt( $query, $offset = 0, $limit = 10, $aParams = array() ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( !is_array( $aParams ) ) {
			$aParams = array();
		}

		// common parameters in this interface
		$aParams['wt'] = self::SOLR_WRITER;
		$aParams['json.nl'] = $this->_namedListTreatment;
		$aParams['q'] = $query;
		$aParams['rows'] = $limit;

		$sQueryString = $this->buildHttpQuery( $aParams );
		wfProfileOut( 'BS::'.__METHOD__ );

		return $this->_sendRawGet( $this->_morelikethisUrl.$sQueryString );
	}

	/**
	 * Simple Search interface for spellcheck query
	 *
	 * @param string $sQuery The raw query string
	 * @param int $offset The starting offset for result documents
	 * @param int $limit The maximum number of result documents to return
	 * @param array $params key / value pairs for other query parameters (see Solr documentation), use arrays for parameter keys used more than once (e.g. facet.field)
	 * @return Apache_Solr_Response
	 */
	public function spellcheck( $sQuery, $iOffset = 0, $iLimit = 10, $aParams = array(), $bIndexing ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( !is_array( $aParams ) ) {
			$aParams = array();
		}

		$aParams['spellcheck'] = 'true';
		$aParams['q'] = $sQuery;
		$aParams['spellcheck.q'] = $sQuery;
		$aParams['spellcheck.count'] = 1;

		if ( $bIndexing === false ) {
			$aParams['wt'] = self::SOLR_WRITER;
			$aParams['json.nl'] = $this->_namedListTreatment;
		}

		$sQueryString = $this->buildHttpQuery( $aParams );
		wfProfileOut( 'BS::'.__METHOD__ );

		return $this->_sendRawGet( $this->_spellcheckUrl.$sQueryString );
	}

	protected function buildHttpQuery( $aParams ) {
		// use http_build_query to encode our arguments because its faster
		// than urlencoding all the parts ourselves in a loop
		$sQueryString = http_build_query( $aParams, null, $this->_queryStringDelimiter );

		// because http_build_query treats arrays differently than we want to, correct the query
		// string by changing foo[#]=bar (# being an actual number) parameter strings to just
		// multiple foo=bar strings. This regex should always work since '=' will be urlencoded
		// anywhere else the regex isn't expecting it
		$sQueryString = preg_replace( '/%5B(?:[0-9]|[1-9][0-9]+)%5D=/', '=', $sQueryString );

		return $this->_queryDelimiter.$sQueryString;
	}

	/**
	 * Queries service with spellchecker
	 * @param string $search Search string
	 * @param array $searchoptions key / value pairs for other query parameters (see Solr documentation), use arrays for parameter keys used more than once (e.g. facet.field)
	 * @return array List of spell check suggestions
	 */
	public function getSpellcheck( $sSearch, $aSearchOptions, $bIndexing = false ) {
		try {
			$oHits = $this->spellcheck( $sSearch, 0, 1, $aSearchOptions, $bIndexing );
		} catch ( Exception $e ) {
			return false;
		}

		$aResult = array();
		if ( !isset( $oHits->spellcheck->suggestions ) ) return $aResult;

		foreach ( $oHits->spellcheck->suggestions as $vTerm ) {
			if ( is_object( $vTerm ) ) {
				if ( isset( $vTerm->suggestion ) ) {
					foreach ( $vTerm->suggestion as $oSuggestion ) {
						$aResult[$oSuggestion->freq] = $oSuggestion->word;
					}
				}
			}
		}
		krsort( $aResult );

		return array_unique( $aResult );
	}

	/**
	 * Prepare curl handle for text extraction
	 */
	public function initGetFileTextCurlHandle() {
		wfProfileIn( 'BS::'.__METHOD__ );
		/* extractFormat=text|xml
		 *    - text: xml-formatted, but whole body embraced by only one tag
		 *    - xml:  xml-formatted, each entity of the document (headings,
		 *            body, paragraphs, ...) embraced by it's own tag
		 */
		$url = $this->sUrl . BsConfig::get( 'MW::ExtendedSearch::SolrCore' ) . '/update/extract?extractOnly=true&extractFormat=text';
		if ( $this->oGetFileTextCurlHandle === null
			|| $this->iGetFileTextConnectionCounter > 30
			|| $this->iGetFileTextCurlAge + 75 < microtime( true ) ) {
			if ( $this->oGetFileTextCurlHandle !== null ) {
				curl_close( $this->oGetFileTextCurlHandle );
			}
			$this->oGetFileTextCurlHandle = curl_init(); // todo: function_exists('curl_init') not true on every installation => handle Exception
			$this->iGetFileTextConnectionCounter = 0;
			$this->iGetFileTextCurlAge = microtime( true );

			curl_setopt( $this->oGetFileTextCurlHandle, CURLOPT_HEADER, false ); // do not include http-header in returned transfer (remains accessible via curl_getinfo)
			curl_setopt( $this->oGetFileTextCurlHandle, CURLOPT_HTTPHEADER, array( "Content-Type: text/xml; charset=utf-8", "Expect:" ) );
			curl_setopt( $this->oGetFileTextCurlHandle, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $this->oGetFileTextCurlHandle, CURLOPT_URL, $url );
			curl_setopt( $this->oGetFileTextCurlHandle, CURLOPT_POST, 1 );

			if ( stripos( $url, 'https' ) === 0 ) {
				curl_setopt( $this->oGetFileTextCurlHandle, CURLOPT_SSL_VERIFYPEER, false ); // Allow self-signed certs
				curl_setopt( $this->oGetFileTextCurlHandle, CURLOPT_SSL_VERIFYHOST, false ); // Allow certs that do not match the hostname
			}
		}
		wfProfileOut( 'BS::'.__METHOD__ );
	}

		/**
	 * Get a vaild curl handle.
	 * @return resource Curl handle.
	 */
	protected function &getCurlHandle() {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( $this->curlConnectionCounter > 200 ) {
			curl_close( $this->curlHandle );
			$this->curlHandle = null;
		}
		if ( $this->curlHandle === null ) {
			$this->curlHandle = curl_init(); // todo: function_exists('curl_init') not true on every installation => handle Exception
			$this->curlConnectionCounter = 0;
			//curl_setopt($this->curlHandle, CURLOPT_FRESH_CONNECT, 1); // Forces new http-connection
			//curl_setopt($this->curlHandle, CURLOPT_FORBID_REUSE, 1);  // Closes http-connection after the request
			//curl_setopt($this->curlHandle, CURLOPT_VERBOSE, 1);
			curl_setopt( $this->curlHandle, CURLOPT_HEADER, true );
			curl_setopt( $this->curlHandle, CURLOPT_HTTPHEADER, array( "Content-Type: text/xml; charset=utf-8", "Expect:" ) );
			curl_setopt( $this->curlHandle, CURLOPT_RETURNTRANSFER, true );
			curl_setopt( $this->curlHandle, CURLOPT_SSL_VERIFYPEER, false ); // Allow self-signed certs
			curl_setopt( $this->curlHandle, CURLOPT_SSL_VERIFYHOST, false ); // Allow certs that do not match the hostname
		}
		wfProfileOut( 'BS::'.__METHOD__ );

		return $this->curlHandle;
	}

	/**
	 * Sends a file to the Solr-Server to be disassembled there
	 * transfer is done by cUrl, not by
	 * @param string $filepath The filepath of the local file
	 * @return mixed The result of the cURL-Request to the Solr-Server or '' if errors occurred
	 */
	public function &getFileText( $filepath, $timeLimit = null ) {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->initGetFileTextCurlHandle();

		// @todo: $rawPost = file_get_contents(urldecode($filepath));
		$vText = file_get_contents( $filepath );
		curl_setopt( $this->oGetFileTextCurlHandle, CURLOPT_POSTFIELDS, $vText );

		curl_setopt( $this->oGetFileTextCurlHandle, CURLOPT_TIMEOUT, 20 ); // 290 did not work! Error: Fatal error: Maximum execution time of 60 seconds exceeded in ...

		$vText = curl_exec( $this->oGetFileTextCurlHandle );
		$vText = simplexml_load_string( $vText );
		$vText = trim( $vText->str );
		$vText = strip_tags( $vText );

		$this->iGetFileTextConnectionCounter++;

		if ( intval( curl_getinfo( $this->oGetFileTextCurlHandle, CURLINFO_HTTP_CODE ) ) != 200 ) {
			$cuGI = curl_getinfo( $this->oGetFileTextCurlHandle );
			wfProfileOut( 'BS::'.__METHOD__ );
			throw new Exception( "Error extracting document {$filepath}, cUrl returns http_code: {$cuGI['http_code']} and upload_content_length: {$cuGI['upload_content_length']}" );
		}
		if ( curl_errno( $this->oGetFileTextCurlHandle ) != 0 ) {
			wfProfileOut( 'BS::'.__METHOD__ );
			throw new Exception( 'Search::getFileText - curl_error '.curl_error( $this->oGetFileTextCurlHandle ).' for file: '.$filepath );
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return $vText;
	}

	/**
	 * If server does not answer with http-status 200 an Exception is thrown
	 * @param string $sParams Param to specify delete query
	 * @return integer status of connect to server
	 */
	public function deleteIndex( $sParams = '' ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$customerId = BsConfig::get( 'MW::ExtendedSearch::CustomerID' );
		if ( empty( $customerId )
			|| ( strpos( $customerId, '?' ) !== false )
			|| ( strpos( $customerId, '*' ) !== false ) ) return false;

		$sQuery = "wiki:$customerId";
		if ( !empty( $sParams ) ) {
			$sQuery = "($sQuery)AND($sParams)";
		}

		$response = $this->deleteByQuery( $sQuery );
		$status = $response->getHttpStatus();

		BuildIndexMainControl::getInstance()->commitAndOptimize( true );

		wfProfileOut( 'BS::'.__METHOD__ );

		return $status;
	}

}