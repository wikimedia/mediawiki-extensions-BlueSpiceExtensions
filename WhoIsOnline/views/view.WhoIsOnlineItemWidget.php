<?php
/**
 * Renders a single WhoIsOnline list item.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: view.WhoIsOnlineItemWidget.php 6444 2012-09-10 13:04:48Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders a single WhoIsOnline list item.
 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 */
class ViewWhoIsOnlineItemWidget extends ViewBaseElement {
	/**
	 * Username to be rendered in link. Used directly to point to users' page.
	 * @var string Name of the user.
	 */
	protected $sUserName        = '';
	/**
	 * Username to be rendered as link description.
	 * @var string. Display name of the user.
	 */
	protected $sUserDisplayName = '';

	/**
	 * Constructor
	 */
	public function  __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @param array $params List of parameters
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		if( empty( $this->sUserDisplayName ) ) {
			$this->sUserDisplayName = $this->sUserName;
		}

		$aOut = array();

		// TODO MRG (08.09.10 00:06): Use user image or MiniProfile
		$aOut[] = '<li>';
		if ( $this->getOption( 'renderLink' ) ) {
			$aOut[] = sprintf(
							'<a href="%s/index.php?title=User:%s" title="%s">%s</a>',
							BsConfig::get( 'MW::ScriptPath' ),
							$this->sUserName,
							$this->sUserDisplayName,
							BsStringHelper::shorten( $this->sUserDisplayName, array('max-length' => 50) )
						);
		}
		else {
			$aOut[] = $this->sUserDisplayName;
		}

		$aOut[] = '</li>';

		return implode( "", $aOut );
	}

	/**
	 * Setter for $sUserName.
	 * @param string $sUserName Name of the user. Used directly to point to users' page.
	 */
	public function setUserName( $sUserName ) {
		$this->sUserName = $sUserName;
	}

	/**
	 * Setter for $sUserDisplayName.
	 * @param string $sUserDisplayName Display name of the user. Used as link description.
	 */
	public function setUserDisplayName( $sUserDisplayName ) {
		$this->sUserDisplayName = $sUserDisplayName;
	}
}
