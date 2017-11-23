<?php

/**
 * Provides the flexiskin api for BlueSpice.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
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
 * @author     Daniel Vogel
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 */


class BSApiFlexiskinUpload extends ApiBase {

	/**
	 * Triggers a file upload to the Flexiskin data directory defined by id via request parameter
	 * @return BSApiTasks response object
	 */
	public function execute() {
		$aRes = array(
			'success' => true,
			'message' => '',
			'name' => ''
		);

		$sId = $this->getParameter( 'skinId' );
		if ( $sId == "" ) {

			$aRes['message'] = wfMessage( 'bs-flexiskin-api-error-missing-param', 'id' )->plain();
			$aRes['success'] = false;
		}

		if ( $aRes['success'] == true ) {
			$sName = $this->getParameter( 'name' );
			if ( $sName == "" ) {
				$aRes['message'] = wfMessage( 'bs-flexiskin-api-error-missing-param', 'name' )->plain();
				$aRes['success'] = false;
			}
		}

		if ( $aRes['success'] == true ) {
			$oStatus = BsFileSystemHelper::uploadFile( $sName, "flexiskin" . DS . $sId . DS . "images" );
			if ( !$oStatus->isGood() ) {
				$aRes['message'] = "err_cd:" . $oStatus->getMessage();
				$aRes['success'] = false;
			} else {
				$aRes['name'] = $oStatus->getValue();
			}
		}

		$oResult = $this->getResult();
		$oResult->addValue( 'result', 'success', $aRes['success'] );
		$oResult->addValue( 'result', 'message', $aRes['message'] );
		$oResult->addValue( 'result', 'name', $aRes['name'] );
	}

	protected function getAllowedParams() {
		return array(
			'skinId' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => '',
				10 /*ApiBase::PARAM_HELP_MSG*/ => 'bs-flexiskin-apihelp-task-param-skinId',
			),
			'name' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => '',
				10 /*ApiBase::PARAM_HELP_MSG*/ => 'bs-flexiskin-apihelp-task-param-name',
			),
		);
	}
}
