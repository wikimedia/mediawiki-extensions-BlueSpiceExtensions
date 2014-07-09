<?php
/**
 * Renders the Shoutbox frame.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage ShoutBox
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

//Last Code review RBV (30.06.2011)

/**
 * This view renders the Shoutbox frame.
 * @package    BlueSpice_Extensions
 * @subpackage ShoutBox
 */
class ViewShoutBox extends ViewBaseElement {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sOut = '';

		if ( $this->getOption( 'showmessageform' ) ) {
			$sOut .= $this->renderMessageForm();
		}
		$sOut = $this->wrapAll( $sOut );

		return $sOut;
	}

	/**
	 * Renders the form that is used to enter new shouts
	 * @return string HTML of the form
	 */
	protected function renderMessageForm() {
		global $wgScriptPath;

		$aOut = array();
		$aOut[] = '<form id="bs-sb-form" class="clearfix">';
		$aOut[] = '<textarea id="bs-sb-message" maxlength="'.BsConfig::get( 'MW::ShoutBox::MaxMessageLength' ).'">'.wfMessage( 'bs-shoutbox-message' )->plain().'</textarea>';
		$aOut[] = '<br />';
		$aOut[] = '<img id="bs-sb-loading" src="'.$wgScriptPath.'/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-ajax-loader-bar-blue.gif" alt="Loading..."/>';
		$aOut[] = '<input id="bs-sb-send" type="submit" value="'.wfMessage( 'bs-shoutbox-shout' )->plain().'" />';
		$aOut[] = '<p class="bs-sb-textarea-additionalinfo">';
		$aOut[] = '<span id="bs-sb-charactercounter"></span>';
		$aOut[] = '</p>';
		$aOut[] = '</form>';

		return implode( "\n" , $aOut );
	}

	/**
	 * Renders the basic shoutbox layer
	 * @param string $innerText HTML that is to be put inside the basic shoutbox layer, i.e. the output box and shouts.
	 * @return string HTML for output
	 */
	protected function wrapAll( $innerText ) {
		$aOut = array();
		$aOut[] = '<div class="bs-sb">';
		$aOut[] = '<fieldset>';
		$aOut[] = '<legend>'.wfMessage( 'bs-shoutbox-title' )->plain().'</legend>';
		$aOut[] = $innerText;
		$aOut[] = '<div id="bs-sb-content" style="display:none;"></div>';
		$aOut[] = '</fieldset>';
		$aOut[] = '</div>';

		return implode( "\n" , $aOut );
	}

}