<?php

class BSApiMyPageAssignmentStore extends BSApiExtJSStoreBase {

	protected function makeData($sQuery = '') {
		$aResult = array();

		//Step 1: get all assignments caused by group membership
		//TODO: encapsulate and abstract
		$aGroups = $this->getUser()->getEffectiveGroups();
		$res = $this->getDB()->select(
			'bs_pageassignments',
			'*',
			array(
				'pa_assignee_type' => 'group',
				'pa_assignee_key' => $aGroups
			)
		);

		foreach( $res as $row ) {
			$this->appendResult( $aResult, $row );
		}

		//Step 2: get all assignments caused by direct user assignment
		//TODO: encapsulate and abstract
		$res = $this->getDB()->select(
			'bs_pageassignments',
			'*',
			array(
				'pa_assignee_type' => 'user',
				'pa_assignee_key' => $this->getUser()->getName()
			)
		);
		foreach( $res as $row ) {
			$this->appendResult( $aResult, $row );
		}

		return $aResult;
	}

	public function appendResult( &$aResult, $row ) {
		$oAssignee = BSAssignableBase::factory( $row->pa_assignee_type, $row->pa_assignee_key )->toStdClass();

		if( isset( $aResult[$row->pa_page_id] ) ) {
			$aResult[$row->pa_page_id]->assigned_by[$oAssignee->id] = $oAssignee;
		}
		else {
			$oTitle = Title::newFromID( $row->pa_page_id );
			$oDataSet = (object)array(
				'page_id' => $oTitle->getArticleID(),
				'page_prefixedtext' => $oTitle->getPrefixedText(),
				'page_link' => Linker::link( $oTitle ),
				'assigned_by' => array(
					$oAssignee->id => $oAssignee
				)
			);
			$aResult[$row->pa_page_id] = $oDataSet;
		}
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