<?php
/**
 * Renders the Readers frame.
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @version    $Id: view.Readers.php 9950 2013-06-26 14:58:43Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage Authors
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the Readers frame.
 * @package    BlueSpice_Extensions
 * @subpackage Readers
 */
class ViewReaders extends ViewBaseElement {

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		if ( empty( $this->_mItems ) ) {
			return '';
		}

		$sReadersList = '';
		$iReaders = count( $this->_mItems );
		$aOptions = array();
		foreach ( $this->_mItems as $oMiniProfile ) {
			$aOptions = $oMiniProfile->getOptions();
			$oMiniProfile->setOption( 'classes', array( 'bs-readers-profile' ) );
			$sReadersList .= $oMiniProfile->execute();
		}

		$sUsername = $aOptions['user']->getName();
		$aOut = array();
		$aOut[] = '<div class="bs-readers">';
		$aOut[] = '  <fieldset>';
		$aOut[] = '    <legend>';
		$aOut[] = wfMessage( 'bs-readers-title', $iReaders, $sUsername )->text();
		$aOut[] = '    </legend>';
		$aOut[] = $sReadersList;
		$aOut[] = '  </fieldset>';
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}

}