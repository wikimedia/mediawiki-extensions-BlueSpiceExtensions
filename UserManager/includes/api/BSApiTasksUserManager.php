<?php
/**
 * Provides the user manager tasks api for BlueSpice.
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
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * UserManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksUserManager extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'addUser',
		'editUser',
		'deleteUser',
		'setUserGroups',
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'addUser' => array( 'wikiadmin' ),
			'editUser' => array( 'wikiadmin' ),
			'deleteUser' => array( 'wikiadmin' ),
			'setUserGroups' => array( 'wikiadmin' ),
		);
	}

	public function needsToken() {
		parent::needsToken();
	}

	public function getTaskDataDefinitions() {
		//TODO
		return false;
	}

	/**
	 * Creates an user.
	 * @return stdClass Standard tasks API return
	 */
	protected function task_addUser( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();
		$aGroups = false;
		if( isset($oTaskData->groups) ) {
			$aGroups = $oTaskData->groups;
		}

		if( empty($oTaskData->userName) ) {
			$oReturn->message = wfMessage(
				'bs-usermanager-invalid-uname'
			)->plain();
		}

		$aMetaData = array();
		if( isset($oTaskData->password) ) {
			$aMetaData['password'] = $oTaskData->password;
		}
		if( isset($oTaskData->rePassword) ) {
			$aMetaData['repassword'] = $oTaskData->rePassword;
		}
		if( isset($oTaskData->email) ) {
			$aMetaData['email'] = $oTaskData->email;
		}
		if( isset($oTaskData->realname) ) {
			$aMetaData['realname'] = $oTaskData->realname;
		}

		$oStatus = UserManager::addUser( $oTaskData->userName, $aMetaData );
		if( !$oStatus->isOK() ) {
			$oReturn->message = $oStatus->getMessage()->parse();
			return $oReturn;
		}

		if( is_array($aGroups) ) {
			$oStatus = UserManager::setGroups(
				$oStatus->getValue(),
				$aGroups
			);
			if( !$oStatus->isOK() ) {
				$oReturn->message = $oStatus->getMessage()->parse();
				return $oReturn;
			}
		}

		$oReturn->success = true;
		$oReturn->message = wfMessage( 'bs-usermanager-user-added' )->plain();

		return $oReturn;
	}

	/**
	 * Edits an user.
	 * @return stdClass Standard tasks API return
	 */
	protected function task_editUser( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();
		$aGroups = false;
		if( isset($oTaskData->groups) ) {
			$aGroups = $oTaskData->groups;
		}

		if( empty($oTaskData->userName) ) {
			$oReturn->message = wfMessage(
				'bs-usermanager-invalid-uname'
			)->plain();
		}
		$oUser = User::newFromName( $oTaskData->userName );

		$aMetaData = array();
		if( isset($oTaskData->password) ) {
			$aMetaData['password'] = $oTaskData->password;
		}
		if( isset($oTaskData->rePassword) ) {
			$aMetaData['repassword'] = $oTaskData->rePassword;
		}
		if( isset($oTaskData->email) ) {
			$aMetaData['email'] = $oTaskData->email;
		}
		if( isset($oTaskData->realname) ) {
			$aMetaData['realname'] = $oTaskData->realname;
		}

		$oStatus = UserManager::editUser( $oUser, $aMetaData, true );
		if( !$oStatus->isOK() ) {
			$oReturn->message = $oStatus->getMessage()->parse();
			return $oReturn;
		}

		if( is_array($aGroups) ) {
			$oStatus = UserManager::setGroups(
				$oStatus->getValue(),
				$aGroups
			);
			if( !$oStatus->isOK() ) {
				$oReturn->message = $oStatus->getMessage()->parse();
				return $oReturn;
			}
		}

		$oReturn->success = true;
		$oReturn->message = wfMessage(
			'bs-usermanager-save-successful'
		)->plain();

		return $oReturn;
	}

	/**
	 * Deletes an User.
	 * @return stdClass Standard tasks API return
	 */
	protected function task_deleteUser( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		if( empty($oTaskData->userName) ) {
			$oReturn->message = wfMessage(
				'bs-usermanager-invalid-uname'
			)->plain();
		}

		$oUser = User::newFromName( $oTaskData->userName );

		$oStatus = UserManager::deleteUser( $oUser );
		if( !$oStatus->isOK() ) {
			$oReturn->message = $oStatus->getMessage()->parse();
			return $oReturn;
		}

		$oReturn->success = true;
		$oReturn->message = wfMessage( 'bs-usermanager-user-deleted' )->plain();

		return $oReturn;
	}

	/**
	 * Deletes an User.
	 * @return stdClass Standard tasks API return
	 */
	protected function task_setUserGroups( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		if( empty($oTaskData->userNames) || !is_array($oTaskData->userNames) ) {
			$oReturn->message = wfMessage(
				'bs-usermanager-invalid-uname'
			)->plain();
		}

		if( !isset($oTaskData->groups) || !is_array($oTaskData->groups) ) {
			$oReturn->message = wfMessage(
				'bs-usermanager-invalid-uname'//TODO
			)->plain();
		}
		$oStatus = Status::newGood();
		foreach( $oTaskData->userNames as $sUserName ) {
			$oUser = User::newFromName( $sUserName );
			if( !$oUser ) {
				$oStatus->merge(
					Status::newFatal('bs-usermanager-invalid-uname')
				);
				continue;
			}
			$oStatus->merge( UserManager::setGroups(
				$oUser,
				$oTaskData->groups
			));
		}

		if( !$oStatus->isOK() ) {
			$oReturn->message = $oStatus->getMessage()->parse();
			return $oReturn;
		}

		$oReturn->success = true;
		$oReturn->message = wfMessage(
			'bs-usermanager-save-successful'
		)->plain();

		return $oReturn;
	}
}