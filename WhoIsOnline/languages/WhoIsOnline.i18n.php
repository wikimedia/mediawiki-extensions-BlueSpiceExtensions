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
	'bs-whoisonline-desc' => 'Displays a list of users who are currently online.',
	'bs-whoisonline-nousers' => 'Nobody is logged in.',
	'bs-whoisonline-widget-title' => 'Who is online?',
	'prefs-whoisonline' => 'Who is online?',
	'bs-whoisonline-pref-limitcount' => 'Maximum number of users shown:',
	'bs-whoisonline-pref-orderby' => 'Sort by:',
	'bs-whoisonline-pref-maxidletime' => 'Time in seconds until a user is marked as offline:',
	'bs-whoisonline-pref-orderby-time' => 'Uptime',
	'bs-whoisonline-pref-orderby-name' => 'Name',
	'bs-whoisonline-pref-interval' => 'Update interval in seconds:',
	'bs-whoisonline-tag-whoisonlinecount-desc' => 'Renders the number of currently logged in users.',
	'bs-whoisonline-tag-whoisonlinepopup-desc' => 'Renders a little fly-out list of currently logged in users.

Valid attributes:
;anchortext: The text you want the fly-out to be attached to.'
);

$messages['de'] = array(
	'bs-whoisonline-desc' => 'Zeigt eine Liste der User an, die momentan online sind.',
	'bs-whoisonline-nousers' => 'Es ist niemand angemeldet.',
	'bs-whoisonline-widget-title' => 'Wer ist online?',
	'prefs-WhoIsOnline' => 'Wer ist online?',
	'bs-whoisonline-pref-limitcount' => 'Maximale Zahl der angezeigten Nutzer:',
	'bs-whoisonline-pref-orderby' => 'Sortieren nach:',
	'bs-whoisonline-pref-maxidletime' => 'Zeit, nach der ein inaktiver Benutzer nicht mehr als Online angezeigt wird (Sekunden)',
	'bs-whoisonline-pref-orderby-time' => 'Onlinezeit',
	'bs-whoisonline-pref-orderby-name' => 'Name',
	'bs-whoisonline-pref-interval' => 'Aktualisierungsintervall in Sekunden:',
	'bs-whoisonline-tag-whoisonlinecount-desc' => 'Gibt die Anzahl der Benutzer aus, die gerade angemeldet sind.',
	'bs-whoisonline-tag-whoisonlinepopup-desc' => 'Stellt eine aufklappbare Liste der gerade angemeldeten Benutzer da.

Verfügbare Parameter:
;anchortext: Der Text, an dem die Liste ausklappt.'
);

$messages['qqq'] = array(
	'bs-whoisonline-desc' => 'Used in [[Special:Wiki_Admin&mode=ExtensionInfo]], description of who is online extension.',
	'bs-whoisonline-nousers' => 'Text for nobody is logged in.',
	'bs-whoisonline-widget-title' => 'Widget headline for who is online?\n {{Identical|Who is online?}}',
	'prefs-whoisonline' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], headline for who is online section.\n{{Identical|Who is online?}}',
	'bs-whoisonline-pref-limitcount' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for maximum number of users shown:',
	'bs-whoisonline-pref-orderby' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for sort by:',
	'bs-whoisonline-pref-maxidletime' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for time in seconds until a user is marked as offline:',
	'bs-whoisonline-pref-orderby-time' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], option label for uptime',
	'bs-whoisonline-pref-orderby-name' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], option label for name',
	'bs-whoisonline-pref-interval' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for update interval in seconds:',
	'bs-whoisonline-tag-whoisonlinecount-desc' => 'Used in InsertMagic extension, tag description for renders the number of currently logged in users.',
	'bs-whoisonline-tag-whoisonlinepopup-desc' => 'Used in InsertMagic extension, tag description for renders a little fly-out list of currently logged in users.

Valid attributes:
;anchortext: Explanation text for the text you want the fly-out to be attached to.'
);