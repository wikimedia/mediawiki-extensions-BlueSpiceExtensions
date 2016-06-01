<?php

class BSApiDashboardStore extends BSApiExtJSStoreBase {

	protected function makeData( $sQuery = '' ) {

		$aPortlets = array();

		Hooks::run( 'BSDashboardsUserDashboardPortalPortlets', array( &$aPortlets ) );

		for ( $i = 0; $i < count( $aPortlets ); $i++ ) {
			if ( !isset( $aPortlets[$i]['group'] ) ) {
				$aPortlets[$i]['groups'] = array( 'UserDashboard' );
			}
		};

		$aReturnArray = $aPortlets;

		$aPortlets = array();

		Hooks::run( 'BSDashboardsAdminDashboardPortalPortlets', array( &$aPortlets ) );

		for ( $i = 0; $i < count( $aPortlets ); $i++ ) {
			if ( !isset( $aPortlets[$i]['groups'] ) ) {
				$aPortlets[$i]['groups'] = array( 'AdminDashboard' );
			}
		};

		$aReturnArray = array_merge( $aReturnArray, $aPortlets );

		$aPortlets = array();

		Hooks::run( 'BSDashboardsGetPortlets', array( &$aPortlets ) );

		for ( $i = 0; $i < count( $aPortlets ); $i++ ) {
			if ( !isset( $aPortlets[$i]['groups'] ) ) {
				$aPortlets[$i]['groups'] = array( 'UserDashboard', 'AdminDashboard' );
			}
		};

		$aReturnArray = array_merge( $aReturnArray, $aPortlets );

		// make sure to return objects, not arrays
		$aReturnObjects = array();
		foreach ( $aReturnArray as $aReturn ) {
			$aReturnObjects[] = (object)$aReturn;
		}

		return $aReturnObjects;
	}

	public function filterCallback( $aDataSet ) {
		$aFilter = $this->getParameter( 'filter' );

		foreach ( $aFilter as $oFilter ) {
			if( $oFilter->type == 'group' ) {
				$bFilterApplies = $this->filterGroup( $oFilter, $aDataSet );
				if( !$bFilterApplies ) {
					return false;
				}
			}
		}

		return parent::filterCallback( $aDataSet );
	}

	public function filterGroup( $oFilter, $aDataSet ) {
		if( !is_string( $oFilter->value ) ) {
			return true; //TODO: Warning
		}
		$sFieldValue = $aDataSet->groups;
		$sFilterValue = $oFilter->value;

		return in_array( $oFilter->value,  $aDataSet->groups );
	}

}