<?php

class PageAssignmentsBookmakerHooks {

	/**
	 * Adds dependencies to SpecialBookshelfBookManager
	 * @param SpecialBookshelfBookManager $oSender
	 * @param OutputPage $oOutput
	 * @param stdClass $oConfig
	 * @return boolean
	 */
	public static function onBSBookshelfBookManager( $oSender, $oOutput, $oConfig ) {
		$oConfig->dependencies[] = 'ext.bluespice.pageassignments.bookshelfPlugin';
		return true;
	}

	/**
	 * Adds information about assignments to PDF export
	 * @param Title $oTitle
	 * @param DOMDocument $oPageDOM
	 * @param array $aParams
	 * @param DOMXPath $oDOMXPath
	 * @param array $aMeta
	 * @return boolean
	 */
	public static function onBSUEModulePDFcollectMetaData( $oTitle, $oPageDOM, &$aParams, $oDOMXPath, &$aMeta ) {
		$aMeta['assigned_users'] = '';
		$aMeta['assigned_groups'] = '';

		$aAssignedUserNames = array();
		$aAssignedGroupNames = array();

		$aAssigments = PageAssignments::getAssignments( $oTitle );
		foreach ( $aAssigments as $oAsignee ) {
			if( $oAsignee instanceof BSAssignableUser ) {
				$aAssignedUserNames[] = $oAsignee->getText();
			}
			if( $oAsignee instanceof BSAssignableGroup ) {
				$aAssignedGroupNames[] = $oAsignee->getText();
			}
		}
		if( !empty( $aAssignedUserNames ) ) {
			$aMeta['assigned_users'] = implode( ', ', $aAssignedUserNames );
		}
		if( !empty( $aAssignedGroupNames ) ) {
			$aMeta['assigned_groups'] = implode( ', ', $aAssignedGroupNames );
		}

		return true;
	}

	/**
	 * Adds information about assignments to the Bookshelf BookManager grid
	 * @param Title $oBookTitle
	 * @param stdClass $oBookRow
	 * @return boolean
	 */
	public static function onBSBookshelfManagerGetBookDataRow( $oBookTitle, $oBookRow ) {
		$oBookRow->assignments = array();
		$aTexts = array();
		$aAssigments = PageAssignments::getAssignments( $oBookTitle );
		foreach ( $aAssigments as $oAsignee ) {
			$oBookRow->assignments[] = $oAsignee->toStdClass();
			$aTexts[] = $oAsignee->getText();
		}
		$oBookRow->flat_assignments = implode( '', $aTexts );
		return true;
	}
}