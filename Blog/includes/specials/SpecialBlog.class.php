<?php
/**
 * Special page for Blog
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @version    $Id$
 * @package    BlueSpice_Extensions
 * @subpackage Blog
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class SpecialBlog extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'Blog' );
	}

	public function execute( $par ) {
		parent::execute( $par );

		BsExtensionManager::setContext( 'MW::Blog::ShowBlog' );

		$aArgs = array();
		$aNamespaces = $this->getLanguage()->getNamespaces();
		$oTitle = Title::newFromText( $par );

		if ( preg_grep( "/^" . $par . "$/i", $aNamespaces ) ) {
			$aArgs['ns'] = $par;
		} else if ( is_object( $oTitle ) && $oTitle->getNamespace() == NS_CATEGORY ) {
			$aArgs['cat'] = $oTitle->getText();
		}

		$oOut = $this->getOutput();
		$oOut->addHTML( BsExtensionManager::getExtension( 'Blog' )->onBlog( '', $aArgs, null ) );

	}
}