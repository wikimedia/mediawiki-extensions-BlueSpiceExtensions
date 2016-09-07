<?php
wfLoadExtension( 'BlueSpiceExtensions/PageTemplates' );

$wgAjaxExportList[] = 'PageTemplatesAdmin::doEditTemplate';
$wgAjaxExportList[] = 'PageTemplatesAdmin::doDeleteTemplate';
$wgAjaxExportList[] = 'PageTemplatesAdmin::doDeleteTemplates';
