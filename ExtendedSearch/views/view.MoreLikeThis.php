<?php
/**
 * Renders the ExtendedSearch Spellchecker hint in result view.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the ExtendedSearch Spellchecker hint in result view.
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch 
 */
class ViewMoreLikeThis extends ViewBaseElement {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $aParam = false ) {
		$sResult = '';
		$vMoreLikeThis = $this->getOption( 'mlt' );

		if ( is_array( $vMoreLikeThis ) && !empty( $vMoreLikeThis ) ) {
			$sResult .= '<ul>';
			foreach ( $vMoreLikeThis as $sMlt ) {
				$sResult .= '<li>'. $sMlt .'</li>';
			}
			$sResult .= '</ul>';
		}

		return $sResult;
	}

}