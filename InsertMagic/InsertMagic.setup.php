<?php

BsExtensionManager::registerExtension('InsertMagic', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

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
		'bs-insertmagic-dlg_title',
		'bs-insertmagic-type_tags',
		'bs-insertmagic-type_variables',
		'bs-insertmagic-type_switches',
		'bs-insertmagic-type_redirect',
		'bs-insertmagic-btn_preview',
		'bs-insertmagic-label_first',
		'bs-insertmagic-label_second',
		'bs-insertmagic-label_third',
		'bs-insertmagic-label_desc'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.insertMagic.styles'] = array(
	'styles' => 'bluespice.insertMagic.css',
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );
