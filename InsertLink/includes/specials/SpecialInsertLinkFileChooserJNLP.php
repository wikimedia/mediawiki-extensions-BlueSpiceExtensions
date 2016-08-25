<?php

class SpecialInsertLinkFileChooserJNLP extends UnlistedSpecialPage {
	protected $sJNPLTemplate = <<<THERE
<?xml version="1.0" encoding="utf-8"?>
<jnlp codebase="" href="" >
	<information>
		<title>BSFileLinkChooser</title>
		<vendor>HalloWelt GmbH</vendor>
		<homepage href="http://hallowelt.com"/>
		<description>Simple FileChooser Application</description>
		<description kind="short">BSFileLinkChooser</description>
		<offline-allowed/>
	</information>
	<security>
		<all-permissions/>
	</security>
	<resources>
		<j2se version="1.6+"/>
		<jar href="%s/extensions/BlueSpiceExtensions/InsertLink/vendor/bsFileLinkChooser.jar" main="true"/>
	</resources>
	<application-desc main-class="bsFileLinkChooser.JWSFileChooser"/>
</jnlp>
THERE;

	public function __construct() {
		parent::__construct( 'InsertLinkFileChooserJNLP' );
	}

	public function execute( $subPage ) {
		global $wgServer, $wgScriptPath;

		$this->getOutput()->disable();

		$oResponse = $this->getRequest()->response();
		$oResponse->header( "Content-type: application/x-java-jnlp-file; charset=utf-8" );
		$oResponse->header( "Content-disposition: attachment;filename=bsFileLinkChooser.jnlp" );

		echo sprintf(
			$this->sJNPLTemplate,
			$wgServer.$wgScriptPath
		);
	}

	protected function getGroupName() {
		return 'bluespice';
	}
}
