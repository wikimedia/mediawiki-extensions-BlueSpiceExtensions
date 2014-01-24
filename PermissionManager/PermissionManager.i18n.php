<?php

/**
 * Internationalisation file for PermissionManager
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author Stephan Muggli <muggli@hallowelt.biz>

 * @packageBlueSpice_Extensions
 * @subpackage PermissionManager
 * @copyrightCopyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @licensehttp://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
$messages = array ( );

$messages[ 'de' ] = array (
	'bs-permissionmanager-extension-description' => 'Hilft Administratoren, die Benutzerrechte zu bearbeiten.',
	'bs-permissionmanager-label'				 => 'Rechteverwaltung',
	'bs-permissionmanager-main-namespaces'		 => 'Hauptnamensraum',
	'bs-permissionmanager-grouping-global' => 'Wikiweite Rechte',
	'bs-permissionmanager-grouping-local' => 'Namensraumspezifische Rechte',
	'bs-permissionmanager-grouping-template' => 'Vorlagen',
	'bs-permissionmanager-btn-group-label' => 'Aktuelle Gruppe: ',
	'bs-permissionmanager-header-permissions' => 'Rechte',
	'bs-permissionmanager-header-global' => 'Wiki',
	'bs-permissionmanager-header-namespaces' => 'Namensräume',
	'bs-permissionmanager-header-group' => 'Gruppe',
	'bs-permissionmanager-btn-save-label' => 'Einstellungen für diese Gruppe speichern',
	'bs-permissionmanager-btn-save-in-progress-label' => 'Speichert ...',
	'bs-permissionmanager-btn-save-success' => 'Die Einstellungen wurden erfolgreich gespeichert.',
	'bs-permissionmanager-labelTemplates' => 'Vorlagen',
	'bs-permissionmanager-btn-template-editor' => 'Vorlagen bearbeiten',
	'bs-permissionmanager-labelTemplateEditor' => 'Vorlagen-Editor',
	'bs-permissionmanager-labelTemplateEditor-description' => 'Beschreibung',
	'bs-permissionmanager-labelTemplateEditor-active' => 'Aktiv',
	'bs-permissionmanager-labelTemplateEditor-permissions' => 'Rechte',
	'bs-permissionmanager-template-editor-labelAdd' => 'Neu',
	'bs-permissionmanager-template-editor-labelEdit' => 'Bearbeiten',
	'bs-permissionmanager-template-editor-labelDelete' => 'Löschen',
	'bs-permissionmanager-template-editor-labelSave' => 'Speichern',
	'bs-permissionmanager-template-editor-labelCancel' => 'Abbrechen',
	'bs-permissionmanager-template-editor-save-success' => 'Vorlage erfolgreich gespeichert',
	'bs-permissionmanager-template-editor-save-failure' => 'Vorlage konnten nicht gespeichert werden',
	'bs-permissionmanager-template-editor-saveOrAbort' => 'Sollen die ungespeicherten Daten jetzt gespeichert werden?',
	'bs-permissionmanager-template-editor-msgNew' => 'Name für die neue Vorlage',
	'bs-permissionmanager-template-editor-msgEdit' => 'Neuer Name für die Vorlage',
	'bs-permissionmanager-template-editor-delete-success' => 'Vorlage erfolgreich gelöscht',
	'bs-permissionmanager-template-editor-delete-failure' => 'Vorlage konnte nicht gelöscht werden',
	'prefs-PermissionManager'		=> 'Rechteverwaltung',
	'bs-pm-pref-lockmode'			=> 'Lockmode aktivieren',
	'bs-pm-pref-skipSysNs'			=> 'MediaWiki Systemnamensräume bei eingschaltetem Lockmode ignorieren',
	'bs-pm-pref-enableRealityCheck'	=> 'Rechteprüfung aktivieren (Ressourcenintensiv)',
);

$messages[ 'de-formal' ] = array ( );

$messages[ 'en' ] = array (
	'bs-permissionmanager-extension-description' => 'Administration interface for editing user rights.',
	'bs-permissionmanager-label'				 => 'Permission manager',
	'bs-permissionmanager-main-namespaces'		 => 'Main namespace',
	'bs-permissionmanager-grouping-global' => 'Wikiwide rights',
	'bs-permissionmanager-grouping-local' => 'Namespace specific rights',
	'bs-permissionmanager-grouping-template' => 'Templates',
	'bs-permissionmanager-btn-group-label' => 'Current group: ',
	'bs-permissionmanager-header-permissions' => 'Rights',
	'bs-permissionmanager-header-global' => 'Wiki',
	'bs-permissionmanager-header-namespaces' => 'Namespaces',
	'bs-permissionmanager-header-group' => 'Group',
	'bs-permissionmanager-btn-save-label' => 'Save settings for this group',
	'bs-permissionmanager-btn-save-in-progress-label' => 'Save in progress ...',
	'bs-permissionmanager-btn-save-success' => 'The settings were saved successfully.',
	'bs-permissionmanager-labelTemplates' => 'Templates',
	'bs-permissionmanager-btn-template-editor' => 'Edit templates',
	'bs-permissionmanager-labelTemplateEditor' => 'Template editor',
	'bs-permissionmanager-labelTemplateEditor-description' => 'Description',
	'bs-permissionmanager-labelTemplateEditor-active' => 'Active',
	'bs-permissionmanager-labelTemplateEditor-permissions' => 'Rights',
	'bs-permissionmanager-template-editor-labelAdd' => 'New',
	'bs-permissionmanager-template-editor-labelEdit' => 'Edit',
	'bs-permissionmanager-template-editor-labelDelete' => 'Delete',
	'bs-permissionmanager-template-editor-labelSave' => 'Save',
	'bs-permissionmanager-template-editor-labelCancel' => 'Cancel',
	'bs-permissionmanager-template-editor-save-success' => 'Template successfully saved',
	'bs-permissionmanager-template-editor-save-failure' => 'Template could not be saved',
	'bs-permissionmanager-template-editor-saveOrAbort' => 'Do you want to save all unsaved data now?',
	'bs-permissionmanager-template-editor-msgNew' => 'Enter the name for the new template',
	'bs-permissionmanager-template-editor-msgEdit' => 'Enter the new name for the template',
	'bs-permissionmanager-template-editor-delete-success' => 'Template successfully deleted',
	'bs-permissionmanager-template-editor-delete-failure' => 'Template could not be deleted',
	'prefs-PermissionManager'		=> 'Permission Manager',
	'bs-pm-pref-lockmode'			=> 'Activate lockmode',
	'bs-pm-pref-skipSysNs'			=> 'Skip MediaWiki system namespaces if lockmode is active',
	'bs-pm-pref-enableRealityCheck'	=> 'Activate permission check (resource intensive)',
);

$messages[ 'qqq' ] = array ( );
