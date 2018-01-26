<?php

class ResourceLoaderFlexiskinModule extends ResourceLoaderFileModule {
	public function __construct( $options = array(), $localBasePath = null, $remoteBasePath = null ) {
		parent::__construct( $options, $localBasePath, $remoteBasePath );
	}

	public function getStyles( \ResourceLoaderContext $context ) {
		$sFlexiSkinId =  $this->makeFlexiSkinID( $context );

		$oStatus = BsFileSystemHelper::getFileContent(
			$this->makeSourceFileName(),
			"flexiskin" . DS . $sFlexiSkinId
		);

		if( !$oStatus->isGood() ){
			wfDebug( "BS::Flexiskin method getStyles: style for " . $sFlexiSkinId . " could not be loaded" );
			return array();
		}
		$aConfJson = FormatJson::decode( $oStatus->getValue() );
		$aConfigs = array();
		foreach( $aConfJson as $aConfig ) {
			$func = "FlexiskinFormatter::format_" . $aConfig->id;

			$bReturn = Hooks::run( "BSFlexiskinGenerateStyleFile", array( &$func, &$aConfig ) );

			if( $bReturn === true && is_callable( $func ) ) {
				 $aConfigs[] = call_user_func_array( $func, array( $aConfig, $sFlexiSkinId ) );
			}
			else {
				wfDebug( "BS::Flexiskin method " . $func . " could not be called." );
			}
		}
		return array( implode( "\n", $aConfigs ) );
	}

	public function makeSourceFileName() {
		return 'conf.json';
	}

	public function makeFlexiSkinID( $context ) {
		return BsConfig::get( 'MW::Flexiskin::Active' );
	}
}
