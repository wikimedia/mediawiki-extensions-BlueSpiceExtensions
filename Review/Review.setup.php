<?php

BsExtensionManager::registerExtension( 'Review', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE );

$wgMessagesDirs['Review'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['ReviewAlias'] = __DIR__ . '/languages/SpecialReview.alias.php';

$aResourceModuleTemplate = array(
	'localBasePath' => __DIR__ . '/resources',
	'remoteExtPath' => 'BlueSpiceExtensions/Review/resources'
);

$wgResourceModules['ext.bluespice.review.styles'] = array(
	'styles' => 'bluespice.review.css',
	'position' => 'top'
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.review'] = array(
	'scripts' => 'bluespice.review.js',
	'dependencies' => array(
		'ext.bluespice',
	),
	'messages' => array(
		'bs-review-review',
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
	'dependencies' => array (
		'ext.bluespice.extjs',
		'ext.bluespice.review',
		'mediawiki.Title'
	),
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
$wgAutoloadClasses['ReviewFormatter'] = __DIR__ . '/includes/ReviewFormatter.class.php';

$wgAutoloadClasses['ViewStateBarBodyElementReview'] = __DIR__ . '/views/view.StateBarBodyElementReview.php';

$wgSpecialPages['Review'] = 'SpecialReview';

$wgAjaxExportList[] = 'Review::doEditReview';
$wgAjaxExportList[] = 'Review::getVoteResponse';
$wgAjaxExportList[] = 'Review::getUsers';
$wgAjaxExportList[] = 'SpecialReview::ajaxGetOverview';

$wgLogTypes[] = 'bs-review';
$wgFilterLogTypes['bs-review'] = true;

$wgHooks['LoadExtensionSchemaUpdates'][] = 'Review::getSchemaUpdates';
