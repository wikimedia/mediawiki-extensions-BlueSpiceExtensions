<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceFlexiskin
 */
class BSApiFlexiskinTasksTest extends BSApiTasksTestBase {
	protected function getModuleName () {
		return 'bs-flexiskin-tasks';
	}

	function getTokens () {
		return $this->getTokenList ( self::$users[ 'sysop' ] );
	}

	protected function setUp() {
		parent::setUp();
		RequestContext::getMain()->setTitle( Title::newMainPage() );
	}

	public function testAdd() {
		$oData = $this->executeTask(
			'add',
			array(
				'data' => array(
					array(
						'name' => 'UT Test1',
						'desc' => 'UNITTEST entry',
						'template' => ''
					)
				)
			)
		);

		$this->assertTrue( $oData->success );

		$sId = str_replace( " ", "_", strtolower( 'UT Test1' ) );
		$sFlexiskinPath = BsFileSystemHelper::getDataDirectory( 'flexiskin' . DS . md5( $sId ) );

		$this->assertTrue( is_dir( $sFlexiskinPath) );

	}

	public function testSave() {
		$oData = $this->executeTask(
			'save',
			array(
				'id' => md5( str_replace( " ", "_", strtolower( 'UT Test1' ) ) ),
				'data' => array(
					array(
						'name' => 'UT TestChanged',
						'desc' => 'UNITTEST entry changed',
						'template' => ''
					)
				)
			)
		);

		$this->assertTrue( $oData->success );
		$this->assertEquals( md5( str_replace( " ", "_", strtolower( 'UT TestChanged' ) ) ), $oData->id );

		$sId = str_replace( " ", "_", strtolower( 'UT TestChanged' ) );
		$sFlexiskinPath = BsFileSystemHelper::getDataDirectory( 'flexiskin' . DS . md5( $sId ) );

		$this->assertTrue( is_dir( $sFlexiskinPath) );
	}

	public function testPreview() {
		$oData = $this->executeTask(
			'preview',
			array(
				'id' => md5( str_replace( " ", "_", strtolower( 'UT TestChanged' ) ) ),
				'data' => array()
			)
		);

		$this->assertTrue( $oData->success );
	}

	public function testReset() {
		$oData = $this->executeTask(
			'reset',
			array(
				'id' => md5( str_replace( " ", "_", strtolower( 'UT TestChanged' ) ) )
			)
		);

		$this->assertTrue( $oData->success );

		$this->assertArrayHasKey( 'skinId', $oData->data );
		$this->assertEquals( md5( str_replace( " ", "_", strtolower( 'UT TestChanged' ) ) ), $oData->data['skinId'] );

	}

	public function testActivate() {
		$oData = $this->executeTask(
			'activate',
			array(
				'id' => md5( str_replace( " ", "_", strtolower( 'UT TestChanged' ) ) )
			)
		);

		$this->assertTrue( $oData->success );

	}

	public function testDelete() {
		$oData = $this->executeTask(
			'delete',
			array(
				'id' => md5( str_replace( " ", "_", strtolower( 'UT TestChanged' ) ) )
			)
		);

		$this->assertTrue( $oData->success );

		$sId = str_replace( " ", "_", strtolower( 'UT TestChanged' ) );
		$sFlexiskinPath = BsFileSystemHelper::getDataDirectory( 'flexiskin' . DS . md5( $sId ) );

		$this->assertFalse( is_dir( $sFlexiskinPath) );

	}
}