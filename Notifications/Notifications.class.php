<?php
/**
 * Notifications extension for BlueSpice
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
 * @author     Stefan Widmann <widmann@hallowelt.biz>
 * @version    2.22.0

 * @package    BlueSpice_Extensions
 * @subpackage Notifications
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v2.22.0
 * - initial release
 */

/**
 * Base class for Notifications extension
 * @package BlueSpice_Extensions
 * @subpackage Notifications
 */
class Notifications extends BsExtensionMW {
	public $aEchoPrefix = array(
		'web'	=> 'echo-subscriptions-web-',
		'email'	=> 'echo-subscriptions-email-'
	);

	public static $aNotificationCategories = array(
		'bs-edit-cat' => array( 'priority' => 3 ),
		'bs-create-cat' => array( 'priority' => 3 ),
		'bs-delete-cat' => array( 'priority' => 3 ),
		'bs-move-cat' => array( 'priority' => 3 ),
		'bs-newuser-cat' => array( 'priority' => 3 ),
		'bs-shoutbox-cat' => array( 'priority' => 3 ),

	);

	/**
	 * Constructor of Notifications class
	 */
	public function __construct() {
		wfProfileIn( 'BS::Notifications::Construct' );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::PARSERHOOK; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'Notifications',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-notifications-desc' )->text(),
			EXTINFO::AUTHOR      => array( '[https://www.mediawiki.org/wiki/User:Swidmann Stefan Widmann]' ),
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::Notifications';
		wfProfileOut( 'BS::Notifications::Construct' );
	}

	/**
	 * Initialization of ShoutBox extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::Notifications::Init' );
		// Hooks
		$this->setHook( 'ArticleSaveComplete' );
		$this->setHook( 'ArticleDeleteComplete' );
		$this->setHook( 'TitleMoveComplete' );
		$this->setHook( 'BSUserManagerAfterAddUser' );
		$this->setHook( 'BSShoutBoxAfterInsertShout' );
		$this->setHook( 'BeforeCreateEchoEvent' );
		$this->setHook( 'EchoGetDefaultNotifiedUsers' );
		$this->setHook( 'GetPreferences' );
		$this->setHook( 'UserSaveOptions' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );

		// Variables
		BsConfig::registerVar( 'MW::Notifications::Active', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-notifications-pref-active', 'toggle' );
		BsConfig::registerVar( 'MW::Notifications::NotifyNS', array( 0 ), BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_ARRAY_INT|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-notifications-pref-notifyns', 'multiselectex' );
		BsConfig::registerVar( 'MW::Notifications::NotifyNoMinor', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-notifications-pref-notifynominor', 'toggle' );
		wfProfileOut( 'BS::Notifications::Init' );
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
					'options' => BsNamespaceHelper::getNamespacesForSelectOptions( array( NS_SPECIAL, NS_MEDIA ) )
				);
		return $aPrefs;
	}

	/**
	 * Adds Notifications preferences to the echo section
	 * @param User $user
	 * @param array $preferences
	 * @return boolean
	 */
	public function onGetPreferences( $user, array &$preferences ) {

		$preferences['MW::Notifications::NotifyNoMinor'] = array(
			'type'			=> 'toggle',
			'label-message'	=> 'bs-notifications-pref-notifynominor',
			'section'		=> 'echo/echo-extended',
		);

		$preferences['MW::Notifications::Active'] = array(
			'type'			=> 'toggle',
			'label-message'	=> 'bs-notifications-pref-active',
			'section'		=> 'echo/echo-extended',
			'default'		=> true,
		);

		// ugly workaraound for mw's handling of get default options from multivaluefields
		$sNotifyDefault = ( $user->getOption( 'MW::Notifications::NotifyNS', false ) ) ? $user->getOption( 'MW::Notifications::NotifyNS' ) : array(0);

		$preferences['MW::Notifications::NotifyNS'] = array(
			'type'			=> 'multiselectex',
			'label-message'	=> 'bs-notifications-pref-notifyns',
			'section'		=> 'echo/echo-extended',
			'options'		=> BsNamespaceHelper::getNamespacesForSelectOptions( array( NS_SPECIAL, NS_MEDIA ) ),
			'default'		=> $sNotifyDefault,
		);

		return true;
	}

