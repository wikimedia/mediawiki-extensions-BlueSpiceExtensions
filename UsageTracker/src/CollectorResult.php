<?php
namespace BS\UsageTracker;

class CollectorResult {
	public $count = 0;
	public $descriptionKey = '';
	public $identifier = '';
	public $type = '';
	public $updateDate = '';

	public function __construct( $oCollector=null ) {
		if ( is_object( $oCollector ) && ( $oCollector instanceof Collectors\Base ) ) {
			$this->descriptionKey = $oCollector->getDescriptionKey();
			$this->identifier = $oCollector->getIdentifier();
			$this->updateDate = wfTimestamp();
			$this->type = get_class( $oCollector );
		}
	}

	public static function newFromDBRow( $oRow ) {
		$oResult = new self();
		$oCollector = new $oRow->ut_type();
		$oResult->descriptionKey = $oCollector->getDescriptionKey();
		$oResult->identifier = $oRow->ut_identifier;
		$oResult->type = $oRow->ut_type;
		$oResult->updateDate = $oRow->ut_timestamp;
		$oResult->count = $oRow->ut_count;
		unset( $oCollector );
		return $oResult;
	}

	public function getUpdateDate() {
		return $this->updateDate;
	}

	public function getDescription() {
		return wfMessage(
			$this->descriptionKey,
			wfMessage( $this->identifier )->exists()
				?wfMessage( $this->identifier )->text()
				:$this->identifier
			)->text();
	}
}