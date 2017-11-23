<?php

/**
 * UserManager Extension for BlueSpice
 *
 * Central point of administration for BlueSpice
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Sebastian Ulbricht
 * @package    BlueSpice_Extensions
 * @subpackage WikiAdmin
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
  */

class WikiAdmin extends BsExtensionMW {

	protected static $prLoadModulesAndScripts;
	protected static $prRegisteredModules = array();
	protected static $prRegisteredModuleClasses = array();
	protected static $prRunningModules = array();
	protected static $messagesLoaded = false;

	// TODO SU (04.07.11 11:08): Brauchen wir das noch?
	protected static $prExcludeGroups = array(
		'autoconfirmed',
		'emailconfirmed',
		'bot',
		'bureaucrat',
		'sysop'
	);
	// TODO SU (04.07.11 11:08): Brauchen wir das noch?
	protected static $prExcludeDeleteGroups = array(
		'*',
		'user',
		'sysop'
	);
	// TODO SU (04.07.11 11:08): Brauchen wir das noch? - SM (25.06.12 14:01) prExcludeRights ist da einzige was noch verwendet wird ... im PermissionManger
	protected static $prExcludeRights = array(
		'reupload',
		'reupload-shared',
		'minoredit',
		'deletedhistory',
		'editinterface',
		'importupload',
		'patrol',
		'autopatrol',
		'trackback',
		'unwatchedpages',
		'autoconfirmed',
		'upload_by_url',
		'ipblock-exempt',
		'blockemail',
		'purge',
		'emailconfirmed',
		'nominornewtalk'
	);
	// TODO SU (04.07.11 11:09): Brauchen wir das noch?
	protected static $prCommonRights = array(
		'read',
		'edit',
		'createpage',
		'createtalk',
		'move',
		'delete',
		'rollback',
		'minoredit',
		'workflowview',
		'workflowedit',
		'workflowlist',
		'undelete'
	);
	// TODO SU (04.07.11 11:09): Brauchen wir das noch?
	protected static $prHardPerms = array(
		'user' => array(
				'read',
				'edit',
				'createpage',
				'createtalk',
				'move',
				'upload',
				'files',
				'searchfiles',
				'bot',
				'delete'
	));

	// TODO SU (04.07.11 11:09): Brauchen wir das noch?
	public static function &get( $name ) {
		switch ( $name ) {
			case 'ExcludeGroups': return self::$prExcludeGroups;
			case 'ExcludeDeleteGroups': return self::$prExcludeDeleteGroups;
			case 'ExcludeRights': return self::$prExcludeRights;
			case 'CommonRights': return self::$prCommonRights;
			case 'HardPerms': return self::$prHardPerms;
		}
		return null;
	}

	public static function getRegisteredModule( $name ) {
		$vModule = array_key_exists( $name, self::$prRegisteredModules ) ? self::$prRegisteredModules[$name] : false;
		$vModuleClass = array_key_exists( $name, self::$prRegisteredModuleClasses ) ? self::$prRegisteredModuleClasses[$name] : false;
		if ( $vModule !== false ) return $vModule;
		if ( $vModuleClass !== false ) return $vModuleClass;
		return false;
	}

	public static function getRegisteredModules() {
		return array_merge( self::$prRegisteredModules, self::$prRegisteredModuleClasses );
	}

	public static function getRunningModules() {
		return self::$prRunningModules;
	}

	/**
	 * @param $params expects an array with keys 'image' and 'level'
	 */
	public static function registerModule( $name, $params ) {
		self::$prRegisteredModules[$name] = $params;
	}

	public static function registerModuleClass( $name, $params ) {
		self::$prRegisteredModuleClasses[$name] = $params;
	}

