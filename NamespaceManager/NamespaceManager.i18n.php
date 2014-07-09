<?php
/**
 * Internationalisation file for NamespaceManager
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    $Id' =>NamespaceManager.i18n.php 6401 2012-09-06 11:03:08Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage NamespaceManager
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

$messages = array();

$messages['de'] = array(
	'bs-namespacemanager-extension-description'       => 'Administratoren-Werkzeug, um abgegrenzte Bereiche zu definieren, zu bearbeiten und auch wieder zu löschen und um Benutzern hierfür definierte Rechte zu geben.',
	'bs-namespacemanager-back'                        => 'Zurück',
	'bs-namespacemanager-label'                       => 'Namensraumverwaltung',
	'bs-namespacemanager-not_allowed'                 => 'Du bist leider nicht berechtigt, diese Seite zu benutzen.',
	'bs-namespacemanager-wrong_namespace_name_format' => 'Der von Dir gewählte Namespace-Name enthält unzulässige Zeichen.',
	'bs-namespacemanager-namespace_already_exists'    => 'Der von Dir gewählte Namespace-Name existiert bereits.',
	'bs-namespacemanager-namespace_name_length'       => 'Der von Ihnen eingetragene Namespace-Name muss minderstens zwei Zeichen lang sein.',
	'bs-namespacemanager-no_valid_namespace_id'       => 'Keine zulässige Namespace ID',
	'bs-namespacemanager-label-id'                    => 'ID',
	'bs-namespacemanager-label-namespaces'            => 'Namensräume',
	'bs-namespacemanager-label-editable'              => 'umbenennbar',
	'bs-namespacemanager-label-content'               => 'in Statistik',
	'bs-namespacemanager-label-searchable'            => 'in Standardsuche',
	'bs-namespacemanager-label-subpages'              => 'mit Unterseiten',
	'bs-namespacemanager-nsadded'                     => 'Der Namesraum wurde erfolgreich erstellt',
	'bs-namespacemanager-nsremoved'                   => 'Der Namesraum wurde erfolgreich gelöscht',
	'bs-namespacemanager-nsedited'                    => 'Der Namensraum wurde erfolgreich bearbeitet',

	//Javascript
	'bs-namespacemanager-headerNamespaceId' =>'ID',
	'bs-namespacemanager-headerNamespaceName' =>'Namensraum',
	'bs-namespacemanager-headerIsUserNamespace' =>'System (nicht änderbar)',
	'bs-namespacemanager-headerIsContentNamespace' =>'in Statistik',
	'bs-namespacemanager-headerIsSearchableNamespace' =>'in Standardsuche',
	'bs-namespacemanager-headerIsSubpagesNamespace' =>'mit Unterseiten',
	'bs-namespacemanager-headerActions' =>'Aktionen',
	'bs-namespacemanager-yes' =>'ja',
	'bs-namespacemanager-no' =>'nein',
	'bs-namespacemanager-btnAddNamespace' =>'Namensraum hinzufügen',
	'bs-namespacemanager-tipEdit' =>'Namensraum bearbeiten',
	'bs-namespacemanager-tipRemove' =>'Namensraum entfernen',
	'bs-namespacemanager-msgNotEditable' =>'Dieser Namensraum ist ein Systemnamensraum und kann nicht bearbeitet werden.',
	'bs-namespacemanager-msgNotEditableDelete' =>'Dieser Namensraum ist ein Systemnamensraum und kann nicht gel&ouml;scht werden.',
	'bs-namespacemanager-titleNewNamespace' =>'Namensraum hinzufügen',
	'bs-namespacemanager-labelNamespaceName' =>'Namensraum-Name',
	'bs-namespacemanager-emptyMsgNamespaceName' =>'Der Name des Namensraums kann nicht leer sein',
	'bs-namespacemanager-labelContentNamespace' =>'Namensraum statistisch auswerten',
	'bs-namespacemanager-labelSearchedNamespace' =>'Namensraum durchsuchbar',
	'bs-namespacemanager-labelSubpagesNamespace' =>'Unterseiten ermöglichen',
	'bs-namespacemanager-btnSave' =>'Speichern',
	'bs-namespacemanager-btnCancel' =>'Abbrechen',
	'bs-namespacemanager-titleError' =>'Fehler',
	'bs-namespacemanager-willDelete' =>'... werden gel&ouml;scht',
	'bs-namespacemanager-willMove' =>'... werden in (Seiten) verschoben*',
	'bs-namespacemanager-willMoveSuffix' =>'... werden mit dem Suffix "(von <span class="removeWindowNamespaceName"></span>)" in (Seiten) verschoben',
	'bs-namespacemanager-deletewarning' =>'Bist du dir sicher, dass du den Namensraum l&ouml;schen willst? Das L&ouml;schen eines Namensraums kann nicht r&uuml;ckg&auml;ngig gemacht werden!',
	'bs-namespacemanager-moveConflict' =>'* Wenn ein Namenskonflikt auftritt, wird dem zu verschiebenden Artikel das Suffix "(von <span class="removeWindowNamespaceName"></span>)" angehängt',
	'bs-namespacemanager-articlesPresent' =>'Noch vorhandene Artikel in diesem Namensraum ... ',
	'bs-namespacemanager-btnDelete' =>'L&ouml;schen',
	'bs-namespacemanager-deleteNamespace' =>'Namensraum l&ouml;schen',
	'bs-namespacemanager-showEntries' =>'Angezeigte Einträge {0} - {1} von {2}',
	'bs-namespacemanager-pageSize ' =>'Seitengröße'
);

$messages['de-formal'] = array(
	'bs-namespacemanager-not_allowed'                 => 'Sie sind leider nicht berechtigt, diese Seite zu benutzen.',
	'bs-namespacemanager-wrong_namespace_name_format' => 'Der von Ihnen gewählte Namespace-Name enthält unzulässige Zeichen.',
	'bs-namespacemanager-namespace_already_exists'    => 'Der von Ihnen gewählte Namespace-Name existiert bereits.',
	'bs-namespacemanager-namespace_name_length'       => 'Der von Ihnen eingetragene Namespace-Name muss minderstens zwei Zeichen lang sein.',

	//Javascript
	'bs-namespacemanager-sureDeletePt1' => 'Sind Sie sich sicher, dass Sie den Namensraum l&ouml;schen wollen? Das L&ouml;schen eines Namensraums kann nicht r&uuml;ckg&auml;ngig gemacht werden!'
);

$messages['en'] = array(
	'bs-namespacemanager-extension-description'       => 'Administration interface for adding, editing and deleting namespaces',
	'bs-namespacemanager-back'                        => 'Back',
	'bs-namespacemanager-label'                       => 'Namespace manager',
	'bs-namespacemanager-not_allowed'                 => 'You are not allowed to use this page.',
	'bs-namespacemanager-wrong_namespace_name_format' => 'The namespace name you have chosen contains invalid characters.',
	'bs-namespacemanager-namespace_already_exists'    => 'The namespace name you have chosen already exists.',
	'bs-namespacemanager-namespace_name_length'       => 'The namespace you have entered must have a length of two chars.',
	'bs-namespacemanager-no_valid_namespace_id'       => 'No valid namespace ID',
	'bs-namespacemanager-label-id'                    => 'ID',
	'bs-namespacemanager-label-namespaces'            => 'Namespaces',
	'bs-namespacemanager-label-editable'              => 'renamable',
	'bs-namespacemanager-label-content'               => 'in statistics',
	'bs-namespacemanager-label-searchable'            => 'in standard search',
	'bs-namespacemanager-label-subpages'              => 'has subpages',
	'bs-namespacemanager-nsadded'                     => 'The namespace has been added successfully',
	'bs-namespacemanager-nsremoved'                   => 'The namespace has been removed successfully',
	'bs-namespacemanager-nsedited'                    => 'The namespace has been edited successfully',

	//Javascript
	'bs-namespacemanager-headerNamespaceId' => 'ID',
	'bs-namespacemanager-headerNamespaceName' => 'Namespaces',
	'bs-namespacemanager-headerIsUserNamespace' => 'System (non-editable)',
	'bs-namespacemanager-headerIsContentNamespace' => 'in statistics',
	'bs-namespacemanager-headerIsSearchableNamespace' => 'in standard search',
	'bs-namespacemanager-headerIsSubpagesNamespace' => 'has subpages',
	'bs-namespacemanager-headerActions' => 'Actions',
	'bs-namespacemanager-yes' => 'yes',
	'bs-namespacemanager-no' => 'no',
	'bs-namespacemanager-btnAddNamespace' => 'Add namespace',
	'bs-namespacemanager-tipEdit' => 'Edit namespace',
	'bs-namespacemanager-tipRemove' => 'Remove namespace',
	'bs-namespacemanager-msgNotEditable' => 'This namespace is a systemnamespace and cannot be edited.',
	'bs-namespacemanager-msgNotEditableDelete' => 'This namespace is a systemnamespace and cannot be deleted.',
	'bs-namespacemanager-titleNewNamespace' => 'Add Namespace',
	'bs-namespacemanager-labelNamespaceName' => 'Namespace name',
	'bs-namespacemanager-emptyMsgNamespaceName' => 'Namespace title cannot be empty',
	'bs-namespacemanager-labelContentNamespace' => 'Evaluate namespace statistically',
	'bs-namespacemanager-labelSearchedNamespace' => 'Add namespace to standard search',
	'bs-namespacemanager-labelSubpagesNamespace' => 'Enable subpages',
	'bs-namespacemanager-btnSave' => 'Save',
	'bs-namespacemanager-btnCancel' => 'Cancel',
	'bs-namespacemanager-titleError' => 'Error',
	'bs-namespacemanager-willDelete' => '... will be deleted',
	'bs-namespacemanager-willMove' => '... will be moved to the Mainnamespace *',
	'bs-namespacemanager-willMoveSuffix' => '... will be moved to the Mainnamespace with the Suffix "(from <span class="removeWindowNamespaceName"></span>)"',
	'bs-namespacemanager-deletewarning' => 'Are you sure that you want to delete this namespace? Deleting a namespace can not be undone!',
	'bs-namespacemanager-moveConflict' => '*The namespace will be extended by the Suffix"(from <span class="removeWindowNamespaceName"></span>)" if a conflict occurs',
	'bs-namespacemanager-articlesPresent' => 'Other Articles present in this namespace ...',
	'bs-namespacemanager-btnDelete' => 'Delete',
	'bs-namespacemanager-deleteNamespace' => 'Delete Namespace',
	'bs-namespacemanager-showEntries' => 'Showing entries {0} - {1} of {2}',
	'bs-namespacemanager-pageSize ' => 'Page size'
);

$messages['qqq'] = array();