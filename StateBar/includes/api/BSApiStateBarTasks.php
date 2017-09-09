<?php

class BSApiStateBarTasks extends BSApiTasksBase {

	protected $aTasks = array(
		'collectBodyViews' => [
			'examples' => [],
			'params' => []
		]
	);

	protected function getRequiredTaskPermissions() {
		return [ 'collectBodyViews' => [ 'read' ] ];
	}

	public function task_collectBodyViews( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		$oUser = $this->getUser();

		$iArticleID = $this->getContext()->getWikiPage()->getId();

		if( $iArticleID === 0 ) {
			$oResponse->message = wfMessage( 'bs-statebar-ajax-nobodyviews' )->plain();
			return $oResponse;
		}

		$oStateBar = BsExtensionManager::getExtension( 'StateBar' );
		$oStateBar->registerSortVars();

		$oTitle = $oStateBar->checkContext(
			Title::newFromID( $iArticleID ),
			true //because you already have the possible redirected title!
				 //also prevents from getting wrong data in redirect redirect
		);
		if( is_null( $oTitle ) ) {
			$oResponse->message = wfMessage( 'bs-statebar-ajax-nobodyviews' )->plain();
			return $oResponse;
		}

		$aBodyViews = array();
		Hooks::run( 'BSStateBarBeforeBodyViewAdd', array( $oStateBar, &$aBodyViews, $oUser, $oTitle ) );
		if( empty( $aBodyViews ) ) {
			$oResponse->success = true;
			$oResponse->message = wfMessage( 'bs-statebar-ajax-nobodyviews' )->plain();
			return $oResponse;
		}

		$aSortBodyVars = BsConfig::get('MW::StateBar::SortBodyVars');
		if( !empty( $aSortBodyVars ) ) {
			$aBodyViews = $oStateBar->reorderViews( $aBodyViews, $aSortBodyVars );
		}

		//execute all views to an array with numeric index
		$aExecutedBodyViews = array();
		foreach( $aBodyViews as $oView ) $aExecutedBodyViews[] = $oView->execute();

		$oResponse->payload['views'] = $aExecutedBodyViews;
		$oResponse->success = true;
		return $oResponse;

	}

}
