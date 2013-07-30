<?php
/**
 * Internationalisation file for PageAccess
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Marc Reymann <reymann@hallowelt.biz>
 * @version    $Id: PageAccess.i18n.php 9848 2013-06-21 13:41:20Z mreymann $
 * @package    BlueSpice_Extensions
 * @subpackage CountThings
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['de'] = array(
	'bs-pageaccess-extension-description'              => 'Regelt den Zugriff auf Seitenebene.',
	'PageAccess'                                       => 'PageAccess',
	'bs-pageaccess-error-no-groups-given'              => 'Es wurden keine Gruppen angegeben. Verwende das Tag wie folgt: <bs:pageaccess groups="sysop" />',
	'bs-pageaccess-error-not-member-of-given-groups'   => 'Du bist nicht Mitglied der angegebenen Gruppen. Um zu verhindern, dass Du dich aus der Seite aussperrst, wurde das Speichern deaktiviert.',
	'bs-pageaccess-error-included-forbidden-template'  => 'Du hast das Template "$1" eingebunden, auf das du keine Leseberechtigung hast. Um zu verhindern, dass Du dich aus der Seite aussperrst, wurde das Speichern deaktiviert.',
	'bs-pageaccess-tag-groups-desc'                    => 'Gib die Gruppen an, die exklusiven Zugriff auf die Seite erhalten sollen. Mehrere Gruppen können durch Kommata getrennt werden.',
	'pageaccess'                                       => 'Durch PageAccess geschützte Seiten'
);

$messages['de-formal'] = array(
	'bs-pageaccess-error-no-groups-given'              => 'Es wurden keine Gruppen angegeben. Verwenden Sie das Tag wie folgt: <bs:pageaccess groups="sysop" />',
	'bs-pageaccess-error-not-member-of-given-groups'   => 'Sie sind nicht Mitglied der angegebenen Gruppen. Um zu verhindern, dass Sie sich aus der Seite aussperren, wurde das Speichern deaktiviert.',
	'bs-pageaccess-error-included-forbidden-template'  => 'Sie haben das Template "$1" eingebunden, auf das Sie keine Leseberechtigung haben. Um zu verhindern, dass Sie sich aus der Seite aussperren, wurde das Speichern deaktiviert.',
	'bs-pageaccess-tag-groups-desc'                    => 'Geben Sie die Gruppen an, die exklusiven Zugriff auf die Seite erhalten sollen. Mehrere Gruppen können durch Kommata getrennt werden.',
	'pageaccess'                                       => 'Durch PageAccess geschützte Seiten'
);

$messages['en'] = array(
	'bs-pageaccess-extension-description'              => 'Controls access on page level.',
	'PageAccess'                                       => 'PageAccess',
	'bs-pageaccess-error-no-groups-given'              => 'No groups were specified. Use the tag as follows: <bs:pageaccess groups="sysop" />',
	'bs-pageaccess-error-not-member-of-given-groups'   => 'You\'re not a member of the given groups. In order to prevent you from locking yourself out, saving was disabled.',
	'bs-pageaccess-error-included-forbidden-template'  => 'You\'ve tried to use the template "$1" to which you don\'t have read access. In order to prevent you from locking yourself out, saving was disabled.',
	'bs-pageaccess-tag-groups-desc'                    => 'Specify the groups that should have exclusive access to this page. Multiple groups can be separated by commas.',
	'pageaccess'                                       => 'Pages secured by PageAccess'
);

$messages['qqq'] = array();
