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
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    2.23.0 stable
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class of ExtendedSearch extension
 */
class ExtendedSearch extends BsExtensionMW {

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
			EXTINFO::DESCRIPTION => wfMessage( 'bs-extendedsearch-desc' )->escaped(),
			EXTINFO::AUTHOR => 'Stephan Muggli, Mathias Scheer, Markus Glaser',
			EXTINFO::VERSION => 'default',
			EXTINFO::STATUS => 'default',
			EXTINFO::PACKAGE => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::ExtendedSearch';

		WikiAdmin::registerModuleClass( 'ExtendedSearchAdmin', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_suche_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-extendedsearchadmin-label'
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of ExtendedSearch extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		global $wgSecretKey, $wgDBname, $wgDBserver;

		// max 32 chars with userlevel! 123 456789012345678 90123456789012 '::' counts as one char :-)
		BsConfig::registerVar( 'MW::ExtendedSearch::DefFuzziness', '0.5', BsConfig::TYPE_STRING, 'bs-extendedsearch-pref-defduzziness' );
		BsConfig::registerVar( 'MW::ExtendedSearch::LimitResults', 15, BsConfig::TYPE_INT|BsConfig::LEVEL_USER,  'bs-extendedsearch-pref-limitresultdef', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SearchFiles', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_USER|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-extendedsearch-pref-searchfiles', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::JumpToTitle', false, BsConfig::TYPE_BOOL|BsConfig::LEVEL_USER, 'bs-extendedsearch-pref-jumptotitle', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowCreateSugg', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_USER, 'bs-extendedsearch-pref-showcreatesugg', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowFacets', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_USER, 'bs-extendedsearch-pref-showfacets', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowAutocomplete', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-showautocomplete', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowCreSugInAc', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_USER, 'bs-extendedsearch-pref-showcresuginac', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::AcEntries', 10, BsConfig::TYPE_INT|BsConfig::LEVEL_PUBLIC,  'bs-extendedsearch-pref-acentries', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::IndexTyLinked', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-indextylinked', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::IndexTypesRepo', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-indextypesrepo', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::IndexTypesWiki', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-indextypeswiki', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::IndexTypesSpecial', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-indextypesspecial', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ExternalRepo', '', BsConfig::TYPE_STRING|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-externalrepo' );
		BsConfig::registerVar( 'MW::ExtendedSearch::DefScopeUser', 'text', BsConfig::TYPE_STRING|BsConfig::LEVEL_USER|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-extendedsearch-pref-defscopeuser', 'select' );
		BsConfig::registerVar( 'MW::ExtendedSearch::HighlightSnippets', '3', BsConfig::TYPE_INT|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-highlightsnippets', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::LogUsers', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-logusers', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::Logging', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-logging', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::IndexFileTypes', 'doc, docx, pdf, ppt, pptx, xls, xlsx, txt', BsConfig::TYPE_STRING|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-indexfiletypes' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SolrServiceUrl', 'http://127.0.0.1:8080/solr', BsConfig::TYPE_STRING|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-solrserviceurl' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SolrPingTime', 2, BsConfig::TYPE_INT|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-solrpingtime', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SetFocus', true, BsConfig::LEVEL_USER|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_BOOL, 'bs-extendedsearch-pref-setfocus', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::CustomerID', sha1( $wgDBserver . $wgDBname . $wgSecretKey ), BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING );
		BsConfig::registerVar( 'MW::ExtendedSearch::NumFacets', 15, BsConfig::LEVEL_USER|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_INT, 'bs-extendedsearch-pref-numfacets', 'int' );
		BsConfig::registerVar( 'MW::ExtendedSearch::ShowMlt', true, BsConfig::TYPE_BOOL|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-showmlt', 'toggle' );
		BsConfig::registerVar( 'MW::ExtendedSearch::SolrCore', 'bluespice', BsConfig::TYPE_STRING|BsConfig::LEVEL_PUBLIC, 'bs-extendedsearch-pref-solrcore' );
		BsConfig::registerVar( 'MW::ExtendedSearch::MltNS', array( 0 ), BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_ARRAY_INT|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-extendedsearch-pref-mltns', 'multiselectex' );

		// Hooks
		$this->setHook( 'ArticleSaveComplete' );
		$this->setHook( 'ArticleDeleteComplete' );
		$this->setHook( 'ArticleUndelete' );
		$this->setHook( 'TitleMoveComplete' );
		$this->setHook( 'FileUpload' );
		$this->setHook( 'FileDeleteComplete' );
		$this->setHook( 'FileUndeleteComplete' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSWidgetListHelperInitKeyWords' );
		$this->setHook( 'BSStateBarBeforeBodyViewAdd' );
		$this->setHook( 'BSStateBarAddSortBodyVars' );
		$this->setHook( 'BSDashboardsAdminDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsAdminDashboardPortalPortlets' );
		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );

		$this->mCore->registerPermission( 'searchfiles', array( 'user' ) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for Hook 'OpenSearchUrls'
	 * @param array §$aUrls array of urls for opensearch
	 * @return boolean Always true
	 */
	public static function onOpenSearchUrls( &$aUrls ) {
		$oSearch = SpecialPage::getTitleFor( 'ExtendedSearch' );
		$aUrls = array(
			array(
				'type' => 'text/html',
				'method' => 'get',
				'template' => $oSearch->getCanonicalURL( 'q={searchTerms}' )
			)
		);
		return true;
	}

	/**
	 * Hook-Handler for Hook 'LoadExtensionSchemaUpdates'
	 * @param object §updater Updater
	 * @return boolean Always true
	 */
	public static function getSchemaUpdates( $updater ) {
		$updater->addExtensionTable(
			'bs_searchstats', __DIR__.DS.'db'.DS.'ExtendedSearch.sql'
		);
		$updater->addExtensionField(
			'bs_searchstats', 'stats_scope',
			__DIR__.DS.'db'.DS.'ExtendedSearch.stats_scope.patch.sql'
		);
		return true;
	}

	/**
	* Adds the 'ext.bluespice.extendedsearch' module to the OutputPage
	* @param OutputPage $oOut
	* @param Skin $oSkin
	* @return boolean
	*/
	public function onBeforePageDisplay( $oOut, $oSkin ) {
		$oOut->addModuleStyles( 'ext.bluespice.extendedsearch.autocomplete.style' );
		$oOut->addModules( 'ext.bluespice.extendedsearch.autocomplete' );
		$oOut->addModules( 'ext.bluespice.extendedsearch.focus' );
		$oOut->addModules( 'ext.bluespice.extendedsearch.form' );
		return true;
	}

	/**
	 * Event-Handler for 'MW::Utility::WidgetListHelper::InitKeywords'. Registers a callback for the WHOISONLINE Keyword.
	 * @param array $aKeywords An array of Keywords array( 'KEYWORD' => $callable )
	 * @return array The appended array of Keywords array( 'KEYWORD' => $callable )
	 */
	public function onBSWidgetListHelperInitKeyWords( &$aKeywords, $oTitle ) {
		$aKeywords[ 'MORELIKETHIS' ] = array( $this, 'onWidgetListKeyword' );
		return true;
	}

	/**
	 * Callback for WidgetListHelper. Adds the WhoIsOnline Widget to the list if Keyword is found.
	 * @return ViewWidget.
	 */
	public function onWidgetListKeyword( $oTitle ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$oWidgetView = new ViewWidget();
		$oWidgetView
			->setId( 'bs-extendedsearch-mlt' )
			->setTitle( wfMessage( 'bs-extendedsearch-morelikethis' )->plain() )
			->setBody( ExtendedSearchBase::getInstance( $this )->getViewMoreLikeThis( $oTitle )->execute() )
			->setTooltip( wfMessage( 'bs-extendedsearch-morelikethis' )->plain() )
			->setAdditionalBodyClasses( array( 'bs-nav-links', 'bs-extendedsearch-portlet' ) ); //For correct margin and fontsize

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oWidgetView;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 * @param array $aSortBodyVars
	 * @return boolean Always true to keep hook running
	 */
	public function onBSStateBarAddSortBodyVars( &$aSortBodyVars ) {
		$aSortBodyVars['statebarbodymorelikethis'] = wfMessage( 'bs-extendedsearch-morelikethis' )->plain();
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeBodyViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aBodyViews
	 * @return boolean Always true to keep hook running
	 */
	public function onBSStateBarBeforeBodyViewAdd( $oStateBar, &$aBodyViews, $oUser, $oTitle ) {
		if ( $oTitle->exists() == false ) return true;

		$oMltListView = new ViewStateBarBodyElement();
		$oMltListView->setKey( 'MoreLikeThis' );
		$oMltListView->setHeading( wfMessage( 'bs-extendedsearch-morelikethis' )->plain() );
		$oMltListView->setBodyText( ExtendedSearchBase::getInstance( $this )->getViewMoreLikeThis( $oTitle )->execute() );

		$aBodyViews['statebarbodymorelikethis'] = $oMltListView;
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
		if ( $oVariable->getName() === 'DefScopeUser' ) {
			$aPrefs = array(
				'options' => array(
					wfMessage( 'bs-extendedsearch-pref-scope-text' )->plain()  => 'text',
					wfMessage( 'bs-extendedsearch-pref-scope-title' )->plain() => 'title'
				)
			);
		} elseif ( $oVariable->getName() === 'MltNS' ) {
			$aPrefs = array(
				'type' => 'multiselectex',
				'options' => BsNamespaceHelper::getNamespacesForSelectOptions( array( NS_SPECIAL, NS_MEDIA ) )
			);
		}
		return $aPrefs;
	}

	/**
	 * Returns rendered inner part of search results page. Used for faceting and paging. AJAX function.
	 * @return string JSON encoded HTML of search results page.
	 */
	public static function getRequestJson() {
		$viewContentsSpecialPage = ExtendedSearchBase::getInstance( RequestContext::getMain() )
			->getResults( true );

		$oDummy = new StdClass();
		$oDummy->data = array(
			'bodytext' => $viewContentsSpecialPage->execute()
		);

		wfRunHooks( 'ExtendedSearchBeforeAjaxResponse', array( null, &$oDummy ) );

		return json_encode(
			array(
				'contents' => $oDummy->data['bodytext']
			)
		);
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = array(
			'type'  => 'BS.ExtendedSearch.RecentSearchTermsPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-extendedsearch-recentsearchterms' )->plain()
			),
			'title' => wfMessage( 'bs-extendedsearch-recentsearchterms' )->plain(),
			'description' => wfMessage( 'bs-extendedsearch-recentsearchtermsdesc' )->plain()
		);

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param boolean $bIsDefault default
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = array(
			'type'  => 'BS.ExtendedSearch.RecentSearchTermsPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-extendedsearch-recentsearchterms' )->plain()
			)
		);
		return true;
	}

	/**
	 * Sets values for search form.
	 * @param SkinTemplate $sktemplate
	 * @param BaseTemplate $tpl
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ){
		$tpl->set( 'searchtitle', SpecialPage::getTitleFor( 'SpecialExtendedSearch' )->getFullText() );

		if ( !empty( SearchOptions::$searchStringRaw ) ) {
			$tpl->set( 'q', SearchOptions::$searchStringRaw );
		}

		$tpl->set(
			'bs_search_input',
			array(
				'id' => 'bs-extendedsearch-input',
				'type' => 'text',
				'name' => 'q'
			)
		);

		$aHiddenFields = array();
		$aHiddenFields['sft'] = '1';
		if ( BsConfig::get( 'MW::ExtendedSearch::SearchFiles' ) ) {
			$aHiddenFields['search_files'] = '1';
		}

		$tpl->set( 'bs_search_hidden_fields', $aHiddenFields );
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
			BuildIndexMainControl::getInstance()->deleteFromIndexWiki( $iID );
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
			BuildIndexMainControl::getInstance()->updateIndexWiki( $oArticle );
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
			BuildIndexMainControl::getInstance()->updateIndexWikiByTitleObject( $oTitle );
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
			BuildIndexMainControl::getInstance()->updateIndexWikiByTitleObject( $oNewtitle );
			// Check if redirect is created; 0 is no redirect
			if ( $iNewID !== 0 ) {
				BuildIndexMainControl::getInstance()->updateIndexWikiByTitleObject( $oTitle );
			}
			// Moving file if namespace of title is the file namespace
			if ( $oTitle->getNamespace() == NS_FILE ) {
				$oOldFile = LocalFile::newFromTitle( $oTitle, RepoGroup::singleton()->getLocalRepo() );
				$oNewFile = RepoGroup::singleton()->findFile( $oNewtitle );

				BuildIndexMainControl::getInstance()->deleteIndexFile( $oOldFile->getPath(), 'repo' );
				BuildIndexMainControl::getInstance()->updateIndexFile( $oNewFile );
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
			BuildIndexMainControl::getInstance()->updateIndexFile( $oFile );
		} catch ( BsException $e ) {
			wfDebugLog( 'ExtendedSearch', 'onFileUpload: '.$e->getMessage() );
		}
		return true;
	}

	/**
	 * Delete file from index when file is deleted
	 * @param File $oFile MediaWiki file object of deleted file
	 * @param File $oOldimage the name of the old file
	 * @param Article $oArticle reference to the article if all revisions are deleted
	 * @param User $oUser user who performed the deletion
	 * @param string $sReason reason
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onFileDeleteComplete( $oFile, $oOldimage, $oArticle, $oUser, $sReason ) {
		try {
			BuildIndexMainControl::getInstance()->deleteIndexFile( $oFile->getPath(), 'repo' );
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
			BuildIndexMainControl::getInstance()->updateIndexFile( $oFile );
		} catch ( BsException $e ) {
			wfDebugLog( 'ExtendedSearch', 'onFileUndeleteComplete: '.$e->getMessage() );
		}
		return true;
	}

}
