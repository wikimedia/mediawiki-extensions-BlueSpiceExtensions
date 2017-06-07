<?php

/**
 * @group medium
 * @group api
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceStatistics
 */
class BSApiStatisticsAvailableDiagramsStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 6;

	protected function getStoreSchema () {
		return [
			'key' => [
				'type' => 'string'
			],
			'displaytitle' => [
				'type' => 'string'
			],
			'listable' => [
				'type' => 'boolean'
			],
			'filters' => [
				'type' => 'array'
			]
		];
	}

	protected function createStoreFixtureData () {
		return 6;
	}

	protected function getModuleName() {
		return 'bs-statistics-available-diagrams-store';
	}

	public function provideSingleFilterData () {
		return [
			'Filter by listable' => [ 'boolean', 'eq', 'listable', true, 4 ]
		];
	}

	public function provideMultipleFilterData () {
		return [
			'Filter by listable and key' => [
				[
					[
						'type' => 'boolean',
						'comparison' => 'eq',
						'field' => 'listable',
						'value' => true
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'key',
						'value' => 'Searches'
					]
				],
				1
			]
		];
	}

}
