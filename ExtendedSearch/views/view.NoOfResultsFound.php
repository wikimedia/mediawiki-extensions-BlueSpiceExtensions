<?php
/**
 * Renders the ExtendedSearch search results summary.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/**
 * Hierarchically constructed:
 * Form
 *  Table
 *   Inputfields etc.
 * Thus recursively assembled
 */
/**
 * This view renders the ExtendedSearch search results summary.
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 */
class ViewNoOfResultsFound extends ViewBaseElement {

	/**
	 * Constructor
	 * @param I18n $I18N Internationalisation object that is used for all messages
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Prepares output of search term. preg_replace_callback in execute function.
	 * @param array $input from preg_replace_callback: $input[0]: whole match, $input[1]: \1 (preg)
	 * @return string Modified string.
	 */
	protected function makeSpan( $input ) {
		return Xml::element( 'span', array( 'id' => 'bs-extendedsearch-searchterm' ), $input[1], false );
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$noOfResultsFound = $this->getOption( 'NoOfResultsFound' );
		if ( $noOfResultsFound === false ) return '';
		$searchTerm = Html::element( 'b', array(), $this->getOption( 'SearchTerm' ) );

		if ( $this->getOption( 'EscalatedToFuzzy' ) && $noOfResultsFound > 0 ) {
			$sOut = wfMessage( 'bs-extendedsearch-fuzzy', $searchTerm, $noOfResultsFound )->text();
		} else {
			$sOut = wfMessage( 'bs-extendedsearch-result', $searchTerm, $noOfResultsFound )->text();
		}

		$sOut = preg_replace_callback( '|<b>(.*?)</b>|', array( &$this, 'makeSpan' ), $sOut );
		$sOut = Xml::openElement( 'div', array( 'id' => 'bs-extendedsearch-noofresultsandsearchterm' ) ).
				$sOut.
				Xml::closeElement( 'div' );

		return $sOut;
	}

}
