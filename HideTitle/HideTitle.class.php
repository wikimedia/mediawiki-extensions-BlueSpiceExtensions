<?php
/**
  * blue spice for MediaWiki
  * Extension: HideTitle
  * Description: Tag to hide the title of an article.
  * Authors: Markus Glaser, Sebastian Ulbricht
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
 *
 * v0.1
 * - initial release
 */

class HideTitle extends BsExtensionMW {

	protected $bHideTitle = false;

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'HideTitle',
			EXTINFO::DESCRIPTION => 'Tag to hide the title of an article.',
			EXTINFO::AUTHOR      => 'Markus Glaser',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array('bluespice' => '2.22.0')
		);
		$this->mExtensionKey = 'MW::HideTitle';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Hooks
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		$this->mCore->registerBehaviorSwitch( 'bs_hidetitle' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}
	
	/**
	 *
	 * @param OutputPage $oOutputPage
	 * @param SkinTemplate $oSkinTemplate
	 * @return boolean 
	 */
	public function onBeforePageDisplay(  $oOutputPage, $oSkinTemplate ) {
		$oTitle = $oOutputPage->getTitle();
		$sHideTitlePageProp = BsArticleHelper::getInstance( $oTitle )->getPageProp( 'bs_hidetitle' );
		if( $sHideTitlePageProp === '' ) {
			$oOutputPage->mPagetitle = '';
			$oOutputPage->addInlineScript( "$('.firstHeading').remove()" );
		}		
		
		return true;
	}
	
	public function onBSInsertMagicAjaxGetData( $oResponse, $type ) { 
		if( $type !== 'switches' ) return true;
		$oResponse->result[] = array(
			'id' => 'bs:hidetitle',
			'type' => 'switch',
			'name' => 'HIDETITLE',
			'desc' => wfMessage( 'bs-hidetitle-extension-description' )->plain(),
			'code' => '__HIDETITLE__',
		);
		return true;
	}
}