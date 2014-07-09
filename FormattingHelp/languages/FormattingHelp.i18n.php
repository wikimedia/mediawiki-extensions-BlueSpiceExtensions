<?php

/**
 * Internationalisation file for ArticleInfo
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage ArticleInfo
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
$messages = array();

$messages['de'] = array(
	'bs-formattinghelp-extension-description' => 'Zeigt oberhalb des WikiText-Editors eine Kurzreferenz der Wiki-Syntax.',
	'bs-formattinghelp-formatting' => 'Formatierhilfe',
	'bs-formattinghelp-help-text' => "<table border='1'>
<thead>
  <tr>
    <th></th>
    <th>Beschreibung</th>
    <th>Beispiel</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td width='20%'><strong>Fett</strong></td>
    <td>3 Hochkommas</td>
    <td><nowiki>'''Text'''</nowiki></td>
  </tr>
  <tr>
    <td width='20%'><strong>Kursiv</strong></td>
    <td> 2 Hochkommas</td>
    <td><nowiki>''Text''</nowiki></td>
  </tr>
  <tr>
    <td><strong>Leerzeichen</strong></td>
    <td>Sonderzeichen</td>
    <td>&amp;nbsp;</td>
  </tr>
  <tr>
    <td><strong>Formatierung unterdr&uuml;cken</strong></td>
    <td>nowiki-Tag</td>
    <td>&lt;nowiki&gt;Text&lt;/nowiki&gt;</td>
  </tr>
  <tr>
    <td><strong>Farbiger Text</strong></td>
    <td>font-Tag mit Angabe des Farbcodes</td>
    <td>&lt;font color=\"#DDBB65\"&gt;Text&lt;/font&gt;</td>
  </tr>
   <tr>
    <td><strong>&Uuml;berschriften</strong></td>
    <td>1-5 Gleichheitszeichen erzeugen <br/>Überschriften unterschiedlicher Ebenen</td>
    <td>== &Uuml;berschrift 2 ==<br/>=== &Uuml;berschrift 3 ===</td>
  </tr>
  <tr>
    <td><strong>Zeilenumbruch</strong></td>
    <td>Zeilenumbruch einfügen</td>
    <td>&lt;br /&gt;</td>
  </tr>
  <tr>
    <td><strong>Unsortierte Liste</strong></td>
    <td>Erzeugen einer unsortierten Liste</td>
    <td>* Listenpunkt 1<br/>** Unterlistenpunkt 1<br/>* Listenpunkt 2
    </td>
  </tr>
  <tr>
    <td><strong>Nummerierte Liste</strong></td>
    <td>Erzeugen einer nummerierten Liste</td>
    <td># Listenpunkt 1<br/>## Unterlistenpunkt 1<br/># Listenpunkt 2
    </td>
  </tr>
  <tr>
    <td><strong>Interner Link</strong></td>
    <td>Zwei eckige Klammern</td>
    <td><nowiki>[[Text]]</nowiki></td>
  </tr>
   <tr>
    <td><strong>Interner Link mit Alternativtext</strong></td>
    <td>Zwei eckige Klammern mit einer<br/>Pipe vor dem Beschreibungstext</td>
    <td><nowiki>[[Namensraum:Titel|Beschriftung]]</nowiki></td>
  </tr>
  <tr>
    <td><strong>Externer Link</strong></td>
    <td>Die URL wird in einen Link umgewandelt</td>
    <td><nowiki>http://www.hallowelt.biz</nowiki></td>
  </tr>
  <tr>
    <td><strong>Externer Link mit Alternativtext</strong></td>
    <td>Eine eckige Klammer mit Leerzeichen <br/>vor dem Beschreibungstext</td>
    <td><nowiki>[http://www.hallowelt.biz Homepage Hallo Welt!]</nowiki></td>
  </tr>
  <tr>
    <td><strong>Bilder</strong></td>
    <td>Einfügen von Bildern mit Metadaten</td>
    <td><nowiki>[[Datei:Dateiname|50px|Beschreibung|link=Seitentitel]]</nowiki></td>
  </tr>
  <tr>
    <td><strong>Linie</strong></td>
    <td>Horizontale Linie</td>
    <td>----</td>
  </tr>
  <tr>
    <td><strong>Vorlage</strong></td>
    <td>Einfügen einer Vorlage</td>
    <td><nowiki>{{Vorlagenname|Parameter1=Wert1}}</nowiki></td>
  </tr>
  <tr>
    <td><strong>Kategorie</strong></td>
    <td>Einfügen einer Kategorie</td>
    <td><nowiki>[[Kategorie:Name der Kategorie]]</nowiki></td>
  </tr>
  <tr>
    <td valign='top'><strong>Tabelle</strong></td>
    <td>Einfügen einer Tabelle <br/> mit 2 Zeilen und 2 Spalten</td>
    <td><nowiki>{|<br/>|-<br/>|Zelle1|Zelle2<br/>|-<br/>|Zelle3|Zelle4<br/>|}</nowiki></td>
  </tr>
</tbody>
</table>"
);

$messages['de-formal'] = array();

$messages['en'] = array(
	'bs-formattinghelp-extension-description' => 'Displays a help screen in the wiki edit view.',
	'bs-formattinghelp-formatting' => 'Formatting help',
	'bs-formattinghelp-help-text' => "<table border='1'>
<thead>
  <tr>
    <th></th>
    <th>Description</th>
    <th>Example</th>
  </tr>
</thead>
<tbody>
  <tr>
    <td width='20%'><strong>Bold</strong></td>
    <td>3 apostrophes</td>
    <td><nowiki>'''Text'''</nowiki></td>
  </tr>
  <tr>
    <td width='20%'><strong>Italics</strong></td>
    <td> 2 apostrophes</td>
    <td><nowiki>''Text''</nowiki></td>
  </tr>
  <tr>
    <td><strong>Blank</strong></td>
    <td>Special character</td>
    <td>&amp;nbsp;</td>
  </tr>
  <tr>
    <td><strong>Prevent formatting</strong></td>
    <td>nowiki-Tag</td>
    <td>&lt;nowiki&gt;Text&lt;/nowiki&gt;</td>
  </tr>
  <tr>
    <td><strong>Colored Text</strong></td>
    <td>font-Tag with declaration of the color code</td>
    <td>&lt;font color=\"#DDBB65\"&gt;Text&lt;/font&gt;</td>
  </tr>
   <tr>
    <td><strong>Headlines</strong></td>
    <td>1-5 equal signs create headlines <br/>with different levels</td>
    <td>== Heading 2 == <br/>=== Heading 3 ===</td>
  </tr>
  <tr>
    <td><strong>Line break</strong></td>
    <td>Insert line break</td>
    <td>&lt;br /&gt;</td>
  </tr>
  <tr>
    <td><strong>Bullet list</strong></td>
    <td>Creates a bullet list</td>
    <td>* bullet point 1<br/>** sub bullet point 1<br/>* bullet point 2
    </td>
  </tr>
  <tr>
    <td><strong>Numbered list</strong></td>
    <td>Creates a numbered list</td>
    <td># bullet point 1<br/>## sub bullet point 1<br/># bullet point 2
    </td>
  </tr>
  <tr>
    <td><strong>Internal link</strong></td>
    <td>Two square brackets</td>
    <td><nowiki>[[Text]]</nowiki></td>
  </tr>
   <tr>
    <td><strong>Internal link with alternative text</strong></td>
    <td>Two square brackets with a pipe <br/>before the description text</td>
    <td><nowiki>[[Namespace:Title|Label]]</nowiki></td>
  </tr>
  <tr>
    <td><strong>External link</strong></td>
    <td>The URL will be converted to a link.</td>
    <td><nowiki>http://www.hallowelt.biz</nowiki></td>
  </tr>
  <tr>
    <td><strong>External link with alternative text</strong></td>
    <td>One square bracket with a space<br/> before the description text</td>
    <td><nowiki>[http://www.hallowelt.biz Homepage Hallo Welt!]</nowiki></td>
  </tr>
  <tr>
    <td><strong>Images</strong></td>
    <td>Insert Images with metadata</td>
    <td><nowiki>[[File:Filename|50px|label|link=pagetitle]]</nowiki></td>
  </tr>
  <tr>
    <td><strong>Line</strong></td>
    <td>horizontal line</td>
    <td>----</td>
  </tr>
  <tr>
    <td><strong>Template</strong></td>
    <td>Insert a Template</td>
    <td><nowiki>{{Templatename|parameter1=value1}}</nowiki></td>
  </tr>
  <tr>
    <td><strong>Category</strong></td>
    <td>Insert a category</td>
    <td><nowiki>[[Category:categoryname]]</nowiki></td>
  </tr>
  <tr>
    <td valign='top'><strong>Table</strong></td>
    <td>Insert a table with<br/> 2 lines and 2 columns</td>
    <td><nowiki>{|<br/>|-<br/>|cell1|cell2<br/>|-<br/>|cell3|cell4<br/>|}</nowiki></td>
  </tr>
</tbody>
</table>"
);

$messages['qqq'] = array();
