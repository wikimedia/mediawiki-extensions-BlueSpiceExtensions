<?php
/**
 * This is the Preferences class.
 *
 * The Preferences offers an easy way to manage the settings of BlueSpice.
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
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage Preferences
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// Last review MRG (01.07.11 13:56)

/* Changelog
 *
 */

/**
 * the Preferences class
 * @package BlueSpice_Extensions
 * @subpackage Preferences
 */
class BsPreferences extends BsExtensionMW {

	/**
	 * contructor of the BsPreferences class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME => 'Preferences',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-preferences-desc' )->escaped(),
			EXTINFO::AUTHOR => 'Sebastian Ulbricht, Stephan Muggli',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array('bluespice' => '2.22.0')
		);

		WikiAdmin::registerModule( 'Preferences', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_einstellungen_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-preferences-label'
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * returns the formular for Preferences
	 * @return string the formular string
	 */
	public function getForm() {
		if ( wfReadOnly() ) {
			throw new ReadOnlyError;
		}

		$this->getOutput()->addModuleScripts( 'ext.bluespice.preferences' );
		$this->getOutput()->addHTML( '<br />' );

		$oRequest = $this->getRequest();
		if ( $this->getRequest()->getVal( 'success' ) == true ) {
			$this->getOutput()->wrapWikiMsg(
				'<div class="successbox"><strong>$1</strong></div><div id="mw-pref-clear"></div>'."\n",
				'savedprefs'
			);
		}

		$orig_deliver = BsConfig::deliverUsersSettings( false );

		BsConfig::loadSettings();
		BsExtensionManager::getExtensionInformation();

		$vars = BsConfig::getRegisteredVars();

		$bShowall = $oRequest->getFuzzyBool( 'showall' );
		if ( $bShowall ) {
			$out = '';
			foreach ( $vars as $var ) {
				$out .= $var->getAdapter() . "::";
				if ( $var->getExtension() !== null ) {
					$out .= $var->getExtension() . "::";
				}
				$out .= $var->getName() . "<br>";
			}

			return $out;
		}

		$preferences = array();
		$aSortedVariables = array();

		foreach ( $vars as $var ) {
			$options = $var->getOptions();
			if ( !( $options & ( BsConfig::LEVEL_PUBLIC | BsConfig::LEVEL_USER ) ) ) {
				continue;
			}
			if ( $options & BsConfig::NO_DEFAULT ) continue;
			$extension = $var->getI18nExtension() ? $var->getI18nExtension() : 'BASE';
			$aSortedVariables[$extension][] = $var;
		}

		foreach ( $aSortedVariables as $sExtensionName => $aExtensions ) {
			if ( !count( $aExtensions ) ) continue;

			foreach ( $aExtensions as $oVariable ) {
				// if continue, then $oAdapterSetView is not added to output
				if ( !count( $oVariable ) ) continue;
				$sSection = $sExtensionName;
				$oExtension = BsExtensionManager::getExtension( $sExtensionName );
				$field = $oVariable->getFieldDefinition( $sSection );

				if ( $oVariable->getOptions() & BsConfig::USE_PLUGIN_FOR_PREFS ) {

					$oExtension = BsExtensionManager::getExtension( $sExtensionName );
					$tmp = $oExtension->runPreferencePlugin( 'MW', $oVariable );

					$field = array_merge( $field, $tmp );
				}
				$preferences[$oVariable->generateFieldId()] = $field;

			}
		}
		BsConfig::deliverUsersSettings( $orig_deliver );

		$oForm = new HTMLFormEx( $preferences, 'prefs' );
		$oForm->setTitle( $this->getTitle() );
		$oForm->addHiddenField( 'mode', 'Preferences' );
		$oForm->setSubmitText( wfMessage( 'bs-extjs-save' )->plain() );
		$oForm->setSubmitName( 'WikiAdminPreferencesSubmit' );
		$oForm->setSubmitCallback( array( $this, 'savePreferences' ) );

		$oForm->show();

		$this->getOutput()->addHTML( '<br />' );

		return '';
	}

	/**
	 * saves the settings to the database
	 * @param array $aData an associative array of fieldnames and values
	 */
	public function savePreferences( $aData ) {
		if ( wfReadOnly() ) {
			$url = SpecialPage::getTitleFor( 'WikiAdmin' )->getFullURL( array(
				'mode' => 'Preferences',
				'success' => 0
			) );
			$this->getOutput()->redirect( $url );

			return false;
		}

		$vars = BsConfig::getRegisteredVars();
		foreach ( $vars as $var ) {
			$options = $var->getOptions();
			if ( !( $options & ( BsConfig::LEVEL_PUBLIC | BsConfig::LEVEL_USER ) ) ) {
				continue;
			}
			if ( $options & BsConfig::NO_DEFAULT ) continue;

			$name = $this->generateFieldId( $var );
			$value = isset( $aData[$name] ) ? $aData[$name] : NULL;
			if ( ( $var->getOptions() & BsConfig::TYPE_BOOL ) && $value == NULL ) {
				BsConfig::set($var->getKey(), 0, true);
			}

			$bReturn = true;

			wfRunHooks( 'BSWikiAdminPreferencesBeforeSetVariable', array( $this, &$var, &$value, &$bReturn ) );

			if ( $value !== null && $bReturn !== false ) {
				BsConfig::set( $var->getKey(), $value, true );
			}
		}

		BsConfig::saveSettings();

		$url = SpecialPage::getTitleFor( 'WikiAdmin' )->getFullURL( array(
			'mode' => 'Preferences',
			'success' => 1
		) );
		$this->getOutput()->redirect( $url );

		return false;
	}

	/**
	 * generates an id string for a BsCOnfig instance for usage in html code
	 * @param BsConfig $var
	 * @return string the field id
	 */
	protected function generateFieldId( $var ) {
		return $var->getAdapter() . "_" . $var->getExtension() . "_" . $var->getName();
	}

	public static function onBeforePageDisplay( OutputPage &$out, &$skin ) {
		if( !$out->getTitle()->isSpecial( 'WikiAdmin' ) ) return true;
		if( strtolower( $out->getRequest()->getVal( 'mode' )  ) != 'preferences' ) return true;

		$out->addInlineStyle(
			'.bs-prefs legend{cursor:pointer;}'
		);

		return true;
	}

}