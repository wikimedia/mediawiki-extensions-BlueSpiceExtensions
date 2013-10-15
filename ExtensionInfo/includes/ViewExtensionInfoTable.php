<?php
/**
 * Renders the ExtensionInfo.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>

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
		
		$aExtensionInfo = array();
		foreach ( $this->mExtensions as $aExtension ) {

			if ( !isset( $aExtension[EXTINFO::NAME] ) ) continue;

			$aExtensionInfoArray = array(
				'name'        => array(
					$aExtension[EXTINFO::NAME],
					$this->getHelpdeskUrl($aExtension)
				),
				'version'     => $aExtension[EXTINFO::VERSION],
				'description' => $this->getExtensionDescription( $aExtension ),
				'status'      => $aExtension[EXTINFO::STATUS],
			);
			
			$aExtensionInfo[] = $aExtensionInfoArray;
		}
		
		RequestContext::getMain()->getOutput()->addJsConfigVars(
				'aExtensionInfo', $aExtensionInfo
		);
		
		$aOut = array();
		$aOut[] = '<table class="softwaretable">';
		$aOut[] = '  <tr>';
		$aOut[] = '    <th style="width:20%; height:18px;">Software</th>';
		$aOut[] = '    <th>Version</th>';
		$aOut[] = '  </tr>';
		$aOut[] = '  <tr>';
		$aOut[] = '    <td><a title="'.$wgBlueSpiceExtInfo['url'].'" href="'.$wgBlueSpiceExtInfo['url'].'">'.$wgBlueSpiceExtInfo['name'].'</a></td>';
		$aOut[] = '    <td>'.$wgBlueSpiceExtInfo['version'].'</td>';
		$aOut[] = '  </tr>';
		$aOut[] = '</table>';
		$aOut[] = '<div id="bs-extensioninfo-grid"></div>';

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
		$baseUrl = wfMessage( 'bs-extensioninfo-HelpdeskBaseUrl' )->plain();
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
		$sDescription                 = wfMessage( 'bs-' . $sExtensionName . '-' .  $sExtensionDescriptionI18NKey )->plain();
		if ($sDescription == $sExtensionDescriptionI18NKey ) {
			$sDescription = $aExtensionInfo[EXTINFO::DESCRIPTION];
		}
		return $sDescription;
	}

}
