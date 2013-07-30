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
 * @version    1.22.0 stable
 * @version    $Id: ResponsibleEditors.class.php 9781 2013-06-18 11:52:09Z swidmann $
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
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['ResponsibleEditors'] = dirname( __FILE__ ) . '/ResponsibleEditors.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME => 'ResponsibleEditors',
			EXTINFO::DESCRIPTION => 'Enables MediaWiki to manage responsible editors for articles.',
			EXTINFO::AUTHOR => 'Robert Vogel',
			EXTINFO::VERSION => '1.22.0 ($Rev: 9781 $)',
			EXTINFO::STATUS => 'stable',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array(
				'bluespice' => '1.22.0',
				'StateBar' => '1.22.0',
				'Authors' => '1.22.0'
			)
		);
		$this->mExtensionKey = 'MW::ResponsibleEditors';
		$this->registerExtensionSchemaUpdate('bs_responsible_editors', dirname(__FILE__) . DS . 'ResponsibleEditors.sql');

		wfProfileOut('BS::' . __METHOD__);
	}

	protected function initExt() {
		wfProfileIn('BS::' . __METHOD__);
		BsConfig::registerVar( 'MW::ResponsibleEditors::EChange', false, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-EChange', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::EDelete', false, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-EDelete', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::EMove',   false, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-EMove', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::ActivatedNamespaces', array(0), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-responsibleeditors-pref-ActivatedNamespaces', 'multiselectex' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::AutoAssignOnArticleCreation', false, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-AutoAssignOnArticleCreation', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::ResponsibleEditorMayChangeAssignment', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-ResponsibleEditorMayChangeAssignment', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::SpecialPageDefaultPageSize', 25, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_INT, 'bs-responsibleeditors-pref-SpecialPageDefaultPageSize', 'int' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::ImageResponsibleEditorStatebarIcon', 'bs-infobar-responsibleeditor.png', BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_STRING, 'bs-responsibleeditors-pref-ImageResponsibleEditorStatebarIcon' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::EMailNotificationOnResponsibilityChange', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-EMailNotificationOnResponsibilityChange', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::AddArticleToREWatchLists', false, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-AddArticleToREWatchLists', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::AutoPermissions', array('read'), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-responsibleeditors-pref-AutoPermissions', 'multiselectex' );

		//Hooks
		$this->setHook( 'BeforeInitialize' );
		$this->setHook( 'SkinTemplateNavigation::Universal', 'onSkinTemplateNavigationUniversal' );
		$this->setHook( 'SkinTemplateContentActions' );
		$this->setHook( 'ArticleInsertComplete' );
		$this->setHook( 'SpecialMovepageAfterMove' );
		$this->setHook( 'ArticleDeleteComplete' );
		$this->setHook( 'ArticleSaveComplete' );
		$this->setHook( 'TitleMoveComplete' );
		$this->setHook( 'BSBookshelfManagerGetBookDataRow' );
		$this->setHook( 'BSBookshelfBookManager' );
		$this->setHook( 'BSUEModulePDFcollectMetaData' );
		$this->setHook( 'BSStateBarAddSortTopVars', 'onStatebarAddSortTopVars' );
		$this->setHook( 'BSStateBarAddSortBodyVars', 'onStatebarAddSortBodyVars' );
		$this->setHook( 'BSStateBarBeforeTopViewAdd', 'onStateBarBeforeTopViewAdd' );
		$this->setHook( 'BSStateBarBeforeBodyViewAdd', 'onStateBarBeforeBodyViewAdd' );
//		$this->setHook( 'SpecialPage_initList' );
		$this->setHook( 'BSPageAccessAddAdditionalAccessGroups', 'onPageAccessAddAdditionalAccessGroups' );
		$this->setHook( 'BeforePageDisplay' );   

		$this->mAdapter->addRemoteHandler( 'ResponsibleEditors', $this, 'ajaxGetActivatedNamespacesForCombobox', 'edit' );
		$this->mAdapter->addRemoteHandler( 'ResponsibleEditors', $this, 'ajaxGetResponsibleEditorsByArticleId', 'edit' );
		$this->mAdapter->addRemoteHandler( 'ResponsibleEditors', $this, 'ajaxGetArticlesByNamespaceId', 'edit' );
		$this->mAdapter->addRemoteHandler( 'ResponsibleEditors', $this, 'ajaxGetListOfResponsibleEditorsForArticle', 'edit' );
		$this->mAdapter->addRemoteHandler( 'ResponsibleEditors', $this, 'ajaxSetListOfResponsibleEditorsForArticle', 'edit' );
		$this->mAdapter->addRemoteHandler( 'ResponsibleEditors', $this, 'ajaxDeleteResponsibleEditorsForArticle', 'edit' );

		//AJAX
		global $wgAjaxExportList;
		$wgAjaxExportList[] = 'SpecialResponsibleEditors::ajaxGetResponsibleEditors';
		$wgAjaxExportList[] = 'SpecialResponsibleEditors::ajaxSetResponsibleEditors';
		$wgAjaxExportList[] = 'SpecialResponsibleEditors::ajaxGetPossibleEditors';

		$this->mAdapter->registerPermission( 'responsibleeditors-changeresponsibility' );
		$this->mAdapter->registerPermission( 'responsibleeditors-viewspecialpage' );
		$this->mAdapter->registerPermission( 'responsibleeditors-takeresponsibility' );

		$sExtensionLibDir = dirname(__FILE__) . DS . 'includes';
		BsCore::registerClass('BsResponsibleEditor', $sExtensionLibDir);

//		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/ResponsibleEditors/js', 'ResponsibleEditors', false, true, false, 'MW::ResponsibleEditorsShow' );
//		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/ResponsibleEditors/js', 'ResponsibleEditors.lib.AssignmentPanel', false, true, false, 'MW::ResponsibleEditorsAssignmentPanel' );
//		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/ResponsibleEditors/js', 'ResponsibleEditors.SpecialPage.AssignmentDialog', false, true, true, 'MW::ResponsibleEditorsSpecialPageAssignmentDialog' );
//		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/ResponsibleEditors/js', 'ResponsibleEditors.SpecialPage.AssignmentWindow', false, true, true, 'MW::ResponsibleEditorsSpecialPageAssignmentWindow' );
//		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/ResponsibleEditors/js', 'ResponsibleEditors.BookshelfPlugin', false, true, false, 'MW::ResponsibleEditorsBookshelfPlugin' );
//		$this->registerStyleSheet( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/ResponsibleEditors/ResponsibleEditors.css', false, 'MW::ResponsibleEditorsCSS' );

		wfProfileOut('BS::' . __METHOD__);
	}

//	public function onSpecialPage_initList( &$aList ) {
//		$aList['ResponsibleEditors'] = 'SpecialResponsibleEditors';
//		return true;
//	}
	
	/**
	 * Adds the 'ext.bluespice.responsibleeditors' module to the OutputPage
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public function onBeforePageDisplay( $out, $skin ) {
		if( BsExtensionManager::isContextActive( 'MW::ResponsibleEditorsShow' ) ) $out->addModules( 'ext.bluespice.responsibleEditors' );
		if( BsExtensionManager::isContextActive( 'MW::ResponsibleEditorsAssignmentPanel' ) ) $out->addModules( 'ext.bluespice.responsibleEditors.assignmentPanel' );
		if( BsExtensionManager::isContextActive( 'MW::ResponsibleEditorsSpecialPageAssignmentDialog' ) ) $out->addModules( 'ext.bluespice.responsibleEditors.specialAssignmentDialog' );
		if( BsExtensionManager::isContextActive( 'MW::ResponsibleEditorsSpecialPageAssignmentWindow' ) ) $out->addModules( 'ext.bluespice.responsibleEditors.specialAssignmentWindow' );
		if( BsExtensionManager::isContextActive( 'MW::ResponsibleEditorsBookshelfPlugin' ) ) $out->addModules( 'ext.bluespice.responsibleEditors.bookshelfPlugin' );
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
		
		$this->mAdapter->addTemporaryGroupToUser( $wgUser, 'tmprespeditors', $aAvailablePermissions );
		return true;
	}
	
	public function onBSUEModulePDFcollectMetaData($oTitle, $oPageDOM, &$aParams, $oDOMXPath, &$aMeta) {
		$aEditors = $this->getResponsibleEditorIdsByArticleId($oTitle->getArticleId());
		$aEditorNames = array();
		foreach ($aEditors as $iEditorId) {
			$aEditorNames[] = BsAdapterMW::getUserDisplayName(User::newFromId($iEditorId));
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
				'name' => BsAdapterMW::getUserDisplayName(User::newFromId($iEditorId))
			);
		}
		return true;
	}

	public function onBSBookshelfBookManager($oSpecialBookshelfBookManager, $oOut, $oData, &$aPlugins) {
		BsExtensionManager::setContext('MW::ResponsibleEditorsBookshelfPlugin');
		BsExtensionManager::setContext('MW::ResponsibleEditorsAssignmentPanel');
		BsExtensionManager::setContext('MW::ResponsibleEditorsSpecialPageAssignmentWindow');
		BsExtensionManager::setContext('MW::ResponsibleEditorsCSS');

		$aPlugins[] = 'biz.hallowelt.ResponsibleEditors.BookshelfPlugin';

		$oOut->addHtml('<div id="bs-responsibleeditors-assignmentwindow"></div>');

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
					'options' => BsAdapterMW::getNamespacesForSelectOptions(array(-2, -1)),
				);
				break;
			case 'AutoPermissions':
				$wgGroupPermissions = $this->mAdapter->get('GroupPermissions');

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
	 * MediaWiki ContentActions hook. For more information please refer to <mediawiki>/docs/hooks.txt
	 * @param Array $aContentActions This array is used within the skin to render the content actions menu
	 * @return Boolean Always true for it is a MediwWiki Hook callback.
	 */
	public function onSkinTemplateContentActions( &$aContentActions) {

		//Check if menu entry has to be displayed
		$oCurrentUser = $this->mAdapter->get('User');
		if ($oCurrentUser->isLoggedIn() === false)
			return true;

		$oCurrentTitle = $this->mAdapter->get('Title');
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

		$oSpecialPageWithParam =
			SpecialPage::getTitleFor('ResponsibleEditors', $oCurrentTitle->getPrefixedText());

		//Add menu entry
		$aContentActions['responsibilitybutton'] = array(
			'class' => false,
			'text'  => wfMsg( 'bs-responsibleeditors-contentactions-label' ),
			'href'  => $oSpecialPageWithParam->getLocalURL(),
			'id'    => 'ca-respeditors'
		);

		return true;
	}

	/**
	 * MediaWiki ContentActions hook. For more information please refer to <mediawiki>/docs/hooks.txt
	 * @param Array $aContentActions This array is used within the skin to render the content actions menu
	 * @return Boolean Always true for it is a MediwWiki Hook callback.
	 */
	public function onSkinTemplateNavigationUniversal($oSkinTemplate, &$links) {
		//Check if menu entry has to be displayed
		$oCurrentUser = $this->mAdapter->get('User');
		if ($oCurrentUser->isLoggedIn() === false)
			return true;

		$oCurrentTitle = $this->mAdapter->get('Title');
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

		$oSpecialPageWithParam =
				SpecialPage::getTitleFor('ResponsibleEditors', $oCurrentTitle->getPrefixedText());

		//Add menu entry
		$links['actions']['responsibilitybutton'] = array(
			'class' => false,
			'text'  => wfMsg( 'bs-responsibleeditors-contentactions-label' ),
			'href'  => $oSpecialPageWithParam->getLocalURL(),
			'id'    => 'ca-respeditors'
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
		$sDispalyName = $this->mAdapter->getUserDisplayName($oFirstResponsibleEditor);

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
					BsAdapterMW::getUserMiniProfile(
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
	public function ajaxDeleteResponsibleEditorsForArticle() {
		$oResponse = new BsXHRJSONResponse();
		$oResponse->status = BsXHRResponseStatus::ERROR;

		$iArticleId = BsCore::getParam('articleId', -1, BsPARAM::REQUEST | BsPARAMTYPE::INT);
		$aUserIDs = BsCore::getParam('user_ids', array(), BsPARAM::REQUEST | BsPARAMTYPE::ARRAY_MIXED);
		
		$oRequestedTitle = Title::newFromId($iArticleId);

		if ($iArticleId === -1 || empty( $aUserIDs ) || $oRequestedTitle === null) {
			$oResponse->shortMessage = wfMsg( 'bs-responsibleeditors-error-ajax-invalid-parameter' );
			echo $oResponse;
			return;
		}

		$oCurrentUser = $this->mAdapter->get('User');
		//TODO: prevent delete on specific variations
		//$oCurrentUserResponsibleEditor = BsResponsibleEditor::newFromUser($oCurrentUser);
		if ($oRequestedTitle->userCan('responsibleeditors-changeresponsibility') === false
				//&& ( $oCurrentUserResponsibleEditor->isAssignedToArticleId($iArticleId) === false
				//&& BsConfig::get('MW::ResponsibleEditors::ResponsibleEditorMayChangeAssignment') === true
		) {
			$oResponse->shortMessage = wfMsg( 'bs-responsibleeditors-error-ajax-not-allowed' );
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
		$oResponse->shortMessage = wfMsg( 'bs-responsibleeditors-success-ajax' );
		echo $oResponse;
	}

	public function ajaxGetActivatedNamespacesForCombobox() {
		$aNamespaces = array();
		$aNamespaces[] = array(
			'namespace_id' => -99,
			'namespace_text' => BsAdapterMW::getNamespaceName(-99, true)
		);

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');
		foreach ($aActivatedNamespaces as $iNamespaceId) {
			$aNamespaces[] = array(
				'namespace_id' => $iNamespaceId,
				'namespace_text' => BsAdapterMW::getNamespaceName($iNamespaceId, true)
			);
		}
		echo '{ namespaces: ' . json_encode($aNamespaces) . ' }';
	}

	public function ajaxGetResponsibleEditorsByArticleId($iArticleId) {
		$aResponsibleEditorIds = $this->getResponsibleEditorIdsByArticleId($iArticleId);
		echo json_encode($aResponsibleEditorIds);
	}

	public function ajaxGetListOfResponsibleEditorsForArticle() {

		$iArticleId = BsCore::getParam('articleId', -1, BsPARAM::REQUEST | BsPARAMTYPE::INT);
		if ($iArticleId == -1)
			echo 'ERROR';
		$aListOfPossibleEditors = $this->getListOfResponsibleEditorsForArticle($iArticleId);
		echo '{users: ' . json_encode($aListOfPossibleEditors) . '}';
	}

	public function getListOfResponsibleEditorsForArticle($iArticleId) {
		$oCurrentTitle = Title::newFromId($iArticleId);

		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->select(
						'user',
						array('user_id', 'user_real_name'),
						array(),
						__METHOD__,
						array('ORDER BY' => 'user_real_name')
		);

		$aListOfPossibleEditors = array();
		$aListOfPossibleEditors[] = array(
			'user_id' => -1,
			'user_displayname' => wfMsg( 'bs-responsibleeditors-remove-assignment' )
		);

		foreach ($res as $row) {
			$oEditorUser = User::newFromId($row->user_id);
			$aPermissionErrors = $oCurrentTitle->getUserPermissionsErrors('responsibleeditors-takeresponsibility', $oEditorUser);
			if (empty($aPermissionErrors)) {
				$aListOfPossibleEditors[] =
						array(
							'user_id' => $oEditorUser->getId(),
							'user_displayname' => $this->mAdapter->getUserDisplayName($oEditorUser)
				);
			}
		}

		return $aListOfPossibleEditors;
	}

	public function ajaxGetArticlesByNamespaceId() {
		global $wgOut;
		$wgOut->disable();

		$iStart       = BsCore::getParam('start', 0, BsPARAM::POST | BsPARAMTYPE::INT);
		$sSort        = BsCore::getParam('sort', 'page_title', BsPARAM::POST | BsPARAMTYPE::SQL_STRING);
		$sDirection   = BsCore::getParam('dir', 'ASC', BsPARAM::POST | BsPARAMTYPE::SQL_STRING);
		$iLimit       = BsCore::getParam('limit', BsConfig::get('MW::ResponsibleEditors::SpecialPageDefaultPageSize'), BsPARAM::POST | BsPARAMTYPE::INT);
		$sDisplayMode = BsCore::getParam('displayMode', 'only-assigned', BsPARAM::POST | BsPARAMTYPE::STRING);
		$iNamespaceId = BsCore::getParam('namespaceId', -99, BsPARAM::POST | BsPARAMTYPE::INT);

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');

		$oResult = new stdClass();

		$aTables     = array('bs_responsible_editors', 'user', 'page');
		$aVariables  = array( 'page_id', 'page_title', 'page_namespace' );
		$aConditions = array('page_namespace' => $aActivatedNamespaces);

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
		if ($sDisplayMode == 'only-assigned' || $sDisplayMode == 'only-not-assigned') {
			$row = $dbr->select(
					array('page', 'bs_responsible_editors'), 'page_id AS cnt', $aConditions, __METHOD__, array('GROUP BY' => 'page_id'), array('page' => array('RIGHT JOIN', 'page_id = re_page_id'))
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
			$aTables, $aVariables, $aConditions, __METHOD__, $aOptions, $aJoinOptions
		);

		$oResult->pages = array();
		foreach ($res as $row) {
			$oTitle = Title::newFromId($row->page_id);

			$iPageId = $row->page_id;
			$sPageNsText = (!empty($row->page_namespace) ) ? BsAdapterMW::getNamespaceName($row->page_namespace, true) : BsAdapterMW::getNamespaceName(0, true);
			$sPageNsId = (!empty($row->page_namespace) ) ? $row->page_namespace : 0;
			$sPageTitle = $row->page_title; //utf8_encode( $row->page_title );

			$oPage = new stdClass();
			$oPage->page_id = $iPageId;
			$oPage->page_namespace_text = $sPageNsText;
			$oPage->page_namespace = $sPageNsId;
			$oPage->page_title = $sPageTitle;
			$oPage->page_link_url = $oTitle->getFullUrl();
			$oPage->users = array();

			$aEditorIDs = $this->getResponsibleEditorIdsByArticleId($row->page_id);
			$aEditorIDs = array_unique($aEditorIDs);
			foreach ($aEditorIDs as $iEditorID) {
				$oUser = USER::newFromId($iEditorID);
				if ($oUser) {
					$sUserRealname = $oUser->getRealName();
					$sUserDisplayName = empty($sUserRealname) ? $oUser->getName() : $sUserRealname;

					$oPage->users[] = array(
						'user_id' => $iEditorID,
						'user_page_link_url' => $oUser->getUserPage()->getFullUrl(),
						'user_displayname' => $sUserDisplayName
					);
				}
			}

			$oResult->pages[] = $oPage;
		}

		echo json_encode($oResult);
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
		
		$sUserName    = BsAdapterMW::getUserDisplayName( $oUser );
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
}