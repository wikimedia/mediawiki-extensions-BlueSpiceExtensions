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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Marc Reymann <reymann@hallowelt.biz>
 * @version    2.22.0 stable
 * @version    $Id$
 * @package    BlueSpice_Extensions
 * @subpackage Avatars
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
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
	 * Constructor of Avatars class
	 */
	public function __construct() {
		wfProfileIn('BS::' . __METHOD__);

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME => 'Avatars',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-avatars-desc' )->escaped(),
			EXTINFO::AUTHOR => 'Marc Reymann',
			EXTINFO::VERSION => 'default',
			EXTINFO::STATUS => 'default',
			EXTINFO::PACKAGE => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array('bluespice' => '2.22.0'));
		$this->mExtensionKey = 'MW::Avatars';
		wfProfileOut('BS::' . __METHOD__);
	}

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
		if ($oUser->getOption('MW::UserImage')) {
			return true;
		}
		# Set default image in read-only mode or thumb creation might get triggered
		if (wfReadOnly()) {
			$oUserMiniProfileView->setUserImageSrc(BsConfig::get('MW::DefaultUserImage'));
			return true;
		}
		# Set or generate user's avatar
		$oUserMiniProfileView->setUserImageSrc($this->generateAvatar($oUser, $aParams));
		return true;
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
	 * Sets a user's UserImage setting to a URL or Wiki image
	 * @param string $sUserImage
	 * @return type
	 */
	public static function setUserImage($sUserImage) {
		if (wfReadOnly()) {
			global $wgReadOnly;
			return FormatJson::encode(array(
						'success' => false,
						'message' => array(wfMessage('bs-readonly', $wgReadOnly)->escaped())
			));
		}
		// check if string is URL or valid file
		$oFile = wfFindFile($sUserImage);
		$bIsImage = is_object($oFile) && $oFile->canRender();
		if (!wfParseUrl($sUserImage) && !$bIsImage) {
			return FormatJson::encode(array(
						'success' => false,
						'message' => array(wfMessage('bs-avatars-set-userimage-failed')->plain())
			));
		} else {
			$oUser = RequestContext::getMain()->getUser();
			$oUser->setOption('MW::UserImage', $sUserImage);
			$oUser->saveSettings();

			return FormatJson::encode(array(
						'success' => true,
						'message' => array(wfMessage('bs-avatars-set-userimage-saved')->plain())
			));
		}
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
		if ($oUser->getOption('MW::UserImage')) {
			$oUser->setOption('MW::UserImage', null);
			$oUser->saveSettings();
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

		$sAvatarFileName = self::$sAvatarFilePrefix . $iUserId . ".png";
		# TODO: Check if this is more expensive than a simple file_exists()
		$oFile = BsFileSystemHelper::getFileFromRepoName($sAvatarFileName, 'Avatars');

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
			$oStatus = BsFileSystemHelper::saveToDataDirectory($sAvatarFileName, $sRawPNGAvatar, $this->mInfo[EXTINFO::NAME]);
			if (!$oStatus->isGood())
				throw new MWException('FATAL: Avatar could not be saved!');
			# found no way to regenerate thumbs. just delete thumb folder if it exists
			$oStatus = BsFileSystemHelper::deleteFolder('Avatars' . DS . 'thumb' . DS . $sAvatarFileName, true);
			if (!$oStatus->isGood())
				throw new MWException('FATAL: Avatar thumbs could no be deleted!');
			$oFile = BsFileSystemHelper::getFileFromRepoName($sAvatarFileName, 'Avatars');
		}
		$sNewUserImageSrc = $oFile->createThumb($iAvatarWidth, $iAvatarHeight);
		return $sNewUserImageSrc;
	}

	public static function uploadFile() {
		if (wfReadOnly()) {
			global $wgReadOnly;
			$oAjaxResponse = new AjaxResponse(FormatJson::encode(array('success' => false, 'msg' => wfMessage('bs-readonly', $wgReadOnly)->escaped())));
			$oAjaxResponse->setContentType('text/html');
			return $oAjaxResponse;
		}
		global $wgRequest, $wgUser;
		self::unsetUserImage($wgUser);
		$oAvatars = BsExtensionManager::getExtension('Avatars');
		$sAvatarFileName = self::$sAvatarFilePrefix . $wgUser->getId() . ".png";
		$oStatus = BsFileSystemHelper::uploadAndConvertImage($wgRequest->getVal('name'), $oAvatars->mInfo[EXTINFO::NAME], $sAvatarFileName);
		if (!$oStatus->isGood()) {
			$aErrors = $oStatus->getErrorsArray();
			$aResult = json_encode(array('success' => false, 'msg' => $aErrors[0][0]));
		} else {
			$aResult = json_encode(array('success' => true, 'msg' => wfMessage('bs-avatars-upload-complete')->plain(), 'name' => $oStatus->getValue()));
			# found no way to regenerate thumbs. just delete thumb folder if it exists
			$oStatus = BsFileSystemHelper::deleteFolder('Avatars' . DS . 'thumb' . DS . $sAvatarFileName, true);
			if (!$oStatus->isGood())
				throw new MWException('FATAL: Avatar thumbs could no be deleted!');
		}
		$oAjaxResponse = new AjaxResponse($aResult);
		$oAjaxResponse->setContentType('text/html');
		return $oAjaxResponse;
	}

}
