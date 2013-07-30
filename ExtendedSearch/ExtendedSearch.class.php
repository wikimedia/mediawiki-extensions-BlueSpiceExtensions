<?php
/**
 * Extended Search extension for BlueSpice
 *
 * Search plugin on Apache Solr basis
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
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    1.22.0 stable
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v1.20.1
 * - Fixed indexing of zipped doctypes
 * - Layout Improvments
 * - Serveral bug fixes
 * v1.20.0
 *
 * v1.0.0
 * - raised to stable
 * v0.1
 * - initial release
 */

/**
 * Base class for ExtendedSearch extension
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ExtendedSearch extends BsExtensionMW {

	/**
	 * Object of BsExtendedSearchBaseMW
	 * @var Object
	 */
	protected $oExtendedSearchBase = null;
	/**
	 * Unique wiki id
	 */
	private $sWikiID = '';

	/**
	 * Constructor of ExtendedSearch class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME => 'ExtendedSearch',
			EXTINFO::DESCRIPTION => 'Apache Solr based search plugin for BlueSpice',
			EXTINFO::AUTHOR => 'Stephan Muggli, Mathias Scheer, Markus Glaser',
			EXTINFO::VERSION => '1.22.0 ($Rev: 9903 $)',
			EXTINFO::STATUS => 'stable',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array( 'bluespice' => '1.22.0' )
		);
		$this->mExtensionKey = 'MW::ExtendedSearch';

		$this->registerExtensionSchemaUpdate( 'bs_searchstats', __DIR__ . DS . 'db' . DS . 'ExtendedSearch.sql' );

		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'ExtendedSearch', $this, 'getRequestJson', 'read' );

		WikiAdmin::registerModuleClass( 'ExtendedSearchAdmin', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_suche_v1.png',
			'level' => 'editadmin'
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of ExtendedSearch extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		global $wgSecretKey;

		// max 32 chars with userlevel! 123 456789012345678 90123456789012 '::' counts as one char :-)
		BsConfig::registerVar( 'MW::ExtendedSearch::DefFuzziness', '0.5', BsConfig::TYPE_STRING, 'bs-extendedsearch-pref-defduzziness' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowPercent', true, BsConfig::TYPE_BOOL, 'bs-extendedsearch-pref-showpercent', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::LimitResultDef', 25, BsConfig::TYPE_INT|BsConfig::LEVEL_PUBLIC,  'bs-extendedsearch-pref-limitresultdef', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SearchFiles', false, BsConfig::TYPE_BOOL|BsConfig::LEVEL_USER, 'bs-extendedsearch-pref-searchfiles', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::JumpToTitle', false, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-jumptotitle', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowCreateSugg', false, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-showcreatesugg', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowSpell', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-showspell', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowFacets', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_USER, 'bs-extendedsearch-pref-showfacets', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowAutocomplete', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-showautocomplete', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowCreSugInAc', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_USER, 'bs-extendedsearch-pref-showcresuginac', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::AcEntries', 10, BsConfig::TYPE_INT|BsConfig::LEVEL_PUBLIC,  'bs-extendedsearch-pref-acentries', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::IndexTyLinked', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-indextylinked', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::IndexTypesRepo', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-indextypesrepo', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::IndexTypesWiki', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-indextypeswiki', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ExternalRepo', '', BsConfig::TYPE_STRING|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-externalrepo' );
		BsConfig::registerVar( 'MW::ExtendedSearch::DefScopeUser', 'text', BsConfig::TYPE_STRING|BsConfig::LEVEL_USER|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-extendedsearch-pref-defscopeuser', 'select' );
		BsConfig::registerVar( 'MW::ExtendedSearch::FormMethod', 'get', BsConfig::TYPE_STRING|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-formmethod' );
		BsConfig::registerVar( 'MW::ExtendedSearch::HighlightSnippets', '3', BsConfig::TYPE_INT|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-highlightsnippets', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::LogUsers', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-logusers', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::Logging', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-logging', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SelectorHeight', 10, BsConfig::TYPE_INT );
		BsConfig::registerVar( 'MW::ExtendedSearch::SelectorWidth', '200px', BsConfig::TYPE_STRING );
		BsConfig::registerVar( 'MW::ExtendedSearch::IndexFileTypes', 'doc, pdf, ppt, xls, txt', BsConfig::TYPE_STRING|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-indexfiletypes' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SolrServiceUrl', 'http://127.0.0.1:8080/solr', BsConfig::TYPE_STRING|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-solrserviceurl' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SolrPingTime', 2, BsConfig::TYPE_INT|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-solrpingtime', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SetFocus', true, BsConfig::LEVEL_USER|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-extendedsearch-pref-setfocus', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::CustomerID', md5( $wgSecretKey ), BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING, 'bs-extendedsearch-pref-CustomerID' );
		BsConfig::registerVar( 'MW::ExtendedSearch::NumFacets', 15, BsConfig::LEVEL_USER|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_INT, 'bs-extendedsearch-pref-numfacets', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowMlt', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-showmlt', 'toggle' );

		// Hooks
		$this->setHook( 'FormDefaults' );
		$this->setHook( 'SpecialPage_initList' );
		$this->setHook( 'ArticleSaveComplete' );
		$this->setHook( 'ArticleDeleteComplete' );
		$this->setHook( 'ArticleUndelete' );
		$this->setHook( 'TitleMoveComplete' );
		$this->setHook( 'FileUpload' );
		$this->setHook( 'FileDeleteComplete' );
		$this->setHook( 'FileUndeleteComplete' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSBlueSpiceSkinAfterArticleContent' );

		$this->oExtendedSearchBase = ExtendedSearchBase::getInstance();

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	* Adds the 'ext.bluespice.extendedsearch' module to the OutputPage
	* @param OutputPage $out
	* @param Skin $skin
	* @return boolean
	*/
	public function onBeforePageDisplay( $oOut, $oSkin ) {
		$oOut->addModules( 'ext.bluespice.extendedsearch' );
		return true;
	}

	/**
	 * Hook handler for SpecialPage_initList
	 * @param Array $aList reference list of core special pages
	 * @return true Always ture to keep hook alive
	 */
	public function onSpecialPage_initList( &$aList ) {
		$aList['ExtendedSearch'] = 'SpecialExtendedSearch';
		return true;
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array();
		switch ( $oVariable->getName() ) {
			case 'DefScopeUser' :
				$aPrefs = array(
					'options' => array(
						wfMessage( 'bs-extendedsearch-pref-scope-text' )->plain()  => 'text',
						wfMessage( 'bs-extendedsearch-pref-scope-title' )->plain() => 'title'
					)
				);
				break;
		}

		return $aPrefs;
	}

	/**
	 * Returns rendered inner part of search results page. Used for faceting and paging. AJAX function.
	 * @return string JSON encoded HTML of search results page.
	 */
	public function getRequestJson() {
		$sParamMode    = BsCore::getParam( 'mode', false, BsPARAM::GET | BsPARAMTYPE::STRING );
		$sSearchString = BsCore::getParam( 'searchstring', false, BsPARAM::GET | BsPARAMTYPE::STRING );

		if ( $sParamMode == 'autocomplete' ) {
			$aResults = $this->oExtendedSearchBase->searchAutocomplete( $sSearchString );
			exit( $aResults );
		}

		$viewContentsSpecialPage = $this->oExtendedSearchBase->renderSpecialpage();

		$oDummy = new StdClass();
		$oDummy->data = array( 'bodytext' => $viewContentsSpecialPage->execute() );

		wfRunHooks( 'ExtendedSearchBeforeAjaxResponse', array( null, &$oDummy ) );

		exit( json_encode( array( 'contents' => $oDummy->data['bodytext'] ) ) );
	}

	/**
	 * Hook-Handler for 'BSBlueSpiceSkinAfterArticleContent'. Creates the authors list below an article.
	 * @param array $aViews Array of views to be rendered in skin
	 * @param User $oUser Current user object
	 * @param Title $oTitle Current title object
	 * @return Boolean Always true to keep hook running.
	 */
	public function onBSBlueSpiceSkinAfterArticleContent( &$aViews, $oUser, $oTitle ) {
		if ( BsConfig::get( 'MW::ExtendedSearch::ShowMlt' ) === false ) return true;
		if ( !$oTitle->exists() || $oTitle->isSpecialPage() ) return true;
		global $wgRequest;
		if ( $wgRequest->getVal( 'action', 'view' ) != 'view' ) return true;

		$aMltQuery = SearchOptions::getInstance()->getSolrMltQuery();
		try {
			$oResults = SearchService::getInstance()->mlt( $aMltQuery['searchString'], $aMltQuery['offset'], $aMltQuery['searchLimit'], $aMltQuery['searchOptions'] );
		} catch ( Exception $e ) {
			return true;
		}

		$aMlt = array();
		$aMlt[] = implode( ', ', $oResults->interestingTerms );
		foreach ( $oResults->response->docs as $oRes ) {
			if ( $oRes->namespace != 999 ) {
				$oMltTitle = Title::makeTitle( $oRes->namespace, $oRes->title );
			} else {
				$oMltTitle = Title::makeTitle( NS_FILE, $oRes->title );
			}

			if ( !$oMltTitle->userCan( 'read' ) ) continue;
			if ( $oMltTitle->getArticleID() == $oTitle->getArticleID() ) continue;

			$aMlt[] = BsLinkProvider::makeLink( $oMltTitle );
		}

		if ( !empty( $aMlt ) ) {
			$oViewMlt = new ViewMoreLikeThis;
			$oViewMlt->setOption( 'mlt', $aMlt );

			array_unshift( $aViews, $oViewMlt );
		}

		return true;
	}

	/**
	 * Determines default values for search form.
	 * @param BsEvent $oEvent Contains additional information, e.g. calling instance.
	 * @param array $aSearchBoxKeyValues List of parameters sent by calling event.
	 * @return array Modified parameters list.
	 */
	public function onFormDefaults( $oCallingInstance, &$aSearchBoxKeyValues ) {
		$aSearchBoxKeyValues['SearchTextFieldName'] = 'search_input';

		$aLocalUrl = SpecialPage::getTitleFor( 'SpecialExtendedSearch' )->getLocalUrl();
		$aLocalUrl = explode( '?', $aLocalUrl );
		$aSearchBoxKeyValues['SearchDestination'] = $aLocalUrl[0];

		$aSearchBoxKeyValues['HiddenFields']['search_go'] = 'true';
		if ( isset( $aLocalUrl[1] ) && strpos( $aLocalUrl[1], '=' ) !== false ) {
			$aTitle = explode( '=', $aLocalUrl[1] );
			$aSearchBoxKeyValues['HiddenFields']['title'] = urldecode( $aTitle[1] );
		}
		if ( BsConfig::get( 'MW::ExtendedSearch::SearchFiles' ) ) {
			$aSearchBoxKeyValues['HiddenFields']['search_files'] = '1';
		}
		if ( $oCallingInstance instanceof ViewExtendedSearchFormPage ) {
			$aSearchBoxKeyValues['HiddenFields']['search_origin'] = 'search_form_body';
		} elseif ( $oCallingInstance instanceof BlueSpiceTemplate ) {
			$aSearchBoxKeyValues['HiddenFields']['search_origin'] = 'titlebar';
		}

		$aSearchBoxKeyValues['TitleKeyValuePair'] = array( 'search_scope', 'title' );
		$aSearchBoxKeyValues['FulltextKeyValuePair'] = array( 'search_scope', 'text' );

		// Default scope 
		$aSearchBoxKeyValues['DefaultKeyValuePair'] = ( BsConfig::get( 'MW::ExtendedSearch::DefScopeUser' ) == 'title' )
			? $aSearchBoxKeyValues['TitleKeyValuePair']
			: $aSearchBoxKeyValues['FulltextKeyValuePair'];

		$aSearchBoxKeyValues['method'] = ( 0 == strcasecmp( BsConfig::get( 'MW::ExtendedSearch::FormMethod' ), 'get' ) ) ? 'get' : 'post';

		if ( !empty( SearchOptions::$searchStringRaw ) ) $aSearchBoxKeyValues['SearchTextFieldText'] = SearchOptions::$searchStringRaw;

		return true;
	}

	/**
	 * Delete search index entry on article deletion
	 * @param Article $oArticle The article that is being deleted.
	 * @param User $oUser The user that deletes.
	 * @param string $sReason A reason for article deletion
	 * @param int $iID Id of article that was deleted.
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onArticleDeleteComplete( &$oArticle, &$oUser, $sReason, $iID ) {
		try {
			$this->oExtendedSearchBase->deleteFromIndexWiki( $iID );
		} catch ( BsException $e ) {
			wfDebugLog( 'ExtendedSearch', 'onArticleDeleteComplete: '.$e->getMessage() );
		}

		return true;
	}

	/**
	 * Update index on article change.
	 * @param Article $oArticle The article that is created.
	 * @param User $oUser User that saved the article.
	 * @param string $sText New text.
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onArticleSaveComplete( &$oArticle, &$oUser ) {
		try {
			$this->oExtendedSearchBase->updateIndexWiki( $oArticle );
		} catch ( BsException $e ) {
			wfDebugLog( 'ExtendedSearch', 'onArticleSaveComplete: '.$e->getMessage() );
		}

		return true;
	}

	/**
	 * Update index on article undelete
	 * @param Title $oTitle MediaWiki title object of recreated article
	 * @param bool $bCreate Whether or not the restoration caused the page to be created (i.e. it didn't exist before)
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onArticleUndelete( $oTitle, $bCreate ) {
		try {
			$this->oExtendedSearchBase->updateIndexWikiByTitleObject( $oTitle );
		} catch ( BsException $e ) {
			wfDebugLog( 'ExtendedSearch', 'onArticleUndelete: '.$e->getMessage() );
		}

		return true;
	}

	/**
	 * Update search index when an article is moved.
	 * @param Title $oTitle Old title of the moved article.
	 * @param Title $oNewtitle New title of the moved article.
	 * @param User $oUser User that moved the article.
	 * @param int $iOldID ID of the page that has been moved.
	 * @param int $iNewID ID of the newly created redirect.
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onTitleMoveComplete( &$oTitle, &$oNewtitle, &$oUser, $iOldID, $iNewID ) {
		try {
			// Moving article
			$this->oExtendedSearchBase->updateIndexWikiByTitleObject( $oNewtitle );
			// Check if redirect is created; 0 is no redirect
			if ( $iNewID != 0 ) {
				$this->oExtendedSearchBase->updateIndexWikiByTitleObject( $oTitle );
			}
			// Moving file if namespace of title is the file namespace
			if ( $oTitle->getNamespace() == NS_FILE ) {
				$oOldFile = LocalFile::newFromTitle( $oTitle, RepoGroup::singleton()->getLocalRepo() );
				$oNewFile = RepoGroup::singleton()->findFile( $oNewtitle );

				$oFileRepoLocalRef = $oOldFile->getRepo()->getLocalReference( $oOldFile->getPath() );
				if ( !is_null( $oFileRepoLocalRef ) ) {
					$sFilePath = $oFileRepoLocalRef->getPath();
				}

				$this->oExtendedSearchBase->deleteIndexFile( -1,$sFilePath );
				$this->oExtendedSearchBase->updateIndexFile( $oNewFile );
			}
		} catch ( BsException $e ) {
			wfDebugLog( 'ExtendedSearch', 'onTitleMoveComplete: '.$e->getMessage() );
		}

		return true;
	}

	/**
	 * Update index on file upload
	 * @param File $oFile MediaWiki file object of uploaded file
	 * @param bool $bReupload indicates if file was uploaded before
	 * @param bool $bHasDescription indicates if a description page existed before
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onFileUpload( $oFile, $bReupload = false, $bHasDescription = false ) {
		try {
			$this->oExtendedSearchBase->updateIndexFile( $oFile );
		} catch ( BsException $e ) {
			wfDebugLog( 'ExtendedSearch', 'onFileUpload: '.$e->getMessage() );
		}

		return true;
	}

	/**
	 * Delete file from index when file is deleted
	 * @param File $oFile MediaWiki file object of deleted file
	 * @param unknown $uOldimage the name of the old file
	 * @param Article $oArticle reference to the article if all revisions are deleted
	 * @param User $oUser user who performed the deletion
	 * @param string $sReason reason
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onFileDeleteComplete( $oFile, $oOldimage, $oArticle, $oUser, $sReason ) {
		try {
			$oFileRepoLocalRef = $oFile->getRepo()->getLocalReference( $oFile->getPath() );
			if ( !is_null( $oFileRepoLocalRef ) ) {
				$sFilePath = $oFileRepoLocalRef->getPath();
			}
			$this->oExtendedSearchBase->deleteIndexFile( -1, $sFilePath );
		} catch ( BsException $e ) {
			wfDebugLog( 'ExtendedSearch', 'onFileDeleteComplete: '.$e->getMessage() );
		}

		return true;
	}

	/**
	 * Update index when file is undeleted
	 * @param Title $oTitle MediaWiki title object of undeleted file
	 * @param array $aFileVersions array of undeleted versions
	 * @param User $oUser user who performed the undeletion
	 * @param string $sReason reason
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onFileUndeleteComplete( $oTitle, $aFileVersions, $oUser, $sReason ) {
		try {
			$oFile = wfFindFile( $oTitle );
			$this->oExtendedSearchBase->updateIndexFile( $oFile );
		} catch ( BsException $e ) {
			wfDebugLog( 'ExtendedSearch', 'onFileUndeleteComplete: '.$e->getMessage() );
		}

		return true;
	}

}