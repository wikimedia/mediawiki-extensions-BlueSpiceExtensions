<?php
/**
 * ExtendedEditBar extension for BlueSpice
 *
 * Provides additional buttons to the wiki edit field.
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
 * @author     MediaWiki Extension
 * @version    1.22.0 stable
 * @version    $Id: ExtendedEditBar.class.php 9745 2013-06-14 12:09:29Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedEditBar
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * 
 * v1.0.0
 * - raised to stable
 * v0.1
 * - initial release
 */

/**
 * Base class for ExtendedEditBar extension
 * @package BlueSpice_Extensions
 * @subpackage ExtendedEditBar
 */
class ExtendedEditBar extends BsExtensionMW {

	/**
	 * Constructor of ExtendedEditBar class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['ExtendedEditBar'] = dirname( __FILE__ ) . '/ExtendedEditBar.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'ExtendedEditBar',
			EXTINFO::DESCRIPTION => 'Provides additional buttons to the wiki edit field.',
			EXTINFO::AUTHOR      => 'MediaWiki Extension, packaging by Markus Glaser',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9745 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.blue-spice.org',
			EXTINFO::DEPS        => array( 'bluespice' => '1.22.0' )
		);
		$this->mExtensionKey = 'MW::ExtendedEditBar';
		wfProfileOut('BS::'.__METHOD__ );
	}

	/**
	 * Initialization of ExtendedEditBar extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook('BeforePageDisplay');

		BsConfig::registerVar( 'MW::ExtendedEditBar::ImagePath', 
			$this->getImagePath(), 
			BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING|BsConfig::RENDER_AS_JAVASCRIPT 
		);
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Adds the 'ext.bluespice.extendededitbar' module to the OutputPage
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	function onBeforePageDisplay( $out, $skin) {
		if( $out->getRequest()->getVal( 'action' ) != 'edit') return true; 
		$out->addModules('ext.bluespice.extendededitbar');
		return true;
	}
}