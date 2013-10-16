<?php

BsExtensionManager::registerExtension('InsertFile', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_ON_API);

$wgExtensionMessagesFiles['InsertFile'] = __DIR__ . '/languages/InsertFile.i18n.php';

$wgAutoloadClasses['JsonLicenses']          = __DIR__ . '/includes/JsonLicenses.php';
$wgAutoloadClasses['InsertFileAJAXBackend'] = __DIR__ . '/includes/InsertFileAJAXBackend.php';

$wgAjaxExportList[] = 'InsertFileAJAXBackend::getFilePage';
$wgAjaxExportList[] = 'InsertFileAJAXBackend::getFiles';
$wgAjaxExportList[] = 'InsertFileAJAXBackend::getLicenses';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/InsertFile/resources'
);

$wgResourceModules['ext.bluespice.insertFile'] = array(
	'scripts' =>  'bluespice.insertFile.js',
	'dependencies' => array(
		'ext.bluespice.extjs'
	),
	'messages' => array(
		'bs-insertfile-button_image_title',
		'bs-insertfile-button_file_title',
		'bs-insertfile-uploadsDisabled',
		'bs-insertfile-noMatch',
		'bs-insertfile-labelSort',
		'bs-insertfile-labelFilter',
		'bs-insertfile-fileName',
		'bs-insertfile-fileSize',
		'bs-insertfile-lastModified',
		'bs-insertfile-labelUpload',
		'bs-insertfile-tabTitle1',
		'bs-insertfile-labelDimensions',
		'bs-insertfile-labelAlt',
		'bs-insertfile-labelAlign' ,
		'bs-insertfile-labelLink',
		'bs-insertfile-alignNone',
		'bs-insertfile-alignLeft',
		'bs-insertfile-alignCenter',
		'bs-insertfile-alignRight',
		'bs-insertfile-labelType',
		'bs-insertfile-typeNone',
		'bs-insertfile-typeThumb',
		'bs-insertfile-typeFrame',
		'bs-insertfile-typeBorder',
		'bs-insertfile-tabTitle2',
		'bs-insertfile-uploadButtonText',
		'bs-insertfile-uploadImageEmptyText',
		'bs-insertfile-uploadImageFieldLabel',
		'bs-insertfile-uploadFileEmptyText',
		'bs-insertfile-uploadFileFieldLabel',
		'bs-insertfile-uploadDestFileLabel',
		'bs-insertfile-uploadDescFileLabel',
		'bs-insertfile-uploadWatchThisLabel',
		'bs-insertfile-uploadIgnoreWarningsLabel',
		'bs-insertfile-uploadSubmitValue',
		'bs-insertfile-specialUpload',
		'bs-insertfile-errorLoading',
		'bs-insertfile-fileNS',
		'bs-insertfile-imageNS',
		'bs-insertfile-wrongType',
		'bs-insertfile-warning',
		'bs-insertfile-warningUpload',
		'bs-insertfile-allowedFiletypesAre',
		'bs-insertfile-success',
		'bs-insertfile-error',
		'bs-insertfile-errorNoFileExtensionOnUpload',
		'bs-insertfile-errorNoFileExtensionOnDestination',
		'bs-insertfile-errorWrongFileExtensionOnUpload',
		'bs-insertfile-errorWrongImageTypeOnUpload',
		'bs-insertfile-errorWrongFileTypeOnUpload',
		'bs-insertfile-errorWrongFileExtensionOnDestination',
		'bs-insertfile-errorWrongImageTypeOnDestination',
		'bs-insertfile-errorWrongFileTypeOnDestination',
		'bs-insertfile-uploadComplete',
		'bs-insertfile-statusNotClear',
		'bs-insertfile-bytes',
		'bs-insertfile-kilobytes' ,
		'bs-insertfile-dateformat',
		'bs-insertfile-titleFile' ,
		'bs-insertfile-titleImage' ,
		'bs-insertfile-tipKeepRatio',
		'bs-insertfile-pagingToolbarPosition',
		'bs-insertfile-select_a_link',
		'bs-insertfile-license',
		'bs-insertfile-categories',
		'bs-insertfile-upload-error',
		'bs-insertfile-upload-warning',
		'bs-insertfile-upload-warning_exists',
		'bs-insertfile-upload-warning_duplicate',
		'bs-insertfile-upload-waitMessage',
		'bs-insertfile-upload-waitTitle',
		'bs-insertfile-upload-default-description',
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
