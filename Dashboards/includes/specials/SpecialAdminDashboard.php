<?php

class SpecialAdminDashboard extends BsSpecialPage {
	public function __construct( $name = '', $restriction = '', $listed = true, $function = false, $file = 'default', $includable = false ) {
		parent::__construct( 'AdminDashboard', 'wikiadmin' );
	}

	/**
	 *
	 * @global OutputPage $wgOut
	 * @param string $sParameter
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$this->checkForReadOnly();
		$this->getAdminConfig();

		$this->getOutput()->addHTML(
			Html::element( 'div', array( 'id' => 'bs-dashboards-admindashboard' ) )
		);

		return;
	}

	private function getAdminConfig() {
		$oDbr = wfGetDB( DB_REPLICA );
		$res = $oDbr->select(
				'bs_dashboards_configs',
				'*',
				array( 'dc_type' => 'admin' ),
				__METHOD__
		);

		if ( $oDbr->numRows( $res ) > 0 ) {
			$row = $oDbr->fetchObject( $res );
			$aPortalConfig = $row->dc_config;
			$aPortalConfig = FormatJson::decode( $aPortalConfig );
		} else {
			$bIsDefault = true;
			$aPortalConfig = array(
				array(),
				array(),
				array()
			);

			Hooks::run( 'BSDashboardsAdminDashboardPortalConfig', array( $this, &$aPortalConfig, $bIsDefault ) );
		}

		$sSaveBackend = 'saveAdminDashboardConfig';
		$sLocation = 'AdminDashboard';
		$this->getOutput()->addJsConfigVars( 'bsPortalConfigSavebackend', $sSaveBackend );
		$this->getOutput()->addJsConfigVars( 'bsPortalConfigLocation', $sLocation );
		$this->getOutput()->addJsConfigVars( 'bsPortalConfig', $aPortalConfig );

		$this->getOutput()->addModuleStyles( 'ext.bluespice.extjs.BS.portal.css' );
		$this->getOutput()->addModules( 'ext.bluespice.dashboards.adminDashboard' );

		return true;
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

	protected function getGroupName() {
		return 'bluespice';
	}
}