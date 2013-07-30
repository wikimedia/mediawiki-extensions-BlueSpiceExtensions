<?php

BsExtensionManager::registerExtension('Review',                          BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$dir = dirname( __FILE__ );
$wgExtensionMessagesFiles['Review']      = $dir . '/Review.i18n.php';
$wgExtensionMessagesFiles['ReviewAlias'] = $dir . '/SpecialReview.alias.php';

$wgAutoloadClasses['SpecialReview'] = $dir . '/SpecialReview.class.php';

$wgSpecialPageGroups['Review'] = 'bluespice';

$wgSpecialPages['Review'] = 'SpecialReview';