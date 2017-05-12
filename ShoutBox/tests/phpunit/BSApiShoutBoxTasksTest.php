<?php

/**
 * @group medium
 * @group Database
 * @group API
 * @group BlueSpice
 * @group BlueSpiceExtensions
 * @group BlueSpiceShoutBox
 */
require_once __DIR__."/BSShoutBoxFixture.php";
class BSApiShoutBoxTasksTest extends BSApiTasksTestBase {
	/**
	 * @var BSShoutBoxFixture
	 */
	protected $oShoutBoxFixture = null;

	/**
	 * @return BSShoutBoxFixture
	 */
	protected function getFixture() {
		if( $this->oShoutBoxFixture ) {
			return $this->oShoutBoxFixture;
		}
		$this->oShoutBoxFixture = new BSShoutBoxFixture( $this );
		return $this->oShoutBoxFixture;
	}

	protected $tablesUsed = [
		'user',
		'user_groups',
		'user_properties',
		'bs_shoutbox'
	];

	protected function setUp() {
		parent::setUp();
		if( !is_array( self::$users ) ) {
			self::$users = [];
		}
		self::$users = array_merge(
			self::$users,
			$this->getFixture()->getTestUsers()
		);
		$this->insertPage(
			$this->getFixture()->getTestTitle()->getText(),
			"ShoutBox test page"
		);
		$this->getFixture()->addTestDBData( $this->db );
		$this->mergeMwGlobalArrayValue(
			'wgGroupPermissions',
			[ 'user' => [ 'readshoutbox' => true, 'writeshoutbox' => true ] ]
		);
	}

	protected function getModuleName () {
		return 'bs-shoutbox-tasks';
	}

	function getTokens () {
		return $this->getTokenList( self::$users[ 'sbtestuser' ] );
	}

	public function testFailGetShoutsMissingArticleID() {
		$this->doLogin( 'sbtestuser' );
		$oData = $this->executeTask(
			'getShouts',
			[]
		);

		$this->assertFalse( $oData->success, "API sends success where it should have failed" );
		$this->assertArrayEquals( ['html' => ''], $oData->payload, "HTML is not empty" );
		$this->assertNotEmpty( $oData->message, "Error message is missing" );
	}

	public function testGetShouts() {
		$this->doLogin( 'sbtestuser' );
		$oParams = (object)[
			'articleId' => $this->getFixture()->getTestTitle()->getArticleId()
		];
		$oData = $this->executeTask(
			'getShouts',
			$oParams
		);

		$this->assertTrue( $oData->success, "API sends failed where it should have succeeded" );
		$this->assertTrue( $oData->payload_count == 2, "Count reports wrong number" );
		$this->assertEmpty( $oData->message, "There is an error message where there should be none" );
	}

	public function testFailInsertShoutMissingArticleId() {
		$this->doLogin( 'sbtestuser' );
		$oParams = (object)[
			'message' => __METHOD__,
		];
		$oData = $this->executeTask(
			'insertShout',
			$oParams
		);

		$this->assertFalse( $oData->success, "API sends success where it should have failed" );
		$this->assertNotEmpty( $oData->message, "Error message is missing" );
	}

	public function testFailInsertShoutMissingMessage() {
		$this->doLogin( 'sbtestuser' );
		$oParams = (object)[
			'articleId' => $this->getFixture()->getTestTitle()->getArticleId(),
		];
		$oData = $this->executeTask(
			'insertShout',
			$oParams
		);

		$this->assertFalse( $oData->success, "API sends success where it should have failed" );
		$this->assertNotEmpty( $oData->message, "Error message is missing" );
	}

	public function testInsertShout() {
		$this->doLogin( 'sbtestuser' );
		$oParams = (object)[
			'articleId' => $this->getFixture()->getTestTitle()->getArticleId(),
			'message' => __METHOD__,
		];
		$oData = $this->executeTask(
			'insertShout',
			$oParams
		);

		$this->assertTrue( $oData->success, "API sends failed where it should have succeeded" );
		$this->assertArrayHasKey( 'sb_id', $oData->payload, "There is no id information" );
		$this->assertNotEmpty( $oData->payload['sb_id'], "ID field is empty" );
	}

	public function testFailArchiveShoutMissingShoutId() {
		$this->doLogin( 'sbtestuser' );
		$oParams = (object)[];
		$oData = $this->executeTask(
			'archiveShout',
			$oParams
		);

		$this->assertFalse( $oData->success, "API sends success where it should have failed" );
		$this->assertNotEmpty( $oData->message, "Error message is missing" );
	}

	public function testArchiveShout() {
		$this->doLogin( 'sbtestuser' );
		$aUsers = $this->getFixture()->getTestUsers();
		$oUser = $aUsers['sbtestuser']->getUser();

		$oRow = $this->db->selectRow(
			'bs_shoutbox',
			'sb_id',
			[
				'sb_user_id' => (int)$oUser->getId(),
				'sb_page_id' => (int)$this->getFixture()->getTestTitle()
					->getArticleID(),
				'sb_archived' => 0,
			],
			__METHOD__
		);

		if( !$oRow ) {
			$this->fail();
		}

		$oParams = (object)[
			'shoutId' => $oRow->sb_id,
		];
		$oData = $this->executeTask(
			'archiveShout',
			$oParams
		);

		$this->assertTrue( $oData->success, "API sends failed where it should have succeeded" );
	}

	public function testFailArchiveShoutNotMine() {
		$this->doLogin( 'sbtestuser' );
		$aUsers = $this->getFixture()->getTestUsers();
		$oUser = $aUsers['sbtestsysop']->getUser();

		$oRow = $this->db->selectRow(
			'bs_shoutbox',
			'sb_id',
			[
				'sb_user_id' => (int)$oUser->getId(),
				'sb_page_id' => (int)$this->getFixture()->getTestTitle()
					->getArticleID(),
				'sb_archived' => 0,
			],
			__METHOD__
		);
		if( !$oRow ) {
			$this->fail();
		}
		$oParams = (object)[
			'shoutId' => $oRow->sb_id,
		];
		$oData = $this->executeTask(
			'archiveShout',
			$oParams
		);

		$this->assertFalse( $oData->success, "API sends success where it should have failed" );
		$this->assertNotEmpty( $oData->message, "Error message is missing" );
	}

	public function testArchiveShoutNotMine() {
		$this->doLogin( 'sbtestsysop' );
		$aUsers = $this->getFixture()->getTestUsers();
		$oUser = $aUsers['sbtestuser']->getUser();

		$oRow = $this->db->selectRow(
			'bs_shoutbox',
			'sb_id',
			[
				'sb_user_id' => (int)$oUser->getId(),
				'sb_page_id' => (int)$this->getFixture()->getTestTitle()
					->getArticleID(),
				'sb_archived' => 0,
			],
			__METHOD__
		);
		if( !$oRow ) {
			$this->fail();
		}
		$oParams = (object)[
			'shoutId' => $oRow->sb_id,
		];
		$oData = $this->executeTask(
			'archiveShout',
			$oParams
		);

		$this->assertTrue( $oData->success, "API sends failed where it should have succeeded" );
	}
}