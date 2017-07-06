<?php
/**
 * Renders the WhoIsOnline widget.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the WhoIsOnline widget.
 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 */
class ViewWhoIsOnlineWidget extends ViewBaseElement {

	/**
	 * Constructor
	 */
	public function  __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @param array $params List of parameters
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$aOut = array();
		$aOut[] = '<ul>';
		if ( $this->getOption( 'count' ) == 0 ) {
			$aOut[] = '<li>' . wfMessage( 'bs-whoisonline-nousers' )->plain() . '</li>';
		}
		else {
			$this->setAutoElement( '' );
			$aOut[] = parent::execute();
		}
		$aOut[] = '</ul>';

		$sOut = implode( "\n", $aOut );
		if( $this->getOption( 'wrapper-id' ) !== false ) {
			return '<div style="display: none;" class="bs-tooltip"><div id="'.$this->getOption( 'wrapper-id' ).'">'.$sOut.'</div></div>';
		}
		return $sOut;
	}
}
