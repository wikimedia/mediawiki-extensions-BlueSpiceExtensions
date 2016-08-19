<?php
/**
 * This job is created when a usage tracker requests the usage data to be
 * collected. This may be very resource intense, so the collection of data
 * itself is deferred to a job.
 */
namespace BS\UsageTracker\Jobs;

class UsageTrackerCollectJob extends \Job {

	/**
	 * Configuration of the job
	 * @var array
	 */
	protected $config = array();

	/**
	 * @param Title $title
	 * @param array $params definition array for specific collector
	 */
	public function __construct( $title, $params ) {
		parent::__construct( 'usageTrackerCollectJob', $title, $params );
		$this->config = $params;
	}

	/**
	 * Run the job of collecting usage data for a given collector
	 */
	public function run() {
		\BsExtensionManager::getExtension( 'UsageTracker' )->getUsageData( $this->config );
		return true;
	}

}
