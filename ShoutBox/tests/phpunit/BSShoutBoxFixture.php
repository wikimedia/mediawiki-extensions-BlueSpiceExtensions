<?php

class BSShoutBoxFixture {
	protected $oTest = null;
	protected $aTestUsers = null;

	public function __construct( $oTest ) {
		$this->oTest = $oTest;
	}

	public function addTestDBData( $oDB ) {
		$aShouts = $this->getTestShouts();
		foreach( $aShouts as $row ) {
			$oDB->insert( 'bs_shoutbox', [
				'sb_page_id' => $row[0],
				'sb_user_id' => $row[1],
				'sb_timestamp' => $row[2],
				'sb_user_name' => $row[3],
				'sb_message' => $row[4],
			]);
		}
	}

	public function getTestTitle() {
		return Title::newFromText( 'ShoutBoxUnitTestPage', NS_MAIN );
	}

	public function getTestUsers() {
		if( $this->aTestUsers ) {
			return $this->aTestUsers;
		}
		$this->aTestUsers = [
			'sbtestuser' => new TestUser(
				'Shoutboxtestuser',
				'ShoutBox Test User',
				'Shoutboxtestuser@example.com',
				[]
			),
			'sbtestsysop' => new TestUser(
				'Shoutboxtestsysop',
				'ShoutBox Test Sysop',
				'Shoutboxtestsysop@example.com',
				[ 'sysop' ]
			),
		];
		return $this->aTestUsers;
	}

	public function getTestShouts() {
		$aUsers = $this->getTestUsers();
		return [
			[
				(int)$this->getTestTitle()->getArticleID(),
				(int)$aUsers['sbtestuser']->getUser()->getId(),
				'20150314092653',
				$aUsers['sbtestuser']->getUser()->getRealName(),
				"I shout: the sheriff",
			], [
				(int)$this->getTestTitle()->getArticleID(),
				(int)$aUsers['sbtestsysop']->getUser()->getId(),
				'20170517101431',
				$aUsers['sbtestsysop']->getUser()->getRealName(),
				"Another generic test shout",
			],
		];
	}
}
