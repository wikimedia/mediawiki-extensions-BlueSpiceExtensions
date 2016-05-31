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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * GroupManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksResponsibleEditors extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'setResponsibleEditors',
	);

	protected function task_setResponsibleEditors( $oTaskData, $aParams ) {
		$oReturn = $this->makeStandardReturn();

		$iArticleId = isset( $oTaskData->articleId )
			? $oTaskData->articleId
			: 0
		;

		if( empty($iArticleId) || !$oTitle = Title::newFromId($iArticleId) ) {
			//PW(24.03.2016) TODO: Tell the user the parameter, that is invalid
			$oReturn->message = wfMessage(
				'bs-responsibleeditors-error-ajax-invalid-parameter'
			)->plain();
			return $oReturn;
		}

		if( !$oTitle->userCan('responsibleeditors-changeresponsibility') ) {
			$oReturn->message = wfMessage(
				'bs-responsibleeditors-error-ajax-not-allowed'
			)->plain();
			return $oReturn;
		}

		$aEditors = isset( $oTaskData->editorIds )
			? $oTaskData->editorIds
			: array()
		;
		if( !is_array($aEditors) ) {
			$aEditors = array();
		}
		//Make sure this is not something weired like array( 0 => NULL ) and
		//every id belongs to a valid user
		if( !empty($aEditors) ) {
			$aEditors = array_filter( $aEditors, function($e) use($oReturn) {
				if( !is_numeric($e) || User::newFromId((int) $e)->isAnon() ) {
					//PW(24.03.2016) TODO: Tell the user the parameter, that is
					//not a valid user id
					//$oReturn->errors[] = wfMessage('',$e)->plain();
					return false;
				}
				return true;
			});
		}

		$oReturn->success = ResponsibleEditors::setResponsibleEditors(
			$oTitle,
			$aEditors
		);

		return $oReturn;
	}

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'setResponsibleEditors' => array(
				'responsibleeditors-changeresponsibility'
			),
		);
	}

	public function needsToken() {
		return parent::needsToken();
	}
}