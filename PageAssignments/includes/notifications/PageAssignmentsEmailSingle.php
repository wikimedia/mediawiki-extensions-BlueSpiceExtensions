<?php

class PageAssignmentsEmailSingle extends BsEchoEmailSingle {
	/**
	 * Adds information about "why" the user receives this notification
	 * @return string
	 */
	public function buildSummary() {
		$sSummary = parent::buildSummary();

		$aAssignments = $this->event->getExtraParam( 'assignment-sources', array() );
		$bIsDirectlyAssigned = false;
		$aAssignedByGroups = array();
		if( isset( $aAssignments[$this->user->getId()] ) ) {
			$aUserAssignments = $aAssignments[$this->user->getId()];
			foreach( $aUserAssignments as $oAssignable ) {
				if( $oAssignable instanceof BSAssignableUser ) {
					$bIsDirectlyAssigned = true;
				}
				if( $oAssignable instanceof BSAssignableGroup ) {
					$aAssignedByGroups[] = $oAssignable->getText();
				}
			}
		}

		if( $bIsDirectlyAssigned || !empty( $aAssignedByGroups ) ) {
			$aReasons = array(
				"\n",
				wfMessage( 'bs-pageassignments-notification-reason' )->plain()
			);
			if( $bIsDirectlyAssigned ) {
				$aReasons[] = '* ' . wfMessage( 'bs-pageassignments-notification-reason-user' )->plain();
			}
			if( !empty( $aAssignedByGroups ) ) {
				$aReasons[] = '* ' . wfMessage(
					'bs-pageassignments-notification-reason-group',
					count( $aAssignedByGroups ),
					implode( ', ', $aAssignedByGroups )
				)->parse();
			}

			$sSummary .= implode( "\n", $aReasons );
		}

		return $sSummary;
	}
}