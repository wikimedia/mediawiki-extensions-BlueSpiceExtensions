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
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
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
 * @version    2.22.0

 * @package    BlueSpice_Extensions
 * @subpackage PermissionManager
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
// Last review MRG (01.07.11 12:51)

/**
 * Class for managing all usergroup permissions
 * @package BlueSpice_Extensions
 * @subpackage WikiAdmin
 */
class PermissionManager extends BsExtensionMW {

	public static $sPmLockModeGroup = 'lockmode';
	protected static $aGroups = array();
	protected static $aInvisibleGroups = array('Sysop');
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
			EXTINFO::DESCRIPTION => wfMessage( 'bs-permissionmanager-desc' )->parse(),
			EXTINFO::AUTHOR => 'Sebastian Ulbricht',
			EXTINFO::VERSION => 'default',
			EXTINFO::STATUS => 'default',
			EXTINFO::PACKAGE => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array('bluespice' => '2.22.0')
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
	 * @param object §updater Updater
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

			$aAvailablePermissions = self::getAllPermissions();
			foreach ($aAvailablePermissions as $permissionName) {
				$wgGroupPermissions[self::$sPmLockModeGroup][$permissionName] = true;
				if (isset($wgNamespacePermissionLockdown[$nsKey][$permissionName])) {
					//if( in_array( self::$sPmLockModeGroup, $wgNamespacePermissionLockdown[$nsKey][$permissionName] ) && count( $wgNamespacePermissionLockdown[$nsKey][$permissionName] ) == 1 ) continue;
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
		$this->getOutput()->addModules('ext.bluespice.permissionManager');
		return '<div id="panelPermissionManager"></div><div id="panelPermissionManagerExtra"></div>';
	}

	public static function getGroupNames() {
		$groups = self::getAllGroups();
		$output = array();

		foreach ($groups as $group) {
			$output[] = array(
				'groupName' => $group
			);
		}

		return json_encode($output);
	}

	/**
	 * Creates a virtual user which belongs to all given groups and checks all
	 * the given permissions agains a virtual title in every namespace.
	 *
	 * Returns an array of the form
	 * <code>
	 * array(
	 *     'permission1' => array(
	 *         'ns1' => true,
	 *         'ns2' => false,
	 *         ...
	 *     ),
	 *     'permission2' => array(
	 *         'ns1' => false,
	 *         'ns2' => false,
	 *         ...
	 *     )
	 * )
	 * </code>
	 *
	 * @global Language $wgLang
	 * @param string|array $groups
	 * @param string|array $permissions
	 * @return array
	 */
	public static function checkRealPermissions($groups, $permissions) {
		global $wgLang;

		if (!is_array($groups)) {
			$groups = array($groups);
		}

		if (!is_array($permissions)) {
			$permissions = array($permissions);
		}

		$checkUser = new PMCheckUser();
		$checkUser->setGroups($groups);

		$namespaces = $wgLang->getNamespaces();
		$permissionMap = array();

		foreach ($permissions as $permission) {
			foreach ($namespaces as $nsId => $nsName) {
				$permissionMap[$permission][$nsId] = false;

				if (!$checkUser->isAllowed($permission)) {
					continue;
				}

				$checkTitle = Title::makeTitle($nsId, 'Check_permission_title');
				if ($checkTitle->userCan($permission, $checkUser, false)) {
					$permissionMap[$permission][$nsId] = true;
				}
			}
		}

		return $permissionMap;
	}

	/**
	 *
	 * @global array $wgGroupPermissions
	 * @global array $wgNamespacePermissionLockdown
	 * @global WebRequest $wgRequest
	 * @return string
	 */
	public static function getAccessRules($group = 'user') {
		global $wgGroupPermissions, $wgNamespacePermissionLockdown, $wgRequest;

		// if there are no rules for this group, we return a empty array
		if (!isset($wgGroupPermissions[$group])) {
			return json_array(array());
		}

		// initialise the basic data
		$permissions = self::getAllPermissions();
		$namespaces = self::getAllNamespaces();
		$namespaces[0] = trim( wfMessage('bs-ns_main')->plain(), '()' );
		$rules = array();

		$permissionMap = self::checkRealPermissions($group, $permissions);

		// one ruleset per permission
		foreach ($permissions as $permission) {
			// default: restricted
			$permitted = false;
			$rule = array(
				'permission' => $permission,
				'tip' => User::getRightDescription($permission),
				'isGlobal' => false,
				'isTemplate' => false
			);

			// if there is a rule for this group and permission
			if (isset($wgGroupPermissions[$group][$permission])) {
				// save that rule
				$permitted = $wgGroupPermissions[$group][$permission];
			}

			$rule['global'] = $permitted;

			if (!in_array($group, array('*', 'user'))) {
				if (!$permitted) {
					if (isset($wgGroupPermissions['user'][$permission])) {
						$permitted = $wgGroupPermissions['user'][$permission];
					}
				}
				if (!$permitted) {
					if (isset($wgGroupPermissions['*'][$permission])) {
						$permitted = $wgGroupPermissions['*'][$permission];
					}
				}
			}
			$rule['global_allowed'] = $permitted;

			// if this permission is a global permission
			if (in_array($permission, self::$aGlobalPermissions)) {
				// save that information in the ruleset
				$rule['isGlobal'] = true;
			}

			if ($rule['isGlobal']) {
				$rule['grouping'] = wfMessage('bs-permissionmanager-grouping-global')->plain();
			} else {
				$rule['grouping'] = wfMessage('bs-permissionmanager-grouping-local')->plain();
			}

			// go throught all namespaces
			foreach ($namespaces as $namespaceId => $namespaceName) {
				$ns_permitted = false;
				$ns_allowed = $permitted;
				// if the permission is not global and the group has the permission
				if (!$rule['isGlobal'] && $permitted) {
					// search a matching lockdown rule
					$groups = @$wgNamespacePermissionLockdown[$namespaceId][$permission];
					// if we found a match, we test it
					if (is_array($groups)) {
						// if the group is not in the rule, this permission is restricted
						if (in_array($group, $groups)) {
							$ns_permitted = true;
						} else {
							$ns_allowed = false;
						}
					} else {
						if ($groups === null) {
							$groups = @$wgNamespacePermissionLockdown['*'][$permission];
						}
						if ($groups === null) {
							$groups = @$wgNamespacePermissionLockdown[$namespaceId]['*'];
						}
						if (is_array($groups)) {
							if (!in_array($group, $groups)) {
								$ns_allowed = false;
							}
						}
					}
				}

				// save the namespace information in the ruleset
				$rule[$namespaceName] = $ns_permitted;
				$rule[$namespaceName . '_allowed'] = $permissionMap[$permission][$namespaceId]; //$ns_allowed;
			}
			// add the ruleset to the rule selection
			$rules[] = $rule;
		}

		$templates = self::getTemplateRules();

		$namespaceList = array();
		natsort($namespaces);
		foreach ($namespaces as $namespaceId => $namespaceName) {
			if (in_array($namespaceId, array(0, 1))) {
				continue;
			}
			$namespaceList[] = array('id' => $namespaceId, 'name' => $namespaceName);
		}
		array_unshift($namespaceList, array('id' => 0, 'name' => $namespaces[0]), array('id' => 1, 'name' => $namespaces[1])
		);

		return json_encode(
				array(
					'data' => array(
						'activeGroup' => $group,
						'btnGroup' => self::buildGroupButton($group),
						'namespaces' => $namespaceList,
						'rules' => $rules,
						'templates' => $templates
					)
				)
		);
	}

	public static function getGroupAccessData() {
		global $wgGroupPermissions, $wgNamespacePermissionLockdown;

		$aAccessData = array();

		$aPermissions = self::getAllPermissions();
		$aGroups = self::getAllGroups();
		$aNamespaces = self::getAllNamespaces();
		$aNamespaces[0] = trim( wfMessage('bs-ns_main')->plain(), '()' );

		$aRealPermissionMaps = array();
		foreach ($aGroups as $sGroup) {
			$aRealPermissionMaps[$sGroup] = self::checkRealPermissions($sGroup, $aPermissions);
		}

		foreach ($aPermissions as $sPermission) {
			$aDataSet = array(
				'permission' => $sPermission,
				'data' => array()
			);

			foreach ($aGroups as $sGroup) {
				if (!wfMessage('group-' . $sGroup)->inContentLanguage()->isBlank()) {
					$sDisplayName = wfMessage('group-' . $sGroup)->plain() . " (" . $sGroup . ")";
				} else {
					$sDisplayName = $sGroup;
				}
				$aSet = array(
					'group' => $sDisplayName,
					'group_value' => $sGroup
				);

				if (isset($wgGroupPermissions[$sGroup]) && isset($wgGroupPermissions[$sGroup][$sPermission]) && $wgGroupPermissions[$sGroup][$sPermission]) {
					$aSet['global'] = true;
				} else {
					$aSet['global'] = false;
				}

				$permitted = $aSet['global'];
				if (!in_array($sGroup, array('*', 'user'))) {
					if (!$permitted) {
						if (isset($wgGroupPermissions['user'][$sPermission])) {
							$permitted = $wgGroupPermissions['user'][$sPermission];
						}
					}
					if (!$permitted) {
						if (isset($wgGroupPermissions['*'][$sPermission])) {
							$permitted = $wgGroupPermissions['*'][$sPermission];
						}
					}
				}
				$aSet['global_allowed'] = $permitted;

				foreach ($aNamespaces as $iNsId => $sNsName) {
					if (!$aSet['global']) {
						$aSet[$sNsName] = false;
					} else {
						if (isset($wgNamespacePermissionLockdown[$iNsId]) && isset($wgNamespacePermissionLockdown[$iNsId][$sPermission]) && $wgNamespacePermissionLockdown[$iNsId][$sPermission] && in_array($sGroup, $wgNamespacePermissionLockdown[$iNsId][$sPermission])) {
							$aSet[$sNsName] = true;
						} else {
							$aSet[$sNsName] = false;
						}
					}
					$aSet[$sNsName . '_allowed'] = $aRealPermissionMaps[$sGroup][$sPermission][$iNsId];
				}

				$aDataSet['data'][] = $aSet;
			}

			$aAccessData[] = $aDataSet;
		}
		return json_encode($aAccessData);
	}

	public static function setTemplateData() {
		$dbw = wfGetDB(DB_WRITE);
		$oTemplate = json_decode($_POST['template']);

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

	public static function setAccessRules() {
		global $wgGroupPermissions, $wgNamespacePermissionLockdown, $wgLang;

		$rules = json_decode($_POST['rules']);

		if (!is_array($rules)) {
			$rules = array($rules);
		}
		$aGroupPermissions = $wgGroupPermissions;
		$aLockdown = array();

		foreach ($rules as $rule) {
			if ($rule->isTemplate === true) {
				continue;
			}
			$group = $rule->group;
			$permission = $rule->permission;
			$isGlobal = in_array($permission, self::$aGlobalPermissions);
			$mainSpace = trim( wfMessage('bs-ns_main')->plain(), '()' );
			if (in_array($group, self::$aInvisibleGroups)) {
				continue;
			}

			$aGroupPermissions[$group][$permission] = $rule->global;

			if (!$isGlobal) {

				unset($rule->group, $rule->permission, $rule->isGlobal,
						$rule->grouping, $rule->global, $rule->isTemplate,
						$rule->tip);

				foreach ($rule as $namespace => $permitted) {
					if (strpos($namespace, '_allowed') !== false) {
						continue;
					}
					if ($namespace == $mainSpace) {
						$namespaceId = 0;
					} else {
						$namespaceId = $wgLang->getNsIndex($namespace);
					}
					if ($permitted === true && $namespaceId !== false) {
						$aLockdown[$namespaceId][$permission][] = $group;

					}
				}
			}
		}

		if (!empty($aGroupPermissions)) {
			$tmp = $wgGroupPermissions;
			foreach ($aGroupPermissions as $groupName => $permissions) {
				$tmp[$groupName] = $permissions;
			}
			$aGroupPermissions = $tmp;

			if (!empty($aLockdown)) {

				$tmp = $wgNamespacePermissionLockdown;
				foreach ($tmp as $namespaceId => $permissions) {
					foreach ($permissions as $permission => $groups) {
						$matches = array_keys($groups, $group);
						if ($matches !== false) {
							foreach ($matches as $key) {
								unset($tmp[$namespaceId][$permission][$key]);
							}
						}
					}
				}

				foreach ($aLockdown as $namespaceId => $permissions) {
					foreach ($permissions as $permission => $groups) {
						foreach ($groups as $group) {
							if (!isset($tmp[$namespaceId]) ||
									!isset($tmp[$namespaceId][$permission]) ||
									!in_array($group, $tmp[$namespaceId][$permission])) {
								$tmp[$namespaceId][$permission][] = $group;
							}
						}
					}
				}

				$aLockdown = $tmp;
			}
		}

		return json_encode(self::writeGroupSettings($aGroupPermissions, $aLockdown));
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

		$sSaveContent = "<?php\n";
		foreach ($aGroupPermissions as $sGroup => $aPermissions) {
			foreach ($aPermissions as $sPermission => $bValue) {
				$sSaveContent .= "\$wgGroupPermissions['{$sGroup}']['{$sPermission}'] = " . ($bValue ? 'true' : 'false') . ";\n";
			}
		}

		if (is_array($aNamespacePermissionLockdown)) {
			foreach ($aNamespacePermissionLockdown as $iNS => $aPermissions) {
				$isReadLockdown = false;
				foreach ($aPermissions as $sPermission => $aGroups) {
					if( empty( $aGroups ) ) {
						continue;
					}
					$sSaveContent .= "\$wgNamespacePermissionLockdown[$iNS]['$sPermission']"
							. " = array(" . (count($aGroups) ? "'" . implode("','", $aGroups) . "'" : '') . ");\n";
					if ($sPermission == 'read') {
						$isReadLockdown = true;
					}
				}
				if ($isReadLockdown) {
					$sSaveContent .= "\$wgNonincludableNamespaces[] = $iNS;\n";
				}
			}
		}

		$res = file_put_contents(BSROOTDIR . DS . 'config' . DS . 'pm-settings.php', $sSaveContent);
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

	protected static function buildGroupButton($group) {
		$menu = array();
		$groups = self::getAllGroups();

		foreach ($groups as $groupName) {
			if (!wfMessage('group-' . $groupName)->inContentLanguage()->isBlank()) {
				$sDisplayName = wfMessage('group-' . $groupName)->plain() . " (" . $groupName . ")";
			} else {
				$sDisplayName = $groupName;
			}
			$menu[] = array(
				'text' => $sDisplayName,
				'value' => $groupName,
				'checked' => $groupName == $group
			);
		}

		return $menu;
	}

	/**
	 * @global Language $wgLang
	 * @return array
	 */
	protected static function getAllNamespaces() {
		global $wgLang;

		return $wgLang->getNamespaces();
	}

	/**
	 * @return array
	 */
	protected static function getAllPermissions() {
		return User::getAllRights();
	}

	/**
	 * @return array
	 */
	protected static function getAllGroups() {
		return BsGroupHelper::getAvailableGroups(array('blacklist' => self::$aInvisibleGroups));
	}

}
