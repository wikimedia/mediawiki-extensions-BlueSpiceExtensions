<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceNamespaceManager
 */
class BSApiNamespaceStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 21;

	protected function getStoreSchema () {
		return [
			'id' => [
				'type' => 'integer'
			],
			'name' => [
				'type' => 'string'
			],
			'isSystemNS' => [
				'type' => 'boolean'
			],
			'isTalkNS' => [
				'type' => 'boolean'
			],
			'pageCount' => [
				'type' => 'integer'
			],
			'content' => [
				'type' => 'boolean'
			],
			'subpages' => [
				'type' => 'boolean'
			],
			'searched' => [
				'type' => 'boolean'
			],
		];
	}

	protected function createStoreFixtureData () {
		return 21;
	}

	protected function getModuleName () {
		return 'bs-namespace-store';
	}

	public function provideSingleFilterData() {
		return [
			'Filter by isSystemNS' => [ 'boolean', 'eq', 'isSystemNS', false, 3 ]
		];
	}

	public function provideMultipleFilterData () {
		return [
			'Filter by subpages and searched' => [
				[
					[
						'type' => 'boolean',
						'comparison' => 'eq',
						'field' => 'subpages',
						'value' => true
					],
					[
						'type' => 'boolean',
						'comparison' => 'eq',
						'field' => 'searched',
						'value' => true
					]
				],
				2
			]
		];
	}

}

