<?php
/**
 * Provides the feedback helper api for BlueSpice.
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
 * BlueSpiceProjectFeedbackHelper Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksBlueSpiceProjectFeedbackHelper extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array( 'disableFeedback' );

	protected function task_disableFeedback() {
		$oReturn = $this->makeStandardReturn();

		BsConfig::set( 'MW::BlueSpiceProjectFeedbackHelper::Active', false );
		BsConfig::saveSettings();

		$oReturn->success = true;
		return $oReturn;
	}

	public function needsToken() {
		return parent::needsToken();
	}

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'disableFeedback' => array( 'wikiadmin' )
		);
	}
}