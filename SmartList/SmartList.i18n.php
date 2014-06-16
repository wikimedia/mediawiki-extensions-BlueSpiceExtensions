<?php
/**
 * Internationalisation file for SmartList
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage SmartList
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['de'] = array(
	'bs-smartlist-extension-description' => 'Zeigt die letzten drei Änderungen im Wiki an.',
	'SmartList' => 'SmartList',
	'prefs-SmartList' => 'SmartList',
	'bs-smartlist-recent-changes' => 'Letzte Änderungen',
	'bs-smartlist-no-entries' => 'Es wurden keine Einträge gefunden.',
	'bs-smartlist-invalid-namespaces' => 'Bitte überprüfen Sie diese Namespaces: $1.',
	'bs-smartlist-comment' => 'Kommentar',
	'bs-smartlist-pref-Heading' => 'Widget-Titel',
	'bs-smartlist-pref-Count' => 'Anzahl Einträge',
	'bs-smartlist-pref-Comments' => 'Zeige Kommentar',
	'bs-smartlist-pref-Date' => 'Zeige Datum',
	'bs-smartlist-pref-DateFormat' => 'Datumsformat',
	'bs-smartlist-pref-Namespaces' => 'Namensräume (Whitelist)',
	'bs-smartlist-pref-ExcludeNamespaces' => 'Namensräume (Blacklist)',
	'bs-smartlist-pref-Categories' => 'Kategorien (Whitelist)',
	'bs-smartlist-pref-Categories-title' => 'Kategorien (Whitelist)',
	'bs-smartlist-pref-Categories-message' => 'Diese Kategorie zur Whitelist hinzufügen.',
	'bs-smartlist-pref-ExcludeCategories' => 'Kategorien (Blacklist)',
	'bs-smartlist-pref-ExcludeCategories-title' => 'Kategorien (Blacklist)',
	'bs-smartlist-pref-ExcludeCategories-message' => 'Diese Kategorie zur Blacklist hinzufügen.',
	'bs-smartlist-pref-CategoryMode' => 'Verknüpfung der Kategorien',
	'bs-smartlist-pref-Period' => 'Zeitraum',
	'bs-smartlist-pref-Mode' => 'Modus (<strong>recentchanges</strong>|changesofweek|ratings)',
	'bs-smartlist-pref-ShowMinorChanges' => 'Geringfügige Änderungen anzeigen',
	'bs-smartlist-pref-ShowOnlyNewArticles' => 'Nur neue Artikel anzeigen',
	'bs-smartlist-pref-Trim' => 'Maximale Anzahl der Zeichen für Titel',
	'bs-smartlist-pref-ShowText' => 'Seitentext mit anzeigen',
	'bs-smartlist-pref-TrimText' => 'Maximale Anzahl der Zeichen für den Seitentext',
	'bs-smartlist-pref-Order' => 'Reihenfolge',
	'bs-smartlist-pref-sort' => 'Sortierung',
	'bs-smartlist-pref-ShowNamespace' => 'Namensräume mit anzeigen',
	'bs-smartlist-asc' => 'Aufsteigend',
	'bs-smartlist-desc' => 'Absteigend',
	'bs-smartlist-AND' => '"Und"-Verknüpft',
	'bs-smartlist-OR' => '"Oder"-Verknüpft',
	'bs-smartlist-day' => 'Tag',
	'bs-smartlist-week' => 'Woche',
	'bs-smartlist-month' => 'Monat',
	'bs-smartlist-time' => 'Zeit',
	'bs-smartlist-title' => 'Artikeltitel',
	'bs-smartlist-toplist-noresults' => 'Es wurden keine Ergebnisse gefunden.',
	'bs-smartlist-tag-smartlist-desc' => 'Dieser Tag stellt die Funktionalität bereit, sich auf jeder Seite Informationen über das Wiki anzeigen zu lassen.',
	'bs-smartlist-tag-newbies-desc' => 'Dieser Tag stellt die Funktionalität bereit, sich auf jeder Seite die neuesten Mitglieder dieses Wikis anzeigen zu lassen.',
	'bs-smartlist-tag-toplist-desc' => 'Dieser Tag stellt die Funktionalität bereit, sich auf jeder Seite die meistgeklickten Seiten dieses Wikis anzeigen zu lassen',
	'bs-smartlist-mostvisitedpages' => 'Meist besuchte Artikel',
	'bs-smartlist-mosteditedpages' => 'Meist editierte Artikel',
	'bs-smartlist-mostactiveusers' => 'Aktivste Benutzer (Bearbeitungen)',
	'bs-smartlist-lastedits' => 'Meine Änderungen',
	'bs-smartlist-noedits' => 'Keine Änderungen gefunden',
	'bs-smartlist-mostactiveusersdesc' => 'Liste der aktivsten Benutzer dieses Wikis, sortiert nach der Anzahl der Bearbeitungen',
	'bs-smartlist-mosteditedpagesdesc' => 'Liste der meisten editierten Artikel dieses Wikis, sortiert nach der Anzahl der Bearbeitungen',
	'bs-smartlist-mostvisitedpagesdesc' => 'Liste der meisten besuchten Artikel dieses Wikis, sortiert nach der Anzahl der Besuche',
	'bs-smartlist-lasteditsdesc' => 'Liste der von dir zuletzt bearbeiteten Artikel'
);

$messages['de-formal'] = array(
	'invalid-namespaces' => 'Bitte überprüfen Sie diese Namespaces: $1.',
	'bs-smartlist-lastedits' => 'Meine Änderungen',
	'bs-smartlist-lasteditsdesc' => 'Liste der von Ihnen zuletzt bearbeiteten Artikel',
);

$messages['en'] = array(
	'bs-smartlist-extension-description' => 'Displays the last five changes of the wiki in a list.',
	'prefs-SmartList' => 'SmartList',
	'SmartList' => 'SmartList',
	'bs-smartlist-recent-changes' => 'Recent changes',
	'bs-smartlist-no-entries' => 'No entries were found.',
	'bs-smartlist-invalid-namespaces' => 'Please check these namespaces: $1.',
	'bs-smartlist-comment' => 'comment',
	'bs-smartlist-pref-Heading' => 'Widget-Heading',
	'bs-smartlist-pref-Count' => 'Number of entries',
	'bs-smartlist-pref-Comments' => 'Show comment',
	'bs-smartlist-pref-Date' => 'Show date',
	'bs-smartlist-pref-DateFormat' => 'Date format',
	'bs-smartlist-pref-Namespaces' => 'Namespaces (Whitelist)',
	'bs-smartlist-pref-ExcludeNamespaces' => 'Namespaces (Blacklist)',
	'bs-smartlist-pref-Categories' => 'Categories (Whitelist)',
	'bs-smartlist-pref-Categories-title' => 'Kategorien (Whitelist)',
	'bs-smartlist-pref-Categories-message' => 'Diese Kategorie zur Whitelist hinzufügen.',
	'bs-smartlist-pref-ExcludeCategories' => 'Categories (Blacklist)',
	'bs-smartlist-pref-ExcludeCategories-title' => 'Kategorien (Blacklist)',
	'bs-smartlist-pref-ExcludeCategories-message' => 'Diese Kategorie zur Blacklist hinzufügen.',
	'bs-smartlist-pref-CategoryMode' => 'Combination of categories',
	'bs-smartlist-pref-Period' => 'Period',
	'bs-smartlist-pref-Mode' => 'Mode (<strong>recentchanges</strong>|changesofweek|ratings)',
	'bs-smartlist-pref-ShowMinorChanges' => 'Show minor changes',
	'bs-smartlist-pref-ShowOnlyNewArticles' => 'Show only new articles',
	'bs-smartlist-pref-Trim' => 'Maximum count of title characters',
	'bs-smartlist-pref-ShowText' => 'Show page text',
	'bs-smartlist-pref-TrimText' => 'Maximum count of page text characters',
	'bs-smartlist-pref-Order' => 'Order by',
	'bs-smartlist-pref-sort' => 'Sort by',
	'bs-smartlist-pref-ShowNamespace' => 'Show namespaces',
	'bs-smartlist-asc' => 'Aufsteigend',
	'bs-smartlist-desc' => 'Absteigend',
	'bs-smartlist-AND' => '"AND"',
	'bs-smartlist-OR' => '"OR"',
	'bs-smartlist-day' => 'Day',
	'bs-smartlist-week' => 'Week',
	'bs-smartlist-month' => 'Month',
	'bs-smartlist-time' => 'Time',
	'bs-smartlist-title' => 'Articletitle',
	'bs-smartlist-toplist-noresults' => 'No results were found.',
	'bs-smartlist-tag-smartlist-desc' => 'This tag provides you the opportunity to display informations about this wiki on every page.',
	'bs-smartlist-tag-newbies-desc' => 'This tag provides you the opportunity to display the latest members of this wiki on every page.',
	'bs-smartlist-tag-toplist-desc' => 'This tag provides you the opportunity to display a list of the most clicked pages of this wiki on every page.',
	'bs-smartlist-mostvisitedpages'  => 'Most visited pages',
	'bs-smartlist-mosteditedpages' => 'Most edited pages',
	'bs-smartlist-mostactiveusers' => 'Most active users (edits)',
	'bs-smartlist-lastedits' => 'My edits',
	'bs-smartlist-noedits' => 'No edits found',
	'bs-smartlist-mostactiveusersdesc' => 'List of most active users of this wiki sorted by number of edits',
	'bs-smartlist-mosteditedpagesdesc' => 'List of most edited articles of this wiki sorted by number of edits',
	'bs-smartlist-mostvisitedpagesdesc' => 'List of most visited articles of this wiki sorted by number of visits',
	'bs-smartlist-lasteditsdesc' => 'List of articles which have been edited by you'
);

$messages['qqq'] = array();