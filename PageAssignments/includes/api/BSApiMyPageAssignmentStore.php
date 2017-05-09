<?php

class BSApiMyPageAssignmentStore extends BSApiExtJSStoreBase {

	protected function makeData($sQuery = '') {
		$aAssignments = BSAssignableBase::getForUser( $this->getUser() );

		$aResult = [];
		foreach( $aAssignments as $iPageId => $aAssignees ) {
			$aAssignedBy = [];
			foreach( $aAssignees as $oAssignee ) {
				$oAssigneeStdClass = $oAssignee->toStdClass();
				$aAssignedBy[$oAssigneeStdClass->id] = $oAssigneeStdClass;
			}

			$oTitle = Title::newFromID( $iPageId );
			$oDataSet = (object)array(
				'page_id' => $oTitle->getArticleID(),
				'page_prefixedtext' => $oTitle->getPrefixedText(),
				'page_link' => Linker::link( $oTitle ),
				'assigned_by' => $aAssignedBy
			);
			$aResult[] = $oDataSet;
		}

		return $aResult;
	}

	public function filterString($oFilter, $aDataSet) {
		if( $oFilter->field !== 'assigned_by') {
			return parent::filterString($oFilter, $aDataSet);
		}

		$sFieldValue = '';
		foreach( $aDataSet->assigned_by as $oAsignee ) {
			if( $oAsignee->type == 'user' ) {
				$sFieldValue .= wfMessage( 'bs-pageassignments-directly-assigned' )->plain();
			}
			else {
				$sFieldValue .= $oAsignee->text;
			}
		}

		return BsStringHelper::filter( $oFilter->comparison, $sFieldValue, $oFilter->value );
	}

}