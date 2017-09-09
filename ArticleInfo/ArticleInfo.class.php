<?php
/**
 * ArticleInfo extension for BlueSpice
 *
 * Provides information about an article for status bar.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage ArticleInfo
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for ArticleInfo extension
 * @package BlueSpice_Extensions
 * @subpackage ArticleInfo
 */

class ArticleInfo extends BsExtensionMW {

	/**
	 * Initialization of ArticleInfo extension
	 */
	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		BsConfig::registerVar( 'MW::ArticleInfo::ImageLastEdited', 'bs-infobar-last-edited.png', BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING );
		BsConfig::registerVar( 'MW::ArticleInfo::ImageLastEditor', 'bs-infobar-author.png', BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING );
		BsConfig::registerVar( 'MW::ArticleInfo::ImageCategories', 'bs-infobar-category.png', BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING );
		BsConfig::registerVar( 'MW::ArticleInfo::ImageSubpages', 'bs-infobar-subpages.png', BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING );
		BsConfig::registerVar( 'MW::ArticleInfo::ImageCheckRevision', 'bs-infobar-revision.png', BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING );
		BsConfig::registerVar( 'MW::ArticleInfo::CheckRevisionInterval', 10, BsConfig::LEVEL_PUBLIC|BsConfig::RENDER_AS_JAVASCRIPT|BsConfig::TYPE_INT, 'bs-articleinfo-pref-CheckRevisionInterval', 'int' );

		$this->mCore->registerBehaviorSwitch( 'bs_noarticleinfo' );

		$this->setHook( 'BSStateBarAddSortTopVars', 'onStatebarAddSortTopVars' );
		$this->setHook( 'BSStateBarAddSortBodyVars', 'onStatebarAddSortBodyVars' );
		$this->setHook( 'BSStateBarBeforeTopViewAdd', 'onStateBarBeforeTopViewAdd' );
		$this->setHook( 'BSStateBarBeforeBodyViewAdd', 'onStateBarBeforeBodyViewAdd' );
		$this->setHook( 'BsAdapterAjaxPingResult' );

