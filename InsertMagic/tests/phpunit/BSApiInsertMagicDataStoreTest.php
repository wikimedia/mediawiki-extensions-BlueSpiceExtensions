<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceInsertMagic
 */
class BSApiInsertMagicDataStoreTest extends BSApiExtJSStoreTestBase {

	protected $iFixtureTotal = 86;

	protected function skipAssertTotal() {
		return true;
	}

	protected function getStoreSchema() {
		return [
			'id' => [
				'type' => 'string'
			],
			'type' => [
				'type' => 'string'
			],
			'name' => [
				'type' => 'string'
			],
			'desc' => [
				'type' => 'string'
			],
			'code' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData() {
		return;
	}

	protected function getModuleName() {
		return 'bs-insertmagic-data-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by name' => [ 'string', 'eq', 'name', 'NOTOC', 1 ]
		];
	}

	public function provideMultipleFilterData() {
		return [
			'Filter by name and type' => [
				[
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'type',
						'value' => 'switch'
					],
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'name',
						'value' => 'NOTOC'
					]
				],
				1
			]
		];
	}
}
