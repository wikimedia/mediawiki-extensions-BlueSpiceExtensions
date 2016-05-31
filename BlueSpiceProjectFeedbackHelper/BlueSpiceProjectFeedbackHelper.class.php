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
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage BlueSpiceProjectFeedbackHelper
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class BlueSpiceProjectFeedbackHelper extends BsExtensionMW {
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'BlueSpiceProjectFeedbackHelper',
			EXTINFO::DESCRIPTION => 'bs-bluespiceprojectfeedbackhelper-desc',
			EXTINFO::AUTHOR      => 'Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'https://help.bluespice.com/index.php/BlueSpiceProjectFeedbackHelper',
			EXTINFO::DEPS        => array(
				'bluespice' => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::BlueSpiceProjectFeedbackHelper';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		BsConfig::registerVar(
			'MW::BlueSpiceProjectFeedbackHelper::Active',
			true,
			BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL,
			'bs-bluespiceprojectfeedbackhelper-active',
			'toggle'
		);

		$bActive = BsConfig::get(
			'MW::BlueSpiceProjectFeedbackHelper::Active'
		);
		if( !$bActive ) {
			wfProfileOut( 'BS::'.__METHOD__ );
			return;
		}

		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'SkinAfterContent' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if
	 * needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		$oOutputPage->addModules(
			'ext.bluespice.blueSpiceprojectfeedbackhelper'
		);

		return true;
	}

	/**
	 * @param string $sData
	 * @param Skin $oSkin
	 * @return boolean
	 */
	public function onSkinAfterContent( &$sData, $oSkin ) {
		if ( !$oSkin->getUser()->isAllowed('wikiadmin') ) {
			return true;
		}

		$oView = new ViewBlueSpiceProjectFeedbackHelperPanel();

		$sData .= $oView->execute();
		return true;
	}
}