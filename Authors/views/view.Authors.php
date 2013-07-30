<?php
/**
 * Renders the Authors frame.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @version    $Id: view.Authors.php 7354 2012-11-16 09:33:21Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage Authors
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// Last review MRG (30.06.11 10:25)

/**
 * This view renders the Authors frame.
 * @package    BlueSpice_Extensions
 * @subpackage Authors
 */
class ViewAuthors extends ViewBaseElement {

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
		
		$sAuthorsList = '';
		$iAuthors = count( $this->_mItems );
		$lastIndex = $iAuthors - 1;
		for( $i = 0; $i < $iAuthors; $i++ ) {
			$oUserProfileImageView = $this->_mItems[$i];
			if( $i == 0 ) {
				$oUserProfileImageView->setOption( 'classes', array('bs-authors-originator') );
			}
			if ($i == $lastIndex ) {
				$oUserProfileImageView->setOption( 'classes', array('bs-authors-lasteditor') );
			}
			
			$sAuthorsList .= $oUserProfileImageView->execute();
			
			//Reset classes to prevent wrong styling in other places
			$oUserProfileImageView->setOption( 'classes', array() );
		}

		if( isset( $this->mOptions['print'] ) && $this->mOptions['print'] === true ) {
			$sAuthorsList = substr( $sAuthorsList, 0, -2 ); //Cut off tailing ', '
		}

		$aOut = array();
		$aOut[] = '<div class="bs-authors">';
		$aOut[] = '  <fieldset>';
		$aOut[] = '    <legend>';
		$aOut[] = wfMsgExt( 'bs-authors-title', array( 'parsemag' ), $iAuthors );
		$aOut[] = '    </legend>';
		$aOut[] = $sAuthorsList;
		$aOut[] = '  </fieldset>';
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}

}