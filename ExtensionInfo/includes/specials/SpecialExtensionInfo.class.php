<?php
/**
 * Special page for ExtensionInfo for MediaWiki
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage ExtensionInfo
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class SpecialExtensionInfo extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'ExtensionInfo', 'wikiadmin' );
	}

	public function execute( $par ) {
		parent::execute( $par );

		$this->getOutput()->addModuleStyles( 'ext.bluespice.extensioninfo.styles' );
		$this->getOutput()->addModules( 'ext.bluespice.extensioninfo' );
		$this->getOutput()->addHtml( $this->getForm() );
	}

	public function getForm() {
		$oViewExtensionInfoTable = new ViewExtensionInfoTable();

		$aInfos = BsExtensionManager::getExtensionInformation();
		ksort( $aInfos );

		$oViewExtensionInfoTable->setExtensions( $aInfos );

		return $oViewExtensionInfoTable->execute();
	}

	protected function getGroupName() {
		return 'bluespice';
	}
}