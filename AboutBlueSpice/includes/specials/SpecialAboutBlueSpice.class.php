<?php

/**
 * Renders the About BlueSpice special page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage AboutBlueSpice
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class SpecialAboutBlueSpice extends BsSpecialPage {

	/**
	 * Constructor of SpecialAboutBlueSpice class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		parent::__construct( 'AboutBlueSpice', 'aboutbluespice-viewspecialpage' );
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
				$sUrl = "https://de.bluespice.com/about-bluespice/";
				break;
			default :
				$sUrl = "https://bluespice.com/about-bluespice/";
		};

		$sOutHTML = '<iframe src="' . $sUrl . '" id="aboutbluespiceremote" name="aboutbluespiceremote" style="width:100%;border:0px;min-height:1200px;"></iframe>';

		$oOutputPage = $this->getOutput();

		$oOutputPage->addHTML( $sOutHTML );

		return true;
	}

	protected function getGroupName() {
		return 'bluespice';
	}

}