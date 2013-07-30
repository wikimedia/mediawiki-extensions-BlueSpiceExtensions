<?php
/**
 * Renders the ExtendedSearch create or suggest hint in result view.
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
 * This view renders the ExtendedSearch create or suggest hint in result view.
 * @package BlueSpice_Extensions
 * @subpackage ExtendedSearch 
 */
class ViewSearchSuggest extends ViewBaseElement {

	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $aParam = false ) {
		$sSearch             = str_replace( BsCore::getForbiddenCharsInArticleTitle(), '', $this->getOption( 'search' ) );
		$oTitle              = Title::newFromText( $sSearch );
		$sSearchUrlencoded   = urlencode( $sSearch );
		$sSearchHtmlEntities = htmlentities( $sSearch, ENT_QUOTES, 'UTF-8' );
		$sCreatesuggest      = '<ul>';

		wfRunHooks( 'BSExtendedSearchAdditionalActions', array( &$sCreatesuggest, &$sSearchUrlencoded, &$sSearchHtmlEntities, &$oTitle ) );

		$sCreatesuggest .= '</ul>';
		$sCreatesuggest .= '<br />';

		return $sCreatesuggest;
	}

}
