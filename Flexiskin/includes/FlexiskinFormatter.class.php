<?php

class FlexiskinFormatter {

	public static function format_general( $aConfig, $sNewId ) {
		$aReturn = "";
		if ( $aConfig->customBackgroundColor == "" && (ctype_xdigit( $aConfig->customBackgroundColor )) ){
			$aReturn[] = "body{background-color:#" . $aConfig->backgroundColor . " !important;}";
		}
		else{
			$aReturn[] = "body{background-color:#" . $aConfig->customBackgroundColor . " !important;}";
		}
		if ( isset( $aConfig->backgroundImage ) && $aConfig->backgroundImage != "" ) {
			$aReturn[] = "body{background-image:url('images/" . $aConfig->backgroundImage . "') !important;}";
		}
		else {
			$sPath = RequestContext::getMain()->getSkin()->getSkinStylePath( "resources/images/desktop/bg-lo.png" );
			$aReturn[] = "body{background-image:url('" . $sPath . "') !important;}";
		}
		$aReturn[] = "body{background-repeat:" . $aConfig->repeatBackground . " !important;}";
		wfRunHooks( "BSFlexiskinFormatterGeneral", array( &$aConfig, &$aReturn ) );
		return implode( " \n", $aReturn );
	}

	public static function format_header( $aConfig, $sNewId ) {
		$aReturn = array();
		BsConfig::set("MW::Flexiskin::Logo", $aConfig->logo);
		BsConfig::saveSettings();
		wfRunHooks( "BSFlexiskinFormatterHeader", array( &$aConfig, &$aReturn ) );
		return implode( " \n", $aReturn );
	}

	public static function format_position( $aConfig, $sNewId ) {
		$aReturn = "";
		if ( $aConfig->navigation == 'right' ) {
			$aReturn[] = "#bs-application{position: relative;}";
			$aReturn[] = "#bs-content-column{margin: 0 302px 0 0;}";
			$aReturn[] = "#bs-nav-sections{right: 0; top: 30px;}";
			$aReturn[] = "#footer{margin: 0px 302px 5px 0px}";
		}
		if ( $aConfig->content != 'center' ) {
			$aReturn[] = "#bs-wrapper{margin-" . $aConfig->content . ":0;}";
		}
		if ( $aConfig->fullWidth == 0 ) {
			$aReturn[] = "#bs-menu-top{width:" . (int) $aConfig->width . "px;}";
			$aReturn[] = "#bs-application{width:" . (int) $aConfig->width . "px;}";
			$aReturn[] = "#bs-wrapper{width:" . (int) $aConfig->width . "px;min-width:" . (int) $aConfig->width . "px;}";
			//$aReturn[] = "#bs-tools-container{width:" . ((int) $aConfig->width - 200 + 28) . "px;margin-left:-" . ((int) $aConfig->width - 246) . "px}";
		} else {
			$aReturn[] = "#bs-menu-top{width:100%;}";
			$aReturn[] = "#bs-application{width:100%;}";
			$aReturn[] = "#bs-wrapper{width:100%;min-width:100%;}";
		}
		wfRunHooks( "BSFlexiskinFormatterPosition", array( &$aConfig, &$aReturn ) );

		return implode( " \n", $aReturn );
	}

}
