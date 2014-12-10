<?php
/**
 * This is the GroupManager class.
 *
 * The GroupManager offers an easy way to manage the usergroups of the wiki.
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
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    2.22.0
 * @package    Bluespice_Extensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * the GroupManager class
 * @package BlueSpice_Extensions
 * @subpackage GroupManager
 */
class GroupManager extends BsExtensionMW {

	/**
	 * constructor for GroupManager class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'GroupManager',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-groupmanager-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::GroupManager';

		WikiAdmin::registerModule('GroupManager', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_gruppe_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-groupmanager-label'
			)
		);
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * offers the formular for the group manager
	 * @return string the formular code
	 */
	public function getForm() {
		$this->getOutput()->addModules( 'ext.bluespice.groupManager' );
		BsExtensionManager::setContext( 'MW::GroupManagerShow' );
		$sForm = '<div id="bs-groupmanager-grid"></div>';
		return $sForm;
	}

	/**
	 * returns a json object which hold the data of all existing usergroups
	 * @param string $output the ajax output string
	 */
	public static function getData() {
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;
		global $wgGroupPermissions, $wgAdditionalGroups;

		$oStoreParams = BsExtJSStoreParams::newFromRequest();
		$iLimit     = $oStoreParams->getLimit();
		$iStart     = $oStoreParams->getStart();
		$sSort      = $oStoreParams->getSort( 'group_name' );
		$sDirection = $oStoreParams->getDirection();

		$aGroups = array();
		foreach ( BsGroupHelper::getAvailableGroups() as $sGroup ) {
			$aGroups['groups'][] = array(
				'group_name' => $sGroup,
				'additional_group' => ( isset( $wgAdditionalGroups[$sGroup] ) )
			);
		}

		if ( $sDirection == 'DESC' ) {
			usort( $aGroups['groups'], function ($a, $b) { return strnatcasecmp($b["group_name"], $a["group_name"]); });
		} else {
			usort( $aGroups['groups'], function ($a, $b) { return strnatcasecmp($a["group_name"], $b["group_name"]); });
		}

		$aGroups['totalCount'] = sizeof( $aGroups['groups'] );

		// Apply limit and offset
		$aGroups['groups'] = array_slice( $aGroups['groups'], $iStart, $iLimit );


		return FormatJson::encode( $aGroups );
	}

	/**
	 * returns a json object which hold the data of all existing usergroups
	 * @param string $output the ajax output string
	 */
	public static function getGroups() {
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;
		global $wgGroupPermissions;

		$aGroups = array();
		foreach ( BsGroupHelper::getAvailableGroups() as $sGroup ) {
			if ( in_array( $sGroup, array( '*', 'user', 'autoconfirmed', 'emailconfirmed' ) ) ) continue;
			if ( !wfMessage( 'group-' . $sGroup )->inContentLanguage()->isBlank() ) {
				$sDisplayName = wfMessage( 'group-' . $sGroup )->plain() . " (" . $sGroup . ")";
			} else {
				$sDisplayName = $sGroup;
			}

			$aGroups[] = array(
				'group' => $sGroup,
				'displayname' => $sDisplayName
			);
		}

		return FormatJson::encode( array( 'groups' => $aGroups ) );
	}

	/**
	 * adds an usergroup to the wiki
	 * @param string $output the ajax output string
	 */
	public static function addGroup( $sGroup ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		// TODO SU (04.07.11 11:40): global sind leider hier noch nötig, da werte in den globals geändert werden müssen.
		global $wgGroupPermissions, $wgAdditionalGroups;

		$output = FormatJson::encode( array(
			'success' => true,
			'message' => wfMessage( 'bs-groupmanager-grpadded' )->plain() )
		);

		if ( array_key_exists( $sGroup, $wgAdditionalGroups ) ) {
			return FormatJson::encode( array(
					'success' => false,
					'msg' => wfMessage( 'bs-groupmanager-grpexists' )->plain()
				)
			);
		}

		if ( !empty( $sGroup ) ) {
			if ( isset( $wgGroupPermissions[$sGroup] ) ) {
				return $output;
			}
			$wgAdditionalGroups[$sGroup] = true;
			return FormatJson::encode( BsExtensionManager::getExtension( 'GroupManager' )->saveData() );
		}
	}

