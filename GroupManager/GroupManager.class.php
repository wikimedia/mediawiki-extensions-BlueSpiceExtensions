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
 * @version    1.22.0
 * @version    $Id: GroupManager.class.php 9932 2013-06-25 15:46:48Z mreymann $
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
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['GroupManager'] = dirname( __FILE__ ) . '/GroupManager.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'GroupManager',
			EXTINFO::DESCRIPTION => 'Administration interface for adding, editing and deletig user groups and their rights',
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9932 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '1.22.0' )
		);
		$this->mExtensionKey = 'MW::GroupManager';

		WikiAdmin::registerModule('GroupManager', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/images/bs-btn_gruppe_v1.png',
			'level' => 'useradmin'
			)
		);

		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ).'/extensions/BlueSpiceExtensions/GroupManager/js', 'GroupManager', false, true, false, 'MW::GroupManagerShow' );

		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'GroupManager', $this, 'getData', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'GroupManager', $this, 'addGroup', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'GroupManager', $this, 'editGroup', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'GroupManager', $this, 'removeGroup', 'wikiadmin' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * offers the formular for the group manager
	 * @return string the formular code
	 */
	public function getForm() {
		BsExtensionManager::setContext( 'MW::GroupManagerShow' );
		$sForm = '<div id="bs-groupmanager-grid"></div>';
		return $sForm;
	}

	/**
	 * returns a json object which hold the data of all existing usergroups
	 * @param string $output the ajax output string 
	 */
	public function getData( &$output ) {
		$wgGroupPermissions = $this->mAdapter->get( 'GroupPermissions' );
		$wgAdditionalGroups = $this->mAdapter->get( 'AdditionalGroups' );

		$aGroups = array();
		foreach ( $wgGroupPermissions as $sGroup => $aPermissions ) {
			$aGroups[] = array( $sGroup, ( isset( $wgAdditionalGroups[$sGroup] ) ) );
		}

		$output = json_encode($aGroups);
	}

	/**
	 * adds an usergroup to the wiki
	 * @param string $output the ajax output string 
	 */
	public function addGroup( &$output ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
			return;
		}

		// TODO SU (04.07.11 11:40): global sind leider hier noch nötig, da werte in den globals geändert werden müssen.
		global $wgGroupPermissions, $wgAdditionalGroups;

		$output = json_encode( array( 'success' => true ) );
		$sGroup = BsCore::getParam( 'group', false );

		if( array_key_exists( $sGroup, $wgAdditionalGroups ) ) {
			$output = json_encode( array( 
										'success' => false, 
										'msg' => wfMsg( 'bs-groupmanager-grp_exists' ) 
									) 
			);
			return;
		}
		if ( $sGroup ) {
			if( isset( $wgGroupPermissions[$sGroup] ) ) {
				return;
			}
			$wgAdditionalGroups[$sGroup] = true;
			$output = json_encode( $this->saveData() );
		}
	}

	/**
	 * changes the name of a given usergroup.
	 * @param string $output the ajax output string 
	 */
	public function editGroup( &$output ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
			return;
		}

		// TODO SU (03.07.11 15:08): global sind leider hier noch nötig, da werte in den globals geändert werden müssen.
		global $wgGroupPermissions, $wgAdditionalGroups, $wgNamespacePermissionLockdown;
		// TODO SU (04.07.11 12:11): Bitte checken, ob es die Gruppe schon gibt und ggf. Fehlermeldung schicken
		$output = json_encode( array( 'success' => true ) );
		$sGroup = BsCore::getParam( 'group', false );
		$sNewGroup = BsCore::getParam( 'newgroup', false );

		if ( $sGroup && $sNewGroup ) {
			if ( !isset( $wgAdditionalGroups[$sGroup] ) ) {
				// If group is not in $wgAdditionalGroups, it's a system group and mustn't be renamed.
				return;
			}
			// Copy the data of the old group to the group with the new name and then delete the old group
			$wgAdditionalGroups[$sGroup] = false;
			$wgAdditionalGroups[$sNewGroup] = true;
			$result = $this->saveData();
			if($result['success'] == false) {
				$output = json_encode( $result );
				return;
			}
			$wgGroupPermissions[$sNewGroup] = $wgGroupPermissions[$sGroup];
			unset( $wgGroupPermissions[$sGroup] );
			foreach ( $wgNamespacePermissionLockdown as $iNs => $aPermissions ) {
				foreach ( $aPermissions as $sPermission => $aGroups ) {
					$iIndex = array_search( $sGroup, $aGroups );
					if ( $iIndex !== false ) {
						array_splice( $wgNamespacePermissionLockdown[$iNs][$sPermission], $iIndex, 1, array( $sNewGroup ) );
					}
				}
			}
			$_SESSION['pmTemp'] = array(
				'aGroupPermissions' => $wgGroupPermissions,
				'aLockdown' => $wgNamespacePermissionLockdown
			);
			BsExtensionManager::getExtension( 'PermissionManager' )->setData( $output );
		}
	}

	/**
	 * removes a usergroup
	 * @param string $output the ajax output string  
	 */
	public function removeGroup( &$output ) {
		// TODO SU (04.07.11 11:43): global sind leider hier noch nötig, da werte in den globals geändert werden müssen.
		global $wgGroupPermissions, $wgAdditionalGroups, $wgNamespacePermissionLockdown;

		$output = json_encode( array( 'success' => true ) );
		$sGroup = BsCore::getParam( 'group', false );
		if ( $sGroup ) {
			if( !isset( $wgAdditionalGroups[$sGroup] ) ) {
				return;
			}
			$wgAdditionalGroups[$sGroup] = false;
			$this->saveData();
			unset( $wgGroupPermissions[$sGroup] );
			foreach ( $wgNamespacePermissionLockdown as $iNS => $aPermissions ) {
				foreach ( $aPermissions as $sPermission => $aGroups ) {
					$iIndex = array_search( $sGroup, $aGroups );
					if ( $iIndex !== false ) {
						if ( count( $aGroups ) == 1 ) {
							unset( $wgNamespacePermissionLockdown[$iNS][$sPermission] );
						}
						else {
							array_splice( $wgNamespacePermissionLockdown[$iNS][$sPermission], $iIndex, 1 );
						}
					}
				}
			}
			$_SESSION['pmTemp'] = array(
				'aGroupPermissions' => $wgGroupPermissions,
				'aLockdown' => $wgNamespacePermissionLockdown
			);
			BsExtensionManager::getExtension( 'PermissionManager' )->setData( $output );
		}
	}

	/**
	 * saves all groupspecific data to bluespice-core/config/gm-settings.php
	 * @return array the json answer 
	 */
	protected function saveData() {
		$wgAdditionalGroups = $this->mAdapter->get( 'AdditionalGroups' );

		$sSaveContent = "<?php\nglobal \$wgAdditionalGroups;\n\$wgAdditionalGroups = array();\n\n";
		foreach ( $wgAdditionalGroups as $sGroup => $mValue ) {
			$aInvalidChars = array();
			$sGroup = trim( $sGroup );
			if( substr_count( $sGroup, '\'' ) > 0 ) $aInvalidChars[] = '\'';
			if( substr_count( $sGroup, '"' ) > 0 ) $aInvalidChars[] = '"';
			if( !empty( $aInvalidChars ) ) {
				return array(
					'success' => false,
					'msg' => wfMsg( 'bs-groupmanager-invalid_name', implode( ',', $aInvalidChars ) )
				);
			}
			else if( preg_match( "/^[0-9]+$/", $sGroup ) ) {
				return array(
					'success' => false,
					'msg' => wfMsg( 'bs-groupmanager-invalid_name_numeric' )
				);
			}
			else if( strlen( $sGroup ) > 16 ) {
				return array(
					'success' => false,
					'msg' => wfMsg( 'bs-groupmanager-invalid_name_length' )
				);
			}
			else {
				if ( $mValue !== false ) {
					$sSaveContent .= "\$wgAdditionalGroups['{$sGroup}'] = array();\n";
					$this->checkI18N( $sGroup );
				}
				else {
					$this->checkI18N( $sGroup, $mValue );
				}
			}
		}

		$sSaveContent .= "\n\$wgGroupPermissions = array_merge(\$wgGroupPermissions, \$wgAdditionalGroups);";

		$res = file_put_contents( BSROOTDIR.DS.'config'.DS.'gm-settings.php', $sSaveContent );
		if ( $res ) {
			return array(
				'success' => true
			);
		}
		else {
			return array(
				'success' => false,
				// TODO SU (04.07.11 11:44): i18n
				'msg' => 'Not able to create or write file "'.BSROOTDIR.DS.'config'.DS.'gm-settings.php".'
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
		}
		else {
			if ( !$oTitle->exists() ) {
				$oArticle = new Article( $oTitle );
				$oArticle->doEdit( $sGroup, '', EDIT_NEW );
			}
		}
	}
}
