<?php

BsExtensionManager::registerExtension('InsertMagic', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['InsertMagic'] = __DIR__ . '/InsertMagic.class.php';

$wgMessagesDirs['InsertMagic'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['InsertMagic'] = __DIR__ . '/languages/InsertMagic.i18n.php';

$wgAutoloadClasses['InsertMagic'] = __DIR__ . '/InsertMagic.class.php';
$wgAjaxExportList[] = 'InsertMagic::ajaxGetData';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__.'/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/InsertMagic/resources',
);

$wgResourceModules['ext.bluespice.insertMagic'] = array(
	'scripts' => 'bluespice.insertMagic.js',
	'messages' => array(
		'bs-insertmagic-dlg-title',
		'bs-insertmagic-type-tags',
		'bs-insertmagic-type-variables',
		'bs-insertmagic-type-switches',
		'bs-insertmagic-type-redirect',
		'bs-insertmagic-btn-preview',
		'bs-insertmagic-label-first',
		'bs-insertmagic-label-second',
		'bs-insertmagic-label-third',
		'bs-insertmagic-label-desc'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.insertMagic.styles'] = array(
	'styles' => 'bluespice.insertMagic.css',
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );
