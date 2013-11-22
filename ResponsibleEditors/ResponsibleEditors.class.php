<?php

/**
 * ResponsibleEditors extension for BlueSpice
 *
 * Enables MediaWiki to manage responsible editors for articles.
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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @version    2.22.0 stable
 * @package    BlueSpice_Extensions
 * @subpackage ResponsibleEditors
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
// Last review MRG (01.07.11 11:05)

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * - More user notifications
 * - Elevated permissions for responsible editors
 * v1.0.0
 * - version number reset
 * v1.9.1
 * - Added new assignment dialog as SpecialPage to avoid ExtJS loading in view mode.
 * v1.9.0
 * - Raised to stable
 * v1.0.0b
 * - Initial release
 * - Port from HalloWiki Sunrise 1.9
 */

class ResponsibleEditors extends BsExtensionMW {
	protected static $aResponsibleEditorIdsByArticleId = array();
	protected static $aResponsibleEditorsByArticleId = array();

	public function __construct() {
		wfProfileIn('BS::' . __METHOD__);
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME => 'ResponsibleEditors',
			EXTINFO::DESCRIPTION => 'Enables MediaWiki to manage responsible editors for articles.',
			EXTINFO::AUTHOR => 'Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array(
				'bluespice' => '2.22.0',
				'StateBar' => '2.22.0',
				'Authors' => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::ResponsibleEditors';

		wfProfileOut('BS::' . __METHOD__);
	}

	protected function initExt() {
		wfProfileIn('BS::' . __METHOD__);
		BsConfig::registerVar( 'MW::ResponsibleEditors::EChange', true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-EChange', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::EDelete', true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-EDelete', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::EMove',   true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-EMove', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::ActivatedNamespaces', array(0), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-responsibleeditors-pref-ActivatedNamespaces', 'multiselectex' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::AutoAssignOnArticleCreation', false, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-AutoAssignOnArticleCreation', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::ResponsibleEditorMayChangeAssignment', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-ResponsibleEditorMayChangeAssignment', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::ImageResponsibleEditorStatebarIcon', 'bs-infobar-responsibleeditor.png', BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_STRING, 'bs-responsibleeditors-pref-ImageResponsibleEditorStatebarIcon' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::EMailNotificationOnResponsibilityChange', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-EMailNotificationOnResponsibilityChange', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::AddArticleToREWatchLists', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-AddArticleToREWatchLists', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::AutoPermissions', array('read', 'edit'), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-responsibleeditors-pref-AutoPermissions', 'multiselectex' );

		//Hooks
		$this->setHook( 'BeforeInitialize' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'SkinTemplateNavigation::Universal', 'onSkinTemplateNavigationUniversal' );
		$this->setHook( 'SkinTemplateContentActions' );
		$this->setHook( 'ArticleInsertComplete' );
		$this->setHook( 'SpecialMovepageAfterMove' );
		$this->setHook( 'ArticleDeleteComplete' );
		$this->setHook( 'ArticleSaveComplete' );
		$this->setHook( 'TitleMoveComplete' );
		$this->setHook( 'LoadExtensionSchemaUpdates' );
		
		$this->setHook( 'BSBookshelfManagerGetBookDataRow' );
		$this->setHook( 'BSUEModulePDFcollectMetaData' );
		$this->setHook( 'BSStateBarAddSortTopVars', 'onStatebarAddSortTopVars' );
		$this->setHook( 'BSStateBarAddSortBodyVars', 'onStatebarAddSortBodyVars' );
		$this->setHook( 'BSStateBarBeforeTopViewAdd', 'onStateBarBeforeTopViewAdd' );
		$this->setHook( 'BSStateBarBeforeBodyViewAdd', 'onStateBarBeforeBodyViewAdd' );
		$this->setHook( 'BSPageAccessAddAdditionalAccessGroups', 'onPageAccessAddAdditionalAccessGroups' );
		$this->setHook( 'BSDashboardsUserDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsUserDashboardPortalPortlets' );
		
		// Echo extension hooks
		$this->setHook( 'BeforeCreateEchoEvent' );
		$this->setHook( 'EchoGetDefaultNotifiedUsers' );

		$this->mCore->registerPermission( 'responsibleeditors-changeresponsibility' );
		$this->mCore->registerPermission( 'responsibleeditors-viewspecialpage' );
		$this->mCore->registerPermission( 'responsibleeditors-takeresponsibility', array('user') );
		wfProfileOut('BS::' . __METHOD__);
	}

	
	/**
	 * Adds the 'ext.bluespice.responsibleeditors' module to the OutputPage
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public function onBeforePageDisplay( $out, $skin ) {
		if ( $out->getRequest()->getVal( 'action', 'view') == 'view' && $out->getTitle()->isContentPage() ) {
			$out->addModules( 'ext.bluespice.responsibleEditors' );
			$out->addModuleStyles( 'ext.bluespice.responsibleEditors.styles' );

			//Make information about current pages RespEds available on client side
			$iArticleId = $out->getTitle()->getArticleID();
			$aResponsibleEditorIds = $this->getResponsibleEditorIdsByArticleId( $iArticleId );
			$oData = new stdClass();
			$oData->articleId = $iArticleId;
			$oData->editorIds = $aResponsibleEditorIds;

			$out->addJsConfigVars( 'bsResponsibleEditors', $oData );
		}
		//Attach Bookshelfs plugin if in context
		if ( SpecialPage::getTitleFor( 'BookshelfBookManager' )->equals( $out->getTitle() ) ) {
			$out->addModules( 'ext.bluespice.responsibleEditors.bookshelfPlugin' );
		}
		return true;
	}

	/**
	 * Add temporary group to page access extension - Resposible editors are always alowed
	 * @param array $aAccessGroups
	 * @return boolean alway true
	 */
	public function onPageAccessAddAdditionalAccessGroups( &$aAccessGroups ) {
		$aAccessGroups[] = 'tmprespeditors';
		return true;
	}

