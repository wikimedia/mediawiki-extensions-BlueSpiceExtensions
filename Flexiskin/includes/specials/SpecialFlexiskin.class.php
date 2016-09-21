<?php

class SpecialFlexiskin extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'Flexiskin', 'flexiskin-viewspecialpage' );
	}

	/**
	 *
	 * @global OutputPage $this->getOutput()
	 * @param string $sParameter
	 * @return void
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$this->getOutput()->addModules( "ext.bluespice.flexiskin" );
		$this->getOutput()->addHTML( '<div id="bs-flexiskin-container"></div>' );
	}
}
