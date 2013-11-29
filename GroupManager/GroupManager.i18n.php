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
	'bs-groupmanager-extension-description' => 'Schnittstelle für Administratoren um Benutzergruppen, sowie deren Rechte, hinzuzufügen, zu bearbeiten und zu löschen.',
	'bs-groupmanager-*'                     => 'Alle',
	'bs-groupmanager-actions'               => 'Aktionen',
	'bs-groupmanager-back'                  => 'Zurück',
	'bs-groupmanager-block'                 => 'User blocken',
	'bs-groupmanager-button_cancel'         => 'Abbrechen',
	'bs-groupmanager-button_ok'             => 'OK',
	'bs-groupmanager-cannot_delete_group'   => 'Diese Gruppe kann nicht gelöscht werden.',
	'bs-groupmanager-create_new'            => 'Neue Gruppe erstellen',
	'bs-groupmanager-createaccount'         => 'Account erstellen',
	'bs-groupmanager-createpage'            => 'Artikel erstellen',
	'bs-groupmanager-createtalk'            => 'Diskussion erstellen',
	'bs-groupmanager-del_question1'         => 'Willst Du die Gruppe',
	'bs-groupmanager-del_question2'         => 'wirklich löschen?',
	'bs-groupmanager-delete'                => 'Löschen',
	'bs-groupmanager-edit'                  => 'Bearbeiten',
	'bs-groupmanager-files'                 => 'Dateien ansehen',
	'bs-groupmanager-group'                 => 'Gruppe',
	'bs-groupmanager-group_added'           => 'Die Gruppe $1 wurde hinzugefügt',
	'bs-groupmanager-group_changed'         => 'Die Rechte der Gruppe wurden geändert.',
	'bs-groupmanager-group_deleted'         => 'Die Gruppe $1 wurde gelöscht',
	'bs-groupmanager-groupname'             => 'Name der Gruppe',
	'bs-groupmanager-grp_2long'             => 'Der Gruppenname ist zu lang. Gib maximal 16 Zeichen an!',
	'bs-groupmanager-grp_exists'            => 'Die Gruppe ist bereits vorhanden.',
	'bs-groupmanager-grp_not_exists'        => 'Die Gruppe existiert nicht',
	'bs-groupmanager-grpadded'              => 'Die Gruppe wurde erfolgreich hinzugefügt',
	'bs-groupmanager-grpedited'             => 'Die Gruppe wurde erfolgreich bearbeitet',
	'bs-groupmanager-grpremoved'            => 'Die Gruppe wurde erfolgreich gelöscht',
	'bs-groupmanager-import'                => 'Seiten importieren',
	'bs-groupmanager-invalid_grp'           => 'Der Gruppenname ist ungültig.',
	'bs-groupmanager-invalid_grp_esc'       => 'Der Gruppenname ist ungültig. Verwende keine Hochkommas oder Backslashes.',
	'bs-groupmanager-invalid_grp_spc'       => 'Der Gruppenname ist ungültig. Verwende keine Leerzeichen.',
	'bs-groupmanager-invalid_rights_esc'    => 'Mindestens eines der Rechte ist ungültig. Verwende keine Hochkommas oder Backslashes.',
	'bs-groupmanager-invalid_rights_spc'    => 'Mindestens eines der Rechte ist ungültig. Verwende keine Leerzeichen.',
	'bs-groupmanager-label'                 => 'Gruppenverwaltung',
	'bs-groupmanager-move'                  => 'Verschieben',
	'bs-groupmanager-no_grp'                => 'Bitte gib einen Gruppennamen ein.',
	'bs-groupmanager-not_allowed'           => 'Du bist leider nicht berechtigt, diese Seite zu benutzen.',
	'bs-groupmanager-preset_rights'         => 'Bereits durch andere Gruppen (Alle, Benutzer) erhaltene Rechte:',
	'bs-groupmanager-protect'               => 'Seiten schützen',
	'bs-groupmanager-read'                  => 'Lesen',
	'bs-groupmanager-rights'                => 'Rechte',
	'bs-groupmanager-rollback'              => 'Rollback',
	'bs-groupmanager-searchfiles'           => 'Dateien durchsuchen',
	'bs-groupmanager-sysop'                 => 'WikiSysop',
	'bs-groupmanager-upload'                => 'Dateien hochladen',
	'bs-groupmanager-user'                  => 'Benutzer',
	'bs-groupmanager-wikiadmin'             => 'WikiAdmin',
	'bs-groupmanager-workflowedit'          => 'Workflow bearbeiten',
	'bs-groupmanager-workflowlist'          => 'Workflow Liste',
	'bs-groupmanager-workflowview'          => 'Workflow ansehen',
	'bs-groupmanager-invalid_name'          => 'Der eingegebene Gruppenname enthält nicht erlaubte Zeichen: $1',
	'bs-groupmanager-invalid_name_length'   => 'Der eingegebene Gruppenname darf nicht länger als 16 Zeichen lang sein.',
	'bs-groupmanager-invalid_name_numeric'  => 'Der eingegebene Gruppenname darf nicht zur aus Zahlen bestehen.',

	//Javascript
	'bs-groupmanager-headerGroupname' => 'Gruppen',
	'bs-groupmanager-headerActions' => 'Aktionen',
	'bs-groupmanager-btnAddGroup' => 'Gruppe hinzufügen',
	'bs-groupmanager-tipEdit' => 'Gruppe umbennen',
	'bs-groupmanager-tipRemove' => 'Gruppe löschen',
	'bs-groupmanager-titleNewGroup' => 'Gruppe hinzufügen',
	'bs-groupmanager-titleEditGroup' => 'Gruppe bearbeiten',
	'bs-groupmanager-titleError' => 'Fehler',
	'bs-groupmanager-removeGroup' => 'Willst du die Gruppe wirklich löschen?',
	'bs-groupmanager-lableName' => 'Gruppenname:',
	'bs-groupmanager-msgNotEditable' => 'Dies ist eine Systemgruppe und kann nicht umbenannt werden.',
	'bs-groupmanager-msgNotRemovable' => 'Dies ist eine Systemgruppe und kann nicht gelöscht werden.'
);

