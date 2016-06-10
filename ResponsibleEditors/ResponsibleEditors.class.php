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
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    2.23.3
 * @package    BlueSpice_Extensions
 * @subpackage ResponsibleEditors
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class ResponsibleEditors extends BsExtensionMW {

	protected static $aResponsibleEditorsByArticleId = array();

	public function __construct() {
		wfProfileIn('BS::' . __METHOD__);
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME => 'ResponsibleEditors',
			EXTINFO::DESCRIPTION => 'bs-responsibleeditors-desc',
			EXTINFO::AUTHOR => 'Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'https://help.bluespice.com/index.php/ResponsibleEditors',
			EXTINFO::DEPS => array(
				'bluespice' => '2.23.3',
				'StateBar' => '2.22.0',
				'Authors' => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::ResponsibleEditors';

		wfProfileOut('BS::' . __METHOD__);
	}

	protected function initExt() {
		wfProfileIn('BS::' . __METHOD__);
		BsConfig::registerVar( 'MW::ResponsibleEditors::EChange', true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-echange', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::EDelete', true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-edelete', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::EMove',   true, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-emove', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::ActivatedNamespaces', array(0), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-responsibleeditors-pref-activatednamespaces', 'multiselectex' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::AutoAssignOnArticleCreation', false, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-autoassignonarticlecreation', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::ResponsibleEditorMayChangeAssignment', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-responsibleeditormaychangeassignment', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::EMailNotificationOnResponsibilityChange', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-emailnotificationonresponsibilitychange', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::AddArticleToREWatchLists', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-responsibleeditors-pref-responsibleeditormaychangeassignment', 'toggle' );
		BsConfig::registerVar( 'MW::ResponsibleEditors::AutoPermissions', array('read', 'edit'), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-responsibleeditors-pref-autopermissions', 'multiselectex' );

		//Hooks
		$this->setHook( 'BeforeInitialize' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'SkinTemplateNavigation' );
		$this->setHook( 'ArticleInsertComplete' );
		$this->setHook( 'SpecialMovepageAfterMove' );
		$this->setHook( 'ArticleDeleteComplete' );
		$this->setHook( 'ArticleSaveComplete' );
		$this->setHook( 'TitleMoveComplete' );

		$this->setHook( 'BSBookshelfManagerGetBookDataRow' );
		$this->setHook( 'BSUEModulePDFcollectMetaData' );
		$this->setHook( 'BSStateBarAddSortTopVars', 'onStatebarAddSortTopVars' );
		$this->setHook( 'BSStateBarAddSortBodyVars', 'onStatebarAddSortBodyVars' );
		$this->setHook( 'BSStateBarBeforeTopViewAdd', 'onStateBarBeforeTopViewAdd' );
		$this->setHook( 'BSStateBarBeforeBodyViewAdd', 'onStateBarBeforeBodyViewAdd', true );
		$this->setHook( 'RevisionAjaxReviewBeforeParams' );
		$this->setHook( 'BSPageAccessAddAdditionalAccessGroups', 'onPageAccessAddAdditionalAccessGroups' );
		$this->setHook( 'BSDashboardsUserDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsUserDashboardPortalPortlets' );
		$this->setHook( 'BSUserSidebarGlobalActionsWidgetGlobalActions' );

		$this->setHook( 'EchoGetDefaultNotifiedUsers' );

		$this->setHook( 'SuperList::getFieldDefinitions', 'onSuperListGetFieldDefinitions' );
		$this->setHook( 'SuperList::getColumnDefinitions', 'onSuperListGetColumnDefinitions' );
		$this->setHook( 'SuperList::queryPagesWithFilter', 'onSuperListQueryPagesWithFilter' );
		$this->setHook( 'SuperList::buildDataSets', 'onSuperListBuildDataSets' );

		$this->mCore->registerPermission( 'responsibleeditors-changeresponsibility', array(), array( 'type' => 'global' ) );
		$this->mCore->registerPermission( 'responsibleeditors-viewspecialpage', array(), array( 'type' => 'global' ) );
		$this->mCore->registerPermission( 'responsibleeditors-takeresponsibility', array( 'user' ), array( 'type' => 'global' ) );

		BSNotifications::registerNotificationCategory( 'bs-responsible-editors-assignment-cat' );
		BSNotifications::registerNotificationCategory( 'bs-responsible-editors-action-cat' );
		BSNotifications::registerNotification(
			'bs-responsible-editors-assign',
			'bs-responsible-editors-assignment-cat',
			'notification-bs-responsibleeditors-assign-summary',
			array('agent', 'title', 'titlelink'),
			'notification-bs-responsibleeditors-assign-subject',
			array('agent', 'title', 'titlelink'),
			'notification-bs-responsibleeditors-assign-body',
			array('agent', 'title', 'titlelink')
		);
		BSNotifications::registerNotification(
			'bs-responsible-editors-revoke',
			'bs-responsible-editors-assignment-cat',
			'notification-bs-responsibleeditors-revoke-summary',
			array('agent', 'title', 'titlelink'),
			'notification-bs-responsibleeditors-revoke-subject',
			array('agent', 'title', 'titlelink'),
			'notification-bs-responsibleeditors-revoke-body',
			array('agent', 'title', 'titlelink')
		);
		BSNotifications::registerNotification(
			'bs-responsible-editors-change',
			'bs-responsible-editors-action-cat',
			'notification-bs-responsibleeditors-change-summary',
			array('agent', 'title', 'titlelink'),
			'notification-bs-responsibleeditors-change-subject',
			array('agent', 'title', 'titlelink'),
			'notification-bs-responsibleeditors-change-body',
			array('agent', 'title', 'titlelink')
		);
		BSNotifications::registerNotification(
			'bs-responsible-editors-delete',
			'bs-responsible-editors-action-cat',
			'notification-bs-responsibleeditors-delete-summary',
			array('agent', 'title', 'titlelink'),
			'notification-bs-responsibleeditors-delete-subject',
			array('agent', 'title', 'titlelink'),
			'notification-bs-responsibleeditors-delete-body',
			array('agent', 'title', 'titlelink')
		);
		BSNotifications::registerNotification(
			'bs-responsible-editors-move',
			'bs-responsible-editors-action-cat',
			'notification-bs-responsibleeditors-move-summary',
			array('agent', 'title', 'titlelink', 'newtitle', 'newtitlelink'),
			'notification-bs-responsibleeditors-move-subject',
			array('agent', 'title', 'titlelink', 'newtitle', 'newtitlelink'),
			'notification-bs-responsibleeditors-move-body',
			array('agent', 'title', 'titlelink', 'newtitle', 'newtitlelink'),
			array(
				'formatter-class' => 'ResponsibleEditorFormatter'
			)
		);
		wfProfileOut('BS::' . __METHOD__);
	}

	/**
	 * Adds the 'ext.bluespice.responsibleeditors' module to the OutputPage
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public function onBeforePageDisplay( $out, $skin ) {
		if ( $out->getRequest()->getVal( 'action', 'view') == 'view' && !$out->getTitle()->isSpecialPage() ) {
			$out->addModules( 'ext.bluespice.responsibleEditors' );
			$out->addModuleStyles( 'ext.bluespice.responsibleEditors.styles' );

			//Make information about current pages RespEds available on client side
			$iArticleId = $out->getTitle()->getArticleID();
			$aResponsibleEditorIds = static::getResponsibleEditorIds(
				$iArticleId
			);
			$oData = new stdClass();
			$oData->articleId = $iArticleId;
			$oData->editorIds = $aResponsibleEditorIds;

			$out->addJsConfigVars( 'bsResponsibleEditors', $oData );
		}

		if ( BsExtensionManager::getExtension( 'Bookshelf' ) !== null ) {
			//Attach Bookshelfs plugin if in context
			if ( SpecialPage::getTitleFor( 'BookshelfBookManager' )->equals( $out->getTitle() ) ) {
				$out->addModules( 'ext.bluespice.responsibleEditors.bookshelfPlugin' );
			}
		}

		if ( BsExtensionManager::getExtension( 'SuperList' ) !== null ) {
			//Attach SuperList plugin if in context
			if ( SpecialPage::getTitleFor( 'SuperList' )->equals( $out->getTitle() ) ) {
				$out->addModules( 'ext.bluespice.responsibleEditors.superList' );
			}
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
	public static function getSchemaUpdates( $updater ) {
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
	 * Add the given User to a temporary group if he is a responsible editor
	 * for the given Title. This group will have special permissions for the
	 * Title's namespace. The group assignment exists only during the current
	 * request. This method needs to be called before a permission check is
	 * performed on the Title.
	 * @param Title $oTitle
	 * @param User $oUser
	 * @return boolean
	 */
	public function applyTempPermissionsForRespEditor( Title $oTitle, User $oUser ) {
		$iArticleID = $oTitle->getArticleID();
		$aResponsibleEditorsIDs = static::getResponsibleEditorIds(
			$iArticleID
		);

		if ( !in_array( $oUser->getId(), $aResponsibleEditorsIDs ) ){
			return false;
		}

		$aAvailablePermissions = BsConfig::get( 'MW::ResponsibleEditors::AutoPermissions' );
		if ( empty( $aAvailablePermissions ) ) {
			return false;
		}

		BsGroupHelper::addTemporaryGroupToUser(
			$oUser,
			'tmprespeditors',
			$aAvailablePermissions,
			$oTitle
		);

		return true;
	}

	/**
	 * Hook handler for FlaggedRevs RevisionReview overwrite.
	 * ATTENTION: This is a handler for a custom hook in FlaggedRevsConnector!
	 * It will be removed in next version!
	 * @param FRCRevisionReview $oRevisionReview
	 * @param Title $oTitle
	 * @param type $aArgs
	 * @return boolean
	 * @deprecated since version 1.22
	 */
	public function onRevisionAjaxReviewBeforeParams( $oRevisionReview, &$oTitle, &$aArgs ) {
		//MW BeforeInitialize hook is not present in ajax calls, so apply
		//possible permissions for responsible editors in this context
		if( is_null($oTitle) ) {
			foreach( $aArgs as $sArg ) {
				$set = explode( '|', $sArg, 2 );
				if( count( $set ) != 2 ) {
					continue;
				}

				list( $sKey, $vVal ) = $set;
				if( $sKey != 'target' ) {
					continue;
				}

				$oTitle = Title::newFromText( $vVal );
				break;
			}
		}
		if( is_null($oTitle) || !$oTitle->exists() ) {
			return true;
		}

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');
		if ( !in_array($oTitle->getNamespace(), $aActivatedNamespaces) ) {
			return true;
		}

		global $wgUser;
		$this->applyTempPermissionsForRespEditor( $oTitle, $wgUser );
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
	public function onBeforeInitialize( &$oTitle, $article, &$output, &$oUser, $request, $mediaWiki ) {
		if( is_null($oTitle) || !$oTitle->exists() ) return true;

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');
		if ( !in_array($oTitle->getNamespace(), $aActivatedNamespaces) ) return true;

		$this->applyTempPermissionsForRespEditor( $oTitle, $oUser );
		return true;
	}

	public function onBSUEModulePDFcollectMetaData($oTitle, $oPageDOM, &$aParams, $oDOMXPath, &$aMeta) {
		$aEditors = static::getResponsibleEditorIds( $oTitle->getArticleId() );
		$aEditorNames = array();
		foreach ( $aEditors as $iEditorId ) {
			$aEditorNames[] = $this->mCore->getUserDisplayName(User::newFromId($iEditorId));
		}
		$aMeta['responsibleeditors'] = implode(', ', $aEditorNames);
		return true;
	}

	public function onBSBookshelfManagerGetBookDataRow($oBookTitle, $oBookRow) {
		$oBookRow->editors = array();
		$aEditors = static::getResponsibleEditorIds( $oBookRow->page_id );
		foreach ( $aEditors as $iEditorId ) {
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
	 * Adds Special:ResponsibleEditors link to wiki wide widget
	 * @param UserSidebar $oUserSidebar
	 * @param User $oUser
	 * @param array $aLinks
	 * @param string $sWidgetTitle
	 * @return boolean
	 */
	public function onBSUserSidebarGlobalActionsWidgetGlobalActions( UserSidebar $oUserSidebar, User $oUser, &$aLinks, &$sWidgetTitle ) {
		$oSpecialResponsibleEditors = SpecialPageFactory::getPage(
			'ResponsibleEditors'
		);
		if( !$oSpecialResponsibleEditors ) {
			return true;
		}
		$aLinks[] = array(
			'target' => $oSpecialResponsibleEditors->getPageTitle(),
			'text' => $oSpecialResponsibleEditors->getDescription(),
			'attr' => array(),
			'position' => 500,
			'permissions' => array(
				'read',
				'responsibleeditors-viewspecialpage'
			),
		);
		return true;
	}

	/**
	 * Adds the "Responsible editors" menu entry in view mode
	 * @param SkinTemplate $sktemplate
	 * @param array $links
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateNavigation( &$sktemplate, &$links ) {
		//Check if menu entry has to be displayed
		$oCurrentUser = $this->getUser();
		if ( $oCurrentUser->isLoggedIn() === false ) {
			return true;
		}

		$oCurrentTitle = $this->getTitle();
		if ( $oCurrentTitle->exists() === false ) {
			return true;
		}

		$aActivatedNamespaces = BsConfig::get( 'MW::ResponsibleEditors::ActivatedNamespaces' );
		if ( is_array( $aActivatedNamespaces ) ) {
			if ( !in_array( $oCurrentTitle->getNamespace(), $aActivatedNamespaces ) ) return true;
		} else {
			if ( $oCurrentTitle->getNamespace() == $aActivatedNamespaces ) return true;
		}
		if ( $this->userIsAllowedToChangeResponsibility( $oCurrentUser, $oCurrentTitle ) === false ) return true;

		$links['actions']['respeditors'] = array(
			'text'  => wfMessage( 'bs-responsibleeditors-contentactions-label' )->text(),
			'href'  => '#',
			'class' => false,
			'id' => 'ca-respeditors'
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
		$aResponsibleEditorIds = static::getResponsibleEditorIds(
			$oCurrentTitle->getArticleId()
		);
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
		$aSortTopVars['statebartopresponsibleeditorsentries'] = wfMessage( 'bs-responsibleeditors-statebartopresponsibleeditorsentries' )->plain();
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 * @param array $aSortBodyVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortBodyVars( &$aSortBodyVars ) {
		$aSortBodyVars['statebarbodyresponsibleeditorsentries'] = wfMessage( 'bs-responsibleeditors-statebarbodyresponsibleeditorsentries' )->plain();
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

		//MW BeforeInitialize hook is not present in ajax calls, so apply
		//possible permissions for responsible editors in this context
		$this->applyTempPermissionsForRespEditor( $oTitle, $oUser );

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
		$aResponsibleEditorIds = static::getResponsibleEditorIds( $iArticleId );
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
		$aResponsibleEditorIds = static::getResponsibleEditorIds( $iArticleId );
		self::notifyResponsibleEditors($aResponsibleEditorIds, $user, array(Title::newFromID($iArticleId)), 'change');
		return true;
	}

	public function onTitleMoveComplete(&$title, &$newtitle, &$user, $oldid, $newid) {
		$aResponsibleEditorIds = static::getResponsibleEditorIds( $oldid );
		self::notifyResponsibleEditors($aResponsibleEditorIds, $user, array($title, $newtitle), 'move');
		return true;
	}

	private function makeStateBarTopResponsibleEditorsEntries($iArticleId) {
		global $wgScriptPath;
		$aResponsibleEditorIds = static::getResponsibleEditorIds( $iArticleId );
		if (empty($aResponsibleEditorIds))
			return false;

		$oResponsibleEditorsTopView = new ViewStateBarTopElement();

		$oFirstResponsibleEditor = User::newFromId($aResponsibleEditorIds[0]);
		$sDispalyName = $this->mCore->getUserDisplayName($oFirstResponsibleEditor);

		$oResponsibleEditorsTopView->setKey('ResponsibleEditors-Top');
		$oResponsibleEditorsTopView->setIconSrc( $wgScriptPath . '/extensions/BlueSpiceExtensions/' . $this->mInfo[EXTINFO::NAME] . '/resources/images/bs-infobar-responsibleeditor.png' );
		$oResponsibleEditorsTopView->setIconAlt( wfMessage( 'bs-responsibleeditors-statebartop-icon-alt' )->plain() );
		$oResponsibleEditorsTopView->setText($sDispalyName);
		$oResponsibleEditorsTopView->setTextLinkTitle($sDispalyName);
		$oResponsibleEditorsTopView->setTextLink($oFirstResponsibleEditor->getUserPage()->getFullURL());

		return $oResponsibleEditorsTopView;
	}

	private function makeStateBarBodyResponsibleEditorsEntries($iArticleId) {
		$aResponsibleEditorIds = static::getResponsibleEditorIds( $iArticleId );
		if (empty($aResponsibleEditorIds))
			return false;

		$oResponsibleEditorsBodyView = new ViewStateBarBodyElement();

		$sLastUsername = '';
		$aResponsibleEditorUserMiniProfiles = array();
		foreach ( $aResponsibleEditorIds as $iResponsibleEditorId ) {
			$oUser = User::newFromId( $iResponsibleEditorId );
			$sLastUsername = $oUser->getName();
			$aResponsibleEditorUserMiniProfiles[] = $this->mCore->getUserMiniProfile(
				$oUser,
				array(
					'width' => 48,
					'height' => 48,
					'classes' => array( 'left' )
				)
			)->execute();
		}

		$sStateBarBodyHeadline = wfMessage( 'bs-responsibleeditors-statebarbody-headline' )
			->numParams( count( $aResponsibleEditorIds ) )
			->params( $sLastUsername )
			->text();

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

	public static function getListOfPossibleResponsibleEditorsForArticle( $iArticleId, $aListOfPossibleEditors = array() ) {
		if( empty($iArticleId) || !$oTitle = Title::newFromId($iArticleId) ) {
			return array();
		}

		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->select(
			'user',
			array('user_id', 'user_real_name')
		);

		foreach( $res as $row ) {
			$oEditorUser = User::newFromId( $row->user_id );
			$aPermissionErrors = $oTitle->getUserPermissionsErrors(
				'responsibleeditors-takeresponsibility',
				$oEditorUser
			);
			if( !empty($aPermissionErrors) ) {
				continue;
			}
			$sDisplayName = empty( $row->user_real_name )
				? $oEditorUser->getName()
				: $row->user_real_name
			;
			$aListOfPossibleEditors[] = array(
				'user_id' => $oEditorUser->getId(),
				'user_displayname' => $sDisplayName
			);
		}

		return $aListOfPossibleEditors;
	}

	/**
	 * DEPRECATED
	 * @deprecated since version 2.23.3
	 * @param integer $iArticleId
	 * @return array
	 */
	public function getListOfResponsibleEditorsForArticle( $iArticleId ) {
		return self::getListOfPossibleResponsibleEditorsForArticle(
			$iArticleId
		);
	}

	/**
	 * DEPRECATED
	 * Helper function. Fetches database and returns array of user_id's of
	 * responsible editors of an article
	 * @deprecated since version 2.23.3
	 * @param Integer $iArticleId The page_id of the article you want to retrieve the responsible editors for.
	 * @return Array user_ids of responsible editors for given article
	 */
	public function getResponsibleEditorIdsByArticleId( $iArticleId, $bForceReload = false ) {
		return static::getResponsibleEditorIds( $iArticleId, $bForceReload );
	}

	/**
	 * DEPRECATED
	 * Helper function. Fetches database and returns array of responsible editors of an article
	 * @deprecated since version 2.23.3
	 * @param Integer $iArticleId The page_id of the article you want to retrieve the responsible editors for.
	 * @return Array user_ids of responsible editors for given article
	 */
	public function getResponsibleEditorsByArticleId( $iArticleId, $bForceReload = false ) {
		return static::getResponsibleEditors( $iArticleId, $bForceReload );
	}

	protected static function getResponsibleEditorsFromCache( $iArticleId ) {
		if ( isset( self::$aResponsibleEditorsByArticleId[$iArticleId] ) ) {
			return self::$aResponsibleEditorsByArticleId[$iArticleId];
		}
		$sKey = BsCacheHelper::getCacheKey(
			'ResponsibleEditors',
			'getResponsibleEditorsByArticleId',
			(int)$iArticleId
		);

		if( $aData = BsCacheHelper::get( $sKey ) ) {
			wfDebugLog(
				'BsMemcached',
				__CLASS__.': Fetching ResponsibleEditors from cache'
			);
		}
		return $aData;
	}

	protected static function appendResponsibleEditorsCache( $iArticleId, $aRespEditors = array() ) {
		$sKey = BsCacheHelper::getCacheKey(
			'ResponsibleEditors',
			'getResponsibleEditorsByArticleId',
			(int) $iArticleId
		);
		BsCacheHelper::set( $sKey, $aRespEditors );
		self::$aResponsibleEditorsByArticleId[$iArticleId] = $aRespEditors;

		return $aRespEditors;
	}

	/**
	 * Helper function. Fetches database and returns array of user_id's of
	 * responsible editors of an article
	 * @param Integer $iArticleId The page_id of the article you want to
	 * retrieve the responsible editors for.
	 * @return Array user_ids of responsible editors for given article
	 */
	public static function getResponsibleEditorIds( $iArticleId, $bForceReload = false ) {
		$aRespEditors = static::getResponsibleEditors(
			$iArticleId,
			$bForceReload
		);

		if ( empty( $aRespEditors ) ) {
			return $aRespEditors;
		}
		$aReturn = array();
		foreach( $aRespEditors as $aRespEditor ) {
			$aReturn[] = (int)$aRespEditor['re_user_id'];
		}
		return $aReturn;
	}

	/**
	 * Helper function. Fetches database and returns array of responsible
	 * editors of an article
	 * @param Integer $iArticleId The page_id of the article you want to
	 * retrieve the responsible editors for.
	 * @return Array of responsible editors for given article
	 */
	public static function getResponsibleEditors( $iArticleId, $bForceReload = false ) {
		if ( empty( $iArticleId ) ) {
			return false;
		}

		if( !$bForceReload ) {
			$aRespEditors = self::getResponsibleEditorsFromCache( $iArticleId );
			if( $aRespEditors ) {
				return $aRespEditors;
			}
		}
		wfDebugLog(
			'BsMemcached',
			__CLASS__.': Fetching ResponsibleEditors from DB'
		);

		$oRes = wfGetDB( DB_SLAVE )->select(
			'bs_responsible_editors',
			'*',
			array( 're_page_id' => $iArticleId ),
			__METHOD__,
			array( 'ORDER BY' => 're_position' )
		);
		if( !$oRes ) {
			return array();
		}

		$aRespEditors = array();
		foreach( $oRes as $row ) {
			$row->re_user_id = (int) $row->re_user_id;
			$aRespEditors[] = (array) $row;
		}

		return self::appendResponsibleEditorsCache(
			$iArticleId,
			$aRespEditors
		);
	}

	/**
	 * Sets responsible editors for a title
	 * @param Title $oTitle
	 * @param array $aEditors - Array of user ids
	 * @return boolean
	 */
	public static function setResponsibleEditors( Title $oTitle, $aEditors = array() ) {
		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		$res = $dbw->select(
			'bs_responsible_editors',
			're_user_id',
			array( 're_page_id' => $oTitle->getArticleID() )
		);

		$aCurrentEditorIds = array();
		foreach( $res as $row) {
			$aCurrentEditorIds[] = $row->re_user_id;
		}

		$aRemovedEditorIds = array_diff( $aCurrentEditorIds, $aEditors );
		$aNewEditorIds = array_diff( $aEditors, $aCurrentEditorIds );
		$bAddWatchList = BsConfig::get(
			'MW::ResponsibleEditors::AddArticleToREWatchLists'
		);

		if( !empty($aNewEditorIds) && $bAddWatchList ) {
			foreach($aNewEditorIds as $iUserId) {
				$oNewEditorUser = User::newFromId($iUserId);
				if( !$oNewEditorUser->isWatched($oTitle) ) {
					$oNewEditorUser->addWatch($oTitle);
				}
			}
		}
		//Remove all
		$dbw->delete(
			'bs_responsible_editors',
			array(
				're_page_id' => $oTitle->getArticleID()
			)
		);

		//Add all --> to maintain position! As log as re_position field is not
		//used properly...
		$iPosition = 0;
		foreach( $aEditors as $iEditor ) {
			$dbw->insert(
				'bs_responsible_editors',
				array(
					're_page_id' => $oTitle->getArticleID(),
					're_user_id' => $iEditor,
					're_position' => $iPosition
				)
			);
			$iPosition++;
		}

		$dbw->commit();

		$oUser = RequestContext::getMain()->getUser();

		BSNotifications::notify(
			'bs-responsible-editors-assign',
			$oUser,
			$oTitle,
			array( 'affected-users' => $aNewEditorIds )
		);
		BSNotifications::notify(
			'bs-responsible-editors-revoke',
			$oUser,
			$oTitle,
			array( 'affected-users' => $aRemovedEditorIds )
		);

		foreach( $aNewEditorIds as $iNewEditorId ) {
			$oEditor = User::newFromId( $iNewEditorId );
			$oLogger = new ManualLogEntry( 'bs-responsible-editors', 'add' );
			$oLogger->setPerformer( $oUser );
			$oLogger->setTarget( $oTitle );
			$oLogger->setParameters( array(
					'4::editor' => $oEditor->getName()
			) );
			$oLogger->insert();
		}
		foreach( $aRemovedEditorIds as $iRemovedEditorId ) {
			$oEditor = User::newFromId( $iRemovedEditorId );
			$oLogger = new ManualLogEntry( 'bs-responsible-editors', 'remove' );
			$oLogger->setPerformer( $oUser );
			$oLogger->setTarget( $oTitle );
			$oLogger->setParameters( array(
					'4::editor' => $oEditor->getName()
			) );
			$oLogger->insert();
		}

		ResponsibleEditors::invalidateCache( $oTitle->getArticleID() );
		return true;
	}

	/**
	 *
	 * @param array $aResponsibleEditorIds
	 * @param User $oUser
	 * @param array $aTitles
	 * @param string $sAction
	 */
	public static function notifyResponsibleEditors($aResponsibleEditorIds, $oUser, $aTitles, $sAction) {
		if ( empty( $aResponsibleEditorIds ) ) return true;

		BSNotifications::notify(
			"bs-responsible-editors-{$sAction}",
			$oUser,
			$aTitles[0],
			array(
				'affected-users' => $aResponsibleEditorIds,
				'titles' => $aTitles
			)
		);

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
			$aResults[] = wfMessage( 'bs-responsibleeditors-no-own-responsibilities' )->plain();
		}

		return implode( '', $aResults );
	}

	public function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
		switch ( $event->getType() ) {
			case 'bs-responsible-editors-assign':
			case 'bs-responsible-editors-revoke':
			case 'bs-responsible-editors-change':
			case 'bs-responsible-editors-delete':
			case 'bs-responsible-editors-move':
				$extra = $event->getExtra();
				if ( !$extra || !isset( $extra['affected-users'] ) ) {
					break;
				}
				$aAffectedUsers = $extra['affected-users'];
				foreach($aAffectedUsers as $iUserId) {
					$users[$iUserId] = User::newFromId($iUserId);
				}
				break;
		}
		return true;
	}

	public function onSuperListGetFieldDefinitions(&$aFields) {
		$aFields[] = array(
			'name' => 'responsible_editors',
		);
		return true;
	}

	public function onSuperListGetColumnDefinitions(&$aColumns) {
		$aColumns[] = array(
			'header' => wfMessage( 'bs-responsibleeditors-assignededitors' )->escaped(),
			'dataIndex' => 'responsible_editors',
			'id' => 'responsible_editors',
			'filter' => array(
				'type' => 'string'
			),
			'width' => 200,
			'hidden' => true
		);
		return true;
	}
	public function onSuperListQueryPagesWithFilter($aFilters, &$aTables, &$aFields, &$aConditions, &$aJoinConditions) {
		$dbr = wfGetDB(DB_SLAVE);
		$sTablePrefix = $dbr->tablePrefix();

		$aTables[] = "{$sTablePrefix}bs_responsible_editors AS responsible";
		$aJoinConditions["{$sTablePrefix}bs_responsible_editors AS responsible"] = array(
			'LEFT OUTER JOIN', "{$sTablePrefix}page.page_id=responsible.re_page_id"
		);
		$aTables[] = "{$sTablePrefix}user AS responsible_users";
		$aJoinConditions["{$sTablePrefix}user AS responsible_users"] = array(
			'LEFT OUTER JOIN', "responsible.re_user_id=responsible_users.user_id"
		);
		$aFields[] = "GROUP_CONCAT(IF(STRCMP(responsible_users.user_real_name,''),responsible_users.user_real_name,responsible_users.user_name)) AS responsible_editors";

		if (array_key_exists('responsible_editors', $aFilters)) {
			SuperList::filterStringsTable("CONCAT_WS(',',IF(STRCMP(responsible_users.user_real_name,''),responsible_users.user_real_name,responsible_users.user_name))", $aFilters['responsible_editors'], $aConditions);
		}

		return true;
	}

	function onSuperListBuildDataSets(&$aRows) {
		if (!count($aRows)) {
			return true;
		}

		$aPageIds = array_keys($aRows);

		$dbr = wfGetDB(DB_READ);
		$aTables = array(
			'bs_responsible_editors', 'user'
		);
		$aJoinConditions = array(
			'user' => array('JOIN', 're_user_id=user_id')
		);
		$sField = "re_page_id, re_position, user_id";
		$sCondition = "re_page_id IN (" . implode(',', $aPageIds) . ")";
		$aOptions = array(
			'ORDER BY' => 're_page_id, re_position'
		);

		$res = $dbr->select( $aTables, $sField, $sCondition, __METHOD__,
			$aOptions, $aJoinConditions );

		$aData = array();
		$aUserIds = array();
		while ($row = $res->fetchObject()) {
			$oUser = User::newFromId($row->user_id);
			if( $oUser === null ) continue;
			$aUserIds[$row->re_page_id][] = $row->user_id;
			$aData[$row->re_page_id][] =
				'<li>'.
					'<a class="bs-re-superlist-editor" href="#">'.
						BsCore::getUserDisplayName($oUser).
					'</a>'.
				'</li>';
		}

		foreach ($aRows as $iKey => $aRowSet) {
			if (array_key_exists($iKey, $aData)) {
				$aRows[$iKey]['responsible_editors'] =
					Html::rawElement(
						'ul',
						array(
							'data-articleId' => $iKey,
							'data-editorIds' => FormatJson::encode($aUserIds[$iKey])
						),
						implode('', $aData[$iKey])
					);
			}
		}

		return true;
	}

	public static function invalidateCache( $iArticleId ) {
		if ( empty( $iArticleId ) ) {
			return false;
		}
		$sKey = BsCacheHelper::getCacheKey(
			'ResponsibleEditors',
			'getResponsibleEditorsByArticleId',
			(int) $iArticleId
		);
		if ( isset( self::$aResponsibleEditorsByArticleId[$iArticleId] ) ) {
			unset( self::$aResponsibleEditorsByArticleId[$iArticleId] );
		}
		BsCacheHelper::invalidateCache( $sKey );
		if ( $oTitle = Title::newFromID( $iArticleId ) ) {
			$oTitle->invalidateCache();
		}
		return true;
	}

	public static function addPropertyValues( SMW\SemanticData $aSemanticData, WikiPage $aWikiPage, SMW\DIProperty $aProperty ) {
		$arrUserIds = ResponsibleEditors::getResponsibleEditors( $aWikiPage->getId() );
		if ( is_array( $arrUserIds ) && count( $arrUserIds ) >= 1 ) {
			foreach ( $arrUserIds as $userId ) {
				$user = User::newFromId( $userId[ "re_user_id" ] );
				$aSemanticData->addPropertyObjectValue(
				  $aProperty, SMW\DIWikiPage::newFromTitle( $user->getUserPage() )
				);
			}
		}
	}
}
