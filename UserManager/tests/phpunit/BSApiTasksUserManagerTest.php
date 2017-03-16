<?php

/**
 * @group large
 */
class BSApiTasksUserManagerTest extends BSApiTasksTestBase {

	protected function getModuleName () {
		return 'bs-usermanager-tasks';
	}

	function getTokens () {
		return $this->getTokenList ( self::$users[ 'sysop' ] );
	}

	public function testAddUser () {
		$data = $this->executeTask ( 'addUser', array (
			'userName' => 'SomeName',
			'realname' => 'Some Name',
			'password' => 'pass123',
			'rePassword' => 'pass123',
			'email' => 'example@localhost.com',
			'enabled' => true,
			'groups' => array ( 'sysop' )
		) );

		$this->assertEquals ( true, $data->success );

		$this->assertSelect(
			'user',
			array( 'user_name', 'user_real_name', 'user_email',  ),
			array( "user_name = 'SomeName'" ),
			array(  array( 'SomeName', 'Some Name', 'example@localhost.com' )  )
		);
	}

	public function testEditUser () {
		$data = $this->executeTask ( 'editUser', array (
			'userID' => self::$users[ 'uploader' ]->user->getId(),
			'realname' => 'Some Other Name',
			'password' => 'pass123',
			'rePassword' => 'pass123',
			'email' => 'example@localhost.com',
			'enabled' => false,
			'groups' => array ( 'bureaucrat' )
		) );

		$this->assertEquals ( true, $data->success );

		$this->assertSelect(
			'user',
			array( 'user_real_name'),
			array( "user_id = '" . self::$users[ 'uploader' ]->user->getId() . "'" ),
			array(  array( 'Some Other Name' )  )
		);
	}

	public function testDisableUser () {
		$data = $this->executeTask ( 'disableUser', array (
			'userID' => self::$users[ 'uploader' ]->user->getId ()
		) );

		$this->assertEquals ( true, $data->success );

		$this->assertTrue( $this->userIsBlocked( self::$users[ 'uploader' ]->user->getId() ) );
	}

	public function testEnableUser () {
		$data = $this->executeTask ( 'enableUser', array (
			'userID' => self::$users[ 'uploader' ]->user->getId ()
		) );

		$this->assertEquals ( true, $data->success );

		$this->assertFalse( $this->userIsBlocked( self::$users[ 'uploader' ]->user->getId() ) );
	}

	public function testDeleteUser () {
		$data = $this->executeTask ( 'deleteUser', array (
			'userIDs' => [ self::$users[ 'uploader' ]->user->getId () ]
		) );

		$this->assertEquals ( true, $data->success );

		$this->assertFalse( $this->existsInDb( self::$users[ 'uploader' ]->user->getId() ) );
	}

	public function setUserGroups () {
		$data = $this->executeTask ( 'addUser', array (
			'userIDs' => [ self::$users[ 'uploader' ]->user->getId () ],
			'groups' => array ( 'bot' )
		) );

		$this->assertEquals ( true, $data->success );

		$this->assertSelect(
			'user_groups',
			array( 'ug_group'),
			array( "ug_user = '" . self::$users[ 'uploader' ]->user->getId() . "'" ),
			array(  array( 'bot' )  )
		);
	}

	public function editPassword () {
		$data = $this->executeTask ( 'addUser', array (
			'userID' => self::$users[ 'uploader' ]->user->getId (),
			'password' => 'pass1234',
			'rePassword' => 'pass1234'
		) );

		$this->assertEquals ( true, $data->success );
	}

	protected function userIsBlocked( $iId ) {
		$db = wfGetDB( DB_SLAVE );
		$res = $db->select( 'ipblocks', array( 'ipb_user' ), array( 'ipb_user = ' . $iId ), wfGetCaller() );
		if( $res->numRows() === 0 ) {
			return false;
		} else {
			return true;
		}
	}

	protected function existsInDb( $iId ) {
		$db = wfGetDB( DB_SLAVE );
		$res = $db->select( 'user', array( 'user_id' ), array( 'user_id = ' . $iId ), wfGetCaller() );
		if( $res->numRows() === 0 ) {
			return false;
		} else {
			return true;
		}
	}
}
