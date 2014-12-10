<?php
/**
 * NamespaceCss extension for BlueSpice
 *
 * Use different CSS-Styles in Namespaces by add page MediaWiki:'NAMESPACENAME'_css
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
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    2.22.0

 * @package    Bluespice_Extensions
 * @subpackage NamespaceCss
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// Last review MRG (07.11.11 18:22)

class NamespaceCss extends BsExtensionMW {

	public function __construct() {
		wfProfileIn( 'BS::NamespaceCss_construct' );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'NamespaceCss',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-namespacecss-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Robert Vogel, Tobias Weichart, Patric Wirth',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);

		$this->mExtensionKey = 'MW::NamespaceCss';

		wfProfileOut( 'BS::NamespaceCss_construct' );
	}

	public function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->setHook( 'BeforePageDisplay' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * @param OutputPage $oOut
	 * @param Skin $oSkin
	 * @return boolean
	 */
	public function onBeforePageDisplay( &$oOut, &$oSkin ) {
		$oTitle = $oSkin->getTitle();

		$aNamespaces	= MWNamespace::getCanonicalNamespaces();
		$iCurrentNs		= $oTitle->getNamespace();

		if( $oTitle->isTalkPage() ) $iCurrentNs--;
		if( !isset($aNamespaces[$iCurrentNs]) ) return true;

		$oStyleSheetTitle = Title::newFromText( $aNamespaces[$iCurrentNs].'_css', NS_MEDIAWIKI );
		if( $oStyleSheetTitle->exists() ) {
			$oOut->addStyle($oStyleSheetTitle->getLocalUrl( array( 'action' => 'raw', 'ctype' => 'text/css' ) ));
		}

		return true;
	}
}