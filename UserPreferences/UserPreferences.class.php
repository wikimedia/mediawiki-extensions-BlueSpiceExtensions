<?php

/**
 * UserPreferences Extension for BlueSpice
 *
 * Renders the blue spice tab in preferences
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

 * @author     Sebastian Ulbricht
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage UserPreferences
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// Last review MRG (13.10.10 23:23)


class UserPreferences extends BsExtensionMW {

	public function __construct() {
		wfProfileIn( 'BS::' . __METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME => 'UserPreferences',
			EXTINFO::DESCRIPTION => 'bs-userpreferences-desc',
			EXTINFO::AUTHOR => 'Sebastian Ulbricht, Stephan Muggli',
			EXTINFO::VERSION => 'default',
			EXTINFO::STATUS => 'default',
			EXTINFO::PACKAGE => 'default',
			EXTINFO::URL => 'https://help.bluespice.com/index.php/UserPreferences',
			EXTINFO::DEPS => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::UserPreferences';
		wfProfileOut( 'BS::' . __METHOD__ );
	}

	protected function initExt() {
		wfProfileIn( 'BS::' . __METHOD__ );

		//PW(27.11.2013): ensure that this hook-handler is called first or strange things happen
		$this->setHook( 'GetPreferences', 'onGetPreferences', true );
		$this->setHook( 'UserSaveOptions' );
		$this->setHook( 'BeforePageDisplay' );

		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if ( !SpecialPage::getTitleFor('Preferences') ->equals( $oOutputPage->getTitle() ) ) return true;
		$oOutputPage->addModules('ext.bluespice.userpreferences');

		return true;
	}

	/**
	 * Hook handler for GetPreferences
	 * @param User $user User whose preferences are being modified.
	 * @param Array &$preferences Preferences description array, to be fed to an HTMLForm object
	 * @return true always true to keep hook alive
	 */
	public function onGetPreferences( $user, &$preferences ) {
		$bOrigDeliver = BsConfig::deliverUsersSettings( true );
		$aRegisteredVariables = BsConfig::getRegisteredVars();
		$aSortedVariables = array();

		foreach ( $aRegisteredVariables as $oVariable ) {
			$iOptions = $oVariable->getOptions();

			if ( !( $iOptions & ( BsConfig::LEVEL_USER ) ) ) continue;

			$sExtensionName = $oVariable->getExtension();

			if ( empty( $sExtensionName ) ){
				$sExtensionName = "BASE";
				$sExtensionTranslation = wfMessage( 'CORE' )->plain();
			} else {
				$sExtensionNameLower = strtolower( $sExtensionName );
				$sExtensionTranslation = wfMessage( 'prefs-' . $sExtensionNameLower )->plain();
			}
			$aSortedVariables[$sExtensionTranslation][$sExtensionName][] = $oVariable;
			ksort( $aSortedVariables );
		}

		foreach ( $aSortedVariables as $val ){
			if ( !count( $val ) ) continue;

			foreach ( $val as $sExtensionName => $aSettings ) {
				// if continue, then $oAdapterSetView is not added to output
				if ( !count( $aSettings ) ) continue;
				$sSection = 'bluespice/' . $sExtensionName;

				foreach ( $aSettings as $oVariable ) {
					$field = $oVariable->getFieldDefinition( $sSection );

					if ( $oVariable->getOptions() & BsConfig::USE_PLUGIN_FOR_PREFS ) {
						$oExtension = BsExtensionManager::getExtension( $sExtensionName );
						$tmp = $oExtension->runPreferencePlugin( 'MW', $oVariable );

						$field = array_merge( $field, $tmp );
					}

					$preferences[$oVariable->generateFieldId()] = $field;
				}
			}
		}
		BsConfig::deliverUsersSettings( $bOrigDeliver );

		return true;
	}

	/**
	 * Hook handler for UserLoadOptions
	 * @param User $user User whose options are being modified.
	 * @param Array &$options Options array
	 * @return true always true to keep hook alive
	 */
	public static function onUserLoadOptions( $user, &$options ) {

		foreach ( $options as $key => $value ) {
			if ( strpos( $key, 'MW::' ) !== 0 ) {
				continue;
			}
			if ( BsStringHelper::isSerialized( $value ) ) {
				$options[$key] = unserialize( $value );
			}
			BsConfig::set( $key, $options[$key], true );
		}

		return true;
	}

	/**
	 * Hook handler for UserSaveOptions
	 * @param User $user User whose options are being modified.
	 * @param Array &$options Options array
	 * @return true always true to keep hook alive
	 */
	public function onUserSaveOptions( $user, &$options ) {
		BsConfig::loadSettings();

		$oCurrentTitle = $this->getTitle(); //May return null in CLI
		if ( $oCurrentTitle instanceof Title && $oCurrentTitle->isSpecialPage() ) {
			$bDeliverUserSettings = true;
		} else {
			$bDeliverUserSettings = false;
		}
		$bOrigDeliver = BsConfig::deliverUsersSettings( $bDeliverUserSettings );

		$aRegisteredVariables = BsConfig::getRegisteredVars();
		$aSortedVariables = array();

		foreach ( $aRegisteredVariables as $oVariable ) {
			$iOptions = $oVariable->getOptions();

			if ( !( $iOptions & ( BsConfig::LEVEL_USER ) ) ) continue;

			if ( $bDeliverUserSettings === false ) {
				if ( $iOptions & BsConfig::NO_DEFAULT ) continue;
			}

			$sAdapterName = strtoupper( $oVariable->getAdapter() );
			$sExtensionName = $oVariable->getExtension();
			if ( empty( $sExtensionName ) ) $sExtensionName = 'BASE';
			$aSortedVariables[ $sAdapterName ][ $sExtensionName ][ ] = $oVariable;
		}

		foreach ( $aSortedVariables as $sAdapterName => $aExtensions ) {
			if ( !count( $aExtensions ) ) continue;

			foreach ( $aExtensions as $sExtensionName => $aSettings ) {
				// if continue, then $oAdapterSetView is not added to output
				if ( !count( $aSettings ) ) continue;

				foreach ( $aSettings as $oVariable ) {

					//Avoid "undefined index" notices and weird NULL values in settings
					$value = $oVariable->getValue();

					if ( isset( $options[ $oVariable->generateFieldId() ] ) ) { //Set but no bool
						$value = $options[ $oVariable->generateFieldId() ];
					}
					if ( isset( $options[ $oVariable->getKey() ] ) ) { //Set but no bool
						$value = $options[ $oVariable->getKey() ];
					}


					$options[$oVariable->getKey()] = ( BsStringHelper::isSerialized( $value ) ) ? $value : serialize( $value );
					unset( $options[$oVariable->generateFieldId()] );
				}
			}
		}
		BsConfig::deliverUsersSettings( $bOrigDeliver );

		return true;
	}

}
