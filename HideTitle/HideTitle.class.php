<?php
/**
 * BlueSpice for MediaWiki
 * Extension: HideTitle
 * Description: Tag to hide the title of an article.
 * Authors: Markus Glaser, Sebastian Ulbricht
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
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage StateBar
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class HideTitle extends BsExtensionMW {

	protected $bHideTitle = false;

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