	/**
	 * Get subscribers for the echo notifications
	 * @param EchoEvent $event
	 * @param type $users
	 * @return boolean
	 */
	public function onEchoGetDefaultNotifiedUsers( $event, &$users ) {
		$aTmpUsers = array_unique(
			array_merge(
				// e.g. echo-subscriptions-email-bs-cat-edit
				BsConfig::getUsersForVar( $this->aEchoPrefix['web'].$event->getType().'-cat' , '1', false, false ),
				BsConfig::getUsersForVar( $this->aEchoPrefix['email'].$event->getType().'-cat', '1', false, false )
			)
		);

		foreach ( $aTmpUsers as $index => $user ) {
			if ( !$user->getOption( 'MW::Notifications::Active', false ) ) continue;
			if( $event->getTitle() instanceof Title ) {
				if ( !$event->getTitle()->userCan( 'read', $user ) ) continue;
				if ( is_array( $user->getOption( 'MW::Notifications::NotifyNS', array() ) ) ) {
					if ( !in_array( $event->getTitle()->getNamespace(), $user->getOption( 'MW::Notifications::NotifyNS', array() ) ) ) continue;
				}
			}
			if( $event->getAgent() instanceof User ) {
				if ( $event->getAgent()->getRequest()->getVal( 'wpMinoredit', false ) && $user->getOption( 'MW::Notifications::NotifyNoMinor', false ) ) continue;
			}
			$users[] = $user;
		}

		return true;
	}

