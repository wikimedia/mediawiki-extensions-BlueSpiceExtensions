<?php
/**
 * Internationalisation file for Notifications
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stefan Widmann <widmann@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage Notifications
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['en'] = array(
	// START HERE -------------------------------------------------------------- //
	'prefs-echo-extended'						=> 'Advanced Systemsettings',
	'echo-pref-subscription-bs-edit-cat'		=> 'site changed.',
	'echo-pref-subscription-bs-create-cat'		=> 'a site has been created.',
	'echo-pref-subscription-bs-delete-cat'		=> 'a site has been deleted.',
	'echo-pref-subscription-bs-move-cat'		=> 'a site has been moved.',
	'echo-pref-subscription-bs-newuser-cat'		=> 'If a new user gets created (only for Administrators)',
	'echo-pref-subscription-bs-shoutbox-cat'	=> 'Notification messages on observed Pages (Shoutbox)',

	'bs-echo-anon-user'							=> "'''anonymous'''",
	'bs-echo-unknown-user'						=> "'''unknown'''",
	'echo-dismiss-title-bs-edit'				=> 'site changed',
	'echo-dismiss-title-bs-create'				=> 'site created',
	'echo-dismiss-title-bs-delete'				=> 'site deleted',
	'echo-dismiss-title-bs-move'				=> 'site moved',
	'echo-dismiss-title-bs-newuser'				=> 'New users',
	'echo-dismiss-title-bs-shoutbox'			=> 'ShoutBox messages',

	'bs-echo-page-edit'							=> 'The site [[$1]] has been changed',
	'bs-echo-flyout-page-edit'					=> 'The site  [[$1]] has been changed by $2 ',
	'bs-echo-page-create'						=> 'The site  [[$1]] was created',
	'bs-echo-flyout-page-create'				=> 'The site [[$1]] was created by $2',
	'bs-echo-page-delete'						=> 'The site [[$1]] has been deleted',
	'bs-echo-flyout-page-delete'				=> 'The site [[$1]] has been deleted',
	'bs-echo-page-move'							=> 'The site [[$1]] has been moved',
	'bs-echo-flyout-page-move'					=> 'The site [[$1]] has been moved',
	'bs-echo-page-newuser'						=> 'The User [[$1]] was created.',
	'bs-echo-flyout-page-newuser'				=> 'The User [[$1]] was created.',
	'bs-echo-page-shoutbox'						=> 'On the site [[$1]] was a new ShoutBox-Message created .',
	'bs-echo-flyout-page-shoutbox'				=> 'On the site [[$1]] was a new ShoutBox-Message created .',

	// Email subject
	'bs-echo-email-subject-page-edit'			=> 'The site [[$1]] has been changed by [[$2]]',
	'bs-echo-email-subject-page-create'			=> 'The site [[$1]] has been created',
	'bs-echo-email-subject-page-delete'			=> 'The site [[$1]] has been deleted',
	'bs-echo-email-subject-page-move'			=> 'The site [[$1]] has been moved',
	'bs-echo-email-subject-page-newuser'		=> 'The User [[$1]] was created',
	'bs-echo-email-subject-page-shoutbox'		=> 'On the site [[$1]] was a new ShoutBox-Message created',

	// Email body
	'bs-echo-email-body-page-edit'				=> "The site $1 was changed by $2.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-create'			=> "The site $1 was created by $2.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-delete'			=> "The site $1 was deleted by $2.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-move'				=> "The site $1 was moved by $2.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-newuser'			=> "The User was new created.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-shoutbox'			=> "On the site was a new ShoutBox-Message created.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",

	// Email batch
	'bs-echo-emailbatch-subject-page-edit'		=> 'The wiki pages were edited',
	'bs-echo-emailbatch-body-page-edit'			=> 'The site [[$1]] was edited by $2', // entspricht bs-echo-flyout-*******

	'bs-echo-emailbatch-subject-page-create'	=> 'The wiki pages were created',
	'bs-echo-emailbatch-body-page-create'		=> 'The site [[$1]] was created by $2',

	'bs-echo-emailbatch-subject-page-delete'	=> 'The wiki pages were deleted',
	'bs-echo-emailbatch-body-page-delete'		=> 'The site [[$1]] was deleted by $2',

	'bs-echo-emailbatch-subject-page-move'		=> 'The wiki pages were moved',
	'bs-echo-emailbatch-body-page-move'			=> 'The site [[$1]] was moved by $2',

	'bs-echo-emailbatch-subject-page-newuser'	=> 'A new User was created',
	'bs-echo-emailbatch-body-page-newuser'		=> 'On the site [[$1]] was a new user created by $2',

	'bs-echo-emailbatch-subject-page-shoutbox'	=> 'On the site was a new ShoutBox-Message created',
	'bs-echo-emailbatch-body-page-shoutbox'		=> 'On the site [[$1]] was a new ShoutBox-Message created by $2',
	// END HERE -------------------------------------------------------------- //

	'bs-notifications-extension-description'    => 'Sends an email notication of changes.',
	'bs-notifications-email-new-subject'        => "Page $1 created by $2",
	'bs-notifications-email-new'                => "the page $1 was created by $2.\n\nComment:\n $3 \nYou can visit the page following this link:\n$4\n",
	'bs-notifications-email-edit-subject'       => "Page <b>$1</b> edited by <b>$2</b>",
	'bs-notifications-email-edit'               => "the page $1 was edited by $2.\n\nComment:\n $3 \nYou can visit the page following this link:\n$4.\n\nIf you only want to see the changes, follow this link:\n$5\n",
	'bs-notifications-email-move-subject'       => "Page $1 moved by $2",
	'bs-notifications-email-move'               => "the page $1 was moved to $3 by $2. You can visit the page following this link:\n$4\n",
	'bs-notifications-email-delete-subject'     => "Page $1 deleted by $2",
	'bs-notifications-email-delete'             => "the page $1 was deleted by $2.\n\nThis was the reason:\n$3",
	'bs-notifications-email-addaccount-subject' => "User $1 created",
	'bs-notifications-email-addaccount'         => "the user $1 with username $2 was created.",
	'bs-notifications-email-shout-subject'      => "$2 posted a message on page $1",
	'bs-notifications-email-shout'              => "$2 posted a message on page $1.\n\nMessage:\n$3\n\n You can visit the page following this link:\n$4.\n",
	'prefs-Notifications'                       => 'Notifications',
	'echo-category-title-bs-create-cat'           => 'Notification for new pages',
	'echo-category-title-bs-edit-cat'          => 'Notification for edits',
	'echo-category-title-bs-move-cat'          => 'Notification for moves',
	'echo-category-title-bs-delete-cat'        => 'Notification for deletion',
	'bs-notifications-pref-notifynominor'       => 'No notification for minor changes',
	'echo-category-title-bs-newuser-cat'          => 'Notification for new users (only for sysops)',
	'bs-notifications-pref-notifyns'            => 'Only notify for changes in these namespaces',
	'echo-category-title-bs-shoutbox-cat'         => 'Notification for messages on watched pages(shoutbox)',
	'bs-notifications-pref-active'              => 'Enable Notification',

);

$messages['de'] = array(
	// START HERE -------------------------------------------------------------- //
	'prefs-echo-extended'						=> 'Erweiterte Benachrichtigungseinstellungen',
	'echo-pref-subscription-bs-edit-cat'		=> 'eine Seite bearbeitet hat.',
	'echo-pref-subscription-bs-create-cat'		=> 'eine Seite neu angelegt hat.',
	'echo-pref-subscription-bs-delete-cat'		=> 'eine Seite gelöscht hat.',
	'echo-pref-subscription-bs-move-cat'		=> 'eine Seite verschoben hat.',
	'echo-pref-subscription-bs-newuser-cat'		=> 'Wenn ein neuer Benutzer angelegt wird (nur für Administratoren)',
	'echo-pref-subscription-bs-shoutbox-cat'	=> 'Benachrichtigung bei Mitteilungen auf beobachteten Seiten (Shoutbox)',

	'echo-dismiss-title-bs-edit'				=> 'Seiten erstellt',// Alle "Seite erstellt"-Benachrichtigungen abschalten.
	'echo-dismiss-title-bs-create'				=> 'Seiten angelegt',
	'echo-dismiss-title-bs-delete'				=> 'Seiten gelöscht',
	'echo-dismiss-title-bs-move'				=> 'Seiten verschoben',
	'echo-dismiss-title-bs-newuser'				=> 'Neue Benutzer ',
	'echo-dismiss-title-bs-shoutbox'			=> 'Shoutbox Messages',

	'bs-echo-anon-user'							=> "'''Anonym'''",
	'bs-echo-unknown-user'						=> "'''Unbekannt'''",
	'bs-echo-page-edit'							=> 'Die Seite [[$1]] wurde bearbeitet',
	'bs-echo-flyout-page-edit'					=> 'Die Seite [[$1]] wurde von [[$2]] bearbeitet',
	'bs-echo-page-create'						=> 'Die Seite [[$1]] wurde neu angelegt',
	'bs-echo-flyout-page-create'				=> 'Die Seite [[$1]] wurde von $2 neu angelegt',
	'bs-echo-page-delete'						=> 'Die Seite [[$1]] wurde gelöscht',
	'bs-echo-flyout-page-delete'				=> 'Die Seite [[$1]] wurde gelöscht',
	'bs-echo-page-move'							=> 'Die Seite [[$1]] wurde verschoben',
	'bs-echo-flyout-page-move'					=> 'Die Seite [[$1]] wurde verschoben',
	'bs-echo-page-newuser'						=> 'Der Benutzer $1 wurde neu angelegt.',
	'bs-echo-flyout-page-newuser'				=> 'Der Benutzer [[$1]] wurde neu angelegt.',
	'bs-echo-page-shoutbox'						=> 'Auf der Seite [[$1]] wurde eine ShoutBox-Mitteilung verfasst .',
	'bs-echo-flyout-page-shoutbox'				=> 'Auf der Seite [[$1]] wurde eine ShoutBox-Mitteilung verfasst .',

	// Email subject
	'bs-echo-email-subject-page-edit'			=> 'Die Seite [[$1]] wurde von [[$2]] bearbeitet',
	'bs-echo-email-subject-page-create'			=> 'Die Seite [[$1]] wurde angelegt',
	'bs-echo-email-subject-page-delete'			=> 'Die Seite [[$1]] wurde gelöscht',
	'bs-echo-email-subject-page-move'			=> 'Die Seite [[$1]] wurde verschoben',
	'bs-echo-email-subject-page-newuser'		=> 'Der Benutzer [[$1]] wurde neu angelegt',
	'bs-echo-email-subject-page-shoutbox'		=> 'Auf der Seite [[$1]] wurde eine ShoutBox-Mitteilung verfasst',

	// Email body
	'bs-echo-email-body-page-edit'				=> "die Seite $1 wurde von $2 geändert.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-create'			=> "die Seite $1 wurde von $2 erstellt.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-delete'			=> "die Seite $1 wurde von $2 gelöscht.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-move'				=> "die Seite $1 wurde von $2 verschoben.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-newuser'			=> "Der Benutzer wurde neu angelegt.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-echo-email-body-page-shoutbox'			=> "Auf der Seite wurde eine neue ShoutBox-Mitteilung verfasst.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",

	// Email batch
	'bs-echo-emailbatch-subject-page-edit'		=> 'Im Wiki wurden Seiten bearbeitet',
	'bs-echo-emailbatch-body-page-edit'			=> 'Die Seite [[$1]] wurde von $2 bearbeitet', // entspricht bs-echo-flyout-*******

	'bs-echo-emailbatch-subject-page-create'	=> 'Im Wiki wurden neue Seiten angelegt',
	'bs-echo-emailbatch-body-page-create'		=> 'Die Seite [[$1]] wurde von $2 neu angelegt',

	'bs-echo-emailbatch-subject-page-delete'=> 'Im Wiki wurden Seiten gelöscht',
	'bs-echo-emailbatch-body-page-delete'	=> 'Die Seite [[$1]] wurde von $2 gelöscht',

	'bs-echo-emailbatch-subject-page-move'	=> 'Im Wiki wurden Seiten bewegt',
	'bs-echo-emailbatch-body-page-move'		=> 'Die Seite [[$1]] wurde von $2 bewegt',

	'bs-echo-emailbatch-subject-page-newuser'=> 'Auf der Seite wurde ein neuer Benutzer angelegt',
	'bs-echo-emailbatch-body-page-newuser'	=> 'Auf der Seite [[$1]] wurde von $2 ein neuer Benutzer angelegt',

	'bs-echo-emailbatch-subject-page-shoutbox'=> 'Auf der Seite wurde eine ShoutBox-Mitteilung verfasst',
	'bs-echo-emailbatch-body-page-shoutbox'	=> 'Auf der Seite [[$1]] wurde von $2 eine ShoutBox-Mitteilung verfasst',
	// END HERE -------------------------------------------------------------- //

	'bs-notifications-extension-description'    => 'Sendet eine E-Mail-Benachrichtigung bei Änderungen.',
	'bs-notifications-email-new-subject'        => "Seite $1 wurde von $2 angelegt",
	'bs-notifications-email-new'                => "Die Seite $1 wurde von $2 neu angelegt.\n\nKommentar:\n $3 \nDu kannst den Artikel über diesen Link aufrufen:\n$4\n",
	'bs-notifications-email-edit-subject'       => "Seite $1 wurde von $2 geändert",
	'bs-notifications-email-edit'               => "Die Seite $1 wurde von $2 geändert.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-notifications-email-move-subject'       => "Seite $1 wurde von $2 nach $3 verschoben",
	'bs-notifications-email-move'               => "Die Seite $1 wurde von $2 nach $3 verschoben.\n\nDu kannst den Artikel über diese URL aufrufen:\n$4\n",
	'bs-notifications-email-delete-subject'     => "Seite $1 wurde von $2 gelöscht",
	'bs-notifications-email-delete'             => "Die Seite $1 wurde von $2 gelöscht.\n\nAls Grund wurde angegeben:\n$3",
	'bs-notifications-email-addaccount-subject' => "Benutzer $1 wurde neu angelegt",
	'bs-notifications-email-addaccount'         => "Der Benutzer $1 wurde neu angelegt.",
	'bs-notifications-email-shout-subject'      => "$2 hat auf der Seite $1 eine Nachricht hinterlassen",
	'bs-notifications-email-shout'              => "$2 hat auf der Seite $1 eine Nachricht hinterlassen.\n\nNachricht:\n$3\n\nDu kannst den Artikel über diese URL aufrufen:\n$4\n",
	'prefs-Notifications'                       => 'Benachrichtigungen',
	'echo-category-title-bs-create-cat'           => 'Benachrichtigung bei neuen Seiten',
	'echo-category-title-bs-edit-cat'          => 'Benachrichtigung beim Bearbeiten von Seiten',
	'echo-category-title-bs-move-cat'          => 'Benachrichtigung beim Verschieben von Seiten',
	'echo-category-title-bs-delete-cat'        => 'Benachrichtigung beim Löschen von Seiten',
	'bs-notifications-pref-notifynominor'       => 'Keine Benachrichtigung bei geringfügigen Änderungen',
	'echo-category-title-bs-newuser-cat'          => 'Benachrichtigung bei Anmeldung neuer Nutzer (nur für Administratoren)',
	'bs-notifications-pref-notifyns'            => 'Nur bei Bearbeitungen in diesen Namespaces benachrichtigen',
	'echo-category-title-bs-shoutbox-cat'         => 'Benachrichtigung bei Mitteilungen auf beobachteten Seiten (Shoutbox)',
	'bs-notifications-pref-active'              => 'Benachrichtigungen aktivieren'

);

$messages['de-formal'] = array(
	'bs-notifications-email-new'                => "Die Seite $1 wurde von $2 neu angelegt.\n\nKommentar:\n$3\n\nSie können den Artikel über diesen Link aufrufen:\n$4\n",
	'bs-notifications-email-edit'               => "Die Seite $1 wurde von $2 geändert.\n\nKommentar:\n$3\n\nSie können den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folgen Sie diesem Link:\n$5\n",
	'bs-notifications-email-move'               => "Die Seite $1 wurde von $2 nach $3 verschoben.\n\nSie können den Artikel über diese URL aufrufen:\n$4\n",
	'bs-notifications-email-shout'              => "$2 hat auf der Seite $1 eine Nachricht hinterlassen.\n\nNachricht:\n$3\n\nSie können den Artikel über diese URL aufrufen:\n$4\n"
);

$messages['qqq'] = array();