	/**
	 * changes the name of a given usergroup.
	 * @param string $output the ajax output string
	 */
	public static function editGroup( $sNewGroup, $sGroup ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		global $wgGroupPermissions, $wgAdditionalGroups, $wgNamespacePermissionLockdown;

		$output = FormatJson::encode( array(
				'success' => true,
				'message' => wfMessage( 'bs-groupmanager-grpedited' )->plain()
			)
		);

		if ( !empty( $sGroup ) && !empty( $sNewGroup ) ) {
			if ( !isset( $wgAdditionalGroups[$sGroup] ) ) {
				// If group is not in $wgAdditionalGroups, it's a system group and mustn't be renamed.
				return FormatJson::encode( array(
						'success' => true,
						'message' => wfMessage( 'bs-groupmanager-grpedited' )->plain()
					)
				);
			}
			// Copy the data of the old group to the group with the new name and then delete the old group
			$wgAdditionalGroups[$sGroup] = false;
			$wgAdditionalGroups[$sNewGroup] = true;

			$result = BsExtensionManager::getExtension( 'GroupManager' )->saveData();
			if ( $result['success'] === false ) {
				return FormatJson::encode( $result );
			}

			wfRunHooks( "BSGroupManagerGroupNameChanged", array( $sGroup, $sNewGroup, &$result ) );
			if ( $result['success'] === false ) {
				return FormatJson::encode( $result );
			}
		}

		return $output;
	}

	/**
	 * removes a usergroup
	 * @param string $output the ajax output string
	 */
	public static function removeGroup( $sGroup ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;
		global $wgGroupPermissions, $wgAdditionalGroups, $wgNamespacePermissionLockdown;

		$output = FormatJson::encode( array(
				'success' => true,
				'message' => wfMessage( 'bs-groupmanager-grpremoved' )->plain()
			)
		);

		if ($sGroup) {
			if (!isset($wgAdditionalGroups[$sGroup])) {
				return FormatJson::encode(array(
					'success' => false,
					'message' => wfMessage('bs-groupmanager-msgnotremovable')->plain()
						)
				);
			}

			$wgAdditionalGroups[$sGroup] = false;
			BsExtensionManager::getExtension( 'GroupManager' )->saveData();

			wfRunHooks( "BSGroupManagerGroupDeleted", array( $sGroup, &$result ) );
			if ( $result['success'] === false ) {
				return FormatJson::encode( $result );
			}

		}

		return $output;
	}

	public static function removeGroups( $aGroups ){
		$output = array();
		if (is_array($aGroups) && count($aGroups) > 0){
			foreach($aGroups as $sGroup){
				$output [$sGroup] = FormatJson::decode(self::removeGroup($sGroup));
			}
		}
		return FormatJson::encode($output);
	}

	/**
	 * saves all groupspecific data to bluespice-core/config/gm-settings.php
	 * @return array the json answer
	 */
	protected function saveData() {
		global $wgAdditionalGroups;

		$sSaveContent = "<?php\nglobal \$wgAdditionalGroups;\n\$wgAdditionalGroups = array();\n\n";
		foreach ( $wgAdditionalGroups as $sGroup => $mValue ) {
			$aInvalidChars = array();
			$sGroup = trim( $sGroup );
			if ( substr_count( $sGroup, '\'' ) > 0 ) $aInvalidChars[] = '\'';
			if ( substr_count( $sGroup, '"' ) > 0 ) $aInvalidChars[] = '"';
			if ( !empty( $aInvalidChars ) ) {
				return array(
					'success' => false,
					'message' => wfMessage( 'bs-groupmanager-invalid-name' )
						->numParams( count( $aInvalidChars ) )
						->params( implode( ',', $aInvalidChars ) )
						->text()
				);
			} elseif ( preg_match( "/^[0-9]+$/", $sGroup ) ) {
				return array(
					'success' => false,
					'message' => wfMessage( 'bs-groupmanager-invalid-name-numeric' )->plain()
				);
			} elseif ( strlen( $sGroup ) > 16 ) {
				return array(
					'success' => false,
					'message' => wfMessage( 'bs-groupmanager-invalid-name-length' )->plain()
				);
			} else {
				if ( $mValue !== false ) {
					$sSaveContent .= "\$wgAdditionalGroups['{$sGroup}'] = array();\n";
					$this->checkI18N( $sGroup );
				} else {
					$this->checkI18N( $sGroup, $mValue );
				}
			}
		}

		$sSaveContent .= "\n\$wgGroupPermissions = array_merge(\$wgGroupPermissions, \$wgAdditionalGroups);";

		$res = file_put_contents( BSROOTDIR.DS.'config'.DS.'gm-settings.php', $sSaveContent );
		if ( $res ) {
			return array(
				'success' => true,
				'message' => wfMessage( 'bs-groupmanager-grpadded' )->plain()
			);
		} else {
			return array(
				'success' => false,
				// TODO SU (04.07.11 11:44): i18n
				'message' => 'Not able to create or write file "'.BSROOTDIR.DS.'config'.DS.'gm-settings.php".'
			);
		}
	}

	public function checkI18N( $sGroup, $bValue = true ) {
		$oTitle   = Title::newFromText( 'group-' . $sGroup, NS_MEDIAWIKI );
		$oArticle = null;

		if ( $bValue === false ) {
			if ( $oTitle->exists() ) {
				$oArticle = new Article( $oTitle );
				$oArticle->doDeleteArticle( 'Group does no more exist' );
			}
		} else {
			if ( !$oTitle->exists() ) {
				$oArticle = new Article( $oTitle );
				$oArticle->doEdit( $sGroup, '', EDIT_NEW );
			}
		}
	}

}