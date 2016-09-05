<?php
wfLoadExtension( 'BlueSpiceExtensions/Flexiskin' );

$wgForeignFileRepos[] = array(
	'class' => 'FSRepo',
	'name' => 'Flexiskin',
	'directory' => BS_DATA_DIR . '/Flexiskin/',
	'hashLevels' => 0,
	'url' => BS_DATA_PATH . '/Flexiskin',
);