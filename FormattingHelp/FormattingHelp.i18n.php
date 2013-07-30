<?php
/**
 * Internationalisation file for ArticleInfo
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    $Id: ArticleInfo.i18n.php 6401 2012-09-06 11:03:08Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage ArticleInfo
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['de'] = array(
	'bs-formattinghelp-extension-description' => 'Zeigt oberhalb des WikiText-Editors eine Kurzreferenz der Wiki-Syntax.',
	'bs-formattinghelp-formatting'            => 'Formatierhilfe',
	'bs-formattinghelp-help-text'             => "<table border='0'>
<tbody>
  <tr>
    <td width='20%'><strong>Fett:</strong></td>
    <td> 3 Hochkommas &quot;'&quot;. Beispiel: <nowiki>'''Text'''</nowiki></td>
  </tr>
  <tr>
    <td width='20%'><strong>Kursiv:</strong></td>
    <td> 2 Hochkommas &quot;'&quot;. Beispiel: <nowiki>''Text''</nowiki></td>
  </tr>
  <tr>
    <td><strong>Leerzeichen:</strong></td>
    <td> Sonderzeichen: &amp;nbsp;</td>
  </tr>
  <tr>
    <td><strong>Formatierung unterdr&uuml;cken:</strong></td>
    <td> &quot;&lt;nowiki&gt;&quot;. Beispiel: &lt;nowiki&gt;Text&lt;/nowiki&gt;</td>
  </tr>
  <tr>
    <td><strong>Farbiger Text:</strong></td>
    <td>&quot;&lt;font&gt;&quot;. Beispiel: &lt;font color=\"#DDBB65\"&gt;Text&lt;/font&gt;</td>
  </tr>
   <tr>
    <td><strong>&Uuml;berschriften:</strong></td>
    <td> 1-5 Gleicheitszeichen &quot;=&quot;. Beispiel: == &Uuml;berschrift 2 == </td>
  </tr>
  <tr>
    <td><strong>Zeilenumbruch:</strong></td>
    <td> &quot;&lt;br /&gt;&quot; </td>
  </tr>
  <tr>
    <td><strong>Listen:</strong></td>
    <td> &quot;*&quot; f&uuml;r einfache Listen, &quot;#&quot; f&uuml;r nummerierte Listen</td>
  </tr>
  
  <tr>
    <td><strong>Interner Link:</strong></td>
    <td> Zwei eckige Klammern. Beispiel: <nowiki>[[Text]]</nowiki></td>
  </tr>
   <tr>
    <td><strong>Interner Link mit Alternativtext:</strong></td>
    <td> Zwei eckige Klammern. Beispiel: <nowiki>[[Namensraum:Titel|Beschriftung]]</nowiki></td>
  </tr>
  <tr>
    <td><strong>Externer Link:</strong></td>
    <td> Die URL. Beispiel: <nowiki>http://www.hallowelt.biz</nowiki></td>
  </tr>
  <tr>
    <td><strong>Externer Link mit Alternativtext:</strong></td>
    <td> Eine eckige Klammer. Beispiel: <nowiki>[http://www.hallowelt.biz Homepage Hallo Welt!]</nowiki></td>
  </tr>
  <tr>
    <td><strong>Bilder:</strong></td>
    <td><nowiki>[[Datei:Dateiname|50px|Beschreibung|link=Seitentitel]]</nowiki></td>
  </tr>
  <tr>
    <td><strong>Linie:</strong></td>
    <td>&quot;----&quot;</td>
  </tr>
  <tr>
    <td><strong>Vorlage:</strong></td>
    <td><nowiki>{{Vorlagenname|Parameter1=Wert1}}</nowiki></td>
  </tr>
  <tr>
    <td><strong>Kategorie:</strong></td>
    <td><nowiki>[[Kategorie:Name der Kategorie]]</nowiki></td>
  </tr>
  <tr>
    <td valign='top'><strong>Tabelle:</strong></td>
    <td><nowiki>{|<br/>|-<br/>|Zelle1|Zelle2<br/>|-<br/>|Zelle3|Zelle4<br/>|}</nowiki></td>
  </tr>
</tbody>
</table>"
);

$messages['de-formal'] = array();

$messages['en'] = array(
	'bs-formattinghelp-extension-description' => 'Displays a help screen in the wiki edit view.',
	'bs-formattinghelp-formatting'            => 'Formatting help',
	'bs-formattinghelp-help-text'             => "<table border='0'>
<tbody>
  <tr>
    <td width='20%'><strong>Bold:</strong></td>
    <td> 3 apostrophes &quot;'&quot;. Example: '''Text'''</td>
  </tr>
  <tr>
    <td width='20%'><strong>Italics:</strong></td>
    <td> 2 apostrophes &quot;'&quot;. Example: ''Text''</td>
  </tr>
  <tr>
    <td><strong>Blank:</strong></td>
    <td> Custom character: &amp;nbsp;</td>
  </tr>
  <tr>
    <td><strong>Prevent formatting:</strong></td>
    <td> &quot;&lt;nowiki&gt;&quot;. Example:
&lt;nowiki&gt;Text&lt;/nowiki&gt;</td>
  </tr>
  <tr>
    <td><strong>Colored text:</strong></td>
    <td> &quot;&lt;font&gt;&quot;. Example: &lt;font
color=\"#DDBB65\"&gt;Text&lt;/font&gt;</td>
  </tr>
   <tr>
    <td><strong>Headlines:</strong></td>
    <td> 1-5 equal signs &quot;=&quot;. Example: ==
Heading 2 == </td>
  </tr>
  <tr>
    <td><strong>Line break:</strong></td>
    <td> &quot;&lt;br /&gt;&quot; </td>
  </tr>
  <tr>
    <td><strong>Lists:</strong></td>
    <td> &quot;*&quot; for unordered lists, &quot;#&quot; for ordered lists</td>
  </tr>
  <tr>
    <td><strong>Internal link:</strong></td>
    <td> Two square brackets. Example: <nowiki>[[Text]]</nowiki> </td>
  </tr>
  <tr>
    <td><strong>Internal link with alternative text:</strong></td>
    <td> Two square brackets. Example: <nowiki>[[Namespace:Title|Label]]</nowiki> </td>
  </tr>
  <tr>
    <td><strong>External link:</strong></td>
    <td> The URL. Example: http://www.ibm.com</td>
  </tr>
  <tr>
    <td><strong>External link with alternative text:</strong></td>
    <td> Square bracket. Example: [http://www.ibm.com IBM]</td>
  </tr>
  <tr>
    <td><strong>Images:</strong></td>
    <td>[[File:filename|50px|description|link=page title]]</td>
  </tr>
  <tr>
    <td><strong>Line:</strong></td>
    <td>&quot;----&quot;</td>
  </tr>
  <tr>
    <td><strong>Template:</strong></td>
    <td><nowiki>{{Template name|Parameter1=Value1}}</nowiki></td>
  </tr>
  <tr>
    <td><strong>Category:</strong></td>
    <td><nowiki>[[Category:Name of the category]]</nowiki></td>
  </tr>
  <tr>
    <td valign='top'><strong>Table:</strong></td>
    <td><nowiki>{|<br/>|-<br/>|Cell1|Cell2<br/>|-<br/>|Cell3|Cell4<br/>|}</nowiki></td>
  </tr>
</tbody>
</table>"
);

$messages['qqq'] = array();