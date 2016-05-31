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
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
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
			EXTINFO::DESCRIPTION => 'bs-usermanager-desc',
			EXTINFO::AUTHOR      => 'Markus Glaser, Stephan Muggli',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'https://help.bluespice.com/index.php/UserManager',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);

		WikiAdmin::registerModule( 'UserManager', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_usermanagement_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-usermanager-label'
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Adds an user
	 * @param string $sUserName
	 * @param array $aMetaData
	 * @return Status
	 */
	public static function addUser( $sUserName, $aMetaData = array() ) {
		//This is to overcome username case issues with custom AuthPlugin (i.e. LDAPAuth)
		//LDAPAuth woud otherwise turn the username to first-char-upper-rest-lower-case
		//At the end of this method we switch $_SESSION['wsDomain'] back again
		$tmpDomain = isset( $_SESSION['wsDomain'] ) ? $_SESSION['wsDomain'] : '';
		$_SESSION['wsDomain'] = 'local';

		$sUserName = ucfirst( $sUserName );
		$oUser = User::newFromName( $sUserName, true );
		if ( !$oUser ) {
			return Status::newFatal( 'bs-usermanager-invalid-uname' );
		}
		if( $oUser->getId() !== 0 ) {
			return Status::newFatal( 'bs-usermanager-user-exists' );
		}

		$oStatus = self::editUser( $oUser, $aMetaData, true );
		if( !$oStatus->isOK() ) {
			return $oStatus;
		}

		$_SESSION['wsDomain'] = $tmpDomain;

		$oUser = $oStatus->getValue();
		$oUserManager = BsExtensionManager::getExtension( 'UserManager' );
		Hooks::run(
			'BSUserManagerAfterAddUser',
			array(
				$oUserManager,
				$oUser,
				$aMetaData,
				&$oStatus
			)
		);

		$ssUpdate = new SiteStatsUpdate( 0, 0, 0, 0, 1 );
		$ssUpdate->doUpdate();

		return $oStatus;
	}

	/**
	 * Edits or adds an user
	 * @param User $oUser
	 * @param array $aMetaData
	 * @param boolean $bCreateIfNotExists
	 * @return Status
	 */
	public static function editUser( User $oUser, $aMetaData = array(), $bCreateIfNotExists = false ) {
		$oStatus = Status::newGood();
		$bNew = false;

		if ( $oUser->getId() === 0  ) {
			if( !$bCreateIfNotExists ) {
				$oStatus->merge(
					Status::newFatal( 'bs-usermanager-idnotexist' )
				);
				return $oStatus;
			}
			$bNew = true;
		}
		$sPass = $aMetaData['password'];
		if ( !empty( $aMetaData['password'] ) || $bNew ) {
			if( !$oUser->isValidPassword( $sPass ) ) {
				$oNewStatus = Status::newFatal( 'bs-usermanager-invalid-pwd' );
				$oStatus->merge( $oNewStatus );
			}
			if ( strtolower( $oUser->getName() ) == strtolower( $sPass ) ) {
				$oNewStatus = Status::newFatal( 'password-name-match' );
				$oStatus->merge( $oNewStatus );
			}
			$sRePass = $aMetaData['repassword'];
			if ( !isset($sRePass) || $sPass !== $sRePass ) {
				$oNewStatus = Status::newFatal( 'badretype' );
				$oStatus->merge( $oNewStatus );
			}
		}

		if( !empty($aMetaData['realname']) ) {
			if ( strpos( $aMetaData['realname'], '\\' ) ) {
				$oNewStatus = Status::newFatal(
					'bs-usermanager-invalid-realname'
				);
				$oStatus->merge( $oNewStatus );
			}
		}
		if( !empty($aMetaData['email']) ) {
			if ( Sanitizer::validateEmail( $aMetaData['email'] ) === false ) {
				$oNewStatus = Status::newFatal(
					'bs-usermanager-invalid-email-gen'
				);
				$oStatus->merge( $oNewStatus );
			}
		}
		if( !$oStatus->isOK() ) {
			return $oStatus;
		}

		if( $bNew ) {
			$oUser->addToDatabase();
			$oUser->setToken();
		}

		if( !empty($sPass) ) {
			$oUser->setPassword( $sPass );
		}
		if( !empty($aMetaData['email']) ) {
			$oUser->setEmail( $aMetaData['email'] );
		} else {
			$oUser->setEmail('');
		}
		if( !empty($aMetaData['realname']) ) {
			$oUser->setRealName( $aMetaData['realname'] );
		} else {
			$oUser->setRealName('');
		}

		$oUser->saveSettings();

		$oUserManager = BsExtensionManager::getExtension( 'UserManager' );
		Hooks::run(
			'BSUserManagerAfterEditUser',
			array(
				$oUserManager,
				$oUser,
				$aMetaData,
				&$oStatus
			)
		);

		return Status::newGood( $oUser );
	}

	/**
	 * Deletes an user form the database
	 * TODO: Merge into DeleteUser
	 * @param User $oUser
	 * @return Status
	 */
	public static function deleteUser( User $oUser ) {
		if ( $oUser->getId() == 0 ) {
			return Status::newFatal( 'bs-usermanager-idnotexist' );
		}

		if ( $oUser->getId() == 1 ) {
			return Status::newFatal( 'bs-usermanager-admin-nodelete' );
		}

		$oLoggedInUser = RequestContext::getMain()->getUser();
		if ( $oUser->getId() == $oLoggedInUser->getId() ) {
			return Status::newFatal( 'bs-usermanager-self-nodelete' );
		}

		$oStatus = Status::newGood( $oUser );
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
			$oStatus->merge( Status::newFatal( 'bs-usermanager-db-error' ) );
		}

		$oUserManager = BsExtensionManager::getExtension( 'UserManager' );
		Hooks::run( 'BSUserManagerAfterDeleteUser', array(
			$oUserManager,
			$oUser,
			&$oStatus
		));

		return $oStatus;
	}

	/**
	 * Removes / adds groups to a user
	 * @param User $oUser
	 * @param type $aGroups
	 * @return type
	 */
	public static function setGroups( User $oUser, $aGroups = array() ) {
		$oLoggedInUser = RequestContext::getMain()->getUser();

		$bCheckDeSysop = $oLoggedInUser->getId() == $oUser->getId()
			&& in_array( 'sysop', $oLoggedInUser->getEffectiveGroups() )
			&& !in_array( 'sysop', $aGroups )
		;
		if ( $bCheckDeSysop ) {
			return Status::newFatal( 'bs-usermanager-no-self-desysop' );
		}

		$aCurrentGroups = $oUser->getEffectiveGroups();
		$aSetGroups = array_diff( $aGroups, $aCurrentGroups );
		$aRemoveGroups = array_diff( $aCurrentGroups, $aGroups );

		foreach ( $aSetGroups as $sGroup ) {
			if ( in_array( $sGroup, self::$excludegroups ) ) {
				continue;
			}
			$oUser->addGroup( $sGroup );
		}
		foreach ( $aRemoveGroups as $sGroup ) {
			if ( in_array( $sGroup, self::$excludegroups ) ) {
				continue;
			}
			$oUser->removeGroup( $sGroup );
		}

		$oStatus = Status::newGood( $oUser );
		Hooks::run( 'BSUserManagerAfterSetGroups', array(
			$oUser,
			$aGroups,
			$aSetGroups,
			$aRemoveGroups,
			self::$excludegroups,
			&$oStatus
		));

		$oUser->invalidateCache();
		return $oStatus;
	}

	public function getForm( $firsttime = false ) {
		$this->getOutput()->addModules( 'ext.bluespice.userManager' );
		return '<div id="bs-usermanager-grid"></div>';
	}
}
