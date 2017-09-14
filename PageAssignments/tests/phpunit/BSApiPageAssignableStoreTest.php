<?php

/**
 * @group medium
 * @group API
 * @group Database
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpicePageAssignments
 */
class BSApiPageAssignableStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 9;

	protected function skipAssertTotal() {
		return true;
	}

	protected function setUp() {
		parent::setUp();
		new BSUserFixturesProvider();
	}

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
		self::$userFixtures = new BSUserFixtures( $this );
		return true;
	}

	protected function getModuleName () {
		return 'bs-pageassignable-store';
	}

	public function provideSingleFilterData () {
		return [
			'Filter by id' => [ 'string', 'ct', 'id', 'John', 1 ],
			'Filter by text' => [ 'string', 'eq', 'text', 'Ringo S.', 1 ]
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

	public function provideKeyItemData() {
		return[
			'Test user John: text' => [ "text", "John L." ],
			'Test user Paul: text' => [ "text", "Paul M." ]
		];
	}
}