<?php
/**
 * Internationalisation file for InsertMagic
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @version    $Id: InsertMagic.i18n.php 9209 2013-04-19 13:44:02Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage InsertMagic
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['en'] = array(
	'bs-insertmagic-extension-description' => 'Adds a dialog windows to edit mode that allows the insertion of MagicWords and tags.',
	'bs-insertmagic' => 'Insert MagicWord',
	
	//HINT: http://www.mediawiki.org/wiki/Help:Magic_words
	'bs-insertmagic-__NOTOC__' => "Hides the table of contents (TOC).",
	'bs-insertmagic-__FORCETOC__' => "Forces the table of contents to appear at its normal position (above the first header).",
	'bs-insertmagic-__TOC__' => "Places a table of contents at the word's current position (overriding <code>__NOTOC__</code>). If this is used multiple times, the table of contents will appear at the first word's position.",
	'bs-insertmagic-__NOEDITSECTION__' => "Hides the section edit links beside headings. This is especially useful, where a heading is created from within a template: the normal wiki section-edit would in this case edit the template code, which is normally counterintuitive to the user. If a template contains multiple headings, it may be necessary to add <code>__NOEDITSECTION__</code> after each heading.",
	'bs-insertmagic-__NEWSECTIONLINK__' => "Adds a link ('+' by default) beside the 'edit' tab for adding a new section on a non-talk page (see Adding a section to the end).",
	'bs-insertmagic-__NONEWSECTIONLINK__' => "Removes the link beside the 'edit' tab on pages in talk namespaces.",
	'bs-insertmagic-__NOGALLERY__' => "Used on a category page, replaces thumbnails in the category view with normal links.",
	'bs-insertmagic-__HIDDENCAT__' => "Used on a category page, hides the category from the lists of categories in its members and parent categories (there is an option in the user preferences to show them).",
	'bs-insertmagic-__NOCONTENTCONVERT__' => "On wikis with language variants, don't perform any content language conversion (character and phase) in article display; for example, only show Chinese (zh) instead of variants like zh_cn, zh_tw, zh_sg, or zh_hk.",
	'bs-insertmagic-__NOTITLECONVERT__' => "On wikis with language variants, don't perform language conversion on the title (all other content is converted).",
	'bs-insertmagic-__END__' => "Explicitly marks the end of the article, to prevent MediaWiki from removing trailing whitespace. Removed in 19213.",
	'bs-insertmagic-__INDEX__' => "Tell search engines to index the page (overrides \$wgArticleRobotPolicies, but not robots.txt).",
	'bs-insertmagic-__NOINDEX__' => "Tell search engines not to index the page (ie, do not list in search engines' results).",
	'bs-insertmagic-__STATICREDIRECT__' => "On redirect pages, don't allow MediaWiki to automatically update the link when someone moves a page and checks 'Update any redirects that point to the original title'.",
	
	'bs-insertmagic-{{CURRENTYEAR}}' => "Year, i.e. {{CURRENTYEAR}}",
	'bs-insertmagic-{{CURRENTMONTH}}' => "Month (zero-padded number)",
	'bs-insertmagic-{{CURRENTMONTHNAME}}' => "Month (name)",
	'bs-insertmagic-{{CURRENTMONTHNAMEGEN}}' => "Month (genitive form)",
	'bs-insertmagic-{{CURRENTMONTHABBREV}}' => "Month (abbreviation)",
	'bs-insertmagic-{{CURRENTDAY}}' => "Day of the month (unpadded number)",
	'bs-insertmagic-{{CURRENTDAY2}}' => "Day of the month (zero-padded number)",
	'bs-insertmagic-{{CURRENTDOW}}' => "Day of the week (unpadded number), 0 (for Sunday) through 6 (for Saturday)",
	'bs-insertmagic-{{CURRENTDAYNAME}}' => "Day of the week (name)",
	'bs-insertmagic-{{CURRENTTIME}}' => "Time (24-hour HH:mm format)",
	'bs-insertmagic-{{CURRENTHOUR}}' => "Hour (24-hour zero-padded number)",
	'bs-insertmagic-{{CURRENTWEEK}}' => "Week (number)",
	'bs-insertmagic-{{CURRENTTIMESTAMP}}' => "YYYYMMDDHHmmss timestamp",
	'bs-insertmagic-{{SITENAME}}' => "The wiki's site name (\$wgSitename).",
	'bs-insertmagic-{{SERVER}}' => "domain URL (\$wgServer)",
	'bs-insertmagic-{{SERVERNAME}}' => "domain name (No longer dependent on \$wgServerName as of version 1.17)",
	'bs-insertmagic-{{SCRIPTPATH}}' => "relative script path (\$wgScriptPath)",
	'bs-insertmagic-{{STYLEPATH}}' => "relative style path (\$wgStylePath)",
	'bs-insertmagic-{{CURRENTVERSION}}' => "The wiki's MediaWiki version.",
	'bs-insertmagic-{{CONTENTLANGUAGE}}' => "The wiki's default interface language (\$wgLanguageCode)",
	'bs-insertmagic-{{PAGEID}}' => "Returns the page identifier.",
	'bs-insertmagic-{{PAGESIZE:"page name"}}' => "'''[expensive]''' Returns the byte size of the specified page. Use '<code>|R</code>' to get raw (unformatted) numbers.",
	'bs-insertmagic-{{PROTECTIONLEVEL:"action"}}' => "Outputs the protection level (e.g. 'autoconfirmed', 'sysop') for a given action (e.g. 'edit', 'move') on the current page or an empty string if not protected.",
	'bs-insertmagic-{{REVISIONID}}' => "Unique revision ID",
	'bs-insertmagic-{{REVISIONDAY}}' => "Day edit was made (unpadded number)",
	'bs-insertmagic-{{REVISIONDAY2}}' => "Day edit was made (zero-padded number)",
	'bs-insertmagic-{{REVISIONMONTH}}' => "Month edit was made (zero-padded number as of 1.17+, unpadded number in prior versions)",
	'bs-insertmagic-{{REVISIONMONTH1}}' => "Month edit was made (unpadded number)",
	'bs-insertmagic-{{REVISIONYEAR}}' => "Year edit was made",
	'bs-insertmagic-{{REVISIONTIMESTAMP}}' => "Timestamp as of time of edit",
	'bs-insertmagic-{{REVISIONUSER}}' => "The username of the user who made the most recent edit to the page, or the current user when previewing an edit",
	'bs-insertmagic-{{DISPLAYTITLE:"title"}}' => "Format the current page's title header. The value must be equivalent to the default title: only capitalization changes and replacing spaces with underscores are allowed (this can be changed with \$wgRestrictDisplayTitle). It can be disabled or enabled by \$wgAllowDisplayTitle; disabled by default before 1.10+, enabled by default thereafter.",
	'bs-insertmagic-{{DEFAULTSORT:"sortkey"}}' => "Used for categorizing pages, sets a default category sort key. For example if you put {{DEFAULTSORT:Smith, John}} at the end of John Smith, the page would be sorted under 'S' by default in categories. It can take a second argument of noerror or noreplace to suppress error messages when multiple defaultsortkey's are used on one page or to make it do nothing if multiple defaultsortkey's are used.",
	
	'bs-insertmagic-gallery' => "It's easy to make a gallery of thumbnails with the <code>&lt;gallery&gt;</code> tag. Note that the image code is not enclosed in brackets when enclosed in gallery tags. Captions are optional, and may contain wiki links or other formatting. If an image is in the File namespace, the <code>File:</code> prefix can be omitted.
		
;caption=&quot;{caption}&quot;: (caption text between double quotes for more than a word) sets a caption centered atop the gallery.
;widths={width}px: sets the widths of the images, default 120px. Note the plural, widths
;heights={heights}px: sets the (max) heights of the images.
;perrow={integer}: sets the number of images per row.
;showfilename={anything}: Show the filenames of the images in the individual captions for each image (1.17+)
",
	'bs-insertmagic-gallery-code' => "<gallery>
File:Example.jpg|Item 1
File:Example.jpg|a link to [[Help:Contents]]
File:Example.jpg
File:Example.jpg|alt=An example image. It has flowers
File:Example.jpg| ''italic caption''
</gallery>",
	'bs-insertmagic-nowiki' => 'Escape wiki markup',
	'bs-insertmagic-nowiki-code' => "<nowiki>[[WikiText]] that may '''not''' be parsed</nowiki>",
	'bs-insertmagic-noinclude' => "Anything between <code>&lt;noinclude&gt;</code> and <code>&lt;/noinclude></code> will be processed and displayed only when the page is being viewed directly; it will not be included or substituted. Possible applications are:

#Categorising templates, see template documentation.
#Interlanguage links to similar templates in other languages.
#Pages in the MediaWiki namespace.
",
	'bs-insertmagic-noinclude-code' => "<noinclude>This text won't be included with the template it is placed within</noinclude>",
	'bs-insertmagic-includeonly' => "The converse is <code>&lt;includeonly&gt;</code>. Text between <code>&lt;includeonly&gt;</code> and <code>&lt;/includeonly&gt;</code> will be processed and displayed only when the page is being included. Applications include:

#Adding all pages containing a given template to a category, but not the template itself.
#Avoiding messy rendering on the template page.

Note that spaces and newlines between the general content and the tagged part belong to the general content. If they are not desired the include tag should directly follow the content on the same line",
	'bs-insertmagic-includeonly-code' => "<includeonly>Lorem impsum dolor sit amet...</includeonly>",
	'bs-insertmagic-redirect' => 'Create a redirect to another article.',
	'bs-insertmagic-redirect-code' => "#REDIRECT [[Insert target]]",
	
	//JavaScript
	'bs-insertmagic-dlg_title' => 'Insert Tag or MagicWord',
	'bs-insertmagic-type_tags' => 'Tags',
	'bs-insertmagic-type_variables' => 'Variables',
	'bs-insertmagic-type_switches' => 'Behavior switches',
	'bs-insertmagic-type_redirect' => 'Redirect',
	'bs-insertmagic-btn_preview' => 'Preview',
	'bs-insertmagic-label_first' => '1. Choose Tag or MagicWord',
	'bs-insertmagic-label_second' => '2. Modify code',
	'bs-insertmagic-label_third' => '3. Check result',
	'bs-insertmagic-label_desc' => 'Description'
);

$messages['de'] = array(
	'bs-insertmagic-extension-description' => 'Fügt dem Bearbeitenmodus einen Dialog hinzu der das Einfügen von MagicWords und Tags erlaubt.',
	'bs-insertmagic' => 'MagicWord einfügen',
	
	//HINT: http://www.mediawiki.org/wiki/Help:Magic_words
	'bs-insertmagic-__NOTOC__' => "Blendet das Inhaltsverzeichnis des Artikels aus.",
	'bs-insertmagic-__FORCETOC__' => "Erzwingt das Einblenden des Inhaltsverzeichnisses an seiner normalen Position (Oberhalb der ersten Überschrift).",
	'bs-insertmagic-__TOC__' => "Places a table of contents at the word's current position (overriding <code>__NOTOC__</code>). If this is used multiple times, the table of contents will appear at the first word's position.",
	'bs-insertmagic-__NOEDITSECTION__' => "Hides the section edit links beside headings. This is especially useful, where a heading is created from within a template: the normal wiki section-edit would in this case edit the template code, which is normally counterintuitive to the user. If a template contains multiple headings, it may be necessary to add <code>__NOEDITSECTION__</code> after each heading.",
	'bs-insertmagic-__NEWSECTIONLINK__' => "Adds a link ('+' by default) beside the 'edit' tab for adding a new section on a non-talk page (see Adding a section to the end).",
	'bs-insertmagic-__NONEWSECTIONLINK__' => "Removes the link beside the 'edit' tab on pages in talk namespaces.",
	'bs-insertmagic-__NOGALLERY__' => "Used on a category page, replaces thumbnails in the category view with normal links.",
	'bs-insertmagic-__HIDDENCAT__' => "Used on a category page, hides the category from the lists of categories in its members and parent categories (there is an option in the user preferences to show them).",
	'bs-insertmagic-__NOCONTENTCONVERT__' => "On wikis with language variants, don't perform any content language conversion (character and phase) in article display; for example, only show Chinese (zh) instead of variants like zh_cn, zh_tw, zh_sg, or zh_hk.",
	'bs-insertmagic-__NOTITLECONVERT__' => "On wikis with language variants, don't perform language conversion on the title (all other content is converted).",
	'bs-insertmagic-__END__' => "Explicitly marks the end of the article, to prevent MediaWiki from removing trailing whitespace. Removed in 19213.",
	'bs-insertmagic-__INDEX__' => "Tell search engines to index the page (overrides \$wgArticleRobotPolicies, but not robots.txt).",
	'bs-insertmagic-__NOINDEX__' => "Tell search engines not to index the page (ie, do not list in search engines' results).",
	'bs-insertmagic-__STATICREDIRECT__' => "On redirect pages, don't allow MediaWiki to automatically update the link when someone moves a page and checks 'Update any redirects that point to the original title'.",
	
	'bs-insertmagic-{{CURRENTYEAR}}' => "Das aktuelle Jahr, z.B. {{CURRENTYEAR}}",
	'bs-insertmagic-{{CURRENTMONTH}}' => "Der aktuelle Monat (mit vorangestellter Null)",
	'bs-insertmagic-{{CURRENTMONTHNAME}}' => "Der aktuelle Monatsname",
	'bs-insertmagic-{{CURRENTMONTHNAMEGEN}}' => "Der aktuelle Monatsname im Genitiv",
	'bs-insertmagic-{{CURRENTMONTHABBREV}}' => "Der aktuelle Monatsname als Abkürzung",
	'bs-insertmagic-{{CURRENTDAY}}' => "Day of the month (unpadded number)",
	'bs-insertmagic-{{CURRENTDAY2}}' => "Day of the month (zero-padded number)",
	'bs-insertmagic-{{CURRENTDOW}}' => "Day of the week (unpadded number), 0 (for Sunday) through 6 (for Saturday)",
	'bs-insertmagic-{{CURRENTDAYNAME}}' => "Day of the week (name)",
	'bs-insertmagic-{{CURRENTTIME}}' => "Time (24-hour HH:mm format)",
	'bs-insertmagic-{{CURRENTHOUR}}' => "Hour (24-hour zero-padded number)",
	'bs-insertmagic-{{CURRENTWEEK}}' => "Week (number)",
	'bs-insertmagic-{{CURRENTTIMESTAMP}}' => "YYYYMMDDHHmmss timestamp",
	'bs-insertmagic-{{SITENAME}}' => "The wiki's site name (\$wgSitename).",
	'bs-insertmagic-{{SERVER}}' => "domain URL (\$wgServer)",
	'bs-insertmagic-{{SERVERNAME}}' => "domain name (No longer dependent on \$wgServerName as of version 1.17)",
	'bs-insertmagic-{{SCRIPTPATH}}' => "relative script path (\$wgScriptPath)",
	'bs-insertmagic-{{STYLEPATH}}' => "relative style path (\$wgStylePath)",
	'bs-insertmagic-{{CURRENTVERSION}}' => "The wiki's MediaWiki version.",
	'bs-insertmagic-{{CONTENTLANGUAGE}}' => "The wiki's default interface language (\$wgLanguageCode)",
	'bs-insertmagic-{{PAGEID}}' => "Returns the page identifier.",
	'bs-insertmagic-{{PAGESIZE:"page name"}}' => "'''[expensive]''' Returns the byte size of the specified page. Use '<code>|R</code>' to get raw (unformatted) numbers.",
	'bs-insertmagic-{{PROTECTIONLEVEL:"action"}}' => "Outputs the protection level (e.g. 'autoconfirmed', 'sysop') for a given action (e.g. 'edit', 'move') on the current page or an empty string if not protected.",
	'bs-insertmagic-{{REVISIONID}}' => "Unique revision ID",
	'bs-insertmagic-{{REVISIONDAY}}' => "Day edit was made (unpadded number)",
	'bs-insertmagic-{{REVISIONDAY2}}' => "Day edit was made (zero-padded number)",
	'bs-insertmagic-{{REVISIONMONTH}}' => "Month edit was made (zero-padded number as of 1.17+, unpadded number in prior versions)",
	'bs-insertmagic-{{REVISIONMONTH1}}' => "Month edit was made (unpadded number)",
	'bs-insertmagic-{{REVISIONYEAR}}' => "Year edit was made",
	'bs-insertmagic-{{REVISIONTIMESTAMP}}' => "Timestamp as of time of edit",
	'bs-insertmagic-{{REVISIONUSER}}' => "The username of the user who made the most recent edit to the page, or the current user when previewing an edit",
	'bs-insertmagic-{{DISPLAYTITLE:"title"}}' => "Format the current page's title header. The value must be equivalent to the default title: only capitalization changes and replacing spaces with underscores are allowed (this can be changed with \$wgRestrictDisplayTitle). It can be disabled or enabled by \$wgAllowDisplayTitle; disabled by default before 1.10+, enabled by default thereafter.",
	'bs-insertmagic-{{DEFAULTSORT:"sortkey"}}' => "Used for categorizing pages, sets a default category sort key. For example if you put {{DEFAULTSORT:Smith, John}} at the end of John Smith, the page would be sorted under 'S' by default in categories. It can take a second argument of noerror or noreplace to suppress error messages when multiple defaultsortkey's are used on one page or to make it do nothing if multiple defaultsortkey's are used.",
	
	'bs-insertmagic-gallery' => "It's easy to make a gallery of thumbnails with the <code>&lt;gallery&gt;</code> tag. Note that the image code is not enclosed in brackets when enclosed in gallery tags. Captions are optional, and may contain wiki links or other formatting. If an image is in the File namespace, the <code>File:</code> prefix can be omitted.
		
;caption=&quot;{caption}&quot;: (caption text between double quotes for more than a word) sets a caption centered atop the gallery.
;widths={width}px: sets the widths of the images, default 120px. Note the plural, widths
;heights={heights}px: sets the (max) heights of the images.
;perrow={integer}: sets the number of images per row.
;showfilename={anything}: Show the filenames of the images in the individual captions for each image (1.17+)
",
	'bs-insertmagic-gallery-code' => "<gallery>
Datei:Beispiel.jpg|Eintrag 1
Datei:Beispiel.jpg|Ein Link auf [[Hilfe:Inhalt]]
Datei:Beispiel.jpg
Datei:Beispiel.jpg|alt=Ein Beispiel
Datei:Beispiel.jpg| ''kursive Bildunterschrift''
</gallery>",
	'bs-insertmagic-nowiki' => 'WikiText von der Umwandlung ausschließen',
	'bs-insertmagic-nowiki-code' => "<nowiki>[[WikiText]] der '''nicht''' umgewandelt werden soll</nowiki>",
	'bs-insertmagic-noinclude' => "Anything between <code>&lt;noinclude&gt;</code> and <code>&lt;/noinclude></code> will be processed and displayed only when the page is being viewed directly; it will not be included or substituted. Possible applications are:

#Categorising templates, see template documentation.
#Interlanguage links to similar templates in other languages.
#Pages in the MediaWiki namespace.
",
	'bs-insertmagic-noinclude-code' => "<noinclude>This text won't be included with the template it is placed within</noinclude>",
	'bs-insertmagic-includeonly' => "The converse is <code>&lt;includeonly&gt;</code>. Text between <code>&lt;includeonly&gt;</code> and <code>&lt;/includeonly&gt;</code> will be processed and displayed only when the page is being included. Applications include:

#Adding all pages containing a given template to a category, but not the template itself.
#Avoiding messy rendering on the template page.

Note that spaces and newlines between the general content and the tagged part belong to the general content. If they are not desired the include tag should directly follow the content on the same line",
	'bs-insertmagic-includeonly-code' => "<includeonly>Lorem impsum dolor sit amet...</includeonly>",
	'bs-insertmagic-redirect' => 'Erstellt eine Weiterleitung zu einem anderen Artikel.',
	'bs-insertmagic-redirect-code' => "#REDIRECT [[Ziel eingeben]]",
	
	//JavaScript
	'bs-insertmagic-dlg_title' => 'Tag oder MagicWord einfügen',
	'bs-insertmagic-type_tags' => 'Tags',
	'bs-insertmagic-type_variables' => 'Variablen',
	'bs-insertmagic-type_switches' => 'Schalter',
	'bs-insertmagic-type_redirect' => 'Weiterleitung',
	'bs-insertmagic-btn_preview' => 'Vorschau',
	'bs-insertmagic-label_first' => '1. Wähle das MagicWord',
	'bs-insertmagic-label_second' => '2. Gib den Text ein',
	'bs-insertmagic-label_third' => '3. Überprüfe das Ergebnis',
	'bs-insertmagic-label_desc' => 'Kurzbeschreibung'
);

$messages['de-formal'] = array(
	'bs-insertmagic-label_first' => '1. Wählen Sie das MagicWord',
	'bs-insertmagic-label_second' => '2. Geben Sie den Text ein',
	'bs-insertmagic-label_third' => '3. Überprüfen Sie das Ergebnis',
);

$messages['qqq'] = array();