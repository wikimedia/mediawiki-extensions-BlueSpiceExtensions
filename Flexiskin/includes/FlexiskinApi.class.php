<?php
//todo: switch to BsApiBase
class FlexiskinApi extends ApiBase {

	/**
	 * Execute method for the API
	 * @return boolean
	 */
	public function execute() {
		$aParams = $this->extractRequestParams();
		$sType = isset($aParams['type']) ? $aParams['type'] : "get";
		$sMode = isset($aParams['mode']) ? $aParams['mode'] : "flexiskin";

		$sAction = $sType . ucfirst( $sMode );
		if ( !method_exists( $this, $sAction ) ) {
			$this->dieUsageMsg( array( 'bs-flexiskin-api-error-invalid-action' ) );
		}
		$aResult = $this->$sAction();
		$this->getResult()->addValue( null, $this->getModuleName(), $aResult );

		//tbd: check result, maybe return false sometimes
		return true;
	}

	/**
	 * Method to return all existing Flexiskins
	 * @return Array contining the Flexiskins
	 */
	public function getFlexiskin() {
		$sActiveSkin = BsConfig::get( 'MW::Flexiskin::Active' );
		$oStatus = BsFileSystemHelper::ensureDataDirectory( "flexiskin" . DS );
		if (!$oStatus->isGood()){
			return array();
		}
		$aData = array ();
		if ( $handle = opendir( $oStatus->getValue() ) ) {
			while ( false !== ($entry = readdir( $handle )) ) {
				if ( $entry != "." && $entry != ".." ) {
					$oStatus = BsFileSystemHelper::getFileContent( "conf.json", "flexiskin" . DS . $entry );
					if ( !$oStatus->isGood() ) {
						continue;
					}
					$aFile = FormatJson::decode( $oStatus->getValue() );
					//PW(27.11.2013) TODO: this should not be needed!
					if ( !isset( $aFile[0] ) || !is_object( $aFile[0] ) ) {
						continue;
					}
					$aData [] = array(
						'flexiskin_id' => $entry,
						'flexiskin_name' => $aFile[0]->name,
						'flexiskin_desc' => $aFile[0]->desc,
						'flexiskin_active' => $sActiveSkin == $entry ? true : false
					);
				}
			}
			closedir( $handle );
		}

		return $aData;
	}

