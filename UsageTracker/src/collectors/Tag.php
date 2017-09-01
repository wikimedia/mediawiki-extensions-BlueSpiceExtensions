<?php
namespace BS\UsageTracker\Collectors;
class Tag extends Base {

	protected $descKey = 'bs-usagetracker-tag-collector-desc';

	public function __construct( $aConfig = array() ) {
		parent::__construct( $aConfig );
	}

	public function getUsageData() {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			[ 'page', 'revision', 'text' ],
			[ 'old_text' ],
			[ 'old_text LIKE "%' . $this->identifier . '%"' ],
			__METHOD__,
			[],
			array(
				'revision' => [ 'JOIN', [ 'page_latest=rev_id' ] ],
				'text' => [ 'JOIN', [ 'rev_text_id=old_id' ] ]
			)
		);

		$oRes = new \BS\UsageTracker\CollectorResult( $this );
		$oRes->count = $res->numRows();
		return $oRes;
	}
}