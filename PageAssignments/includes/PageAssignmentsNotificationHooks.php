<?php

class PageAssignmentsNotificationHooks {

	/**
	 * Notifications:
	 * - A user gets assigned to a page --> notify user
	 * - A Group gets assigned to a page --> notify all users in group
	 * - A page gets edited --> notify all assignees
	 * - A page gets deleted --> notify all assignees
	 * - A page gets moved --> notify all assignees
	 * - A user gets added to a group --> notify user about potential change of assignments
	 * - A user gets removed from a group --> notify user about potential change of assignments
	 */


	public static function setup() {

		BSNotifications::registerNotificationCategory( 'bs-pageassignments-action-cat' );
		$aExtraParams = array(
			'formatter-class' => 'PageAssignmentsNotificationFormatter',
			'primary-link' => array( 'message' => 'notification-link-text-view-page', 'destination' => 'title' )
		);

		BSNotifications::registerNotification(
			'notification-bs-pageassignments-assignment-change-add',
			'bs-pageassignments-action-cat',
			'notification-bs-pageassignments-assignment-change-add-summary',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-assignment-change-add-subject',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-assignment-change-add-body',
			array('agent', 'title', 'titlelink'),
			$aExtraParams
		);

		BSNotifications::registerNotification(
			'notification-bs-pageassignments-assignment-change-remove',
			'bs-pageassignments-action-cat',
			'notification-bs-pageassignments-assignment-change-remove-summary',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-assignment-change-remove-subject',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-assignment-change-remove-body',
			array('agent', 'title', 'titlelink'),
			$aExtraParams
		);

		BSNotifications::registerNotification(
			'bs-pageassignments-page-move',
			'bs-pageassignments-action-cat',
			'notification-bs-pageassignments-page-move-summary',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-page-move-subject',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-page-move-body',
			array('agent', 'title', 'titlelink'),
			$aExtraParams
		);

		BSNotifications::registerNotification(
			'bs-pageassignments-page-edit',
			'bs-pageassignments-action-cat',
			'notification-bs-pageassignments-page-edit-summary',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-page-edit-subject',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-page-edit-body',
			array('agent', 'title', 'titlelink'),
			$aExtraParams
		);

		BSNotifications::registerNotification(
			'bs-pageassignments-page-delete',
			'bs-pageassignments-action-cat',
			'notification-bs-pageassignments-page-delete-summary',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-page-delete-subject',
			array('agent', 'title', 'titlelink'),
			'notification-bs-pageassignments-page-delete-body',
			array('agent', 'title', 'titlelink'),
			$aExtraParams
		);

		BSNotifications::registerNotification(
			'bs-pageassignments-user-group-add',
			'bs-pageassignments-action-cat',
			'notification-bs-pageassignments-user-group-add-summary',
			array( 'agent', 'title', 'titlelink', 'group', 'groupcount' ),
			'notification-bs-pageassignments-user-group-add-subject',
			array( 'agent', 'title', 'titlelink', 'group', 'groupcount' ),
			'notification-bs-pageassignments-user-group-add-body',
			array( 'agent', 'title', 'titlelink', 'group', 'groupcount' )
		);

		BSNotifications::registerNotification(
			'bs-pageassignments-user-group-remove',
			'bs-pageassignments-action-cat',
			'notification-bs-pageassignments-user-group-remove-summary',
			array( 'agent', 'title', 'titlelink', 'group', 'groupcount' ),
			'notification-bs-pageassignments-user-group-remove-subject',
			array( 'agent', 'title', 'titlelink', 'group', 'groupcount' ),
			'notification-bs-pageassignments-user-group-remove-body',
			array( 'agent', 'title', 'titlelink', 'group', 'groupcount' )
		);

		array_unshift(
		  $GLOBALS[ 'wgHooks' ]['userCan'], "PageAssignmentsUsersAdditionalPermissionsHooks::onUserCan"
		);
	}

