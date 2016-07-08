<?php

BsExtensionManager::registerExtension( 'Flexiskin', BsRUNLEVEL::FULL | BsRUNLEVEL::REMOTE );

$GLOBALS['wgAutoloadClasses']['Flexiskin'] = __DIR__ . '/Flexiskin.class.php';

$wgHooks['BeforePageDisplay'][] = "Flexiskin::onBeforePageDisplay";
$wgHooks['ResourceLoaderRegisterModules'][] = "Flexiskin::onResourceLoaderRegisterModules";

$wgMessagesDirs['Flexiskin'] = __DIR__ . '/i18n';
$wgExtensionMessagesFiles['FlexiskinAlias'] = __DIR__ . '/languages/Flexiskin.alias.php';

$wgAutoloadClasses['FlexiskinApi'] = __DIR__ . '/includes/FlexiskinApi.class.php';
$wgAutoloadClasses['FlexiskinFormatter'] = __DIR__ . '/includes/FlexiskinFormatter.class.php';
$wgAutoloadClasses['ResourceLoaderFlexiskinModule'] = __DIR__ . '/includes/resourceloader/ResourceLoaderFlexiskinModule.php';
$wgAutoloadClasses['ResourceLoaderFlexiskinPreviewModule'] = __DIR__ . '/includes/resourceloader/ResourceLoaderFlexiskinPreviewModule.php';

// using BsApi
$GLOBALS['wgAutoloadClasses']['BSApiFlexiskinTasks'] = __DIR__ . '/includes/api/BSApiFlexiskinTasks.php';
$wgAPIModules['bs-flexiskin-tasks'] = 'BSApiFlexiskinTasks';

$GLOBALS['wgAutoloadClasses']['BSApiFlexiskinStore'] = __DIR__ . '/includes/api/BSApiFlexiskinStore.php';
$wgAPIModules['bs-flexiskin-store'] = 'BSApiFlexiskinStore';

$GLOBALS['wgAutoloadClasses']['BSApiFlexiskinUpload'] = __DIR__ . '/includes/api/BSApiFlexiskinUpload.php';
$wgAPIModules['bs-flexiskin-upload'] = 'BSApiFlexiskinUpload';

$GLOBALS['wgAutoloadClasses']['BSApiFlexiskinUploadStore'] = __DIR__ . '/includes/api/BSApiFlexiskinUploadStore.php';
$wgAPIModules['bs-flexiskin-upload-store'] = 'BSApiFlexiskinUploadStore';

$wgResourceModules['ext.bluespice.flexiskin.skin.preview'] =  array(
	'class' => 'ResourceLoaderFlexiskinPreviewModule'
);

$wgResourceModules['ext.bluespice.flexiskin'] = array(
	'scripts' => array(
		'extensions/BlueSpiceExtensions/Flexiskin/resources/bluespice.flexiskin.js',
	),
	'styles' => array(
		'extensions/BlueSpiceExtensions/Flexiskin/resources/bluespice.flexiskin.css',
	),
	'dependencies' => array(
		'ext.bluespice.extjs'
	),
	'messages' => array(
		'bs-flexiskin-labelname',
		'bs-flexiskin-labeldesc',
		'bs-flexiskin-headergeneral',
		'bs-flexiskin-dialogclose',
		'bs-flexiskin-dialogreset',
		'bs-flexiskin-confirmdeleteskin',
		'bs-flexiskin-titleaddskin',
		'bs-flexiskin-labelskins',
		'bs-flexiskin-defaultname',
		'bs-flexiskin-defaultdesc',
		'bs-flexiskin-headeractive',
		'bs-flexiskin-labelbgcolor',
		'bs-flexiskin-headerheader',
		'bs-flexiskin-labellogoupload',
		'bs-flexiskin-labelbackgroundupload',
		'bs-flexiskin-labelrepeatbackground',
		'bs-flexiskin-no-repeat',
		'bs-flexiskin-repeat-x',
		'bs-flexiskin-repeat-y',
		'bs-flexiskin-repeat',
		'bs-flexiskin-labelcustombgcolor',
		'bs-flexiskin-labelnavigation',
		'bs-flexiskin-headerposition',
		'bs-flexiskin-labelcontent',
		'bs-flexiskin-left',
		'bs-flexiskin-right',
		'bs-flexiskin-center',
		'bs-flexiskin-labelwidth',
		'bs-flexiskin-labelfullwidth',
		'bs-flexiskin-error-nameempty',
		'bs-flexiskin-error-templatenotexists',
		'bs-flexiskin-usebackground',
		'bs-flexiskin-labelcurrentbackground'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAPIModules['flexiskin'] = 'FlexiskinApi';

$wgEditPageFrameOptions = "SAMEORIGIN";

$wgForeignFileRepos[] = array(
	'class' => 'FSRepo',
	'name' => 'Flexiskin',
	'directory' => BS_DATA_DIR . '/Flexiskin/',
	'hashLevels' => 0,
	'url' => BS_DATA_PATH . '/Flexiskin',
);
