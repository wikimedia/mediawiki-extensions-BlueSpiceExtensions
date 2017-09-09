<?php
/**
 * Renders the profile image frame on the users page.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage Authors
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// Last review MRG (30.06.11 10:25)

/**
 * This view renders the profile image frame on the users page.
 * @package    BlueSpice_Extensions
 * @subpackage Authors
 */
class ViewAuthorsUserPageProfileImageSetting extends ViewBaseElement {
	/*
	 * @var User $oUser The User object of the current user.
	 */
	protected $oUser                = null;
	/*
	 * @var string $sImagePath Path to the current user image.
	 */
	protected $sImagePath           = '';
	/*
	 * @var string $sUserDisplayName Display name of the current user.
	 */
	protected $sUserDisplayName     = '';
	/*
	 * @var string $sImageUploadPath URL of upload script for changing the current user image.
	 */
	protected $sImageUploadPath     = '';

	/**
	 * This method actually generates the output
	 * @param mixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function  execute( $params = false ) {
		$this->initFields();
		Hooks::run( 'BsAuthorPageProfileImageAfterInitFields', array( $this, $this->oUser ) );

		$aOut = array();
		$aOut[] = '<div id="bs-authors-imageform" class="bs-userpagesettings-item">';
		$aOut[] = $this->renderLink(
			array(
				'href'   => htmlspecialchars( $this->sImageUploadPath ),
				'title'  => wfMessage( 'bs-authors-profileimage-change' )->plain()
			),
			'<img src="'.$this->sImagePath.'" alt="'.$this->sUserDisplayName.'" width="64" title="'.wfMessage( 'bs-authors-profileimage' )->plain().'" />'.
			'<div class="bs-user-label">'.wfMessage( 'bs-authors-profileimage-change' )->plain().'</div>'
		);
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}

	/**
	 * Setter for internal User object.
	 * @param User $oUser The MediaWiki User object the profile image frame should be rendered for.
	 */
	public function setCurrentUser( $oUser ) {
		$this->oUser = $oUser;
	}

	public function setImagePath( $sImagePath ) {
		$this->sImagePath = $sImagePath;
	}

	public function getImagePath() {
		return $this->sImagePath;
	}

	/**
	 * Initializes required fields.
	 */
	private function initFields() {
		if ( $this->oUser === null ) throw new BsException( __METHOD__.' - No user specified.' );

		$this->sUserDisplayName = BsCore::getInstance()->getUserDisplayName( $this->oUser );
		$sUserImage             = $this->oUser->getOption( 'MW::UserImage', '' ); //BsConfig::get() won't work on first call

		//Is it a URL? Some external image?
		$aParsedUrl = parse_url( $sUserImage );
		if ( !empty($sUserImage) && ($sUserImage{0} == '/' || isset( $aParsedUrl['scheme'] )) ) {
			$this->sImageUploadPath = SpecialPage::getTitleFor( 'Preferences' )->getLinkUrl();

			$aPathInfo = pathinfo( $aParsedUrl['path'] );
			$aFileExtWhitelist = array( 'gif', 'jpg', 'jpeg', 'png' );
			$this->sImagePath  = $aParsedUrl['scheme'].'://'.$aParsedUrl['host'].$aParsedUrl['path'];

			if ( !in_array( strtolower( $aPathInfo['extension'] ), $aFileExtWhitelist ) ) {
				$this->sImagePath = BsConfig::get( 'MW::AnonUserImage' );
			}
			return;
		}

		$oUserImageFile = RepoGroup::singleton()->getLocalRepo()->newFile( $sUserImage );
		if ( $oUserImageFile ) {
			$UserImageArticle       = new ImagePage( $oUserImageFile->getTitle() );
			$this->sImageUploadPath = $UserImageArticle->getUploadUrl();

			if ( $oUserImageFile->exists() === false ) {
				$this->sImagePath = BsConfig::get( 'MW::DefaultUserImage' );
			}
			else {
				$oUserThumbnail = $oUserImageFile->transform( array( 'width' => 64, 'height' => 64 ) );
				if ( $oUserThumbnail !== false ) {
					$this->sImagePath = $oUserThumbnail->getUrl();
				}
				else {
					$this->sImagePath = $oUserImageFile->getUrl();
				}
			}
		}
		else {
			$this->sImagePath = BsConfig::get( 'MW::DefaultUserImage' );
		}
	}
}
