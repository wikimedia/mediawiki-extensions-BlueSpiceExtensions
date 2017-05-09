<?php

class PageAssignmentsStateBarHooks {

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeTopViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aTopViews
	 * @return boolean Always true to keep hook running
	 */
	public static function onStateBarBeforeTopViewAdd( $oStateBar, &$aTopViews, $oUser, $oTitle ) {
		if ( $oTitle->exists() === false ) {
			return true;
		}

		global $wgScriptPath;

		$aAssignments = PageAssignments::getAssignments( $oTitle );
		if( count( $aAssignments ) === 0 ) {
			return true;
		}

		$oTopView = new ViewStateBarTopElement();
		$oTopView->setKey( 'PageAssignments-Top' );
		$oTopView->setIconSrc( $wgScriptPath . '/extensions/BlueSpiceExtensions/PageAssignments/resources/images/bs-statebar-assignment.png' );
		$oTopView->setIconAlt( wfMessage( 'bs-pageassignments-statebartop-icon-alt' )->plain() );
		$oTopView->setText( wfMessage('bs-pageassignments-statebar-label')->escaped() );
		$oTopView->setTextLink( '#' );
		$oTopView->setIconTogglesBody( true );

		$aTopViews['statebartoppageassignments'] = $oTopView;
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeBodyViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aBodyViews
	 * @return boolean Always true to keep hook running
	 */
	public static function onStateBarBeforeBodyViewAdd( $oStateBar, &$aBodyViews, $oUser, $oTitle ) {
		if ( $oTitle->exists() == false ) {
			return true;
		}

		//MW BeforeInitialize hook is not present in ajax calls, so apply
		//possible permissions for responsible editors in this context
		#$this->applyTempPermissionsForRespEditor( $oTitle, $oUser );

		$aAssignments = PageAssignments::getAssignments( $oTitle );
		if( count( $aAssignments ) < 1 ) {
			return true;
		}
		$oView = new ViewPAStateBarBodyElement();
		$oView->setKey( 'PageAssignments-Body' );
		$oView->setHeading( wfMessage( 'bs-pageassignments-statebar-label' )->escaped() );
		$oView->addCompleteDataset( $aAssignments );

		$aBodyViews['statebarbodypageassignments'] = $oView;
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortTopVars'
	 * @param array $aSortTopVars
	 * @return boolean Always true to keep hook running
	 */
	public static function onStatebarAddSortTopVars( &$aSortTopVars ) {
		$aSortTopVars['statebartoppageassignments'] = wfMessage( 'bs-pageassignments-statebartoppageassignments' )->plain();
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortBodyVars'
	 * @param array $aSortBodyVars
	 * @return boolean Always true to keep hook running
	 */
	public static function onStatebarAddSortBodyVars( &$aSortBodyVars ) {
		$aSortBodyVars['statebarbodypageassignments'] = wfMessage( 'bs-pageassignments-statebarbodypageassignments' )->plain();
		return true;
	}

}