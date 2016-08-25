<?php

class PageAssignmentsNotificationFormatter extends BsNotificationsFormatter {

	/**
	 * Adds information about "why" the user receives this notification
	 * @param EchoEvent $event
	 * @param User $user
	 * @return \PageAssignmentsEmailSingle
	 */
	protected function newEmailSingle( $event, $user ) {
		return new PageAssignmentsEmailSingle( $this, $event, $user );
	}
}