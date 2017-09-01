<?php
namespace BS\UsageTracker\Collectors;
class Property extends Base {

	public function __construct( $aConfig = array() ) {
		parent::__construct( $aConfig );
	}

	public function getUsageData() {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			[ 'page_props' ],
			[ 'pp_propname' ],
			[ 'pp_propname' => $this->identifier ],
			__METHOD__
		);

		$oRes = new \BS\UsageTracker\CollectorResult( $this );
		$oRes->count = $res->numRows();
		return $oRes;
	}
}