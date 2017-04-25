<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceUserManager
 */
class BSApiChangeableGroupStoreTest extends BSApiExtJSStoreTestBase {

	protected $iFixtureTotal = 6;

	protected function getStoreSchema() {
		return [
			'group_name' => [
				'type' => 'string'
			],
			'additional_group' => [
				'type' => 'boolean'
			],
			'displayname' => [
				'type' => 'string'
			]
		];
	}

	protected function createStoreFixtureData() {
		global $wgAddGroups, $wgRemoveGroups, $wgGroupsAddToSelf, $wgGroupsRemoveFromSelf;
		$wgAddGroups['sysop'] = true;
		$wgRemoveGroups['sysop'] = true;
		$wgGroupsAddToSelf['sysop'] = true;
		$wgGroupsRemoveFromSelf['sysop'] = true;

		return 6;
	}

	public function provideSingleFilterData() {
		return [
			'Filter by group_name' => [ 'string', 'ct', 'group_name', 'auto', 1 ],
			'Filter by additional_group' => ['boolean', 'eq', 'additional_group', false, 6]
		];
	}

	public function provideMultipleFilterData() {
		return [
			'Filter by group_name and displayname' => [
				[
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'group_name',
						'value' => 'bureaucrat'
					],
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'displayname',
						'value' => 'Bur'
					]
				],
				1
			]
		];
	}

	protected function getModuleName() {
		return 'bs-usermanager-group-store';
	}
}
