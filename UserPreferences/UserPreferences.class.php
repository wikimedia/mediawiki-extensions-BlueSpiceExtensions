<?php

/**
 * blue spice for MediaWiki
 * Extension: UserPreferences
 * Description: Renders the blue spice tab in preferences
 * Authors: Sebastian Ulbricht
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
 * $LastChangedDate: 2013-06-14 14:09:29 +0200 (Fr, 14 Jun 2013) $
 * $LastChangedBy: pwirth $
 * $Rev: 9745 $

 */
/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * v1.0.0
 * - raised to stable
 * v0.3.0b
 * - Code Review (2010-10-01)
 * - Refactored Code for compliance to new coding conventions
 * v0.2.0b
 * - Refactored / beautified code
 * - Using new database table scheme from WhosOnline Extension
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
			EXTINFO::DESCRIPTION => 'Renders the BlueSpice tab in preferences.',
			EXTINFO::AUTHOR => 'Sebastian Ulbricht, Stephan Muggli',
			EXTINFO::VERSION => '2.22.0',
			EXTINFO::STATUS => 'beta',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::UserPreferences';
		wfProfileOut( 'BS::' . __METHOD__ );
	}

	protected function initExt() {
		wfProfileIn( 'BS::' . __METHOD__ );

		$this->setHook( 'GetPreferences' );
		$this->setHook( 'UserLoadOptions' );
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
		if( !SpecialPage::getTitleFor('Preferences') ->equals( $oOutputPage->getTitle() ) ) return true;
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
		BsConfig::loadUserSettings( $user );
		BsCore::loadHtmlFormClass();

		$bOrigDeliver = BsConfig::deliverUsersSettings( true );
		$aRegisteredVariables = BsConfig::getRegisteredVars();
		$aSortedVariables = array();

		foreach ( $aRegisteredVariables as $oVariable ) {
			$iOptions = $oVariable->getOptions();

			if ( !( $iOptions & ( BsConfig::LEVEL_USER ) ) )
				continue;

			$sAdapterName = strtoupper( $oVariable->getAdapter() );
			$sExtensionName = $oVariable->getExtension();

			if ( empty( $sExtensionName ) ){
				$sExtensionName = "BASE";
				$sExtensionTranslation = wfMessage( 'CORE' )->plain();
			} else {
				$sExtensionNameLower = strtolower( $sExtensionName );
				$sExtensionTranslation = wfMessage( 'prefs-' . $sExtensionNameLower )->plain();
			}
			$aSortedVariables[ $sAdapterName ][$sExtensionTranslation][ $sExtensionName][ ] = $oVariable;
			ksort($aSortedVariables[$sAdapterName]);
		}

		foreach ( $aSortedVariables as $sAdapterName => $aExtensions ) {
			if ( !count( $aExtensions ) )continue;
			$sBaseSection = 'bluespice/' . $sAdapterName;

			foreach ($aExtensions as $val){
				if ( !count( $val ) ) continue;

				foreach ( $val as $sExtensionName => $aSettings ) {
					// if continue, then $oAdapterSetView is not added to output
					if ( !count( $aSettings ) ) continue;
					$sSection = $sBaseSection . '/' . $sExtensionName;

					foreach ( $aSettings as $oVariable ) {
						$field = array(
							'type' => $oVariable->getFieldMapping(),
							'label-message' => $oVariable->getI18nName(), // a system message
							'section' => $sSection,
							'default' => $oVariable->getValue()
						);

						if ( $oVariable->getFieldMapping() == 'multiselectplusadd' ) {
							$field[ 'options' ] = $oVariable->getValue();
							$field[ 'title' ] = 'toc-' . $oVariable->getName() . '-title';
							$field[ 'message' ] = 'toc-' . $oVariable->getName() . '-message';
						}

						if ( $oVariable->getOptions() & BsConfig::USE_PLUGIN_FOR_PREFS ) {
							$tmp = NULL;
							if ( $sExtensionName == 'BASE' ) {
								#$tmp = $this->mAdapter->runPreferencePlugin( 'MW', $oVariable ); @TODO remove me
							} else {
								$oExtension = BsExtensionManager::getExtension( $sExtensionName );
								$tmp = $oExtension->runPreferencePlugin( 'MW', $oVariable );
							}
							$field = array_merge( $field, $tmp );
						}

						$preferences[ $oVariable->generateFieldId() ] = $field;
					}
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
	public function onUserLoadOptions( $user, &$options ) {
		$aRegisteredVariables = BsConfig::getRegisteredVars();
		$aSortedVariables = array();

		foreach ( $aRegisteredVariables as $oVariable ) {
			$iOptions = $oVariable->getOptions();

			if ( !( $iOptions & ( BsConfig::LEVEL_USER ) ) )continue;

			$sAdapterName = strtoupper( $oVariable->getAdapter() );
			$sExtensionName = $oVariable->getExtension();

			if ( empty( $sExtensionName ) ) $sExtensionName = 'BASE';

			$aSortedVariables[ $sAdapterName ][ $sExtensionName ][ ] = $oVariable;
		}

		foreach ( $aSortedVariables as $sAdapterName => $aExtensions ) {
			if ( !count( $aExtensions ) ) continue;

			foreach ( $aExtensions as $sExtensionName => $aSettings ) {
				if ( !count( $aSettings ) ) continue;

				foreach ( $aSettings as $oVariable ) {
					$id = $oVariable->getKey();
					if ( isset( $options[ $id ] ) ) {
						$options[ $id ] = unserialize( $options[ $id ] );
						BsConfig::set( $id, $options[ $id ] );
					}
				}
			}
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

		$oCurrentTitle = $this->getContext()->getTitle(); //May return null in CLI
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

					if( isset( $options[ $oVariable->generateFieldId() ] ) ) { //Set but no bool
						$value = $options[ $oVariable->generateFieldId() ];
					}
					if( isset( $options[ $oVariable->getKey() ] ) ) { //Set but no bool
						$value = $options[ $oVariable->getKey() ];
					}

					$options[ $oVariable->getKey() ] = serialize( $value );
					unset( $options[ $oVariable->generateFieldId() ] );
				}
			}
		}
		BsConfig::deliverUsersSettings( $bOrigDeliver );

		return true;
	}

}
