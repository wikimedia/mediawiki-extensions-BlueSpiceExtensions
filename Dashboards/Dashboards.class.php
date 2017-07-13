<?php
/**
 * Dashboards for BlueSpice
 *
 * Provides dashboards for normal users and admins
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage Dashboards
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for Dashboards extension
 * @package BlueSpice_Extensions
 * @subpackage Dashboards
 */
class Dashboards extends BsExtensionMW {
	/**
	 * Initialization of Dashboards extension
	 */
	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		BsConfig::registerVar('MW::Dashboards::UserDashboardOnLogo', false, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL, 'bs-dashboards-pref-userdashboardonlogo', 'toggle');

		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'PersonalUrls' );
		$this->setHook( 'BSDashboardsUserDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsUserDashboardPortalPortlets' );
		//$this->setHook( 'BSInsertMagicAjaxGetData' );

		$this->mCore->registerPermission( 'dashboards-viewspecialpage-userdashboard', array('user'), array( 'type' => 'global' ) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		$oOutputPage->addModules( 'ext.bluespice.dashboards' );

		return true;
	}

	/**
	 * Registers the  <bs:dashboard /> tag
	 * @param Parser $parser
	 * @return boolean Always true to keep Hook running
	 */
	public function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'bs:dashboard', array( $this, 'onTagDasboard' ) );
		return true;
	}

	/**
	 * Adds the table to the database
	 * @param DatabaseUpdater $updater
	 * @return boolean Always true to keep Hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		$updater->addExtensionTable(
			'bs_dashboards_configs',
			__DIR__ .'/db/mysql/bs_dashboards_configs.sql'
		);

		$updater->addPostDatabaseUpdateMaintenance( BSDashBoardsClearConfigMaintenance::class );
		return true;
	}

	public function onPersonalUrls( &$aPersonal_urls, &$oTitle ) {
		$oUser = RequestContext::getMain()->getUser();
		if ( $oUser->isLoggedIn() ) {
			$aPersonal_urls['userdashboard'] = array(
				'href' => SpecialPage::getTitleFor( 'UserDashboard' )->getLocalURL(),
				'text' => SpecialPageFactory::getPage( 'UserDashboard' )->getDescription()
			);
		}

		if ( in_array( 'sysop', $oUser->getGroups() ) ) {
			$aPersonal_urls['admindashboard'] = array(
				'href' => SpecialPage::getTitleFor( 'AdminDashboard' )->getLocalURL(),
				'text' => SpecialPageFactory::getPage( 'AdminDashboard' )->getDescription()
			);
		}

		return true;
	}

	protected static $aPageTagIdentifiers = array();

	/**
	 * Renders <bs:dasboard />
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @param PPFrame $frame
	 * @return string The resulting markup
	 */
	public function onTagDasboard( $input, array $args, Parser $parser, PPFrame $frame ) {
		throw new MWException( 'Not implemented' );

		return Html::element(
			'div',
			array(
				'class' => 'bs-dashboard-tag',
				'data-identifier' => 0
			)
		);
	}

	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if ( $type != 'tags' ) return true;

		$oResponse->result[] = array(
			'id'   => 'bs:dashboard',
			'type' => 'tag',
			'name' => 'dashboard',
			'desc' => wfMessage( 'bs-dashboards-tag-desc' )->plain(),
			'code' => '<bs:dashboard />',
		);

		return true;
	}

	/**
	 * AjaxDispatcher callback for saving a user portal config
	 * @return BsCAResponse
	 */
	public static function saveUserDashboardConfig() {
		$oResponse = BsCAResponse::newFromPermission( 'read' );
		$aPortalConfig = RequestContext::getMain()->getRequest()->getVal( 'portletConfig', '' );

		$oDbw = wfGetDB( DB_MASTER );
		$iUserId = RequestContext::getMain()->getUser()->getId();
		$oDbw->replace(
				'bs_dashboards_configs',
				array(
					'dc_identifier'
				),
				array(
					'dc_type' => 'user',
					'dc_identifier' => $iUserId,
					'dc_config' => serialize( $aPortalConfig ),
					'dc_timestamp' => '',
				),
				__METHOD__
		);

		return $oResponse;
	}

	public function onBSDashboardsUserDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = array(
			'type'  => 'BS.Dashboards.CalendarPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-dashboard-userportlet-calendar-title' )->plain()
			)
		);
		$aPortalConfig[0][] = array(
			'type'  => 'BS.Dashboards.WikiPagePortlet',
			'config' => array(
				'title' => wfMessage( 'bs-dashboard-userportlet-wikipage-title' )->plain()
			)
		);
		return true;
	}

	/**
	 *
	 * @global OutputPage $wgOut
	 * @param type $aPortlets
	 * @return boolean
	 */
	public function onBSDashboardsUserDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = array(
			'type'  => 'BS.Dashboards.CalendarPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-dashboard-userportlet-calendar-title' )->plain(),
			),
			'title' => wfMessage( 'bs-dashboard-userportlet-calendar-title' )->plain(),
			'description' => wfMessage( 'bs-dashboard-userportlet-calendar-description' )->plain()
		);
		$aPortlets[] = array(
			'type'  => 'BS.Dashboards.WikiPagePortlet',
			'config' => array(
				'title' => wfMessage( 'bs-dashboard-userportlet-wikipage-title' )->plain(),
			),
			'title' => wfMessage( 'bs-dashboard-userportlet-wikipage-title' )->plain(),
			'description' => wfMessage( 'bs-dashboard-userportlet-wikipage-description' )->plain()
		);
		return true;
	}

	/**
	 * UnitTestsList allows registration of additional test suites to execute
	 * under PHPUnit. Extensions can append paths to files to the $paths array,
	 * and since MediaWiki 1.24, can specify paths to directories, which will
	 * be scanned recursively for any test case files with the suffix "Test.php".
	 * @param array $paths
	 */
	public static function onUnitTestsList( array &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}
}