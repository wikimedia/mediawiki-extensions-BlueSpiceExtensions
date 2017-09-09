<?php
/**
 * Provides the group manager tasks api for BlueSpice.
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
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * GroupManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksGroupManager extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'addGroup' => [
			'examples' => [
				[
					'group' => 'Some name'
				]
			],
			'params' => [
				'group' => [
					'desc' => 'Group name',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'editGroup' => [
			'examples' => [
				[
					'group' => 'Some name',
					'newGroup' => 'New name'
				]
			],
			'params' => [
				'group' => [
					'desc' => 'Old group name',
					'type' => 'string',
					'required' => true
				],
				'newGroup' => [
					'desc' => 'New group name',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'removeGroup' => [
			'examples' => [
				[
					'group' => 'Some name'
				]
			],
			'params' => [
				'group' => [
					'desc' => 'Group name',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'removeGroups' => [
			'examples' => [
				[
					'group' => [ 'Group 1', 'Group 2', 'Group 3' ]
				]
			],
			'params' => [
				'groups' => [
					'desc' => 'Array containing group names',
					'type' => 'array',
					'required' => true
				]
			]
		],
	);

	protected function task_addGroup( $oTaskData, $aParams ) {
		// TODO SU (04.07.11 11:40): global sind leider hier noch nötig, da werte in den globals geändert werden müssen.
		global $wgGroupPermissions, $wgAdditionalGroups;

		$oReturn = $this->makeStandardReturn();

		$sGroup = isset( $oTaskData->group )
			? (string) $oTaskData->group
			: ''
		;
		if( empty($sGroup) ) {
			$oReturn->message = wfMessage(
				'bs-groupmanager-grpempty'
			)->plain();
			return $oReturn;
		}
		if ( array_key_exists( $sGroup, $wgAdditionalGroups ) ) {
			$oReturn->message = wfMessage(
				'bs-groupmanager-grpexists'
			)->plain();
			return $oReturn;
		}

		if ( !isset( $wgGroupPermissions[ $sGroup ] ) ) {
			$wgAdditionalGroups[ $sGroup ] = true;
			$output = GroupManager::saveData();
		}
		if( $output[ 'success' ] === true ) {
			// Create a log entry for the creation of the group
			$oTitle = SpecialPage::getTitleFor( 'WikiAdmin' );
			$oUser = RequestContext::getMain()->getUser();
			$oLogger = new ManualLogEntry( 'bs-group-manager', 'create' );
			$oLogger->setPerformer( $oUser );
			$oLogger->setTarget( $oTitle );
			$oLogger->setParameters( array(
					'4::group' => $sGroup
			) );
			$oLogger->insert();
		}

		$oReturn->success = true;
		$oReturn->message = wfMessage( 'bs-groupmanager-grpadded' )->plain();
		return $oReturn;
	}

	protected function task_editGroup( $oTaskData, $aParams ) {
		global $wgAdditionalGroups;

		$oReturn = $this->makeStandardReturn();
		$sGroup = isset( $oTaskData->group )
			? (string) $oTaskData->group
			: ''
		;
		$sNewGroup = isset( $oTaskData->newGroup )
			? (string) $oTaskData->newGroup
			: ''
		;
		if( empty( $sGroup ) || empty( $sNewGroup ) || $sGroup == $sNewGroup ) {
			$oReturn->message = wfMessage(
				'bs-groupmanager-grpempty'
			)->plain();
			return $oReturn;
		}
		if( !isset( $wgAdditionalGroups[$sGroup] ) ) {
			// If group is not in $wgAdditionalGroups, it's a system group and mustn't be renamed.
			$oReturn->message = wfMessage(
				'bs-groupmanager-grpedited'
			)->plain();
			return $oReturn;
		}
		// Copy the data of the old group to the group with the new name and then delete the old group
		$wgAdditionalGroups[$sGroup] = false;
		$wgAdditionalGroups[$sNewGroup] = true;

		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->update(
			'user_groups',
			array(
				'ug_group' => $sNewGroup
			),
			array(
				'ug_group' => $sGroup
			)
		);

		if( $res === false ) {
			$oReturn->message = wfMessage(
				'bs-groupmanager-removegroup-message-unknown'
			)->plain();
			return $oReturn;
		}

		$oReturn->success = true;

		$result = GroupManager::saveData();
		//Backwards compatibility
		$result = array_merge(
			(array) $oReturn,
			$result
		);

		Hooks::run( "BSGroupManagerGroupNameChanged", array( $sGroup, $sNewGroup, &$result ) );

		if ( $result['success'] === false ) {
			return (object) $result;
		}
		$result['message'] = wfMessage( 'bs-groupmanager-grpedited' )->plain();

		// Create a log entry for the change of the group
		$oTitle = SpecialPage::getTitleFor( 'WikiAdmin' );
		$oUser = RequestContext::getMain()->getUser();
		$oLogger = new ManualLogEntry( 'bs-group-manager', 'modify' );
		$oLogger->setPerformer( $oUser );
		$oLogger->setTarget( $oTitle );
		$oLogger->setParameters( array(
				'4::group' => $sGroup,
				'5::newGroup' => $sNewGroup
		) );
		$oLogger->insert();

		return (object) $result;
	}

	protected function task_removeGroups( $oTaskData, $aParams ) {
		$oReturn = $this->makeStandardReturn();
		$aGroups = isset( $oTaskData->groups )
			? $oTaskData->groups
			: array()
		;
		if( !is_array($aGroups) || empty($aGroups) ){
			$oReturn->message = wfMessage(
				'bs-groupmanager-grpempty'
			)->plain();
			return $oReturn;
		}
		$aFails = array();
		foreach( $aGroups as $sGroup ){
			$oReturn->payload[$sGroup] = $this->task_removeGroup(
				(object) array( 'group' => $sGroup ),
				array()
			);
			$oReturn->payload_count++;
			if( isset($oReturn->payload[$sGroup]->success) ) {
				continue;
			}
			$aFails[] = $sGroup;
		}

		if( !empty($aFails) ) {
			$oReturn->success = false;
			$sErrorList = Xml::openElement( 'ul' );
			foreach( $aFails as $sGroup ) {
				$sErrorList .= Xml::element( 'li', array(), $sGroup );
			}
			$sErrorList .= Xml::closeElement( 'ul' );
			$oReturn->message = wfMessage(
				'bs-groupmanager-removegroup-message-failure',
				count( $aFails ),
				$sErrorList
			)->parse();
		} else {
			$oReturn->success = true;
			$oReturn->message = wfMessage(
				'bs-groupmanager-grpremoved'
			)->plain();
		}
		return $oReturn;
	}

	protected function task_removeGroup( $oTaskData, $aParams ) {
		global $wgAdditionalGroups;
		$oReturn = $this->makeStandardReturn();

		$sGroup = isset( $oTaskData->group )
			? (string) $oTaskData->group
			: ''
		;
		if( empty( $sGroup ) ) {
			$oReturn->message = wfMessage(
				'bs-groupmanager-grpempty'
			)->plain();
			return $oReturn;
		}
		if( !isset($wgAdditionalGroups[$sGroup]) ) {
			$oReturn->message = wfMessage(
				'bs-groupmanager-msgnotremovable'
			)->plain();
			return $oReturn;
		}

		$wgAdditionalGroups[$sGroup] = false;
		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->delete(
			'user_groups',
			array(
				'ug_group' => $sGroup
			)
		);
		if( $res === false ) {
			$oReturn->message = wfMessage(
				'bs-groupmanager-removegroup-message-unknown'
			)->plain();
			return $oReturn;
		}

		$result = GroupManager::saveData();
		//Backwards compatibility
		$result = array_merge(
			(array) $oReturn,
			$result
		);

		Hooks::run( "BSGroupManagerGroupDeleted", array( $sGroup, &$result ) );
		if( $result['success'] === false ) {
			return (object) $result;
		}
		$result['message'] = wfMessage( 'bs-groupmanager-grpremoved' )->plain();

		// Create a log entry for the removal of the group
		$oTitle = SpecialPage::getTitleFor( 'WikiAdmin' );
		$oUser = RequestContext::getMain()->getUser();
		$oLogger = new ManualLogEntry( 'bs-group-manager', 'remove' );
		$oLogger->setPerformer( $oUser );
		$oLogger->setTarget( $oTitle );
		$oLogger->setParameters( array(
				'4::group' => $sGroup
		));
		$oLogger->insert();

		return (object) $result;
	}

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'addGroup' => array( 'wikiadmin' ),
			'editGroup' => array( 'wikiadmin' ),
			'removeGroup' => array( 'wikiadmin' ),
			'removeGroups' => array( 'wikiadmin' ),
		);
	}

	public function needsToken() {
		return parent::needsToken();
	}
}