$messages['de-formal'] = array(
	'bs-groupmanager-del_question1'         => 'Wollen Sie die Gruppe',
	'bs-groupmanager-grp_2long'             => 'Der Gruppenname ist zu lang. Geben Sie maximal 16 Zeichen an!',
	'bs-groupmanager-invalid_grp_esc'       => 'Der Gruppenname ist ungültig. Verwenden Sie keine Hochkommas oder Backslashes.',
	'bs-groupmanager-invalid_grp_spc'       => 'Der Gruppenname ist ungültig. Verwenden Sie keine Leerzeichen.',
	'bs-groupmanager-invalid_rights_esc'    => 'Mindestens eines der Rechte ist ungültig. Verwenden Sie keine Hochkommas oder Backslashes.',
	'bs-groupmanager-invalid_rights_spc'    => 'Mindestens eines der Rechte ist ungültig. Verwenden Sie keine Leerzeichen.',
	'bs-groupmanager-no_grp'                => 'Bitte geben Sie einen Gruppennamen ein.',
	'bs-groupmanager-not_allowed'           => 'Sie sind leider nicht berechtigt, diese Seite zu benutzen.',

	//Javascript
	'bs-groupmanager-removeGroup' => 'Wollen Sie die Gruppe wirklich löschen?'
);

