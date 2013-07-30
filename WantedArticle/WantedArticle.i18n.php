<?php
/**
 * Internationalisation file for WantedArticle
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    $Id: WantedArticle.i18n.php 9415 2013-05-16 13:01:38Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage WantedArticle
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['de'] = array(
	'bs-wantedarticle-extension-description'                        => 'Ein Textfeld, über das man einer Liste gewünschte Seiten hinzufügen kann.',
	'bs-wantedarticle-ajax-error-no-parameter'                      => 'Es wurde kein Titel angegeben',
	'bs-wantedarticle-ajax-error-suggested-article-already-exists'  => 'Die Seite $1 existiert bereits.',
	'bs-wantedarticle-ajax-error-suggested-article-already-on-list' => 'Die Seite $1 wurde bereits vorgeschlagen.',
	'bs-wantedarticle-ajax-error-invalid-chars'                     => 'Die folgenden Zeichen sind in einem Titel nicht erlaubt: '+"\n",
	'bs-wantedarticle-edit-comment-suggestion-added'                => '$1 wurde über das Formular eingetragen.',
	'bs-wantedarticle-success-suggestion-entered'                   => 'Dein Vorschlag wurde eingetragen',
	'bs-wantedarticle-integer-range-validation-too-low'             => 'Der angegebene Wert ist zu niedrig',
	'bs-wantedarticle-integer-range-validation-too-high'            => 'Der angegebene Wert ist zu hoch',
	'bs-wantedarticle-sort-value-unknown'                           => 'Es sind nur die Werte "title" (Nach Titel) und "time" (Nach Zeit) gültig',
	'bs-wantedarticle-order-value-unknown'                          => 'Es sind nur die Werte ASC (aufsteigend) und DESC (absteigend) gültig',
	'bs-wantedarticle-article-removed'                              => 'Der Artikel $1 wurde von der Liste entfernt',
	'bs-wantedarticle-single-textfield-suggestbutton-text'          => 'Vorschlagen',
	'bs-wantedarticle-single-textfield-createbutton-text'           => 'Anlegen',
	'bs-wantedarticle-single-textfield-defaulttext'                 => 'Artikeltitel',
	'bs-wantedarticle-tag-wantedarticle-desc'						=> "Stellt eine Liste der gewünschten Artikel dar.
Verfügbare Parameter:
;count: Numerischer Freitext
;sort: Mögliche Werte sind <code>time</code>, <code>title</code>
;order: Mögliche Werte sind <code>ASC</code>, <code>DESC</code>
;title: Freitext
;type: Mögliche Werte sind <code>list</code>, <code>queue</code>",
	'bs-wantedarticle-tag-more-linktext'                            => 'mehr...',
	'bs-wantedarticle-tag-no-wishes'                                => 'Keine gewünschten Seiten vorhanden.',
	'bs-wantedarticle-tag-default-title'                            => 'Gewünschte Seiten',
	'WantedArticle'                                                 => 'Gewünschte Seiten',
	'prefs-WantedArticle'                                           => 'Gewünschte Seiten',
	'bs-wantedarticle-pref-AddMode'                                 => 'Hinzufüge-Modus (<strong>sort</strong>|append|prepend)',
	'bs-wantedarticle-pref-Separator'                               => 'Trennzeichen (<strong>,</strong>)',
	'bs-wantedarticle-pref-GotoTarget'                              => 'Dem Aufruf folgen',
	'bs-wantedarticle-pref-InLines'                                 => 'Zeilenweises hinzufügen',
	'bs-wantedarticle-pref-IncludeLimit'                            => 'Maximale Länge der Wunschliste',
	'bs-wantedarticle-pref-InlineText'                              => 'Text in Zeile',
	'bs-wantedarticle-pref-Label'                                   => 'Überschrift <em>Seite Vorschlagen</em> anzeigen',
	'bs-wantedarticle-pref-NewpageLabel'                            => 'Überschrift <em>Neue Seite</em> anzeigen',
	'bs-wantedarticle-pref-ShowLink'                                => 'Link zu <em>Liste der Vorschläge</em> anzeigen',
	'bs-wantedarticle-pref-NewpageButtonText'                       => 'Beschriftung der <em>Neue Seite anlegen</em>-Schaltfläche',
	'bs-wantedarticle-pref-NewpageInlineText'                       => 'Standardbefüllung des Textfeldes',
	'bs-wantedarticle-pref-ButtonText'                              => 'Beschriftung der <em>Neue Seite vorschlagen</em>-Schaltfläche',
	'bs-wantedarticle-pref-DeleteExisting'                          => 'Existierende Artikel aus Wunschliste löschen',
	'bs-wantedarticle-pref-ShowCreate'                              => '<em>Anlegen</em> anzeigen',
	'bs-wantedarticle-pref-DeleteOnCreation'                        => 'Beim Anlegen eines Artikels diesen aus der Liste entfernen',
	'bs-wantedarticle-pref-DataSourceTemplateTitle'                 => 'Titel des Artikels, in dem die Vorschläge aufgelistet werden (Wird im &quot;Vorlage&quot; Namensraum erstellt)',
	'bs-wantedarticle-pref-Sort'                                    => 'Sortierreihenfolge',
	'bs-wantedarticle-pref-Order'                                   => 'Sortierrichtung',
	'bs-wantedarticle-pref-sort-time'                               => 'Zeit',
	'bs-wantedarticle-pref-sort-title'                              => 'Titel',
	'bs-wantedarticle-pref-order-asc'                               => 'aufsteigend',
	'bs-wantedarticle-pref-order-desc'                              => 'absteigend',
	'bs-wantedarticle-create-page'                                  => 'Seite $1 erstellen.',
	'bs-wantedarticle-suggest-page'                                 => 'Seite $1 vorschlagen.',
	
	//JavaScript
	'bs-wantedarticle-info_dialog_title'                      => 'Hinweis',
	'bs-wantedarticle-info_nothing_entered'                   => 'Bitte gib einen zulässigen Titel ein.',
	'bs-wantedarticle-info_title_contains_invalid_characters' => 'Die folgenden Zeichen sind in einem Titel nicht erlaubt: '
);

$messages['de-formal'] = array(
	'bs-wantedarticle-info_nothing_entered'                   => 'Bitte geben Sie einen zulässigen Titel ein.',
);

$messages['en'] = array(
	'bs-wantedarticle-extension-description'                        => 'Add an article to the wanted article list.',
	'bs-wantedarticle-ajax-error-no-parameter'                      => 'No title provided.',
	'bs-wantedarticle-ajax-error-suggested-article-already-exists'  => 'The suggested article $1 already exitis.',
	'bs-wantedarticle-ajax-error-suggested-article-already-on-list' => 'The suggested article $1 is already in list.',
	'bs-wantedarticle-ajax-error-invalid-chars'                     => 'The following characters are not valid for use in an article title: '+"\n",
	'bs-wantedarticle-edit-comment-suggestion-added'                => '$1 entered by form.',
	'bs-wantedarticle-success-suggestion-entered'                   => 'Your suggestion was entered to the list.',
	'bs-wantedarticle-integer-range-validation-too-low'             => 'Provided value is to low',
	'bs-wantedarticle-integer-range-validation-too-high'            => 'Provided value is to high',
	'bs-wantedarticle-sort-value-unknown'                           => 'Only values "title" and "time" are valid',
	'bs-wantedarticle-order-value-unknown'                          => 'Only values ASC (acending) and DESC (descendig) are valid',
	'bs-wantedarticle-article-removed'                              => 'The article $1 has been removed from the list',
	'bs-wantedarticle-single-textfield-suggestbutton-text'          => 'Suggest',
	'bs-wantedarticle-single-textfield-createbutton-text'           => 'Create',
	'bs-wantedarticle-single-textfield-defaulttext'                 => 'Title',
	'bs-wantedarticle-tag-wantedarticle-desc'						=> "Renders a list of wanted articles.
Valid attributes:
;count: Numerical free text
;sort: Possible values are <code>time</code>, <code>title</code>
;order: Possible values are <code>ASC</code>, <code>DESC</code>
;title: Free text
;type: Mögliche Werte sind <code>list</code>, <code>queue</code>",
	'bs-wantedarticle-tag-more-linktext'                            => 'more...',
	'bs-wantedarticle-tag-no-wishes'                                => 'No wanted articles available.',
	'bs-wantedarticle-tag-default-title'                            => 'Wanted articles',
	'WantedArticle'                                                 => 'Wanted article',
	'prefs-WantedArticle'                                           => 'Wanted article',
	'bs-wantedarticle-pref-AddMode'                                 => 'Append mode (<strong>sort</strong>|append|prepend)',
	'bs-wantedarticle-pref-Separator'                               => 'Separator (<strong>,</strong>)',
	'bs-wantedarticle-pref-GotoTarget'                              => 'Go to the target',
	'bs-wantedarticle-pref-InLines'                                 => 'InLines',
	'bs-wantedarticle-pref-IncludeLimit'                            => 'Include limits',
	'bs-wantedarticle-pref-InlineText'                              => 'Inline Text',
	'bs-wantedarticle-pref-Label'                                   => 'Label',
	'bs-wantedarticle-pref-NewpageLabel'                            => 'Label for <em>New page</em>',
	'bs-wantedarticle-pref-ShowLink'                                => 'Show hyperlink',
	'bs-wantedarticle-pref-NewpageButtonText'                       => 'Text for <em>New page</em> button',
	'bs-wantedarticle-pref-NewpageInlineText'                       => 'Text for texfield',
	'bs-wantedarticle-pref-ButtonText'                              => 'Text for button',
	'bs-wantedarticle-pref-DeleteExisting'                          => 'Delete existing entries',
	'bs-wantedarticle-pref-ShowCreate'                              => 'Show <em>Create</em>',
	'bs-wantedarticle-pref-DeleteOnCreation'                        => 'Remove list entry if created',
	'bs-wantedarticle-pref-DataSourceTemplateTitle'                 => 'Article title to place the suggestions (Is created in &quot;Template&quot; namespace)',
	'bs-wantedarticle-pref-Sort'                                    => 'Sort by',
	'bs-wantedarticle-pref-Order'                                   => 'Sortdirection',
	'bs-wantedarticle-pref-sort-time'                               => 'Time',
	'bs-wantedarticle-pref-sort-title'                              => 'Title',
	'bs-wantedarticle-pref-order-asc'                               => 'ascending',
	'bs-wantedarticle-pref-order-desc'                              => 'descending',
	'bs-wantedarticle-create-page'                                  => 'Create article $1',
	'bs-wantedarticle-suggest-page'                                 => 'Suggest article $1.',
	
	//JavaScript
	'bs-wantedarticle-info_dialog_title'                      => 'Information',
	'bs-wantedarticle-info_nothing_entered'                   => 'Please provide a valid title.',
	'bs-wantedarticle-info_title_contains_invalid_characters' => 'The following characters are not valid for use in an article title: ' + '<br />'
);

$messages['qqq'] = array();