	public function onBeforeCreateEchoEvent( &$notifications, &$notificationCategories, &$icons ) {
		$sIconPath = 'BlueSpiceExtensions/Notifications/resources/icons/';
		$icons = array_merge( $icons, array(
			'bs-edit' => array(
				'path' => $sIconPath.'edit.png'
			),
			'bs-create' => array(
				'path' => $sIconPath.'create.png'
			),
			'bs-delete' => array(
				'path' => $sIconPath.'delete.png'
			),
			'bs-move' => array(
				'path' => $sIconPath.'move.png'
			),
			'bs-newuser' => array(
				'path' => $sIconPath.'newuser.png'
			),
			'bs-shoutbox' => array(
				'path' => $sIconPath.'shoutbox.png'
			),
		) );
		// category definition via self::$aNotificationCategories
		//  HINT: http://www.mediawiki.org/wiki/Echo_(Notifications)/Developer_guide#Notification_category_parameters
		foreach( self::$aNotificationCategories as $sCategory => $aCategoryDefinition ) {
			$notificationCategories[$sCategory] = $aCategoryDefinition;
		}

		$notifications['bs-edit'] = array( // HINT: http://www.mediawiki.org/wiki/Echo_(Notifications)/Developer_guide#Defining_a_notification
			'category' => 'bs-edit-cat',
			'group' => 'neutral',
			'formatter-class' => 'BsNotificationsFormatter',
			'title-message' => 'bs-echo-page-edit',
			'title-params' => array( 'title' ),
			'flyout-message' => 'bs-notifications-email-edit-subject',
			'flyout-params' => array( 'titlelink', 'agentlink' ),
			'email-subject-message' => 'bs-notifications-email-edit-subject',
			'email-subject-params' => array( 'title', 'agent' ),
			'email-body-message' => 'bs-notifications-email-edit',
			'email-body-params' => array( 'title', 'agent', 'summary', 'titlelink', 'difflink' ),
			'email-body-batch-message' => 'bs-notifications-email-edit',
			'email-body-batch-params' => array( 'title', 'agent', 'summary', 'titlelink', 'difflink' ),
			'icon' => 'bs-edit',
		);

		$notifications['bs-create'] = array(
			'category' => 'bs-create-cat',
			'group' => 'neutral',
			'formatter-class' => 'BsNotificationsFormatter',
			'title-message' => 'bs-echo-page-create',
			'title-params' => array( 'title' ),
			'flyout-message' => 'bs-notifications-email-new-subject',
			'flyout-params' => array( 'titlelink', 'agentlink' ),
			'email-subject-message' => 'bs-notifications-email-new-subject',
			'email-subject-params' => array( 'title', 'agent' ),
			'email-body-message' => 'bs-notifications-email-new',
			'email-body-params' => array( 'title', 'agent', 'summary', 'titlelink', 'difflink' ),
			'email-body-batch-message' => 'bs-notifications-email-new',
			'email-body-batch-params' => array( 'title', 'agent', 'summary', 'titlelink', 'difflink' ),
			'icon' => 'bs-create',
		);

		$notifications['bs-delete'] = array(
			'category' => 'bs-delete-cat',
			'group' => 'neutral',
			'formatter-class' => 'BsNotificationsFormatter',
			'title-message' => 'bs-echo-page-delete',
			'title-params' => array( 'title' ),
			'flyout-message' => 'bs-notifications-email-delete-subject',
			'flyout-params' => array( 'titlelink', 'agentlink' ),
			'email-subject-message' => 'bs-notifications-email-delete-subject',
			'email-subject-params' => array( 'title', 'agent' ),
			'email-body-message' => 'bs-notifications-email-delete',
			'email-body-params' => array( 'titlelink', 'agent', 'deletereason' ),
			'email-body-batch-message' => 'bs-notifications-email-delete',
			'email-body-batch-params' => array( 'titlelink', 'agent', 'deletereason' ),
			'icon' => 'bs-delete',
		);

		$notifications['bs-move'] = array(
			'category' => 'bs-move-cat',
			'group' => 'neutral',
			'formatter-class' => 'BsNotificationsFormatter',
			'title-message' => 'bs-echo-page-move',
			'title-params' => array( 'title' ),
			'flyout-message' => 'bs-notifications-email-move-subject',
			'flyout-params' => array( 'title', 'agentlink', 'newtitlelink' ),
			'email-subject-message' => 'bs-notifications-email-move-subject',
			'email-subject-params' => array( 'title', 'agent', 'newtitle' ),
			'email-body-message' => 'bs-notifications-email-move',
			'email-body-params' => array( 'title', 'agent', 'newtitle', 'newtitlelink' ),
			'email-body-batch-message' => 'bs-notifications-email-move',
			'email-body-batch-params' => array( 'title', 'agent', 'newtitle', 'newtitlelink' ),
			'icon' => 'bs-move',
		);

		$notifications['bs-newuser'] = array(
			'category' => 'bs-newuser-cat',
			'group' => 'neutral',
			'formatter-class' => 'BsNotificationsFormatter',
			'title-message' => 'bs-echo-page-newuser',
			'title-params' => array( 'userlink' ),
			'flyout-message' => 'bs-notifications-email-addaccount-subject',
			'flyout-params' => array( 'userlink' ),
			'email-subject-message' => 'bs-notifications-email-addaccount-subject',
			'email-subject-params' => array( 'user' ),
			'email-body-message' => 'bs-notifications-email-addaccount',
			'email-body-params' => array( 'userlink' ),
			'email-body-batch-message' => 'bs-notifications-email-addaccount',
			'email-body-batch-params' => array( 'userlink' ),
			'icon' => 'bs-newuser',
		);

		$notifications['bs-shoutbox'] = array(
			'category' => 'bs-shoutbox-cat',
			'group' => 'neutral',
			'formatter-class' => 'BsNotificationsFormatter',
			'title-message' => 'bs-echo-page-shoutbox',
			'title-params' => array( 'title' ),
			'flyout-message' => 'bs-notifications-email-shout-subject',
			'flyout-params' => array( 'titlelink', 'agentlink' ),
			'email-subject-message' => 'bs-notifications-email-shout-subject',
			'email-subject-params' => array( 'title', 'agent' ),
			'email-body-message' => 'bs-notifications-email-shout',
			'email-body-params' => array( 'title', 'agent', 'shoutmsg', 'titlelink' ),
			'email-body-batch-message' => 'bs-notifications-email-shout',
			'email-body-batch-params' => array( 'title', 'agent', 'shoutmsg', 'titlelink' ),
			'icon' => 'bs-shoutbox',
		);
		return true;
	}

	public function onUserSaveOptions( User $user, array &$options ) {
		if( isset( $options['MW::Notifications::NotifyNS'] ) ) {
			$options['MW::Notifications::NotifyNS'] = serialize( $options['MW::Notifications::NotifyNS'] );
		}

		return true;
	}

