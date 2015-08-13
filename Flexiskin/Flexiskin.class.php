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
 * @author     Tobias Weichart <weichart@hallowelt.biz>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage Flexiskin
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
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
	 * Contructor of the Flexiskin class
	 */
	public function __construct() {
		wfProfileIn( 'BS::' . __METHOD__ );
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER;
		$this->mInfo = array(
			EXTINFO::NAME => 'Flexiskin',
			EXTINFO::DESCRIPTION => 'bs-flexiskin-desc',
			EXTINFO::AUTHOR => 'Tobias Weichart',
			EXTINFO::VERSION => 'default',
			EXTINFO::STATUS => 'default',
			EXTINFO::PACKAGE => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::Flexiskin';

		WikiAdmin::registerModule( 'Flexiskin', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_flexiskin_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-flexiskin-label'
		) );

		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * Initialization of ArticleInfo extension
	 */
	public function initExt() {
		global $wgOut, $wgUploadPath;
		wfProfileIn( 'BS::' . __METHOD__ );
		//$this->mCore->registerPermission('flexiskinchange');
		BsConfig::registerVar( 'MW::Flexiskin::Active', "default", BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-flexiskin-pref-active', 'select' );
		BsConfig::registerVar( 'MW::Flexiskin::Logo', "", BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-flexiskin-pref-logo', 'text' );
		$sFlexiskin = $this->getRequest()->getVal( 'flexiskin' );
		if ( $sFlexiskin || BsConfig::get( 'MW::Flexiskin::Active' ) != '' ) {
			$sId = $sFlexiskin != '' ? $sFlexiskin : BsConfig::get( 'MW::Flexiskin::Active' );
			if ( $sId != "default" ) {
				$bIsTemp = $this->getRequest()->getBool( 'preview', false );
				$this->addCssFile( $sId, $bIsTemp );
			}
		}
		$this->mCore->registerPermission( 'flexiskinedit', array(), array( 'type' => 'global' ) );
		wfProfileOut('BS::' . __METHOD__);
	}

	/**
	 * Replaces the BlueSpiceSkin screen.less file with the one specified by the parameters
	 * @global string $wgResourceModules
	 * @param string $sFlexiskinId
	 * @param int $bIsTemp
	 * @return boolean true of replaced correctly, otherwise false
	 */
	public function addCssFile( $sFlexiskinId, $bIsTemp = false ) {
		global $wgResourceModules;
		$oStatus = BsFileSystemHelper::ensureDataDirectory( "flexiskin/" . $sFlexiskinId );
		if ( !$oStatus->isGood() ) {
			return false;
		}
		$sFilePath = BsFileSystemHelper::getDataPath("flexiskin/" . $sFlexiskinId);
		$sFilePath .= "/screen" . ($bIsTemp ? '.tmp' : '') . ".less";
		if ( !isset( $wgResourceModules['skins.bluespiceskin'] ) ||
				!isset( $wgResourceModules['skins.bluespiceskin']['styles'] ) ) {
			return false;
		}
		foreach ( $wgResourceModules['skins.bluespiceskin']['styles'] as $iIndex => $sStylePath ) {
			//check if element ends with "screen.less"
			if ( strpos( $sStylePath, "screen.less", strlen( $sStylePath ) - strlen( "screen.less" ) ) === false ) {
				continue;
			}
			$wgResourceModules['skins.bluespiceskin']['styles'][$iIndex] = ".." . $sFilePath;
			return true;
		}
		return false;
	}

	public function getForm() {
		$this->getOutput()->addModules( "ext.bluespice.flexiskin" );
		return '<div id="bs-flexiskin-container"></div>';
	}

	public function runPreferencePlugin( $sAdapterName, BsConfig $oVariable ) {
		if (substr($oVariable->getKey(), 0, 13) != "MW::Flexiskin"){
			return array();
		}

		$api = new ApiMain(
				new DerivativeRequest(
				$this->getRequest(), array(
			'action' => 'flexiskin',
			'type' => 'get'
				), false
				), true
		);
		$oResult = $api->execute();
		$aData = $api->getResultData();
		$aResult = array( 'options' => array(
				wfMessage( 'bs-flexiskin-defaultname' )->plain() => 'default',
			) );
		if ( isset( $aData['flexiskin'] ) && count( $aData['flexiskin'] ) > 0 ) {
			foreach ( $aData['flexiskin'] as $aConf ) {
				$aResult['options'][$aConf['flexiskin_name']] = $aConf['flexiskin_id'];
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
		return FormatJson::encode($aConfig);
	}

	public static function generateScreenFile($bIsTmp = false){
		$aScreenFile = array();
		$aScreenFile[] = "@import '../../../../skins/BlueSpiceSkin/resources/variables.less';";
		$aScreenFile[] = "@import 'variables.".($bIsTmp ? "tmp." : "")."less';";
		$aScreenFile[] = "@import '../../../../skins/BlueSpiceSkin/resources/screen.layout.less';";
		$aScreenFile[] = "@import '../../../../skins/BlueSpiceSkin/resources/components.less';";
		return implode("\n", $aScreenFile);
	}

	public static function generateStyleFile( $aConfigs ) {
		$aFile = array();
		if ( !is_array( $aConfigs ) ) {
			$aConfigs = FormatJson::decode( $aConfigs );
		}
		$aFile[] = '@bs-skin-path: "../../../../skins/BlueSpiceSkin/resources/";';
		$sNewId = self::getFlexiskinIdFromConfig($aConfigs);
		foreach ( $aConfigs as $aConfig ) {
			$func = "FlexiskinFormatter::format_" . $aConfig->id;
			$bReturn = wfRunHooks( "BSFlexiskinGenerateStyleFile", array( &$func, &$aConfig ) );

			if ( $bReturn === true && is_callable( $func ) ) {
				$aFile[] = call_user_func_array( $func, array( $aConfig, $sNewId) );
			}
			else{
				wfDebug("BS::Flexiskin method " . $func . " could not be called.");
			}
		}
		return implode( " \n", $aFile );
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
	 * Modifies the logo on runtime
	 * @param SkinTemplate $sktemplate
	 * @param BaseTemplate $tpl
	 * @return boolean Always true to keep hook running
	 */
	public static function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		$sFlexiskin = $sktemplate->getRequest()->getVal( 'flexiskin' );
		if ( $sFlexiskin || BsConfig::get( 'MW::Flexiskin::Active' ) != '' ) {
			$sId = $sFlexiskin != '' ? $sFlexiskin : BsConfig::get( 'MW::Flexiskin::Active' );
			if ( $sId != "default" ) {
				$bPreview = $sktemplate->getRequest()->getVal( 'preview', false );
				$api = new ApiMain(
						new DerivativeRequest(
							$sktemplate->getRequest(),
							array(
								'action' => 'flexiskin',
								'type' => 'get',
								'mode' => 'config',
								'id' => $sId,
								'preview' => $bPreview
							),
							false
						),
						true
				);
				$api->execute();
				$aResult = $api->getResultData();
				$oResult = FormatJson::decode( $aResult['flexiskin'] );
				if ($oResult->success === false){
					return true;
				}
				$aConfig = FormatJson::decode($oResult->config);
				$sLogo = BsConfig::get("MW::Flexiskin::Logo");
				if ( $sLogo == "" ) {
					return true;
				}
				$sPath = BS_DATA_PATH . "/flexiskin/" . $sId . "/images/";
				$tpl->set( 'logopath', $sPath . $sLogo );
				return true;
			}
			return true;
		}
		return true;
	}

}
