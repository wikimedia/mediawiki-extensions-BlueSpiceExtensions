<?php

class BSAssignableEveryone extends BSAssignableBase {

	protected $sType = 'specialeveryone';
	protected $sId = 'specialeveryone/everyone';

	public function __construct( $sKey ) {
		$this->sText = wfMessage('bs-pageassignments-assignee-special-everyone-label')->plain();
		$this->sAnchor = Html::element(
			'span',
			[
			'class' => 'bs-pa-special-everyone'
			],
			wfMessage('bs-pageassignments-assignee-special-everyone-label')->plain()
		);
	}

	public static function getList($sQuery, $oContext) {
		$oAssignableEveryone = new BSAssignableEveryone();
		return [
			$oAssignableEveryone->toStdClass()
		];
	}

	public function getUserIds() {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'user', 'user_id' );
		$aUserIds = [];
		foreach( $res as $row ) {
			$aUserIds[] = $row->user_id;
		}

		return $aUserIds;
	}

	protected static function getQueryConds( $oUser ) {
		return array(
			'pa_assignee_type' => 'specialeveryone'
		);
	}

}
