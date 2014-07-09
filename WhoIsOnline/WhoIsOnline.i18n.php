<?php
/**
 * Internationalisation file for WhoIsOnline
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage WhoIsOnline
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['en'] = array(
	'bs-whoisonline-extension-description' => 'Displays a list of users who are currently online.',
	'bs-whoisonline-nousers'               => 'Nobody is logged in.',
	'bs-whoisonline-widget-title'          => 'Who is online?',
	'bs-whoisonline-deprecated'            => 'The display of users is now in the widget bar on the right. Please remove {{#userslink}}.',
	'prefs-WhoIsOnline'                    => 'Who is online?',
	'bs-whoisonline-pref-LinkUsers'        => 'Link user name to user page',
	'bs-whoisonline-pref-ShowRealName'     => 'Show real name',
	'bs-whoisonline-pref-LimitCount'       => 'Maximum number of users shown',
	'bs-whoisonline-pref-OrderBy'          => 'Sort by',
	'bs-whoisonline-pref-MaxIdleTime'      => 'Expired time when a inactive user is not shown as online anymore (seconds)',
	'bs-whoisonline-pref-sort-time'        => 'Online time',
	'bs-whoisonline-pref-sort-name'        => 'Name',
	'bs-whoisonline-pref-Interval'         => 'Update interval (seconds).',
	'bs-whoisonline-table-does-not-exist'  => 'Database table whoisonline does not exist',
	'bs-whoisonline-tag-whoisonlinecount-desc' => 'Renders the number of currently logged in users.',
	'bs-whoisonline-tag-whoisonlinepopup-desc' => 'Renders a little fly-out list of currently logged in users.

Valid attributes:
;anchortext: The text you want the fly-out to be attached to.',
);

$messages['de'] = array(
	'bs-whoisonline-extension-description' => 'Zeigt eine Liste der User an, die momentan online sind.',
	'bs-whoisonline-nousers'               => 'Es ist niemand angemeldet.',
	'bs-whoisonline-widget-title'          => 'Wer ist online?',
	'bs-whoisonline-deprecated'            => 'Die Anzeige der Nutzer findet sich nun in der Widget-Leiste rechts. Bitte entferne {{#userslink}}.',
	'prefs-WhoIsOnline'                    => 'Wer ist online?',
	'bs-whoisonline-pref-LinkUsers'        => 'Benutzernamen mit der Benutzerseite verlinken',
	'bs-whoisonline-pref-ShowRealName'     => 'Den echten Namen anzeigen',
	'bs-whoisonline-pref-LimitCount'       => 'Maximale Zahl der angezeigten Nutzer',
	'bs-whoisonline-pref-OrderBy'          => 'Sortieren nach',
	'bs-whoisonline-pref-MaxIdleTime'      => 'Zeit, nach der ein inaktiver Benutzer nicht mehr als Online angezeigt wird (Sekunden)',
	'bs-whoisonline-pref-sort-time'        => 'Onlinezeit',
	'bs-whoisonline-pref-sort-name'        => 'Name',
	'bs-whoisonline-pref-Interval'         => 'Aktualisierungsintervall (in Sekunden).',
	'bs-whoisonline-table-does-not-exist'  => 'Die Datenbank-Tabelle whoisonline existiert nicht.',
	'bs-whoisonline-tag-whoisonlinecount-desc' => 'Gibt die Anzahl der Benutzer aus, die gerade angemeldet sind.',
	'bs-whoisonline-tag-whoisonlinepopup-desc' => 'Stellt eine aufklappbare Liste der gerade angemeldeten Benutzer da.

Verfügbare Parameter:
;anchortext: Der Text, an dem die Liste ausklappt.'
);

$messages['de-formal'] = array(
	'bs-whoisonline-deprecated'            => 'Die Anzeige der Nutzer findet sich nun in der Widget-Leiste rechts. Bitte entfernen Sie {{#userslink}}.',
);

$messages['qqq'] = array();