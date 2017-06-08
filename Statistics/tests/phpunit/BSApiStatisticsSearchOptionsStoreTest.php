<?php

/**
 * @group medium
 * @group api
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceStatistics
 */
class BSApiStatisticsSearchOptionsStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 4;

	protected function getStoreSchema () {
		return [
			'key' => [
				'type' => 'string'
			],
			'leaf' => [
				'type' => 'boolean'
			],
			'displaytitle' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData () {
		return 4;
	}

	protected function getModuleName() {
		return 'bs-statistics-search-options-store';
	}

	public function provideSingleFilterData () {
		return [
			'Filter by key' => [ 'string', 'eq', 'key', 'text', 1 ]
		];
	}

	public function provideMultipleFilterData () {
		return [
			'Filter by leaf and key' => [
				[
					[
						'type' => 'boolean',
						'comparison' => 'eq',
						'field' => 'leaf',
						'value' => true
					],
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'key',
						'value' => 'text'
					]
				],
				1
			]
		];
	}
}

