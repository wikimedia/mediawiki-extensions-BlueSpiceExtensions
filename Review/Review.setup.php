<?php

BsExtensionManager::registerExtension('Review', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

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
	'position' => 'bottom',
	'dependencies' => 'ext.bluespice.extjs',
	'messages' => array(
		'bs-review-title',
		'bs-review-updateRow',
		'bs-review-cancelRow',
		'bs-review-btnAddReviewer',
		'bs-review-btnEditReviewer',
		'bs-review-btnRemoveReviewer',
		'bs-review-btnMoveUp',
		'bs-review-btnMoveDown',
		'bs-review-btnOk',
		'bs-review-btnCancel',
		'bs-review-colStatus',
		'bs-review-colReviewer',
		'bs-review-colDelegatedTo',
		'bs-review-colComment',
		'bs-review-lblStartdate',
		'bs-review-lblEnddate',
		'bs-review-btnCreate',
		'bs-review-btnSave',
		'bs-review-btnDelete',
		'bs-review-btnCancel',
		'bs-review-noReviewAssigned',
		'bs-review-headerActions',
		'bs-review-titleAddReviewer',
		'bs-review-titleEditReviewer',
		'bs-review-labelUsername',
		'bs-review-labelComment',
		'bs-review-titleStatus',
		'bs-review-labelTemplate',
		'bs-review-labelTemplateLoad',
		'bs-review-labelTemplateSaveForMe',
		'bs-review-labelTemplateSaveForAll',
		'bs-review-labelTemplateDelete',
		'bs-review-templateName',
		'bs-review-mode',
		'bs-review-modeVote',
		'bs-review-modeSign',
		'bs-review-modeComment',
		'bs-review-modeWorkflow',
		'bs-review-confirm-delete-step',
		'bs-review-confirm-delete-review'
	)
) + $aResourceModuleTemplate;

$wgResourceModules['ext.bluespice.review.overview'] = array(
	'scripts' => 'bluespice.review.overview.js',
	'dependencies' => 'ext.bluespice.review',
	'messages' => array(
		'bs-review-header-page_title',
		'bs-review-header-owner_name',
		'bs-review-header-rev_mode',
		'bs-review-header-assessors',
		'bs-review-header-accepted_text',
		'bs-review-header-startdate',
		'bs-review-header-enddate',
		'bs-review-overviewpanel-alloption',
	)
) + $aResourceModuleTemplate;

unset( $aResourceModuleTemplate);

$wgAutoloadClasses['SpecialReview'] = __DIR__ . '/includes/specials/SpecialReview.class.php';
$wgAutoloadClasses['BsReviewProcess'] = __DIR__ . '/includes/ReviewProcess.class.php';
$wgAutoloadClasses['BsReviewProcessStep'] = __DIR__ . '/includes/ReviewProcessStep.class.php';

$wgAutoloadClasses['ViewReviewForm'] = __DIR__ . '/views/view.ReviewForm.php';
$wgAutoloadClasses['ViewReviewStep'] = __DIR__ . '/views/view.ReviewStep.php';

$wgSpecialPageGroups['Review'] = 'bluespice';

$wgSpecialPages['Review'] = 'SpecialReview';

$wgAjaxExportList[] = 'Review::doEditReview';
$wgAjaxExportList[] = 'Review::getVoteResponse';
$wgAjaxExportList[] = 'Review::getUsers';
$wgAjaxExportList[] = 'SpecialReview::ajaxGetOverview';
