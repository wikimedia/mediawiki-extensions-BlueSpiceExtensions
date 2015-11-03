<?php

BsExtensionManager::registerExtension( 'AboutBlueSpice', BsRUNLEVEL::FULL | BsRUNLEVEL::REMOTE );

$GLOBALS['wgAutoloadClasses']['AboutBlueSpice'] = __DIR__ . '/AboutBlueSpice.class.php';
$wgAutoloadClasses['SpecialAboutBlueSpice'] = __DIR__ . '/includes/specials/SpecialAboutBlueSpice.class.php';

$wgMessagesDirs['AboutBlueSpice'] = __DIR__ . '/i18n';

$wgSpecialPages['AboutBlueSpice'] = 'SpecialAboutBlueSpice';
$wgExtensionMessagesFiles['ExtendedStatisticsAlias'] = __DIR__ . '/includes/specials/SpecialAboutBlueSpice.alias.php';

$aResourceModuleTemplate = array (
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/AboutBlueSpice/resources'
);

$wgResourceModules['ext.bluespice.aboutbluespice'] = array (
	'scripts' => array (
		'bluespice.aboutbluespice.js'
	),
	'messages' => array (
		'bs-aboutbluespice-about-bluespice'
	)
) + $aResourceModuleTemplate;

unset( $aResoureModuleTemplate );
