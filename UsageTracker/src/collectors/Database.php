<?php
namespace BS\UsageTracker\Collectors;
class Database extends Base {

	protected $table;
	protected $uniqueColumns;

	public function __construct( $config = array() ) {
		parent::__construct( $config );
		if ( isset( $config['config'] ) && is_array( $config['config'] ) ) {
			if ( isset( $config['config']['table'] ) ) {
				$this->table = $config['config']['table'];
			}
			if ( isset( $config['config']['uniqueColumns'] ) ) {
				$this->uniqueColumn =
					is_array( $config['config']['uniqueColumns'] )
					? $config['config']['uniqueColumns']
					: [ $config['config']['uniqueColumns'] ];
			}
		};
	}

	public function getUsageData() {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			[ $this->table ],
			$this->uniqueColumns,
			[],
			__METHOD__,
			[ "GROUP BY" => $this->uniqueColumns ]
		);

		$oRes = new \BS\UsageTracker\CollectorResult( $this );
		$oRes->count = $res->numRows();
		return $oRes;
	}
}