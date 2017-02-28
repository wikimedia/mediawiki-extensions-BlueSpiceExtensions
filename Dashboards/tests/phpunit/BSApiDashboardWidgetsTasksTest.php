<?php

/*
 * Test BlueSpiceDashboards API Endpoints
 */

/**
 * @group BlueSpiceDashboards
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 */

class BSApiDashboardWidgetsTasksTest extends BSApiTasksTestBase {

	protected $tmpPageName = "Testpage";
	protected $tmpPageContent = "Contentv4785zbn8c7w35zo";
	/**
	 * Anything that needs to happen before your tests should go here.
	 */
	protected function setUp() {
		// Be sure to do call the parent setup and teardown functions.
		// This makes sure that all the various cleanup and restorations
		// happen as they should (including the restoration for setMwGlobals).
		parent::setUp();
		$this->doLogin();
		$this->insertPage( $this->tmpPageName, $this->tmpPageContent );
	}

	/*
	 *
	 */
	public function testWikipage() {

		$data = $this->executeTask(
			'wikipage',
			[
				'wikiArticle' => $this->tmpPageName
			]
		);

		$this->assertEquals( true, $data->success );
		$this->assertContains( $this->tmpPageContent, $data->payload["html"] );

		return $data;
	}

	protected function getModuleName() {
		return 'bs-dashboards-widgets-tasks';
	}

}