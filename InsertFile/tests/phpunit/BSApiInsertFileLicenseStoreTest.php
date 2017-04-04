<?php

/*
 * Test BSApiInsertFileLicenseStore API Endpoints
 */

/**
 * @group BlueSpiceDashboards
 * @group BlueSpice
 * @group API
 * @group Database
 * @group medium
 */

class BSApiInsertFileLicenseStoreTest extends ApiTestCase {
	/**
	 * Anything that needs to happen before your tests should go here.
	 */
	protected function setUp() {
		// Be sure to do call the parent setup and teardown functions.
		// This makes sure that all the various cleanup and restorations
		// happen as they should (including the restoration for setMwGlobals).
		parent::setUp();
		$this->doLogin();
	}

	public function testMakeData(){
		$data = $this->doApiRequest( [
			'action' => 'bs-insertfile-license-store'
		] );

		$this->assertArrayHasKey( 'total', $data[0] );
		$this->assertArrayHasKey( 'results', $data[0] );

		return $data;
	}
}