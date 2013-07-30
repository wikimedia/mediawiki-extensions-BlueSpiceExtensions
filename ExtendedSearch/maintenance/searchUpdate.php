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
 * These params can be optionally submitted to overwrite defaults
 * -ty   list of domains, comma separated, e.g. '-ty wiki,linked,external'
 * -ft   list of filetypes, comma separated, e.g. '-ft doc,ppt,xls'
 * -li   limit two integers ({iStart},{iRange}), comma separated, e.g. '-li 0,100'
 * -tl   timeLimit seconds, e.g. '-tl 60'
 */
error_reporting( -1 );

//We are on <mediawiki>/extensions/BlueSpiceExtensions/ExtendedSearch/maintenance
$mediaWikiPath = realpath( dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) );

require( $mediaWikiPath.'/maintenance/commandLine.inc' );

// overwrite with command line args
while ( $arg = array_shift( $argv ) ) {
	switch ( $arg ) {
		case '-ty':
			$aAllTypesDisabled = array_fill_keys( explode( ',', 'wiki,repo,linked,special-linked,external' ), false );
			$aTypesFromArgs = array_fill_keys( explode( ',', array_shift( $argv ) ), true );
			$types = array_merge( $aAllTypesDisabled, $aTypesFromArgs );
			break;
		case '-ft':
			$fileTypes = array_fill_keys( explode( ',', array_shift( $argv ) ), true );
			break;
		case '-li':
			$limit = array_shift( $argv );
			break;
		case '-tl':
			$timeLimit = (int) array_shift( $argv );
			break;
		case '-h':
			echo "Params:\n";
			echo "  -ty   list of domains, comma separated, e.g. '-ty wiki,linked,external'\n";
			echo "  -ft   list of filetypes, comma separated, e.g. '-ft doc,ppt,xls'\n";
			echo "  -li   limit two integers ({iStart},{iRange}), comma separated, e.g. '-li 0,100'\n";
			echo "  -tl   timeLimit seconds, e.g. '-tl 60'\n";
			exit;
	}
}

$oSearchService = SearchService::getInstance();
try {
	$oSearchService->deleteIndex();
} catch ( Exception $e ) {}

BuildIndexMainControl::getInstance()->buildIndex();