<?php
namespace BS\UsageTracker\Collectors;
use BS\UsageTracker\Jobs\UsageTrackerCollectJob;

abstract class Base {
	protected $identifier = 'bs:';
	protected $descKey = 'bs-usagetracker-base-collector-desc';

	/**
	 * Initial configuration. Needed to register as job
	 * @var type
	 */
	protected $config = array();

	public function __construct( $config ) {
		if ( isset( $config['config'] ) && is_array( $config['config'] ) ) {
			if ( isset( $config['config']['identifier'] ) ) {
				$this->identifier = $config['config']['identifier'];
			}
		};
		$this->config = $config;
	}

	public function getDescriptionKey() {
		return $this->descKey;
	}

	public function getIdentifier() {
		return $this->identifier;
	}

	abstract public function getUsageData();

	public function registerJob() {
		$oJob = new UsageTrackerCollectJob(
			\Title::newFromText( $this->identifier . wfTimestampNow() ),
			$this->config
		);
		\JobQueueGroup::singleton()->push( $oJob );
		return true;
	}
}