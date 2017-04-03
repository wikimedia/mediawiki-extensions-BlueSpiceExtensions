<?php

class BSApiPageAssignmentTasks extends BSApiTasksBase {

	protected $sTaskLogType = 'bs-pageassignments';

	protected $aTasks = array(
		'edit' => [
			'examples' => [
				[
					'pageId' => 152,
					'pageAssignments' => [
						'user/WikiSysop',
						'group/bot'
					]
				]
			],
			'params' => [
					'pageId' => [
						'desc' => 'ID of a page assignment is created for',
						'type' => 'integer',
						'required' => true
					],
					'pageAssignments' => [
						'desc' => 'Array of strings in form of "key/value", eg. "user/WikiSysop" or "group/sysop", can be empty',
						'type' => 'array',
						'required' => true
					]
			]
		],
		'getForPage' => [
			'examples' => [
				[
					'pageId' => 152
				]
			],
			'params' => [
				'pageId' => [
					'desc' => 'ID of a page to get assignments for',
					'type' => 'integer',
					'required' => true
				]
			]
		]
	);

	protected function getRequiredTaskPermissions() {
		return array(
			'edit' => array( 'pageassignments' ),
			'getForPage' => array( 'read' ),
		);
	}

	protected function task_edit( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();
		$aErrors = [];

		if( is_int( $oTaskData->pageId ) === false ) {
			$oResult->message = wfMessage( 'bs-pageassignments-api-error-no-page' )->plain();
			return $oResult;
		}

		$oTitle = Title::newFromID( $oTaskData->pageId );
		if( !$oTitle || !$oTitle->exists() ) {
			$oResult->message = wfMessage(
				'bs-pageassignments-api-error-no-page'
			)->plain();
			return $oResult;
		}

		$aCurrentAssignments = PageAssignments::getAssignments( $oTitle );
		$aCurrentAssigneeIds = array();
		foreach( $aCurrentAssignments as $oAsignee ) {
			$aCurrentAssigneeIds[] = $oAsignee->getId();
		}

		$aNewAssignmentIds = array_diff( $oTaskData->pageAssignments, $aCurrentAssigneeIds );
		$aUnchangedAssignmentIds = array_intersect( $oTaskData->pageAssignments, $aCurrentAssigneeIds );
		$aRemovedAssignmentIds = array_diff( $aCurrentAssigneeIds, $aUnchangedAssignmentIds );

		//Persist to Database
		//Step 1: remove all entries for this page
		$res = $this->getDB()->delete(
			'bs_pageassignments',
			array(
				'pa_page_id' => $oTaskData->pageId
			)
		);

		//Step 2: add new entries for this page
		foreach( $oTaskData->pageAssignments as $iIdx => $sAssigneeId ) {
			//$aAssigneeParts = 'user/WikiSysop' or 'group/bureaucrats'
			$aAssigneeParts = explode( '/', $sAssigneeId );
			$res = $this->getDB()->insert( 'bs_pageassignments', array(
				'pa_page_id' => $oTaskData->pageId,
				'pa_assignee_key' => $aAssigneeParts[1],
				'pa_assignee_type' => $aAssigneeParts[0],
				'pa_position' => $iIdx
			) );
			if( !$res ) {
				$aErrors[] =  wfMessage( 'bs-pageassignments-api-error-insert', $sAssigneeId, $oTitle->getPrefixedText() )->plain();
			}
		}

		if( empty( $aErrors ) ) {
			$oResult->success = true;
			$this->logAssignmentChange( $oTitle, $aNewAssignmentIds, $aRemovedAssignmentIds );
			$this->notifyAssignmentChange( $oTitle, $aNewAssignmentIds, $aRemovedAssignmentIds );
			$this->runUpdates();
		}
		else {
			$oResult->message = wfMessage( 'bs-pageassignments-api-error' )->plain();
			$oResult->errors = $aErrors;
		}

		return $oResult;
	}

	/**
	 * This is a convenience method. It could also be done by quering
	 * 'bs-pageassignment-store' with the right set of filters, but this one
	 * is much easier to access
	 * @param object $oTaskData
	 * @param array $aParams
	 * @return BSStandardAPIResponse
	 */
	protected function task_getForPage( $oTaskData, $aParams ) {
		$oResult = $this->makeStandardReturn();

		if( is_int( $oTaskData->pageId ) === false ) {
			$oResult->message = wfMessage( 'bs-pageassignments-api-error-no-page' )->plain();
			return $oResult;
		}

		$aAssignees = PageAssignments::getAssignments( Title::newFromID( $oTaskData->pageId ) );

		$aPayload = array();
		foreach( $aAssignees as $oAssignee ) {
			$aPayload[] = $oAssignee->toStdClass();
		}
		$oResult->success = true;
		$oResult->payload = $aPayload;

		return $oResult;
	}

