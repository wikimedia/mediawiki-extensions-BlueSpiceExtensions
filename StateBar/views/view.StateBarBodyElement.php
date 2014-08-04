<?php
/**
 * Renders a StateBar BodyElement.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage StateBar
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders a StateBar BodyElement.
 * @package    BlueSpice_Extensions
 * @subpackage StateBar
 */
class ViewStateBarBodyElement extends ViewBaseElement {

	/**
	 *
	 * @var string The unique key
	 */
	protected $sKey     = '';

	/**
	 *
	 * @var string The heading of the BodyElement.
	 */
	protected $sHeading = '';

	/**
	 *
	 * @var string The text/html content for the BodyElement.
	 */
	protected $sBodyText = '';

	public function getDebugKey() {
		return "{$this->sKey} - {$this->sHeading}\n";
	}

	/**
	 * This method actually generates the output
	 * @param mixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$arOut = array();

		if( empty( $this->sKey ) ) $this->sKey = md5( $this->sHeading );
		$arOut[] = '<div id="sbb-'.$this->sKey.'" class="bs-statebar-body-item">';
		$arOut[] = ' <h4 id="sbb-'.$this->sKey.'-heading" class="bs-statebar-body-itemheading">'.$this->sHeading.'</h4>';
		$arOut[] = ' <div id="sbb-'.$this->sKey.'-text" class="bs-statebar-body-itembody">'.$this->sBodyText.'</div>';
		$arOut[] = '</div>';

		return implode( "\n", $arOut );
	}

	/**
	 * Setter-Method for the internal $sKey field.
	 * @param string $sKey The unique key.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setKey( $sKey ) {
		$this->sKey = $sKey;
		return $this;
	}

	/**
	 * Setter-Method for the internal $sHeading field.
	 * @param string $sHeading The heading of the BodyElement.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setHeading( $sHeading ) {
		$this->sHeading = $sHeading;
		return $this;
	}

	/**
	 * Setter-Method for the internal $sBodyText field.
	 * @param string $sBodyText The text/html content for the BodyElement.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setBodyText( $sBodyText ) {
		$this->sBodyText = $sBodyText;
		return $this;
	}

}