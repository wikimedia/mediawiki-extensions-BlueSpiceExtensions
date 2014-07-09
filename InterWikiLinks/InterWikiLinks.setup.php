<?php
BsExtensionManager::registerExtension('InterWikiLinks',                  BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['InterWikiLinks'] = __DIR__ . '/languages/InterWikiLinks.i18n.php';

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
$wgResourceModules['bluespice.insertLink.interWikiLinks'] = array(
	'scripts' => 'extensions/BlueSpiceExtensions/InterWikiLinks/resources/bluespice.insertLink.interWikiLinks.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-interwikilinks-insertlink-tabtitle',
		'bs-interwikilinks-insertlink-labelprefix',
		'bs-interwikilink-select_a_prefix'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAjaxExportList[] = 'InterWikiLinks::getInterWikiLinks';
$wgAjaxExportList[] = 'InterWikiLinks::doEditInterWikiLink';
$wgAjaxExportList[] = 'InterWikiLinks::doDeleteInterWikiLink';