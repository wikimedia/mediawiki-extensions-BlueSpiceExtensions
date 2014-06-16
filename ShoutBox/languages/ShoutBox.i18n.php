<?php
/**
 * Internationalisation file for Shoutbox
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage Shoutbox
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['en'] = array(
	'bs-shoutbox-desc' => 'Adds a parser function for embedding your own shoutbox.',
	'prefs-shoutbox' => 'Shoutbox',
	'bs-shoutbox-title'=> 'Shoutbox',
	'bs-shoutbox-message' => 'Message',
	'bs-shoutbox-shout' => 'Shout it',
	'bs-shoutbox-no-entries' => 'Nothing here yet.',
	'bs-shoutbox-loading' => 'Loading...',
	'bs-shoutbox-pref-committimeinterval' => 'Time between two messages:',
	'bs-shoutbox-pref-maxmessagelength' => 'Maximum length of a message:',
	'bs-shoutbox-pref-numberofshouts' => 'Number of messages shown:',
	'bs-shoutbox-pref-showage' => 'Show creation date of a message',
	'bs-shoutbox-pref-ShowUser' => 'Show author of a message',
	'bs-shoutbox-pref-allowarchive' => 'User are able to delete their own entries',
	'bs-shoutbox-pref-show' => 'Display Shoutbox',
	'bs-shoutbox-archive-success' => 'Entry successfully deleted.',
	'bs-shoutbox-archive-failure' => 'An error occurred while trying to delete the entry, please try again.',
	'bs-shoutbox-archive-failure-user' => 'Entries can only be deleted by their author.',
	'bs-shoutbox-switch-description' => 'Hides shoutbox on this page',
	'bs-shoutbox-charactersleft' => 'characters left',
	'bs-shoutbox-confirm-text' => 'Do you really want to delete this entry?',
	'bs-shoutbox-confirm-title' => 'Confirm',
	'bs-shoutbox-entermessage' => 'Please enter a message.',
	'bs-shoutbox-too-early' => 'Please wait a few seconds before submitting the next entry.'
);

$messages['de'] = array(
	'bs-shoutbox-desc' => 'Unterhalb von Seiten wird eine Shoutbox für unmittelbare Kommentare bereitgestellt.',
	'prefs-shoutbox' => 'Shoutbox',
	'bs-shoutbox-title' => 'Shoutbox',
	'bs-shoutbox-message' => 'Mitteilung',
	'bs-shoutbox-shout' => 'Schicken',
	'bs-shoutbox-no-entries' => 'Bisher wurden keine Mitteilungen gesendet.',
	'bs-shoutbox-loading' => 'Lade...',
	'bs-shoutbox-pref-committimeinterval' => 'Zeit zwischen zwei Eintragungen in Sekunden <i>(Spamschutz)</i> (<b>15</b>)',
	'bs-shoutbox-pref-maxmessagelength' => 'Maximale Länge der Mittelungen',
	'bs-shoutbox-pref-numberofshouts' => 'Anzahl der dargestellten Mitteilungen',
	'bs-shoutbox-pref-showage' => 'Alter der Mitteilung anzeigen',
	'bs-shoutbox-pref-showuser' => 'Sender der Mitteilung anzeigen',
	'bs-shoutbox-pref-allowarchive' => 'Benutzer können ihre eigenen Einträge löschen',
	'bs-shoutbox-archive-success' => 'Eintrag wurde erfolgreich gelöscht.',
	'bs-shoutbox-archive-failure' => 'Beim Löschen des Eintrags ist ein Fehler aufgetreten, bitte versuche es erneut.',
	'bs-shoutbox-archive-failure-user' => 'Einträge können nur von deren Autor gelöscht werden.',
	'bs-shoutbox-switch-description' => 'Shoutbox wird auf dieser Seite nicht angezeigt',
	'bs-shoutbox-pref-show' => 'Shoutbox anzeigen',
	'bs-shoutbox-charactersleft' => 'übrige Zeichen',
	'bs-shoutbox-confirm-text' => 'Möchtest du diesen Eintrag wirklich löschen?',
	'bs-shoutbox-confirm-title' => 'Bestätigen',
	'bs-shoutbox-entermessage' => 'Bitte gib eine Nachricht ein.',
	'bs-shoutbox-too-early' => 'Bitte warte ein paar Sekunden, bevor du den nächsten Eintrag abschickst.'
);

$messages['de-formal'] = array(
	'bs-shoutbox-archive-failure' => 'Beim Löschen des Eintrags ist ein Fehler aufgetreten, bitte versuchen Sie es erneut.',
	'bs-shoutbox-confirm-text' => 'Möchten Sie diesen Eintrag wirklich löschen?',
	'bs-shoutbox-entermessage' => 'Bitte geben Sie eine Nachricht ein.',
	'bs-shoutbox-too-early' => 'Bitte warten Sie ein paar Sekunden, bevor Sie den nächsten Eintrag abschicken.'
);

$messages['qqq'] = array(
	'bs-shoutbox-desc' => 'Used in [[Special:Wiki_Admin&mode=ExtensionInfo]], description of shoutbox extension.',
	'prefs-shoutbox' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], headline for shout box section in preferences. \n {{Identical|Shoutbox}}',
	'bs-shoutbox-title'=> 'Fieldset legend for shoutbox',
	'bs-shoutbox-message' => 'Default text in textarea for message',
	'bs-shoutbox-shout' => 'Shout it',
	'bs-shoutbox-no-entries' => 'Text for nothing here yet.',
	'bs-shoutbox-loading' => 'Text for loading...',
	'bs-shoutbox-pref-committimeinterval' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], label for time between two messages:',
	'bs-shoutbox-pref-maxmessagelength' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], label for maximum length of a message:',
	'bs-shoutbox-pref-numberofshouts' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], label for number of messages shown:',
	'bs-shoutbox-pref-showage' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for show creation date of a message',
	'bs-shoutbox-pref-ShowUser' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for show author of a message',
	'bs-shoutbox-pref-allowarchive' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for user are able to delete their own entries',
	'bs-shoutbox-pref-show' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for display shoutbox',
	'bs-shoutbox-archive-success' => 'Text for entry successfully deleted.',
	'bs-shoutbox-archive-failure' => 'Text for an error occurred while trying to delete the entry, please try again.',
	'bs-shoutbox-archive-failure-user' => 'Text for entries can only be deleted by their author.',
	'bs-shoutbox-switch-description' => 'Used in InsertMagic extension, tag description for hides shoutbox on this page',
	'bs-shoutbox-charactersleft' => 'Text for characters left',
	'bs-shoutbox-confirm-text' => 'Text for do you really want to delete this entry?',
	'bs-shoutbox-confirm-title' => 'Window title for confirm',
	'bs-shoutbox-entermessage' => 'Text for please enter a message.',
	'bs-shoutbox-too-early' => 'Please wait a few seconds before submitting the next entry.'
);