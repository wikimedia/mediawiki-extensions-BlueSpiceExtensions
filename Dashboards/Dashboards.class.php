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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    2.22.0
 * @package    BlueSpice_Extensions
 * @subpackage Dashboards
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - initial release
*/

/**
 * Base class for Dashboards extension
 * @package BlueSpice_Extensions
 * @subpackage Dashboards
 */
class Dashboards extends BsExtensionMW {

	/**
	 * Contructor of the Dashboards class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'Dashboards',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-dashboards-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Robert Vogel, Stephan Muggli',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
				'bluespice'    => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::Dashboards';

		WikiAdmin::registerModuleClass( 'SpecialAdminDashboard', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_dashboard_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-specialadmindashboard-label'
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

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

	public static function getAdminDashboardConfig() {
		$aPortlets = array();
		wfRunHooks( 'BSDashboardsAdminDashboardPortalPortlets', array( &$aPortlets ) );

		return json_encode( array( 'portlets' => $aPortlets ) );
	}

	public static function getUserDashboardConfig() {
		$aPortlets = array();
		wfRunHooks( 'BSDashboardsUserDashboardPortalPortlets', array( &$aPortlets ) );

		return json_encode( array( 'portlets' => $aPortlets ) );
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

	/**
	 * AjaxDispatcher callback for saving an admin portal config
	 * @return BsCAResponse
	 */
	public static function saveAdminDashboardConfig() {
		$oResponse = BsCAResponse::newFromPermission( 'read' );
		$aPortalConfig = RequestContext::getMain()->getRequest()->getVal( 'portletConfig', '' );

		$oDbw = wfGetDB( DB_MASTER );
		$oDbw->delete(
			'bs_dashboards_configs',
			array( 'dc_type' => 'admin' )
		);
		$oDbw->insert(
			'bs_dashboards_configs',
			array(
				'dc_type' => 'admin',
				'dc_identifier' => '',
				'dc_config' => serialize( $aPortalConfig ),
				'dc_timestamp' => '',
			),
			__METHOD__
		);

		return $oResponse;
	}

	/**
	 * AjaxDispatcher callback for saving a tag portal config
	 * @return BsCAResponse
	 */
	public static function saveTagDashboardConfig( $aPortalConfig ) {
		$aPortalConfig = FormatJson::decode( $aPortalConfig );
		$oResponse = BsCAResponse::newFromPermission('read');
		$oResponse->setPayload( $aPortalConfig );

		return $oResponse;
	}

	/**
	 * AjaxDispatcher callback for getting a list of available portlets
	 * @return BsCAResponse
	 */
	public static function getPortlets() {
		$oResponse = BsCAResponse::newFromPermission('read');

		$aPortlets = array();

		wfRunHooks( 'BSDashboardsGetPortlets', array( &$aPortlets ) );
		//LogPage::validTypes();
		$oResponse->setPayload( $aPortlets );

		return $oResponse;
	}

	public function onBSDashboardsUserDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = array(
			'type'  => 'BS.Dashboards.CalendarPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-dashboard-userportlet-calendar-title' )->plain()
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
		return true;
	}
}