<?php
namespace BS\UsageTracker\Collectors;
class Database extends Base {

	protected $table;
	protected $uniqueColumns;
	protected $descKey = 'bs-usagetracker-db-collector-desc';

	public function __construct( $config = array() ) {
		parent::__construct( $config );
		if ( isset( $config['config'] ) && is_array( $config['config'] ) ) {
			if ( isset( $config['config']['table'] ) ) {
				$this->table = $config['config']['table'];
			}
			if ( isset( $config['config']['uniqueColumns'] ) ) {
				$this->uniqueColumns =
					is_array( $config['config']['uniqueColumns'] )
					? $config['config']['uniqueColumns']
					: [ $config['config']['uniqueColumns'] ];
			}
		};
	}

	public function getUsageData() {
		$oRes = new \BS\UsageTracker\CollectorResult( $this );
		if ( !$this->table || !$this->uniqueColumns ) {
			throw new \MWException( "UsageTracker::DatabaseCollector: table or columns are not set." );
		}

		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			[ $this->table ],
			$this->uniqueColumns,
			[],
			__METHOD__,
			[ "GROUP BY" => $this->uniqueColumns ]
		);

		$oRes->count = $res->numRows();
		return $oRes;
	}
}