	/**
	 * This method gets called by the MediaWiki Framework
	 * @param DatabaseUpdater $updater Provided by MediaWikis update.php
	 * @return boolean Always true to keep the hook running
	 */
	public function onLoadExtensionSchemaUpdates( $updater ) {
		global $wgDBtype;
		switch( $wgDBtype ) {
			case 'postgres': $updater->addExtensionTable( 
					'bs_responsible_editors', 
					__DIR__.'/db/ResponsibleEditors.pg.sql'
				);
				break;
			case 'oracle': $updater->addExtensionTable( 
					'bs_responsible_editors', 
					__DIR__.'/db/ResponsibleEditors.oci.sql'
				);
				break;
			default: $updater->addExtensionTable( 
					'bs_responsible_editors', 
					__DIR__.'/db/ResponsibleEditors.sql'
				);
		}
		
		return true;
	}

	/**
	 * Hook-Handler for MediaWiki hook BeforeInitialize
	 * @global array $wgGroupPermissions
	 * @global User $wgUser
	 * @param Title $title
	 * @param Article $article
	 * @param Output $output
	 * @param User $user
	 * @param Request $request
	 * @param MediaWiki $mediaWiki
	 * @return boolean Always true
	 */
	public function onBeforeInitialize( &$title, $article, &$output, &$user, $request, $mediaWiki ) {
		if( !$title->exists() ) return true;

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');
		if( !is_array( $aActivatedNamespaces ) ) return true;
		if( !in_array($title->getNamespace(), $aActivatedNamespaces) ) return true;
		
		global $wgUser;
		
		$iArticleID = $title->getArticleID();
		$aResponsibleEditorsIDs = $this->getResponsibleEditorIdsByArticleId( $iArticleID );
		if( !in_array($wgUser->getId(), $aResponsibleEditorsIDs) ) return true;
		
		$aAvailablePermissions = BsConfig::get('MW::ResponsibleEditors::AutoPermissions');
		if( empty($aAvailablePermissions) ) return true;
		
		$this->mCore->addTemporaryGroupToUser( $wgUser, 'tmprespeditors', $aAvailablePermissions );
		return true;
	}
	
	public function onBSUEModulePDFcollectMetaData($oTitle, $oPageDOM, &$aParams, $oDOMXPath, &$aMeta) {
		$aEditors = $this->getResponsibleEditorIdsByArticleId($oTitle->getArticleId());
		$aEditorNames = array();
		foreach ($aEditors as $iEditorId) {
			$aEditorNames[] = $this->mCore->getUserDisplayName(User::newFromId($iEditorId));
		}
		$aMeta['responsibleeditors'] = implode(', ', $aEditorNames);
		return true;
	}

