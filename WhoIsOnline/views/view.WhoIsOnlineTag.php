<?php
/**
 * Renders the WhoIsOnline tag.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: view.WhoIsOnlineTag.php 9308 2013-05-06 13:00:30Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the WhoIsOnline tag.
 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 */
class ViewWhoIsOnlineTag extends ViewBaseElement {
	
	/**
	 * Counter increments with every instance. Used to separate several instances on one page.
	 * @var int Current number of instance.
	 */
	private static $iCount = 0;
	/**
	 * ID of the tag area.
	 * @var string Any distinct name.
	 */
	protected $sTargetId   = '';

	protected $oPortlet = null;
	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->setAutoElement(''); // TODO RBV (16.11.10 16:42): Workaround for bug "Parser/Outputhandler breaks markup"
		$this->sTargetId = 'bs-wo-link-'.self::$iCount++;
	}

	/**
	 * Getter for sTargetId.
	 * @return string The target id.
	 */
	public function getTargetId() {
		return $this->sTargetId;
	}
	
	public function setPortlet( $oPortlet ) {
		$this->oPortlet = $oPortlet;
		return $this;
	}

	/**
	 * This method actually generates the output
	 * @param array $params List of parameters
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sMaxHeight = 'data-maxheight="'.(BsConfig::get('MW::WhoIsOnline::LimitCount')*20).'"';
		$sOut = '<a class="bs-tooltip-link" href="#" id="'.$this->getTargetId().'">'.$this->getOption( 'title' ).'</a>'
				.'<div class="bs-tooltip">'
					.'<ul class="bs-who-heading">'
						.'<li>'.wfMsg('bs-whoisonline-widget-title').'</li>'
					.'</ul>'
					.'<div id="'.$this->getTargetId().'-target" class="bs-whoisonline-portlet" '.$sMaxHeight.'>'
						.($this->oPortlet ? $this->oPortlet->execute() : '')
					.'</div>'
				.'</div>'
				;

		return $sOut;
	}
}
