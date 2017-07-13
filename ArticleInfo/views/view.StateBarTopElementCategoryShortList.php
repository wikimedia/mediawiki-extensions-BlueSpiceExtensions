<?php
/**
 * Renders the CategoryShortList from the ArticleInfo extension.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage ArticleInfo
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the CategoryShortList from the ArticleInfo extension.
 * @package    BlueSpice_Extensions
 * @subpackage ArticleInfo
 */
class ViewStateBarTopElementCategoryShortList extends ViewStateBarTopElement {

	/**
	 * Holds all categories as array( 'url' => '...', 'name' => 'CategoryName' ).
	 * @var array
	 */
	protected $aCategories = array();
	/**
	 * Wether to show an ellipisis or not.
	 * @var bool
	 */
	protected $bMoreCategoriesAvailable = false;

	/**
	 * Adder for a category array like array( 'url' => '...', 'name' => 'CategoryName' ).
	 * @param array $aCategory
	 */
	public function addCategory( $aCategory ) {
		$this->aCategories[] = $aCategory;
	}

	/**
	 * Setter for the internal field $bMoreCategoriesAvailable.
	 * @param bool $bMoreCategoriesAvailable The value.
	 */
	public function setMoreCategoriesAvailable( $bMoreCategoriesAvailable ) {
		$this->bMoreCategoriesAvailable = $bMoreCategoriesAvailable;
	}

	/**
	 * This method actually generates the output
	 * @param mixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$aOut = array();

		$sMaxDisplayNameLength = 45 / count( $this->aCategories );

		foreach ( $this->aCategories as $sCategory ) {
			$aOut[] = $sCategory;
		}
		if( $this->bMoreCategoriesAvailable ) {
			$aOut[] = '<a class="bs-statebar-viewtoggler" href="#" title="'.wfMessage( 'bs-articleinfo-more-categories' )->plain().'">[...]</a>';
		}

		$this->sText = implode( ', ', $aOut );

		if ( !empty ( $this->sKey ) ) {
			return parent::execute();
		} else {
			return NULL;
		}
	}

}
