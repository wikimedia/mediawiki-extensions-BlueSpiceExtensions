<?php

/**
 * @group medium
 * @group Database
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceStateBar
 */
class BSApiStateBarTasksTest extends BSApiTasksTestBase {
	protected function setUp() {
		parent::setUp();

		//Statebar get it's context from BSApiTasks::getContext()->getWikiPage()
		//so we need to manually reset the context and inject our test title
		RequestContext::resetMain();
		$GLOBALS['wgTitle'] = Title::newFromText( "StatebarUnitTestPage" );
		//Clear the BSStateBarBeforeBodyViewAdd to have always same environment
		$GLOBALS['wgHooks']['BSStateBarBeforeBodyViewAdd'] = [];
	}

	protected function getModuleName () {
		return 'bs-statebar-tasks';
	}
	public function addDBDataOnce() {
		$this->insertPage( 'StatebarUnitTestPage', "Statebar test page" );
	}
	function getTokens () {
		return $this->getTokenList( self::$users[ 'sysop' ] );
	}

	public function testFailCollectBodyViews() {
		$oData = $this->executeTask(
			'collectBodyViews',
			[]
		);

		$this->assertTrue( $oData->success );
		$this->assertArrayEquals( [], $oData->payload );
	}

	public function testCollectBodyViews() {
		$GLOBALS['wgHooks']['BSStateBarBeforeBodyViewAdd'][]
			= function( $oStateBar, &$aBodyViews, $oUser, $oTitle ) {
			$aBodyViews[] = new StateBarTasksTestViewStateBarBodyElement();
		};
		$oData = $this->executeTask(
			'collectBodyViews',
			[]
		);

		$this->assertTrue( $oData->success );
		$this->assertArrayHasKey( 'views', $oData->payload );
		$this->assertArrayHasKey( 0, $oData->payload['views'] );
		$this->assertEquals(
			'StateBarTasksTestViewStateBarBodyElement',
			$oData->payload['views'][0]
		);
	}
}

class StateBarTasksTestViewStateBarBodyElement extends ViewStateBarBodyElement {
	public function execute( $param = false ) {
		return 'StateBarTasksTestViewStateBarBodyElement';
	}
}