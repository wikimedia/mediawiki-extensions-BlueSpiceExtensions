<?php

BsExtensionManager::registerExtension('InsertMagic', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$GLOBALS['wgAutoloadClasses']['InsertMagic'] = __DIR__ . '/InsertMagic.class.php';

$wgMessagesDirs['InsertMagic'] = __DIR__ . '/i18n';

$wgAutoloadClasses['InsertMagic'] = __DIR__ . '/InsertMagic.class.php';
$wgAutoloadClasses['BSApiInsertMagicDataStore'] = __DIR__ . '/includes/api/BSApiInsertMagicDataStore.php';
$wgAPIModules['bs-insertmagic-data-store'] = 'BSApiInsertMagicDataStore';

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
		'bs-insertmagic-label-desc',
		'bs-insertmagic-label-examples',
		'bs-insertmagic-label-see-also'
	),
	'dependencies' => array(
		'ext.bluespice.extjs'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.insertMagic.styles'] = array(
	'styles' => 'bluespice.insertMagic.css',
	'position' => 'top'
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );
