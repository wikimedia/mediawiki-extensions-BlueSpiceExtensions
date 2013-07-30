<?php
/**
 * InterWiki Links extension for BlueSpice for MediaWiki
 *
 * Administration interface for adding, editing and deleting interwiki links
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * 
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @version    1.22.0 stable
 * @version    $Id: InterWikiLinks.class.php 9907 2013-06-25 08:52:25Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage InterWikiLinks
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
 
/**
 * Main class for InterWikiLinks extension
 * @package BlueSpice_Extensions
 * @subpackage InterWikiLinks
 */
class InterWikiLinks extends BsExtensionMW {

	/**
	 * Constructor of ShoutBox class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['InterWikiLinks'] = dirname( __FILE__ ) . '/InterWikiLinks.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'InterWikiLinks',
			EXTINFO::DESCRIPTION => 'Administration interface for adding, editing and deleting interwiki links',
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9907 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '1.22.0' )
		);

		WikiAdmin::registerModule('InterWikiLinks', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/images/bs-btn_interwikilinks_v1.png',
			'level' => 'editadmin'
			)
		);

		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'InterWikiLinks', $this, 'getInterWikiLinks', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'InterWikiLinks', $this, 'doEditInterWikiLink', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'InterWikiLinks', $this, 'doDeleteInterWikiLink', 'wikiadmin' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/*
	 * Returns the HTML of the inner InterwikiLinks area
	 * @return string HTML that is to be rendered
	 */
	public function getForm() {
		global $wgOut;
		$wgOut->addModules('ext.bluespice.interWikiLinks');
		return '<div id="InterWikiManagerGrid"></div>';
	}

	/**
	 * Provides a list of current interwiki links. This function is called via AJAX
	 * @param string $sOutput JSON encoded list of interwiki links
	 * @return bool allow other hooked methods to be executed. always true.
	 */
	public function getInterWikiLinks( &$sOutput ) {
		$iLimit = BsCore::getParam('limit', 25, BsPARAM::REQUEST|BsPARAMTYPE::NUMERIC);
		$iStart = BsCore::getParam('start', 0, BsPARAM::REQUEST|BsPARAMTYPE::NUMERIC);

		$data = array();

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 
				'interwiki', 
				'*', 
				'', 
				'', 
				array( "ORDER BY" => "iw_prefix" )
		);
		$data['totalCount'] = $dbr->numRows($res);

		global $wgDBtype, $wgDBprefix;
		if( $wgDBtype == 'oracle' ) {
			$res = $dbr->query( "SELECT * FROM 
									(SELECT iw_prefix,iw_url,iw_api,iw_wikiid,iw_local,iw_trans,row_number() over (order by iw_prefix ASC) rnk  
										FROM \"".strtoupper($wgDBprefix)."INTERWIKI\"  
									) 
									where rnk BETWEEN ".($iStart+1)." AND ".($iLimit + $iStart)
						);
		} else {
			$res = $dbr->select( 
					'interwiki', 
					'*', 
					'', 
					'', 
					array(
						"ORDER BY" => "iw_prefix",
						"LIMIT" => $iLimit,
						"OFFSET" => $iStart
					)
			);
		}

		$data['iwlinks'] = array();
		while ( $row = $dbr->fetchObject( $res )) {
			$tmp = array();
			$tmp['prefix'] = $row->iw_prefix;
			$tmp['url'] = $row->iw_url;
			$data['iwlinks'][] = $tmp;
		}
		$dbr->freeResult( $res );

		//$oEvent = new BsEvent( $this, 'MW:InterWikiManagerBeforeUserListSend', array( 'data' => &$data ) );
		//BsEventDispatcher::getInstance('MW')->notify( $oEvent );

