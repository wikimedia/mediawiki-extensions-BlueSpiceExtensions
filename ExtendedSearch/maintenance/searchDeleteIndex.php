<?php
/**
 * Maintenance script to delete the search index
 *
 * @file
 * @ingroup Maintenance
 * @author Patric Wirth <wirth@hallowelt.biz>
 * @licence GNU General Public Licence 2.0 or later
 */

require_once( dirname(dirname(dirname(dirname(__DIR__)))) . '/maintenance/Maintenance.php' );

class SearchDeleteIndex extends Maintenance {
	protected $sType = array();

	public function __construct() {
		parent::__construct();

		$this->addOption( 'param', 'Specific param to delete', false, true );
	}

	public function execute() {
		$this->sType = $this->getOption( 'param', '' );
		
		//PW(24.07.2013) TODO: add help, descriptions, status/error output
		$solr = SearchService::getInstance();
		echo $solr->deleteIndex( $this->sType );

		return;
	}

	
}

$maintClass = 'SearchDeleteIndex';
if (defined('RUN_MAINTENANCE_IF_MAIN')) {
	require_once( RUN_MAINTENANCE_IF_MAIN );
} else {
	require_once( DO_MAINTENANCE ); # Make this work on versions before 1.17
}
