<?php

wfLoadExtension( 'BlueSpiceExtensions/ResponsibleEditors' );

//TODO: Revisit when rework dashboards. Find a gerneric portlet store solution
$wgAjaxExportList[] = 'ResponsibleEditors::getResponsibleEditorsPortletData';

$GLOBALS["bssDefinitions"]["_RESPEDITOR"] = array(
	"id" => "___RESPEDITOR",
	"type" => 9,
	"show" => false,
	"msgkey" => "prefs-responsibleeditors",
	"alias" => "prefs-responsibleeditors",
	"label" => "Responsible editor",
	"mapping" => "ResponsibleEditors::addPropertyValues"
);
