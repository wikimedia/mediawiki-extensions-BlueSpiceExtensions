<?php

class BSApiPageAssignmentStore extends BSApiExtJSStoreBase {

	protected function makeData($sQuery = '') {
		$aResult = array();

		$aPageAssignments = $this->getPageAssignments();

		$res = $this->getDB()->select( 'page', '*' );
		foreach( $res as $row ) {
			$oTitle = Title::newFromRow( $row );
			$oDataSet = (object)array(
				'page_id' => $oTitle->getArticleID(),
				'page_prefixedtext' => $oTitle->getPrefixedText(),
				'assignments' => array()
			);

			//This is for better performance. For some reason PHP is very slow then accessing
			// $aPageAssignments[$oTitle->getArticleID()] directly
			if( isset( $aPageAssignments[$oTitle->getArticleID()] ) ) {
				$oDataSet->assignments = $aPageAssignments[$oTitle->getArticleID()];
			}

			$aResult[$oTitle->getArticleID()] = $oDataSet;
		}

		return $aResult;
	}

	public function filterString($oFilter, $aDataSet) {
		if( $oFilter->field !== 'assignments') {
			return parent::filterString($oFilter, $aDataSet);
		}

		$sFieldValue = '';
		foreach( $aDataSet->assignments as $oAsignee ) {
			$sFieldValue .= $oAsignee->text;
		}

		if( empty( $sFieldValue ) ) {
			$sFieldValue = wfMessage( 'bs-pageassignments-no-assignments' )->plain();
		}

		return BsStringHelper::filter( $oFilter->comparison, $sFieldValue, $oFilter->value );
	}

	public function getPageAssignments() {
		$aPageAssignments= array();

		$res = $this->getDB()->select( 'bs_pageassignments', '*' );
		foreach( $res as $row ) {
			if( !isset( $aPageAssignments[$row->pa_page_id] ) ) {
				$aPageAssignments[$row->pa_page_id] = array();
			}
			$aPageAssignments[$row->pa_page_id][] =
				BSAssignableBase::factory( $row->pa_assignee_type, $row->pa_assignee_key )->toStdClass();
		}

		return $aPageAssignments;
	}

}