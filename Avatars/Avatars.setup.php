<?php

wfLoadExtension( 'BlueSpiceExtensions/Avatars' );

$wgForeignFileRepos[] = array(
	'class' => 'FSRepo',
	'name' => 'Avatars',
	'directory' => BS_DATA_DIR . '/Avatars/',
	'hashLevels' => 0,
	'url' => BS_DATA_PATH . '/Avatars',
);