	/**
	 * As hook "EchoGetDefaultNotifiedUsers" is to late to add arbitrary
	 * 'extra' to an event, we bild out own notification interface here
	 * @param string $sKey
	 * @param User $oAgent
	 * @param Title $oTitle
	 * @param array $aExtraParams
	 */
	public static function notify( $sKey, $oAgent = null, $oTitle = null, $aExtraParams = array() ) {
		$aAssignedUserIds = PageAssignments::resolveAssignmentsToUserIdsWithSource( $oTitle );
		$aAffectedUsers = array();
		foreach( $aAssignedUserIds as $iUserId => $oAssignable ) {
			$aAffectedUsers[] = $iUserId;
		}

		$aExtraParams += array(
			'affected-users' => $aAffectedUsers,
			//This is required to have personalized messages that tell the
			//recipient _why_ he/she receives the notification
			'assignment-sources' => $aAssignedUserIds,
			'formatter-class' => 'PageAssignmentsNotificationFormatter'
		);

		BSNotifications::notify( $sKey, $oAgent, $oTitle, $aExtraParams );
	}

	/**
	 * Hook handler for MediaWiki 'ArticleDeleteComplete' hook.
	 * @param WikiPage $wikipage
	 * @param user $user
	 * @param string $reason
	 * @param int $id
	 * @param Content $content
	 * @param ManualLogEntry $logEntry
	 * @return boolean Always true to keep other hooks running.
	 */
	public static function onArticleDeleteComplete( &$wikipage, &$user, $reason, $id, $content, $logEntry ) {
		PageAssignmentsNotificationHooks::notify( 'bs-pageassignments-page-delete', $user, $wikipage->getTitle() );
		return true;
	}

	/**
	 *
	 * @param Article $article
	 * @param user $user
	 * @param Content $content
	 * @param string $summary
	 * @param bool $minoredit
	 * @param bool $watchthis
	 * @param int $sectionanchor
	 * @param oint $flags
	 * @param Revision $revision
	 * @param Status $status
	 * @param int $baseRevId
	 * @return boolean
	 */
	public static function onPageContentSaveComplete( &$article, &$user, $content, $summary, $minoredit, $watchthis, $sectionanchor, &$flags, $revision, &$status, $baseRevId ) {
		PageAssignmentsNotificationHooks::notify( 'bs-pageassignments-page-edit', $user, $article->getTitle() );
		return true;
	}

	/**
	 *
	 * @param Title $title
	 * @param Title $newtitle
	 * @param User $user
	 * @param int $oldid
	 * @param int $newid
	 * @return boolean
	 */
	public static function onTitleMoveComplete( &$title, &$newtitle, $user, $oldid, $newid ) {
		PageAssignmentsNotificationHooks::notify(
			'bs-pageassignments-page-move',
			$user,
			$title,
			array(
				'newtitle' => $newtitle
			)
		);
		return true;
	}

	/**
	 * Notifies user about possible assignment change
	 * @param User $oUser
	 * @param array $aGroups
	 * @param array $aSetGroups
	 * @param array $aRemoveGroups
	 * @param array $excludegroups
	 * @param Status $oStatus
	 * @return boolean
	 */
	public static function onBSUserManagerAfterSetGroups( User $oUser, $aGroups, $aSetGroups, $aRemoveGroups, $excludegroups, &$oStatus ) {
		if( !empty($aRemoveGroups) ) {
			self::onUserRemoveGroup(
				$oUser,
				implode( ', ', $aRemoveGroups ),
				count( $aRemoveGroups )
			);
		}
		if( !empty($aSetGroups) ) {
			self::onUserAddGroup(
				$oUser,
				implode( ', ', $aSetGroups ),
				count( $aSetGroups )
			);
		}

		return true;
	}

	/**
	 *
	 * @param User $user
	 * @param string $group
	 * @return boolean
	 */
	public static function onUserAddGroup( $user, $group, $iCount = 1 ) {
		PageAssignmentsNotificationHooks::notify(
			'bs-pageassignments-user-group-add',
			RequestContext::getMain()->getUser(),
			SpecialPage::getTitleFor( 'PageAssignments' ),
			array(
				'group' => $group,
				'groupcount' => $iCount,
				'affected-users' => array( $user )
			)
		);
		return true;
	}

	/**
	 *
	 * @param User $user
	 * @param string $group
	 * @return boolean
	 */
	public static function onUserRemoveGroup( $user, $group, $iCount = 1 ) {
		PageAssignmentsNotificationHooks::notify(
			'bs-pageassignments-user-group-remove',
			RequestContext::getMain()->getUser(),
			SpecialPage::getTitleFor( 'PageAssignments' ),
			array(
				'group' => $group,
				'affected-users' => array( $user )
			)
		);
		return true;
	}
}