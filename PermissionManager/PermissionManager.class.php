<?php

/**
 * PermissionManager extension for BlueSpice
 *
 * Provides information about an article for status bar.
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
 * @author     Sebastian Ulbricht <sebastian.ulbricht@gmx.de>
 * @version    2.23.0
 * @package    BlueSpice_Extensions
 * @subpackage PermissionManager
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Class for managing all usergroup permissions
 * @package BlueSpice_Extensions
 * @subpackage WikiAdmin
 */
class PermissionManager extends BsExtensionMW {

	/**
	 * @var string name of the virtual group which should be used to hold the lockmode settings
	 */
	public static $sPmLockModeGroup = 'lockmode';
	/**
	 * @var array
	 */
	protected static $aGroups = array();
	protected static $aBuiltInGroups = array(
		'autoconfirmed', 'emailconfirmed', 'bot', 'sysop', 'bureaucrat', 'developer'
	);
	/**
	 * @var array
	 */
	protected static $aInvisibleGroups = array('Sysop');
	/**
	 * @var array
	 */
	protected static $aGlobalPermissions = array(
		"apihighlimits", "autoconfirmed", "autopatrol", "bigdelete", "block",
		"blockemail", "bot", "browsearchive", "createaccount", "editinterface",
		"editusercssjs", "editusercss", "edituserjs", "hideuser", "import",
		"importupload", "ipblock-exempt", "move-rootuserpages",
		"override-export-depth", "passwordreset", "proxyunbannable",
		"sendemail", "siteadmin", "unblockself", "userrights",
		"userrights-interwiki", "writeapi", "skipcaptcha", "renameuser", "viewfiles",
		"searchfiles", "wikiadmin"
	);
	/**
	 * @var array Holds all rights which are protected. Protected rights have to be applied to at least one real group
	 *            and get applied automatically to the sysop group, if no other group hold them.
	 */
	protected static $aProtectedPermissions = array(
		'read', 'siteadmin', 'wikiadmin'
	);

	/**
	 * Constructor of PermissionManager
	 */
	public function __construct() {
		wfProfileIn('BS::' . __METHOD__);

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME => 'PermissionManager',
			EXTINFO::DESCRIPTION => 'Administration interface for editing user rights',
			EXTINFO::AUTHOR => 'Sebastian Ulbricht',
			EXTINFO::VERSION => 'default',
			EXTINFO::STATUS => 'default',
			EXTINFO::PACKAGE => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array('bluespice' => '2.23.0')
		);

