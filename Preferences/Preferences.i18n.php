<?php
/**
 * Internationalisation file for Preferences
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage Preferences
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['de'] = array(
	'prefs-BASE'                           => 'BlueSpice',
	'prefs-CORE'                           => 'BlueSpice - Basis-Einstellungen',
	'prefs-bluespice'                      => 'BlueSpice',
	'prefs-MW'                             => 'BlueSpice - MediaWiki-Einstellungen',
	'prefs-Statistics'                     => 'Erweiterte Statistik',
	'prefs-InfoBox'                        => 'InfoBox',
	'prefs-PagesVisited'                   => 'Besuchte Seiten',
	'prefs-Review'                         => 'Qualitätssicherung',
	'prefs-MailChanges'                    => 'E-Mail bei Änderungen',
	'bs-pref-LanguageCode'                 => 'Sprache',
	'bs-pref-Count'                        => 'Anzahl Einträge',
	'bs-pref-Namespaces'                   => 'Namensräume (Whitelist)',
	'bs-pref-ExcludeNamespaces'            => 'Namensräume (Blacklist)',
	'bs-pref-Categories'                   => 'Kategorien (Whitelist)',
	'bs-pref-ShowMinorChanges'             => 'Geringfügige Änderungen anzeigen',
	'bs-pref-Period'                       => 'Zeitraum (day|week|month)',
	'bs-pref-Mode'                         => 'Modus (<strong>recentchanges</strong>|changesofweek|ratings)',
	'bs-pref-ShowOnlyNewArticles'          => 'Nur neue Artikel anzeigen',
	'bs-pref-UserSidebarLimit'             => 'Anzahl der Einträge im Fokus',
	'bs-pref-UserSidebarNamespaces'        => 'Anzuzeigende Namensräume',
	'bs-pref-NotifyNew'                    => 'Benachrichtigung bei Bearbeitung von Seiten:',
	'bs-pref-NotifyAll'                    => 'Benachrichtigung bei neuen Seiten:',
	'bs-pref-EmailNotifyOwner'             => 'Den Besitzer eines Workflows per E-Mail über Änderungen benachrichtigen:',
	'bs-pref-DiagramWidth'                 => 'Breite des Diagramms:',
	'bs-pref-DiagramHeight'                => 'Höhe des Diagramms:',
	'bs-pref-DiagramType'                  => 'Diagrammtyp (Linien|Balken):',
	'bs-pref-DefaultFrom'                  => 'Standard Startdatum (mm/dd/jjjj):',
	'bs-pref-FileExtensions'               => 'Erlaubte Dokumentdateitypen',
	'bs-pref-FileExtensions-title'         => 'Dateiendung hinzufügen', //deprecated!?
	'bs-pref-FileExtensions-message'       => 'Welche Dateiendung möchten du hinzufügen?', //deprecated!?
	'toc-FileExtensions-title'             => 'Dateiendung hinzufügen',
	'toc-FileExtensions-message'           => 'Welche Dateiendung möchtest du hinzufügen?',
	'bs-pref-RekursionBreakLevel'          => 'max. rekursive Verschachtelung von Datenbäumen (z.Bsp. Kategorien)',
	'bs-pref-BlueSpiceScriptPath'          => 'Skriptpfad',
	'bs-pref-UseMinify'                    => 'Minifier verwenden',
	'bs-pref-ImageExtensions'              => 'Erlaubte Bilddateitypen',
	'bs-pref-ImageExtensions-title'        => 'Bilddateiendung hinzufügen', //deprecated!?
	'bs-pref-ImageExtensions-message'      => 'Welche Bilddateiendung möchten du hinzufügen?', //deprecated!?
	'toc-ImageExtensions-title'            => 'Bilddateiendung hinzufügen',
	'toc-ImageExtensions-message'          => 'Welche Bilddateiendung möchtest du hinzufügen?',
	'bs-pref-LogoPath'                     => 'Logopfad',
	'bs-pref-FaviconPath'                  => 'Faviconpfad',
	'bs-pref-MiniProfileEnforceHeight'     => 'Benutzerprofilbildhöhe erzwingen',
	'bs-pref-DefaultUserImage'             => 'Standardbenutzerbild',
	'bs-pref-AnonUserImage'                => 'Bild für anonyme Benutzer',
	'bs-pref-TestMode'                     => 'Testmodus aktivieren',
	'bs-pref-BSPingInterval'               => 'Bluespice Ping-Interval',
	'bs-preferences-extension-description' => 'Gibt dem Admin die Möglichkeit bestimmte Wikieinstellungen, z. B. welche Dateien ins Wiki eingebunden werden können, über eine Spezialseite zu konfigurieren.',
	'CORE'                                 => 'BlueSpice',
	'MW'                                   => 'MediaWiki',
	'BASE'                                 => 'Grundeinstellungen',
	'MW__FileExtensions'                   => 'Erlaubte Dateitypen',
	'MW__ImageExtensions'                  => 'Dateitypen, die als Bilder verarbeitet werden',
	'MW__LogoPath'                         => 'Logo',
	'MW__UserImage'                        => 'Benutzerprofilbild',
	'MW__DefaultUserImage'                 => 'Standardprofilbild',
	'MW__AnonUserImage'                    => 'Profilbild für anonyme und gelöschte Benutzer',
	'Core__BlueSpiceScriptPath'            => 'Relative URL zum &quot;BlueSpice Core&quot; Verzeichnis',
	'Core__MinifyPath'                     => 'Relative URL zum &quot;Minifier&quot;',
	'Core__UseMinify'                      => 'Verwende &quot;Minifier&quot;',
	'Core__LanguageCode'                   => 'Sprache der Benutzeroberfläche',
	'allow_all'                            => 'alle',
	'allow_loggedin'                       => 'angemeldete',
	'allow_register'                       => 'Benutzer können sich selbst anmelden.',
	'bs-preferences-button_save'           => 'Speichern',
	'file-too-big'                         => 'Die Datei ist größer als $1 MB',
	'bs-preferences-label'                 => 'Einstellungen',
	'mainpage_visible'                     => 'Die Hauptseite soll für alle sichtbar sein.',
	'max-upload-size'                      => 'Maximale Größe hochladbarer Dateien:',
	'megabyte'                             => 'MB',
	'prefs_saved'                          => 'Die &Auml;nderungen wurden gespeichert.',
	'server-limit'                         => 'Die Uploadgrenze des Servers ist $1 MB',
	'whos_allowed'                         => 'Wer kann dieses Wiki lesen?'
);

$messages['de-formal'] = array(
	'bs-pref-ImageExtensions-message'      => 'Welche Bilddateiendung möchten Sie hinzufügen?',
	'toc-ImageExtensions-message'          => 'Welche Bilddateiendung möchten Sie hinzufügen?',
	'bs-pref-FileExtensions-message'       => 'Welche Dateiendung möchten Sie hinzufügen?',
	'toc-FileExtensions-message'           => 'Welche Dateiendung möchten Sie hinzufügen?',
);

$messages['en'] = array(
	'prefs-BASE'                           => 'BlueSpice',
	'prefs-CORE'                           => 'BlueSpice - Core settings',
	'prefs-bluespice'                      => 'BlueSpice',
	'prefs-MW'                             => 'BlueSpice - MediaWiki settings',
	'prefs-InfoBox'                        => 'InfoBox',
	'prefs-PagesVisited'                   => 'Visited pages',
	'prefs-MailChanges'                    => 'Send email on changes',
	'prefs-Statistics'                     => 'Extended Statistics',
	'prefs-Review'                         => 'Quality assurance',
	'bs-pref-LanguageCode'                 => 'Language',
	'bs-pref-Count'                        => 'Number of entries',
	'bs-pref-Namespaces'                   => 'Namespaces (Whitelist)',
	'bs-pref-ExcludeNamespaces'            => 'Namespaces (Blacklist)',
	'bs-pref-Categories'                   => 'Categories (Whitelist)',
	'bs-pref-ShowMinorChanges'             => 'Show minor changes',
	'bs-pref-Period'                       => 'Period (day|week|month)',
	'bs-pref-Mode'                         => 'Mode (<strong>recentchanges</strong>|changesofweek|ratings)',
	'bs-pref-ShowOnlyNewArticles'          => 'Show only new articles',
	'bs-pref-UserSidebarLimit'             => 'Limit of entries in Focus',
	'bs-pref-UserSidebarNamespaces'        => 'Namespaces',
	'bs-pref-NotifyNew'                    => 'Notification for new pages:',
	'bs-pref-NotifyAll'                    => 'Notification for edits:',
	'bs-pref-EmailNotifyOwner'             => 'Notify the owner of a workfow about changes:',
	'bs-pref-DiagramWidth'                 => 'Diagram width:',
	'bs-pref-DiagramHeight'                => 'Diagram height:',
	'bs-pref-DiagramType'                  => 'Diagram type (line|bar):',
	'bs-pref-DefaultFrom'                  => 'Default startdate (mm/dd/yyyy):',
	'bs-pref-FileExtensions'               => 'Allowed file extensions',
	'bs-pref-FileExtensions-title'         => 'Add file extension', //deprecated!?
	'bs-pref-FileExtensions-message'       => 'Which file extension do you want to add to the list?', //deprecated!?
	'toc-FileExtensions-title'             => 'Add file extension',
	'toc-FileExtensions-message'           => 'Which file extension do you want to add to the list?',
	'bs-pref-RekursionBreakLevel'          => 'Max. recursive nesting of data trees (e.g. categories)',
	'bs-pref-BlueSpiceScriptPath'          => 'ScriptPath',
	'bs-pref-UseMinify'                    => 'Use minifier',
	'bs-pref-ImageExtensions'              => 'Allowed image file extensions',
	'bs-pref-ImageExtensions-title'        => 'Add image file extension', //deprecated!?
	'bs-pref-ImageExtensions-message'      => 'Which image file extension do you want to add to the list?', //deprecated!?
	'toc-ImageExtensions-title'            => 'Add image file extension',
	'toc-ImageExtensions-message'          => 'Which image file extension do you want to add to the list?',
	'bs-pref-LogoPath'                     => 'Logo path',
	'bs-pref-FaviconPath'                  => 'Favicon path',
	'bs-pref-MiniProfileEnforceHeight'     => 'Force height of Userprofilepicture',
	'bs-pref-DefaultUserImage'             => 'Default user image',
	'bs-pref-AnonUserImage'                => 'Image for anonymous users',
	'bs-pref-TestMode'                     => 'Activate MailChanges-Testmode',
	'bs-pref-BSPingInterval'               => 'Bluespice ping interval',
	'bs-preferences-extension-description' => 'Offers the possibility to admins, to configurate the whole wiki from a single SpecialPage.',
	'CORE'                                 => 'BlueSpice',
	'MW'                                   => 'MediaWiki',
	'BASE'                                 => 'Base Settings',
	'MW__FileExtensions'                   => 'Permitted filetypes',
	'MW__ImageExtensions'                  => 'Filetypes handled as images',
	'MW__LogoPath'                         => 'Logo file',
	'MW__UserImage'                        => 'Profile image',
	'MW__DefaultUserImage'                 => 'Default profile image',
	'MW__AnonUserImage'                    => 'Profile image for anonymous and deleted users',
	'Core__BlueSpiceScriptPath'            => 'Relative URL to &quot;BlueSpice Core&quot; directory',
	'Core__MinifyPath'                     => 'Relative URL to &quot;Minifier&quot;',
	'Core__UseMinify'                      => 'Use &quot;Minifier&quot;',
	'Core__LanguageCode'                   => 'Userinterface language',
	'allow_all'                            => 'everyone',
	'allow_loggedin'                       => 'logged in users',
	'allow_register'                       => 'Users may register themselves.',
	'bs-preferences-button_save'           => 'Save',
	'file-too-big'                         => 'The file size is above $1 MB',
	'bs-preferences-label'                 => 'Preferences',
	'mainpage_visible'                     => 'Main page is visible to everyone.',
	'max-upload-size'                      => 'Maximum size for file uploads:',
	'megabyte'                             => 'MB',
	'prefs_saved'                          => 'Your preferences have been saved.',
	'server-limit'                         => 'The upload limit of the server is $1 MB',
	'whos_allowed'                         => 'Who\'s allowed to read this Wiki?'
);

$messages['qqq'] = array();