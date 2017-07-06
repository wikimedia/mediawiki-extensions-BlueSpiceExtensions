<?php
/**
 * Renders the ExtendedSearch create or suggest hint in result view.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @author     Mathias Scheer <scheer@hallowelt.com>
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
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

		if( $oTitle instanceof Title === false ) {
			return '';
		}

		$aLinks = [];
		if( !$oTitle->exists() ) {
			$aLinks['bs-extendedsearch-suggest'] = [
				'href' => $oTitle->getLocalURL(),
				'title' => wfMessage( 'bs-extendedsearch-create-page', $oTitle->getPrefixedText() )->plain(),
				'text' => wfMessage( 'bs-extendedsearch-create-page', $oTitle->getPrefixedText() )->plain()
			];
		}

		Hooks::run( 'BSExtendedSearchSpecialPageTermLinks', [ &$aLinks ] );

		$sCreatesuggest  = '<ul>';
		$sCreatesuggest .= $this->renderList( $aLinks );

		$sSearchUrlencoded   = urlencode( $sSearch );
		$sSearchHtmlEntities = htmlentities( $sSearch, ENT_QUOTES, 'UTF-8' );
		Hooks::run( 'BSExtendedSearchAdditionalActions', array( &$sCreatesuggest, &$sSearchUrlencoded, &$sSearchHtmlEntities, &$oTitle ), '2.27' );

		$sCreatesuggest .= '</ul>';
		$sCreatesuggest .= '<br />';

		return $sCreatesuggest;
	}

	/**
	 * Renders a given array to links in list items.
	 *
	 * @param $aLinks array An array of links.
	 * @return string Returns the HTML from the given array.
	 */
	private function renderList( $aLinks ) {
		$sResult = '';

		foreach ($aLinks as $sId => $aItem) {
			$sResult .= Html::rawElement(
				'li',
				array(
				),
				Html::element(
					'a',
					array(
						'id' => $sId,
						'title' => $aItem['title'],
						'href' => $aItem['href'],
						'text' => $aItem['text']
					),
					$aItem['text']
				)
			);
		}

		return $sResult;
	}
}
