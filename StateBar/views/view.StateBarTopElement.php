<?php
/**
 * Renders a StateBar TopElement.
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
 * This view renders a StateBar TopElement.
 * @package    BlueSpice_Extensions
 * @subpackage StateBar
 */
class ViewStateBarTopElement extends ViewBaseElement {

	/**
	 * The unique key.
	 * @var string
	 */
	protected $sKey           = '';
	/**
	 * The URL to the icon to display. Should be relative to the document root.
	 * @var string
	 */
	protected $sIconSrc       = '';
	/**
	 * The "alt-text" for the icon image.
	 * @var string
	 */
	protected $sIconAlt       = '';
	/**
	 * The href for the anchor around the icon image.
	 * @var string
	 */
	protected $sIconHref      = '';
	/**
	 * The text next to the icon. It will automatically be shortend to 30 characters.
	 * @var string
	 */
	protected $sText          = '';
	/**
	 *
	 * @var string If the text should be a link, specify the href here.
	 */
	protected $sTextLink      = '';
	/**
	 *
	 * @var string The value for the title attribute of the link. If not set, the link text will be used.
	 */
	protected $sTextLinkTitle = '';
	/**
	 * If a click on the icon shoul open up the body part of the statebar
	 * @var bool
	 */
	protected $bIconTogglesBody = false;
	/**
	 * Data attributes which should be added to statebar top item
	 * @var array
	 */
	protected $aDataAttributes = array();

	public function getDebugKey() {
		return "{$this->sKey}\n";
	}

	/**
	 * This method actually generates the output
	 * @param mixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sText = '';
		if( !empty( $this->sTextLink ) ) {
			if( empty( $this->sTextLinkTitle ) ) $this->sTextLinkTitle = $this->sText;
			$sText  = '<span id="sb-'.$this->sKey.'-text" class="bs-statebar-top-text">';
			$sText .= ' <a id="sb-'.$this->sKey.'-link" href="'.$this->sTextLink.'" title="'.$this->sTextLinkTitle.'">'.$this->sText.'</a>';
			$sText .= '</span>';
		}
		else {
			$sText = ' <span id="sb-'.$this->sKey.'-text" class="bs-statebar-top-text">'.$this->sText.'</span>';
		}

		$aOut = array();
		if ( !empty ( $this->sKey ) ) {
			$sDataAttributes = '';
			$sClasses = '';

			if( !empty($this->aDataAttributes) ) {
				foreach($this->aDataAttributes as $key => $value) 
					$sDataAttributes .= ' data-'.$key.'="'.$value.'"';
			}

			if( $this->bIconTogglesBody ) $sClasses = ' bs-statebar-viewtoggler';
			$aOut[] = '<div id="sb-'.$this->sKey.'" class="bs-statebar-top-item'.$sClasses.'" '.$sDataAttributes.'>';
			$aOut[] = ' <div id="sb-'.$this->sKey.'-icon" class="bs-statebar-top-icon">';
			if( !empty( $this->sIconHref ) ) $aOut[] = '<a href="'.$this->sIconHref.'" title="'.$this->sIconAlt.'">';
			$aOut[] = '  <img src="'.$this->sIconSrc.'" alt="'.$this->sIconAlt.'" title="'.$this->sIconAlt.'" />';
			if( !empty( $this->sIconHref ) ) $aOut[] = '</a>';
			$aOut[] = ' </div>';
			$aOut[] = $sText;
			$aOut[] = '</div>';
		}
		return join( "\n", $aOut );
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
	 * Setter-Method for the internal $sIconSrc field.
	 * @param string $sIconSrc The URL to the icon to display. Should be relative to the document root.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setIconSrc( $sIconSrc ) {
		$this->sIconSrc = $sIconSrc;
		return $this;
	}

	/**
	 * Setter-Method for the internal $sIconAlt field.
	 * @param string $sIconAlt The "alt-text" for the icon image.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setIconAlt( $sIconAlt ) {
		$this->sIconAlt = $sIconAlt;
		return $this;
	}

	/**
	 * Setter-Method for the internal $sText field.
	 * @param string $sText The text next to the icon. It will automatically be shortend to 30 characters.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setText( $sText ) {
		$this->sText = BsStringHelper::shorten( $sText, array( 'max-length' => 30 ) );;
		return $this;
	}

	/**
	 * Setter-Method for the internal $sTextLink field.
	 * @param string $sTextLink If the text should be a link, specify the href here.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setTextLink( $sTextLink ) {
		$this->sTextLink = $sTextLink;
		return $this;
	}

	/**
	 * Setter-Method for the internal $sTextLinkTitle field.
	 * @param string $sTextLinkTitle The value for the title attribute of the link. If not set, the link text will be used.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setTextLinkTitle( $sTextLinkTitle ) {
		$this->sTextLinkTitle = $sTextLinkTitle;
		return $this;
	}

	/**
	 * Setter-Method for the internal $sTextLinkTitle field.
	 * @param string $sIconHref The value for the title attribute of the link. If not set, the link text will be used.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setIconHref( $sIconHref ) {
		$this->sIconHref = $sIconHref;
		return $this;
	}

	/**
	 * Setter-Method for the internal $sTextLinkTitle field.
	 * @param string $bToggle The value for the title attribute of the link. If not set, the link text will be used.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setIconTogglesBody( $bToggle ) {
		$this->bIconTogglesBody = $bToggle;
		return $this;
	}
	
	/**
	 * Setter-Method for the data values ( data-$sKey="$sValue" ).
	 * @param string $sKey The key of the attribute. 
	 * @param string $sValue The value of the attribute. 
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function setDataAttribute( $sKey, $sValue ) {
		$this->aDataAttributes[$sKey] = $sValue;
		return $this;
	}

}