	/**
	 * Method to save a Flexiskin defined by id and data via request parameter
	 * @global String $wgScriptPath
	 * @return String encoded result JSON string
	 */
	public function saveFlexiskin() {
		global $wgScriptPath;
		$aData = $this->getMain()->getVal( 'data', array() );
		$sOldId = $this->getMain()->getVal( 'id' );
		$aConfigs = FormatJson::decode( $aData );
		$sNewId = Flexiskin::getFlexiskinIdFromConfig( $aConfigs );
		$aFile = Flexiskin::generateStyleFile( $aConfigs );
		$sFlexiskinPath = BsFileSystemHelper::getDataDirectory( 'flexiskin' ) . DS;
		//check if skin already exists
		if ( $sOldId != $sNewId && is_dir( $sFlexiskinPath . DS . $sNewId ) && file_exists( $sFlexiskinPath . DS . $sNewId . DS . "conf.json" ) ) {
			return FormatJson::encode( array(
				'success' => false,
				'msg' => wfMessage( 'bs-flexiskin-error-skinexists' )->plain()
					) );
		}
		if ( $sOldId != $sNewId && is_dir( $sFlexiskinPath . DS . $sOldId ) ) {
			$oStatus = BsFileSystemHelper::renameFolder( "flexiskin" . DS . $sOldId, "flexiskin" . DS . $sNewId );
			if ( !$oStatus->isGood() ) {
				return FormatJson::encode( array(
					'success' => false,
					'msg' => wfMessage( "bs-flexiskin-api-error-save", $this->getErrorMessage( $oStatus ) )->plain()
						) );
			}
		}
		BsFileSystemHelper::ensureDataDirectory($sFlexiskinPath . DS . $sNewId);
		$oStatus = BsFileSystemHelper::saveToDataDirectory( "variables.less", $aFile, "flexiskin" . DS . $sNewId );
		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array(
				'success' => false,
				'msg' => wfMessage( "bs-flexiskin-api-error-save", $this->getErrorMessage( $oStatus ) )->plain()
					) );
		}
		$oStatus = BsFileSystemHelper::saveToDataDirectory( "conf.json", $aData, "flexiskin" . DS . $sNewId );
		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array(
				'success' => false,
				'msg' => wfMessage( "bs-flexiskin-api-error-save", $this->getErrorMessage( $oStatus ) )->plain()
					) );
		}
		return FormatJson::encode( array(
			'success' => true,
			'id' => $sNewId,
			'src' => $wgScriptPath . "/index.php?flexiskin=" . $sNewId )
				);
	}

	/**
	 * Saves the preview of a flexiskin defined by id and data via request parameter
	 * @global String $wgScriptPath
	 * @return String encoded result JSON string
	 */
	public function savePreview() {
		global $wgScriptPath;
		$sId = $this->getMain()->getVal( 'id', '' );
		if ( $sId == "" ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-api-error-missing-param', 'id' )->plain() ) );
		}
		$aData = $this->getMain()->getVal( 'data', array() );
		$aConfigs = FormatJson::decode( $aData );
		$aFile = Flexiskin::generateStyleFile( $aConfigs );
		$oStatus = BsFileSystemHelper::saveToDataDirectory( "variables.tmp.less", $aFile, "flexiskin" . DS . $sId );
		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( "bs-flexiskin-api-error-save-preview", $this->getErrorMessage( $oStatus ) )->plain() ) );
		}
		$oStatus = BsFileSystemHelper::saveToDataDirectory( "conf.tmp.json", $aData, "flexiskin" . DS . $sId );
		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( "bs-flexiskin-api-error-save-preview", $this->getErrorMessage( $oStatus ) )->plain() ) );
		}
		//tbd: check if this is usefull
		RequestContext::getMain()->getTitle()->invalidateCache();
		return FormatJson::encode( array( 'success' => true, 'src' => $wgScriptPath . "/index.php?flexiskin=" . $sId . "&preview=true" ) );
	}

	/**
	 * Gets the configuration from the config file defined by id via request parameter
	 * @return String encoded result JSON string
	 */
	public function getConfig() {
		$sId = $this->getMain()->getVal( 'id', '' );
		$bIsPreview = $this->getRequest()->getBool( 'preview', false );

		if ( $sId == "" ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-api-error-missing-param', 'id' )->plain() ) );
		}

		$oStatus = $this->getConfigFromId( $sId, $bIsPreview );

		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-error-get-config', $this->getErrorMessage( $oStatus ) )->plain() ) );
		}
		return FormatJson::encode( array( 'success' => true, 'config' => $oStatus->getValue() ) );
	}

	/**
	 * Gets the configuration for a Flexiskin by ID
	 * @param String $sId
	 * @param boolean $bPreview
	 * @return Status The status object
	 */
	public function getConfigFromId( $sId, $bPreview = false ) {
		if ( $bPreview ) {
			$oStatus = BsFileSystemHelper::getFileContent( "conf.tmp.json", "flexiskin" . DS . $sId );
		} else {
			$oStatus = BsFileSystemHelper::getFileContent( "conf.json", "flexiskin" . DS . $sId );
		}
		return $oStatus;
	}

	/**
	 * Deletes a flexiskin defined by id via request parameter
	 * @return String encoded result JSON string
	 */
	public function deleteFlexiskin() {
		$sId = $this->getMain()->getVal( 'id', '' );
		if ( $sId == "" ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-api-error-missing-param', 'id' )->plain() ) );
		}

		$oStatus = BsFileSystemHelper::deleteFolder( "flexiskin" . DS . $iId );
		if ( BsConfig::get( "MW::Flexiskin::Active" ) == $iId ) {
			BsConfig::set( "MW::Flexiskin::Active", "" );
			BsConfig::saveSettings();
		}
		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-error-delete-skin', $this->getErrorMessage( $oStatus ) )->plain() ) );
		} else {
			return FormatJson::encode( array( 'success' => true ) );
		}
	}

	/**
	 * Adds a Flexiskin defined by data via request parameter
	 * @return String encoded result JSON string
	 */
	public function addFlexiskin() {
		$aData = FormatJson::decode( $this->getMain()->getVal( 'data', "" ) );
		$oData = $aData[0];

		if ( is_null( $oData->template ) ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-error-templatenotexists' )->plain() ) );
		}
		if ( empty( $oData->template ) ) {
			$oData->template = 'default';
		}

		$sId = str_replace( " ", "_", strtolower( $oData->name ) );
		$sFlexiskinPath = BsFileSystemHelper::getDataDirectory( 'flexiskin' . DS . md5($sId) );
		if ( is_dir( $sFlexiskinPath ) ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-error-skinexists' )->plain() ) );
		}
		//PW(27.11.2013) TODO: add check template really exists before add
		if ( empty( $oData->name ) ) {
			//PW(27.11.2013) TODO: add msg
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-error-nameempty' )->plain() ) );
		}
		if ( $oData->template != 'default' ) {
			$oConfig = $this->getConfigFromId( $oData->template );
			if ( !$oConfig->isGood() ) {
				return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-error-templatenotexists' )->plain() ) );
			}
			$oConf = FormatJson::decode( $oConfig->getValue() );
			$oConf[0]->name = $oData->name;
			$oConf[0]->desc = $oData->desc;
			$sConfigFile = FormatJson::encode( $oConf );
		} else {
			$sConfigFile = Flexiskin::generateConfigFile( $oData );
		}

		$oStatus = BsFileSystemHelper::saveToDataDirectory( 'conf.json', $sConfigFile, "flexiskin" . DS . md5( $sId ) );
		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-error-fail-add-skin', $this->getErrorMessage( $oStatus ) )->plain() ) );
		}
		if ( $oData->template != 'default' ) {
			$oStatus = BsFileSystemHelper::copyFile( 'variables.less', "flexiskin" . DS . $oData->template, "flexiskin" . DS . md5( $sId ) );
			$oStatus = BsFileSystemHelper::copyFolder( "images", "flexiskin" . DS . $oData->template, "flexiskin" . DS . md5( $sId ) );
		} else {
			$oStatus = BsFileSystemHelper::saveToDataDirectory( 'variables.less', Flexiskin::generateStyleFile( $sConfigFile ), "flexiskin" . DS . md5( $sId ) );
		}
		$oStatus = BsFileSystemHelper::saveToDataDirectory('screen.less', Flexiskin::generateScreenFile(), "flexiskin" . DS . md5($sId));
		$oStatus = BsFileSystemHelper::saveToDataDirectory('screen.tmp.less', Flexiskin::generateScreenFile(true), "flexiskin" . DS . md5($sId));
		//tbd: check 1st, 2nd and 3rd status
		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-error-fail-add-skin', $this->getErrorMessage( $oStatus ) )->plain() ) );
		}
		BsFileSystemHelper::ensureDataDirectory( "flexiskin" . DS . md5( $sId ) . DS . "images" );
		return FormatJson::encode( array( 'success' => true ) );
	}

	/**
	 * Activates the Flexiskin defined by id via request parameter
	 * @return String encoded result JSON string
	 */
	public function activateFlexiskin() {
		$sId = $this->getMain()->getVal( 'id', '' );
		BsConfig::set( "MW::Flexiskin::Active", $sId );
		BsConfig::saveSettings();
		return FormatJson::encode( array( 'success' => true ) );
	}

	/**
	 * Resets the Flexiskin preview to the last saved one defined by id via request parameter
	 * @global String $wgScriptPath
	 * @return String encoded result JSON string
	 */
	public function resetFlexiskin() {
		global $wgScriptPath;
		$sId = $this->getMain()->getVal( 'id', '' );
		if ( $sId == "" ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-api-error-missing-param', 'id' )->plain() ) );
		}
		$oStatus = BsFileSystemHelper::deleteFile( "variables.tmp.less", "flexiskin" . DS . $sId );
		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( "bs-flexiskin-reset-error", $this->getErrorMessage( $oStatus ) )->plain() ) );
		}
		$oStatus = BsFileSystemHelper::deleteFile( "conf.tmp.json", "flexiskin" . DS . $sId );
		if ( !$oStatus->isGood() ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( "bs-flexiskin-reset-error", $this->getErrorMessage( $oStatus ) )->plain() ) );
		}
		$oResult = FormatJson::decode( $this->getConfig( $sId ) );
		if ( !$oResult->success ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( "bs-flexiskin-reset-error", $oResult->msg )->plain() ) );
		}
		return FormatJson::encode( array(
					'success' => true,
					'src' => $wgScriptPath . "/index.php?flexiskin=" . $sId,
					'data' => array( 'skinId' => $sId, 'config' => $oResult->config )
				) );
	}

	/**
	 * Triggers a file upload to the Flexiskin data directory defined by id via request parameter
	 * @return AjaxResponse The AjaxResponse Object
	 */
	public function uploadFile() {
		$sId = $this->getMain()->getVal( 'id', '' );
		if ( $sId == "" ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-api-error-missing-param', 'id' )->plain() ) );
		}
		$sName = $this->getMain()->getVal( 'name', '' );
		if ( $sId == "" ) {
			return FormatJson::encode( array( 'success' => false, 'msg' => wfMessage( 'bs-flexiskin-api-error-missing-param', 'name' )->plain() ) );
		}
		$oStatus = BsFileSystemHelper::uploadFile( $sName, "flexiskin" . DS . $sId . DS . "images" );

		if ( !$oStatus->isGood() ) {
			$aResult = FormatJson::encode( array( 'success' => false, 'msg' => "err_cd:" . $oStatus->getMessage() ) );
		} else {
			$aResult = FormatJson::encode( array( 'success' => true, 'name' => $oStatus->getValue() ) );
		}
		return $aResult;
	}

	/**
	 * Wrapper method for Status Object messages
	 * @param Status $oStatus
	 * @return String The error message
	 */
	private function getErrorMessage( Status $oStatus ) {
		return $oStatus->getMessage()->plain();
	}

	/**
	 * The API description displayed at api.php
	 * @return String the description
	 */
	public function getDescription() {
		return wfMessage( "bs-flexiskin-api-desc" )->plain();
	}

	/**
	 * Defines the params that can be used via api.php
	 * @return Array The params
	 */
	public function getAllowedParams() {
		return array(
			'type' => array(
				ApiBase::PARAM_TYPE => 'string',
				ApiBase::PARAM_REQUIRED => true
			),
			'mode' => array(
				ApiBase::PARAM_TYPE => 'string'
			),
			'id' => array(
				ApiBase::PARAM_TYPE => 'string'
			),
			'data' => array(
				ApiBase::PARAM_TYPE => 'string'
			),
			'preview' => array(
				ApiBase::PARAM_TYPE => 'boolean'
			),
			'background-hidden-field' => array(
				ApiBase::PARAM_TYPE => 'string'
			),
			//tbd: implement extjs params
			'_dc' => array(
				ApiBase::PARAM_TYPE => 'string'
			),
			'page' => array(
				ApiBase::PARAM_TYPE => 'integer'
			),
			'start' => array(
				ApiBase::PARAM_TYPE => 'integer'
			),
			'limit' => array(
				ApiBase::PARAM_TYPE => 'integer'
			)
		);
	}

	/**
	 * Sets the description for the params beeing used via api.php
	 * @return type
	 */
	public function getParamDescription() {
		return array(
			'type' => wfMessage( "bs-flexiskin-api-type-desc" )->plain(),
			'mode' => wfMessage( "bs-flexiskin-api-mode-desc" )->plain(),
			'id' => wfMessage( "bs-flexiskin-api-id-desc" )->plain(),
			'data' => wfMessage( "bs-flexiskin-api-data-desc" )->plain(),
			'preview' => wfMessage( "bs-flexiskin-api-preview-desc" )->plain()
			);
	}

	/**
	 * Set the api example displayed at api.php
	 * @return Array The example
	 */
	public function getExamples() {
		return array(
			'api.php?action=flexiskin&type=get&mode=config&format=xml'
			=> wfMessage( "bs-flexiskin-api-example-desc" )->plain()
		);
	}

}
