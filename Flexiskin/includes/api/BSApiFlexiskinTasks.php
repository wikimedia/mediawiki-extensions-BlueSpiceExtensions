<?php
/**
 * Provides the flexiskin api for BlueSpice.
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
 * @author     Daniel Vogel
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

class BSApiFlexiskinTasks extends BSApiTasksBase {

	protected $aTasks = array(
		'activate' => [
			'examples' => [
				[
					'id' => 'SomeSkinName'
				]
			],
			'params' => [
				'id' => [
					'desc' => 'Valid skin ID',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'add' => [
			'examples' => [
				[
					'data' => [
						[
							'name' => "SkinName",
							'desc' => "SkinDesc",
							'template' => "NameOfTemplate"
						]
					]
				]
			],
			'params' => [
				'data' => [
					'desc' => 'Array of JSON objects in form of { name: "SkinName", desc: "SkinDesc", template: "NameOfTemplate" }',
					'type' => 'array',
					'required' => true
				]
			]
		],
		'delete' => [
			'examples' => [
				[
					'id' => 'SomeSkinName'
				]
			],
			'params' => [
				'id' => [
					'desc' => 'Valid skin ID',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'save' => [
			'examples' => [
				[
					'id' => 'SomeSkinName',
					'data' => [
						[
							'name' => "SkinName",
							'desc' => "SkinDesc",
							'template' => "NameOfTemplate"
						]
					]
				]
			],
			'params' => [
				'id' => [
					'desc' => 'Valid skin ID',
					'type' => 'string',
					'required' => true
				],
				'data' => [
					'desc' => 'Array of JSON objects in form of { name: "SkinName", desc: "SkinDesc", template: "NameOfTemplate" }',
					'type' => 'array',
					'required' => false
				]
			]
		],
		'reset' => [
			'examples' => [
				[
					'id' => 'SomeSkinName'
				]
			],
			'params' => [
				'id' => [
					'desc' => 'Valid skin ID',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'preview' => [
			'examples' => [
				[
					'id' => 'SomeSkinName',
					'data' => [
						[
							'name' => "SkinName",
							'desc' => "SkinDesc",
							'template' => "NameOfTemplate"
						]
					]
				]
			],
			'params' => [
				'id' => [
					'desc' => 'Valid skin ID',
					'type' => 'string',
					'required' => true
				],
				'data' => [
					'desc' => 'Array of JSON objects in form of { name: "SkinName", desc: "SkinDesc", template: "NameOfTemplate" }',
					'type' => 'array',
					'required' => false
				]
			]
		],
	);

	protected $sTaskLogType = 'bs-flexiskin';

	protected function getRequiredTaskPermissions() {
		return array(
			'activate' => array( 'flexiskinedit' ),
			'add' => array( 'flexiskinedit' ),
			'delete' => array( 'flexiskinedit' ),
			'save' => array( 'flexiskinedit' ),
			'reset' => array( 'flexiskinedit' ),
			'preview' => array( 'flexiskinedit' ),
		);
	}

	/**
	 * Activates the Flexiskin defined by id via request parameter
	 * @return String encoded result JSON string
	 */
	public function task_activate( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		BsConfig::set( "MW::Flexiskin::Active", $oTaskData->id );
		BsConfig::saveSettings();

		$oResponse->success = true;
		return $oResponse;
	}

	/**
	 * Adds a Flexiskin defined by data via request parameter
	 * @return String encoded result JSON string
	 */
	public function task_add( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		$oData = $oTaskData->data[0];

		if ( is_null( $oData->template ) ) {
			$oResponse->message =  wfMessage( 'bs-flexiskin-error-templatenotexists' )->plain();
			return $oResponse;
		}
		if ( empty( $oData->template ) ) {
			$oData->template = 'default';
		}

		$sId = str_replace( " ", "_", strtolower( $oData->name ) );
		$sFlexiskinPath = BsFileSystemHelper::getDataDirectory( 'flexiskin' . DS . md5( $sId ) );
		if ( is_dir( $sFlexiskinPath ) ) {
			$oResponse->message = wfMessage( 'bs-flexiskin-error-skinexists' )->plain();
			return $oResponse;
		}
		if ( empty( $oData->name ) ) {
			$oResponse->message = wfMessage( 'bs-flexiskin-error-nameempty' )->plain();
			return $oResponse;
		}
		if ( $oData->template != 'default' ) {
			$oConf = Flexiskin::getFlexiskinConfig( $oData->template );
			$oConf[0]->name = $oData->name;
			$oConf[0]->desc = $oData->desc;
			$sConfigFile = FormatJson::encode( $oConf, true );
		}
		else {
			$sConfigFile = Flexiskin::generateConfigFile( $oData );
		}

		$oStatus = BsFileSystemHelper::saveToDataDirectory( 'conf.json', $sConfigFile, "flexiskin" . DS . md5( $sId ) );

		if ( !$oStatus->isGood() ) {
			$oResponse->message = wfMessage( 'bs-flexiskin-error-fail-add-skin', $this->getErrorMessage( $oStatus ) )->plain();
			return $oResponse;
		}
		if ( $oData->template != 'default' ) {
			$oStatus = BsFileSystemHelper::copyFolder( "images", "flexiskin" . DS . $oData->template, "flexiskin" . DS . md5( $sId ) );
		}

		BsFileSystemHelper::ensureDataDirectory( "flexiskin" . DS . md5( $sId ) . DS . "images" );

		$oResponse->success = true;
		return $oResponse;
	}

	/**
	 * Deletes a flexiskin defined by id via request parameter
	 * @return String encoded result JSON string
	 */
	public function task_delete( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		$sId = $oTaskData->id;
		if ( $sId == "" ) {
			$oResponse->message = wfMessage( 'bs-flexiskin-api-error-missing-param', 'id' )->plain();
			return $oResponse;
		}

		$oStatus = BsFileSystemHelper::deleteFolder( "flexiskin" . DS . $sId );
		if ( BsConfig::get( "MW::Flexiskin::Active" ) == $sId ) {
			BsConfig::set( "MW::Flexiskin::Active", "" );
			BsConfig::saveSettings();
		}
		if ( !$oStatus->isGood() ) {
			$oResponse->message = wfMessage( 'bs-flexiskin-error-delete-skin', $this->getErrorMessage( $oStatus ) )->plain();
			return $oResponse;
		}

		$oResponse->success = true;
		return $oResponse;
	}

	/**
	 * Method to save a Flexiskin defined by id and data via request parameter
	 * @global String $wgScriptPath
	 * @return String encoded result JSON string
	 */
	public function task_save( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		global $wgScriptPath;

		$aConfigs = ( $oTaskData->data )? $oTaskData->data : array();
		$sOldId = $oTaskData->id;
		$sNewId = Flexiskin::getFlexiskinIdFromConfig( $aConfigs );

		$sFlexiskinPath = BsFileSystemHelper::getDataDirectory( 'flexiskin' ) . DS;

		// skin already exists ?
		if ( $sOldId != $sNewId && is_dir( $sFlexiskinPath . DS . $sNewId ) && file_exists( $sFlexiskinPath . DS . $sNewId . DS . "conf.json" ) ) {
			$oResponse->message =  wfMessage( 'bs-flexiskin-error-skinexists' )->plain();
			return $oResponse;
		}
		if ( $sOldId != $sNewId && is_dir( $sFlexiskinPath . DS . $sOldId ) ) {

			$oStatus = BsFileSystemHelper::renameFolder( "flexiskin" . DS . $sOldId, "flexiskin" . DS . $sNewId );

			if ( !$oStatus->isGood() ) {
				$oResponse->message = wfMessage( "bs-flexiskin-api-error-save", $this->getErrorMessage( $oStatus ) )->plain();
				return $oResponse;
			}
		}

		BsFileSystemHelper::ensureDataDirectory( $sFlexiskinPath . DS . $sNewId );

		$oStatus = BsFileSystemHelper::saveToDataDirectory( "conf.json", FormatJson::encode( $aConfigs, true ), "flexiskin" . DS . $sNewId );
		if ( !$oStatus->isGood() ) {
			$oResponse->message = wfMessage( "bs-flexiskin-api-error-save", $this->getErrorMessage( $oStatus ) )->plain();
			return $oResponse;
		}

		$oStatus = BsFileSystemHelper::saveToDataDirectory( "conf.tmp.json", FormatJson::encode( $aConfigs, true ), "flexiskin" . DS . $sNewId );
		if ( !$oStatus->isGood() ) {
			$oResponse->message = wfMessage( "bs-flexiskin-api-error-save", $this->getErrorMessage( $oStatus ) )->plain();
			return $oResponse;
		}

		$oResponse->id = $sNewId;
		$oResponse->src = $wgScriptPath . "/index.php?flexiskin=" . $sNewId . "&preview=true";
		$oResponse->success = true;

		return $oResponse;
	}

	/**
	 * Resets the Flexiskin preview to the last saved one defined by id via request parameter
	 * @return String encoded result JSON string
	 */
	public function task_reset( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		$sId = $oTaskData->id;
		if ( $sId == "" ) {
			$oResponse->message =  wfMessage( 'bs-flexiskin-api-error-missing-param', 'id' )->plain();
			return $oResponse;
		}

		$oConfig = Flexiskin::getFlexiskinConfig( $sId );
		$oStatus = BsFileSystemHelper::saveToDataDirectory( "conf.tmp.json", FormatJson::encode( $oConfig, true ), "flexiskin" . DS . $sId );
		if ( !$oStatus->isGood() ) {
			$oResponse->message = wfMessage( "bs-flexiskin-api-error-save-preview", $this->getErrorMessage( $oStatus ) )->plain();
			return $oResponse;
		}

		$oResponse->data = array( 'skinId' => $sId, 'config' => $oConfig );
		$oResponse->src = wfAssembleUrl( array(
			'path' => wfScript(),
			'query' => wfArrayToCgi( array(
				'flexiskin' => $sId,
				'preview' => 'true'
			) )
		) );

		$oResponse->success = true;
		return $oResponse;
	}

	/**
	 * Saves the preview of a flexiskin defined by id and data via request parameter
	 * @return String encoded result JSON string
	 */
	public function task_preview( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		$sId = $oTaskData->id;
		if ( $sId == "" ) {
			$oResponse->message = wfMessage( 'bs-flexiskin-api-error-missing-param', 'id' )->plain();
			return $oResponse;
		}

		$aConfigs = ( $oTaskData->data )? $oTaskData->data : array();

		$oStatus = BsFileSystemHelper::saveToDataDirectory( "conf.tmp.json", FormatJson::encode( $aConfigs, true ), "flexiskin" . DS . $sId );
		if ( !$oStatus->isGood() ) {
			$oResponse->message = wfMessage( "bs-flexiskin-api-error-save-preview", $this->getErrorMessage( $oStatus ) )->plain();
			return $oResponse;
		}

		RequestContext::getMain()->getTitle()->invalidateCache();

		$oResponse->success = true;
		$oResponse->src = wfAssembleUrl( array(
			'path' => wfScript(),
			'query' => wfArrayToCgi( array(
				'flexiskin' => $sId,
				'preview' => 'true'
			) )
		) );

		return $oResponse;
	}
}
