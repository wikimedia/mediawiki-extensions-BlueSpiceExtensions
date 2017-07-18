<?php

/**
 * Avatars extension for BlueSpice
 *
 * Provide generic and individual user images
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Marc Reymann <reymann@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage Avatars
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for the Avatars extension
 * @package BlueSpice_Extensions
 * @subpackage Avatars
 */
class Avatars extends BsExtensionMW {

	public static $bAvatarsActive = true;
	public static $sAvatarFilePrefix = "BS_avatar_";

	/**
	 * Initialization of Avatar extension
	 */
	protected function initExt() {
		wfProfileIn('BS::' . __METHOD__);

		BsConfig::registerVar('MW::Avatars::DefaultSize', 40, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_INT, 'bs-avatars-pref-defaultsize', 'int');
		BsConfig::registerVar('MW::Avatars::Generator', 'InstantAvatar', BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-avatars-pref-generator', 'select');

		$this->setHook('BSCoreGetUserMiniProfileBeforeInit');
		$this->setHook('BsAuthorPageProfileImageAfterInitFields');

		# TODO: required rights? user->read?
		#$this->mCore->registerPermission( 'viewfiles', array( 'user' ) );
		wfProfileOut('BS::' . __METHOD__);
	}

	/**
	 * extension.json callback
	 * @global array $wgForeignFileRepos
	 */
	public static function onRegistration() {
		global $wgForeignFileRepos;
		if ( version_compare( $GLOBALS['wgVersion'], '1.28c', '>' ) ) {
			$wgForeignFileRepos[] = array(
				'class' => 'FileRepo',
				'name' => 'Avatars',
				'directory' => BS_DATA_DIR . '/Avatars/',
				'hashLevels' => 0,
				'url' => BS_DATA_PATH . '/Avatars',
			);
		} else {
			$wgForeignFileRepos[] = array(
				'class' => 'FSRepo',
				'name' => 'Avatars',
				'directory' => BS_DATA_DIR . '/Avatars/',
				'hashLevels' => 0,
				'url' => BS_DATA_PATH . '/Avatars',
			);
		}
	}

	/**
	 * Adds module
	 * @param OutputPage $out
	 * @param SkinTemplate $skin
	 * @return boolean
	 */
	public static function onBeforePageDisplay(&$out, &$skin) {
		if (!$out->getTitle()->equals($out->getUser()->getUserPage()))
			return true;
		$out->addModules("ext.bluespice.avatars.js");
		return true;
	}

	public function runPreferencePlugin($sAdapterName, $oVariable) {
		$aPrefs = array('options' => array('InstantAvatar (random)' => 'InstantAvatar', 'Identicon (non-random)' => 'Identicon'));
		return $aPrefs;
	}

	/**
	 * Show avatar if user has no UserImage setting
	 * @param type $oUserMiniProfileView
	 * @param User $oUser
	 * @param type $aParams
	 * @return boolean
	 */
	public function onBSCoreGetUserMiniProfileBeforeInit(&$oUserMiniProfileView, &$oUser, &$aParams) {
		# Set anonymous image for anonymous or deleted users
		if ($oUser->isAnon()) {
			$oUserMiniProfileView->setUserImageSrc(BsConfig::get('MW::DeletedUserImage'));
			$oUserMiniProfileView->setOption('linktargethref', ''); # don't link to user page
			return true;
		}

		# If user has set MW image or URL return immediately
		if( !empty( $oUser->getOption( 'MW::UserImage' ) ) ) {
			return true;
		}

		# Set default image in read-only mode or thumb creation might get triggered
		if (wfReadOnly()) {
			$oUserMiniProfileView->setUserImageSrc(BsConfig::get('MW::DefaultUserImage'));
			return true;
		}

		# Set or generate user's avatar
		$oUserMiniProfileView->setUserImageSrc( $this->generateAvatar(
			$oUser,
			$aParams
		));

		return true;
	}

	/**
	 * Gets Avatar file from user ID
	 * @param int $iUserId
	 * @return boolean|\File
	 */
	public static function getAvatarFile( $iUserId ) {
		$sAvatarFileName = self::$sAvatarFilePrefix . $iUserId . ".png";
		return BsFileSystemHelper::getFileFromRepoName( $sAvatarFileName, 'Avatars' );
	}

	/**
	 * Show avatar on user page
	 * @param ViewAuthorsUserPageProfileImageSetting $oView
	 * @param User $oUser
	 * @return boolean
	 */
	public function onBsAuthorPageProfileImageAfterInitFields($oView, $oUser) {
		# If user has set MW image or URL return immediately
		if ($oUser->getOption('MW::UserImage'))
			return true;
		# Set default image in read-only mode or thumb creation might get triggered
		if (wfReadOnly()) {
			$oView->setImagePath(BsConfig::get('MW::DefaultUserImage'));
			return true;
		}
		$oView->setImagePath($this->generateAvatar($oUser));
		return true;
	}

	/**
	 * Generate a new generic avatar on user request
	 * @return type
	 */
	public static function generateAvatarAjax() {
		if (wfReadOnly()) {
			global $wgReadOnly;
			return new AjaxResponse(FormatJson::encode(wfMessage('bs-readonly', $wgReadOnly)->escaped()));
		}
		$oUser = RequestContext::getMain()->getUser();
		self::unsetUserImage($oUser);
		$oAvatars = BsExtensionManager::getExtension('Avatars');
		$sNewPath = $oAvatars->generateAvatar($oUser, array(), true);
		return FormatJson::encode(wfMessage('bs-avatars-generate-complete')->plain());
	}

	/**
	 * Clears a user's UserImage setting
	 * @param User $oUser
	 */
	public static function unsetUserImage($oUser) {
		if( $oUser->getOption( 'MW::UserImage' ) ) {
			$oUser->setOption( 'MW::UserImage', false );
			$oUser->saveSettings();
			$oUser->invalidateCache();
		}
		return;
	}

	/**
	 * Generate an avatar image
	 * @param User $oUser
	 * @return string Relative URL to avatar image
	 */
	public function generateAvatar($oUser, $aParams = array(), $bOverwrite = false) {
		$iAvatarDefaultSize = BsConfig::get('MW::Avatars::DefaultSize');
		$iAvatarHeight = ( isset($aParams['height']) ) ? $aParams['height'] : $iAvatarDefaultSize;
		$iAvatarWidth = ( isset($aParams['width']) ) ? $aParams['width'] : $iAvatarDefaultSize;

		$iUserId = $oUser->getId();
		$sUserName = $oUser->getName();
		$sUserRealName = $oUser->getRealName();

		# TODO: Check if this is more expensive than a simple file_exists()
		$oFile = self::getAvatarFile( $iUserId );

		// Prevent fatal when filerepo cannot be found.
		if ( !$oFile ) {
			return '';
		}

		# If avatar doesn't yet exit, create one
		if (!$oFile->exists() || $bOverwrite) {
			$sGenerator = BsConfig::get('MW::Avatars::Generator');
			switch ($sGenerator) {
				case 'Identicon':
					require_once( __DIR__ . "/includes/lib/Identicon/identicon.php" );
					$sRawPNGAvatar = generateIdenticon($iUserId, $iAvatarDefaultSize); # non-random
					break;
				case 'InstantAvatar':
					require_once( __DIR__ . "/includes/lib/InstantAvatar/instantavatar.php" );
					$iFontSize = round(18 / 40 * $iAvatarDefaultSize);
					$oIA = new InstantAvatar(__DIR__ . '/includes/lib/InstantAvatar/Comfortaa-Regular.ttf', $iFontSize, $iAvatarDefaultSize, $iAvatarDefaultSize, 2, __DIR__ . '/includes/lib/InstantAvatar/glass.png');
					if ($sUserRealName) {
						preg_match_all('#(^| )(.)#u', $sUserRealName, $aMatches);
						$sChars = implode('', $aMatches[2]);
						if (mb_strlen($sChars) < 2)
							$sChars = $sUserRealName;
					}
					else {
						$sChars = $sUserName;
					}
					$oIA->generateRandom($sChars); # random
					$sRawPNGAvatar = $oIA->getRawPNG();
					break;
				default:
					throw new MWException('FATAL: Avatar generator not found!');
					break;
			}

			$sAvatarFileName = $oFile->getName();
			$oStatus = BsFileSystemHelper::saveToDataDirectory($sAvatarFileName, $sRawPNGAvatar, 'Avatars');
			if ( !$oStatus->isGood() ) {
				throw new MWException( 'FATAL: Avatar could not be saved! '.$oStatus->getMessage() );
			}
			# found no way to regenerate thumbs. just delete thumb folder if it exists
			$oStatus = BsFileSystemHelper::deleteFolder('Avatars' . DS . 'thumb' . DS . $sAvatarFileName, true);
			if (!$oStatus->isGood())
				throw new MWException('FATAL: Avatar thumbs could no be deleted!');
			$oFile = BsFileSystemHelper::getFileFromRepoName($sAvatarFileName, 'Avatars');

			$oUser->invalidateCache();
		}
		$sNewUserImageSrc = $oFile->createThumb($iAvatarWidth, $iAvatarHeight);
		return $sNewUserImageSrc;
	}

	/**
	 * Create an initial Avatar
	 * @param User $user
	 * @param boolean $autocreated
	 * @return boolean
	 */
	public static function onLocalUserCreated( $user, $autocreated ) {
		$oAvatars = BsExtensionManager::getExtension( 'Avatars' );
		try{
			$sNewPath = $oAvatars->generateAvatar( $user, array(), true );
		} catch( Exception $e ) {
			wfDebugLog(
				'BS::Avatars',
				'onLocalUserCreated: Error: '.$e->getMessage()
			);
		}
		return true;
	}

	/**
	 * Create an initial Avatar
	 * @param UserManager $oUserManager
	 * @param User $oUser
	 * @param array $aMetaData
	 * @param Status $oStatus
	 * @param User $oPerformer
	 * @return boolean
	 */
	public static function onBSUserManagerAfterAddUser( $oUserManager, $oUser, $aMetaData, &$oStatus, $oPerformer ) {
		$oAvatars = BsExtensionManager::getExtension( 'Avatars' );
		try{
			$sNewPath = $oAvatars->generateAvatar( $oUser, array(), true );
		} catch( Exception $e ) {
			wfDebugLog(
				'BS::Avatars',
				'onBSUserManagerAfterAddUser: Error: '.$e->getMessage()
			);
		}
		return true;
	}

	/**
	 * UnitTestsList allows registration of additional test suites to execute
	 * under PHPUnit. Extensions can append paths to files to the $paths array,
	 * and since MediaWiki 1.24, can specify paths to directories, which will
	 * be scanned recursively for any test case files with the suffix "Test.php".
	 * @param array $paths
	 */
	public static function onUnitTestsList( array &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}
}
