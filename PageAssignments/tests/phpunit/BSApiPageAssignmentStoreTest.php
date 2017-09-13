<?php

/**
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpicePageAssignments
 */
class BSApiPageAssignmentStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 7;

	protected $aPages = array(
		'UT_PageAssignmentStore_Test' => array( 'key' => 'sysop', 'type' => 'group' ),
		'UT_PageAssignmentStore_Test2' => array( 'key' => 'bureaucrat', 'type' => 'group' ),
		'UT_PageAssignmentStore_Test3' => array( 'key' => 'Apitestsysop', 'type' => 'user' ),
		'UT_PageAssignmentStore_Test4' => array( 'key' => 'UTSysop', 'type' => 'user' ),
		'UT_PageAssignmentStore_Test5' => array( 'key' => 'PASysop', 'type' => 'group' ),
		'UT_PageAssignmentStore_Test6' => array( 'key' => 'sysop', 'type' => 'group' )
	);

	protected function skipAssertTotal() {
		return true;
	}

	protected function getStoreSchema () {
		return [
			'page_id' => [
				'type' => 'integer'
			],
			'page_prefixedtext' => [
				'type' => 'string'
			],
			'assignments' => [
				'type' => 'array'
			]
		];
	}

	protected function createStoreFixtureData () {
		$dbw = $this->db;

		$iCount = 1;
		foreach( $this->aPages as $sPage => $aData ) {
			$aRes = $this->insertPage( $sPage );
			$iPageId = $aRes['id'];
			$this->assertGreaterThan( 0, $iPageId );
			$dbw->insert( 'bs_pageassignments',
				array( 'pa_page_id' => $iPageId, 'pa_assignee_key' => $aData['key'], 'pa_assignee_type' => $aData['type'], 'pa_position' => $iCount )
			);

			$iCount++;
		}
		return true;
	}

	protected function getModuleName () {
		return 'bs-pageassignment-store';
	}

	public function provideSingleFilterData () {
		return [
			'Filter by page_prefixedtext' => [ 'string', 'ct', 'page_prefixedtext', "UT PageAssignmentStore Test" , 4 ],
			'Filter by page_prefixedtext' => [ 'string', 'ct', 'page_prefixedtext', "UT PageAssignmentStore Test3" , 1 ]
		];
	}

	public function provideMultipleFilterData () {
		return [
			'Filter by page_prefixedtext and assignment' => [
				[
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'page_prefixedtext',
						'value' => 'UT PageAssignmentStore'
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'assignments',
						'value' => 'PASysop'
					]
				],
				1
			]
		];
	}
}
