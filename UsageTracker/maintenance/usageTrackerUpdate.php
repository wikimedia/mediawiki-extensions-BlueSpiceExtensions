<?php

/**
 * Called via commandline
 * Can be run without params
 * Registers usage statistics collect jobs with the job queue. In order to
 * actually get the data, you need to execute maintenance/runJobs.php in
 * in addition. Typical commandline:
 * ?> php extensions/BlueSpiceExtensions/UsageTracker/maintenance/usagetrackerUpdate.php
 * ?> php maintenance/runJobs.php
 * runJobs, however, should be run on a cronjob anyways.
 */

//We are on <mediawiki>/extensions/BlueSpiceExtensions/UsageTracker/maintenance
$IP = realpath( dirname( dirname( dirname( __DIR__ ) ) ) );

require_once( $IP.'/BlueSpiceFoundation/maintenance/BSMaintenance.php' );

class UsageTrackerUpdate extends BSMaintenance {
	public function __construct() {
		parent::__construct();
		$this->requireExtension( 'UsageTracker' );
	}

	public function execute() {
		$aData = BsExtensionManager::getExtension( 'UsageTracker' )->getUsageData();
	}

	public function finalSetup() {
		parent::finalSetup();
		$GLOBALS['wgMainCacheType'] = CACHE_NONE;
	}
}

$maintClass = 'UsageTrackerUpdate';
if (defined('RUN_MAINTENANCE_IF_MAIN')) {
	require_once( RUN_MAINTENANCE_IF_MAIN );
} else {
	require_once( DO_MAINTENANCE ); # Make this work on versions before 1.17
}