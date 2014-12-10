<?php
/**
 * ExtensionInfo extension for BlueSpice
 *
 * Information about active Hallo Welt! extensions.
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
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage ExtensionInfo
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
* v1.20.0:
 *
 * v1.0:
 * - Using ExtJS to render table
 * - Grouping and sorting is now possible
 * v0.1.1b style-information (table) moved to view.ExtensionInfoTable.php and ExtensionInfo.css
 * v0.1
 * - initial commit
 */

/**
 * Base class for ExtensionInfo extension
 * @package BlueSpice_Extensions
 * @subpackage ExtensionInfo
 */
class ExtensionInfo extends BsExtensionMW {

	/**
	 * Constructor of ExtensionInfo class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'ExtensionInfo',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-extensioninfo-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE	 => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);

		WikiAdmin::registerModule( 'ExtensionInfo', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_information_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-extensioninfo-label'
		) );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Renders the main form. Called by WikiAdmin
	 * @return string rendered HTML
	 */
	public function getForm() {
		$this->getOutput()->addModuleStyles( 'ext.bluespice.extensioninfo.styles' );
		$this->getOutput()->addModules( 'ext.bluespice.extensioninfo' );
		BsExtensionManager::setContext( 'MW::ExtensionInfoShow' );
		$oViewExtensionInfoTable = new ViewExtensionInfoTable();

		$aInfos = BsExtensionManager::getExtensionInformation();
		ksort( $aInfos );

		$oViewExtensionInfoTable->setExtensions( $aInfos );

		return $oViewExtensionInfoTable->execute();
	}

}
