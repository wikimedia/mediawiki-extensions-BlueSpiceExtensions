<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceFlexiskin
 */
class BSApiFlexiskinStoreTest extends BSApiExtJSStoreTestBase{
	protected $iFixtureTotal = 3;

	protected $aFlexiskins = array(
		array( 'name' => 'UTTest1', 'desc' => 'Desc1'),
		array( 'name' => 'UTTest2', 'desc' => 'Desc2'),
		array( 'name' => 'UTTest3', 'desc' => 'Desc3'),
	);

	protected function getStoreSchema () {
		return [
			'flexiskin_id' => [
				'type' => 'string'
			],
			'flexiskin_name' => [
				'type' => 'string'
			],
			'flexiskin_desc' => [
				'type' => 'string'
			],
			'flexiskin_active' => [
				'type' => 'boolean'
			],
			'flexiskin_config' => [
				'type' => 'array'
			]
		];
	}

	protected function createStoreFixtureData () {
		return 4;
	}

	protected function setUp() {
		$aFSs = $this->aFlexiskins;
		foreach( $aFSs as $aFS ) {
			$sConfigFile = Flexiskin::generateConfigFile( (object) $aFS );
			$sId = $aFS['name'];
			BsFileSystemHelper::saveToDataDirectory( 'conf.json', $sConfigFile, "flexiskin" . DS . md5( $sId ) );
			BsFileSystemHelper::ensureDataDirectory( "flexiskin" . DS . md5( $sId ) . DS . "images" );
		}
		parent::setUp();
	}

	protected function tearDown() {
		$aFSs = $this->aFlexiskins;
		foreach( $aFSs as $aFS ) {
			$sId = $aFS['name'];
			$oStatus = BsFileSystemHelper::deleteFolder( "flexiskin" . DS . md5( $sId ) );
		}
		parent::tearDown();
	}

	protected function getModuleName () {
		return 'bs-flexiskin-store';
	}

	public function provideSingleFilterData () {
		return [
			'Filter by flexiskin_name' => [ 'string', 'ct', 'flexiskin_name', 'UT', 3 ]
		];
	}

	public function provideMultipleFilterData () {
		return [
			'Filter by flexiskin_name and flexiskin_desc' => [
				[
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'flexiskin_name',
						'value' => 'UT'
					],
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'flexiskin_desc',
						'value' => 'Desc1'
					]
				],
				1
			]
		];
	}
}
