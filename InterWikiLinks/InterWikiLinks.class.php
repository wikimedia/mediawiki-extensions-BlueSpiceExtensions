<?php
/**
 * InterWiki Links extension for BlueSpice MediaWiki
 *
 * Administration interface for adding, editing and deleting interwiki links
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @version    2.27
 * @package    BlueSpice_Extensions
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Main class for InterWikiLinks extension
 * @package BlueSpice_Extensions
 * @subpackage InterWikiLinks
 */
class InterWikiLinks extends BsExtensionMW {

	/**
	 * Constructor of InterWikiLinks class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		WikiAdmin::registerModule( 'InterWikiLinks', [
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_interwikilinks_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-interwikilinks-label',
			'iconCls' => 'bs-icon-chain',
			'permissions' => [ 'interwikilinks-viewspecialpage' ],
		]);
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	protected function initExt() {
		wfProfileIn('BS::InterWikiLinks::initExt');

		$this->setHook( 'BeforePageDisplay' );

		$this->mCore->registerPermission( 'interwikilinks-viewspecialpage', array('sysop'), array( 'type' => 'global' ) );

		wfProfileOut('BS::InterWikiLinks::initExt');
	}

	/**
	 *
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return boolean - always true
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if( !in_array($oOutputPage->getRequest()->getVal('action', 'view'), array('edit', 'submit')) ) return true;
		$oOutputPage->addModules('bluespice.insertLink.interWikiLinks');
		//TODO implement ow
		$oOutputPage->addJsConfigVars( 'BSInterWikiPrefixes', $this->getInterWikiLinkPrefixes() );

		return true;
	}

	public static function getInterWikiLinkPrefixes() {
		$oDbr = wfGetDB( DB_REPLICA );
		$rRes = $oDbr->select(
				'interwiki',
				'iw_prefix',
				'',
				'',
				array( "ORDER BY" => "iw_prefix" )
		);

		$aInterWikiPrefixes = array();
		while( $o = $oDbr->fetchObject($rRes) ) $aInterWikiPrefixes[] = $o->iw_prefix;

		return $aInterWikiPrefixes;
	}

	public static function purgeTitles($iw_prefix) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'iwlinks',
			array('iwl_from', 'iwl_prefix'),
			array('iwl_prefix' => $iw_prefix)
		);

		foreach( $res as $row ) {
			$oTitle = Title::newFromID( $row->iwl_from );
			if( $oTitle instanceof Title == false ) continue;
			$oTitle->invalidateCache();
		}
	}

	/**
	 * UnitTestsList allows registration of additional test suites to execute
	 * under PHPUnit. Extensions can append paths to files to the $paths array,
	 * and since MediaWiki 1.24, can specify paths to directories, which will
	 * be scanned recursively for any test case files with the suffix "Test.php".
	 * @param array $paths
	 */
	public static function onUnitTestsList ( array &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}

}
