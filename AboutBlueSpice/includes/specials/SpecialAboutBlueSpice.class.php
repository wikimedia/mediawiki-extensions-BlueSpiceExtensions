<?php

/**
 * Renders the About BlueSpice special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage AboutBlueSpice
 * @copyright  Copyright (C) 2015 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class SpecialAboutBlueSpice extends BsSpecialPage {

	/**
	 * Constructor of SpecialAboutBlueSpice class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		parent::__construct( 'AboutBlueSpice' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Renders special page output.
	 * @param string $sParameter Not used.
	 * @return bool Allow other hooked methods to be executed. Always true.
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$sLang = $this->getLanguage()->getCode();
		switch ( substr( $sLang, 0, 2 ) ) {
			case "de" :
				$sUrl = "http://de.bluespice.com";
				break;
			default :
				$sUrl = "http://www.bluespice.com";
		};

		$sOutHTML = '<iframe src="' . $sUrl . '" id="aboutbluespiceremote" style="width:100%;border:0px;min-height:400px;"></iframe>';

		$oOutputPage = $this->getOutput();

		$oOutputPage->addHTML( $sOutHTML );

		return true;
	}

	protected function getGroupName() {
		return 'bluespice';
	}

}