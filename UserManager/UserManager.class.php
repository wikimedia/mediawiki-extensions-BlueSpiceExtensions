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
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    2.22.0 stable
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
	protected static $excludegroups = array( '*', 'user', 'autoconfirmed', 'emailconfirmed' );

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'UserManager',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-usermanager-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser, Stephan Muggli',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);

		WikiAdmin::registerModule( 'UserManager', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_usermanagement_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-usermanager-label'
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public static function getUsers() {
		//if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		$oStoreParams = BsExtJSStoreParams::newFromRequest();
		$iLimit = $oStoreParams->getLimit();
		$iStart = $oStoreParams->getStart();
		$sSort = $oStoreParams->getSort( 'user_name' );
		$sDirection = $oStoreParams->getDirection();
		$aFilters = $oStoreParams->getFilter();

		$aSortingParams = FormatJson::decode( $sSort );
		if ( is_array( $aSortingParams ) ) {
			$sSort = $aSortingParams[0]->property;
			$sDirection = $aSortingParams[0]->direction;
		}

		$dbr = wfGetDB( DB_SLAVE );

		global $wgDBtype, $wgDBprefix;
		//PW TODO: filter for oracle
		if( $wgDBtype == 'oracle' ) {
			$res = $dbr->query(
				"SELECT * FROM
					(
						SELECT user_id,user_name,user_real_name,user_email,row_number() OVER (ORDER BY ".$sSort." ".$sDirection.") rnk
						FROM \"".strtoupper($wgDBprefix)."MWUSER\"
					)
				WHERE rnk BETWEEN ".($iStart+1)." AND ".($iLimit + $iStart)
			);
		} else {
			$aTables = array(
				'user'
			);

			$aOptions = array(
				'ORDER BY' => $sSort.' '.$sDirection,
				'LIMIT'    => $iLimit,
				'OFFSET'   => $iStart
			);

			$aJoins = array();

			$aConditions = array();
			if( !empty($aFilters) ) {
				foreach($aFilters as $oFilter) {
					switch($oFilter->field) {
						case 'user_name':
							$aConditions[] = "user_name LIKE '%".trim($oFilter->value)."%'";
							break;
						case 'user_real_name':
							$aConditions[] = "user_real_name LIKE '%".trim($oFilter->value)."%'";
							break;
						case 'user_email':
							$aConditions[] = "user_email LIKE '%".trim($oFilter->value)."%'";
							break;
						case 'groups':
							$aTables[] = 'user_groups';
							$aConditions[] = "ug_group IN ('".implode("','", $oFilter->value)."')";
							$aJoins['user_groups'] = array('LEFT JOIN', 'ug_user=user_id');
					}
				}
			}

			$res = $dbr->select(
				$aTables,
				'*',
				$aConditions,
				__METHOD__,
				$aOptions,
				$aJoins
			);
		}

		$data = array();
		$data['users'] = array();

		while( $row = $res->fetchObject() ) {
			$oUserTitle = Title::newFromText($row->user_name, NS_USER);
			$tmp = array();
			$tmp['user_id']        = $row->user_id;
			$tmp['user_name']      = $row->user_name;
			$tmp['user_page_link'] = Linker::link( $oUserTitle, $row->user_name.' ' ); //The whitespace is to aviod automatic rewrite to user_real_name by BSF
			$tmp['user_real_name'] = $row->user_real_name;
			$tmp['user_email']     = $row->user_email == null ? '' : $row->user_email; //PW: Oracle returns null when field is emtpy
			$tmp['groups']         = array();

			$res1 = $dbr->select( 'user_groups', 'ug_group', 'ug_user='.$row->user_id );
			while( $row1 = $res1->fetchObject() ) {
				//$tmp['groups'][] = ( !wfMessage( 'group-' . $row1->ug_group )->inContentLanguage()->isBlank() ) ? wfMessage( 'group-' . $row1->ug_group )->plain() : $row1->ug_group ;
				if ( !wfMessage( 'group-' . $row1->ug_group )->inContentLanguage()->isBlank() ) {
					$tmp['groups'][] = array( 'group' => $row1->ug_group, 'displayname' => wfMessage( 'group-' . $row1->ug_group )->plain() . " (" . $row1->ug_group . ")" );
				} else {
					$tmp['groups'][] = array( 'group' => $row1->ug_group, 'displayname' => $row1->ug_group );
				}
			}
			if ( is_array( $tmp['groups'] ) ) sort( $tmp['groups'] );
			$data['users'][] = $tmp;
		}

		$row = $dbr->selectRow( 'user', 'COUNT( user_id ) AS cnt', array() );
		$data['totalCount'] = $row->cnt;

		$oUserManager = BsExtensionManager::getExtension( 'UserManager' );
		wfRunHooks( 'BSWikiAdminUserManagerBeforeUserListSend', array( $oUserManager, &$data ) );

		return FormatJson::encode( $data );
	}

	/**
	 * Adds an user to the database
	 * @param String $uUser Json encoded new user
	 * @return string json encoded response
	 */
	public static function addUser( $sUsername, $sPassword, $sRePassword, $sEmail, $sRealname, $aGroups = array() ) {

		$res = $resDelGroups = $resInsGroups = $resERealUser = false;

		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		//This is to overcome username case issues with custom AuthPlugin (i.e. LDAPAuth)
		//LDAPAuth woud otherwise turn the username to first-char-upper-rest-lower-case
		//At the end of this method we switch $_SESSION['wsDomain'] back again
		$tmpDomain = isset( $_SESSION['wsDomain'] ) ? $_SESSION['wsDomain'] : '';
		$_SESSION['wsDomain'] = 'local';

		$aResponse = array(
			'success'  => false,
			'errors'   => array(),
			'message' => array()
		);

		$sUsername = ucfirst( $sUsername );
		if ( User::isCreatableName( $sUsername ) === false ) {
			$aResponse['errors'][] = array(
				'id' => 'username',
				'message' => wfMessage( 'bs-usermanager-invalid-uname' )->plain()
			);
		}

		if ( $sEmail != '' && Sanitizer::validateEmail( $sEmail ) === false ) {
			$aResponse['errors'][] = array(
				'id' => 'email',
				'message' => wfMessage( 'bs-usermanager-invalid-email-gen' )->plain()
			);
		}

		if ( $sPassword == '' ) {
			$aResponse['errors'][] = array(
				'id' => 'pass',
				'message' => wfMessage( 'bs-usermanager-enter-pwd' )->plain()
			);
		}

		if ( strpos( $sRealname, '\\' ) ) {
			$aResponse['errors'][] = array(
				'id' => 'realname',
				'message' => wfMessage( 'bs-usermanager-invalid-realname' )->plain()
			);
		}

		if ( $sPassword != $sRePassword ) {
			$aResponse['errors'][] = array(
				'id' => 'repass',
				'message' => wfMessage( 'badretype' )->plain() // MW message
			);
		}

		if ( strtolower( $sUsername ) == strtolower( $sPassword ) ) {
			$aResponse['errors'][] = array(
				'id' => 'pass',
				'message' => wfMessage( 'password-name-match' )->plain() // MW message
			);
		}

		$oNewUser = User::newFromName( $sUsername );
		if ( $oNewUser == null ) { //Should not be neccessary as we check for username validity above
			$aResponse['errors'][] = array(
				'id' => 'username',
				'message' => wfMessage( 'bs-usermanager-invalid-uname' )->plain()
			);
		}

		if ( $oNewUser instanceof User ) {
			if( $oNewUser->getId() != 0 ) {
				$aResponse['errors'][] = array(
					'id' => 'username',
					'message' => wfMessage( 'bs-usermanager-user-exists' )->plain()
				);
			}

			if ( $oNewUser->isValidPassword( $sPassword ) == false ) {
				//TODO: $oNewUser->getPasswordValidity() returns a message key in case of error. Maybe we sould return this message.
				$aResponse['errors'][] = array(
					'id' => 'pass',
					'message' => wfMessage( 'bs-usermanager-invalid-pwd' )->plain()
				);
			}
		}

		if ( !empty( $aResponse['errors'] ) ) { //In case that any error occurred
			return FormatJson::encode( $aResponse );
		}

		$oNewUser->addToDatabase();
		$oNewUser->setPassword( $sPassword );
		$oNewUser->setEmail( $sEmail );
		$oNewUser->setRealName( $sRealname );
		$oNewUser->setToken();

		$oNewUser->saveSettings();

		$dbw = wfGetDB( DB_MASTER );
		$resDelGroups = $dbw->delete( 'user_groups',
			array(
				'ug_user' => $oNewUser->getId()
			)
		);

		$resInsGroups = true;
		if( is_array( $aGroups ) ) {
			foreach ( $aGroups as $sGroup ) {
				if ( in_array( $sGroup, self::$excludegroups ) ) continue;
				$resInsGroups = $dbw->insert(
						'user_groups',
						array(
							'ug_user' => $oNewUser->getId(),
							'ug_group' => addslashes( $sGroup )
						)
				);
			}
		}

		if ( $resDelGroups === false || $resInsGroups === false ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-db-error' )->plain();
		}

		$ssUpdate = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
		$ssUpdate->doUpdate();

		$aResponse['success'] = true;
		$aResponse['message'][] = wfMessage( 'bs-usermanager-user-added' )->plain();

		$_SESSION['wsDomain'] = $tmpDomain;

		$oUserManager = BsExtensionManager::getExtension( 'UserManager' );
		wfRunHooks(
			'BSUserManagerAfterAddUser',
			array(
				$oUserManager,
				$oNewUser,
				array(
					'username' => $sUsername,
					'email'    => $sEmail,
					'password' => $sPassword,
					'realname' => $sRealname
				)
			)
		);

		return FormatJson::encode( $aResponse );
	}

	/**
	 * Adds an user to the database
	 * @param String $sUsername user name
	 * @param String $sPassword password
	 * @param String $sRePassword password confirmation
	 * @param String $sEmail user's e-mail address
	 * @param String $sRealname user's real name
	 * @param Array $aGroups user name
	 * @return string json encoded response
	 */
	public static function editUser( $sUsername, $sPassword, $sRePassword, $sEmail, $sRealname, $aGroups = array() ) {
		$res = $resDelGroups = $resInsGroups = $resERealUser = false;

		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'message' => array()
		);

		$oUser = User::newFromName( $sUsername );

		if ( $oUser->getId() === 0 ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-idnotexist' )->plain(); // id_noexist = 'This user ID does not exist'
		}
		if ( !empty( $sPassword ) && !$oUser->isValidPassword( $sPassword ) ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array(
				'id' => 'pass',
				'message' => wfMessage( 'bs-usermanager-invalid-pwd' )->plain()
			);
		}
		if ( $sPassword !== $sRePassword ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array(
				'id' => 'newpass',
				'message' => wfMessage( 'badretype' )->plain() // MW message
			);
		}
		if ( strpos( $sRealname, '\\' ) ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array(
				'id' => 'realname',
				'message' => wfMessage( 'bs-usermanager-invalid-realname' )->plain()
			);
		}
		if ( $sEmail != '' && Sanitizer::validateEmail( $sEmail ) === false ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array(
				'id' => 'email',
				'message' => wfMessage( 'bs-usermanager-invalid-email-gen' )->plain()
			);
		}

		global $wgUser;
		if (
			$wgUser->getId() == $oUser->getId() &&
			in_array( 'sysop', $wgUser->getEffectiveGroups() ) &&
			!in_array( 'sysop', $aGroups )
		) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array(
				'id' => 'groups',
				'message' => wfMessage( 'bs-usermanager-no-self-desysop' )->plain()
			);
		}

		$dbw = wfGetDB( DB_MASTER );
		if ( $aAnswer['success'] ) {
			if ( !empty( $sPassword ) ) {
				$res = $dbw->update(
						'user',
						array( 'user_password' => User::crypt( $sPassword ) ),
						array( 'user_id' => $oUser->getId() )
				);
			} else {
				$res = true;
			}

			$resDelGroups = $dbw->delete(
				'user_groups',
				array(
					'ug_user' => $oUser->getId()
				)
			);

			$resInsGroups = true;
			if ( is_array( $aGroups ) ) {
				foreach ( $aGroups as $sGroup ) {
					if ( in_array( $sGroup, self::$excludegroups ) ) continue;
					$resInsGroups = $dbw->insert(
						'user_groups',
						array(
							'ug_user' => $oUser->getId(),
							'ug_group' => addslashes( $sGroup )
						)
					);
				}
			}

			$resERealUser = $dbw->update(
					'user',
					array(
						'user_real_name' => $sRealname,
						'user_email'     => $sEmail
					),
					array( 'user_id' => $oUser->getId() )
			);

			$oUser->invalidateCache();
		}

		if ( $res === false || $resDelGroups === false
			|| !$resInsGroups || $resERealUser === false ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-db-error' )->plain();
		}

		if ( $aAnswer['success'] ) {
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-save-successful' )->plain();
		}

		return FormatJson::encode( $aAnswer );
	}

	/**
	 * Deletes an user form the database
	 * @global User $wgUser
	 * @param Integer $iUserId user id
	 * @return string json encoded response
	 */
	public static function deleteUser( $iUserId ) {
		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'message' => array()
		);

		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$aAnswer['success'] = false;
			$aAnswer['message'][] =  wfMessage( 'bs-readonly', $wgReadOnly )->plain();
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] =  wfMessage( 'bs-wikiadmin-notallowed' )->plain();
		}

		$oUser = User::newFromId( $iUserId );

		if ( $oUser->getId() == 0 ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-idnotexist' )->plain();
		}

		if ( $oUser->getId() == 1 ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-admin-nodelete' )->plain();
		}

		global $wgUser;
		if ( $oUser->getId() == $wgUser->getId() ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-self-nodelete' )->plain();
		}

		if( !$aAnswer['success'] ) {
			return FormatJson::encode( $aAnswer );
		}

		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->delete( 'user',
			array( 'user_id' => $oUser->getId() )
		);
		$res1 = $dbw->delete( 'user_groups',
			array( 'ug_user' => $oUser->getId() )
		);
		$res2 = $dbw->delete( 'user_newtalk',
			array( 'user_id' => $oUser->getId() )
		);
		$iUsers = $dbw->selectField( 'user', 'COUNT(*)', array() );
		$res3 = $dbw->update( 'site_stats',
			array( 'ss_users' => $iUsers ),
			array( 'ss_row_id' => 1 )
		);

		if ( $oUser->getUserPage()->exists() ) {
			$oUserPageArticle = new Article( $oUser->getUserPage() );
			$oUserPageArticle->doDelete( wfMessage( 'bs-usermanager-db-error' )->plain() );
		}

		if ( ( $res === false ) || ( $res1 === false ) || ( $res2 === false ) || ( $res3 === false ) ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-db-error' )->plain();
			return FormatJson::encode( $aAnswer );
		}

		$aAnswer['message'][] = wfMessage( 'bs-usermanager-user-deleted' )->plain();

		return FormatJson::encode( $aAnswer );
	}

	public static function setUserGroups( $aUserIds, $aGroups ) {
		$res = $resDelGroups = $resInsGroups = $resERealUser = false;

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'message' => array()
		);

		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$aAnswer['success'] = false;
			$aAnswer['message'][] =  wfMessage( 'bs-readonly', $wgReadOnly )->plain();
		}

		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] =  wfMessage( 'bs-wikiadmin-notallowed' )->plain();
		}

		global $wgUser;
		if (
			in_array( $wgUser->getId(), $aUserIds ) &&
			in_array( 'sysop', $wgUser->getEffectiveGroups() ) &&
			!in_array( 'sysop', $aGroups )
		) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array(
				'id' => 'groups',
				'message' => wfMessage( 'bs-usermanager-no-self-desysop' )->plain()
			);
		}

		if ( $aAnswer['success'] ) {
			$dbw = wfGetDB( DB_MASTER );
			$resDelGroups = $dbw->delete( 'user_groups',
				array(
					'ug_user' => $aUserIds
				)
			);

			$resInsGroups = true;
			if( is_array( $aGroups ) ) {
				foreach ( $aGroups as $sGroup ) {
					if ( in_array( $sGroup, self::$excludegroups ) ) {
						continue;
					}
					foreach( $aUserIds as $iUserId ) {
						$resInsGroups = $dbw->insert(
								'user_groups',
								array(
									'ug_user' => (int)$iUserId,
									'ug_group' => addslashes( $sGroup )
								)
						);
						if( $resInsGroups === false ) {
							break;
						}
					}
				}
			}
		}

		if ( $resDelGroups === false || $resInsGroups === false ) {
			$aAnswer['success'] = false;
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-db-error' )->plain();
		}

		if ( $aAnswer['success'] ) {
			$aAnswer['message'][] = wfMessage( 'bs-usermanager-save-successful' )->plain();
		}

		return FormatJson::encode( $aAnswer );
	}

	public function getForm( $firsttime = false ) {
		$this->getOutput()->addModules( 'ext.bluespice.userManager' );
		return '<div id="bs-usermanager-grid"></div>';
	}
}