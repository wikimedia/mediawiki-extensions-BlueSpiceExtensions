<?php
wfLoadExtension( 'BlueSpiceExtensions/PageTemplates' );

$GLOBALS["wgAjaxExportList"] [] = "PageTemplatesAdmin::doEditTemplate";
$GLOBALS["wgAjaxExportList"] [] = "PageTemplatesAdmin::doDeleteTemplate";
$GLOBALS["wgAjaxExportList"] [] = "PageTemplatesAdmin::doDeleteTemplates";