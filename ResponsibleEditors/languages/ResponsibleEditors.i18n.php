<?php
/**
 * Internationalisation file for HideTitle
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage HideTitle
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['en'] = array(
	'ResponsibleEditors'                                                   => 'Responsible Editors',
	'prefs-ResponsibleEditors'                                             => 'Responsible Editors',
	'bs-responsibleeditors-extension-description'                          => 'Enables MediaWiki to assign responsible editors to an article.',

	//Prefs
	'bs-responsibleeditors-pref-ActivatedNamespaces'                       => 'Enabled namespaces',
	'bs-responsibleeditors-pref-AutoAssignOnArticleCreation'               => 'Auto-assign on article creation (if permission is okay)',
	'bs-responsibleeditors-pref-ResponsibleEditorMayChangeAssingment'      => 'Assigned editor may change assignment',
	'bs-responsibleeditors-pref-SpecialPageDefaultPageSize'                => 'SpecialPage: page size of the table',
	'bs-responsibleeditors-pref-EMailNotificationOnResponsibilityChange'   => 'E-mail notification on responsibility change',
	'bs-responsibleeditors-pref-ResponsibleEditorMayChangeAssignment'      => 'Responsible editor may change own responsibilites',
	'bs-responsibleeditors-pref-AddArticleToREWatchLists'                  => 'Add articles to responsible editors watchlists',
	'bs-responsibleeditors-pref-EChange'                                   => 'Notify when article has been changed',
	'bs-responsibleeditors-pref-EDelete'                                   => 'Notify when article has been deleted',
	'bs-responsibleeditors-pref-EMove'                                     => 'Notify when article has been moved',
	'bs-responsibleeditors-pref-AutoPermissions'                           => 'Automatic assignment of rights',
	'bs-responsibleeditors-statebartopresponsibleeditorsentries'           => 'Responsible editors',
	'bs-responsibleeditors-statebarbodyresponsibleeditorsentries'          => 'Responsible editors',
	'bs-responsibleeditors-right-responsibleeditors-changeresponsibility'  => 'Change responsibility',
	'bs-responsibleeditors-right-responsibleeditors-viewspecialpage'       => 'Access SpecialPage &quot;Responsible Editors&quot;',
	'bs-responsibleeditors-right-responsibleeditors-takeresponsibility'    => 'Take responsiblity',
	'bs-responsibleeditors-success-ajax'                                   => 'Responsibility sucessfully changed.',
	'bs-responsibleeditors-error-ajax-invalid-parameter'                   => 'Invalid values provided. Please contact your admin.',
	'bs-responsibleeditors-error-ajax-not-allowed'                         => 'Access denied.',
	'bs-responsibleeditors-contentactions-label'                           => 'Change responsibility',
	'bs-responsibleeditors-statebartop-icon-alt'                           => 'There is a responsible editor for this article',
	'bs-responsibleeditors-statebarbody-headline-singular'                 => 'Responsible Editor',
	'bs-responsibleeditors-statebarbody-headline-plural'                   => 'Responsible Editors',
	'bs-responsibleeditors-main-namespace'                                 => 'Main',
	'bs-responsibleeditors-all-namespaces'                                 => 'All namespaces',
	'bs-responsibleeditors-remove-assignment'                              => 'Without responsibility',
	'bs-responsibleeditors-error-specialpage-given-article-does-not-exist' => 'The given article does not exist.',
	'bs-responsibleeditors-mail-subject-new-editor'                        => 'Responsiblity for "$1" assigned',
	'bs-responsibleeditors-mail-text-new-editor'                           => "the user $1 assigned you to \"$2\".
After logging into the wiki you can open the article with this link:
	$3
If you have any questions please refer to $1.",
	'bs-responsibleeditors-mail-subject-former-editor'                     => 'Responsibility for "$1" revoked',
	'bs-responsibleeditors-mail-text-former-editor'                        => "the user $1 revoked the responsibility for the article \"$2\"
The new {{PLURAL:$4|responsible editor is|responsible editors are}}: $3.

After logging into the wiki you can open the article with this link:
	$5
If you have any questions please refer to $1.",
	'bs-responsibleeditors-mail-subject-re-article-changed'                => 'The article "$1" has been edited by $2',
	'bs-responsibleeditors-mail-text-re-article-changed'                   => "The article \"$1\" for which you are registered as a responsible editor has been edited by $2.
After logging into the wiki you can open the article with this link:
		$3",
	'bs-responsibleeditors-mail-subject-re-article-deleted'                => 'The article "$1" has been deleted by $2',
	'bs-responsibleeditors-mail-text-re-article-deleted'                   => "The article \"$1\" for which you are registered as a responsible editor has been deleted by $2.
After logging into the wiki you can open the article with this link:
	$3",
	'bs-responsibleeditors-mail-subject-re-article-moved'                  => 'The article "$1" has been moved by $2',
	'bs-responsibleeditors-mail-text-re-article-moved'                     => "The article \"$1\" for which you are registered as a responsible editor has been moved to \"$2\" by $3.
After logging into the wiki you can open the article with this link:
	$4",
	'responsibleeditors'                                            => 'Responsible Editors',
	'responsibleeditors-desc'                                       => 'This specialpage enables you to set responsible editors for pages',
	'bs-responsibleeditors-no-own-responsibilities'                 => 'There are no articles which you are the responsible editor for',
	'bs-responsibleeditors-yourresponsibilities'                    => 'Your responsibilities',
	'bs-responsibleeditors-yourresponsibilitiesdesc'                => 'List of articles witch you are responsible for',
	// TODO: add rights- messages
	'action-responsibleeditors-viewspecialpage'     => 'view pages which are protected with the "ResponsibleEditors-Viewspecialpage" right',
	'log-name-bs-responsibleeditors' => 'Responsible editors log',

	//JavaScript
	'bs-responsibleeditors-availableEditors'=> 'Available editors',
	'bs-responsibleeditors-assignedEditors'=> 'Assigned editors',
	'bs-responsibleeditors-title'=> 'Responsible editors',
	'bs-responsibleeditors-titleEditors'=> 'Responsible editors',
	'bs-responsibleeditors-cmChangeRespEditors'=> 'Change responsible editors',
	'bs-responsibleeditors-columnHeaderArticle'=> 'Article',
	'bs-responsibleeditors-columnHeaderResponsibleEditor'=> 'Responsible Editors',
	'bs-responsibleeditors-columnHeaderNamespace'=> 'Namespace',
	'bs-responsibleeditors-columnHeaderActions'=> 'Actions',
	'bs-responsibleeditors-tipEditAssignment'=> 'Change responsibility',
	'bs-responsibleeditors-tipRemoveAssignement'=> 'Remove assignement',
	'bs-responsibleeditors-btnDisplayModeText'=> 'View',
	'bs-responsibleeditors-rbDisplayModeOnlyAssignedText'=> 'Show only articles with responsibility already assigned.',
	'bs-responsibleeditors-rbDisplayModeOnlyNotAssigned'=> 'Show only articles without assigned responsibility.',
	'bs-responsibleeditors-rbDisplayModeAll'=> 'Show all articles.',
	'bs-responsibleeditors-ptbDisplayMsgText'=> 'Article {0} - {1} of {2}',
	'bs-responsibleeditors-ptbEmptyMsgText'=> 'No articles found.',
	'bs-responsibleeditors-ptbBeforePageText'=> 'Page',
	'bs-responsibleeditors-ptbAfterPageText'=> 'of {0}',
	'bs-responsibleeditors-cbNamespacesEmptyText'=> 'Select a namespace',
	'bs-responsibleeditors-cbNamespacesLable'=> 'Namespace',
	'bs-responsibleeditors-columnResponsibleEditorNotSet '=> 'Not assigned',
	'bs-responsibleeditors-dialogTitle'=> 'Verantwortlichen Redakteur zuweisen',
	'bs-responsibleeditors-pnlDescriptionText'=> 'Wählen Sie einen verantwortlichen Redakteur aus dem Drop-Down-Menü aus.',
	'bs-responsibleeditors-pnlSucessText'=> 'Die Verantwortlichkeit wurde erfolgreich aktuallisiert!',
	'bs-responsibleeditors-pnlFailureText'=> 'Beim Ändern der Verantwortlichkeit ist ein Fehler aufgetreten. Bitte kontaktieren Sie Ihren Administrator.',
	'bs-responsibleeditors-cbLabelEditorList'=> 'Verantwortlicher Reakteur',
	'bs-responsibleeditors-cbEmptyText'=> 'Kein Redakteur gewählt',
	'bs-responsibleeditors-loadMaskMessage'=> 'Übertrage Daten...'
);

$messages['de'] = array(
	'ResponsibleEditors'                                                   => 'Verantwortliche Redakteure',
	'prefs-ResponsibleEditors'                                             => 'Verantwortliche Redakteure',
	'bs-responsibleeditors-extension-description'                          => 'Erlaubt es Artikeln verantwortliche Redakteure zuzuweisen.',

	//Prefs
	'bs-responsibleeditors-pref-ActivatedNamespaces'                       => 'Aktiviert für Namensräume',
	'bs-responsibleeditors-pref-AutoAssignOnArticleCreation'               => 'Verantwortung beim Anlegen einer Seite automatisch zuweisen',
	'bs-responsibleeditors-pref-ResponsibleEditorMayChangeAssingment'      => 'Verwantwortung kann abgetreten werden',
	'bs-responsibleeditors-pref-SpecialPageDefaultPageSize'                => 'Spezialseite: Seitengröße der Tabelle',
	'bs-responsibleeditors-pref-EMailNotificationOnResponsibilityChange'   => 'Bei Änderung der Verantwortlichkeit beteiligte Benutzer benachrichtigen',
	'bs-responsibleeditors-pref-ResponsibleEditorMayChangeAssignment'      => 'Verantwortlicher Redakteur kann eigene Veranwortlichkeiten ändern',
	'bs-responsibleeditors-pref-AddArticleToREWatchLists'                  => 'Artikel zur Beobachtungsliste der Verantwortlichen Redakteure hinzufügen',
	'bs-responsibleeditors-pref-EChange'                                   => 'Benachrichtigen wenn ein Artikel geändert wurde',
	'bs-responsibleeditors-pref-EDelete'                                   => 'Benachrichtigen wenn ein Artikel gelöscht wurde',
	'bs-responsibleeditors-pref-EMove'                                     => 'Benachrichtigen wenn ein Artikel verschoben wurde',
	'bs-responsibleeditors-pref-AutoPermissions'                           => 'Automatische Zuweisung von Rechten',
	'bs-responsibleeditors-statebartopresponsibleeditorsentries'           => 'Verantwortliche Redakteure',
	'bs-responsibleeditors-statebarbodyresponsibleeditorsentries'          => 'Verantwortliche Redakteure',
	'bs-responsibleeditors-right-responsibleeditors-changeresponsibility'  => 'Verantwortlichkeit ändern',
	'bs-responsibleeditors-right-responsibleeditors-viewspecialpage'       => 'Spezialseite &quot;Verantwortliche Redakteure&quot; betrachten',
	'bs-responsibleeditors-right-responsibleeditors-takeresponsibility'    => 'Verantwortung übernehmen',
	'bs-responsibleeditors-success-ajax'                                   => 'Die Verantwortlichkeit wurde erfolgreich geändert.',
	'bs-responsibleeditors-error-ajax-invalid-parameter'                   => 'Es wurden unzulässige Werte übergeben. Bitte kontaktiere Deinen Administrator.',
	'bs-responsibleeditors-error-ajax-not-allowed'                         => 'Du bist nicht berechtigt diese Aktion durchzuführen.',
	'bs-responsibleeditors-contentactions-label'                           => 'Verantwortlichkeit ändern',
	'bs-responsibleeditors-statebartop-icon-alt'                           => 'Diesem Artikel ist ein verantwortlicher Redakteur zugewiesen',
	'bs-responsibleeditors-statebarbody-headline-singular'                 => 'Verantwortlicher Redakteur',
	'bs-responsibleeditors-statebarbody-headline-plural'                   => 'Verantwortliche Redakteure',
	'bs-responsibleeditors-main-namespace'                                 => 'Main',
	'bs-responsibleeditors-all-namespaces'                                 => 'Alle Namensräume',
	'bs-responsibleeditors-remove-assignment'                              => 'Ohne Verantwortlichkeit',
	'bs-responsibleeditors-error-specialpage-given-article-does-not-exist' => 'Der angegebene Artikel existiert nicht.',
	'bs-responsibleeditors-mail-subject-new-editor'                        => 'Verantwortlichkeit für Artikel "$1" übertragen',
	'bs-responsibleeditors-mail-text-new-editor'                           => "Soeben wurde Dir durch den Benutzer $1 die Verantwortlichkeit für den Artikel \"$2\" übertragen.
Nach Anmeldung am Wiki kannst Du den Artikel mit folgendem Link erreichen:
	$3

Solltest Du Fragen zur Übertragung der Verantwortlichkeit auf Dich haben, wende Dich bitte an $1.

",
	'bs-responsibleeditors-mail-subject-former-editor'                     => 'Verantwortlichkeit für Artikel "$1" entzogen',
	'bs-responsibleeditors-mail-text-former-editor'                        => "Soeben wurde Dir durch den Benutzer $1 die Verantwortlichkeit für den Artikel \"$2\" entzogen.
{{PLURAL:$4|Der neue verantwortliche Redakteur ist|Die neuen verantwortlichen Redakteure sind}}: $3

Nach Anmeldung am Wiki kannst Du den Artikel mit folgendem Link erreichen:
	$5

Solltest Du Fragen zur Änderung der Verantwortlichkeit haben, wende Dich bitte an $1.
",
	'bs-responsibleeditors-mail-subject-re-article-changed'                => 'Der Artikel "$1" wurde von $2 bearbeitet',
	'bs-responsibleeditors-mail-text-re-article-changed'                   => "Der Artikel \"$1\", für den Du als verantwortlicher Redakteur eingetragen bist, wurde von $2 bearbeitet

Nach Anmeldung am Wiki kannst Du den Artikel mit folgendem Link erreichen:
	$3",
	'bs-responsibleeditors-mail-subject-re-article-deleted'                => 'Der Artikel "$1" wurde von $2 gelöscht',
	'bs-responsibleeditors-mail-text-re-article-deleted'                   => "Der Artikel \"$1\", für den Du als verantwortlicher Redakteur eingetragen bist, wurde von $2 gelöscht

Nach Anmeldung am Wiki kannst Du den Artikel mit folgendem Link erreichen:
	$3",
	'bs-responsibleeditors-mail-subject-re-article-moved'                  => 'Der Artikel "$1" wurde von $2 verschoben',
	'bs-responsibleeditors-mail-text-re-article-moved'                     => "Der Artikel \"$1\", für den Du als verantwortlicher Redakteur eingetragen bist, wurde von $3 nach \"$2\" verschoben
Nach Anmeldung am Wiki kannst Du den Artikel mit folgendem Link erreichen:
	$4",
	'bs-responsibleeditors-mail-text-auto-generated-article-summary'       => 'Durch die Erweiterung ResponsibleEditors automatisch angelegt.',
	'responsibleeditors'                                            => 'Verantwortliche Redakteure',
	'responsibleeditors-desc'                                       => 'Mit dieser Spezialseite kannst du Artikeln verantwortliche Redakteure zuweisen',
	'bs-responsibleeditors-no-own-responsibilities'                 => 'Du bist für keinen Artikel der verantworliche Redakteur',
	'bs-responsibleeditors-yourresponsibilities'                    => 'Deine Verantwortlichkeiten',
	'bs-responsibleeditors-yourresponsibilitiesdesc'                => 'Liste der Artikel, für die du verantwortlich bist',
	'action-responsibleeditors-viewspecialpage'                     => 'Seiten, die durch das "ResponsibleEditors-Viewspecialpage" Recht geschützt sind, aufzurufen',
	'log-name-bs-responsibleeditors' => 'Verantwortliche Redakteure Logbuch',

	// Javascript i18n
	'bs-responsibleeditors-availableEditors' =>'Verfügbare Redakteure',
	'bs-responsibleeditors-assignedEditors' =>'Zugewiesene Redakteure',
	'bs-responsibleeditors-save' =>'Speichern',
	'bs-responsibleeditors-cancel' =>'Abbrechen',
	'bs-responsibleeditors-title' =>'Verantwortliche Redakteure',
	'bs-responsibleeditors-titleEditors'=> 'Verantwortliche Redakteure',
	'bs-responsibleeditors-cmChangeRespEditors'=> 'Verantwortliche Redakteure ändern',
	'bs-responsibleeditors-columnHeaderArticle'=> 'Artikel',
	'bs-responsibleeditors-columnHeaderResponsibleEditor'=> 'Verantwortliche Redakteure',
	'bs-responsibleeditors-columnHeaderNamespace'=> 'Namensraum',
	'bs-responsibleeditors-columnHeaderActions'=> 'Aktionen',
	'bs-responsibleeditors-tipEditAssignment'=> 'Verantwortlichkeit ändern',
	'bs-responsibleeditors-tipRemoveAssignement'=> 'Zuweisung entfernen',
	'bs-responsibleeditors-btnDisplayModeText '=> 'Ansicht',
	'bs-responsibleeditors-rbDisplayModeOnlyAssignedText'=> 'Artikel denen bereits ein Redakteur zugewiesen ist.',
	'bs-responsibleeditors-rbDisplayModeOnlyNotAssigned'=> 'Artikel denen kein Redakteur zugewiesen ist.',
	'bs-responsibleeditors-rbDisplayModeAll'=> 'Alle Artikel.',
	'bs-responsibleeditors-ptbDisplayMsgText'=> 'Artikel {0} - {1} von {2}',
	'bs-responsibleeditors-ptbEmptyMsgText'=> 'Keine Artikel gefunden.',
	'bs-responsibleeditors-ptbBeforePageText'=> 'Seite',
	'bs-responsibleeditors-ptbAfterPageText'=> 'von {0}',
	'bs-responsibleeditors-cbNamespacesEmptyText'=> 'Namensraum auswählen',
	'bs-responsibleeditors-cbNamespacesLable'=> 'Namensraum',
	'bs-responsibleeditors-columnResponsibleEditorNotSet'=> 'Nicht zugewiesen.',
	'bs-responsibleeditors-dialogTitle'=> 'Verantwortlichen Redakteur zuweisen',
	'bs-responsibleeditors-pnlDescriptionText'=> 'Wählen Sie einen verantwortlichen Redakteur aus dem Drop-Down-Menü aus.',
	'bs-responsibleeditors-pnlSucessText'=> 'Die Verantwortlichkeit wurde erfolgreich aktuallisiert!',
	'bs-responsibleeditors-pnlFailureText'=> 'Beim Ändern der Verantwortlichkeit ist ein Fehler aufgetreten. Bitte kontaktieren Sie Ihren Administrator.',
	'bs-responsibleeditors-cbLabelEditorList'=> 'Verantwortlicher Reakteur',
	'bs-responsibleeditors-cbEmptyText'=> 'Kein Redakteur gewählt',
	'bs-responsibleeditors-loadMaskMessage'=> 'Übertrage Daten...',
);

$messages['de-formal'] = array(
	'bs-responsibleeditors-error-ajax-invalid-parameter'    => 'Es wurden unzulässige Werte übergeben. Bitte kontaktieren Sie Ihren Administrator.',
	'bs-responsibleeditors-error-ajax-not-allowed'          => 'Sie sind nicht berechtigt diese Aktion durchzuführen.',
	'bs-responsibleeditors-mail-text-new-editor'            => "Soeben wurde Ihnen durch den Benutzer $1 die Verantwortlichkeit für den Artikel \"$2\" übertragen.
Nach Anmeldung am Wiki können Sie den Artikel mit folgendem Link erreichen:
	$3

Sollten Sie Fragen zur Übertragung der Verantwortlichkeit auf Sie haben, wenden Sie sich bitte an $1.",
	'bs-responsibleeditors-mail-text-former-editor'         => "Soeben wurde Ihnen durch den Benutzer $1 die Verantwortlichkeit für den Artikel \"$2\" entzogen.
{{PLURAL:$4|Der neue verantwortliche Redakteur ist|Die neuen verantwortlichen Redakteure sind}}: $3

Nach Anmeldung am Wiki können Sie den Artikel mit folgendem Link erreichen:
	$5

Sollten Sie Fragen zur Änderung der Verantwortlichkeit haben, wenden Sie sich bitte an $4.",
	'bs-responsibleeditors-mail-text-re-article-changed'    => "Der Artikel \"$1\", für den Sie als verantwortlicher Redakteur eingetragen sind, wurde von $2 bearbeitet.
Nach Anmeldung am Wiki können Sie den Artikel mit folgendem Link erreichen:
	$3",
	'bs-responsibleeditors-mail-text-re-article-deleted'    => "Der Artikel \"$1\", für den Sie als verantwortlicher Redakteur eingetragen sind, wurde von $2 gelöscht.
Nach Anmeldung am Wiki können Sie den Artikel mit folgendem Link erreichen:
	$3",
	'bs-responsibleeditors-mail-text-re-article-moved'      => "Der Artikel \"$1\", für den Sie als verantwortlicher Redakteur eingetragen sind, wurde von $3 nach \"$2\" verschoben.
Nach Anmeldung am Wiki können Sie den Artikel mit folgendem Link erreichen:
	$3",
	'responsibleeditors-desc' => 'Mit dieser Spezialseite können Sie Artikeln verantwortliche Redakteure zuweisen',
	'bs-responsibleeditors-yourresponsibilities' => 'Ihre Verantwortlichkeiten',
	'bs-responsibleeditors-no-own-responsibilities' => 'Sie sind für keinen Artikel der verantworliche Redakteur',
	'bs-responsibleeditors-yourresponsibilitiesdesc' => 'Liste der Artikel, für die Sie verantworlich sind',
);

$messages['qqq'] = array();
