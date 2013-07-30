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
 * @version    1.22.0
 * @version    $Id: Preferences.class.php 9745 2013-06-14 12:09:29Z pwirth $
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
			EXTINFO::DESCRIPTION => 'Offers the possibility to admins, to configurate the whole wiki from a single SpecialPage',
			EXTINFO::AUTHOR => 'Sebastian Ulbricht, Stephan Muggli',
			EXTINFO::VERSION => '1.22.0 ($Rev: 9745 $)',
			EXTINFO::STATUS => 'stable',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array('bluespice' => '1.22.0')
		);

		WikiAdmin::registerModule( 'Preferences', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/images/bs-btn_einstellungen_v1.png',
			'level' => 'wikiadmin'
		) );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'WikiAdmin::Preferences', $this, 'validate', 'wikiadmin' );
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

		$oRequest = $this->getRequest();
		if ( $this->getRequest()->getVal( 'success' ) == true ) {
			$this->getOutput()->wrapWikiMsg(
				'<div class="successbox"><strong>$1</strong></div><div id="mw-pref-clear"></div>'."\n",
				'savedprefs'
			);
		}

		$orig_deliver = BsConfig::deliverUsersSettings( false );

		BsConfig::loadSettings();
		BsExtensionManager::getExtensionInformations();

		$vars = BsConfig::getRegisteredVars();

		$bShowall = $oRequest->getFuzzyBool( 'showall' );
		if ( $bShowall ) {
			$out = '';
			foreach ( $vars as $var ) {
				$out .= $var->getAdapter() . "::" . $var->getExtension() . "::" . $var->getName() . "<br>";
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
			$adapter = strtoupper( $var->getAdapter() );
			$extension = $var->getI18nExtension() ? $var->getI18nExtension() : 'BASE';
			$aSortedVariables[$adapter][$extension][] = $var;
		}

		foreach ( $aSortedVariables as $sAdapterName => $aExtensions ) {
			if ( !count( $aExtensions ) ) continue;
			// TODO MRG (01.07.11 14:06): Das versteh ich nicht. Was ist denn hier gemeint? Und warum hardgecoded?
			// TODO SU (03.07.11 16:58): @MRG Das dient der Sortierung im Formular.
			// Jede Sektion oder Untersektion ist ein Fieldset und bluespice deshalb, weil es ein BlueSpice-
			// Formluar ist. bluespice ist in diesem Fall die Wurzel aller Fieldsets.
			$sBaseSection = 'bluespice/' . $sAdapterName;

			foreach ( $aExtensions as $sExtensionName => $aSettings ) {
				// if continue, then $oAdapterSetView is not added to output
				if ( !count( $aSettings ) ) continue;
				$sSection = $sBaseSection . '/' . $sExtensionName;
				$oExtension = BsExtensionManager::getExtension( $sExtensionName );

				foreach ( $aSettings as $oVariable ) {
					$field = array(
						'type' => $oVariable->getFieldMapping(),
						'label-message' => $oVariable->getI18nName(), // a system message
						'section' => $sSection,
						'default' => $oVariable->getValue()
					);
					// TODO MRG (01.07.11 14:08): Title und message ist für den Dialog?
					// TODO SU (03.07.11 17:00): @MRG ja genau
					if ( $oVariable->getFieldMapping() == 'multiselectplusadd' ) {
						$field['options'] = $oVariable->getValue();
						$field['title'] = 'toc-' . $oVariable->getName() . '-title';
						$field['message'] = 'toc-' . $oVariable->getName() . '-message';
					}
					if ( $oVariable->getOptions() & BsConfig::USE_PLUGIN_FOR_PREFS ) {
						$tmp = NULL;
						if ( $sExtensionName == 'BASE' ) {
							#$tmp = $this->mAdapter->runPreferencePlugin( 'MW', $oVariable ); Never reached @TODO remove me
						} else {
							$oExtension = BsExtensionManager::getExtension( $sExtensionName );
							$tmp = $oExtension->runPreferencePlugin( 'MW', $oVariable );
						}
						$field = array_merge( $field, $tmp );
					}
					$preferences[$oVariable->generateFieldId()] = $field;
				}
			}
		}
		BsConfig::deliverUsersSettings( $orig_deliver );

		BsCore::loadHtmlFormClass();

		$oForm = new HTMLFormEx( $preferences, 'prefs' );
		$oForm->setTitle( $this->getTitle() );
		$oForm->addHiddenField( 'mode', 'Preferences' );
		$oForm->setSubmitText( wfMessage( 'bs-preferences-button_save' )->plain() );
		$oForm->setSubmitName( 'WikiAdminPreferencesSubmit' );
		$oForm->setSubmitCallback( array( $this, 'savePreferences' ) );

		$oForm->show();

		BsConfig::loadUserSettings( $this->getUser()->getName() );

		return '';
	}

	/**
	 * saves the settings to the database
	 * @param array $aData an associative array of fieldnames and values
	 */
	public function savePreferences( $aData ) {
		if ( wfReadOnly() ) {
			$url = SpecialPage::getTitleFor( 'SpecialWikiAdmin' )->getFullURL( array(
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

		$url = SpecialPage::getTitleFor( 'SpecialWikiAdmin' )->getFullURL( array(
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

}