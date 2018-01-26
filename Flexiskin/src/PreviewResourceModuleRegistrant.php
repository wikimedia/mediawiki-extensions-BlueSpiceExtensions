<?php

namespace BlueSpice\Flexiskin;

class PreviewResourceModuleRegistrant {

	/**
	 *
	 * @var \ResourceLoader
	 */
	protected $resourceLoader = null;

	/**
	 *
	 * @var Data\AvailableConfigs
	 */
	protected $reader = null;

	/**
	 *
	 * @param \ResourceLoader $resourceLoader
	 * @param Data\AvailableConfigs $reader
	 */
	public function __construct( $resourceLoader, $reader ) {
		$this->resourceLoader = $resourceLoader;
		$this->reader = $reader;
	}

	public function register() {
		$dataSets = $this->reader->read();
		foreach( $dataSets as $dataSet ) {
			$flexiskinId = $dataSet->flexiskin_id;
			$this->resourceLoader->register(
				\Flexiskin::generateDynamicModuleStyleName(
					"preview.$flexiskinId"
				),
				[
					'class' => 'ResourceLoaderFlexiskinPreviewModule'
				]
			);
		}
	}
}
