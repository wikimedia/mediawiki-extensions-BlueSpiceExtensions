<?php

class SpecialFlexiskin extends BsSpecialPage {
	public function __construct($name = '', $restriction = '', $listed = true, $function = false, $file = 'default', $includable = false) {
		parent::__construct( 'Flexiskin'/*, 'sysop'*/ );
	}
	
	/**
	 * 
	 * @global OutputPage $wgOut
	 * @param string $sParameter
	 */
	public function execute($sParameter) {
		parent::execute($sParameter);
		if ( $this->getUser()->isAllowed( 'flexiskinedit' ) === false ) {
			$oOutputPage = $this->getOutput();
			$sOut = wfMessage( 'bs-flexiskin-not-allowed' )->plain();
			$oOutputPage->addHTML($sOut);
			return true;
		}
		$this->getOutput()->addModules("ext.bluespice.flexiskin");
		$this->getOutput()->addHTML('<div id="bs-flexiskin-container"></div>');
	}
}