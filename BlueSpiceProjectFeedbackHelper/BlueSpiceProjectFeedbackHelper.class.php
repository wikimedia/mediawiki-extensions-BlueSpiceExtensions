<?php

/**
 * BlueSpiceProjectFeedbackHelper extension for BlueSpice
 *
 * Provides a fixed 'submit bugs' panel at the bottom of the user interface.
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
 * @version    2.22.0
 * @package    BlueSpice_Extensions
 * @subpackage BlueSpiceProjectFeedbackHelper
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * - Only show to Sysops
 * - Added Link to feedback form
 * v0.1.0b
 * FIRST CHANGES
*/

class BlueSpiceProjectFeedbackHelper extends BsExtensionMW {
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'BlueSpiceProjectFeedbackHelper',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-bluespiceprojectfeedbackhelper-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
				'bluespice' => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::BlueSpiceProjectFeedbackHelper';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		BsConfig::registerVar( 'MW::BlueSpiceProjectFeedbackHelper::Active', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-bluespiceprojectfeedbackhelper-active', 'toggle' );

		if ( BsConfig::get( 'MW::BlueSpiceProjectFeedbackHelper::Active' ) == false ) {
			wfProfileOut( 'BS::'.__METHOD__ );
			return;
		}

		$this->setHook( 'BeforePageDisplay' );
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
		if ( BsConfig::get('MW::BlueSpiceProjectFeedbackHelper::Active') == false ) {
			return true;
		}
		$oOutputPage->addModules('ext.bluespice.blueSpiceprojectfeedbackhelper');

		return true;
	}

	/**
	 * @param SkinTemplate $sktemplate a collection of views. Add the view that needs to be displayed
	 * @param BaseTemplate $tpl currently logged in user. Not used in this context.
	 * @return bool always true
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		if ( BsConfig::get( 'MW::BlueSpiceProjectFeedbackHelper::Active' ) == false ) return true;
		if ( !in_array( 'sysop', $sktemplate->getUser()->getGroups() ) ) {
			return true;
		}
		$oView = new ViewBlueSpiceProjectFeedbackHelperPanel();

		if( isset( $tpl->data['dataAfterContent'] ) ) {
			$tpl->data['dataAfterContent'] .= $oView->execute();
		} else {
			$tpl->data['dataAfterContent'] = $oView->execute();
		}

		return true;
	}

	public static function disableFeedback() {
		$oResult = (object) array(
			'success' => false,
			'message' => '',
		);
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) {
			//PW TODO: add error message
			return FormatJson::encode( $oResult );
		}

		BsConfig::set( 'MW::BlueSpiceProjectFeedbackHelper::Active', false );
		BsConfig::saveSettings();
		$oResult->success = true;

		return FormatJson::encode( $oResult );
	}
}