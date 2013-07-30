<?php
BsExtensionManager::registerExtension('InterWikiLinks',                  BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['InterWikiLinks'] = dirname( __FILE__ ) . '/languages/InterWikiLinks.i18n.php';

$wgResourceModules['ext.bluespice.interWikiLinks'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/InterWikiLinks/resources/bluespice.interWikiLinks.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-interwikilinks-headerPrefix',
		'bs-interwikilinks-headerUrl',
		'bs-interwikilinks-headerActions',
		'bs-interwikilinks-tipEditInterWikiLink',
		'bs-interwikilinks-tipDeleteInterWikiLink',
		'bs-interwikilinks-btnOk',
		'bs-interwikilinks-btnCancel',
		'bs-interwikilinks-titleError',
		'bs-interwikilinks-unknownError',
		'bs-interwikilinks-titleAddInterWikiLink',
		'bs-interwikilinks-titleEditInterWikiLink',
		'bs-interwikilinks-labelPrefix',
		'bs-interwikilinks-labelUrl',
		'bs-interwikilinks-titleDeleteInterWikiLink',
		'bs-interwikilinks-confirmDeleteInterWikiLink',
		'bs-interwikilinks-showEntries',
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);