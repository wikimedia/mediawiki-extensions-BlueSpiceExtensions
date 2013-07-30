<?php
/**
 * Special page for ExtensionInfo for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    $Id: SpecialExtensionInfo.class.php 7156 2012-11-07 13:59:09Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage ExtensionInfo
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class SpecialExtensionInfo extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'ExtensionInfo' );
	}

	public function execute( $par ) {
		parent::execute( $par );
		global $wgOut; //TODO: Uses $this->getOutput() in MW 1.18+
		$wgOut->addModules('ext.bluespice.extensioninfo');
		$wgOut->addHtml( $this->getForm() );
	}

	public function getForm() {
		$oViewExtensionInfoTable = new ViewExtensionInfoTable();

		$aInfos = BsExtensionManager::getExtensionInformations();
		ksort($aInfos);

		$oViewExtensionInfoTable->setExtensions( $aInfos );

		return $oViewExtensionInfoTable->execute();
	}

}