		$this->setHook( 'PageContentSaveComplete' );
		$this->setHook( 'ArticleDeleteComplete' );
		$this->setHook( 'BeforePageDisplay');

		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		$oOutputPage->addModules( 'ext.bluespice.articleinfo' );
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortTopVars'
	 * @param array $aSortTopVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortTopVars( &$aSortTopVars ) {
		$aSortTopVars['statebartoplastedited'] = wfMessage( 'bs-articleinfo-statebartoplastedited' )->plain();
		$aSortTopVars['statebartoplasteditor'] = wfMessage( 'bs-articleinfo-statebartoplasteditor' )->plain();
		$aSortTopVars['statebartopcategories'] = wfMessage( 'bs-articleinfo-statebartopcategories' )->plain();
		$aSortTopVars['statebartopsubpages'] = wfMessage( 'bs-articleinfo-statebartopsubpages' )->plain();
		//postponed
		//HINT: http://84.16.252.165/otrs24/index.pl?Action=AgentTicketZoom;TicketID=3980;ArticleID=22500#22173
		//$aSortTopVars['statebartoparticleviews']	= wfMessage( 'bs-articleinfo-statebartoparticleviews' )->text();
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 * @param array $aSortBodyVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortBodyVars( &$aSortBodyVars ) {
		$aSortBodyVars['statebarbodycategories'] = wfMessage( 'bs-articleinfo-statebarbodycategories' )->plain();
		$aSortBodyVars['statebarbodysubpages'] = wfMessage( 'bs-articleinfo-statebarbodysubpages' )->plain();
		$aSortBodyVars['statebarbodytemplates'] = wfMessage( 'bs-articleinfo-statebarbodytemplates' )->plain();
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeTopViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aTopViews
	 * @return boolean Always true to keep hook running
	 */
	public function onStateBarBeforeTopViewAdd( $oStateBar, &$aTopViews, $oUser, $oTitle ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$mContext = BsArticleHelper::getInstance( $oTitle )->getPageProp(
			'bs_noarticleinfo'
		);

		if( $mContext === '' ) {
			wfProfileOut( 'BS::'.__METHOD__ );
			return true;
		}

		$aTopElements = array(
			'statebartoplastedited' => $this->makeStateBarTopLastEdited( $oTitle ),
			'statebartoplasteditor' => $this->makeStateBarTopLastEditor( $oTitle ),
			'statebartopcategories' => $this->makeStateBarTopCategories( $oTitle ),
			'statebartopsubpages'   => $this->makeStateBarTopSubPages( $oTitle )
			//postponed
			//HINT: http://84.16.252.165/otrs24/index.pl?Action=AgentTicketZoom;TicketID=3980;ArticleID=22500#22173
			//'statebartoparticleviews'		=> $this->makeStateBarTopArticleViews	( $oTitle ),
		);

		foreach( $aTopElements as $sKey => $oTopView) {
			if(!$oTopView) continue;
			$aTopViews[$sKey] = $oTopView;
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeBodyViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aBodyViews
	 * @return boolean Always true to keep hook running
	 */
	public function onStateBarBeforeBodyViewAdd( $oStateBar, &$aBodyViews, $oUser, $oTitle ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$mContext = BsArticleHelper::getInstance( $oTitle )->getPageProp(
			'bs_noarticleinfo'
		);
		if( $mContext === '' ) {
			wfProfileOut( 'BS::'.__METHOD__ );
			return true;
		}
		$aBodyElements = array(
			'statebarbodysubpages' => $this->makeStateBarBodySubPages( $oTitle ),
			'statebarbodycategories' => $this->makeStateBarBodyCategories( $oTitle ),
			'statebarbodytemplates' => $this->makeStateBarBodyTemplates( $oTitle )
		);

		foreach ( $aBodyElements as $sKey => $oBodyView ) {
			if ( !$oBodyView ) continue;
			$aBodyViews[$sKey] = $oBodyView;
		}

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Hook-Handler for BS hook BsAdapterAjaxPingResult
	 * @param string $sRef
	 * @param array $aData
	 * @param integer $iArticleId
	 * @param array $aSingleResult
	 * @return boolean
	 */
	public function onBsAdapterAjaxPingResult( $sRef, $aData, $iArticleId, $sTitle, $iNamespace, $iRevision, &$aSingleResult ) {
		if ( $sRef !== 'ArticleInfo' || empty( $iArticleId ) || empty( $iRevision ) ) return true;

		$oTitle = Title::newFromID( $iArticleId );
		if ( is_null( $oTitle ) || !$oTitle->userCan( 'read' ) ) return true;

		$aSingleResult['success'] = true;
		$oUser = $this->getUser();

		if ( $aData[0] == 'checkRevision' ) {
			$aSingleResult['newRevision'] = false;
			$oRevision = Revision::newFromTitle( $oTitle );
			if ( $oRevision->getId() > $iRevision
				&& !( //do not show own made revision when saving is in progress
					$aData[1] == 'edit' && $oUser->getID() > 0 && $oRevision->getUser() === $oUser->getID()
				)
			) {
				$aSingleResult['newRevision'] = true;
				$oCheckRevisionView = new ViewStateBarTopElement();
				$aSingleResult['checkRevisionView'] = $oCheckRevisionView
					->setKey( 'CheckRevision' )
					->setIconSrc( $this->getImagePath( true ).BsConfig::get( 'MW::ArticleInfo::ImageCheckRevision' ) )
					->setIconAlt( wfMessage( 'bs-articleinfo-check-revision' )->plain() )
					->setText( wfMessage( 'bs-articleinfo-check-revision' )->plain() )
					->setTextLink( $oTitle->getFullURL() )
					->setTextLinkTitle( wfMessage( 'bs-articleinfo-check-revision-tooltip' )->plain() )
					->execute()
				;
			}
		}
		return true;
	}

	/**
	 * Hook-Handler for BlueSpice hook SkinTemplateOutputPageBeforeExec - Remove footer links
	 * @param SkinTemplate $sktemplate
	 * @param Template $tpl
	 * @return boolean - always true
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		$aRMLinks = array(
			'info' => array(
				'lastmod',
				//postponed
				//HINT: http://84.16.252.165/otrs24/index.pl?Action=AgentTicketZoom;TicketID=3980;ArticleID=22500#22173
				//'viewcount',
			),
		);

		foreach($aRMLinks as $sKey => $aLinks) {
			if( !isset($tpl->data['footerlinks'][$sKey]) ) continue;
			foreach( $tpl->data['footerlinks'][$sKey] as $sLnkKey => $sLink ) {
				if( !in_array($sLink, $aLinks) ) continue;
				unset($tpl->data['footerlinks'][$sKey][$sLnkKey]);
			}
		}

		return true;
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return false|\ViewStateBarTopElement
	 */
	private function makeStateBarTopLastEdited( $oTitle ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$oLastEditView = new ViewStateBarTopElement();
		$oArticle = Article::newFromID( $oTitle->getArticleID() );
		$iOldId = $this->getRequest()->getInt( 'oldid', 0 );

		if ( $oArticle instanceof Article == false ) {
			return false;
		}

		if ( $iOldId != 0 ) {
			$sTimestamp = Revision::getTimestampFromId( $oArticle->getTitle(), $iOldId );
		} else {
			$sTimestamp = $oArticle->getTimestamp();
		}

		$sFormattedTimestamp = BsFormatConverter::mwTimestampToAgeString( $sTimestamp, true );
		$sArticleHistoryPageLink = $oArticle->getTitle()->getLinkURL(
			array(
				'diff'   => 0,
				'oldid' => $iOldId
			)
		);

		$oLastEditView->setKey( 'LastEdited' );
		$oLastEditView->setIconSrc( $this->getImagePath( true ).BsConfig::get( 'MW::ArticleInfo::ImageLastEdited' ) );
		$oLastEditView->setIconAlt( wfMessage( 'bs-articleinfo-last-edited' )->plain() );
		$oLastEditView->setText( $sFormattedTimestamp );
		$oLastEditView->setTextLink( $sArticleHistoryPageLink );
		$oLastEditView->setTextLinkTitle( wfMessage( 'bs-articleinfo-last-edited-tooltip' )->plain() );
		$oLastEditView->setDataAttribute( 'timestamp', wfTimestamp( TS_UNIX, $sTimestamp ) );

		Hooks::run( 'BSArticleInfoBeforeAddLastEditView', array( $this, &$oLastEditView ) );

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oLastEditView;
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return false|\ViewStateBarTopElement
	 */
	private function makeStateBarTopLastEditor( $oTitle ) {
		$oCurrentArticle = new Article($oTitle);
		if( !is_object( $oCurrentArticle ) || $oCurrentArticle->getUserText() == '' ) return false; //If a not existing article is viewed

		$oLastEditorView = new ViewStateBarTopElement();

		$oLastEditor = User::newFromName( $oCurrentArticle->getUserText() );
		if( is_object( $oLastEditor ) === false || $oLastEditor === null ) return false;

		wfProfileIn( 'BS::'.__METHOD__ );
		$sLastEditorName = $this->mCore->getUserDisplayName( $oLastEditor );
		$sLastEditorUserPageUrl = $oLastEditor->getUserPage()->getFullURL();

		$oLastEditorView->setKey( 'LastEditor' );
		$oLastEditorView->setIconSrc( $this->getImagePath( true ).BsConfig::get('MW::ArticleInfo::ImageLastEditor') );
		$oLastEditorView->setIconAlt( wfMessage('bs-articleinfo-last-editor')->text(), $this->getUser()->getName() );
		$oLastEditorView->setText( $sLastEditorName );
		$oLastEditorView->setTextLinkTitle( $sLastEditorName );
		$oLastEditorView->setTextLink( $sLastEditorUserPageUrl );

		Hooks::run( 'BSArticleInfoBeforeAddTopElement', array( $this, &$oLastEditorView ) );

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oLastEditorView;
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return false|\ViewStateBarTopElementCategoryShortList
	 */
	private function makeStateBarTopCategories( $oTitle ) {
		$aCurrentPagesCategories = $this->getOutput()->getCategoryLinks();
		if( empty( $aCurrentPagesCategories ) ) return false;

		wfProfileIn( 'BS::'.__METHOD__ );
		$oCategoriesLinks = new ViewStateBarTopElementCategoryShortList();
		$bIsProcessed = false;

		Hooks::run( 'BSArticleInfoBeforeAddLastEditorView', array( $this, &$aCurrentPagesCategories , &$bIsProcessed ) );

		if( $bIsProcessed === false ){
			ksort( $aCurrentPagesCategories );
		}

		$aAllCategoriesWithUrls = array();
		$iLoopCount = 0;
		foreach ( $aCurrentPagesCategories['normal'] as $iKey => $sValue ) {
			if( $iLoopCount < 3 ) $oCategoriesLinks->addCategory( $sValue ); //Only three for the topelement
			$aAllCategoriesWithUrls[] = $sValue; //But all for the body element
			$iLoopCount++;
		}

		if ( $this->getUser()->getBoolOption( 'showhiddencats' ) ) {
			foreach ( $aCurrentPagesCategories['hidden'] as $iKey => $sValue ) {
				if( $iLoopCount < 3 ) $oCategoriesLinks->addCategory( $sValue );
				$aAllCategoriesWithUrls[] = $sValue; //But all for the body element
				$iLoopCount++;
			}
		}

		if ( count( $aAllCategoriesWithUrls ) > 3 ) {
			$oCategoriesLinks->setMoreCategoriesAvailable( true );
		}

		if ( count( $aAllCategoriesWithUrls ) > 0 ) {
			$oCategoriesLinks->setKey( 'Categories' );
			$oCategoriesLinks->setIconSrc( $this->getImagePath( true ).BsConfig::get('MW::ArticleInfo::ImageCategories') );
			$oCategoriesLinks->setIconAlt( wfMessage( 'bs-articleinfo-categories' )->plain() );
		}

		Hooks::run('BSArticleInfoBeforeAddCategoryView', array( $this, &$oCategoriesLinks ));

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oCategoriesLinks;
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return false|\ViewStateBarBodyElement
	 */
	private function makeStateBarBodyCategories( $oTitle ) {
		$aCurrentPagesCategories = $oTitle->getParentCategories();
		if ( empty( $aCurrentPagesCategories ) ) return false;

		wfProfileIn( 'BS::'.__METHOD__ );
		$bIsProcessed = false;

		Hooks::run( 'BSArticleInfoBeforeAddLastEditorView', array( $this, &$aCurrentPagesCategories , &$bIsProcessed ) );

		if ( $bIsProcessed === false ){
			ksort( $aCurrentPagesCategories );
		}

		$oDbr = wfGetDB( DB_REPLICA );
		$res = $oDbr->select(
				array( 'page_props' ),
				array( 'pp_page' ),
				array( 'pp_propname' => 'hiddencat' )
		);

		$aHiddenPageIDs = array();
		while ( $row = $oDbr->fetchObject( $res ) ) {
			$aHiddenPageIDs[] = $row->pp_page;
		}

		$sCategories = '';
		$sAllCategoriesWithUrls = '';
		$sAllCategoriesWithUrls = '';

		foreach( $aCurrentPagesCategories as $sCat => $sName ) {
			$oCat = Title::newFromText( $sCat );

			if ( in_array( $oCat->getArticleID(), $aHiddenPageIDs ) ) {
				$sAllCategoriesWithUrls .= '<li>' . BsLinkProvider::makeLink( $oCat, $oCat->getText() ) . '</li>';
				continue;
			}

			$sAllCategoriesWithUrls .= '<li>' . BsLinkProvider::makeLink( $oCat, $oCat->getText() ) . '</li>'; //But all for the body element
		}

		if ( !empty( $sAllCategoriesWithUrls ) ) {
			$sCategories = '<ul>' . $sAllCategoriesWithUrls . '</ul>';
		}

		if ( $this->getUser()->getBoolOption( 'showhiddencats' ) ) {
			if ( !empty( $sAllCategoriesWithUrls ) ) {
				$sCategories .= '<h4>' . wfMessage( 'bs-articleinfo-hiddencats' )->plain() . '</h4>'.
								'<ul>' . $sAllCategoriesWithUrls . '</ul>';
			}
		}

		if ( empty( $sCategories ) ) {
			$sCategories = wfMessage( 'bs-articleinfo-nocategories' )->plain();
		}

		$oCategoriesLinkBodyElement = new ViewStateBarBodyElement();
		$oCategoriesLinkBodyElement->setKey( 'AllCategories' );
		$oCategoriesLinkBodyElement->setHeading( wfMessage( 'bs-articleinfo-all-categories-heading' )->plain() );
		$oCategoriesLinkBodyElement->setBodyText( $sCategories );

		Hooks::run( 'BSArticleInfoBeforeAddCategoryBodyView', array( $this, &$oCategoriesLinkBodyElement ) );

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oCategoriesLinkBodyElement;
	}

	/**
	 * Generates list of templates
	 * @return string list of edits
	 */
	private function makeStateBarBodyTemplates( $oTitle ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$aTemplatesTitles = $oTitle->getTemplateLinksFrom();

		$sTemplates = '';
		foreach ( $aTemplatesTitles as $oTitle ) {
			$sTemplates .= '<li>' . Linker::link( $oTitle, $oTitle->getText() ) . '</li>';
		}

		if ( empty( $sTemplates ) ) {
			$sTemplates = wfMessage( 'bs-articleinfo-notemplates' );
		} else {
			$sTemplates = '<ul>' . $sTemplates . '</ul>';
		}

		$oTemplatesView = new ViewStateBarBodyElement();
		$oTemplatesView->setKey( 'Templates' );
		$oTemplatesView->setHeading( wfMessage( 'bs-articleinfo-templates' )->plain() );
		$oTemplatesView->setBodyText( $sTemplates );

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oTemplatesView;
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return false|\ViewStateBarTopElement
	 */
	private function makeStateBarTopSubPages( $oTitle ) {
		if( $oTitle->hasSubpages() == false ) return false;

		wfProfileIn( 'BS::'.__METHOD__ );
		$oSubpageIcons = new ViewStateBarTopElement();
		$oSubpageIcons->setKey( 'Subpages' );
		$oSubpageIcons->setIconSrc( $this->getImagePath( true ).BsConfig::get('MW::ArticleInfo::ImageSubpages') );
		$oSubpageIcons->setIconAlt( wfMessage( 'bs-articleinfo-subpages-available' )->plain() );
		$oSubpageIcons->setIconTogglesBody( true );
		$oSubpageIcons->setText( wfMessage( 'bs-articleinfo-subpages' )->plain() );
		$oSubpageIcons->setTextLinkTitle( wfMessage( 'bs-articleinfo-subpages' )->plain() );
		$oSubpageIcons->setTextLink( '#' );

		Hooks::run('BSArticleInfoBeforeSubpagesTopView', array( $this, &$oSubpageIcons ));

		return $oSubpageIcons;
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return false|\ViewStateBarBodyElement
	 */
	private function makeStateBarBodySubPages( $oTitle ) {
		if ( $oTitle->hasSubpages() == false ) return false;

		wfProfileIn( 'BS::'.__METHOD__ );

		$oSubpageListView = new ViewStateBarBodyElement();
		$oSubpageListView->setKey( 'Subpages' );
		$oSubpageListView->setHeading( wfMessage( 'bs-articleinfo-subpages' )->plain() );

		$aSubpages = BsArticleHelper::getSubpagesSortedForTitle( $oTitle );

		if ( count( $aSubpages ) > 100 ) {
			$oSubpageListView->setBodyText( wfMessage( 'bs-articleinfo-subpages-too-much' )->plain() );
		} else {
			// TODO RBV (22.02.12 10:22): Less inline CSS, more use of classes
			$oList = new ViewBaseElement();
			$oList->setAutoWrap( '<ul style="margin:0">###CONTENT###</ul>' );
			$oList->setTemplate( '<li style="{STYLE}">{LINK}</li>' );

			foreach ( $aSubpages as $oTitle ) {
				$sLink = Linker::link( $oTitle, $oTitle->getSubpageText() );
				$sStyle = 'margin-left:'.( count( explode( '/', $oTitle->getText() ) ) - 1 ).'em';
				$oList->addData( array( 'LINK' => $sLink, 'STYLE' => $sStyle ) );
			}

			$oSubpageListView->setBodyText( $oList->execute() );
		}

		Hooks::run( 'BSArticleInfoBeforeSubpagesBodyView', array( $this, &$oSubpageListView ) );

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oSubpageListView;
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return false|\ViewStateBarTopElement
	 */
	private function makeStateBarTopArticleViews( $oTitle ) {
		//postponed
		//HINT: http://84.16.252.165/otrs24/index.pl?Action=AgentTicketZoom;TicketID=3980;ArticleID=22500#22173
		return false;
		$oArticleViewsView = new ViewStateBarTopElement();
		$oArticle = Article::newFromID( $oTitle->getArticleID() );

		if( $oArticle instanceof Article == false ) {
			return false;
		}
		wfProfileIn( 'BS::'.__METHOD__ );

		$iArticleViews = $oArticle->getCount();

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oArticleViewsView;
	}

	/**
	 * Hook-Handler for Mediawiki hook ArticleDeleteComplete
	 * @param Article $article
	 * @param User $user
	 * @param Content $content
	 * @param string $summary
	 * @param integer $minoredit
	 * @param type $watchthis
	 * @param type $sectionanchor
	 * @param integer $flags
	 * @param Revision $revision
	 * @param Status $status
	 * @param integer $baseRevId
	 * @return boolean - always true
	 */
	public function onPageContentSaveComplete( &$article, &$user, $content, $summary, $minoredit, $watchthis, $sectionanchor, &$flags, $revision, &$status, $baseRevId ) {
		if( $status->value['new'] === false ) return true;

		$oTitle = $article->getTitle();
		if( !$oTitle->isSubpage() ) return true;

		$sParentTitle = substr( $oTitle->getPrefixedText(), 0, strrpos($oTitle->getPrefixedText(), '/') );
		$oParentTitle = Title::newFromText( $sParentTitle );

		if( is_null($oParentTitle) || !$oParentTitle->exists() ) return true;

		$oParentTitle->invalidateCache();
		return true;
	}

	/**
	 * Hook-Handler for Mediawiki hook ArticleDeleteComplete
	 * @param Article $article
	 * @param User $user
	 * @param string $reason
	 * @param integer $id
	 * @return boolean
	 */
	public function onArticleDeleteComplete( &$article, &$user, $reason, $id ) {
		$oTitle = $article->getTitle();
		if( !$oTitle->isSubpage() ) return true;

		$sParentTitle = substr( $oTitle->getPrefixedText(), 0, strrpos($oTitle->getPrefixedText(), '/') );
		$oParentTitle = Title::newFromText( $sParentTitle );

		if( is_null($oParentTitle) || !$oParentTitle->exists() ) return true;

		$oParentTitle->invalidateCache();
		return true;
	}
}
