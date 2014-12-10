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
 * @version    2.22.0
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

	private static $iOldId = "";
	private static $iNewId = "";
	public static $sFlexiskinPath = "";

	/**
	 * Contructor of the Flexiskin class
	 */
	public function __construct() {
		wfProfileIn('BS::' . __METHOD__);
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME => 'Flexiskin',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-flexiskin-desc' )->escaped(),
			EXTINFO::AUTHOR => 'Tobias Weichart',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array(
				'bluespice' => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::Flexiskin';

		WikiAdmin::registerModule( 'Flexiskin', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_flexiskin_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-flexiskin-label'
		) );

		wfProfileOut('BS::' . __METHOD__);
	}

	/**
	 * Initialization of ArticleInfo extension
	 */
	public function initExt() {
		global $wgOut, $wgRequest, $wgUploadPath;
		wfProfileIn('BS::' . __METHOD__);
		//$this->mCore->registerPermission('flexiskinchange');
		self::$sFlexiskinPath = BsFileSystemHelper::getDataDirectory('flexiskin') . DS;
		BsConfig::registerVar('MW::Flexiskin::Active', "default", BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-flexiskin-pref-active', 'select');
		BsConfig::registerVar('MW::Flexiskin::Logo', "", BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-flexiskin-pref-logo', 'text');
		if (self::getVal('flexiskin') || BsConfig::get('MW::Flexiskin::Active') != '') {
			$sId = self::getVal('flexiskin') != '' ? self::getVal('flexiskin') : BsConfig::get('MW::Flexiskin::Active');
			if ($sId != "default")
				$wgOut->addHeadItem('flexiskin', "<link rel='stylesheet' href='" . $wgUploadPath . "/bluespice/flexiskin/" . $sId . "/style" . (self::getVal('preview', '') != "" ? '.tmp' : '') . ".css'>");
		}
		$this->mCore->registerPermission('flexiskinedit');
		wfProfileOut('BS::' . __METHOD__);
	}

	public function getForm( $firsttime = false ) {
		$this->getOutput()->addModules("ext.bluespice.flexiskin");
		return '<div id="bs-flexiskin-container"></div>';
	}

	public function runPreferencePlugin($sAdapterName, $oVariable) {
		$aData = self::getFlexiskins(false);
		$aResult = array('options' => array(
				wfMessage('bs-flexiskin-defaultname')->plain() => 'default',
		));
		if (isset($aData['flexiskin']) && count($aData['flexiskin']) > 0)
		foreach ($aData['flexiskin'] as $aConf) {
			$aResult['options'][$aConf['flexiskin_name']] = $aConf['flexiskin_id'];
		}
		return $aResult;
	}

	public static function getFlexiskins($bEncode = true) {
		$sActiveSkin = BsConfig::get('MW::Flexiskin::Active');
		BsFileSystemHelper::ensureDataDirectory("flexiskin" . DS );
		if ($handle = opendir(BS_DATA_DIR . DS . "flexiskin")) {
			while (false !== ($entry = readdir($handle))) {
				if ($entry != "." && $entry != "..") {
					$oStatus = BsFileSystemHelper::getFileContent("conf.json", "flexiskin" . DS . $entry);
					if (!$oStatus->isGood())
						continue;
					$aFile = json_decode($oStatus->getValue());
					//PW(27.11.2013) TODO: this should not be needed!
					if (!isset($aFile[0]) || !is_object($aFile[0]))
						continue;
					$aData ['flexiskin'][] = array(
						'flexiskin_id' => $entry,
						'flexiskin_name' => $aFile[0]->name,
						'flexiskin_desc' => $aFile[0]->desc,
						'flexiskin_active' => $sActiveSkin == $entry ? true : false
					);
				}
			}
			closedir($handle);
		}
		$aData['totalCount'] = isset($aData['flexiskin']) ? count($aData['flexiskin']) : 0;

		return $bEncode ? json_encode($aData) : $aData;
	}

	public static function activateFlexiskin() {
		global $wgRequest;
		BsConfig::set("MW::Flexiskin::Active", self::getVal('id', ''));
		BsConfig::saveSettings();
		return json_encode(array('success' => true));
	}

	public static function addFlexiskin() {
		$aData = json_decode(self::getVal('data'));
		$oData = $aData[0];
		if (is_null($oData->template))
			return json_encode(array('success' => false, 'msg' => wfMessage('bs-flexiskin-error-templatenotexists')->plain()));
		if( empty($oData->template) )
			$oData->template = 'default';

		$sId = str_replace(" ", "_", strtolower($oData->name));
		if (is_dir((self::$sFlexiskinPath . "/" . md5($sId)))) {
			return json_encode(array('success' => false, 'msg' => wfMessage('bs-flexiskin-error-skinexists')->plain()));
		}
		//PW(27.11.2013) TODO: add check template really exists before add
		if( empty($oData->name) ) {
			//PW(27.11.2013) TODO: add msg
			return json_encode(array('success' => false, 'msg' => wfMessage('bs-flexiskin-error-nameempty')->plain()));
		}
		if ( $oData->template != 'default'){
			$oConfig = self::getFlexiskinConfig(true, $oData->template);
			if (!$oConfig->isGood())
				return json_encode(array('success' => false, 'msg' => wfMessage('bs-flexiskin-error-templatenotexists')->plain()));
			$oConf = json_decode($oConfig->getValue());
			$oConf[0]->name = $oData->name;
			$oConf[0]->desc = $oData->desc;
			$sConfigFile = json_encode($oConf);
		}
		else $sConfigFile = self::generateConfigFile($oData);

		$oStatus = BsFileSystemHelper::saveToDataDirectory('conf.json', $sConfigFile, "flexiskin" . DS . md5($sId));
		if (!$oStatus->isGood()) {
			return json_encode(array('success' => false, 'msg' => wfMessage('bs-flexiskin-error-fail-add-skin', self::getErrorMessage($oStatus))->plain()));
		}
		if ( $oData->template != 'default' ){
			$oStatus = BsFileSystemHelper::copyFile('style.css', "flexiskin" . DS . $oData->template, "flexiskin" . DS . md5($sId));
			$oStatus = BsFileSystemHelper::copyFolder("images", "flexiskin" . DS . $oData->template, "flexiskin" . DS . md5($sId));
		}
		else{
			$oStatus = BsFileSystemHelper::saveToDataDirectory('style.css', self::generateStyleFile($sConfigFile), "flexiskin" . DS . md5($sId));
		}
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage('bs-flexiskin-error-fail-add-skin', self::getErrorMessage($oStatus))->plain()));
		BsFileSystemHelper::ensureDataDirectory("flexiskin" . DS . md5($sId) . DS . "images");
		return json_encode(array('success' => true));
	}

	private static function generateConfigFile($oData) {
		$sConfig = '[{"id":"general","name":"' . $oData->name . '","desc":"' . $oData->desc . '","backgroundColor":"F4F4F4","customBackgroundColor":"F4F4F4","backgroundImage":"","repeatBackground":"no-repeat"},';
		$sConfig .= '{"id":"header","logo":""},';
		$sConfig .= '{"id":"position","navigation":"left","content":"center","width":"1222", "fullWidth":"0"}]';
		return $sConfig;
	}

	public static function getFlexiskinConfig($bReturnStatus = false, $sId = "", $bIsPreview = false) {
		global $wgRequest;
		$iId = $sId == "" ? self::getVal('id') : $sId;
		if ($bIsPreview)
			$oStatus = BsFileSystemHelper::getFileContent("conf.tmp.json", "flexiskin" . DS . $iId);
		else
			$oStatus = BsFileSystemHelper::getFileContent("conf.json", "flexiskin" . DS . $iId);
		if ($bReturnStatus)
			return $oStatus;
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage('bs-flexiskin-error-get-config', self::getErrorMessage($oStatus))->plain()));
		return json_encode(array('success' => true, 'config' => $oStatus->getValue()));
	}

	public static function deleteFlexiskin() {
		global $wgRequest;
		$iId = self::getVal('skinId');
		$oStatus = BsFileSystemHelper::deleteFolder("flexiskin" . DS . $iId);
		if (BsConfig::get("MW::Flexiskin::Active") == $iId){
			BsConfig::set("MW::Flexiskin::Active", "");
			BsConfig::saveSettings();
		}
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage('bs-flexiskin-error-delete-skin', self::getErrorMessage($oStatus))->plain()));
		else
			return json_encode(array('success' => true));
	}

	public static function saveFlexiskinPreview() {
		global $wgRequest, $wgScriptPath;
		$aData = self::getValues();
		$aFile = "";
		$aConfigs = json_decode($aData['data']);
		$aFile = self::generateStyleFile($aConfigs);
		/* foreach ($aConfigs as $aConfig) {
		  $func = "Flexiskin::format_" . $aConfig->id;
		  if (is_callable($func))
		  $aFile[] = call_user_func($func, $aConfig);
		  } */
		$sId = self::getVal('id');
		$oStatus = BsFileSystemHelper::saveToDataDirectory("style.tmp.css", $aFile, "flexiskin" . DS . $sId);
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage("bs-flexiskin-error-save-preview", self::getErrorMessage($oStatus))->plain()));
		$oStatus = BsFileSystemHelper::saveToDataDirectory("conf.tmp.json", $aData['data'], "flexiskin" . DS . $sId);
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage("bs-flexiskin-error-save-preview", self::getErrorMessage($oStatus))->plain()));
		return json_encode(array('success' => true, 'src' => $wgScriptPath . "/index.php?flexiskin=" . self::getVal('id') . "&preview=true"));
	}

	public static function resetFlexiskin() {
		global $wgRequest, $wgScriptPath;
		$sId = self::getVal('id');
		$oStatus = BsFileSystemHelper::deleteFile("style.tmp.css", "flexiskin" . DS . $sId);
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage("bs-flexiskin-reset-error", self::getErrorMessage($oStatus))->plain()));
		$oStatus = BsFileSystemHelper::deleteFile("conf.tmp.json", "flexiskin" . DS . $sId);
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage("bs-flexiskin-reset-error", self::getErrorMessage($oStatus))->plain()));
		$oStatus = self::getFlexiskinConfig(true);
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage("bs-flexiskin-reset-error", self::getErrorMessage($oStatus))->plain()));
		return json_encode(array(
			'success' => true,
			'src' => $wgScriptPath . "/index.php?flexiskin=" . self::getVal('id'),
			'data' => array('skinId' => $sId, 'config' => $oStatus->getValue())
		));
	}

	public static function saveFlexiskin() {
		global $wgRequest, $wgScriptPath;
		$aData = self::getValues();
		self::$iOldId = $aData['id'];
		$aConfigs = json_decode($aData['data']);
		$aFile = self::generateStyleFile($aConfigs);
		if (self::$iOldId != self::$iNewId && is_dir(self::$sFlexiskinPath . DS . self::$iNewId) && file_exists(self::$sFlexiskinPath . DS . self::$iNewId . DS . "conf.json")) {
			return json_encode(array('success' => false, 'msg' => wfMessage('bs-flexiskin-error-skinexists')->plain()));
		}
		if (self::$iOldId != self::$iNewId && is_dir(self::$sFlexiskinPath . DS . self::$iOldId)) {
			$oStatus = BsFileSystemHelper::renameFolder("flexiskin" . DS . self::$iOldId, "flexiskin" . DS . self::$iNewId);
			if (!$oStatus->isGood())
				return json_encode(array('success' => false, 'msg' => wfMessage("bs-flexiskin-error-save", self::getErrorMessage($oStatus))->plain()));
		}
		//may happen
		if (!is_dir(self::$sFlexiskinPath . "/" . self::$iNewId))
			mkdir(self::$sFlexiskinPath . "/" . self::$iNewId);
		$oStatus = BsFileSystemHelper::saveToDataDirectory("style.css", $aFile, "flexiskin" . DS . self::$iNewId);
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage("bs-flexiskin-error-save", self::getErrorMessage($oStatus))->plain()));
		$oStatus = BsFileSystemHelper::saveToDataDirectory("conf.json", $aData['data'], "flexiskin" . DS . self::$iNewId);
		if (!$oStatus->isGood())
			return json_encode(array('success' => false, 'msg' => wfMessage("bs-flexiskin-error-save", self::getErrorMessage($oStatus))->plain()));
		return json_encode(array('success' => true, 'id' => self::$iNewId, 'src' => $wgScriptPath . "/index.php?flexiskin=" . self::$iNewId));
	}

	public static function uploadFile() {
		global $wgRequest;
		$oStatus = BsFileSystemHelper::uploadFile(self::getVal('name'), "flexiskin" . DS . self::getVal('id') . DS . "images");

		if (!$oStatus->isGood())
			$aResult = json_encode(array('success' => false, 'msg' => "err_cd:" . $aStatus['status']));
		else
			$aResult = json_encode(array('success' => true, 'name' => $oStatus->getValue()));
		$oAjaxResponse = new AjaxResponse( $aResult );
		$oAjaxResponse->setContentType( 'text/html' );
		return $oAjaxResponse;
	}

	private static function getErrorMessage(Status $oStatus) {
		$aMsg = $oStatus->getErrorsArray();
		return $aMsg[0];
	}

	private static function generateStyleFile($aConfigs) {
		$aFile = array();
		if (!is_array($aConfigs))
			$aConfigs = json_decode($aConfigs);
		foreach ($aConfigs as $aConfig) {
			$func = "Flexiskin::format_" . $aConfig->id;
			if (is_callable($func))
				$aFile[] = call_user_func($func, $aConfig);
		}
		return implode(" \n", $aFile);
	}

	/**
	 * Modifies the logo on runtime
	 * @param SkinTemplate $sktemplate
	 * @param BaseTemplate $tpl
	 * @return boolean Always true to keep hook running
	 */
	public static function onSkinTemplateOutputPageBeforeExec(&$sktemplate, &$tpl){
		if (self::getVal('flexiskin') || BsConfig::get('MW::Flexiskin::Active') != '') {
			$sId = self::getVal('flexiskin') != '' ? self::getVal('flexiskin') : BsConfig::get('MW::Flexiskin::Active');
			if ($sId != "default"){
				if (self::getVal("preview")) {
					$aResult = FormatJson::decode(self::getFlexiskinConfig(false, $sId, true));
				}
				else {
					$aResult = FormatJson::decode(self::getFlexiskinConfig(false, $sId, false));
				}
				$aConfig = FormatJson::decode($aResult->config);
				if ($aConfig[1]->logo === "") {
					return true;
				}
				$sPath = BS_DATA_PATH . "/flexiskin/" . $sId . "/images/";
				$tpl->set( 'logopath', $sPath . $aConfig[1]->logo);
				return true;
			}
			return true;
		}
		return true;
	}

	private static function format_general($aConfig) {
		$aReturn = "";
		$sName = str_replace(" ", "_", strtolower($aConfig->name));
		self::$iNewId = md5($sName);
		if ($aConfig->customBackgroundColor == "" && (ctype_xdigit($aConfig->customBackgroundColor)))
			$aReturn[] = "body{background-color:#" . $aConfig->backgroundColor . " !important;}";
		else
			$aReturn[] = "body{background-color:#" . $aConfig->customBackgroundColor . " !important;}";
		if ($aConfig->backgroundImage != "")
			$aReturn[] = "body{background-image:url('images/" . $aConfig->backgroundImage . "') !important;}";
		else
			$aReturn[] = "body{background-image:none !important;}";
		$aReturn[] = "body{background-repeat:".$aConfig->repeatBackground . " !important;}";
		return implode(" \n", $aReturn);
	}

	private static function format_header($aConfig) {
		global $wgRequest;
		$aReturn = array();

		//$aReturn[] = "#bs-logo{background-image:url('images/".$aConfig->logo."');}";
		return implode(" \n", $aReturn);
	}

	private static function format_position($aConfig) {
		$aReturn = "";
		if ($aConfig->navigation == 'right') {
			$aReturn[] = "#bs-application{position: relative;}";
			$aReturn[] = "#bs-content-column{margin: 0 302px 0 0;}";
			$aReturn[] = "#bs-nav-sections{right: 0; top: 30px;}";
			$aReturn[] = "#footer{margin: 0px 302px 5px 0px}";
		}
		if ($aConfig->content != 'center') {
			$aReturn[] = "#bs-wrapper{margin-" . $aConfig->content . ":0;}";
		}
		if ($aConfig->fullWidth == 0){
			$aReturn[] = "#bs-menu-top{width:" . (int) $aConfig->width . "px;}";
			$aReturn[] = "#bs-application{width:" . (int) $aConfig->width . "px;}";
			$aReturn[] = "#bs-wrapper{width:" . (int) $aConfig->width . "px;min-width:" . (int) $aConfig->width . "px;}";
			//$aReturn[] = "#bs-tools-container{width:" . ((int) $aConfig->width - 200 + 28) . "px;margin-left:-" . ((int) $aConfig->width - 246) . "px}";
		}
		else{
			$aReturn[] = "#bs-menu-top{width:100%;}";
			$aReturn[] = "#bs-application{width:100%;}";
			$aReturn[] = "#bs-wrapper{width:100%;min-width:100%;}";
		}

		return implode(" \n", $aReturn);
	}
	private static function getVal($sVal, $sDefault = "", $bIsArray = false){
		global $wgRequest;
		$sValSearched = $wgRequest->getVal($sVal, $sDefault);
		return /*self::sanitizeString(*/$sValSearched/*)*/;
	}

	private static function getValues(){
		global $wgRequest;
		$aValSearched = $wgRequest->getValues();
		return /*self::sanitizeArray(*/$aValSearched/*)*/;
	}

	private static function sanitizeArray($aArray){
		foreach ($aArray as $key => $sString){
			$aReturn [$key] = is_array($sString) ? self::sanitizeArray($sString) : self::sanitizeString($sString);
		}
		return $aReturn;
	}

	private static function sanitizeString($sString){
		return htmlentities(str_replace('\\', "", str_replace('/', "", $sString)), ENT_NOQUOTES);
	}
}
