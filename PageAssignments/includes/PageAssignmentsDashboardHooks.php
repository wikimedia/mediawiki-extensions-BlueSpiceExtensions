<?php

class PageAssignmentsDashboardHooks {

	/**
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public static function onBeforePageDisplay( $out, $skin ) {
		$oTitle = $out->getTitle();
		$aTitles = array(
			$oTitle->equals( SpecialPage::getTitleFor("AdminDashboard") ),
			$oTitle->equals( SpecialPage::getTitleFor("UserDashboard") ),
		);
		if( !in_array(true, $aTitles) ) {
			return true;
		}
		$out->addModules( 'ext.bluespice.pageassignments.portlet' );

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsUserDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return boolean always true to keep hook alive
	 */
	public static function onBSDashboardsUserDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = array(
			'type'  => 'BS.PageAssignments.portlets.PageAssignmentsPortlet',
			'config' => array(
				'title' => wfMessage(
					'bs-pageassignments-yourassignments'
				)->plain(),
			),
			'title' => wfMessage(
				'bs-pageassignments-yourassignments'
			)->plain(),
			'description' => wfMessage(
				'bs-pageassignments-yourassignmentsdesc'
			)->plain(),
		);

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsUserDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param boolean $bIsDefault default
	 * @return boolean always true to keep hook alive
	 */
	public static function onBSDashboardsUserDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = array(
			'type' => 'BS.PageAssignments.portlets.PageAssignmentsPortlet',
			'config' => array(
				'title' => wfMessage(
					'bs-pageassignments-yourassignments'
				)->plain(),
			),
		);

		return true;
	}
}