	public static function loadModules() {
		if ( !self::$prLoadModulesAndScripts ) return;
		foreach( self::$prRegisteredModules as $name => $params ) {
			self::$prRunningModules[$name] = BsExtensionManager::getExtension( $name );
		}
		foreach( self::$prRegisteredModuleClasses as $name => $params ) {
			self::$prRunningModules[$name] = new $name();
		}
	}

	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		self::$prLoadModulesAndScripts = true;
		$this->mCore->registerPermission( 'wikiadmin', array( 'sysop' ), array( 'type' => 'global' ) );

		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSUserSidebarGlobalActionsWidgets' );
		$this->setHook( 'BSUserSidebarGlobalActionsWidgetGlobalActions' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * extension.json callback
	 */
	public static function onRegistration() {
		$GLOBALS['wgExtensionFunctions'][] = 'WikiAdmin::loadModules';
	}

	/**
	 * Adds WikiAdmin tab to main navigation
	 * @param array $aViews
	 * @param User $oUser
	 * @return boolean
	 */
	public function onBSUserSidebarGlobalActionsWidgets( &$aViews, User $oUser ) {
		if( $oUser->isAnon() ) {
			return true;
		}
		$aRegisteredModules = WikiAdmin::getRegisteredModules();

		$aOutSortable = array();
		foreach ( $aRegisteredModules as $sModuleKey => $aModulParams ) {
			if( empty( $aModulParams['permissions'] ) ) {
				$aModulParams['permissions'] = [ 'wikiadmin' ];
			}
			foreach( $aModulParams['permissions'] as $sPermission ) {
				if( $oUser->isAllowed( $sPermission ) ) {
					continue;
				}
				continue 2;
			}
			$oSpecialPage = SpecialPage::getTitleFor( $sModuleKey );
			$skeyLower = mb_strtolower( $sModuleKey );
			$sMessageKey = 'bs-' . $skeyLower . '-label';
			if( isset( $aModulParams['message'] ) ) {
				$sMessageKey = $aModulParams['message'];
			}
			$sModulLabel = wfMessage( $sMessageKey )->plain();
			$sUrl = $oSpecialPage->getLocalURL( );
			//$sUrl = str_replace( '&', '&amp;', $sUrl );

			if ( !isset( $aModulParams['iconCls'] ) ) {
				$aModulParams['iconCls'] = 'bs-icon-text';
			}

			$sLink = Html::element(
				'a',
				array(
					'id' => 'bs-admin-'.$skeyLower,
					'href' => $sUrl,
					'title' => $sModulLabel,
					'class' => 'bs-admin-link ' . $aModulParams['iconCls']
				),
				$sModulLabel
			);
			$aOutSortable[$sModulLabel] = '<li>'.$sLink.'</li>';
		}

		// Allow other extensions to add to the admin menu
		Hooks::run( 'BSWikiAdminMenuItems', [ &$aOutSortable, $oUser ] );
		if( empty( $aOutSortable ) ) {
			return true;
		}
		ksort( $aOutSortable );

		$sBody = implode( "\n", $aOutSortable );
		$oWidgetView = new ViewWidget();
		$oWidgetView
			->setAdditionalBodyClasses( array( 'bs-nav-links' ) )
			->setTitle( wfMessage( 'bs-wikiadmin-widget-title' )->plain() )
			->setBody( "<ul>$sBody</ul>" )
		;

		$aViews[] = $oWidgetView;
		return true;
	}

	/**
	 * Adds Special:WikiAdmin link to wiki wide widget
	 * @param UserSidebar $oUserSidebar
	 * @param User $oUser
	 * @param array $aLinks
	 * @param string $sWidgetTitle
	 * @return boolean
	 */
	public function onBSUserSidebarGlobalActionsWidgetGlobalActions( UserSidebar $oUserSidebar, User $oUser, &$aLinks, &$sWidgetTitle ) {
		$oSpecialWikiAdmin = SpecialPageFactory::getPage( 'WikiAdmin' );
		if( !$oSpecialWikiAdmin ) {
			return true;
		}
		$aLinks[] = array(
			'target' => $oSpecialWikiAdmin->getPageTitle(),
			'text' => $oSpecialWikiAdmin->getDescription(),
			'attr' => array(),
			'position' => 800,
			'permissions' => array(
				'read',
				'wikiadmin'
			),
		);
		return true;
	}

	/**
	 * Adds CSS to Page
	 *
	 * @param OutputPage $out
	 * @param Skin       $skin
	 *
	 * @return boolean
	 */
	public function onBeforePageDisplay( &$out, &$skin ) {
		$out->addModuleStyles( 'ext.bluespice.wikiadmin.styles' );
		return true;
	}
}
