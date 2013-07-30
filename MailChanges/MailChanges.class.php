<?php
/**
 * MailChanges extension for BlueSpice
 *
 * Sends changes in the wiki via email.
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
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    1.22.0
 * @version    $Id: MailChanges.class.php 9745 2013-06-14 12:09:29Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage MailChanges
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 *
 * v1.1
 * - New user notification
 * - Shoutbox notification
 * v1.0.0
 * - Raised to stable
 * - Code review
 * v0.1
 * - initial release
 */

//Last Code review: RBV (30.06.2011)

/**
 * Base class for MailChanges extension
 * @package BlueSpice_Extensions
 * @subpackage MailChanges
 */
class MailChanges extends BsExtensionMW {

	/**
	 * Constructor of MailChanges class
	 */
	public function __construct() {
		wfProfileIn( 'BS::MailChanges::Construct' );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['MailChanges'] = dirname( __FILE__ ) . '/MailChanges.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::PARSERHOOK; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'MailChanges',
			EXTINFO::DESCRIPTION => 'Send changes in the wiki via email.',
			EXTINFO::AUTHOR      => 'Markus Glaser',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9745 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '1.22.0' )
		);
		$this->mExtensionKey = 'MW::MailChanges';
		wfProfileOut( 'BS::MailChanges::Construct' );
	}

	/**
	 * Initialization of ShoutBox extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::MailChanges::Init' );
		// Hooks
		$this->setHook( 'ArticleSaveComplete' );
		$this->setHook( 'ArticleDeleteComplete' );
		$this->setHook( 'TitleMoveComplete' );
		$this->setHook( 'BSUserManagerAfterAddUser' );
		$this->setHook( 'BSShoutBoxAfterInsertShout' );

		// Variables
		BsConfig::registerVar( 'MW::MailChanges::Active', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-mailchanges-pref-active', 'toggle' );
		BsConfig::registerVar( 'MW::MailChanges::NotifyNS', array( 0 ), BsConfig::LEVEL_USER|BsConfig::TYPE_ARRAY_INT|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-mailchanges-pref-notifyns', 'multiselectex' );
		BsConfig::registerVar( 'MW::MailChanges::NotifyNew', false, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL, 'bs-mailchanges-pref-notifynew', 'toggle' );
		BsConfig::registerVar( 'MW::MailChanges::NotifyEdit', false, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL, 'bs-mailchanges-pref-notifyedit', 'toggle' );
		BsConfig::registerVar( 'MW::MailChanges::NotifyMove', false, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL, 'bs-mailchanges-pref-notifymove', 'toggle' );
		BsConfig::registerVar( 'MW::MailChanges::NotifyDelete', false, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL, 'bs-mailchanges-pref-notifydelete', 'toggle' );
		BsConfig::registerVar( 'MW::MailChanges::NotifyNoMinor', false, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL, 'bs-mailchanges-pref-notifynominor', 'toggle' );
		BsConfig::registerVar( 'MW::MailChanges::NotifyUser', false, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL, 'bs-mailchanges-pref-notifyuser', 'toggle' );
		BsConfig::registerVar( 'MW::MailChanges::NotifyShout', false, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL, 'bs-mailchanges-pref-notifyshout', 'toggle' );
		wfProfileOut( 'BS::MailChanges::Init' );
	}

	/**
	 * Specification of values for NotifyNamespaces setting. Called by Preferences and UserPreferences
	 * @param string $sAdapterName Name of the adapter. Probably MW.
	 * @param BsConfig $oVariable The variable that is to be specified.
	 * @return array Option array of specifications.
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array(
					'type'    => 'multiselectex',
					'options' => BsAdapterMW::getNamespacesForSelectOptions( array( NS_SPECIAL, NS_MEDIA ) )
				);
		return $aPrefs;
	}

	/**
	 * Email notification for Shoutbox messages
	 * @param int $iArticleId ID of the article the message was posted to.
	 * @param int $iUserId ID of the user that posted the message.
	 * @param string $sNick Nickname of the user that posted the message.
	 * @param string $sMessage The message posted.
	 * @param string $sTimestamp Time when the message was posted.
	 * @return boolean Allow other binds to this hook to be executed. Always true.
	 */
	public function onBSShoutBoxAfterInsertShout( $iArticleId, $iUserId, $sNick, $sMessage, $sTimestamp ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		global $wgUser;
		if( $wgUser->isAllowed( 'bot' ) ) return true;
		if ( BsConfig::get( 'MW::MailChanges::Active' ) === false ) return true;

		$oTitle       = Title::newFromID( $iArticleId );
		$oFromUser    = User::newFromId( $iUserId );
		$aRecipients  = $this->getUsersFor( 'MW::MailChanges::NotifyShout', $oTitle, true );

		foreach ( $aRecipients as $oNotifyUser ) {
			if ( $oNotifyUser->getEmail() && WatchedItem::fromUserTitle( $oNotifyUser, $oTitle )->isWatched() ) {

				$oTo           = $oNotifyUser;
				$sFromUserName = BsAdapterMW::getUserDisplayName( $oFromUser );
				$sUserLang     = $oNotifyUser->getOption( 'language' );

				$sSubject = wfMsgExt(
									'bs-mailchanges-email-shout-subject',
									array( 'language' => $sUserLang ), 
									$oTitle->getFullText(),
									$sFromUserName
							);
				$sBody    = wfMsgExt(
									'bs-mailchanges-email-shout',
									array( 'language' => $sUserLang ), 
									$oTitle->getFullText(),
									$sFromUserName,
									$sMessage,
									$oTitle->getCanonicalURL(),
									''
							);

				BsMailer::getInstance( 'MW' )->send( $oTo, $sSubject, $sBody );
			}
		}
		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}
	
	// TODO RBV (30.06.11 09:51): Coding Conventions for parameters.
	/**
	 * Sends an email on article creation and edit.
	 * @param Article $article The article that is created.
	 * @param User $user User that saved the article.
	 * @param string $text New text.
	 * @param string $summary Edit summary.
	 * @param bool $minoredit Marked as minor.
	 * @param bool $watchthis Put on watchlist.
	 * @param int $sectionanchor Not in use any more.
	 * @param int $flags Bitfield.
	 * @param Revision $revision New revision object.
	 * @param Status $status Status object (since MW1.14)
	 * @param int $baseRevId Revision ID this edit is based on (since MW1.15)
	 * @param bool $redirect Redirect user back to page after edit (since MW1.17)
	 * @return bool allow other hooked methods to be executed. Always true
	 */
	function onArticleSaveComplete( $article, $user, $text, $summary, $minoredit, $watchthis, $sectionanchor, $flags, $revision, $status, $baseRevId, $redirect = false ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if( $user->isAllowed( 'bot' ) ) return true;
		if ( BsConfig::get( 'MW::MailChanges::Active' ) === false ) return true;

		$oTitle = $article->getTitle();

		if ( $minoredit && BsConfig::get( 'MW::MailChanges::NotifyNoMinor' ) ) {
			return true;
		}

		if ( $flags & EDIT_NEW ) {
			$aRecipients = $this->getUsersFor( 'MW::MailChanges::NotifyNew', $oTitle );
		} else {
			$aRecipients = $this->getUsersFor( 'MW::MailChanges::NotifyEdit', $oTitle );
		}

		foreach ( $aRecipients as $oNotifyUser ) {
			if ( $oNotifyUser->getEmail() ) {

				$oTo           = $oNotifyUser;
				$oFromUser     = $user; //User::newFromId($article->getUser());
				$sFromUserName = BsAdapterMW::getUserDisplayName( $oFromUser );
				$sUserLang     = $oNotifyUser->getOption( 'language' );

				if ( $flags & EDIT_NEW ) {
					$sSubject = wfMsgExt(
										'bs-mailchanges-email-new-subject',
										array( 'language' => $sUserLang ), 
										$oTitle->getFullText(),
										$sFromUserName
								);
					$sBody    = wfMsgExt(
										'bs-mailchanges-email-new',
										array( 'language' => $sUserLang ), 
										$oTitle->getFullText(),
										$sFromUserName,
										$summary,
										$oTitle->getCanonicalURL()
								);
				} else {
					$sSubject = wfMsgExt(
										'bs-mailchanges-email-edit-subject',
										array( 'language' => $sUserLang ), 
										$oTitle->getFullText(),
										$sFromUserName
								);
					$sBody    = wfMsgExt(
										'bs-mailchanges-email-edit',
										array( 'language' => $sUserLang ), 
										$oTitle->getFullText(),
										$sFromUserName,
										$summary,
										$oTitle->getCanonicalURL(),
										is_object( $revision ) ? $oTitle->getCanonicalURL( array( 'diff' => $revision->getId(), 'oldid' => $revision->getPrevious()->getId() ) ) : 'null'
								);
				}

				BsMailer::getInstance( 'MW' )->send( $oTo, $sSubject, $sBody );
			}
		}
		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Sends an email notification on article deletion
	 * @param Article $article The article that is being deleted.
	 * @param User $user The user that deletes.
	 * @param string $reason A reason for article deletion
	 * @param int $id Id of article that was deleted.
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onArticleDeleteComplete( &$article, &$user, $reason, $id ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if( $user->isAllowed( 'bot' ) ) return true;
		if ( BsConfig::get( 'MW::MailChanges::Active' ) === false ) return true;

		$oTitle = $article->getTitle();

		$aRecipients = $this->getUsersFor( 'MW::MailChanges::NotifyDelete', $oTitle );

		foreach ( $aRecipients as $oNotifyUser ) {
			if ( $oNotifyUser->getEmail() ) {

				$oTo           = $oNotifyUser;
				$oFromUser     = $user; //User::newFromId($article->getUser());
				$sFromUserName = BsAdapterMW::getUserDisplayName( $oFromUser );
				$sUserLang     = $oNotifyUser->getOption( 'language' );

				$sSubject = wfMsgExt(
									'bs-mailchanges-email-delete-subject',
									array( 'language' => $sUserLang ), 
									$oTitle->getFullText(),
									$sFromUserName
							);
				$sBody    = wfMsgExt(
									'bs-mailchanges-email-delete',
									array( 'language' => $sUserLang ), 
									$oTitle->getFullText(),
									$sFromUserName,
									$reason
							);

				BsMailer::getInstance( 'MW' )->send( $oTo, $sSubject, $sBody );
			}
		}
		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Sends an email notification when an article is moved.
	 * @param Title $oTitle Old title of the moved article.
	 * @param Title $newtitle New tite of the moved article.
	 * @param User $user User that moved the article.
	 * @param int $oldid ID of the page that has been moved.
	 * @param int $newid ID of the newly created redirect.
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onTitleMoveComplete( $oTitle, $newtitle, $user, $oldid, $newid ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if( $user->isAllowed( 'bot' ) ) return true;
		if ( BsConfig::get( 'MW::MailChanges::Active' ) === false ) return true;

		$aRecipients = $this->getUsersFor( 'MW::MailChanges::NotifyMove', $oTitle );

		foreach ( $aRecipients as $oNotifyUser ) {
			if ( $oNotifyUser->getEmail() ) {

				$oTo           = $oNotifyUser;
				$oFromUser     = $user; //User::newFromId($article->getUser());
				$sFromUserName = BsAdapterMW::getUserDisplayName( $oFromUser );
				$sUserLang     = $oNotifyUser->getOption( 'language' );

				$sSubject = wfMsgExt(
									'bs-mailchanges-email-move-subject',
									array( 'language' => $sUserLang ),
									$oTitle->getFullText(),
									$sFromUserName
							);
				$sBody    = wfMsgExt(
									'bs-mailchanges-email-move',
									array( 'language' => $sUserLang ),
									$oTitle->getFullText(),
									$sFromUserName,
									$newtitle->getFullText(),
									$newtitle->getCanonicalURL()
							);

				BsMailer::getInstance( 'MW' )->send( $oTo, $sSubject, $sBody );
			}
		}
		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Sends an email notification after adding an user.
	 * @param Object $oUserManager Object of BlueSpice UserManager
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onBSUserManagerAfterAddUser( UserManager $oUserManager, $oUser, $aUserDetails ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if( $oUser->isAllowed( 'bot' ) ) return true;
		if ( BsConfig::get( 'MW::MailChanges::Active' ) === false ) return true;

		$aRecipients = BsConfig::getUsersForVar( 'MW::MailChanges::NotifyUser' , true );
		$sUserDisplayName = BsAdapterMW::getUserDisplayName( $oUser );

		foreach ( $aRecipients as $oNotifyUser ) {
			if ( !in_array( 'sysop', $oNotifyUser->getEffectiveGroups( true ) ) ) {
				continue;
			}

			if ( $oNotifyUser->getEmail() ) {
				$oTo       = $oNotifyUser;
				$sUserLang = $oNotifyUser->getOption( 'language' );

				$sSubject = wfMsgExt(
									'bs-mailchanges-email-addaccount-subject',
									array( 'language' => $sUserLang ),
									$sUserDisplayName
							);
				$sBody    = wfMsgExt(
									'bs-mailchanges-email-addaccount',
									array( 'language' => $sUserLang ),
									$sUserDisplayName,
									$aUserDetails['username']
							);

				BsMailer::getInstance( 'MW' )->send( $oTo, $sSubject, $sBody );
			}
		}
		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Collect all users that need to be notified.
	 * @param string $sSettingsKey Key of the setting for notification on a certain action, e.g. MW::MailChanges::NotifyMove
	 * @param Title $oTitle MediaWiki Title object the notification is about.
	 * @param bool $bIgnoreWatchlist Send notification independently of watchlist status
	 * @return array List of users (MediaWiki User objects) that need to be notified.
	 */
	protected function getUsersFor( $sSettingsKey, $oTitle, $bIgnoreWatchlist = false ) {
		wfProfileIn( 'BS::'.__METHOD__ );

		$aResultUsers = array();
		$aUsers       = BsConfig::getUsersForVar( $sSettingsKey, true ); // only select users that have set notification
		$iNamespace   = $oTitle->getNamespace();

		foreach ( $aUsers as $oUser ) {
			$aUserNmsps = BsConfig::getVarForUser( 'MW::MailChanges::NotifyNS', $oUser->getName() ); //Should be array of ints
			//http://stackoverflow.com/questions/3559542/more-concise-way-to-check-to-see-if-an-array-contains-only-numbers-integers
			$bOnlyIntegers = array_filter( $aUserNmsps, 'is_int' ) === $aUserNmsps;

			if( !$bOnlyIntegers ) { //This should never be the case and is just for backwards compatibility
				try{
					$aUserNmsps = BsAdapterMW::getNamespaceIdsFromAmbiguousArray( $aUserNmsps );
				}
				catch( BsInvalidNamespaceException $e ) {
					wfDebug(__METHOD__.': The "MW::MailChanges::NotifyNS" setting of "'.$oUser->getName().'" contains invalid values - '.var_export($aUserNmsps, true) );
					$aUserNmsps = $e->getListOfValidNamespaces();
				}
			}

			if ( !in_array( $iNamespace, $aUserNmsps ) ) continue;

			// check read right for user
			$oGlobalUser = $this->mAdapter->get( 'User' );
			$this->mAdapter->set( 'User', $oUser );

			if ( !$oTitle->userCan( 'read' ) ) {
				$this->mAdapter->set( 'User', $oGlobalUser );
				continue;
			};

			$this->mAdapter->set( 'User', $oGlobalUser );

			// check if user is watching this title (will be notified via watchlist)
			if ( !$bIgnoreWatchlist && $oUser->isWatched( $oTitle ) ) continue;

			// Do not notify own actions
			if ( $oGlobalUser->getId() == $oUser->getId() ) continue;

			$aResultUsers[] = $oUser;
		}
		wfProfileOut( 'BS::'.__METHOD__ );

		return $aResultUsers;
	}

}
