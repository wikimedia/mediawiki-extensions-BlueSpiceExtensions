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
 * @version    1.22.0
 * @version    $Id: BlueSpiceProjectFeedbackHelper.class.php 9753 2013-06-14 14:27:33Z tweichart $
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
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['BlueSpiceProjectFeedbackHelper'] = dirname( __FILE__ ) . '/BlueSpiceProjectFeedbackHelper.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'BlueSpiceProjectFeedbackHelper',
			EXTINFO::DESCRIPTION => 'Provides a fixed \'submit bugs\' panel at the bottom of the user interface.',
			EXTINFO::AUTHOR      => 'Robert Vogel',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9753 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
				'bluespice' => '1.22.0'
			)
		);
		$this->mExtensionKey = 'MW::BlueSpiceProjectFeedbackHelper';

		$this->registerView( 'ViewBlueSpiceProjectFeedbackHelperPanel' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		BsConfig::registerVar( 'MW::BlueSpiceProjectFeedbackHelper::Active', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-bluespiceprojectfeedbackhelper-active', 'toggle' );
		
		if( BsConfig::get('MW::BlueSpiceProjectFeedbackHelper::Active') == false ) {
			wfProfileOut( 'BS::'.__METHOD__ );
			return;
		}
		$this->setHook('BeforePageDisplay');
		$this->setHook( 'BSBlueSpiceSkinAfterArticleContent', 'onAfterArticleContent' );
		
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'BlueSpiceProjectFeedbackHelper', $this, 'disableFeedback', 'edit' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}
	
	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if( BsConfig::get('MW::BlueSpiceProjectFeedbackHelper::Active') == false )
			return true;
		$oOutputPage->addModules('ext.bluespice.blueSpiceprojectfeedbackhelper');

		return true;
	}

	/**
	 * 
	 * @param array $aViews
	 * @param User $oCurrentUser
	 * @param Title $oCurrentTitle
	 * @return boolean
	 */
	public function onAfterArticleContent( &$aViews, $oCurrentUser, $oCurrentTitle ) {
		if( BsConfig::get('MW::BlueSpiceProjectFeedbackHelper::Active') == false ) return true;
		if( !in_array( 'sysop', $oCurrentUser->getGroups() ) ) return true;
		$aViews[] = new ViewBlueSpiceProjectFeedbackHelperPanel();
		return true;
	}
	
	public function disableFeedback( &$output ) {
		$oResult = new stdClass();
		$oResult->success = false;
		
		BsConfig::set('MW::BlueSpiceProjectFeedbackHelper::Active', false);
		BsConfig::saveSettings();
		$oResult-> success = true;
		
		$output = json_encode( $oResult );
		return $output;
	}
}