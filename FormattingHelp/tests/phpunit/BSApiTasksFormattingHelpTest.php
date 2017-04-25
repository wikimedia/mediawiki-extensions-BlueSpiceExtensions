<?php
/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceFormattingHelp
 */
class BSApiTasksFormattingHelpTest extends BSApiTasksTestBase {
	protected function getModuleName () {
		return 'bs-formattinghelp';
	}

	public function testGetFormattingHelp() {
		$oData = $this->executeTask( 'getFormattingHelp', array() );

		$this->assertTrue( $oData->success );
		$this->assertArrayHasKey( 'html', $oData->payload );
		$this->assertNotEmpty( $oData->payload['html'] );
	}
}
