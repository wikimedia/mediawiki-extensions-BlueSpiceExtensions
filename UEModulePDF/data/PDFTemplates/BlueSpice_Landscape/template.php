<?php
/**
 * This is the main description file for the template. It contains all
 * information necessary to load and process the template.
 */

return array(

	/* A brief description. This information may be used in the user interface */
	'info' => array(
		'name'      => 'BlueSpice Landscape',
		'author'    => 'Hallo Welt!',
		'copyright' => 'Hallo Welt! GmbH',
		'url'       => 'http://www.hallowelt.com',
		'description'      => 'This is the default BlueSpice PDF Template for landscape orientation'
	),

	/**
	 * The following resources are used in the conversion from xhtml to PDF.
	 * You may reference them in your template files
	 */
	'resources' => array(
		'ATTACHMENT' => array(), //Some extra attachments to be included in every eport file
		'STYLESHEET' => array(
			'stylesheets/page.css',
			'../common/stylesheets/mediawiki.css',
			'stylesheets/styles.css',
			'../common/stylesheets/geshi-php.css',
			'../common/stylesheets/bluespice.css',
			'../common/stylesheets/tables.css',
			'../common/stylesheets/fonts.css',
			//'../common/stylesheets/debug.css',
			'../common/fonts/DejaVuSans.ttf',
			'../common/fonts/DejaVuSans-Bold.ttf',
			'../common/fonts/DejaVuSans-Oblique.ttf',
			'../common/fonts/DejaVuSans-BoldOblique.ttf',
			'../common/fonts/DejaVuSansMono.ttf',
			'../common/fonts/DejaVuSansMono-Bold.ttf',
			'../common/fonts/DejaVuSansMono-Oblique.ttf',
			'../common/fonts/DejaVuSansMono-BoldOblique.ttf'
		),
		'IMAGE' => array(
			'images/bs-header.jpg',
			'images/bs-page-background.png'
		)
	),

	/**
	 * Here you can define messages for internationalization of your template.
	 */
	'messages' => array(
		'en' => array(
			'desc'        => 'This is the default PDFTemplate for landscape orientation of BlueSpice for single article export.',
			'exportdate'  => 'Export:',
			'page'        => 'Page ',
			'of'          => ' of ',
			'disclaimer'  => 'This document was created with BlueSpice'
		),
		'de' => array(
			'desc'        => 'Dies ist das Standard-PDFTemplate für Querformat von BlueSpice für den Export einzelner Artikel.',
			'exportdate'  => 'Ausgabe:',
			'page'        => 'Seite ',
			'of'          => ' von ',
			'disclaimer'  => 'Dieses Dokument wurde erzeugt mit BlueSpice'),
		'de-formal' => array(
			'desc'        => 'Dies ist das Standard-PDFTemplate für Querformat von BlueSpice für den Export einzelner Artikel.',
			'exportdate'  => 'Ausgabe:',
			'page'        => 'Seite ',
			'of'          => ' von ',
			'disclaimer'  => 'Dieses Dokument wurde erzeugt mit BlueSpice'),
	)
);