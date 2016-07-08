<?php
BsExtensionManager::registerExtension('InterWikiLinks', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$GLOBALS['wgAutoloadClasses']['InterWikiLinks'] = __DIR__ . '/InterWikiLinks.class.php';
$GLOBALS['wgAutoloadClasses']['BSApiTasksInterWikiLinksManager'] = __DIR__ . '/includes/api/BSApiTasksInterWikiLinksManager.php';

$wgMessagesDirs['InterWikiLinks'] = __DIR__ . '/i18n';

$wgResourceModules['ext.bluespice.interWikiLinks'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/InterWikiLinks/resources/bluespice.interWikiLinks.js',
	'messages' => array(
		'bs-interwikilinks-headerprefix',
		'bs-interwikilinks-headerurl',
		'bs-interwikilinks-titleaddinterwikilink',
		'bs-interwikilinks-titleeditinterwikilink',
		'bs-interwikilinks-labelprefix',
		'bs-interwikilinks-labelurl',
		'bs-interwikilinks-titledeleteinterwikilink',
		'bs-interwikilinks-confirmdeleteinterwikilink'
	),
	'dependencies' => array(
		'ext.bluespice.extjs'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);
$wgResourceModules['bluespice.insertLink.interWikiLinks'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/InterWikiLinks/resources/bluespice.insertLink.interWikiLinks.js',
	'messages' => array(
		'bs-interwikilinks-insertlink-tabtitle',
		'bs-interwikilinks-insertlink-labelprefix',
		'bs-interwikilink-select-a-prefix'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAPIModules['bs-interwikilinks-tasks'] = 'BSApiTasksInterWikiLinksManager';