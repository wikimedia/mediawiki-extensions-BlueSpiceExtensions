<?php
/**
 * Renders a single WhoIsOnline list item.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
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
	 * The current user object
	 * @var User
	 */
	protected $oUser = null;

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
		if( $this->oUser instanceof User === false ) {
			$this->oUser = User::newFromName( $this->sUserName );
		}
		//In some rare cases (LDAPAuth + special characters in username)
		//the username isn't saved correctly to the DB, causing '
		//User::newFromName' to return false.
		//TODO: Find and fix real issue
		if( $this->oUser instanceof User === false ) {
			return '';
		}

		if ( empty( $this->sUserDisplayName ) ) {
			$this->sUserDisplayName = $this->oUser->getName();
		}

		$aOut = array();
		$aOut[] = '<li>';
		$aOut[] = Linker::link( $this->oUser->getUserPage(), $this->sUserDisplayName );
		$aOut[] = '</li>';

		return implode( "", $aOut );
	}

	/**
	 * Setter for $sUserName.
	 * @param string $sUserName Name of the user. Used directly to point to users' page.
	 * @deprecated since version 2.23
	 */
	public function setUserName( $sUserName ) {
		wfDeprecated( __METHOD__, '2.23' );
		$this->sUserName = $sUserName;
	}

	/**
	 * Setter for $sUserDisplayName.
	 * @param string $sUserDisplayName Display name of the user. Used as link description.
	 */
	public function setUserDisplayName( $sUserDisplayName ) {
		$this->sUserDisplayName = $sUserDisplayName;
	}

	public function setUser( $oUser ) {
		$this->oUser = $oUser;
	}
}
