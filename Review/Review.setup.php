<?php

BsExtensionManager::registerExtension( 'Review', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$wgMessagesDirs['Review'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['Review']      = __DIR__ . '/languages/Review.i18n.php';
$wgExtensionMessagesFiles['ReviewAlias'] = __DIR__ . '/languages/SpecialReview.alias.php';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/Review/resources'
);

$wgResourceModules['ext.bluespice.review.styles'] = array(
	'styles' => 'bluespice.review.css'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.review'] = array(
	'scripts' => 'bluespice.review.js',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-review-btnmoveup',
		'bs-review-btnmovedown',
		'bs-review-colstatus',
		'bs-review-colreviewer',
		'bs-review-colcomment',
		'bs-review-lblstartdate',
		'bs-review-lblenddate',
		'bs-review-titleaddreviewer',
		'bs-review-labelcomment',
		'bs-review-confirm-delete-step',
		'bs-review-confirm-delete-review'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.review.overview'] = array(
	'scripts' => 'bluespice.review.overview.js',
	'dependencies' => 'ext.bluespice.review',
	'messages' => array(
		'bs-review-header-page-title',
		'bs-review-header-owner-name',
		'bs-review-header-assessors',
		'bs-review-header-accepted-text',
		'bs-review-header-startdate',
		'bs-review-header-enddate'
	)
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate);

$GLOBALS['wgAutoloadClasses']['Review'] = __DIR__ . '/Review.class.php';
$wgAutoloadClasses['SpecialReview'] = __DIR__ . '/includes/specials/SpecialReview.class.php';
$wgAutoloadClasses['BsReviewProcess'] = __DIR__ . '/includes/ReviewProcess.class.php';
$wgAutoloadClasses['BsReviewProcessStep'] = __DIR__ . '/includes/ReviewProcessStep.class.php';

$wgAutoloadClasses['ViewStateBarBodyElementReview'] = __DIR__ . '/views/view.StateBarBodyElementReview.php';

$wgSpecialPageGroups['Review'] = 'bluespice';

$wgSpecialPages['Review'] = 'SpecialReview';

$wgAjaxExportList[] = 'Review::doEditReview';
$wgAjaxExportList[] = 'Review::getVoteResponse';
$wgAjaxExportList[] = 'Review::getUsers';
$wgAjaxExportList[] = 'SpecialReview::ajaxGetOverview';

$wgLogTypes[] = 'bs-review';
$wgFilterLogTypes['bs-review'] = true;

$wgHooks['LoadExtensionSchemaUpdates'][] = 'Review::getSchemaUpdates';