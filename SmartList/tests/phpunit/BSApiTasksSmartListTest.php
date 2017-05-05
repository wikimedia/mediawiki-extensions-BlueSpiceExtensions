<?php

/*
 * Test BlueSpice SmartList API Endpoints
 */

/**
 * @group BlueSpiceSmartList
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 */
class BSApiTasksSmartListTest extends BSApiTasksTestBase {

	protected function getModuleName() {
		return "bs-smartlist-tasks";
	}

	function testGetMostActivePortlet() {
		$data = $this->executeTask(
		  'getMostActivePortlet', [
			'portletConfig' => [ json_encode( [ ] ) ]
		  ]
		);

		$this->assertEquals( true, $data->success );

		return $data;
	}

	function testGetMostEditedPages() {
		$data = $this->executeTask(
		  'getMostEditedPages', [
			'portletConfig' => [ json_encode( [ ] ) ]
		  ]
		);

		$this->assertEquals( true, $data->success );

		return $data;
	}

	function testGetMostVisitedPages() {
		$data = $this->executeTask(
		  'getMostVisitedPages', [
			'portletConfig' => [ json_encode( [ ] ) ]
		  ]
		);

		$this->assertEquals( true, $data->success );

		return $data;
	}

	function testGetYourEditsPortlet() {
		$data = $this->executeTask(
		  'getYourEditsPortlet', [
			'portletConfig' => [ json_encode( [ ] ) ]
		  ]
		);

		$this->assertEquals( true, $data->success );

		return $data;
	}

}