		WikiAdmin::registerModule('PermissionManager', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_rechteverwaltung_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-permissionmanager-label'
				)
		);

		wfProfileOut('BS::' . __METHOD__);
	}

	protected function initExt() {
		BsConfig::registerVar('MW::PermissionManager::Lockmode', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-permissionmanager-pref-lockmode', 'toggle');
		BsConfig::registerVar('MW::PermissionManager::SkipSystemNS', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-permissionmanager-pref-skipsysns', 'toggle');
		BsConfig::registerVar('MW::PermissionManager::RealityCheck', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-permissionmanager-pref-enablerealitycheck', 'toggle');

		$this->setHook('BSWikiAdminUserManagerBeforeUserListSend');
		$this->setHook('BSGroupManagerGroupNameChanged');
	}

	/**
	 * Hook-Handler for Hook 'LoadExtensionSchemaUpdates'
	 * @param object $updater Updater
	 * @return boolean Always true
	 */
	public static function getSchemaUpdates( $updater ) {
		$updater->addExtensionTable(
			'bs_permission_templates',
			__DIR__.DS .'db'.DS.'PermissionManager.sql'
		);

		return true;
	}

	public function onBSGroupManagerGroupNameChanged($sGroup, $sNewGroup, &$result) {
		global $wgGroupPermissions, $wgNamespacePermissionLockdown;
		$wgGroupPermissions[$sNewGroup] = $wgGroupPermissions[$sGroup];
		unset($wgGroupPermissions[$sGroup]);
		foreach ($wgNamespacePermissionLockdown as $iNs => $aPermissions) {
			foreach ($aPermissions as $sPermission => $aGroups) {
				$iIndex = array_search($sGroup, $aGroups);
				if ($iIndex !== false) {
					array_splice($wgNamespacePermissionLockdown[$iNs][$sPermission], $iIndex, 1, array($sNewGroup));
				}
			}
		}
		$result = PermissionManager::writeGroupSettings($wgGroupPermissions, $wgNamespacePermissionLockdown);
		return true;
	}

	public function onBSGroupManagerGroupDeleted($sGroup, &$result) {
		global $wgGroupPermissions, $wgNamespacePermissionLockdown;
		unset($wgGroupPermissions[$sGroup]);

		foreach ($wgNamespacePermissionLockdown as $iNS => $aPermissions) {
			foreach ($aPermissions as $sPermission => $aGroups) {
				$iIndex = array_search($sGroup, $aGroups);
				if ($iIndex !== false) {
					if (count($aGroups) == 1) {
						unset($wgNamespacePermissionLockdown[$iNS][$sPermission]);
					} else {
						array_splice($wgNamespacePermissionLockdown[$iNS][$sPermission], $iIndex, 1);
					}
				}
			}
		}

		$result = PermissionManager::writeGroupSettings($wgGroupPermissions, $wgNamespacePermissionLockdown);
		return true;
	}

	public function onBSWikiAdminUserManagerBeforeUserListSend($oUserManager, &$data) {
		if (!BsConfig::get('MW::PermissionManager::Lockmode'))
			return true;

		foreach ($data['users'] as $keyname => $aUser) {
			foreach ($aUser as $index => $value) {
				if (is_array($value)) {
					foreach ($value as $indexof => $val) {
						if (is_array($val)) {
							foreach ($val as $indexname => $groupName) {
								if ($indexname == 'group') {
									if ($groupName == BsGroupHelper::getLockModeGroup()) {
										unset($data['users'][$keyname][$index][$indexof]);
										$data['users'][$keyname][$index] = array_values($data['users'][$keyname][$index]);
									}
								}
							}
						}
					}
				}
			}
		}

		return true;
	}

	public static function setupLockmodePermissions() {
		global $wgAdditionalGroups, $wgGroupPermissions, $wgNamespacePermissionLockdown;
		if (!BsConfig::get('MW::PermissionManager::Lockmode')) {
			$bSave = false;
			if (isset($wgGroupPermissions[self::$sPmLockModeGroup])) {
				unset($wgGroupPermissions[self::$sPmLockModeGroup]);
				$bSave = true;
			}

			if ( is_array( $wgNamespacePermissionLockdown ) ) {
				foreach ($wgNamespacePermissionLockdown as $iNsIndex => $aNsRights) {
					foreach ($aNsRights as $sRight => $aGroups) {
						if (!in_array(self::$sPmLockModeGroup, $aGroups))
							continue;
						$key = array_search(self::$sPmLockModeGroup, $aGroups);
						if ($key !== false) {
							unset($wgNamespacePermissionLockdown[$iNsIndex][$sRight][$key]);
							if (empty($wgNamespacePermissionLockdown[$iNsIndex][$sRight])) {
								unset($wgNamespacePermissionLockdown[$iNsIndex][$sRight]);
							}
							$bSave = true;
						}
					}
					if (empty($wgNamespacePermissionLockdown[$iNsIndex])) {
						unset($wgNamespacePermissionLockdown[$iNsIndex]);
					}
				}
			}

			if ($bSave) {
				self::writeGroupSettings($wgGroupPermissions, $wgNamespacePermissionLockdown);
			}

			return true;
		}
		$wgAdditionalGroups[self::$sPmLockModeGroup] = array();
		foreach (BsNamespaceHelper::getNamespacesForSelectOptions(array(NS_MEDIA, NS_SPECIAL)) as $nsKey => $nsName) {
			// skip mediawiki namespaces
			if (BsConfig::get('MW::PermissionManager::SkipSystemNS') && $nsKey <= 15) {
				continue;
			}

			$aAvailablePermissions = User::getAllRights();
			foreach ($aAvailablePermissions as $permissionName) {
				$wgGroupPermissions[self::$sPmLockModeGroup][$permissionName] = true;
				if (isset($wgNamespacePermissionLockdown[$nsKey][$permissionName])) {
					$wgNamespacePermissionLockdown[$nsKey][$permissionName] = array_unique(
							array_merge($wgNamespacePermissionLockdown[$nsKey][$permissionName], array(self::$sPmLockModeGroup)
							)
					);
				} else {
					$wgNamespacePermissionLockdown[$nsKey][$permissionName] = array(self::$sPmLockModeGroup);
				}
			}
		}

		self::writeGroupSettings($wgGroupPermissions, $wgNamespacePermissionLockdown);

		return true;
	}

	public function getForm() {
		global $wgImplicitGroups, $wgGroupPermissions, $wgNamespacePermissionLockdown;

		$this->getOutput()->addModules('ext.bluespice.permissionManager');

		$aGroups = array(
			'text' => '*',
			'builtin' => true,
			'implicit' => true,
			'expanded' => true,
			'children' => array(
				array(
					'text' => 'user',
					'builtin' => true,
					'implicit' => true,
					'expanded' => true,
					'children' => array()
				)
			)
		);

		$aExplicitGroups = BsGroupHelper::getAvailableGroups(
						array('blacklist' => $wgImplicitGroups)
		);

		sort($aExplicitGroups);

		$aExplicitGroupNodes = array();
		foreach ($aExplicitGroups as $sExplicitGroup) {
			$aExplicitGroupNode = array(
				'text' => $sExplicitGroup,
				'leaf' => true
			);

			if (in_array($sExplicitGroup, self::$aBuiltInGroups)) {
				$aExplicitGroupNode['builtin'] = true;
			}

			$aExplicitGroupNodes[] = $aExplicitGroupNode;
		}

		$aGroups['children'][0]['children'] = $aExplicitGroupNodes;

		$aJsVars = array(
			'bsPermissionManagerGroupsTree' => $aGroups,
			'bsPermissionManagerNamespaces' => self::buildNamespaceMetadata(),
			'bsPermissionManagerRights' => self::buildRightsMetadata(),
			'bsPermissionManagerGroupPermissions' => $wgGroupPermissions,
			'bsPermissionManagerPermissionLockdown' => $wgNamespacePermissionLockdown,
			'bsPermissionManagerPermissionTemplates' => self::getTemplateRules()
		);

		wfRunHooks('BsPermissionManager::beforeLoadPermissions', array(&$aJsVars));

		$this->getOutput()->addJsConfigVars($aJsVars);

		return '<div id="panelPermissionManager" style="height: 500px"></div>';
	}

	protected static function buildNamespaceMetadata() {
		global $wgLang;

		$aNamespaces = $wgLang->getNamespaces();
		ksort($aNamespaces);

		$aMetadata = array();

		foreach ($aNamespaces as $iNSId => $sLocalizedNSText) {
			if( $iNSId < 0 ) { //Filter pseudo namespaces
				continue;
			}

			$sNsText = str_replace('_', ' ', $sLocalizedNSText);
			if( $iNSId == NS_MAIN ) {
				$sNsText = wfMessage('bs-ns_main')->text();
			}

			$aMetadata[] = array(
				'id' => $iNSId,
				'name' => $sNsText,
				'hideable' => $iNSId !== NS_MAIN
			);
		}

		return $aMetadata;
	}

	protected static function buildRightsMetadata() {
		$aRights = User::getAllRights();
		$aMetadata = array();

		natsort( $aRights );
		foreach($aRights as $sRight) {
			$bGlobalPermission = in_array( $sRight, self::$aGlobalPermissions );
			$aMetadata[] = array(
				'right' => $sRight,
				'type' => $bGlobalPermission ? 2 : 1,
				'typeHeader' => $bGlobalPermission
					? wfMessage('bs-permissionmanager-grouping-global')->plain()
					: wfMessage('bs-permissionmanager-grouping-local')->plain()
			);
		}

		wfRunHooks('BsPermissionManager::buildRightsMetadata', array(&$aMetadata));

		return $aMetadata;
	}

	public static function setTemplateData() {
		global $wgRequest;

		$dbw = wfGetDB(DB_WRITE);
		$oTemplate = $data = FormatJson::decode($wgRequest->getVal('template', '{}'));

		$iId = $oTemplate->id + 0;
		$sName = $dbw->strencode($oTemplate->text);
		$aPermissions = $oTemplate->ruleSet;
		$sDescription = $dbw->strencode($oTemplate->description);

		if ($iId == 0) {
			$bSaveResult = PermissionTemplates::addTemplate($sName, $aPermissions, $sDescription);
		} else {
			$bSaveResult = PermissionTemplates::editTemplate($iId, $sName, $aPermissions, $sDescription);
		}
		$aResult = array(
			'success' => false,
			'msg' => ''
		);

		if ($bSaveResult) {
			$aResult['success'] = true;
		} else {
			$aResult['msg'] = wfMessage('bs-permissionmanager-msgtpled-savefailure')->plain();
		}

		return json_encode($aResult);
	}

	public static function deleteTemplate() {
		global $wgRequest;

		$iId = $wgRequest->getInt('id', 0);

		if ($iId) {
			$bDeleteResult = PermissionTemplates::removeTemplate($iId);
		} else {
			$bDeleteResult = false;
		}
		$aResult = array(
			'success' => false,
			'msg' => ''
		);

		if ($bDeleteResult) {
			$aResult['success'] = true;
		} else {
			$aResult['msg'] = wfMessage('bs-permissionmanager-msgtpled-deletefail')->plain();
		}

		return json_encode($aResult);
	}

	/**
	 * @global WebRequest $wgRequest
	 * @return string
	 */
	public static function savePermissions() {
		global $wgRequest;
		$data = FormatJson::decode($wgRequest->getVal('data', '{}'), true);

		if(!is_array($data) || !isset($data['groupPermission']) || !isset($data['permissionLockdown'])) {
			return json_encode(array(
				'success' => false,
				'msg' => 'NO VALID DATA'
			));
		}

		$aGroupPermissions = $data['groupPermission'];
		$aLockdown = $data['permissionLockdown'];
		$mStatus = wfRunHooks('BsPermissionManager::beforeSavePermissions', array(&$aLockdown, &$aGroupPermissions));

		if($mStatus === true) {
			return FormatJson::encode(
				self::writeGroupSettings( $aGroupPermissions, $aLockdown )
			);
		} else {
			return FormatJson::encode(
				array(
					'success' => false,
					'msg' => $mStatus
				)
			);
		}
	}

	/**
	 * Prevents that the wiki gets accidentally inaccessible for all users.
	 * All rights which are noted in @see PermissionManager::$aProtectedPermssions will be applied automatically to the
	 * sysop group, if no other group holds them.
	 *
	 * @param array $aGroupPermissions
	 */
	protected static function preventPermissionLockout(&$aGroupPermissions) {
		foreach(self::$aProtectedPermissions as $sRight) {
			$isSet = false;
			foreach($aGroupPermissions as $aDataset) {
				if(isset($aDataset[$sRight]) && $aDataset[$sRight]) {
					$isSet = true;
				}
			}
			if(!$isSet) {
				$aGroupPermissions['sysop'][$sRight] = true;
			}
		}
	}

	protected static function getTemplateRules() {
		$aTemplates = PermissionTemplates::getAll();
		$aOutput = array();

		/* @var $oTemplate PermissionTemplates */
		foreach ($aTemplates as $oTemplate) {
			$aOutput[] = array(
				'id' => $oTemplate->getId(),
				'text' => $oTemplate->getName(),
				'leaf' => true,
				'description' => $oTemplate->getDescription(),
				'ruleSet' => $oTemplate->getPermissions()
			);
		}

		return $aOutput;
	}

	protected static function writeGroupSettings($aGroupPermissions, $aNamespacePermissionLockdown) {
		global $bsgPermissionManagerGroupSettingsFile;

		if (wfReadOnly()) {
			global $wgReadOnly;
			return array(
				'success' => false,
				'msg' => wfMessage('bs-readonly', $wgReadOnly)->plain()
			);
		}
		if (BsCore::checkAccessAdmission('wikiadmin') === false)
			return true;

		wfRunHooks('BsNamespacemanageOnSavePermission', array(&$aNamespacePermissionLockdown, &$aGroupPermissions));
		wfRunHooks('BsPermissionManager::writeGroupSettings', array(&$aNamespacePermissionLockdown, &$aGroupPermissions));

		self::backupExistingSettings();

		$sSaveContent = "<?php\n";
		foreach ($aGroupPermissions as $sGroup => $aPermissions) {
			foreach ($aPermissions as $sPermission => $bValue) {
				$sSaveContent .= "\$wgGroupPermissions['{$sGroup}']['{$sPermission}'] = " . ($bValue ? 'true' : 'false') . ";\n";
			}
		}

		if (is_array($aNamespacePermissionLockdown)) {
			foreach ($aNamespacePermissionLockdown as $iNS => $aPermissions) {
				$isReadLockdown = false;
				$sNsCanonicalName = MWNamespace::getCanonicalName( $iNS );
				if( $iNS == NS_MAIN ) {
					$sNsCanonicalName = 'MAIN';
				}
				$sNsConstant = 'NS_'.strtoupper( $sNsCanonicalName );
				foreach ($aPermissions as $sPermission => $aGroups) {
					if( empty( $aGroups ) ) {
						continue;
					}
					$sSaveContent .= "\$wgNamespacePermissionLockdown[$sNsConstant]['$sPermission']"
							. " = array(" . (count($aGroups) ? "'" . implode("','", $aGroups) . "'" : '') . ");\n";
					if ($sPermission == 'read') {
						$isReadLockdown = true;
					}
				}
				if ($isReadLockdown) {
					$sSaveContent .= "\$wgNonincludableNamespaces[] = $sNsConstant;\n";
				}
			}
		}

		$res = file_put_contents($bsgPermissionManagerGroupSettingsFile, $sSaveContent);
		if ($res) {
			return array('success' => true);
		} else {
			return array(
				'success' => false,
				// TODO SU (04.07.11 12:06): i18n
				'msg' => 'Not able to create or write "' . BSROOTDIR . DS . 'config' . DS . 'pm-settings.php".'
			);
		}
	}

	/**
	 * creates a backup of the current pm-settings.php if it exists.
	 *
	 * @global string $bsgPermissionManagerGroupSettingsFile
	 */
	protected static function backupExistingSettings() {
		global $bsgPermissionManagerGroupSettingsFile;

		if(file_exists($bsgPermissionManagerGroupSettingsFile)) {
			$timestamp = wfTimestampNow();
			$backupFilename = "pm-settings-backup-{$timestamp}.php";
			$backupFile = dirname($bsgPermissionManagerGroupSettingsFile)."/{$backupFilename}";

			file_put_contents($backupFile, file_get_contents($bsgPermissionManagerGroupSettingsFile));
		}
	}
}
