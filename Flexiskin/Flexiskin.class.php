<?php

/**
 * Flexiskin extension for BlueSpice
 *
 * Provides a page to manage flexiskins with customizing options.
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
 * @author     Tobias Weichart <weichart@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage Flexiskin
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for Flexiskin extension
 * @package BlueSpice_Extensions
 * @subpackage Flexiskin
 */
class Flexiskin extends BsExtensionMW {
	/**
	 * Initialization of Flexiskin extension
	 *
	 */
	public function __construct() {
		wfProfileIn( 'BS::' . __METHOD__ );
		WikiAdmin::registerModule( 'Flexiskin', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_flexiskin_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-flexiskin-label',
			'iconCls' => 'bs-icon-painting-roll'
		) );
		wfProfileOut( 'BS::' . __METHOD__ );
	}

	public function initExt() {
		wfProfileIn( 'BS::' . __METHOD__ );

		BsConfig::registerVar( 'MW::Flexiskin::Active', "default", BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-flexiskin-pref-active', 'select' );
		BsConfig::registerVar( 'MW::Flexiskin::Logo', "", BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_STRING );
		$this->mCore->registerPermission( 'flexiskinedit', array(), array( 'type' => 'global' ) );
		$this->mCore->registerPermission( 'flexiskin-viewspecialpage', array( 'sysop' ), array( 'type' => 'global' ) );

		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * extension.json callback
	 * @global array $wgForeignFileRepos
	 */
	public static function onRegistration() {
		global $wgForeignFileRepos;
		if ( version_compare( $GLOBALS['wgVersion'], '1.28c', '>' ) ) {
			$wgForeignFileRepos[] = array(
				'class' => 'FileRepo',
				'name' => 'Flexiskin',
				'directory' => BS_DATA_DIR . '/Flexiskin/',
				'hashLevels' => 0,
				'url' => BS_DATA_PATH . '/Flexiskin',
			);
		} else {
			$wgForeignFileRepos[] = array(
				'class' => 'FSRepo',
				'name' => 'Flexiskin',
				'directory' => BS_DATA_DIR . '/Flexiskin/',
				'hashLevels' => 0,
				'url' => BS_DATA_PATH . '/Flexiskin',
			);
		}
	}

	public function runPreferencePlugin( $sAdapterName, BsConfig $oVariable ) {
		if ( substr( $oVariable->getKey(), 0, 13 ) != "MW::Flexiskin" ){
			return array();
		}

		$api = new ApiMain(
			new DerivativeRequest(
				$this->getRequest(),
				array(
					'action' => 'bs-flexiskin-store'
				),
				false
			),
			true
		);

		$oResult = $api->execute();

		if ( defined( 'ApiResult::META_CONTENT' ) ) {
			$oData = $api->getResult()->getResultData( null, array(
				'BC' => array(),
				'Types' => array(),
				'Strip' => 'all',
			) );
		} else {
			$oData = $api->getResultData();
		}
		if ( $oData ) {
			$aData = array();
				foreach ( $oData['results'] as $aConf ) {
				$aData[]=(object) $aConf;
			}
		}

		$aResult = array( 'options' => array(
				wfMessage( 'bs-flexiskin-defaultname' )->plain() => 'default',
			) );

		if ( isset( $aData ) && count( $aData ) > 0 ) {
			foreach ( $aData as $aConf ) {
				$aResult['options'][$aConf->flexiskin_name] = $aConf->flexiskin_id;
			}
		}
		return $aResult;
	}

	public static function generateConfigFile( $oData ) {
		$aConfig = array();
		$aConfig [] = array(
			"id" => "general",
			"name" => $oData->name,
			"desc" => $oData->desc,
			"backgroundColor" => "F4F4F4",
			"customBackgroundColor" => "F4F4F4",
			"repeatBackground" => "no-repeat"
		);
		$aConfig [] = array(
			"id" => "header",
			"logo" => ""
		);
		$aConfig [] = array(
			"id" => "position",
			"navigation" => "left",
			"content" => "center",
			"width" => "1222",
			"fullWidth" => "0"
		);
		$bReturn = wfRunHooks( "BSFlexiskinGenerateConfigFile", array( $oData, &$aConfig ) );
		if ( !$bReturn ) {
			return array();
		}
		return FormatJson::encode( $aConfig, true );
	}

	public static function getFlexiskinIdFromConfig($aConfig){
		if (is_array($aConfig) && isset($aConfig[0]) && isset($aConfig[0]->name)){
			$sName = str_replace(" ", "_", strtolower($aConfig[0]->name));
			$sNewId = md5($sName);
			return $sNewId;
		}
		else{
			return null;
		}
	}

	/**
	 *
	 * @param OutputPage $out
	 * @return boolean
	 */
	public static function onBeforePageDisplay( &$out ) {
		$inPreviewMode = $out->getRequest()->getBool( 'preview' );

		if( $inPreviewMode && $out->getRequest()->getVal( 'flexiskin' ) !== null ) {
			$out->getRequest()->setSessionData( 'flexiskin', $out->getRequest()->getVal( 'flexiskin' ) );
			$out->addModuleStyles( 'ext.bluespice.flexiskin.skin.preview' );
		}
		else {
			$out->addModuleStyles( Flexiskin::generateDynamicModuleStyleName() );
		}

		return true;
	}

	/**
	 *
	 * @param ResourceLoader $resourceLoader
	 * @return boolean
	 */
	public static function onResourceLoaderRegisterModules( &$resourceLoader ) {
		$resourceLoader->register(
			Flexiskin::generateDynamicModuleStyleName(),
			array(
				'class' => 'ResourceLoaderFlexiskinModule'
			)
		);
		return true;
	}

	public static function generateDynamicModuleStyleName(){
		return 'ext.bluespice.flexiskin.skin.' . BsConfig::get( 'MW::Flexiskin::Active' );
	}

	/**
	 * Gets the configuration for a Flexiskin by ID
	 * @param String $sId
	 * @return stdClass The config object
	 */
	public static function getFlexiskinConfig( $sId ) {

		if ( $sId == "" ) {
			throw new Exception( wfMessage( 'bs-flexiskin-error-get-config', 'id' )->plain() );
		}

		$oStatus = BsFileSystemHelper::getFileContent( "conf.json", "flexiskin" . DS . $sId );

		if ( !$oStatus->isGood() ) {
			throw new Exception( wfMessage( 'bs-flexiskin-error-get-config', $this->getErrorMessage( $oStatus ) )->plain() );
		}

		return FormatJson::decode( $oStatus->getValue() );
	}

	/**
	 * UnitTestsList allows registration of additional test suites to execute
	 * under PHPUnit. Extensions can append paths to files to the $paths array,
	 * and since MediaWiki 1.24, can specify paths to directories, which will
	 * be scanned recursively for any test case files with the suffix "Test.php".
	 * @param array $paths
	 */
	public static function onUnitTestsList ( array &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}
}
