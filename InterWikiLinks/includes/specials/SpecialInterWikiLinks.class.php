<?php

/**
 * Special page for InterWikiLinks for MediaWiki
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @version    $Id$
 * @package    BlueSpice_InterWikiLinks
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
class SpecialInterWikiLinks extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'InterWikiLinks', 'interwikilinks-viewspecialpage' );
	}

	public function execute( $par ) {
		parent::execute( $par );
		$oOutputPage = $this->getOutput();

		$oOutputPage->addModules( 'bluespice.insertLink.interWikiLinks' );

		$oOutputPage->addJsConfigVars( 'BSInterWikiPrefixes', InterWikiLinks::getInterWikiLinkPrefixes() );

		$oOutputPage->addModules( 'ext.bluespice.interWikiLinks' );
		$oOutputPage->addHTML( '<div id="InterWikiLinksGrid" class="bs-manager-container"></div>' );
	}

	protected function getGroupName() {
		return 'bluespice';
	}

}
