<?php
/**
 * Renders a single shout.
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
 * This view renders the a single shout.
 * @package    BlueSpice_Extensions
 * @subpackage ShoutBox 
 */
class ViewShoutBoxMessage extends ViewBaseElement {

	/**
	 * Date and time of the shout
	 * @var string readily rendered date
	 */
	protected $sDate;
	/**
	 * Name of the author of the shout
	 * @var string readily rendered name
	 */
	protected $sUsername;
	/**
	 * The message of the shout
	 * @var string readily rendered message 
	 */
	protected $sMessage;
	/**
	 *
	 * @var ViewUserMiniProfile
	 */
	protected $oMiniProfile;
	/**
	 *
	 * @var User 
	 */
	protected $oUser;
	
	/**
	 *
	 * @var ShoutID 
	 */
	protected $iShoutID;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * Sets the date property
	 * @param string $sDate readily rendered date, currently something like "2 minutes ago"
	 */
	public function setDate( $sDate ) {
		$this->sDate = $sDate;
	}

	/**
	 * Sets the username property
	 * @param string $sName readily rendered name, currently the real name
	 */
	public function setUsername( $sName ) {
		$this->sUsername = $sName;
	}

	/**
	 * Sets the message property
	 * @param string $sMessage readily rendered message. Currently just plain text.
	 */
	public function setMessage( $sMessage ) {
		$this->sMessage = $sMessage;
	}

	/**
	 * Sets the UserMiniProfile view property
	 * @param ViewUserMiniProfile $oView 
	 */
	public function setMiniProfile( $oView ) {
		$this->oMiniProfile = $oView;
	}

	/**
	 * Sets the User object
	 * @param User $oUser
	 */
	public function setUser( $oUser ) {
		$this->oUser = $oUser;
	}

	/**
	 * Sets the ID of the shout
	 * @param Integer $iShoutID
	 */
	public function setShoutID( $iShoutID ) {
		$this->iShoutID = $iShoutID;
	}

	/**
	 * This method actually generates the output
	 * @param array $aParams not used here
	 * @return string HTML output
	 */
	public function execute( $aParams = false ) {
		global $wgUser;
		$sUserName     = $this->oUser->getName();
		$sUserRealName = $this->oUser->getRealName();

		//Fallback for old entries without user_id
		if( $this->oUser->isAnon() ) $sUserName = $this->sUsername;

		$aOut = array();
		$aOut[] = '<li class="bs-sb-listitem clearfix" id="bs-sb-'.$this->iShoutID.'">';
		$aOut[] = '  <div class="bs-user-image">';
		if ( $this->oMiniProfile instanceof ViewUserMiniProfile ) {
			$aOut[] = $this->oMiniProfile->execute();
		}
		$aOut[] = '  </div>';
		$aOut[] = '  <div class="bs-sb-message">';
		$aOut[] = '    <div class="bs-sb-message-head">';
		$aOut[] = '      <strong>'. $sUserName . '</strong>';
		if ( !empty( $sUserRealName ) ) {
			$aOut[] = '      <span class="bs-sb-meassage-head-small">'. $sUserRealName . '</span>';
		}
		$aOut[] = '    </div>';
		if ( isset( $this->sDate ) ) {
			$aOut[] = '<div class="bs-sb-message-time">'. $this->sDate;
			$aOut[] = '</div> ';
		}
		$aOut[] = '    <div class="bs-sb-message-text">'. nl2br( $this->sMessage );
		$aOut[] = '    </div> ';
		$aOut[] = '  </div>';
		$sArchiveButton = '';
		$sArchiveButtonEnabled = '  <div class="bs-sb-archive"></div>';
		//set button if user has the right to archive
		if ( BsCore::checkAccessAdmission( 'archiveshoutbox' ) ) $sArchiveButton = $sArchiveButtonEnabled;
		//if setting for "allow own entries to be archived" is set + username == shoutbox-entry-username => set button
		if ( BsConfig::get( 'MW::ShoutBox::AllowArchive' ) && $wgUser->getName() == $sUserName ) $sArchiveButton = $sArchiveButtonEnabled;

		$aOut[] = $sArchiveButton;
		$aOut[] = '</li>';
		return implode( "\n", $aOut);
	}

}