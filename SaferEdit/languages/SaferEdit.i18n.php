<?php
/**
 * Internationalisation file for SaferEdit
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage SaferEdit
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['de'] = array(
	'bs-saferedit-desc'       => 'Bearbeitungen an Seiten werden automatisch zwischengespeichert',
	'prefs-saferedit'                          => 'Sicheres Bearbeiten',
	'bs-saferedit-restore'                     => 'Wiederherstellen',
	'bs-saferedit-safer-edit-tooltip'          => 'Es gibt eine Wiederherstellungsversion.',
	'bs-saferedit-safer-edit-topbar'           => 'Wiederherstellung',
	'bs-saferedit-user-editing'         => '$1 bearbeitet die Seite gerade',
	'bs-saferedit-someone-editing'     => 'Die Seite wird gerade bearbeitet',
	'bs-saferedit-pref-interval'               => 'Intervall der Zwischenspeicherung:',
	'bs-saferedit-pref-shownameofeditinguser'  => 'Name des aktuellen Bearbeiters anzeigen',
	'bs-saferedit-pref-warnonleave'            => 'Warnung bei ungespeicherten Inhalten',
	'bs-saferedit-pref-usese'                  => 'Sicheres Bearbeiten aktivieren',
	'bs-saferedit-statebartopsaferedit'        => 'Sicheres Bearbeiten',
	'bs-saferedit-statebartopsafereditediting' => 'Sicheres Bearbeiten - In Bearbeitung',
	'bs-saferedit-lastsavedversion'  => 'Alter der Sicherungsversion: $1',
	'bs-saferedit-unsavedchanges'    => 'Es gibt noch ungespeicherte Änderungen.',
	'bs-saferedit-othersectiontitle' => 'Sicherung in einem anderen Seitenabschnitt.',
	'bs-saferedit-othersectiontext1' => 'Es gibt eine Sicherung eines Seiten-Abschnitts.',
	'bs-saferedit-othersectiontext2' => 'Sicherungsversion wurde erstellt am $1',
	'bs-saferedit-othersectiontext3' => 'Möchtest Du zum Seitenabschnitt wechseln?'
);

$messages['de-formal'] = array(
	'bs-saferedit-unsavedchanges'    => 'Sie haben noch ungespeicherte Änderungen.',
	'bs-saferedit-othersectiontext3' => 'Möchten Sie zum Seitenabschnitt wechseln?'
);

$messages['en'] = array(
	'bs-saferedit-desc'       => 'Intermediate saving of edits',
	'prefs-saferedit'                          => 'Safer editing',
	'bs-saferedit-restore'                     => 'Restore',
	'bs-saferedit-safer-edit-tooltip'          => 'There is a recovery version.',
	'bs-saferedit-safer-edit-topbar'           => 'Recovery',
	'bs-saferedit-user-editing'         => '$1 is {{GENDER:$1|editing}} the page',
	'bs-saferedit-someone-editing'      => 'The page is being edited',
	'bs-saferedit-pref-interval'               => 'Interval of intermediate saving:',
	'bs-saferedit-pref-shownameofeditinguser'  => 'Show name of current editor',
	'bs-saferedit-pref-warnonleave'            => 'Warn user when unsaved changes',
	'bs-saferedit-pref-usese'                  => 'Activate safer editing',
	'bs-saferedit-statebartopsaferedit'        => 'Safer editing',
	'bs-saferedit-statebartopsafereditediting' => 'Safer editing - someone is editing',
	'bs-saferedit-lastsavedversion'  => 'Last saved version: $1',
	'bs-saferedit-unsavedchanges'    => 'There are unsaved changes.',
	'bs-saferedit-othersectiontitle' => 'Section to restore',
	'bs-saferedit-othersectiontext1' => 'There is a backup of another section.',
	'bs-saferedit-othersectiontext2' => 'The backup was created on $1.',
	'bs-saferedit-othersectiontext3' => 'Do you want to switch to that section?'
);

$messages['qqq'] = array(
	'bs-saferedit-desc'       => 'Used in [[Special:Wiki_Admin&mode=ExtensionInfo]], description of pages visited extension',
	'prefs-saferedit'                          => 'Used in [[Special:Wiki_Admin&mode=Preferences]], headline for safer edit section in preferences.\n{{Identical|Safer editing}}',
	'bs-saferedit-restore'                     => 'Button label for restore\n{{Identical|Restore}}',
	'bs-saferedit-safer-edit-tooltip'          => 'Text for there is a recovery version.',
	'bs-saferedit-safer-edit-topbar'           => 'Text for recovery\n{{Identical|Recovery}}',
	'bs-saferedit-user-editing'         => 'Text for $1 is {{GENDER:$1|editing}} the page \n $1 is the user name of the user who is editing the page at the moment.\nNo punctuation needed.',
	'bs-saferedit-someone-editing'      => 'Text for the page is being edited\n No punctuation needed.',
	'bs-saferedit-pref-interval'               => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for interval of intermediate saving:',
	'bs-saferedit-pref-shownameofeditinguser'  => 'Option in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for show name of current editor',
	'bs-saferedit-pref-warnonleave'            => 'Option in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for warn user when unsaved changes',
	'bs-saferedit-pref-usese'                  => 'Option in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for activate safer editing',
	'bs-saferedit-statebartopsaferedit'        => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for safer editing',
	'bs-saferedit-statebartopsafereditediting' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for safer editing - someone is editing',
	'bs-saferedit-lastsavedversion'  => 'Text for last saved version: $1',
	'bs-saferedit-unsavedchanges'    => 'Text for there are unsaved changes.',
	'bs-saferedit-othersectiontitle' => 'Window title for section to restore',
	'bs-saferedit-othersectiontext1' => 'Text for there is a backup of another section.',
	'bs-saferedit-othersectiontext2' => 'Text for the backup was created on $1.',
	'bs-saferedit-othersectiontext3' => 'Text for do you want to switch to that section?'
);