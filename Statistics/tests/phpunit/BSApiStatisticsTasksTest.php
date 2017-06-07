<?php

/**
 * @group medium
 * @group api
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceStatistics
 */
class BSApiStatisticsTasksTest extends BSApiTasksTestBase {

	protected function getModuleName () {
		return 'bs-statistics-tasks';
	}

	public function testGetData() {
		$oData = $this->executeTask(
			'getData',
			array(
				'diagram' => 'BsDiagramNumberOfUsers',
				'grain' => 'Y',
				'from' => date( '01.01.Y' ),
				'mode' => 'list',
				'to' => date( '31.12.Y' )
			)
		);

		$this->assertTrue( $oData->success, "API reported failure" );

		$this->assertArrayHasKey( 'label', $oData->payload, "Label key is missing" );
		$this->assertArrayHasKey( 'data', $oData->payload, "Data key is missing" );
		$aPayloadData = $oData->payload['data'];

		$this->assertArrayHasKey( 'list', $aPayloadData, "List key is missing from array 'data'" );
		$this->assertArrayHasKey( 'fields', $aPayloadData, "Fields key is missing from array 'data'" );
		$this->assertArrayHasKey( 'columns', $aPayloadData, "Columns key is missing from array 'data'" );
	}
}
