<?php
/**
 * UserManager Extension for BlueSpice
 *
 * Administration interface for adding, editing and deleting users.
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * 
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Sebastian Ulbricht
 * @version    1.22.0 stable
 * @version    $Id: UserManager.class.php 9908 2013-06-25 08:56:34Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v1.20.0
 * - Added page size field in paging toolbar for dynamic page sizes
 * - MediaWiki I18N
 * v1.1.0
 * - Implemented remote sort
 * v0.1
 * FIRST CHANGES
*/

// Last review MRG (01.07.11 15:25)

// TODO MRG (08.01.11 13:04): Hier ist noch viel alter, ungenutzer Code

class UserManager extends BsExtensionMW {

	/* These groups are not touched by the addtogroup tool */
	protected $excludegroups = array( '*', 'user', 'autoconfirmed', 'emailconfirmed' );

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['UserManager'] = dirname( __FILE__ ) . '/UserManager.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'UserManager',
			EXTINFO::DESCRIPTION => 'Administration interface for adding, editing and deleting users.',
			EXTINFO::AUTHOR      => 'Markus Glaser',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9908 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array('bluespice' => '1.22.0')
		);

		WikiAdmin::registerModule( 'UserManager', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/images/bs-btn_usermanagement_v1.png',
			'level' => 'useradmin'
		) );

		BsConfig::registerVar( 'MW::UserManager::AllowPasswordChange', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-usermanager-pref-AllowPasswordChange', 'toggle' );

		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'UserManager', $this, 'getUsers', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'UserManager', $this, 'getUserGroups', 'wikiadmin' );

		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'UserManager', $this, 'doAddUser', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'UserManager', $this, 'doEditUser', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'UserManager', $this, 'doEditPassword', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'UserManager', $this, 'doEditGroups', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'UserManager', $this, 'doDeleteUser', 'wikiadmin' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function getUsers( &$output ) {
		$iLimit     = BsCore::getParam('limit', 25, BsPARAM::REQUEST|BsPARAMTYPE::INT);
		$iStart     = BsCore::getParam('start',  0, BsPARAM::REQUEST|BsPARAMTYPE::INT);
		$sSort      = BsCore::getParam( 'sort',  'user_name', BsPARAM::POST|BsPARAMTYPE::SQL_STRING );
		$sDirection = BsCore::getParam( 'dir',   'ASC',      BsPARAM::POST|BsPARAMTYPE::SQL_STRING );

		$dbr = wfGetDB( DB_SLAVE );

		global $wgDBtype, $wgDBprefix;
		if( $wgDBtype == 'oracle' ) {
			$res = $dbr->query( 
				"SELECT * FROM 
					(
						SELECT user_id,user_name,user_real_name,user_email,row_number() OVER (ORDER BY ".$sSort." ".$sDirection.") rnk  
						FROM \"".strtoupper($wgDBprefix)."MWUSER\"
					)
				WHERE rnk BETWEEN ".($iStart+1)." AND ".($iLimit + $iStart)
			);
		}
		else {
			$aOptions = array( 
				'ORDER BY' => $sSort.' '.$sDirection,
				'LIMIT'    => $iLimit,
				'OFFSET'   => $iStart
			);

			
			$res = $dbr->select( 
				'user',
				'*',
				array(),
				__METHOD__,
				$aOptions
			);
		}
		$data = array();
		$data['users'] = array();

		while( $row = $res->fetchObject() ) {
			$oUserTitle = Title::newFromText($row->user_name, NS_USER);
			$tmp = array();
			$tmp['user_id']        = $row->user_id;
			$tmp['user_name']      = $row->user_name;
			$tmp['user_page']      = $oUserTitle->getLocalURL();
			$tmp['user_real_name'] = $row->user_real_name;
			$tmp['user_email']     = $row->user_email == null ? '' : $row->user_email; //PW: Oracle returns null when field is emtpy
			$tmp['groups']         = array();

			$res1 = $dbr->select( 'user_groups', 'ug_group', 'ug_user='.$row->user_id );
			while( $row1 = $res1->fetchObject() ) {
				//$tmp['groups'][] = ( !wfMessage( 'group-' . $row1->ug_group )->inContentLanguage()->isBlank() ) ? wfMessage( 'group-' . $row1->ug_group )->plain() : $row1->ug_group ;
				if ( !wfMessage( 'group-' . $row1->ug_group )->inContentLanguage()->isBlank() ) {
					$tmp['groups'][] = wfMessage( 'group-' . $row1->ug_group )->plain() . " (" . $row1->ug_group . ")";
			}
				else {
					$tmp['groups'][] = $row1->ug_group;
				}
			}
			if(is_array($tmp['groups']))
				sort($tmp['groups']);
			$data['users'][] = $tmp;
		}

		$row = $dbr->selectRow( 'user', 'COUNT( user_id ) AS cnt', array() );
		$data['totalCount'] = $row->cnt;

		wfRunHooks( 'BSWikiAdminUserManagerBeforeUserListSend', array( $this, &$data ) );

		$output = json_encode($data);
	}

	public function getUserGroups( &$output ) {
		$groupPermissions = BsCore::getInstance( 'MW' )->getAdapter()->get( 'GroupPermissions' );
		//TODO: default 30000 was chosen because default false could be seen as '0' => id of superuser
		$uid = BsCore::getParam('user', 30000, BsPARAM::REQUEST|BsPARAMTYPE::INT);

		$user = User::newFromId($uid);
		$user->loadFromId($uid);

		$answer = array(
			'success' => false,
			'message' => '',
			'groups' => array()
		);

		if ($user->mId == 0) {
			$answer['message'] = wfMessage( 'bs-usermanager-id_noexist' )->plain();
		}
		else {
			foreach ( $groupPermissions as $key => $value ) {
				if ( !in_array( $key, $this->excludegroups ) ) {
					$tmp = array(
						'groupname' => ( !wfMessage( 'group-' .$key )->inContentLanguage()->isBlank() ) ? wfMessage( 'group-' . $key )->plain().' ('.$key.')' : $key,
						'group'  => $key,
						'member' => false
					);
					if (in_array($key, $user->mGroups)) {
						$tmp['member'] = true;
					}
					$answer['groups'][] = $tmp;
				}
			}
			$answer['success'] = true;
		}

		$output = json_encode($answer);
		return;
	}

	public function doAddUser( &$sOutput ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$sOutput = json_encode( array(
				'success' => false,
				'messages' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
			return;
		}

		//This is to overcome username case issues with custom AuthPlugin (i.e. LDAPAuth)
		//LDAPAuth woud otherwise turn the username to first-char-upper-rest-lower-case
		//At the end of this method we switch $_SESSION['wsDomain'] back again
		$tmpDomain = isset( $_SESSION['wsDomain'] ) ? $_SESSION['wsDomain'] : '';
		$_SESSION['wsDomain'] = 'local';

		$aResponse = array(
			'success'  => false,
			'errors'   => array(),
			'messages' => array()
		);

		$sUsername       = BsCore::getParam( 'username', '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		$sPassword       = BsCore::getParam( 'pass',     '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		$sPasswordRepeat = BsCore::getParam( 'repass',   '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		$sEmail          = BsCore::getParam( 'email',    '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		$sRealname       = BsCore::getParam( 'realname', '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		
		$sUsername = ucfirst( $sUsername );
		if ( User::isValidUserName( $sUsername ) === false ) { //TODO: Check if User::isCreatableName() is a better validation
			$aResponse['errors'][] = array( 
				'id'  => 'username', 
				'messages' => wfMessage( 'bs-usermanager-invalid_uname_gen' )->plain()
			);
		}

		if ( $sEmail != '' && User::isValidEmailAddr ( $sEmail ) === false ) {
			$aResponse['errors'][] = array( 
				'id'  => 'email', 
				'messages' => wfMessage( 'bs-usermanager-invalid_email_gen' )->plain()
			);
		}

		if ( $sPassword == '' ) {
			$aResponse['errors'][] = array( 
				'id'  => 'pass', 
				'messages' => wfMessage( 'bs-usermanager-enter_pwd' )->plain()
			);
		}

		if ( strpos( $sRealname, '\\' ) ) {
			$aResponse['errors'][] = array( 
				'id'  => 'realname', 
				'messages' => wfMessage( 'bs-usermanager-invalid_realname' )->plain()
			);
		}

		if ( $sPassword != $sPasswordRepeat ) {
			$aResponse['errors'][] = array(
				'id'  => 'repass',
				'messages' => wfMessage( 'bs-usermanager-pwd_nomatch' )->plain()
			);
		}

		if ( strtolower( $sUsername ) == strtolower( $sPassword ) ) {
			$aResponse['errors'][] = array(
				'id'  => 'pass',
				'messages' => wfMessage( 'bs-usermanager-user_pwd_match' )->plain()
			);
		}

		$oNewUser = User::newFromName( $sUsername );
		if ( $oNewUser == null ) { //Should not be neccessary as we check for username validity above
			$aResponse['errors'][] = array( 
				'id'  => 'username', 
				'messages' => wfMessage( 'bs-usermanager-invalid_uname' )->plain()
			);
		}

		if ( $oNewUser instanceof User ) {
			if( $oNewUser->getId() != 0 ) {
				$aResponse['errors'][] = array( 
					'id'  => 'username', 
					'messages' => wfMessage( 'bs-usermanager-user_exists' )->plain()
				);
			}

			if ( $oNewUser->isValidPassword( $sPassword ) == false ) {
				//TODO: $oNewUser->getPasswordValidity() returns a message key in case of error. Maybe we sould return this message.
				$aResponse['errors'][] = array( 
					'id'  => 'pass', 
					'messages' => wfMessage( 'bs-usermanager-invalid_pwd' )->plain()
				);
			}
		}

		if( !empty( $aResponse['errors'] ) ) { //In case that any error occured
			$sOutput = json_encode( $aResponse );
			return;
		}

		$oNewUser->addToDatabase();
		$oNewUser->setPassword( $sPassword );
		$oNewUser->setEmail( $sEmail );
		$oNewUser->setRealName( $sRealname );
		$oNewUser->setToken();

		$oNewUser->saveSettings();

		$ssUpdate = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
		$ssUpdate->doUpdate();
			
		$aResponse['success'] = true;
		$aResponse['messages'][] = wfMessage( 'bs-usermanager-user_added' )->plain();

		$_SESSION['wsDomain'] = $tmpDomain;

		wfRunHooks( 
			'BSUserManagerAfterAddUser',
			array( 
				$this,
				$oNewUser,
				array(
					'username' => $sUsername,
					'email'    => $sEmail,
					'password' => $sPassword,
					'realname' => $sRealname
				)
			)
		);

		$sOutput = json_encode( $aResponse );
		return;
	}

	/**
	 * Remote-Handler for modifing a user
	 * @global Language $wgContLang
	 * @param string $output
	 * @return void
	 */
	public function doEditUser( &$output ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$output = json_encode( array(
				'success' => false,
				'messages' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
			return;
		}

		global $wgContLang;
		$aResponse = array(
			'success'  => false,
			'errors'   => array(),
			'messages' => array()
		);

		$sUsername   = BsCore::getParam('username',     '', BsPARAM::REQUEST|BsPARAMTYPE::STRING);
		$sEmail      = BsCore::getParam('email',        '', BsPARAM::REQUEST|BsPARAMTYPE::STRING);
		$sRealname   = BsCore::getParam('realname',     '', BsPARAM::REQUEST|BsPARAMTYPE::STRING);
		$sChangeText = BsCore::getParam('changetext', 'no', BsPARAM::REQUEST|BsPARAMTYPE::STRING);
		//TODO: default 30000 was chosen because default false could be seen as '0' => id of superuser
		$iUserId = (int)BsCore::getParam( 'user', 30000, BsPARAM::REQUEST|BsPARAMTYPE::INT );

		if ( User::isValidUserName ( $sUsername ) === false ) {
			$aResponse['errors'][] = array(
				'id' => 'username',
				'messages' => wfMessage( 'bs-usermanager-invalid_uname_gen' )->plain()
			);
		}

		$oUser = User::newFromId($iUserId);
		if ( User::whoIs($iUserId) == false ) {
			$aResponse['errors'][] = true; //Just to have empty($aResponse['errors']) == false
			$aResponse['messages'][] = wfMessage( 'bs-usermanager-id_noexist' )->plain();
		}
		
		if ( $sEmail != '' && User::isValidEmailAddr ( $sEmail ) === false ) {
			$aResponse['errors'][] = array( 
				'id'  => 'email', 
				'messages' => wfMessage( 'bs-usermanager-invalid_email_gen' )->plain()
			);
		}
		
		if( !empty($aResponse['errors'] ) ){
			$output = json_encode( $aResponse );
			return;
		}

		$nameChanged = false;
		if ( $oUser->getName() != $sUsername ) $nameChanged = true;

		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->update( 'user',
			array(
				'user_name'      => $sUsername,
				'user_real_name' => $sRealname,
				'user_email'     => $sEmail
			),
			array( 'user_id' => $iUserId )
		);

		if ($res === false) {
			$aResponse['errors'][] = true;
			$aResponse['messages'][] = wfMessage( 'bs-usermanager-db_error' )->plain();
		}
		else {
			if ( $nameChanged && $sChangeText == 'yes' ) {
				$sOldUserPageName = $wgContLang->getNsText(NS_USER).':'.$oUser->getName();
				$sNewUserPageName = $wgContLang->getNsText(NS_USER).':'.$sUsername;
				$res = $dbw->update(
					'text',
					array(
						// TODO MRG (01.07.11 15:33): This changes all old revisions. maybe only current revision should be updated?
						sprintf( 
							'old_text = REPLACE(old_text, "%s", "%s")',
							$sOldUserPageName,
							$sNewUserPageName
						)
					),
					'*'
				);
				wfDebugLog(
					'BS::UserManager',
					sprintf(
						'Replaced "%s" with "%s" in "text" table.',
						$sOldUserPageName,
						$sNewUserPageName							
					)
				);
			}
		}

		if ( $res === false ) {
			$aResponse['errors'][] = true;
			$aResponse['messages'][] = wfMessage( 'bs-usermanager-db_error' )->plain();
		}

		if ( empty($aResponse['errors'] ) ) {
			$aResponse['success'] = true;
			$aResponse['messages'][] = wfMessage( 'bs-usermanager-save_successful' )->plain();
		}

		wfRunHooks( 
			'BSUserManagerAfterEditUser',
			array( 
				$this,
				$oUser,
				array(
					'username'       => $sUsername,
					'email'          => $sEmail,
					'changepassword' => $sChangeText,
					'realname'       => $sRealname
				)
			)
		);

		$output = json_encode($aResponse);
		return;
	}

	public function doEditPassword( &$output ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$output = json_encode( array(
				'success' => false,
				'messages' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
			return;
		}

		$answer = array(
			'success' => true,
			'errors' => array(),
			'messages' => array()
		);

		if ( !BsConfig::get( 'MW::UserManager::AllowPasswordChange' ) ) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-not_allowed' )->plain();
		}

		$newpw  = BsCore::getParam( 'newpass', '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		$newpwc = BsCore::getParam( 'newrepass', '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		$uid    = BsCore::getParam( 'user', false, BsPARAM::REQUEST|BsPARAMTYPE::INT );
		// TODO: check if next line reasonable!
		if ( $uid === false ) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-id_noexist' )->plain();
		}

		if ( $newpw == '' ) {
			$answer['success'] = false;
			$answer['errors']['newpass'] = wfMessage( 'bs-usermanager-enter_pwd' )->plain(); // 'enter_pwd' = 'Enter a password'
		}
		if ( strpos( $newpw, '\\' ) ) {
			$answer['success'] = false;
			$answer['errors']['newpass'] = wfMessage( 'bs-usermanager-invalid_pwd' )->plain(); // 'invalid_pwd' = 'The supplied password is invalid. Please do not use apostrophes or backslashes.'
		}
		if ( $newpw != $newpwc ) {
			$answer['success'] = false;
			$answer['errors']['newrepass'] = wfMessage( 'bs-usermanager-pwd_nomatch' )->plain(); // 'pwd_nomatch' = 'The supplied passwords do not match'
		}
		$user = User::newFromId( $uid );
		//	$user->loadFromId(123);

		if ($user->mId == 0) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-id_noexist' )->plain(); // id_noexist = 'This user ID does not exist'
		}

		$res = true;
		if ( $answer['success'] ) {
			$dbw = wfGetDB( DB_MASTER );
			$res = $dbw->update( 'user',
				array( 'user_password' => User::crypt( $newpw ) ), 
				array( 'user_id' => $uid )
			);
			$user->invalidateCache();
		}
		if ( $res === false ) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-db_error' )->plain();
		}

		if($answer['success']) {
			$answer['messages'][] = wfMessage( 'bs-usermanager-save_successful' )->plain();
		}

		$output = json_encode( $answer );
		return;
	}

	public function doEditGroups( &$output ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$output = json_encode( array(
				'success' => false,
				'messages' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
			return;
		}

		$answer = array(
			'success' => true,
			'errors' => array(),
			'messages' => array()
		);

		$uid    = BsCore::getParam( 'user', 30000, BsPARAM::REQUEST|BsPARAMTYPE::INT );
		$groups = BsCore::getParam( 'groups', array(), BsPARAM::REQUEST|BsPARAMTYPE::ARRAY_STRING );

		$user = User::newFromId( $uid );

		if ( $user->mId == 0 ) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-id_noexist' )->plain();
		}
		if ( ( $user->mId == 1 ) && ( !in_array( 'sysop', $groups ) ) ) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-sysop_rights' )->plain();
		}

		if ( $answer['success'] ) {

			$dbw = wfGetDB( DB_MASTER );
			$res = $dbw->delete( 'user_groups',
				array( 
					'ug_group NOT' => $this->excludegroups,
					'ug_user' => $uid
				)
			);

			$res1 = true;
			if( is_array( $groups ) ) {
				foreach ( $groups as $g ) {
					$res2 = $dbw->insert( 
										'user_groups',
										array(
											'ug_user' => $uid,
											'ug_group' => addslashes( $g ) 
										)
							);
					if ( $res2 === false ) $res1 = false;
				}
			}
		}

		if ( ( $res === false ) || ( $res1 === false ) ) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-db_error' )->plain();
		}

		if ( $answer['success'] ) {
			$answer['messages'][] = wfMessage( 'bs-usermanager-save_successful' )->plain();
		}

		$output = json_encode( $answer );
		$user->invalidateCache();
		return;
	}

	public function doDeleteUser( &$output ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$output = json_encode( array(
				'success' => false,
				'messages' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
			return;
		}

		$answer = array(
			'success' => true,
			'errors' => array(),
			'messages' => array()
		);

		$uid = BsCore::getParam( 'user', 30000, BsPARAM::REQUEST|BsPARAMTYPE::INT ); // $uid = addslashes($_REQUEST['user_id']);

		$user = User::newFromId( $uid );

		if ( $user->mId == 0 ) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-id_noexist' )->plain();
		}

		if ($user->mId == 1) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-admin_nodelete' )->plain();
		}

		if ($answer['success']) {
			$dbw = wfGetDB( DB_MASTER );
			$res = $dbw->delete( 'user',
				array( 'user_id' => $uid )
			);
			$res1 = $dbw->delete( 'user_groups',
				array( 'ug_user' => $uid )
			);
			$res2 = $dbw->delete( 'user_newtalk',
				array( 'user_id' => $uid )
			);
		}
		
		if( $user->getUserPage()->exists() ) {
			$oUserPageArticle = new Article( $user->getUserPage() );
			$oUserPageArticle->doDelete( wfMessage( 'bs-usermanager-db_error' )->plain() );
		}

		if ( ( $res === false ) || ( $res1 === false ) || ( $res2 === false ) ) {
			$answer['success'] = false;
			$answer['messages'][] = wfMessage( 'bs-usermanager-db_error' )->plain();
		}

		if($answer['success']) {
			$answer['messages'][] = wfMessage( 'bs-usermanager-user_deleted' )->plain();
		}

		$output = json_encode( $answer );
		return;
	}

	public function getForm( $firsttime = false ) {
		global $wgOut;
		$wgOut->addModules('ext.bluespice.userManager');
		return '<div id="UserManagerGrid"></div>';
	}

}