	/**
	 * Notification for Shoutbox messages
	 * @param int $iArticleId ID of the article the message was posted to.
	 * @param int $iUserId ID of the user that posted the message.
	 * @param string $sNick Nickname of the user that posted the message.
	 * @param string $sMessage The message posted.
	 * @param string $sTimestamp Time when the message was posted.
	 * @return boolean Allow other binds to this hook to be executed. Always true.
	 */
	public function onBSShoutBoxAfterInsertShout( $iArticleId, $iUserId, $sNick, $sMessage, $sTimestamp ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		global $wgUser; // TODO SW: use user id
		if ( $wgUser->isAllowed( 'bot' ) ) return true;

		EchoEvent::create( array(
			'type' => 'bs-shoutbox',
			'title' => Title::newFromID( $iArticleId ),
			'agent'	=> $wgUser,
			'extra' => array(
				'shoutmsg' => $sMessage
			)
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	// TODO RBV (30.06.11 09:51): Coding Conventions for parameters.
	/**
	 * Sends a notification on article creation and edit.
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
		if ( $user->isAllowed( 'bot' ) ) return true;
		if ( $article->getTitle()->getNamespace() === NS_USER_TALK ) return true;

		if( $flags & EDIT_NEW ) {
			EchoEvent::create( array(
				'type' => 'bs-create',
				'title' => $article->getTitle(),
				'agent'	=> $user,
				'extra'	=> array(
					'summary'	=>	$summary,
					'titlelink' => true,
				),
			) );
			return true;
		}

		EchoEvent::create( array(
			'type' => 'bs-edit',
			'title' => $article->getTitle(),
			'agent'	=> $user,
			'extra'	=> array(
					'summary'	=>	$summary,
					'titlelink'	=>	true,
					'difflink'	=>	is_object( $revision ) ? array( 'diffparams' => array( 'diff' => $revision->getId(), 'oldid' => $revision->getPrevious()->getId() ) ): array( 'diffparams' => array() ),
					'agentlink' => true,
				),
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Sends a notification on article deletion
	 * @param Article $article The article that is being deleted.
	 * @param User $user The user that deletes.
	 * @param string $reason A reason for article deletion
	 * @param int $id Id of article that was deleted.
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onArticleDeleteComplete( &$article, &$user, $reason, $id ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if ( $user->isAllowed( 'bot' ) ) return true;
		EchoEvent::create( array(
			'type' => 'bs-delete',
			'title' => $article->getTitle(),
			'agent'	=> $user,
			'extra' => array(
				'deletereason' => $reason
			),
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Sends a notification when an article is moved.
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
		EchoEvent::create( array(
			'type' => 'bs-move',
			'title' => $oTitle,
			'agent'	=> $user,
			'extra' => array(
				'newtitle' => $newtitle,
			)
		) );

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Sends a notification after adding an user.
	 * @param Object $oUserManager Object of BlueSpice UserManager
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onBSUserManagerAfterAddUser( UserManager $oUserManager, $oUser, $aUserDetails ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		if( $oUser->isAllowed( 'bot' ) ) return true;
		EchoEvent::create( array(
			'type' => 'bs-newuser',
			// TODO SW: implement own notifications formatter
			'extra'	=> array(
				'user'	=> $oUser->getName(),
				'userlink'	=> true,
			)
		) );


		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Moves Notification link from personal_urls to special bs_personal_info
	 * @param SkinTemplate $sktemplate
	 * @param BaseTemplate $tpl
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateOutputPageBeforeExec(&$sktemplate, &$tpl){
		if (!isset($tpl->data['personal_urls']['notifications'])) {
			return true;
		}


		$tpl->data['bs_personal_info'][10] = array(
			'id' => 'pt-notifications',
			'class' => 'icon-bell2',
		) + $tpl->data['personal_urls']['notifications'];

		if( isset( $tpl->data['personal_urls']['notifications']['text'] ) && $tpl->data['personal_urls']['notifications']['text'] > 0 ) {
			$tpl->data['bs_personal_info'][10]['active'] = true;
		}

		unset($tpl->data['personal_urls']['notifications']);

		return true;
	}

	public function onBeforePageDisplay( OutputPage &$out, Skin &$skin ) {
		$out->addModuleStyles( array(
				'ext.bluespice.notifications'
			)
		);
		return true;
	}

}
