<?php

BsExtensionManager::registerExtension('ShoutBox', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['ShoutBox'] = __DIR__ . '/languages/ShoutBox.i18n.php';
$wgExtensionMessagesFiles['ShoutBoxMagic'] = __DIR__ . '/languages/ShoutBox.i18n.magic.php';

$aResourceModuleTemplate = array(
	'localBasePath' => $IP . '/extensions/BlueSpiceExtensions/ShoutBox/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/ShoutBox/resources'
);

$wgResourceModules['ext.bluespice.shoutbox'] = array(
	'scripts' => 'bluespice.shoutBox.js',
	'dependencies' => 'ext.bluespice',
	'messages' => array(
		'bs-shoutbox-confirm_text',
		'bs-shoutbox-confirm_title',
		'bs-shoutbox-enterMessage'
	),
	'position' => 'bottom'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.shoutbox.styles'] = array(
	'styles'  => 'bluespice.shoutBox.css',
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );

$wgAjaxExportList[] = 'ShoutBox::getShouts';
$wgAjaxExportList[] = 'ShoutBox::insertShout';
$wgAjaxExportList[] = 'ShoutBox::archiveShout';

$wgAutoloadClasses['ViewShoutBox']            = __DIR__ . '/includes/ViewShoutBox.php';
$wgAutoloadClasses['ViewShoutBoxMessageList'] = __DIR__ . '/includes/ViewShoutBoxMessageList.php';
$wgAutoloadClasses['ViewShoutBoxMessage']     = __DIR__ . '/includes/ViewShoutBoxMessage.php';
