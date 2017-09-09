<?php
/**
 * Processes search request for ExtendedSearch for MediaWiki
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Processes a search request
 */
class SearchRequest {

	/**
	 * Request Context
	 * @var $oRequest WebRequest object
	 */
	protected $oRequest;
	/**
	 * Instance of search service
	 * @var object of search service
	 */
	protected static $oInstance = null;

	/**
	 * Cosntructor for SearchRequestMW class
	 * @param BsSearchRequest $instanceToDecorate Object to extend with additional functionality
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->oRequest = RequestContext::getMain()->getRequest();
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Return a instance of SearchRequest.
	 * @return SearchRequest Instance of SearchRequest
	 */
	public static function getInstance() {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( self::$oInstance === null ) {
			self::$oInstance = new self();
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return self::$oInstance;
	}

	/**
	 * Read in the request parameters
	 */
	public function init() {
		$this->setDefaults();
		$this->processSettings();
		$this->processInputs();
	}

	/**
	 * Sets the defaults for a search request.
	 */
	protected function setDefaults() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->sOrder = 'score';
		$this->sAsc = 'desc';
		$this->iOffset = 0;
		$this->bSearchFiles = false;
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Get values from settings
	 */
	protected function processSettings() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->sDefaultFuzziness = BsConfig::get( 'MW::ExtendedSearch::DefFuzziness' );
		$this->sHighlightSnippets = BsConfig::get( 'MW::ExtendedSearch::HighlightSnippets' );
		$this->bLogUsers = BsConfig::get( 'MW::ExtendedSearch::LogUsers' );
		$this->bLogging = BsConfig::get( 'MW::ExtendedSearch::Logging' );
		$this->iMaxDocSize = ( BsConfig::get( 'MW::ExtendedSearch::MaxDocSizeMB', 10 ) * 1024 * 1024 );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 *
	 * @var string
	 */
	public $sScope = null;

	/**
	 * Not used anymore
	 * @var string
	 * @deprecated since version 1.23
	 */
	public $sOperator = null;

	/**
	 *
	 * @var string
	 */
	public $sAsc = null;

	/**
	 *
	 * @var int
	 */
	public $iOffset = null;

	/**
	 *
	 * @var string
	 */
	public $sOrder = null;

	/**
	 *
	 * @var string
	 */
	public $sId = null;

	/**
	 *
	 * @var string
	 */
	public $sInput = null;

	/**
	 *
	 * @var string
	 */
	public $sHidden = null;

	/**
	 *
	 * @var boolean
	 */
	public $bExtendedForm = null;

	/**
	 * Flag that indicates whether or not to automatically redirect to an
	 * existing page if search term matches page title. Used in
	 * 'SearchIndex::search'
	 * @var boolean
	 */
	public $bSft = null;

	/**
	 * This is a legacy typo. Should be 'aEditors'.
	 * @var array
	 * @deprecated since version 1.23
	 */
	public $sEditor = array();

	/**
	 *
	 * @var array
	 */
	public $aEditors = array();

	/**
	 * This is a legacy typo. Should be 'aCategories'.
	 * @var array
	 * @deprecated since version 1.23
	 */
	public $sCategories = array();

	/**
	 *
	 * @var array
	 */
	public $aCategories = array();

	/**
	 *
	 * @var array
	 */
	public $aNamespaces = null;

	/**
	 * This is a legacy typo. Should be 'aTypes'.
	 * @var array
	 * @deprecated since version 1.23
	 */
	public $aType = array();

	/**
	 *
	 * @var array
	 */
	public $aTypes = null;

	/**
	 *
	 * @var boolean
	 */
	public $bNoSelect = null;

	/**
	 *
	 * @var array
	 */
	public $aFacetSettings = array();

	/**
	 * Get values from url parameters
	 */
	protected function processInputs() {
		$this->sScope = $this->oRequest->getVal( 'search_scope' );
		$this->sOperator = $this->oRequest->getVal( 'op' );
		$this->sAsc = $this->oRequest->getVal( 'search_asc', $this->sAsc ); //ASC|DESC
		$this->iOffset = $this->oRequest->getVal( 'search_offset', $this->iOffset ); // todo: type is int??
		$this->sOrder = $this->oRequest->getVal( 'search_order', $this->sOrder ); //score|titleSort|type|ts
		$this->sId = $this->oRequest->getVal( 'search_id', false );
		$this->sInput = $this->oRequest->getVal( 'q', false );
		$this->sHidden = $this->oRequest->getVal( 'search_hidden' );
		$this->bExtendedForm = $this->oRequest->getFuzzyBool( 'search_extended', false );
		$this->bSft = $this->oRequest->getFuzzyBool( 'sft', false );
		$this->sEditor = $this->aEditors = $this->oRequest->getArray( 'ed', array() );
		$this->sCategories = $this->aCategories = $this->oRequest->getArray( 'ca', array() );
		$this->aNamespaces = $this->oRequest->getArray( 'na', array() );
		$this->aType =$this->aTypes = $this->oRequest->getArray( 'ty', array() );
		$this->bNoSelect = $this->oRequest->getBool( 'nosel', false );
		$this->aFacetSettings = FormatJson::decode( $this->oRequest->getVal( 'fset', '{}' ), true );

		$this->bSearchFiles = ( $this->oRequest->getInt( 'search_files', 0 ) === 1 )
			? true
			: false;

		if ( !$this->sScope ) {
			$this->sScope = BsConfig::get( 'MW::ExtendedSearch::DefScopeUser' );
		}

		Hooks::run( 'BSExtendedSearchRequestProcessInputs', array( $this ) );
	}

}