	public function onBSBookshelfManagerGetBookDataRow($oBookTitle, $oBookRow) {
		$oBookRow->editors = array();
		$aEditors = $this->getResponsibleEditorIdsByArticleId($oBookRow->page_id);
		foreach ($aEditors as $iEditorId) {
			$oBookRow->editors[] = array(
				'id' => $iEditorId,
				'name' => $this->mCore->getUserDisplayName(User::newFromId($iEditorId))
			);
		}
		return true;
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin($sAdapterName, $oVariable) {
		switch($oVariable->getName()) {
			case 'ActivatedNamespaces': 
				$aPrefs = array(
					'type' => 'multiselectex',
					'options' => BsNamespaceHelper::getNamespacesForSelectOptions(array(-2, -1)),
				);
				break;
			case 'AutoPermissions':
				global $wgGroupPermissions;

				$aAvailablePermissions = array();
				foreach($wgGroupPermissions as $sGroup => $aPermissions) {
					foreach($aPermissions as $sName => $bValue) {
						if (!in_array($sName, WikiAdmin::get('ExcludeRights'))) {
							$aAvailablePermissions[$sName] = $sName;
						}
					}
				}
				natsort($aAvailablePermissions);
				
				$aPrefs = array(
					'type' => 'multiselectex',
					'options' => array_unique($aAvailablePermissions),
				);
				break;
		}
		return $aPrefs;
	}

		/**
	 * Hook Handler for BSDashboardsUserDashboardPortalPortlets
	 * 
	 * @param array &$aPortlets reference to array portlets
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = array(
			'type'  => 'BS.ResponsibleEditors.ResponsibleEditorsPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-responsibleeditors-yourresponsibilities' )->plain()
			),
			'title' => wfMessage( 'bs-responsibleeditors-yourresponsibilities' )->plain(),
			'description' => wfMessage( 'bs-responsibleeditors-yourresponsibilitiesdesc' )->plain()
		);

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsUserDashboardPortalConfig
	 * 
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param boolean $bIsDefault default
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = array(
			'type' => 'BS.ResponsibleEditors.ResponsibleEditorsPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-responsibleeditors-yourresponsibilities' )->plain()
			)
		);

		return true;
	}

	/**
	 * MediaWiki ContentActions hook. For more information please refer to <mediawiki>/docs/hooks.txt
	 * @param Array $aContentActions This array is used within the skin to render the content actions menu
	 * @return Boolean Always true for it is a MediwWiki Hook callback.
	 */
	public function onSkinTemplateContentActions( &$aContentActions) {

		$links = array( 'actions' => array() );
		$this->onSkinTemplateNavigationUniversal( null, $links );
		$aContentActions['respeditors'] = $links['actions']['respeditors'];

		return true;
	}
	
	/**
	 * MediaWiki ContentActions hook. For more information please refer to <mediawiki>/docs/hooks.txt
	 * @param SkinTemplate $oSkinTemplate
	 * @param array $links This array is used within the skin to render the content actions menu
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateNavigationUniversal($oSkinTemplate, &$links) {
		//Check if menu entry has to be displayed
		$oCurrentUser = $this->getUser();
		if ($oCurrentUser->isLoggedIn() === false)
			return true;

		$oCurrentTitle = $this->getTitle();
		if ($oCurrentTitle->exists() === false)
			return true;
		if ($oCurrentTitle->getNamespace() === NS_SPECIAL)
			return true;

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');
		if (is_array($aActivatedNamespaces)) {
			if (!in_array($oCurrentTitle->getNamespace(), $aActivatedNamespaces))
				return true;
		}
		else {
			if ($oCurrentTitle->getNamespace() == $aActivatedNamespaces)
				return true;
		}
		if ($this->userIsAllowedToChangeResponsibility($oCurrentUser, $oCurrentTitle) === false)
			return true;

		$links['actions']['respeditors'] = array(
			'text'  => wfMsg( 'bs-responsibleeditors-contentactions-label' ),
			'href'  => '#',
			'class' => false
		);
		
		return true;
	}

	/**
	 * Encapsulated permission check.
	 * @param User $oCurrentUser The requested MediaWiki User.
	 * @param Title $oCurrentTitle The MediaWiki Title to check against.
	 * @return boolean Wether the user is allowed to change responsibility or not.
	 */
	public function userIsAllowedToChangeResponsibility($oCurrentUser, $oCurrentTitle) {
		//Check users permissions and/or if he is assigned as a responsible editor
		$bUserIsAllowedToChangeResponsiblity = false;
		$aResponsibleEditorIds = $this->getResponsibleEditorIdsByArticleId($oCurrentTitle->getArticleId());
		if ($oCurrentTitle->userCan('responsibleeditors-changeresponsibility') === true) {
			$bUserIsAllowedToChangeResponsiblity = true;
		} else {
			if (BsConfig::get('MW::ResponsibleEditors::ResponsibleEditorMayChangeAssignment')
					&& in_array($oCurrentUser->getId(), $aResponsibleEditorIds)) {
				$bUserIsAllowedToChangeResponsiblity = true;
			}
		}
		return $bUserIsAllowedToChangeResponsiblity;
	}
	
	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortTopVars'
	 * @param array $aSortTopVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortTopVars( &$aSortTopVars ) {
		$aSortTopVars['statebartopresponsibleeditorsentries'] = wfMsg( 'bs-responsibleeditors-statebartopresponsibleeditorsentries' );
		return true;
	}
	
	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 * @param array $aSortBodyVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortBodyVars( &$aSortBodyVars ) {
		$aSortBodyVars['statebarbodyresponsibleeditorsentries'] = wfMsg( 'bs-responsibleeditors-statebarbodyresponsibleeditorsentries' );
		return true;
	}
	
	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeTopViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aTopViews
	 * @return boolean Always true to keep hook running 
	 */
	public function onStateBarBeforeTopViewAdd( $oStateBar, &$aTopViews, $oUser, $oTitle ) {
		if (!in_array($oTitle->getNamespace(), BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces')))
			return true;
		if ($oTitle->exists() == false)
			return true;
		$oResponsibleEditorsView = $this->makeStateBarTopResponsibleEditorsEntries($oTitle->getArticleID());
		if( !$oResponsibleEditorsView ) return true;

		$aTopViews['statebartopresponsibleeditorsentries'] = $oResponsibleEditorsView;
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeBodyViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aBodyViews
	 * @return boolean Always true to keep hook running
	 */
	public function onStateBarBeforeBodyViewAdd( $oStateBar, &$aBodyViews, $oUser, $oTitle ) {
		if (!in_array($oTitle->getNamespace(), BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces')))
			return true;
		if ($oTitle->exists() == false)
			return true;
		$oResponsibleEditorsView = $this->makeStateBarBodyResponsibleEditorsEntries($oTitle->getArticleID());
		if( !$oResponsibleEditorsView ) return true;

		$aBodyViews['statebarbodyresponsibleeditorsentries'] = $oResponsibleEditorsView;
		return true;
	}

	/**
	 * Hook handler for MediaWiki 'SpecialMovepageAfterMove' hook. Adapts responsibilities in case of article move.
	 * @param Title $oOldTitle
	 * @param Title $oNewTitle
	 * @return bool Always true to keep other hooks running.
	 */
	public function onSpecialMovepageAfterMove($oOldTitle, $oNewTitle) {
		// at least if no redirect is produced, $oOldTitle is of a type like MovePageForm or so
		if ($oOldTitle instanceof Title) {
			$iOldId = $oOldTitle->getArticleID();
		} else {
			$iOldId = 0;
		}

		// TODO RBV (02.07.11 12:31): This works... but why? Examine at some time.
		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->update(
						'bs_responsible_editors',
						array('re_page_id' => $oNewTitle->getArticleID()),
						array('re_page_id' => $iOldId)
		);

		return true;
	}

	/**
	 * Hook handler for MediaWiki 'ArticleDeleteComplete' hook. Removes responsibilities in case of article deletion.
	 * @param Article $oArticle
	 * @param User $oUser
	 * @param string $sReason
	 * @param int $iArticleId
	 * @return bool Always true to keep other hooks running.
	 */
	public function onArticleDeleteComplete($oArticle, $oUser, $sReason, $iArticleId) {
		//E-Mail notifcation
		$aResponsibleEditorIds = $this->getResponsibleEditorIdsByArticleId($iArticleId);
		self::notifyResponsibleEditors($aResponsibleEditorIds, $oUser, array($oArticle->getTitle()), 'delete');

		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->delete(
						'bs_responsible_editors',
						array('re_page_id' => $iArticleId)
		);

		return true;
	}

	public function onArticleSaveComplete(&$article, &$user, $text, $summary, $minoredit, $watchthis, $sectionanchor, &$flags, $revision, &$status, $baseRevId) {
		$iArticleId = $article->getID();
		$aResponsibleEditorIds = $this->getResponsibleEditorIdsByArticleId($iArticleId);
		self::notifyResponsibleEditors($aResponsibleEditorIds, $user, array(Title::newFromID($iArticleId)), 'change');
		return true;
	}

	public function onTitleMoveComplete(&$title, &$newtitle, &$user, $oldid, $newid) {
		$aResponsibleEditorIds = $this->getResponsibleEditorIdsByArticleId($oldid);
		self::notifyResponsibleEditors($aResponsibleEditorIds, $user, array($title, $newtitle), 'move');
		return true;
	}

	private function makeStateBarTopResponsibleEditorsEntries($iArticleId) {
		$aResponsibleEditorIds = $this->getResponsibleEditorIdsByArticleId($iArticleId);
		if (empty($aResponsibleEditorIds))
			return false;

		$oResponsibleEditorsTopView = new ViewStateBarTopElement();

		$oFirstResponsibleEditor = User::newFromId($aResponsibleEditorIds[0]);
		$sDispalyName = $this->mCore->getUserDisplayName($oFirstResponsibleEditor);

		$oResponsibleEditorsTopView->setKey('ResponsibleEditors-Top');
		$oResponsibleEditorsTopView->setIconSrc( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/' . $this->mInfo[EXTINFO::NAME] . '/resources/images/' . BsConfig::get('MW::ResponsibleEditors::ImageResponsibleEditorStatebarIcon'));
		$oResponsibleEditorsTopView->setIconAlt(wfMsg( 'bs-responsibleeditors-statebartop-icon-alt' ));
		$oResponsibleEditorsTopView->setText($sDispalyName);
		$oResponsibleEditorsTopView->setTextLinkTitle($sDispalyName);
		$oResponsibleEditorsTopView->setTextLink($oFirstResponsibleEditor->getUserPage()->getFullURL());

		return $oResponsibleEditorsTopView;
	}
	
	private function makeStateBarBodyResponsibleEditorsEntries($iArticleId) {
		$aResponsibleEditorIds = $this->getResponsibleEditorIdsByArticleId($iArticleId);
		if (empty($aResponsibleEditorIds))
			return false;

		$oResponsibleEditorsBodyView = new ViewStateBarBodyElement();

		$sStateBarBodyHeadline = wfMsg( 'bs-responsibleeditors-statebarbody-headline-singular' );
		if (count($aResponsibleEditorIds) > 1) {
			$sStateBarBodyHeadline = wfMsg( 'bs-responsibleeditors-statebarbody-headline-plural' );
		}

		$aResponsibleEditorUserMiniProfiles = array();
		foreach ($aResponsibleEditorIds as $iResponsibleEditorId) {
			$aResponsibleEditorUserMiniProfiles[] =
					$this->mCore->getUserMiniProfile(
							User::newFromId($iResponsibleEditorId),
							array('width' => 48, 'height' => 48, 'classes' => array('left'))
					)->execute();
		}

		$oResponsibleEditorsBodyView->setKey('ResponsibleEditors-Body');
		$oResponsibleEditorsBodyView->setHeading($sStateBarBodyHeadline);
		$oResponsibleEditorsBodyView->setBodyText(
				implode('', $aResponsibleEditorUserMiniProfiles)
		);

		return $oResponsibleEditorsBodyView;
	}

	/**
	 * Hook handler for MediaWiki 'ArticleInsertComplete' hook. Occurs after a new article has been created.
	 * @param Article $oArticle
	 * @param User $oUser
	 * @param string $sText
	 * @param string $sSummary
	 * @param bool $bIsMinor
	 * @param bool $bIsWatch
	 * @param int $iSection
	 * @param int $iFlags
	 * @param Revision $oRevision
	 * @return bool Always true to keep other hooks running.
	 */
	public function onArticleInsertComplete($oArticle, $oUser, $sText, $sSummary, $bIsMinor, $bIsWatch, $iSection, $iFlags, $oRevision) {
		//Check requirements
		if (BsConfig::get('MW::ResponsibleEditors::AutoAssignOnArticleCreation') === false)
			return true;
		if ($oArticle->getTitle()->userCan('responsibleeditors-takeresponsibility') === false)
			return true;

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');
		if (in_array($oArticle->getTitle()->getNamespace(), $aActivatedNamespaces) === false)
			return true;

		$dbw = wfGetDB(DB_MASTER);
		$dbw->insert(
			'bs_responsible_editors',
			array(
				're_user_id' => $oUser->getId(),
				're_page_id' => $oArticle->getId(),
				're_position' => 0,
			)
		);

		return true;
	}

	//<editor-fold desc="AJAX Interfaces">
	public static function ajaxDeleteResponsibleEditorsForArticle() {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		global $wgRequest;
		$oResponse = new BsXHRJSONResponse();
		$oResponse->status = BsXHRResponseStatus::ERROR;

		$iArticleId = $wgRequest->getInt( 'articleId', -1 );
		$aUserIDs = $wgRequest->getArray( 'articleId', array() );

		$oRequestedTitle = Title::newFromId($iArticleId);

		if ($iArticleId === -1 || empty( $aUserIDs ) || $oRequestedTitle === null) {
			$oResponse->shortMessage = wfMessage( 'bs-responsibleeditors-error-ajax-invalid-parameter' )->plain();
			echo $oResponse;
			return;
		}

		//TODO: prevent delete on specific variations
		//$oCurrentUserResponsibleEditor = BsResponsibleEditor::newFromUser($oCurrentUser);
		if ($oRequestedTitle->userCan('responsibleeditors-changeresponsibility') === false
				//&& ( $oCurrentUserResponsibleEditor->isAssignedToArticleId($iArticleId) === false
				//&& BsConfig::get('MW::ResponsibleEditors::ResponsibleEditorMayChangeAssignment') === true
		) {
			$oResponse->shortMessage = wfMessage( 'bs-responsibleeditors-error-ajax-not-allowed' )->plain();
			echo $oResponse;
			return;
		}

		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->delete(
						'bs_responsible_editors',
						array(
							're_page_id' => $iArticleId,
							're_user_id' => $aUserIDs
						)
		);

		$oRequestedTitle->invalidateCache();

		$oResponse->status = BsXHRResponseStatus::SUCCESS;
		$oResponse->shortMessage = wfMessage( 'bs-responsibleeditors-success-ajax' )->plain();
		return $oResponse;
	}

	public static function ajaxGetActivatedNamespacesForCombobox() {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		$aNamespaces = array();
		$aNamespaces[] = array(
			'namespace_id' => -99,
			'namespace_text' => BsNamespaceHelper::getNamespaceName(-99, true)
		);

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');
		foreach ($aActivatedNamespaces as $iNamespaceId) {
			$aNamespaces[] = array(
				'namespace_id' => $iNamespaceId,
				'namespace_text' => BsNamespaceHelper::getNamespaceName($iNamespaceId, true)
			);
		}
		return '{ namespaces: ' . json_encode($aNamespaces) . ' }';
	}

	public static function ajaxGetResponsibleEditorsByArticleId($iArticleId) {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		$aResponsibleEditorIds = BsExtensionManager::getExtension( 'ResponsibleEditors' )->getResponsibleEditorIdsByArticleId($iArticleId);
		return json_encode($aResponsibleEditorIds);
	}

	public static function ajaxGetListOfResponsibleEditorsForArticle() {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		global $wgRequest;
		$iArticleId = $wgRequest->getInt( 'articleId', -1 );
		if ($iArticleId == -1)
			return 'ERROR';
		$aListOfPossibleEditors = BsExtensionManager::getExtension( 'ResponsibleEditors' )->getListOfResponsibleEditorsForArticle($iArticleId);
		return '{users: ' . json_encode($aListOfPossibleEditors) . '}';
	}

	public function getListOfResponsibleEditorsForArticle($iArticleId) {
		$oCurrentTitle = Title::newFromId($iArticleId);

		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->select(
			'user',
			array('user_id', 'user_real_name')
		);

		$aListOfPossibleEditors = array();

		foreach ($res as $row) {
			$oEditorUser = User::newFromId($row->user_id);
			$aPermissionErrors = $oCurrentTitle->getUserPermissionsErrors('responsibleeditors-takeresponsibility', $oEditorUser);
			if (empty($aPermissionErrors)) {
				$aListOfPossibleEditors[] =
					array(
						'user_id' => $oEditorUser->getId(),
						'user_displayname' => $this->mCore->getUserDisplayName($oEditorUser)
				);
			}
		}

		return $aListOfPossibleEditors;
	}

	public static function ajaxGetArticlesByNamespaceId() {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		global $wgOut, $wgRequest;
		$wgOut->disable();
		
		$oParams = BsExtJSStoreParams::newFromRequest();

		$iStart       = $wgRequest->getInt( 'start', 0 );
		$sSort        = $oParams->getSort( 'page_title' );
		$sDirection   = $oParams->getDirection( );
		$iLimit       = $wgRequest->getInt( 'limit', 25 );
		$sDisplayMode = $wgRequest->getVal( 'displayMode', 'only-assigned' );
		$iNamespaceId = $wgRequest->getInt( 'namespaceId', -99 );

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');

		$oResult = new stdClass();

		$aTables     = array( 'bs_responsible_editors', 'user', 'page' );
		$aVariables  = array( 'page_id', 'page_title', 'page_namespace' );
		$aConditions = array( 'page_namespace' => $aActivatedNamespaces );

		if ($sDisplayMode == 'only-assigned')
			$aConditions[] = 're_user_id IS NOT NULL ';
		else if ($sDisplayMode == 'only-not-assigned')
			$aConditions[] = 're_user_id IS NULL ';
		if ($iNamespaceId != -99)
			$aConditions['page_namespace'] = $iNamespaceId;

		$aOptions = array(
			'ORDER BY' => $sSort . ' ' . $sDirection,
			'LIMIT' => $iLimit,
			'OFFSET' => $iStart,
			'GROUP BY' => 'page_id'
		);
		if ($sSort == 'user_displayname') {
			$aOptions['ORDER BY'] = 'user_real_name, user_name ' . $sDirection;
		}
		$aJoinOptions = array(
			'user' => array('JOIN', 'user_id = re_user_id'),
			'page' => array('RIGHT JOIN', 'page_id = re_page_id')
		);

		$dbr = wfGetDB(DB_SLAVE);

		//TODO: Rework "total" calculation. This seems very complicated but it 
		//should be as easy as excuting the main query without LIMIT/OFFSET.
		if ($sDisplayMode == 'only-assigned' || $sDisplayMode == 'only-not-assigned') {
			$row = $dbr->select(
				array('page', 'bs_responsible_editors'), 
				'page_id AS cnt', 
				$aConditions, 
				__METHOD__, 
				array('GROUP BY' => 'page_id'), 
				array('page' => array(
					'RIGHT JOIN', 'page_id = re_page_id'
				))
			);
			$oResult->total = $row->numRows();
		}
		if ($sDisplayMode == 'all') {
			$aConditionsWithoutRePageID = $aConditions;
			unset($aConditionsWithoutRePageID[0]);
			$row = $dbr->selectRow(
				'page', 'COUNT( page_id ) AS cnt', $aConditionsWithoutRePageID
			);
			$oResult->total = $row->cnt;
		}

		$res = $dbr->select(
			$aTables,
			$aVariables,
			$aConditions, 
			__METHOD__,
			$aOptions,
			$aJoinOptions
		);

		$oResult->pages = array();
		foreach ($res as $row) {
			$oTitle = Title::newFromId($row->page_id);
			
			$iPageId = $row->page_id;
			$sPageNsId = (!empty($row->page_namespace) ) 
				? $row->page_namespace 
				: 0;
			$sPageTitle = $row->page_title;

			$oPage = new stdClass();
			$oPage->page_id = $iPageId;
			$oPage->page_namespace = $sPageNsId;
			$oPage->page_title = $sPageTitle;
			$oPage->page_prefixedtext = $oTitle->getPrefixedText();
			$oPage->users = array();

			$aEditorIDs = BsExtensionManager::getExtension( 'ResponsibleEditors' )
				->getResponsibleEditorIdsByArticleId($row->page_id);
			$aEditorIDs = array_unique($aEditorIDs);

			foreach ($aEditorIDs as $iEditorID) {
				$oUser = User::newFromId($iEditorID);
				if ($oUser == null) continue;
				
				$oPage->users[] = array(
					'user_id'            => $iEditorID,
					'user_page_link_url' => $oUser->getUserPage()->getFullUrl(),
					'user_displayname'   => BsCore::getUserDisplayName( $oUser )
				);
				
			}

			$oResult->pages[] = $oPage;
		}

		return FormatJson::encode( $oResult );
	}

	//</editor-fold>

	/**
	 * Helper function. Fetches database and returns array of user_id's of
	 * responsible editors of an article
	 * @param Integer $iArticleId The page_id of the article you want to retrieve the responsible editors for.
	 * @return Array user_ids of responsible editors for given article
	 */
	public function getResponsibleEditorIdsByArticleId( $iArticleId, $bForceReload = false ) {
		if( isset(self::$aResponsibleEditorIdsByArticleId[$iArticleId]) && $bForceReload === false ) 
			return self::$aResponsibleEditorIdsByArticleId[$iArticleId];

		$this->getResponsibleEditorsByArticleId( $iArticleId, $bForceReload );

		return self::$aResponsibleEditorIdsByArticleId[$iArticleId];
	}
	
	/**
	 * Helper function. Fetches database and returns array of responsible editors of an article
	 * @param Integer $iArticleId The page_id of the article you want to retrieve the responsible editors for.
	 * @return Array user_ids of responsible editors for given article
	 */
	public function getResponsibleEditorsByArticleId( $iArticleId, $bForceReload = false ) {
		if( isset(self::$aResponsibleEditorsByArticleId[$iArticleId]) && $bForceReload === false ) 
			return self::$aResponsibleEditorsByArticleId[$iArticleId];

		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->select(
			'bs_responsible_editors',
			'*',
			array('re_page_id' => $iArticleId),
			__METHOD__,
			array('ORDER BY' => 're_position')
		);

		$aResponsibleEditorIds = array();
		$aResponsibleEditors = array();
		foreach ($res as $row) {
			$aResponsibleEditorIds[] = $row->re_user_id;
			$aResponsibleEditors[] = $row;
		}
		
		self::$aResponsibleEditorIdsByArticleId[$iArticleId] = $aResponsibleEditorIds;
		self::$aResponsibleEditorsByArticleId[$iArticleId] = $aResponsibleEditors;

		return $aResponsibleEditors;
	}

	/**
	 *
	 * @param array $aResponsibleEditorIds
	 * @param User $oUser
	 * @param array $aTitles
	 * @param string $sAction
	 */
	static function notifyResponsibleEditors($aResponsibleEditorIds, $oUser, $aTitles, $sAction) {
		if (empty($aResponsibleEditorIds)) return true;

		$aResponsibleEditors = array();
		foreach ($aResponsibleEditorIds as $iUserId) {
			$oREUser = User::newFromId($iUserId);
			if( $iUserId == $oUser->getId() ) continue;
			if( BsConfig::getVarForUser("MW::ResponsibleEditors::E".ucfirst($sAction), $oREUser) === true ) {
				$aResponsibleEditors[] = $oREUser;
			}
		}

		if( empty( $aResponsibleEditors ) ) return true;
		
		$sUserName    = BsExtensionManager::getExtension( 'ResponsibleEditors' )->mCore->getUserDisplayName( $oUser );
		$sArticleName = $aTitles[0]->getText();
		$sArticleLink = $aTitles[0]->getFullURL();

		switch( $sAction ) {
			case 'change':
				$sSubject = wfMessage(
					'bs-responsibleeditors-mail-subject-re-article-changed',
					$sArticleName,
					$sUserName
				)->plain();
				$sMessage = wfMessage(
					'bs-responsibleeditors-mail-text-re-article-changed',
					$sArticleName,
					$sUserName,
					$sArticleLink
				)->plain();
				break;
			case 'delete':
				$sSubject = wfMessage(
					'bs-responsibleeditors-mail-subject-re-article-deleted',
					$sArticleName,
					$sUserName
				)->plain();
				$sMessage = wfMessage(
					'bs-responsibleeditors-mail-text-re-article-deleted',
					$sArticleName,
					$sUserName,
					$sArticleLink
				)->plain();
				break;
			case 'move':
				$sSubject = wfMessage(
					'bs-responsibleeditors-mail-subject-re-article-moved',
					$sArticleName,
					$sUserName
				)->plain();
				$sMessage = wfMessage(
					'bs-responsibleeditors-mail-text-re-article-moved',
					$sArticleName,
					$aTitles[1]->getPrefixedText(),
					$sUserName,
					$aTitles[1]->getFullURL()
				)->plain();
				break;
			default:
				wfDebugLog( 
					'BS::ResponsibleEditors::notifyResponsibleEditors', 
					'Action "'.$sAction.'" is unknown. No mails sent.'
				);
				return;
		}

		BsMailer::getInstance('MW')->send($aResponsibleEditors, $sSubject, $sMessage);
	}

	public static function getResponsibleEditorsPortletData( $iCount, $iUserId ) {
		$iCount = BsCore::sanitize( $iCount, 10, BsPARAMTYPE::INT );

		$oDbr = wfGetDB( DB_SLAVE );

		$res = $oDbr->select(
			'bs_responsible_editors',
			're_page_id',
			array( 're_user_id' => $iUserId )
		);

		$aResults = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			$aResults[] = Html::openElement( 'ul' );
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->re_page_id );
				if ( $oTitle->exists() ) {
					$aResults[] = Html::openElement( 'li' ) . BsLinkProvider::makeLink( $oTitle, $oTitle->getPrefixedText() ) . Html::closeElement( 'li' );
				}
			}
			$aResults[] = Html::closeElement( 'ul' );
		} else {
			$aResults[] = wfMessage( 'bs-responsibleeditors-no-own-responsibilities' )->escaped();
		}

		return implode( '', $aResults );
	}
	
	public function onBeforeCreateEchoEvent( &$notifications, &$notificationCategories ) {
		/* implement */
		return true;
	}
	
	public function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
		/* implement */
        return true;
}

}