<?php
wfLoadExtension( 'BlueSpiceExtensions/SecureFileStore' );

$GLOBALS["wgAjaxExportList"][] = "SecureFileStore::getFile";