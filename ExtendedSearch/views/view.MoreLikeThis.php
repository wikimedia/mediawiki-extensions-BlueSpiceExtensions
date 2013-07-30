<?php
/**
 * Renders the ExtendedSearch Spellchecker hint in result view.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
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
		$sResult       = '';
		$vMoreLikeThis = $this->getOption( 'mlt' );

		if ( is_array( $vMoreLikeThis ) && !empty( $vMoreLikeThis ) ) {
			$aMlt = array();
			$sResult .= '<div class="bs-extendedsearch-mlt">';
			foreach ( $vMoreLikeThis as $sMlt ) {
				$aMlt[] = $sMlt;
			}
			$sResult .= '<h4>' . wfMessage( 'bs-extendedsearch-morelikethis' )->plain() . '</h4>';
			$sResult .= implode( '<br />', $aMlt );
			$sResult .= '<br /></div>';
		}

		return $sResult;
	}

}