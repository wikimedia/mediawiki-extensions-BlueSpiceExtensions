<?php

/**
 * Called via commandline
 * Can be run without params
 *
 * Defaults are set to:
 * - no limit (script usually successfully runs once)
 * - wiki and wiki-repository (uploaded files in wiki) are indexed
 * - all filetypes (doc, pdf, ppt, xls, txt) are indexed
 *
 */

//We are on <mediawiki>/extensions/BlueSpiceExtensions/ExtendedSearch/maintenance
$IP = realpath( dirname( dirname( dirname( __DIR__ ) ) ) );

require_once( $IP.'/BlueSpiceFoundation/maintenance/BSMaintenance.php' );

class SearchUpdate extends BSMaintenance {

	public function execute() {
		$oSearchService = SearchService::getInstance();
		try {
			$oSearchService->deleteIndex();
		} catch ( Exception $e ) {}

		BuildIndexMainControl::getInstance()->buildIndex();
	}

	public function finalSetup() {
		parent::finalSetup();
		$GLOBALS['wgMainCacheType'] = CACHE_NONE;
	}
}

$maintClass = 'SearchUpdate';
if (defined('RUN_MAINTENANCE_IF_MAIN')) {
	require_once( RUN_MAINTENANCE_IF_MAIN );
} else {
	require_once( DO_MAINTENANCE ); # Make this work on versions before 1.17
}