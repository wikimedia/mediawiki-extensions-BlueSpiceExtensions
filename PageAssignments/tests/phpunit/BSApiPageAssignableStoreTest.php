<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpicePageAssignments
 */
class BSApiPageAssignableStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 9;

	protected function getStoreSchema () {
		return [
			'type' => [
				'type' => 'string'
			],
			'id' => [
				'type' => 'string'
			],
			'text' => [
				'type' => 'string'
			],
			'anchor' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData () {
		return 9;
	}

	protected function getModuleName () {
		return 'bs-pageassignable-store';
	}

	public function provideSingleFilterData () {
		return [
			'Filter by type' => [ 'string', 'eq', 'type', 'group', 6 ]
		];
	}

	public function provideMultipleFilterData () {
		return [
			'Filter by type and id' => [
				[
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'type',
						'value' => 'group'
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'id',
						'value' => 'bot'
					]
				],
				1
			]
		];
	}
}