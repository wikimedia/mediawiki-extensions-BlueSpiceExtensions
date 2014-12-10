<?php
/**
 * Renders the a navigation item of the top bar menu.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage TopMenuBarCustomizer
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders a navigation item.
 * @package    BlueSpice_Extensions
 * @subpackage TopMenuBarCustomizer
 */
class ViewTopMenuItem extends ViewBaseElement {

	/**
	 * Item level
	 * @var integer
	 */
	protected $iLevel = 1;
	/**
	 * Name of the item
	 * @var string
	 */
	protected $sName = '';
	/**
	 * Displayname of the item
	 * @var string
	 */
	protected $sDisplayTitle = '';
	/**
	 * Target link
	 * @var string
	 */
	protected $sLink = '';
	/**
	 * is this item active
	 * @var boolean
	 */
	protected $bActive = false;
	/**
	 * is this item active
	 * @var boolean
	 */
	protected $bContainsActive = false;
	/**
	 * is this item external
	 * @var boolean
	 */
	protected $bExternal = false;
	/**
	 * has this item child items
	 * @var array
	 */
	protected $aChildren = array();

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Sets the level property
	 * @param integer $iLevel
	 */
	public function setLevel( $iLevel ) {
		$this->iLevel = $iLevel;
	}

	/**
	 * Sets the name property
	 * @param string $sName
	 */
	public function setName( $sName ) {
		$this->sName = $sName;
	}

	/**
	 * Sets the display title property
	 * @param string $sDisplayTitle
	 */
	public function setDisplaytitle( $sDisplayTitle ) {
		$this->sDisplayTitle = $sDisplayTitle;
	}

	/**
	 * Sets the Link property
	 * @param string $sLink
	 */
	public function setLink( $sLink ) {
		$this->sLink = $sLink;
	}

	/**
	 * Sets the active property
	 * @param boolean $bActive
	 */
	public function setActive( $bActive ) {
		$this->bActive = $bActive;
	}

	/**
	 * Sets the contains active property
	 * @param boolean $bContainsActive
	 */
	public function setContainsActive( $bContainsActive ) {
		$this->bContainsActive = $bContainsActive;
	}

	/**
	 * Sets the external property
	 * @param boolean $bExternal
	 */
	public function setExternal( $bExternal ) {
		$this->bExternal = $bExternal;
	}

	/**
	 * Sets the children property
	 * @param boolean $aChildren
	 */
	public function setChildren( $aChildren ) {
		$this->aChildren = $aChildren;
	}

	/**
	 * This method actually generates the output
	 * @param array $aParams not used here
	 * @return string HTML output
	 */
	public function execute( $aParams = false ) {
		$aClasses = array();

		$aClasses[] = empty($this->aChildren) ? 'menu-item-single' : 'menu-item-container';
		$aClasses[] = "level-$this->iLevel";
		if( $this->bContainsActive) $aClasses[] = 'contains-active';
		if( $this->bActive) $aClasses[] = 'active';

		$sTitle = $sText = empty($this->sDisplayTitle) ? $this->sName : $this->sDisplayTitle;
		if( wfMessage($sTitle)->exists() ) {
			$sTitle = $sText = wfMessage($sTitle)->plain();
		}

		global $wgExternalLinkTarget;

		$sLinkTarget = '';
		if( $this->bExternal && !empty($wgExternalLinkTarget) ) {
			$sLinkTarget = 'target="'.$wgExternalLinkTarget.'"';
		}

		$sOut = '';
		$sOut .= '<li>';
		$sOut .= "<a href='$this->sLink' class='".implode(' ', $aClasses)."' $sLinkTarget>$sText</a>";
		if( !empty($this->aChildren) ) {
			$sOut .= $this->rederChildItems();
		}
		$sOut .= '</li>';
		return $sOut;
	}

	private function rederChildItems() {
		$aOut[] ='<ul class="bs-apps-child level-'.($this->iLevel+1).'">';

		foreach( $this->aChildren as $aApp ) {
			$aApp = array_merge(TopMenuBarCustomizer::$aNavigationSiteTemplate, $aApp);

			$oItem = new ViewTopMenuItem();
			$oItem->setLevel( $aApp['level'] );
			$oItem->setName( $aApp['id'] );
			$oItem->setLink( $aApp['href'] );
			$oItem->setActive( $aApp['active'] );
			$oItem->setExternal( $aApp['external'] );
			$oItem->setDisplaytitle( $aApp['text'] );
			$oItem->setContainsActive( $aApp['containsactive'] );
			if( !empty($aApp['children']) ) {
				$oItem->setChildren( $aApp['children'] );
			}
			$aOut[] = $oItem->execute();
		}

		$aOut[] ='</ul>';
		return implode( "\n", $aOut);
	}

}