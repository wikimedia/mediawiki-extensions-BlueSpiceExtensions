<?php

// Register the extension with BlueSpice
BsExtensionManager::registerExtension(
	'BoilerPlate',
	BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE,
	BsACTION::LOAD_ON_API|BsACTION::LOAD_SPECIALPAGE,
	__DIR__
);

// Path to message files
$wgMessagesDirs['BoilerPlate'] = __DIR__ . '/i18n';

// Add extension class to autoloader
$GLOBALS['wgAutoloadClasses']['BoilerPlate'] = __DIR__ . '/BoilerPlate.class.php';

// Commonly used settings for resource loader (in scripts and styles)
$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__.'/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/examples/BoilerPlate/resources' //Make sure to change this path
);

// Styles to be embedded by resource loader
$wgResourceModules['ext.bluespice.boilerPlate.styles'] = array(
	'styles' => 'bluespice.boilerPlate.less',
	'position' => 'top'
) + $aResourceModuleTemplate;

// Scripts to be embedded by resource loader
$wgResourceModules['ext.bluespice.boilerPlate.scripts'] = array(
	'scripts' => 'bluespice.boilerPlate.js',
	// List here all message keys that need to be available in the frontend.
	'messages' => array(
		'bs-boilerplate-somekey'
	),
	'dependencies' => 'ext.bluespice'
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );