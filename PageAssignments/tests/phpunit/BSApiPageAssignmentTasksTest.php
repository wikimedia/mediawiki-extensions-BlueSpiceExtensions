<?php

/**
 * @group medium
 * @group Database
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpicePageAssignments
 */
class BSApiPageAssignmentTasksTest extends BSApiTasksTestBase {

	protected $tablesUsed = [ 'bs_pageassignments' ];

	protected function setUp() {
		parent::setUp();
		new BSUserFixturesProvider();
		self::$userFixtures = new BSUserFixtures( $this );
	}

	protected function getModuleName () {
		return 'bs-pageassignment-tasks';
	}

	function getTokens () {
		return $this->getTokenList ( self::$users[ 'sysop' ] );
	}

	public function testEdit() {
		$oData = $this->executeTask(
			'edit',
			array(
				'pageId' => 1,
				'pageAssignments' => array(
					'user/John',
					'group/sysop'
				)
			)
		);

		$this->assertTrue( $oData->success, "API returned failure state" );

		$this->assertSelect(
			'bs_pageassignments',
			array( 'pa_assignee_key', 'pa_assignee_type' ),
			array( 'pa_page_id = 1' ),
			array(  array( 'John', 'user' ), array( 'sysop', 'group' ) ),
			"Assignment was not added to database"
		);

		$oData = $this->executeTask(
			'edit',
			array(
				'pageId' => 1,
				'pageAssignments' => array(
				)
			)
		);

		$this->assertTrue( $oData->success, "API returned failure state" );

		$this->assertSelect(
			'bs_pageassignments',
			array( 'pa_assignee_key', 'pa_assignee_type' ),
			array( 'pa_page_id = 1' ),
			array(),
			"Assignment was not removed from database"
		);
	}

	public function testGetForPage() {
		$oData = $this->executeTask(
			'edit',
			array(
				'pageId' => 1,
				'pageAssignments' => array(
					'user/John',
					'group/sysop'
				)
			)
		);

		$this->assertTrue( $oData->success, "API returned failure state" );

		$oData = $this->executeTask(
			'getForPage',
			array(
				'pageId' => 1
			)
		);

		$this->assertTrue( $oData->success, "API returned failure state" );
		$this->assertArrayHasKey( 0, $oData->payload, "No assignment was returned" );
		$this->assertArrayHasKey( 1, $oData->payload, "Second assignment was not returned" );

		$aAssignment = $oData->payload[0];

		$this->assertArrayHasKey( 'type', $aAssignment, "Assignment type is missing" );
		$this->assertEquals( 'user', $aAssignment['type'], "Assignment type is not 'user'" );
		$this->assertArrayHasKey( 'id', $aAssignment, "Assignment id is missing" );
		$this->assertEquals( 'user/John', $aAssignment['id'], "Assignment id is not 'user/John'" );
		$this->assertArrayHasKey( 'text', $aAssignment, "Assignment text is missing" );
		$this->assertEquals( 'John L.', $aAssignment['text'], "Assignment text is not 'John L.'" );
		$this->assertArrayHasKey( 'anchor', $aAssignment, "Assignment anchor is missing" );
	}
}