<?php
/**
 * Renders a list of shouts.
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
 * This view renders the frame for list of shouts, e.g. when no entries are there,
 * and collects all shouts
 * @package    BlueSpice_Extensions
 * @subpackage ShoutBox 
 */
class ViewShoutBoxMessageList extends ViewBaseElement {

	/**
	 * Maximum number of shouts before more-link is displayed
	 * @var int maximum number
	 */
	protected $iMoreLimit;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @param array $params not used here
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		if ( $this->hasItems() ) {
			$out = parent::execute();
			if ( $this->iMoreLimit ) {
				$out .= '<li class="bs-sb-more"><a href="#" onclick="BsShoutBox.updateShoutbox('.$this->iMoreLimit.');return false;">&nbsp;</a></li>';
			}
		} else {
			$out = '<li><i>'.wfMessage( 'bs-shoutbox-no-entries' )->plain().'</i></li>';
		}
		return '<ul>'.$out.'</ul>';
	}

	/**
	 * Sets the more limit property
	 * @param string $iMoreLimit positiv integer.
	 */
	public function setMoreLimit( $iMoreLimit ) {
		$this->iMoreLimit = $iMoreLimit;
	}

}