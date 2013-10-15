<?php

/**
  * blue spice for MediaWiki
  * Extension: WikiAdmin
  * Description: Central point of administration for blue spice
  * Authors: Central point of administration for blue spice
  *
  * Copyright (C) 2010 Hallo Welt! � Medienwerkstatt GmbH, All rights reserved.
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
  * http://www.gnu.org/copyleft/gpl.html
  *
  * For further information visit http://www.blue-spice.org
  * 
  * Version information
  * $LastChangedDate: 2013-06-25 11:17:54 +0200 (Di, 25 Jun 2013) $
  * $LastChangedBy: rvogel $
  * $Rev: 9912 $

  */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * v0.1.0
 * FIRST CHANGES
 */
  
 // Last review: (01.07.11 01:58)

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
		'proxyunbannable',
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
			case 'ExcludeGroups' :        return self::$prExcludeGroups;
				break;
			case 'ExcludeDeleteGroups' :  return self::$prExcludeDeleteGroups;
				break;
			case 'ExcludeRights' :        return self::$prExcludeRights;
				break;
			case 'CommonRights' :         return self::$prCommonRights;
				break;
			case 'HardPerms' :            return self::$prHardPerms;
				break;
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
			self::$prRunningModules[$name] =& BsExtensionManager::getExtension( $name );
		}
		foreach( self::$prRegisteredModuleClasses as $name => $params ) {
			self::$prRunningModules[$name] = new $name();
		}
	}

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'WikiAdmin',
			EXTINFO::DESCRIPTION => 'Central point of administration for BlueSpice',
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht, Mathias Scheer',
			EXTINFO::VERSION     => '1.22.0',
			EXTINFO::STATUS      => 'beta',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array('bluespice' => '1.22.0')
		);
		$this->mExtensionKey = 'MW::WikiAdmin';

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		self::$prLoadModulesAndScripts = true;

		wfProfileOut( 'BS::'.__METHOD__ );
	}
}