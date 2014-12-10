<?php
/**
 * Renders the StateBar frame.
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
 * This view renders the StateBar frame.
 * @package    BlueSpice_Extensions
 * @subpackage StateBar
 */
class ViewStateBar extends ViewBaseElement {
	/**
	 * Holds all icon elements of the StateBar
	 * @var array All elements have to be of type view (StateBarTopView)
	 */
	protected $mStateBarTopViews  = array();

	/**
	 * Holds all body elements of the StateBar
	 * @var array All elements have to be of type view (StateBarBodyView)
	 */
	protected $mStateBarBodyViews = array();

	/**
	 * This method actually generates the output
	 * @param mixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		global $wgScriptPath;
		$aOut = array();
		$aOut[] = '<div id="bs-statebar">';

		$aOut[] = ' <a id="bs-statebar-viewtoggler" href="#" title="' . wfMessage('bs-statebar-viewtoggler-tooltip')->plain() . '" class="icon-arrow-down9-after">';
		$aOut[] = '<span>' . wfMessage('bs-statebar-viewtoggler')->plain() . '</span>';
		$aOut[] = ' </a>';

		foreach( $this->mStateBarTopViews as $oStateBarTopView ) {
			$aOut[] = $oStateBarTopView->execute();
		}

		$aOut[] = ' <div id="bs-statebar-view" class="clearfix">';

		foreach( $this->mStateBarBodyViews as $oStateBarBodyView ) {
			$aOut[] = $oStateBarBodyView->execute();
		}

		$aOut[] = ' </div>'; // #state_view
		$aOut [] = "<div class='clearfix'></div>";
		$aOut[] = '</div>'; //#page_state

		return join( "\n", $aOut );
	}

	/**
	 * Adder-Method tor the internal $mStateBarTopViews field.
	 * @param ViewStateBarTopElement $StateBarTopView The StateBarTopView object to be added.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function addStateBarTopView( ViewStateBarTopElement $StateBarTopView ) {
		$this->mStateBarTopViews[] = $StateBarTopView;
		return $this;
	}

	/**
	 * Adder-Method tor the internal $mStateBarBodyViews field.
	 * @param ViewStateBarBodyElement $oStateBarBodyView The ViewStateBarBodyElement object to be added.
	 * @return ViewStateBarTopElement Itself. For method chaining
	 */
	public function addStateBarBodyView( ViewStateBarBodyElement $oStateBarBodyView ) {
		$this->mStateBarBodyViews[] = $oStateBarBodyView;
		return $this;
	}

}
