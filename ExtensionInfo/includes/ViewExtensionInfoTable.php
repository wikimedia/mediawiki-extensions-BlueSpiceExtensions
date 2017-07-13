<?php
/**
 * Renders the ExtensionInfo.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>

 * @package    BlueSpice_Extensions
 * @subpackage ExtensionInfo
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
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
		global $bsgBlueSpiceExtInfo, $wgVersion;

		$aExtensionInfo = array();
		foreach ( $this->mExtensions as $sExtName => $aExtension ) {
			if( empty($sExtName) ) {
				continue;
			}
			$aExtension['name'] = array(
				$sExtName,
				$this->getHelpdeskUrl($aExtension)
			);
			if( empty( $aExtension['descriptionmsg'] ) ) {
				$aExtension['descriptionmsg'] = '';
			}
			$aExtension['descriptionmsg'] = wfMessage(
				$aExtension['descriptionmsg']
			)->text();

			$aExtensionInfo[] = $aExtension;
		}

		RequestContext::getMain()->getOutput()->addJsConfigVars( 'aExtensionInfo', $aExtensionInfo );
		$sCreditsLink = ' (<a href="' . SpecialPage::getTitleFor( 'SpecialCredits' )->getFullURL() . '">Credits</a>)';

		$sVersion = $bsgBlueSpiceExtInfo['version'].( ( $bsgBlueSpiceExtInfo['status'] !== 'stable' ) ? ' '.$bsgBlueSpiceExtInfo['status'] : '' );

		$aOut = array();
		$aOut[] = '<table class="bs-softwaretable">';
		$aOut[] = '<tr>';
		$aOut[] = '<th>'.wfMessage( 'bs-extensioninfo-software' )->plain().'</th>';
		$aOut[] = '<th>'.wfMessage( 'bs-extensioninfo-version' )->plain().'</th>';
		$aOut[] = '</tr>';
		$aOut[] = '<tr>';
		$aOut[] = '<td><a title="'.$bsgBlueSpiceExtInfo['url'].'" href="'.$bsgBlueSpiceExtInfo['url'].'">'.$bsgBlueSpiceExtInfo['name'].'</a>'.$sCreditsLink.'</td>';
		$aOut[] = '<td>'.$sVersion.'</td>';
		$aOut[] = '</tr>';
		$aOut[] = '<tr>';
		$aOut[] = '<td><a title="MediaWiki" href="http://www.mediawiki.org/"> MediaWiki </a></td>';
		$oTitle = SpecialPage::getTitleFor( "Version" );
		$aOut[] = '<td><a title="' . $oTitle->getFullText() . '" href="' . $oTitle->getFullURL() . '">' . $wgVersion . '</a></td>';
		$aOut[] = '</tr>';
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
		if( !empty( $aExtensionInfo['url'] ) ) {
			return $aExtensionInfo['url'];
		}
		//(09.05.2012)PW: added helpdeskurls to mI18n-files
		//$baseUrl = BsConfig::get('MW::ExtensionInfo::HelpdeskBaseUrl');
		$baseUrl = 'http://help.bluespice.com/index.php';
		if( empty( $aExtensionInfo['name'] ) ) {
			return $baseUrl;
		}
		$sExtensionName = $aExtensionInfo['name'];
		return "$baseUrl/$sExtensionName";
	}

}
