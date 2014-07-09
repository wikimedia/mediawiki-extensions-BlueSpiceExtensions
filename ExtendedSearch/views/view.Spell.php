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
class ViewSpell extends ViewBaseElement {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $aParam = false ) {
		$sResult = '';
		$vSpellchecker = $this->getOption( 'sim' );

		if ( is_array( $vSpellchecker ) && count( $vSpellchecker ) ) {
			$aSpells = array();
			$sResult .= '<span class="bs-extendedsearch-spellchecker">';
			foreach ( $vSpellchecker as $sSpell ) {
				$sSpell = str_replace( '_', ' ', $sSpell );
				$sSpellcheckerLink = Xml::element(
						'a',
						array( 'href' => $this->getOption( 'url' ) . '&q=' . urlencode( $sSpell ) ),
						ucfirst( htmlspecialchars( $sSpell, ENT_QUOTES, 'UTF-8' ) )
				);

				$aSpells[] = $sSpellcheckerLink;
			}
			$sResult .= wfMessage( 'bs-extendedsearch-did-you-mean', implode( ', ', $aSpells ) )->plain();
		}
		if ( !empty( $aSpells ) ) {
			$sResult .= '</span><br /><br />';
		}

		return $sResult;
	}

}