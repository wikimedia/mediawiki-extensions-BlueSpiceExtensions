<?php

BsExtensionManager::registerExtension('Flexiskin', BsRUNLEVEL::FULL | BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['Flexiskin'] = __DIR__ . '/languages/Flexiskin.i18n.php';
$wgExtensionMessagesFiles['FlexiskinAlias'] = __DIR__ . '/languages/Flexiskin.alias.php';

$wgAutoloadClasses['SpecialFlexiskin'] = __DIR__ . '/includes/specials/SpecialFlexiskin.php';

$wgHooks['BSGetLogo'][] = "Flexiskin::onBSGetLogo";

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
	'bs-flexiskin-headerName',
	'bs-flexiskin-headerDesc',
	'bs-flexiskin-labelName',
	'bs-flexiskin-labelDesc',
	'bs-flexiskin-headerGeneral',
	'bs-flexiskin-dialogClose',
	'bs-flexiskin-dialogReset',
	'bs-flexiskin-confirmDeleteSkin',
	'bs-flexiskin-titleAddSkin',
	'bs-flexiskin-labelSkins',
	'bs-flexiskin-defaultName',
	'bs-flexiskin-defaultDesc',
	'bs-flexiskin-headerActive',
	'bs-flexiskin-labelBackgroundColor',
	'bs-flexiskin-headerHeader',
	'bs-flexiskin-labelLogoUpload',
	'bs-flexiskin-labelBackgroundUpload',
	'bs-flexiskin-labelRepeatBackground',
	'bs-flexiskin-no-repeat',
	'bs-flexiskin-repeat-x',
	'bs-flexiskin-repeat-y',
	'bs-flexiskin-repeat',
	'bs-flexiskin-labelCustomBackgroundColor',
	'bs-flexiskin-labelNavigation',
	'bs-flexiskin-no-img',
	'bs-flexiskin-headerPosition',
	'bs-flexiskin-labelContent',
	'bs-flexiskin-left',
	'bs-flexiskin-right',
	'bs-flexiskin-center',
	'bs-flexiskin-labelWidth',
	'bs-flexiskin-labelFullWidth'
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
