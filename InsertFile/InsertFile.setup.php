<?php

BsExtensionManager::registerExtension('InsertFile', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_ON_API);

$wgMessagesDirs['InsertFile'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['InsertFile'] = __DIR__ . '/languages/InsertFile.i18n.php';

$GLOBALS['wgAutoloadClasses']['InsertFile'] = __DIR__ . '/InsertFile.class.php';
$wgAutoloadClasses['JsonLicenses']          = __DIR__ . '/includes/JsonLicenses.php';
$wgAutoloadClasses['InsertFileAJAXBackend'] = __DIR__ . '/includes/InsertFileAJAXBackend.php';

$wgAjaxExportList[] = 'InsertFileAJAXBackend::getFilePage';
$wgAjaxExportList[] = 'InsertFileAJAXBackend::getFiles';
$wgAjaxExportList[] = 'InsertFileAJAXBackend::getLicenses';
$wgAjaxExportList[] = 'InsertFileAJAXBackend::getExistsWarning';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/InsertFile/resources'
);

$wgResourceModules['ext.bluespice.insertFile'] = array(
	'scripts' =>  'bluespice.insertFile.js',
	'messages' => array(
		'bs-insertfile-button-image-title',
		'bs-insertfile-button-file-title',
		'bs-insertfile-uploadsdisabled',
		'bs-insertfile-nomatch',
		'bs-insertfile-labelsort',
		'bs-insertfile-labelfilter',
		'bs-insertfile-filename',
		'bs-insertfile-filesize',
		'bs-insertfile-lastmodified',
		'bs-insertfile-labelupload',
		'bs-insertfile-details-title',
		'bs-insertfile-labeldimensions',
		'bs-insertfile-labelalt',
		'bs-insertfile-labelalign' ,
		'bs-insertfile-labellink',
		'bs-insertfile-alignnone',
		'bs-insertfile-alignleft',
		'bs-insertfile-aligncenter',
		'bs-insertfile-alignright',
		'bs-insertfile-labeltype',
		'bs-insertfile-typenone',
		'bs-insertfile-typethumb',
		'bs-insertfile-typeframe',
		'bs-insertfile-typeborder',
		'bs-insertfile-uploadbuttontext',
		'bs-insertfile-uploadimageemptytext',
		'bs-insertfile-uploadimagefieldlabel',
		'bs-insertfile-uploadfileemptytext',
		'bs-insertfile-uploadfilefieldlabel',
		'bs-insertfile-uploaddestfilelabel',
		'bs-insertfile-uploaddescfilelabel',
		'bs-insertfile-uploadwatchthislabel',
		'bs-insertfile-uploadignorewarningslabel',
		'bs-insertfile-uploadsubmitvalue',
		'bs-insertfile-errorloading',
		'bs-insertfile-warning',
		'bs-insertfile-warningupload',
		'bs-insertfile-allowedfiletypesare',
		'bs-insertfile-success',
		'bs-insertfile-error',
		'bs-insertfile-errornofileextensiononupload',
		'bs-insertfile-errornofileextensionondestination',
		'bs-insertfile-errorWrongFileExtensionOnUpload',
		'bs-insertfile-errorWrongImageTypeOnUpload',
		'bs-insertfile-errorWrongFileTypeOnUpload',
		'bs-insertfile-errorWrongFileExtensionOnDestination',
		'bs-insertfile-errorwrongimagetypeondestination',
		'bs-insertfile-errorwrongfiletypeondestination',
		'bs-insertfile-uploadcomplete',
		'bs-insertfile-titlefile' ,
		'bs-insertfile-titleimage' ,
		'bs-insertfile-tipkeepratio',
		'bs-insertfile-pagingtoolbarposition',
		'bs-insertfile-select-a-link',
		'bs-insertfile-license',
		'bs-insertfile-categories',
		'bs-insertfile-upload-waitmessage',
		'bs-insertfile-linktext',
		'bs-insertfile-no-link',
		'bs-insertfile-error-no-imagelink',
		'bs-insertfile-error-no-medialink'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.insertFile.styles'] = array(
	'styles' => 'bluespice.insertFile.css'
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate );