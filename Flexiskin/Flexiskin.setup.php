<?php

BsExtensionManager::registerExtension( 'Flexiskin', BsRUNLEVEL::FULL | BsRUNLEVEL::REMOTE );

$wgMessagesDirs['Flexiskin'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['Flexiskin'] = __DIR__ . '/languages/Flexiskin.i18n.php';
$wgExtensionMessagesFiles['FlexiskinAlias'] = __DIR__ . '/languages/Flexiskin.alias.php';

$GLOBALS['wgAutoloadClasses']['Flexiskin'] = __DIR__ . '/Flexiskin.class.php';
$wgAutoloadClasses['SpecialFlexiskin'] = __DIR__ . '/includes/specials/SpecialFlexiskin.php';

$wgHooks['SkinTemplateOutputPageBeforeExec'][] = "Flexiskin::onSkinTemplateOutputPageBeforeExec";

$wgSpecialPageGroups['Flexiskin'] = 'bluespice';

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
		'bs-flexiskin-error-templatenotexists'
	),
	'localBasePath' => $IP,
	'remoteBasePath' => &$GLOBALS['wgScriptPath']
);

$wgAjaxExportList[] = 'Flexiskin::getFlexiskins';
$wgAjaxExportList[] = 'Flexiskin::saveFlexiskin';
$wgAjaxExportList[] = 'Flexiskin::saveFlexiskinPreview';
$wgAjaxExportList[] = 'Flexiskin::getFlexiskinConfig';
$wgAjaxExportList[] = 'Flexiskin::deleteFlexiskin';
$wgAjaxExportList[] = 'Flexiskin::addFlexiskin';
$wgAjaxExportList[] = 'Flexiskin::activateFlexiskin';
$wgAjaxExportList[] = 'Flexiskin::resetFlexiskin';
$wgAjaxExportList[] = 'Flexiskin::uploadFile';
$wgEditPageFrameOptions = "SAMEORIGIN";

$wgForeignFileRepos[] = array(
    'class' => 'FSRepo',
    'name' => 'Flexiskin',
    'directory' => BS_DATA_DIR . '/Flexiskin/',
    'hashLevels' => 0,
    'url' => BS_DATA_PATH . '/Flexiskin',
);
