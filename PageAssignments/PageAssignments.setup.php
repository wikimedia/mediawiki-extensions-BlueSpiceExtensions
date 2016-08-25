<?php

if (!defined('MEDIAWIKI')) {
	die('This is an extension to the MediaWiki software and cannot be used standalone.');
}
$bsgPageAssigneeTypes = array(
	'user' => 'BSAssignableUser',
	'group' => 'BSAssignableGroup'
);
wfLoadExtension( 'BlueSpiceExtensions/PageAssignments' );
$wgExtensionFunctions[] = function() {
	PageAssignmentsNotificationHooks::setup();
};