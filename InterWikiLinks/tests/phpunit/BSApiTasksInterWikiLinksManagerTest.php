<?php

/**
 * @group medium
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceInterWikiLnksManager
 */
class BSApiTasksInterWikiLinksManagerTest extends BSApiTasksTestBase {
	protected function getModuleName( ) {
		return 'bs-interwikilinks-tasks';
	}

	public function testEditInterWikiLink() {
		$oCreateData = $this->executeTask(
			'editInterWikiLink',
			array(
				'prefix' => 'dummylink',
				'url' => 'http://some.wiki.com/$1'
			)
		);

		$this->assertTrue( $oCreateData->success );

		$this->assertSelect(
			'interwiki',
			array( 'iw_prefix', 'iw_url' ),
			array( 'iw_prefix' => 'dummylink'),
			array( array ( 'dummylink', 'http://some.wiki.com/$1' ) )
		);

		$oEditData = $this->executeTask(
			'editInterWikiLink',
			array(
				'oldPrefix' => 'dummylink',
				'prefix' => 'fauxlink',
				'url' => 'http://some.wiki.com/wiki/$1'
			)
		);

		$this->assertTrue( $oEditData->success );

		$this->assertTrue( $this->isDeleted( 'dummylink') );

		$this->assertSelect(
			'interwiki',
			array( 'iw_prefix', 'iw_url' ),
			array( 'iw_prefix' => 'fauxlink'),
			array( array ( 'fauxlink', 'http://some.wiki.com/wiki/$1' ) )
		);
	}

	public function testRemoveInterWikiLink() {
		$this->assertFalse( $this->isDeleted( 'fauxlink' ) );

		$oDeleteData = $this->executeTask(
			'removeInterWikiLink',
			array(
				'prefix' => 'fauxlink'
			)
		);

		$this->assertTrue( $oDeleteData->success );
		$this->assertTrue( $this->isDeleted( 'fauxlink' ) );
	}

	protected function isDeleted( $sValue ) {
		$db = wfGetDB( DB_REPLICA );
		$res = $db->select( 'interwiki', array( 'iw_prefix' ), array( 'iw_prefix' => $sValue ), wfGetCaller() );
		if( $res->numRows() === 0 ) {
			return true;
		}

		return false;
	}
}