		$sOutput = json_encode($data);
		return true;
	}

	/**
	 * Creates or edits an interwiki link. Called via AJAX function
	 * @param string $sOutput JSON encoded notice of failure or success. Interpreted by ExtJS
	 * @return bool allow other hooked methods to be executed. always true.
	 */
	public function doEditInterWikiLink( &$sOutput ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$sOutput = json_encode( array(
				'success' => false,
				'messages' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
			return;
		}

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'messages' => array()
		);

		$iw_prefix     = trim(BsCore::getParam('iweditprefix')); // $iw_prefix = addslashes($_REQUEST['iw_prefix']);
		$iw_old_prefix = trim(BsCore::getParam('iweditoldprefix')); // $iw_prefix = addslashes($_REQUEST['iw_prefix']);
		$iw_url        = trim(BsCore::getParam('iwediturl')); // $iw_url = addslashes($_REQUEST['iw_url']);
		$bEditMode     = BsCore::getParam('iweditmode', false, BsPARAM::REQUEST|BsPARAMOPTION::DEFAULT_ON_ERROR|BsPARAMTYPE::BOOL);

		if (strlen($iw_prefix) > 32) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iweditprefix', 'msg' => wfMsg( 'bs-interwikilinks-pfx_2long' ) );
		}

		if ($iw_prefix == '') {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iweditprefix', 'msg' => wfMsg( 'bs-interwikilinks-no_pfx' ) );
		}

		if ($iw_url == '') {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iwediturl', 'msg' => wfMsg( 'bs-interwikilinks-no_url' ) );
		}

		$oValidationResult = BsValidator::isValid( 'Url', $iw_url, array('fullResponse' => true) );
		if ( $oValidationResult->getErrorCode() ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iwediturl', 'msg' => $oValidationResult->getI18N() );
		}

		if ( substr_count( $iw_prefix, ' ' ) 
				|| substr_count( $iw_prefix, '"' ) 
				|| substr_count( $iw_prefix, '&' )
				|| substr_count( $iw_prefix, ':' ) ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iweditprefix', 'msg' => wfMsg( 'bs-interwikilinks-invalid_pfx_spc' ) );
		}

		if (strpos($iw_url, ' ')) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iwediturl', 'msg' => wfMsg( 'bs-interwikilinks-invalid_url_spc' ) );
		}

		if ( $bEditMode ) {
			$sSearchPrefix = $iw_old_prefix;
		} else {
			$sSearchPrefix = $iw_prefix;
		}

		$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select('interwiki', 'iw_prefix', array("iw_prefix"=>$sSearchPrefix));
			$num_row = $dbr->numRows( $res );

		if (!$bEditMode) {
			if ($num_row >= 1) {
				$aAnswer['success'] = false;
				$aAnswer['errors'][] = array( 'msg' => wfMsg( 'bs-interwikilinks-pfx_exists' ) );
			}
		} else {
			if ($num_row < 1) {
				$aAnswer['success'] = false;
				$aAnswer['errors'][] = array( 'msg' => wfMsg( 'bs-interwikilinks-no_old_pfx' ) );
			}
		}
		
		if ($aAnswer['success']) {
			$dbw = wfGetDB( DB_MASTER );
			if (!$bEditMode) {
				$dbw->insert( 'interwiki',
						array(  
								'iw_prefix' => $iw_prefix,
								'iw_url' => $iw_url,
								'iw_local' => '0'
						)
				);
			} else {
				$dbw->update( 'interwiki',
					array( 'iw_prefix' => $iw_prefix, 'iw_url' => $iw_url),
					array( 'iw_prefix' => $iw_old_prefix )
				);
			}
			$aAnswer['messages'][] = $bEditMode? wfMsg( 'bs-interwikilinks-link_added' ) : wfMsg( 'bs-interwikilinks-link_created' );
		}

		$sOutput = json_encode( $aAnswer );
		return true;
	}



	/**
	 * Deletes an interwiki link. Called via AJAX function
	 * @param string $sOutput JSON encoded notice of failure or success. Interpreted by ExtJS
	 * @return bool allow other hooked methods to be executed. always true.
	 */
	public function doDeleteInterWikiLink( &$sOutput ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$sOutput = json_encode( array(
				'success' => false,
				'messages' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
			return;
		}

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'messages' => array()
		);

		$iw_prefix = BsCore::getParam('deleteprefix'); // $iw_prefix = addslashes($_REQUEST['iw_prefix']);

		if ( $aAnswer['success'] ) {
			$dbw = wfGetDB( DB_MASTER );
			$res1 = $dbw->delete('interwiki', array('iw_prefix' => $iw_prefix));
		}

		if ($res1 === false) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('msg' => wfMsg( 'bs-interwikilinks-no_url' ) );
		}

		if ($aAnswer['success']) {
			$aAnswer['messages'][] = wfMsg( 'bs-interwikilinks-link_deleted' );
		}

		$sOutput = json_encode( $aAnswer );
		return true;
	}

}
