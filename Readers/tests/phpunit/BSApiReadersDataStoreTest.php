<?php

/**
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceReaders
 */
class BSApiReadersDataStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 4;
	protected $sQuery = 1; // Thats the ID of UTSysop

	protected function getStoreSchema () {
		return [
			'pv_page' => [
				'type' => 'string'
			],
			'pv_page_link' => [
				'type' => 'string'
			],
			'pv_page_title' => [
				'type' => 'string'
			],
			'pv_ts' => [
				'type' => 'date'
			],
			'pv_date' => [
				'type' => 'string'
			],
			'pv_readers_link' => [
				'type' => 'string'
			]
		];
	}

	protected $tablesUsed = [ 'bs_readers', 'page', 'user', 'user_groups', 'user_properties' ];

	protected function setUp() {
		parent::setUp();

		/**
		 * This need to be done here and for each and every test, because
		 * tables defined in $tablesUsed are being reset after a test.
		 */
		$oPageFixtures = new BSPageFixturesProvider();
		$aFixtures = $oPageFixtures->getFixtureData();
		foreach( $aFixtures as $aFixture ) {
			$this->insertPage( $aFixture[0], $aFixture[1] );
		}

		new BSReadersFixtures( $this->db );
	}

	protected function createStoreFixtureData() {
		return;
	}

	protected function getModuleName () {
		return 'bs-readers-data-store';
	}

	public function providePagingData() {
		return parent::providePagingData();
	}

	public function provideSingleFilterData() {
		return [
			'Filter by pv_page_title equals' => [ 'string', 'eq', 'pv_page_title', 'Test', 1 ],
			'Filter by pv_page_title contains' => [ 'string', 'ct', 'pv_page_title', 'est', 2 ]
		];
	}

	public function provideMultipleFilterData () {
		return [
			'Filter by pv_page_title contains and pv_ts equals' => [
				[
					[
						'type' => 'date',
						'comparison' => 'gt',
						'field' => 'pv_ts',
						'value' => '20170102000000' //That's "Help:Test"
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'pv_page_title',
						'value' => 'est'
					]
				],
				1
			]
		];
	}

}