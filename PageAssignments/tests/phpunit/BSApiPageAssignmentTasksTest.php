<?php

/**
 * @group medium
 * @group Database
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpicePageAssignments
 */
class BSApiPageAssignmentTasksTest extends BSApiTasksTestBase {
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
					'user/UTSysop',
					'group/sysop'
				)
			)
		);

		$this->assertTrue( $oData->success );

		$this->assertSelect(
			'bs_pageassignments',
			array( 'pa_assignee_key', 'pa_assignee_type' ),
			array( 'pa_page_id = 1' ),
			array(  array( 'UTSysop', 'user' ), array( 'sysop', 'group' ) )
		);

		$oData = $this->executeTask(
			'edit',
			array(
				'pageId' => 1,
				'pageAssignments' => array(
				)
			)
		);

		$this->assertTrue( $oData->success );

		$this->assertSelect(
			'bs_pageassignments',
			array( 'pa_assignee_key', 'pa_assignee_type' ),
			array( 'pa_page_id = 1' ),
			array()
		);
	}

	public function testGetForPage() {
		$oData = $this->executeTask(
			'edit',
			array(
				'pageId' => 1,
				'pageAssignments' => array(
					'user/UTSysop',
					'group/sysop'
				)
			)
		);

		$this->assertTrue( $oData->success );

		$oData = $this->executeTask(
			'getForPage',
			array(
				'pageId' => 1
			)
		);

		$this->assertTrue( $oData->success );
		$this->assertArrayHasKey( 0, $oData->payload );
		$this->assertArrayHasKey( 1, $oData->payload );

		$aAssignment = $oData->payload[0];
		$this->assertArrayHasKey( 'type', $aAssignment );
		$this->assertEquals( 'user', $aAssignment['type'] );
		$this->assertArrayHasKey( 'id', $aAssignment );
		$this->assertEquals( 'user/UTSysop', $aAssignment['id'] );
		$this->assertArrayHasKey( 'text', $aAssignment );
		$this->assertEquals( 'UTSysop', $aAssignment['text'] );
		$this->assertArrayHasKey( 'anchor', $aAssignment );
	}
}