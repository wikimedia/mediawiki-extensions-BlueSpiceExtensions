<?php
/**
 * Internationalisation file for MailChanges
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    $Id: MailChanges.i18n.php 9433 2013-05-17 11:33:58Z mreymann $
 * @package    BlueSpice_Extensions
 * @subpackage MailChanges
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['en'] = array(
	'bs-mailchanges-extension-description'    => 'Sends an email notication of changes.',
	'bs-mailchanges-email-new-subject'        => "Page $1 created by $2",
	'bs-mailchanges-email-new'                => "the page $1 was created by $2.\n\nComment:\n $3 \nYou can visit the page following this link:\n$4\n",
	'bs-mailchanges-email-edit-subject'       => "Page $1 edited by $2",
	'bs-mailchanges-email-edit'               => "the page $1 was edited by $2.\n\nComment:\n $3 \nYou can visit the page following this link:\n$4.\n\nIf you only want to see the changes, follow this link:\n$5\n",
	'bs-mailchanges-email-move-subject'       => "Page $1 moved by $2",
	'bs-mailchanges-email-move'               => "the page $1 was moved to $3 by $2. You can visit the page following this link:\n$4\n",
	'bs-mailchanges-email-delete-subject'     => "Page $1 deleted by $2",
	'bs-mailchanges-email-delete'             => "the page $1 was deleted by $2.\n\nThis was the reason:\n$3",
	'bs-mailchanges-email-addaccount-subject' => "User $1 created",
	'bs-mailchanges-email-addaccount'         => "the user $1 with username $2 was created.",
	'bs-mailchanges-email-shout-subject'      => "$2 posted a message on page $1",
	'bs-mailchanges-email-shout'              => "$2 posted a message on page $1.\n\nMessage:\n$3\n\n You can visit the page following this link:\n$4.\n",
	'prefs-MailChanges'                       => 'Send email on changes',
	'bs-mailchanges-pref-notifynew'           => 'Notification for new pages',
	'bs-mailchanges-pref-notifyedit'          => 'Notification for edits',
	'bs-mailchanges-pref-notifymove'          => 'Notification for moves',
	'bs-mailchanges-pref-notifydelete'        => 'Notification for deletion',
	'bs-mailchanges-pref-notifynominor'       => 'No notification for minor changes',
	'bs-mailchanges-pref-notifyuser'          => 'Notification for new users (only for sysops)',
	'bs-mailchanges-pref-notifyns'            => 'Only notify for changes in these namespaces',
	'bs-mailchanges-pref-notifyshout'         => 'Notification for messages on watched pages(shoutbox)',
	'bs-mailchanges-pref-active'              => 'Enable Notification'
);

$messages['de'] = array(
	'bs-mailchanges-extension-description'    => 'Sendet eine E-Mail-Benachrichtigung bei Änderungen.',
	'bs-mailchanges-email-new-subject'        => "Seite $1 von $2 angelegt",
	'bs-mailchanges-email-new'                => "die Seite $1 wurde von $2 neu angelegt.\n\nKommentar:\n $3 \nDu kannst den Artikel über diesen Link aufrufen:\n$4\n",
	'bs-mailchanges-email-edit-subject'       => "Seite $1 von $2 geändert",
	'bs-mailchanges-email-edit'               => "die Seite $1 wurde von $2 geändert.\n\nKommentar:\n $3 \nDu kannst den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folge diesem Link:\n$5\n",
	'bs-mailchanges-email-move-subject'       => "Seite $1 von $2 verschoben",
	'bs-mailchanges-email-move'               => "die Seite $1 wurde von $2 nach $3 verschoben. Du kannst den Artikel über diese URL aufrufen:\n$4\n",
	'bs-mailchanges-email-delete-subject'     => "Seite $1 von $2 gelöscht",
	'bs-mailchanges-email-delete'             => "die Seite $1 wurde von $2 gelöscht.\n\nAls Grund wurde angegeben:\n$3",
	'bs-mailchanges-email-addaccount-subject' => "Benutzer $1 neu angelegt",
	'bs-mailchanges-email-addaccount'         => "der Benutzer $1 mit Benutzernamen $2 wurde neu angelegt.",
	'bs-mailchanges-email-shout-subject'      => "$2 hat auf Seite $1 eine Nachricht hinterlassen",
	'bs-mailchanges-email-shout'              => "$2 hat auf der Seite $1 eine Nachricht hinterlassen.\n\nNachricht:\n$3\n\nDu kannst den Artikel über diese URL aufrufen:\n$4.\n",
	'prefs-MailChanges'                       => 'E-Mail bei Änderungen',
	'bs-mailchanges-pref-notifynew'           => 'Benachrichtigung bei neuen Seiten',
	'bs-mailchanges-pref-notifyedit'          => 'Benachrichtigung beim Bearbeiten von Seiten',
	'bs-mailchanges-pref-notifymove'          => 'Benachrichtigung beim Verschieben von Seiten',
	'bs-mailchanges-pref-notifydelete'        => 'Benachrichtigung beim Löschen von Seiten',
	'bs-mailchanges-pref-notifynominor'       => 'Keine Benachrichtigung bei geringfügigen Änderungen',
	'bs-mailchanges-pref-notifyuser'          => 'Benachrichtigung bei Anmeldung neuer Nutzer (nur für Administratoren)',
	'bs-mailchanges-pref-notifyns'            => 'Nur bei Bearbeitungen in diesen Namespaces benachrichtigen',
	'bs-mailchanges-pref-notifyshout'         => 'Benachrichtigung bei Mitteilungen auf beobachteten Seiten (Shoutbox)',
	'bs-mailchanges-pref-active'              => 'Benachrichtigungen aktivieren'
	
);

$messages['de-formal'] = array(
	'bs-mailchanges-email-new'                => "die Seite $1 wurde von $2 neu angelegt.\n\nKommentar:\n$3\n\nSie können den Artikel über diesen Link aufrufen:\n$4\n",
	'bs-mailchanges-email-edit'               => "die Seite $1 wurde von $2 geändert.\n\nKommentar:\n$3\n\nSie können den Artikel über diese URL aufrufen:\n$4.\n\nUm nur die Änderungen zu sehen, folgen Sie diesem Link:\n$5\n",
	'bs-mailchanges-email-move'               => "die Seite $1 wurde von $2 nach $3 verschoben. Sie können den Artikel über diese URL aufrufen:\n$4\n",
	'bs-mailchanges-email-shout'              => "$2 hat auf der Seite $1 eine Nachricht hinterlassen.\n\nNachricht:\n$3\n\nSie können den Artikel über diese URL aufrufen:\n$4\n."
);

$messages['qqq'] = array();
