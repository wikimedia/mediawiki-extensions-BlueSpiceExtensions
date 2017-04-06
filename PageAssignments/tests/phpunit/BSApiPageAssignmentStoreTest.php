<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 */
class BSApiPageAssignmentStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 24;

	protected $aPages = array(
		'UT_Test' => array( 'key' => 'sysop', 'type' => 'group' ),
		'UT_Test2' => array( 'key' => 'bureaucrat', 'type' => 'group' ),
		'UT_Test3' => array( 'key' => 'Apitestsysop', 'type' => 'user' ),
		'UT_Test4' => array( 'key' => 'UTSysop', 'type' => 'user' ),
	);

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
		return 24;
	}

	protected function getModuleName () {
		return 'bs-pageassignment-store';
	}

	public function provideSingleFilterData () {
		//8 is expected because of 4 pages inserted and their Talk pages
		return [
			'Filter by page_prefixedtext' => [ 'string', 'ct', 'page_prefixedtext', "UT Test" , 8 ]
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
						'value' => 'UT Test'
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'assignments',
						'value' => 'group/sysop'
					]
				],
				1
			]
		];
	}
}
