<?php
/**
 * Internationalisation file for UniversalExport
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage UniversalExport
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['en'] = array(
	'UniversalExport'                                                  => 'UniversalExport',
	'prefs-UniversalExport'                                            => 'UniversalExport',
	'bs-universalexport-extension-description'                         => 'Adds export functions to MediaWiki. For example export to Pdf.',
	'bs-universalexport-pref-enNS'                                     => 'Activated namespaces',
	'bs-universalexport-pref-MetadataDefaults'                         => 'Metadaten (Standard, JSON)',
	'bs-universalexport-pref-MetadataOverrides'                        => 'Metadaten (Override, JSON)',
	'bs-universalexport-right-universalexport-export'                  => 'Use UniversalExport extension',
	'bs-universalexport-right-universalexport-export-unfiltered'       => 'Export content unfiltered',
	'bs-universalexport-right-universalexport-export-with-attachments' => 'Export articles with file attachments (PDF)',
	'bs-universalexport-right-universalexport-export-recursive'        => 'Include linked atricles in export',
	'bs-universalexport-page-title-without-param'                      => 'UniversalExport: Modules',
	'bs-universalexport-page-title-on-error'                           => 'UniversalExport: Error',
	'bs-universalexport-page-text-without-param'                       => 'This page displays information about available Export-Modules of the UniversalExports.',
	'bs-universalexport-page-text-without-param-no-modules-registered' => 'There are no modules registered.',
	'bs-universalexport-widget-title'                                  => 'Export',
	'bs-universalexport-widget-tooltip'                                => 'Expote the current page',
	'bs-universalexport-tag-pagebreak-text'                            => 'pagebreak',
	'bs-universalexport-tag-exclude-text'                              => 'This text will not be exported.',
	'bs-universalexport-tag-hidetitle-text'                            => 'The title of this article will be not be visible in the export.',
	'bs-universalexport-tag-excludearticle-text'                       => 'This page may not be exported.',
	'bs-universalexport-tag-meta-text'                                 => 'UniversalExport: Metadata',
	'bs-universalexport-tag-params-text'                               => 'UniversalExport: Parameter',
	'bs-universalexport-tag-pagebreak-desc'                            => 'If supported by chosen export module this tag forces a pagebreak in the export document.',
	'bs-universalexport-tag-noexport-desc'                             => 'Content inside this tag will not be exported.',
	'bs-universalexport-tag-meta-desc'                                 => 'Allows to add arbitrary meta data to a exported document.',
	'bs-universalexport-tag-params-desc'                               => 'Allows to set special parameters for export. Wether the parameter will be evaluated depends on the chosen export module.',
	'bs-universalexport-subject'                                       => 'Subject',
	'bs-universalexport-author'                                        => 'Author',
	'bs-universalexport-error-requested-export-module-not-found'       => 'The requested format could not be produced.',
	'bs-universalexport-error-requested-title-does-not-exist'          => 'The requested page does not exist.',
	'bs-universalexport-error-permission'                              => 'You have inssuficient permissions to export this page',
	'bs-universalexport-error-requested-title-in-category-blacklist'   => 'The requested page must not be exported (Category)',
	'bs-universalexport-statebarbodyuniversalexportmeta'               => 'UniversalExport metadata',
	'bs-universalexport-statebarbodyuniversalexportparams'             => 'UniversalExport parameter',
	'specialuniversalexport'                                           => 'UniversalExport' ,
	'universalexport'                                                  => 'UniversalExport' ,
	'specialuniversalexport-desc'                                      => 'Adds export functions to MediaWiki. For example export to Pdf.',
	'right-universalexport-export'                                     => 'Use UniversalExport extension',
	'right-universalexport-export-unfiltered'                          => 'Export content unfiltered',
	'right-universalexport-export-with-attachments'                    => 'Export articles with file attachments (PDF)',
	'right-universalexport-export-recursive'                           => 'Include linked atricles in export',
);

$messages['de'] = array(
	'UniversalExport'                                                  => 'UniversalExport',
	'prefs-UniversalExport'                                            => 'UniversalExport',
	'bs-universalexport-extension-description'                         => 'Ergänzt MediaWiki um verschiedene Exportfunktionen. Zum Beispiel Export nach Pdf.',
	'bs-universalexport-pref-enNS'                                     => 'Aktiviert für Namensräume',
	'bs-universalexport-pref-MetadataDefaults'                         => 'Metadaten (Standard, JSON)',
	'bs-universalexport-pref-MetadataOverrides'                        => 'Metadaten (Übergeordnet, JSON)',
	'bs-universalexport-right-universalexport-export'                  => 'Benutzen der UniversalExport Erweiterung',
	'bs-universalexport-right-universalexport-export-unfiltered'       => 'Inhalte ungefiltert exportieren',
	'bs-universalexport-right-universalexport-export-with-attachments' => 'Exportieren mit Dateianhängen (PDF)',
	'bs-universalexport-right-universalexport-export-recursive'        => 'Verlinkte Artikel mitexportieren',
	'bs-universalexport-page-title-without-param'                      => 'UniversalExport: Module',
	'bs-universalexport-page-title-on-error'                           => 'UniversalExport: Fehler',
	'bs-universalexport-page-text-without-param'                       => 'Diese Seite enthält Informationen über die derzeit installierten Export-Module des UniversalExports.',
	'bs-universalexport-page-text-without-param-no-modules-registered' => 'Es sind keine Module registriert.',
	'bs-universalexport-widget-title'                                  => 'Export',
	'bs-universalexport-widget-tooltip'                                => 'Die aktuelle Seite exportieren',
	'bs-universalexport-tag-pagebreak-text'                            => 'Seitenumbruch',
	'bs-universalexport-tag-exclude-text'                              => 'Dieser Text wird nicht exportiert.',
	'bs-universalexport-tag-hidetitle-text'                            => 'Der Titel dieses Artikels wird im Export nicht angezeigt.',
	'bs-universalexport-tag-excludearticle-text'                       => 'Dieser Artikel kann nicht exportiert werden.',
	'bs-universalexport-tag-meta-text'                                 => 'UniversalExport: Metadaten',
	'bs-universalexport-tag-params-text'                               => 'UniversalExport: Parameter',
	'bs-universalexport-subject'                                       => 'Betreff',
	'bs-universalexport-author'                                        => 'Autor',
	'bs-universalexport-error-requested-export-module-not-found'       => 'Das angeforderte Format kann nicht erzeugt werden.',
	'bs-universalexport-error-requested-title-does-not-exist'          => 'Die angeforderte Seite existiert nicht.',
	'bs-universalexport-error-permission'                              => 'Du hast nicht die erfoderlichen Rechte um diese Seite zu exportieren',
	'bs-universalexport-error-requested-title-in-category-blacklist'   => 'Die angeforderte Seite darf nicht exportiert werden (Kategorie)',
	'bs-universalexport-statebarbodyuniversalexportmeta'               => 'UniversalExport - Metadaten',
	'bs-universalexport-statebarbodyuniversalexportparams'             => 'UniversalExport - Parameter',
	'bs-universalexport-tag-pagebreak-desc'                            => 'Falls vom gewählten Modul unterstützt erzwingt dieses Tag einen Zeilenumbruch im exportierten Dokument.',
	'bs-universalexport-tag-noexport-desc'                             => 'Inhalt innerhalb dieses Tags erscheint nicht im exportierten Dokument.',
	'bs-universalexport-tag-meta-desc'                                 => 'Ermöglicht das hinzufügen beliebiger Metadaten zum exportierten Dokument.',
	'bs-universalexport-tag-params-desc'                               => 'Erlaubt das setzen von Export-Paramtern. Ob diese Wirkung zeigen hängt vom gewählten Export-Modul ab.',
	'specialuniversalexport'                                           => 'UniversalExport' ,
	'specialuniversalexport-desc'                                      => 'Ergänzt MediaWiki um verschiedene Exportfunktionen. Zum Beispiel Export nach Pdf.',
	'right-universalexport-export'                                     => 'Benutzen der UniversalExport Erweiterung',
	'right-universalexport-export-unfiltered'                          => 'Inhalte ungefiltert exportieren',
	'right-universalexport-export-with-attachments'                    => 'Exportieren mit Dateianhängen (PDF)',
	'right-universalexport-export-recursive'                           => 'Verlinkte Artikel mitexportieren',
);

$messages['de-formal'] = array(
	'bs-universalexport-error-permission'                              => 'Sie haben nicht die erfoderlichen Rechte um diese Seite zu exportieren',
);

$messages['qqq'] = array();