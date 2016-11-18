<?php

require_once( dirname(dirname(dirname(dirname(__DIR__)))) . '/maintenance/Maintenance.php' );

class clearConfigs extends Maintenance{
	public function execute() {
		$aFinalPortletList = array();
		$aPortlets = array();

		Hooks::run( 'BSDashboardsUserDashboardPortalPortlets', array( &$aPortlets ) );
		Hooks::run( 'BSDashboardsAdminDashboardPortalPortlets', array( &$aPortlets ) );
		Hooks::run( 'BSDashboardsGetPortlets', array( &$aPortlets ) );

		for ( $i = 0; $i < count( $aPortlets ); $i++ ) {
			$aFinalPortletList[] = $aPortlets[$i]["type"];
		}

		$oDbr = $this->getDB( DB_SLAVE );
		$res = $oDbr->select( 'bs_dashboards_configs', '*' );

		foreach( $res as $row ) {
			$iUser = $row->dc_identifier;
			$sType = $row->dc_type;
			$aPortalConfig = unserialize( $row->dc_config );
			$aPortalConfig = FormatJson::decode( $aPortalConfig );
			$bHasChange = false;

			for ( $x = 0; $x < count( $aPortalConfig ); $x++ ){
				for ( $y = 0; $y < count( $aPortalConfig[$x] ); $y++ ){
					if ( !in_array( $aPortalConfig[$x][$y]->type, $aFinalPortletList ) ){
						$this->output( "Will remove " . $aPortalConfig[$x][$y]->type );
						unset( $aPortalConfig[$x][$y] );
						$bHasChange = true;
					}
				}
			}
			$aPortalConfig = FormatJson::encode( $aPortalConfig );
			if ( $bHasChange ) {
				$oDbw = $this->getDB( DB_MASTER );
				$oDbw->update(
						'bs_dashboards_configs',
						array(
							'dc_config' => serialize( $aPortalConfig )
						),
						array(
							'dc_type' => $sType,
							'dc_identifier' => $iUser
						)
					);
			}
		}
	}
}

$maintClass = 'clearConfigs';
if (defined('RUN_MAINTENANCE_IF_MAIN')) {
	require_once( RUN_MAINTENANCE_IF_MAIN );
} else {
	require_once( DO_MAINTENANCE ); # Make this work on versions before 1.17
}
