<?php
// This class only exists for legacy reasons and may be removed in a future
// version. It maps API calls to the old flexiskin API to the new
// bs-flexiskin-store.
class FlexiskinApi extends ApiBase {

	/**
	 * Execute method for the API
	 * @return boolean
	 */
	public function execute() {
		wfDeprecated( __METHOD__, '2.23.3' );
		$aParams = $this->extractRequestParams();
		$sType = isset( $aParams['type'] ) ? $aParams['type'] : "get";
		$sMode = isset( $aParams['mode'] ) ? $aParams['mode'] : "flexiskin";

		if ( $sType == "get" && $sMode == "flexiskin" ) {
			$aResult = "bs-flexiskin-store";
		}
		else {
			$this->dieUsageMsg( array( 'bs-flexiskin-api-error-invalid-action' ) );
		}

		$this->getResult()->addValue( null, $this->getModuleName(), $aResult );

		//tbd: check result, maybe return false sometimes
		return true;
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

	public function isReadMode() {
		return false;
	}
}
