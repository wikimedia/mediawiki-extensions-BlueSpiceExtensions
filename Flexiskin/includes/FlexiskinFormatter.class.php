<?php

class FlexiskinFormatter {

	public static function format_general( $aConfig, $sNewId ) {
		$aReturn = "";
		if ( $aConfig->customBackgroundColor == "" && ( ctype_xdigit( $aConfig->customBackgroundColor ) ) ){
			$aReturn[] = "body{background-color:#" . $aConfig->backgroundColor . " !important;}";
		}
		else{
			$aReturn[] = "body{background-color:#" . $aConfig->customBackgroundColor . " !important;}";
		}
		if ( isset( $aConfig->backgroundImage ) && $aConfig->backgroundImage != "" ) {
			$oStatus = BsFileSystemHelper::getFileContent( $aConfig->backgroundImage, 'flexiskin' . DS . $sNewId . DS . 'images' );
			if( $oStatus->isGood() ){
				$oFileInfo = new SplFileInfo( $aConfig->backgroundImage );
				$sMime = MimeMagic::singleton()->guessTypesForExtension( $oFileInfo->getExtension() );
				$aReturn[] = "body.mediawiki{background-image:url('data:$sMime;base64," . base64_encode( $oStatus->getValue() ) . "') !important;}";
			}
			else{
				$aReturn[] = "body.mediawiki{background-image:none !important;}";
			}
		}
		elseif ( isset( $aConfig->backgroundImage ) && $aConfig->backgroundImage == "none" ) {
			$aReturn[] = "body.mediawiki{background-image:none !important;}";
		}
		else {
			$sPath = RequestContext::getMain()->getSkin()->getSkinStylePath( "resources/images/desktop/bg-lo.png" );
			$aReturn[] = "body.mediawiki{background-image:url('" . $sPath . "') !important;}";
		}
		$aReturn[] = "body.mediawiki{background-repeat:" . $aConfig->repeatBackground . " !important;}";
		wfRunHooks( "BSFlexiskinFormatterGeneral", array( &$aConfig, &$aReturn ) );
		return implode( " \n", $aReturn );
	}

	public static function format_header( $aConfig, $sNewId ) {
		$aReturn = array();
		if ( isset( $aConfig->logo ) && $aConfig->logo != "" ) {
			$aReturn[] = self::renderLogo( $aConfig->logo, $sNewId );
		}
		elseif ( !isset( $aConfig->logo ) || $aConfig->logo == "" ){
			$sLegacyLogo = BsConfig::get( 'MW::Flexiskin::Logo' );
			if ( $sLegacyLogo != null && $sLegacyLogo != '' ){
				$aReturn[] = self::renderLogo( $sLegacyLogo, $sNewId );
			}
		}

		wfRunHooks( "BSFlexiskinFormatterHeader", array( &$aConfig, &$aReturn ) );
		return implode( " \n", $aReturn );
	}

	public static function format_position( $aConfig, $sNewId ) {
		$aReturn = "";
		if ( $aConfig->navigation == 'right' ) {
			$aReturn[] = "#bs-application{position: relative !important;}";
			$aReturn[] = "#bs-content-column{margin: 0 302px 0 0 !important;}";
			$aReturn[] = "#bs-nav-sections{right: 0; top: 30px !important;}";
			$aReturn[] = "#footer{margin: 0px 302px 5px 0px !important;}";
		}
		if ( $aConfig->content != 'center' ) {
			$aReturn[] = "#bs-wrapper{margin-" . $aConfig->content . ":0 !important;}";
		}
		if ( $aConfig->fullWidth == 0 ) {
			$aReturn[] = "#bs-menu-top{width:" . (int) $aConfig->width . "px !important;}";
			$aReturn[] = "#bs-application{width:" . (int) $aConfig->width . "px !important;}";
			$aReturn[] = "#bs-wrapper{width:" . (int) $aConfig->width . "px !important;min-width:" . (int) $aConfig->width . "px !important;}";
		} else {
			$aReturn[] = "#bs-menu-top{width:100% !important;}";
			$aReturn[] = "#bs-application{width:100% !important;}";
			$aReturn[] = "#bs-wrapper{width:100% !important;min-width:100% !important;}";
		}

		wfRunHooks( "BSFlexiskinFormatterPosition", array( &$aConfig, &$aReturn ) );
		return implode( " \n", $aReturn );
	}

	public static function renderLogo( $sFileName, $sNewId ) {
		$oStatus = BsFileSystemHelper::getFileContent( $sFileName, 'flexiskin' . DS . $sNewId . DS . 'images' );
		if( $oStatus->isGood() ){
			$oFileInfo = new SplFileInfo( $sFileName );
			$sMime = MimeMagic::singleton()->guessTypesForExtension( $oFileInfo->getExtension() );
			return "#bs-logo > a{background-image:url('data:$sMime;base64," . base64_encode( $oStatus->getValue() ) . "') !important;}";
		}
		return '';
	}

}