	public function isWriteMode() {
		return true;
	}

	public function logAssignmentChange( $oTitle, $aNewAssignmentIds, $aRemovedAssignmentIds ) {
		foreach( $aNewAssignmentIds as $sNewAssignmentId ) {
			list( $sType, $sKey ) = explode( '/', $sNewAssignmentId );
			$oAssignee = BSAssignableBase::factory( $sType, $sKey );
			$this->logTaskAction( 'add-'.$sType, array(
				'4::editor' => $sKey
			), array(
				'target' => $oTitle
			));
		}
		foreach( $aRemovedAssignmentIds as $sRemovedAssignmentIds ) {
			list( $sType, $sKey ) = explode( '/', $sRemovedAssignmentIds );
			$oAssignee = BSAssignableBase::factory( $sType, $sKey );
			$this->logTaskAction( 'remove-'.$sType, array(
				'4::editor' => $sKey
			), array(
				'target' => $oTitle
			));
		}
	}

	public function notifyAssignmentChange($oTitle, $aNewAssignmentIds, $aRemovedAssignmentIds) {
		$aNewUsers = array();
		$aRemovedUsers = array();

		foreach( $aNewAssignmentIds as $aNewAssigneeId ) {
			list( $sType, $sKey ) = explode( '/', $aNewAssigneeId );
			$oAssignee = BSAssignableBase::factory( $sType, $sKey );
			$aNewUsers = array_merge(
				$aNewUsers,
				$oAssignee->getUserIds()
			);
		}

		foreach( $aRemovedAssignmentIds as $aRemovedAssigneeId ) {
			list( $sType, $sKey ) = explode( '/', $aRemovedAssigneeId );
			$oAssignee = BSAssignableBase::factory( $sType, $sKey );
			$aRemovedUsers = array_merge(
				$aRemovedUsers,
				$oAssignee->getUserIds()
			);
		}

		if( !empty( $aNewUsers ) ) {
			BSNotifications::notify(
				"notification-bs-pageassignments-assignment-change-add",
				$this->getUser(),
				$oTitle,
				array(
					'affected-users' => $aNewUsers
				)
			);
		}

		if( !empty( $aRemovedUsers ) ) {
			BSNotifications::notify(
				"notification-bs-pageassignments-assignment-change-remove",
				$this->getUser(),
				$oTitle,
				array(
					'affected-users' => $aRemovedUsers
				)
			);
		}
	}

	/**
	 * Creates a log entry for Special:Log, based on $this->sTaskLogType or
	 * $aOptions['type']. See https://www.mediawiki.org/wiki/Manual:Logging_to_Special:Log
	 * @param string $sAction
	 * @param array $aParams for the log entry
	 * @param array $aOptions <br/>
	 * 'performer' of type User<br/>
	 * 'target' of type Title<br/>
	 * 'timestamp' of type string<br/>
	 * 'relations of type array<br/>
	 * 'deleted' of type int<br/>
	 * 'type' of type string; to allow overriding of class default
	 * @param bool $bDoPublish
	 * @return int Id of the newly created log entry or -1 on error
	 */
	protected function logTaskAction( $sAction, $aParams, $aOptions = array(), $bDoPublish = false ) {
		$aOptions += array(
			'performer' => null,
			'target' => null,
			'timestamp' => null,
			'relations' => null,
			'comment' => null,
			'deleted' =>  null,
			'publish' => null,
			'type' => null //To allow overriding of class default
		);

		$oTarget = $aOptions['target'];
		if ( $oTarget === null ) {
			$oTarget = $this->makeDefaultLogTarget();
		}

		$oPerformer = $aOptions['performer'];
		if ( $oPerformer === null ) {
			$oPerformer = $this->getUser();
		}

		$sType = $this->sTaskLogType;
		if ( $aOptions['type'] !== null ) {
			$sType = $aOptions['type'];
		}

		if ( $sType === null ) { //Not set on class, not set as call option
			return -1;
		}

		$oLogger = new ManualLogEntry( $sType, $sAction );
		$oLogger->setPerformer( $oPerformer );
		$oLogger->setTarget( $oTarget );
		$oLogger->setParameters( $aParams );

		if ( $aOptions['timestamp'] !== null ) {
			$oLogger->setTimestamp( $aOptions['timestamp'] );
		}

		if ( $aOptions['relations'] !== null ) {
			$oLogger->setRelations( $aOptions['relations'] );
		}

		if ( $aOptions['comment'] !== null ) {
			$oLogger->setComment( $aOptions['comment'] );
		}

		if ( $aOptions['deleted'] !== null ) {
			$oLogger->setDeleted( $aOptions['deleted'] );
		}

		$iLogEntryId = $oLogger->insert();

		if ( $bDoPublish ) {
			$oLogger->publish();
		}

		return $iLogEntryId;
	}

}