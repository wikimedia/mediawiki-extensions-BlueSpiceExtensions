<?php
/**
 * Internationalisation file for Readers
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage Readers
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['en'] = array(
	'bs-readers-desc' => 'Shows readers of a page.',
	'prefs-readers' => 'Readers of this page',
	'bs-readers-title' => '{{PLURAL:$1|Reader|Readers}}',
	'specialreaders' => 'Readers',
	'specialreaders-user' => 'Read',
	'readers' => 'Readers',
	'specialreaders-desc' => 'This specialpage shows the readers of a page',
	'specialpages-group-bluespice' => 'BlueSpice',
	'bs-readers-contentactions-label' => 'Show readers',
	'bs-readers-article-does-not-exist' => 'Page does not exist.',
	'bs-readers-pref-active' => 'Activate readers of this page',
	'bs-readers-pref-numofreaders' => 'Number of readers to display:',
	'bs-readers-emptyinput' => 'Empty input.',
	'bs-readers-headerusername' => 'User name',
	'bs-readers-headerreaderspath' => 'Readers path',
	'bs-readers-headerts' => 'Date',
	'bs-readers-showentries' => 'Displaying {0} - {1} of {2}',
	'bs-readers-pagesize' => 'Page size: '
);

$messages['qqq'] = array(
	'bs-readers-desc' => 'Used in [[Special:Wiki_Admin&mode=ExtensionInfo]], description of readers extension.',
	'prefs-usersidebar'=> 'Used in [[Special:Wiki_Admin&mode=Preferences]], headline for  section in preferences.\n{{Identical|User sidebar}}',
	'bs-usersidebar-pref-userpagesubpagetitle' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], subpage that hosts the user sidebar:',
	'prefs-readers' => 'Used in [[Special:Wiki_Admin&mode=Preferences]], headline for readers section in preferences.\n{{Identical|Readers of this page}}',
	'bs-readers-title' => 'Headline of readers below page, $1 is the number of readers for PLURAL distinction',
	'specialreaders' => 'Readers',
	'specialreaders-user' => 'Read',
	'readers' => 'Specialpage title for readers\n{{Identical|Readers of this page}}',
	'specialreaders-desc' => 'This specialpage shows the readers of a page',
	'specialpages-group-bluespice' => 'BlueSpice',
	'bs-readers-contentactions-label' => 'Used in content actions, link text for show readers',
	'bs-readers-article-does-not-exist' => 'Error text on special page when page does not exist.',
	'bs-readers-pref-active' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for activate readers of this page \n{{Identical|Activate readers of this page}}',
	'bs-readers-pref-numofreaders' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for number of readers to display: \n{{Identical|Number of readers to display}}',
	'bs-readers-emptyinput' => 'Error text on special page for empty input.',
	'bs-readers-headerusername' => 'Used on special page, headline for user name',
	'bs-readers-headerreaderspath' => 'Used on special page, headline for readers path',
	'bs-readers-headerts' => 'Used on special page, headline for date',
	'bs-readers-showentries' => 'Used on special page, text for displaying {0} - {1} of {2} \n {0} stands for the start of a range \n {1} stands for the end of a range \n {2} is the total amount of entries',
	'bs-readers-pagesize' => 'Used on special page, headline for page size: '
);

$messages['de'] = array(
	'prefs-readers' => 'Besucher dieser Seite',
	'bs-readers-desc' => 'Zeigt die Besucher einer Seite an.',
	'bs-readers-title' => 'Besucher',
	'specialreaders' => 'Besucher',
	'specialreaders-user' => 'Besucht',
	'readers' => 'Besucher',
	'specialreaders-desc' => 'Diese Spezialseite zeigt dir die Besucher einer Seite an',
	'specialpages-group-bluespice' => 'BlueSpice',
	'bs-readers-contentactions-label' => 'Besucher anzeigen',
	'bs-readers-article-does-not-exist' => 'Es wurde keine Seite übergeben.',
	'bs-readers-pref-active' => 'Besucher dieser Seite aktivieren.',
	'bs-readers-pref-numofreaders' => 'Anzahl der anzuzeigenden Besucher:',
	'bs-readers-emptyinput' => 'Du musste einen Seite angeben.',
	'bs-readers-headerusername' => 'Benutzername',
	'bs-readers-headerreaderspath' => 'Lesepfad',
	'bs-readers-headerts' => 'Datum',
	'bs-readers-showentries' => 'Angezeigte Einträge {0} - {1} von {2}',
	'bs-readers-pagesize' => 'Seitengröße: '
);

$messages['de-formal'] = array(
	'specialreaders-desc' => 'Diese Spezialseite zeigt Ihnen die Besucher einer Seite an',
	'bs-readers-emptyinput' => 'Sie müssen eine Seite angeben.',
);