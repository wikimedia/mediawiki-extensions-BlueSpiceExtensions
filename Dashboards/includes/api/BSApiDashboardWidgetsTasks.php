<?php

class BSApiDashboardWidgetsTasks extends BSApiTasksBase {

	protected $aTasks = array(
		'wikipage' => [
			'examples' => [
				[
					'wikiArticle' => 'Main_page'
				]
			],
			'params' => [
				'wikiArticle' => [
					'desc' => 'Valid title name',
					'type' => 'string',
					'required' => true
				]
			]
		]
	);

	protected function getRequiredTaskPermissions() {
		return array(
			'wikipage' => array( 'read' )
		);
	}

	public function task_wikipage(  $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		if ( !isset( $oTaskData->wikiArticle ) ) {
			$oResponse->success = true;
			$oResponse->payload = array( "html" => wfMessage( 'compare-invalid-title' )->plain() );
			return $oResponse;
		}

		$oTitle = Title::newFromText( $oTaskData->wikiArticle );
		if ( !$oTitle ) {
			$oResponse->success = false;
			$oResponse->payload = array( "html" => wfMessage( 'compare-invalid-title' )->plain() );
			return $oResponse;
		}

		if ( !$oTitle->userCan( 'read' ) ) {
			$oResponse->success = false;
			$oResponse->payload = array( "html" => wfMessage( 'bs-permissionerror' )->plain() );
			return $oResponse;
		}
		$oWikiPage = WikiPage::factory( $oTitle );
		if ( !$oWikiPage->getContent() ) {
			$oResponse->success = false;
			$oResponse->payload = array( "html" => wfMessage( 'compare-invalid-title' )->plain() );
			return $oResponse;
		}

		$sHTML = $oWikiPage->getContent()->getParserOutput( $oTitle )->getText();

		$oResponse->success = true;
		$oResponse->payload = array( "html" => $sHTML );
		return $oResponse;
	}

	public function needsToken() {
		return false;
	}
	/**
	 * Returns an array of allowed parameters
	 * @return array
	 */
	protected function getAllowedParams() {
		$paramList = parent::getAllowedParams();

		return array_merge(
			$paramList,
			array(
				'_dc' => array(
					ApiBase::PARAM_TYPE => 'string',
					ApiBase::PARAM_REQUIRED => false,
					//TODO: Description
					10 /*ApiBase::PARAM_HELP_MSG*/ => 'apihelp-bs-dashboard-task-param-dc',
				)
			)
		);
	}
}