$messages['en'] = array(
	'bs-groupmanager-extension-description' => 'Administration interface for adding, editing and deletig user groups and their rights.',
	'bs-groupmanager-*'                     => 'All',
	'bs-groupmanager-actions'               => 'Actions',
	'bs-groupmanager-back'                  => 'Back',
	'bs-groupmanager-block'                 => 'block users',
	'bs-groupmanager-button_cancel'         => 'Cancel',
	'bs-groupmanager-button_ok'             => 'OK',
	'bs-groupmanager-cannot_delete_group'   => 'This group cannot be deleted.',
	'bs-groupmanager-create_new'            => 'create new group',
	'bs-groupmanager-createaccount'         => 'create account',
	'bs-groupmanager-createpage'            => 'create article',
	'bs-groupmanager-createtalk'            => 'create discussion',
	'bs-groupmanager-del_question1'         => 'Do you really want to delete the group',
	'bs-groupmanager-del_question2'         => '?',
	'bs-groupmanager-delete'                => 'delete',
	'bs-groupmanager-edit'                  => 'edit',
	'bs-groupmanager-files'                 => 'view files',
	'bs-groupmanager-group'                 => 'Group',
	'bs-groupmanager-group_added'           => 'Group $1 was added',
	'bs-groupmanager-group_changed'         => 'The rights of the group have been changed.',
	'bs-groupmanager-group_deleted'         => 'Group $1 was deleted',
	'bs-groupmanager-groupname'             => 'group name',
	'bs-groupmanager-grp_2long'             => 'The group name is too long. Do not use more than 16 characters.',
	'bs-groupmanager-grp_exists'            => 'The group already exists.',
	'bs-groupmanager-grp_not_exists'        => 'The group does not exist.',
	'bs-groupmanager-grpadded'              => 'The group has been added successfully',
	'bs-groupmanager-import'                => 'import articles',
	'bs-groupmanager-invalid_grp'           => 'The group name is invalid.',
	'bs-groupmanager-invalid_grp_esc'       => 'The group name is invalid. Please do not use apostrophes or backslashes.',
	'bs-groupmanager-invalid_grp_spc'       => 'The group name is invalid. Please do not use spaces.',
	'bs-groupmanager-invalid_rights_esc'    => 'At least one of the rights is invalid. Please do not use apostrophes or backslashes.',
	'bs-groupmanager-invalid_rights_spc'    => 'At least one of the rights is invalid. Please do not use spaces.',
	'bs-groupmanager-label'                 => 'Group manager',
	'bs-groupmanager-move'                  => 'move',
	'bs-groupmanager-no_grp'                => 'Please enter a group name.',
	'bs-groupmanager-not_allowed'           => 'You are not allowed to use this page.',
	'bs-groupmanager-preset_rights'         => 'These rights are already active through other groups (all, users):',
	'bs-groupmanager-protect'               => 'protect articles',
	'bs-groupmanager-read'                  => 'read',
	'bs-groupmanager-rights'                => 'Rights',
	'bs-groupmanager-rollback'              => 'rollback',
	'bs-groupmanager-searchfiles'           => 'search files',
	'bs-groupmanager-sysop'                 => 'WikiSysop',
	'bs-groupmanager-upload'                => 'upload files',
	'bs-groupmanager-user'                  => 'User',
	'bs-groupmanager-wikiadmin'             => 'wikiadmin',
	'bs-groupmanager-workflowedit'          => 'edit workflow',
	'bs-groupmanager-workflowlist'          => 'Workflow List',
	'bs-groupmanager-workflowview'          => 'view workflow',
	'bs-groupmanager-invalid_name'          => 'The group name you have entered contains chars that are not allowed: $1',
	'bs-groupmanager-invalid_name_length'   => 'The group name you have entered can not be longer than 16 chars.',
	'bs-groupmanager-invalid_name_numeric'  => 'The group name you have entered can not contains only numbers.',

	//Javascript
	'bs-groupmanager-headerGroupname' => 'Groups',
	'bs-groupmanager-headerActions' => 'Actions',
	'bs-groupmanager-btnAddGroup' => 'Add group',
	'bs-groupmanager-tipEdit' => 'Rename group',
	'bs-groupmanager-tipRemove' => 'Remove group',
	'bs-groupmanager-titleNewGroup' => 'New group',
	'bs-groupmanager-titleEditGroup' => 'Edit group',
	'bs-groupmanager-titleError' => 'Error',
	'bs-groupmanager-removeGroup' => 'Are you sure you want to remove this group?',
	'bs-groupmanager-lableName' => 'Group name:',
	'bs-groupmanager-msgNotEditable' => 'This group is a system group and cannot be renamed.',
	'bs-groupmanager-msgNotRemovable' => 'This group is a system group and cannot be removed.'
);

$messages['qqq'] = array();
