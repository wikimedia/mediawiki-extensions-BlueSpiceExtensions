<?php

class SpecialUserDashboard extends BsSpecialPage {
	public function __construct( $name = '', $restriction = '', $listed = true, $function = false, $file = 'default', $includable = false ) {
		parent::__construct( 'UserDashboard' /*, 'sysop'*/ );
	}

	/**
	 * 
	 * @global OutputPage $wgOut
	 * @param string $sParameter
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );
		$this->checkForReadOnly();

		$oDbr = wfGetDB( DB_SLAVE );
		$res = $oDbr->select(
				'bs_dashboards_configs',
				'*',
				array( 'dc_identifier' => $this->getUser()->getId() ),
				__METHOD__
		);

		if ( $oDbr->numRows( $res ) > 0 ) {
			$row = $oDbr->fetchObject( $res );
			$aPortalConfig = unserialize( $row->dc_config );
			$aPortalConfig = FormatJson::decode( $aPortalConfig );
		} else {
			$bIsDefault = true;
			$aPortalConfig = array(
				array(),
				array(),
				array()
			);

			wfRunHooks( 'BSDashboardsUserDashboardPortalConfig', array( $this, &$aPortalConfig, $bIsDefault ) );
		}

		$sSaveBackend = 'Dashboards::saveUserDashboardConfig';
		$sLocation = 'UserDashboard';
		$this->getOutput()->addJsConfigVars( 'bsPortalConfigSavebackend', $sSaveBackend );
		$this->getOutput()->addJsConfigVars( 'bsPortalConfigLocation', $sLocation );
		$this->getOutput()->addJsConfigVars( 'bsPortalConfig', $aPortalConfig );

		$this->getOutput()->addModuleStyles( 'ext.bluespice.extjs.BS.portal.css' );
		$this->getOutput()->addModules('ext.bluespice.dashboards.userDashboard');
		$this->getOutput()->addHTML(
			Html::element( 'div', array( 'id' => 'bs-dashboards-userdashboard' ) )
		);
	}

	private function checkForReadOnly() {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$this->getOutput()->addHTML(
				'<script>var wgReadOnly = true; alert("' . wfMessage( 'bs-readonly', $wgReadOnly )->escaped() . '");</script>'
			);

			return true;
		}

		return false;
	}

}