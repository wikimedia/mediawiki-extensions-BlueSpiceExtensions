<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceFlexiskin
 */
class BSApiFlexiskinUploadStoreTest extends BSApiExtJSStoreTestBase {
	protected $iFixtureTotal = 1;
	protected $sQuery = '';

	protected $aFlexiskin = array( 'name' => 'UTTest1', 'desc' => 'Desc1');

	protected function makeRequestParams() {
		return [
			'action' => $this->getModuleName(),
			'query' => $this->getSkinId()
		];
	}

	protected function getSkinId( ) {
		$sId = str_replace( " ", "_", strtolower( $this->aFlexiskin['name'] ) );
		return md5( $sId );
	}

	protected function getStoreSchema () {
		return [
			'filename' => [
				'type' => 'string'
			],
			'extension' => [
				'type' => 'string'
			],
			'mtime' => [
				'type' => 'numeric'
			]
		];
	}

	protected function createStoreFixtureData () {
		return 1;
	}

	protected function setUp() {
		$aFS = $this->aFlexiskin;

		$sConfigFile = Flexiskin::generateConfigFile( (object) $aFS );
		BsFileSystemHelper::saveToDataDirectory( 'conf.json', $sConfigFile, "flexiskin" . DS . $this->getSkinId() );
		BsFileSystemHelper::ensureDataDirectory( "flexiskin" . DS . $this->getSkinId() . DS . "images" );
		$sSource = __DIR__ . DS . 'data' . DS . 'test.PNG';
		$sDest = BS_DATA_DIR . DS . 'flexiskin' . DS . $this->getSkinId() . DS .'images' . DS  . 'test.PNG';
		copy( $sSource, $sDest );

		$this->sQuery = $this->getSkinId();

		parent::setUp();

		$this->mergeMwGlobalArrayValue(
			'wgGroupPermissions',
			[ 'sysop' => [ 'read' => true ] ]
		);
	}

	protected function tearDown() {
		$aFS = $this->aFlexiskin;
		$oStatus = BsFileSystemHelper::deleteFolder( "flexiskin" . DS . $this->getSkinId() );
		parent::tearDown();
	}

	protected function getModuleName () {
		return 'bs-flexiskin-upload-store';
	}

	public function provideSingleFilterData () {
		return [
			'Filter by filename' => [ 'string', 'ct', 'filename', 'te', 1 ]
		];
	}

	public function provideMultipleFilterData () {
		return [
			'Filter by filename and extension' => [
				[
					[
						'type' => 'string',
						'comparison' => 'ct',
						'field' => 'filename',
						'value' => 'test'
					],
					[
						'type' => 'string',
						'comparison' => 'eq',
						'field' => 'extension',
						'value' => 'png'
					]
				],
				1
			]
		];
	}
}
