<?php
/**
 * Renders the ExtensionInfo.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: ShoutBox.class.php 1284 2011-02-16 11:50:18Z mglaser $
 * @package    BlueSpice_Extensions
 * @subpackage ExtensionInfo
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the ExtensionInfo table.
 * @package    BlueSpice_Extensions
 * @subpackage ExtensionInfo 
 */
class ViewExtensionInfoTable extends ViewBaseElement {

	/**
	 * List of all extensions
	 * @var array Contains an array of Extension info elements as they are declared in the constructor of each extension 
	 */
	protected $mExtensions = array();

	/**
	 * Adds an extension to the internal list.
	 * @param array $aExtension An array that is declared in the constructor of an extension
	 */
	public function addExtension( $aExtension ) {
		$this->mExtensions[] = $aExtension;
	}

	/**
	 * Sets or replaces the internal list of extensions as a whole.
	 * @param array $aExtensions A list of all extensions
	 */
	public function setExtensions( $aExtensions ) {
		$this->mExtensions = $aExtensions;
	}

	/**
	 * Produces the actual output.
	 * @param array $params List of parameters for the rendering of output.
	 * @return string The rendered HTML of the extension list
	 */
	public function execute( $params = false ) {
		global $wgBlueSpiceExtInfo;
		$aOut = array();
		$aOut[] = '<table class="softwaretable"><tr><td style="width:20%; height:18px;"><b>Software</b></td><td><b>Version</b></td></tr><tr><td><a title="'.$wgBlueSpiceExtInfo['url'].'" href="'.$wgBlueSpiceExtInfo['url'].'">'.$wgBlueSpiceExtInfo['name'].'</a></td><td>'.$wgBlueSpiceExtInfo['version']."</td></tr></table><br />";
		$aOut[] = '<div id="bs-extensioninfo-grid"></div>';
		$aOut[] = '<script type="text/javascript">';
		$aOut[] = 'aExtensionData = [';

		$aExtensionInfoArrayList = array();
		foreach ( $this->mExtensions as $aExtension ) {

			if ( !isset( $aExtension[EXTINFO::NAME] ) ) continue;

			if ( key_exists( EXTINFO::STATUS, $aExtension ) ) {
				if ( strtolower( $aExtension[EXTINFO::STATUS] ) == 'alpha' ) $classname = 'alpha';
				elseif ( strtolower( $aExtension[EXTINFO::STATUS] ) == 'beta' ) $classname = 'beta';
				elseif ( strtolower( $aExtension[EXTINFO::STATUS] ) == 'stable' ) $classname = 'stable';
				else $classname = 'standard';
			} else {
				$classname = 'standard';
			}

			$aExtensionInfoArray = array();

			$aExtensionInfoArray[] = '[' ;
			$aExtensionInfoArray[] = '["'.$aExtension[EXTINFO::NAME].'","'.$this->getHelpdeskUrl( $aExtension ).'"],';
			$aExtensionInfoArray[] = '"'.( key_exists( EXTINFO::VERSION, $aExtension )
												?str_replace("$", "", $aExtension[EXTINFO::VERSION] )
												:'' )
										.'",';
			$aExtensionInfoArray[] = '"'.$this->getExtensionDescription( $aExtension ).'",';
			$aExtensionInfoArray[] = '"'.strtolower(( key_exists( EXTINFO::STATUS, $aExtension )?$aExtension[EXTINFO::STATUS]:'' )).'"';
			$aExtensionInfoArray[] = ']';

			$aExtensionInfoArrayList[] = implode( ' ', $aExtensionInfoArray );
		}
		$aOut[] = implode( ','."\n", $aExtensionInfoArrayList );
		$aOut[] = '];';
		$aOut[] = '</script>';

		return implode( "\n", $aOut );
	}

	/**
	 * Retrieves the URL of an extension that points to its helpdesk entry.
	 * @param array $aExtensionInfo An array that is declared in the constructor of an extension
	 * @return string The URL that points to the aproppriate helpdesk entry.
	 */
	private function getHelpdeskUrl( $aExtensionInfo ) {
		//(09.05.2012)PW: added helpdeskurls to mI18n-files
		//$baseUrl = BsConfig::get('MW::ExtensionInfo::HelpdeskBaseUrl');
		$baseUrl = wfMsg( 'bs-extensioninfo-HelpdeskBaseUrl' );
		$sExtensionName = $aExtensionInfo[EXTINFO::NAME];
		$sUrl = $baseUrl . '/' . $sExtensionName;
		return $sUrl;
	}

	/**
	 * Fetches the description of an extension.
	 * @param array $aExtensionInfo An array that is declared in the constructor of an extension
	 * @return string The description of an extension 
	 */
	private function getExtensionDescription( $aExtensionInfo ) {
		if ( !key_exists( EXTINFO::DESCRIPTION, $aExtensionInfo ) ) return '';
		$sExtensionName               = strtolower( $aExtensionInfo[EXTINFO::NAME] );
		$sExtensionDescriptionI18NKey = 'extension-description';
		$sDescription                 = wfMsg( 'bs-' . $sExtensionName . '-' .  $sExtensionDescriptionI18NKey );
		if ($sDescription == $sExtensionDescriptionI18NKey ) {
			$sDescription = $aExtensionInfo[EXTINFO::DESCRIPTION];
		}
		return $sDescription;
	}

}
