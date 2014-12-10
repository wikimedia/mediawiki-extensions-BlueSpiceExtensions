<?php

BsExtensionManager::registerExtension('ContextMenu', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['ContextMenu'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['ContextMenu'] = __DIR__ . '/languages/ContextMenu.i18n.php';

$wgResourceModules['ext.bluespice.contextmenu'] = array(
	'scripts' => 'bluespice.contextmenu.js',
	'messages' => array(
		'bs-contextmenu-page-edit',
		'bs-contextmenu-page-delete',
		'bs-contextmenu-page-move',
		'bs-contextmenu-page-history',
		'bs-contextmenu-page-protect',
		'bs-contextmenu-page-purge',
		'bs-contextmenu-page-info',
		'bs-contextmenu-media-reupload',
		'bs-contextmenu-media-view-page',
		'bs-contextmenu-user-mail',
		'bs-contextmenu-user-talk',
		'bs-contextmenu-file-download'
	),
	'localBasePath' => __DIR__.'/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/ContextMenu/resources'
);