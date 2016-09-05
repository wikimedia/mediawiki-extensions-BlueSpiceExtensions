<?php

class SpecialFlexiskin extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'Flexiskin', 'flexiskin-viewspecialpage' );
		$this->getOutput()->addModules( "ext.bluespice.flexiskin" );
		$this->getOutput()->addHTML( '<div id="bs-flexiskin-container"></div>' );
	}

	/**
	 *
	 * @global OutputPage $this->getOutput()
	 * @param type $sParameter
	 * @return type
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );

	}
}
