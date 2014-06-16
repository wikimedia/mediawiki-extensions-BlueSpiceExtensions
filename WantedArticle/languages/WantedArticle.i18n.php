<?php
/**
 * Internationalisation file for WantedArticle
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage WantedArticle
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['de'] = array(
	'bs-wantedarticle-desc' => 'Ein Textfeld, über das man einer Liste gewünschte Seiten hinzufügen kann.',
	'bs-wantedarticle-ajax-error-no-parameter' => 'Es wurde kein Titel angegeben',
	'bs-wantedarticle-ajax-error-suggested-page-already-exists' => 'Die Seite $1 existiert bereits.',
	'bs-wantedarticle-ajax-error-suggested-page-already-on-list' => 'Die Seite $1 wurde bereits vorgeschlagen.',
	'bs-wantedarticle-ajax-error-invalid-chars' => 'Die folgenden Zeichen sind in einem Titel nicht erlaubt: '+"\n",
	'bs-wantedarticle-edit-comment-suggestion-added' => '$1 wurde über das Formular eingetragen.',
	'bs-wantedarticle-success-suggestion-entered' => 'Dein Vorschlag wurde eingetragen',
	'bs-wantedarticle-sort-value-unknown' => 'Es sind nur die Werte "title" (Nach Titel) und "time" (Nach Zeit) gültig',
	'bs-wantedarticle-order-value-unknown' => 'Es sind nur die Werte ASC (aufsteigend) und DESC (absteigend) gültig',
	'bs-wantedarticle-page-removed' => 'Der Artikel $1 wurde von der Liste entfernt',
	'bs-wantedarticle-single-textfield-suggestbutton-text' => 'Vorschlagen',
	'bs-wantedarticle-single-textfield-createbutton-text' => 'Anlegen',
	'bs-wantedarticle-single-textfield-defaulttext' => 'Artikeltitel',
	'bs-wantedarticle-tag-wantedarticle-desc' => "Stellt eine Liste der gewünschten Artikel dar.
Verfügbare Parameter:
;count: Numerischer Freitext
;sort: Mögliche Werte sind <code>time</code>, <code>title</code>
;order: Mögliche Werte sind <code>ASC</code>, <code>DESC</code>
;title: Freitext
;type: Mögliche Werte sind <code>list</code>, <code>queue</code>",
	'bs-wantedarticle-tag-more-linktext' => 'mehr...',
	'bs-wantedarticle-tag-default-title' => 'Gewünschte Seiten',
	'wantedarticle' => 'Gewünschte Seiten',
	'prefs-wantedarticle' => 'Gewünschte Seiten',
	'bs-wantedarticle-pref-includelimit' => 'Maximale Länge der Wunschliste:',
	'bs-wantedarticle-pref-deleteexisting' => 'Existierende Artikel aus Wunschliste löschen',
	'bs-wantedarticle-pref-showcreate' => 'Anlegen anzeigen',
	'bs-wantedarticle-pref-deleteoncreation' => 'Beim Anlegen eines Artikels diesen aus der Liste entfernen',
	'bs-wantedarticle-pref-datasourcetemplatetitle' => 'Titel des Artikels, in dem die Vorschläge aufgelistet werden (Wird im Vorlage Namensraum erstellt)',
	'bs-wantedarticle-pref-sort' => 'Sortierreihenfolge',
	'bs-wantedarticle-pref-order' => 'Reihenfolge',
	'bs-wantedarticle-pref-sort-time' => 'Zeit',
	'bs-wantedarticle-pref-sort-title' => 'Titel',
	'bs-wantedarticle-pref-order-asc' => 'aufsteigend',
	'bs-wantedarticle-pref-order-desc' => 'absteigend',
	'bs-wantedarticle-create-page' => 'Seite $1 erstellen.',
	'bs-wantedarticle-suggest-page' => 'Seite $1 vorschlagen.',
	'bs-wantedarticle-info_dialog_title' => 'Hinweis',
	'bs-wantedarticle-info-nothing-entered' => 'Bitte gib einen zulässigen Titel ein.',
	'bs-wantedarticle-info-title-contains-invalid-chars' => 'Die folgenden Zeichen sind in einem Titel nicht erlaubt: '
);

$messages['de-formal'] = array(
	'bs-wantedarticle-info-nothing-entered' => 'Bitte geben Sie einen zulässigen Titel ein.',
	'bs-wantedarticle-success-suggestion-entered' => 'Ihr Vorschlag wurde eingetragen',
);

$messages['en'] = array(
	'bs-wantedarticle-desc' => 'Add a page to the wanted page list.',
	'bs-wantedarticle-ajax-error-no-parameter' => 'No title provided.',
	'bs-wantedarticle-ajax-error-suggested-page-already-exists' => 'The suggested page $1 already exitis.',
	'bs-wantedarticle-ajax-error-suggested-page-already-on-list' => 'The suggested page $1 is already in list.',
	'bs-wantedarticle-ajax-error-invalid-chars' => 'The following characters are not valid for use in an page title: '+"\n",
	'bs-wantedarticle-edit-comment-suggestion-added' => '$1 entered by form.',
	'bs-wantedarticle-success-suggestion-entered' => 'Your suggestion was entered to the list.',
	'bs-wantedarticle-sort-value-unknown' => 'Only the values title and time are valid',
	'bs-wantedarticle-order-value-unknown' => 'Only values ASC (acending) and DESC (descendig) are valid',
	'bs-wantedarticle-page-removed' => 'The page $1 has been removed from the list',
	'bs-wantedarticle-single-textfield-suggestbutton-text' => 'Suggest',
	'bs-wantedarticle-single-textfield-createbutton-text' => 'Create',
	'bs-wantedarticle-single-textfield-defaulttext' => 'Title',
	'bs-wantedarticle-tag-wantedarticle-desc' => "Renders a list of wanted articles.
Valid attributes:
;count: Numerical free text
;sort: Possible values are <code>time</code>, <code>title</code>
;order: Possible values are <code>ASC</code>, <code>DESC</code>
;title: Free text
;type: Mögliche Werte sind <code>list</code>, <code>queue</code>",
	'bs-wantedarticle-tag-more-linktext' => 'more...',
	'bs-wantedarticle-tag-default-title' => 'Wanted articles',
	'wantedarticle' => 'Wanted article',
	'prefs-wantedarticle' => 'Wanted article',
	'bs-wantedarticle-pref-includelimit' => 'Include limits:',
	'bs-wantedarticle-pref-deleteexisting' => 'Delete existing entries',
	'bs-wantedarticle-pref-showcreate' => 'Show create',
	'bs-wantedarticle-pref-deleteoncreation' => 'Remove list entry if created',
	'bs-wantedarticle-pref-datasourcetemplatetitle' => 'Article title to place the suggestions (is created in template namespace)',
	'bs-wantedarticle-pref-sort' => 'Sort by',
	'bs-wantedarticle-pref-order' => 'Order',
	'bs-wantedarticle-pref-sort-time' => 'Time',
	'bs-wantedarticle-pref-sort-title' => 'Title',
	'bs-wantedarticle-pref-order-asc' => 'ascending',
	'bs-wantedarticle-pref-order-desc' => 'descending',
	'bs-wantedarticle-create-page' => 'Create article $1.',
	'bs-wantedarticle-suggest-page' => 'Suggest article $1.',
	'bs-wantedarticle-info-nothing-entered' => 'Please provide a valid title.',
	'bs-wantedarticle-info-title-contains-invalid-chars' => 'The following characters are not valid for use in an article title: ' + '<br />'
);

$messages['qqq'] = array(
	'bs-wantedarticle-desc' => 'Used in [[Special:Wiki_Admin&mode=ExtensionInfo]], description of wanted article extension.',
	'bs-wantedarticle-ajax-error-no-parameter' => 'Text for no title provided.',
	'bs-wantedarticle-ajax-error-suggested-page-already-exists'  => 'Text for the suggested page $1 already exitis.',
	'bs-wantedarticle-ajax-error-suggested-page-already-on-list' => 'Text for the suggested page $1 is already in list.',
	'bs-wantedarticle-ajax-error-invalid-chars' => 'Text for the following characters are not valid for use in a page title: '+"\n",
	'bs-wantedarticle-edit-comment-suggestion-added' => 'Text for $1 entered by form. $1 is the name of the suggested page.',
	'bs-wantedarticle-success-suggestion-entered' => 'Text for your suggestion was added to the list.',
	'bs-wantedarticle-sort-value-unknown' => 'Text for only the values title and time are valid',
	'bs-wantedarticle-order-value-unknown' => 'Text for only the values ASC (acending) and DESC (descendig) are valid',
	'bs-wantedarticle-page-removed' => 'Text for the page $1 has been removed from the list',
	'bs-wantedarticle-single-textfield-suggestbutton-text' => 'Button text for suggest',
	'bs-wantedarticle-single-textfield-createbutton-text' => 'Button text for create',
	'bs-wantedarticle-single-textfield-defaulttext' => 'Default text in input field for title',
	'bs-wantedarticle-tag-wantedarticle-desc' => "Text for renders a list of wanted pages.\n
Valid attributes:\n
;count: Numerical free text\n
;sort: Possible values are <code>time</code>, <code>title</code>\n
;order: Possible values are <code>ASC</code>, <code>DESC</code>\n
;title: Free text\n
;type: Mögliche Werte sind <code>list</code>, <code>queue</code>",
	'bs-wantedarticle-tag-more-linktext' => ' Text for more...',
	'bs-wantedarticle-tag-default-title' => 'Text for wanted pages',
	'wantedarticle' => 'Wanted pages',
	'prefs-wantedarticle' => 'Wanted pages',
	'bs-wantedarticle-pref-includelimit' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for include limits:',
	'bs-wantedarticle-pref-deleteexisting' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for delete existing entries',
	'bs-wantedarticle-pref-showcreate' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for show create',
	'bs-wantedarticle-pref-deleteoncreation' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], checkbox label for remove list entry if created',
	'bs-wantedarticle-pref-datasourcetemplatetitle' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for page title to place the suggestions (is created in template namespace)',
	'bs-wantedarticle-pref-sort' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for sort by:',
	'bs-wantedarticle-pref-order' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], label for order:',
	'bs-wantedarticle-pref-sort-time' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], option label for time',
	'bs-wantedarticle-pref-sort-title' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], option label for title',
	'bs-wantedarticle-pref-order-asc' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], option label for ascending',
	'bs-wantedarticle-pref-order-desc' => 'Option in [[Special:Wiki_Admin&mode=Preferences]], option label for descending',
	'bs-wantedarticle-create-page' => 'Text for create page $1. $1 is the entered page name',
	'bs-wantedarticle-suggest-page' => 'Test for suggest page $1. $1 is the entered page name',
	'bs-wantedarticle-info-nothing-entered' => 'Text for please provide a valid title.',
	'bs-wantedarticle-info-title-contains-invalid-chars' => 'Text for the following characters are not valid for use in a page title: \' + \'<br />'
);