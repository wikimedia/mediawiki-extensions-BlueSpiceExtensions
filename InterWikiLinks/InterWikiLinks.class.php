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
 * @version    2.22.0 stable
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
	 * Constructor of InterWikiLinks class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'InterWikiLinks',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-interwikilinks-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);

		WikiAdmin::registerModule('InterWikiLinks', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_interwikilinks_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-interwikilinks-label'
			)
		);
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	protected function initExt() {
		wfProfileIn('BS::InterWikiLinks::initExt');

		$this->setHook( 'BeforePageDisplay' );

		wfProfileOut('BS::InterWikiLinks::initExt');
	}

	/**
	 * 
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return boolean - always true
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if( !in_array($oOutputPage->getRequest()->getVal('action', 'view'), array('edit', 'submit')) ) return true;
		$oOutputPage->addModules('bluespice.insertLink.interWikiLinks');
		//TODO implement ow
		$oOutputPage->addJsConfigVars( 'BSInterWikiPrefixes', $this->getInterWikiLinkPrefixes() );
		return true;
	}

	public function getInterWikiLinkPrefixes() {
		$oDbr = wfGetDB( DB_SLAVE );
		$rRes = $oDbr->select( 
				'interwiki', 
				'iw_prefix', 
				'', 
				'', 
				array( "ORDER BY" => "iw_prefix" )
		);
		
		$aInterWikiPrefixes = array();
		while( $o = $oDbr->fetchObject($rRes) ) $aInterWikiPrefixes[] = $o->iw_prefix;
		
		return $aInterWikiPrefixes;
	}
	/*
	 * Returns the HTML of the inner InterwikiLinks area
	 * @return string HTML that is to be rendered
	 */
	public function getForm() {
		$this->getOutput()->addModules( 'ext.bluespice.interWikiLinks' );
		return '<div id="InterWikiLinksGrid"></div>';
	}

	/**
	 * Provides a list of current interwiki links. This function is called via AJAX
	 * @return bool allow other hooked methods to be executed. always true.
	 */
	public static function getInterWikiLinks() {
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;
		global $wgRequest;
		$iLimit = $wgRequest->getInt( 'limit', 25 );
		$iStart = $wgRequest->getInt( 'start', 0 );

		$data = array();

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 
				'interwiki', 
				'*', 
				'', 
				'', 
				array( "ORDER BY" => "iw_prefix" )
		);
		$data['totalCount'] = $dbr->numRows( $res );

		global $wgDBtype, $wgDBprefix;
		if ( $wgDBtype == 'oracle' ) {
			$res = $dbr->query( "SELECT * FROM 
									(SELECT iw_prefix,iw_url,iw_api,iw_wikiid,iw_local,iw_trans,row_number() over (order by iw_prefix ASC) rnk  
										FROM \"".strtoupper( $wgDBprefix )."INTERWIKI\"  
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
		$tmp = array();
		while ( $row = $dbr->fetchObject( $res )) {
			$tmp['iwl_prefix'] = $row->iw_prefix;
			$tmp['iwl_url'] = $row->iw_url;
			$data['iwlinks'][] = $tmp;
		}
		$dbr->freeResult( $res );

		return FormatJson::encode( $data );
	}

	/**
	 * Creates or edits an interwiki link. Called via AJAX function
	 * @return bool allow other hooked methods to be executed. always true.
	 */
	public static function doEditInterWikiLink( $bEditMode, $iw_prefix, $iw_url, $iw_old_prefix = '' ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'message' => array()
		);

		if ( strlen( $iw_prefix ) > 32 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iweditprefix', 'message' => wfMessage( 'bs-interwikilinks-pfxtoolong' )->plain() );
		}

		if ( $iw_prefix == '' ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iweditprefix', 'message' => wfMessage( 'bs-interwikilinks-nopfx' )->plain() );
		}

		if ( $iw_url == '' ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iwediturl', 'message' => wfMessage( 'bs-interwikilinks-nourl' )->plain() );
		}

		$oValidationResult = BsValidator::isValid( 'Url', $iw_url, array( 'fullResponse' => true ) );
		if ( $oValidationResult->getErrorCode() ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iwediturl', 'message' => $oValidationResult->getI18N() );
		}

		if ( substr_count( $iw_prefix, ' ' ) 
			|| substr_count( $iw_prefix, '"' ) 
			|| substr_count( $iw_prefix, '&' )
			|| substr_count( $iw_prefix, ':' ) ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iweditprefix', 'message' => wfMessage( 'bs-interwikilinks-invalid-pfx-spc' )->plain() );
		}

		if ( strpos( $iw_url, ' ' ) ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array('id' => 'iwediturl', 'message' => wfMessage( 'bs-interwikilinks-invalid-url-spc' )->plain() );
		}

		if ( $bEditMode == 'true' ) {
			$sSearchPrefix = $iw_old_prefix;
		} else {
			$sSearchPrefix = $iw_prefix;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'interwiki', 'iw_prefix', array( "iw_prefix" => $sSearchPrefix ) );
		$num_row = $dbr->numRows( $res );

		if ( $bEditMode == 'false' ) {
			if ( $num_row >= 1 ) {
				$aAnswer['success'] = false;
				$aAnswer['errors'][] = array( 'message' => wfMessage( 'bs-interwikilinks-pfxexists' )->plain() );
			}
		} else {
			if ( $num_row < 1 ) {
				$aAnswer['success'] = false;
				$aAnswer['errors'][] = array( 'message' => wfMessage( 'bs-interwikilinks-nooldpfx' )->plain() );
			}
		}

		if ( $aAnswer['success'] ) {
			$dbw = wfGetDB( DB_MASTER );
			if ( $bEditMode == 'false' ) {
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
			$aAnswer['message'][] = $bEditMode ? wfMessage( 'bs-interwikilinks-link-added' )->plain() : wfMessage( 'bs-interwikilinks-link-created' )->plain();
		}
		
		self::purgeTitles( $iw_prefix );

		return FormatJson::encode( $aAnswer );
	}



	/**
	 * Deletes an interwiki link. Called via AJAX function
	 * @return bool allow other hooked methods to be executed. always true.
	 */
	public static function doDeleteInterWikiLink( $iw_prefix ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'message' => array()
		);

		if ( $aAnswer['success'] ) {
			$dbw = wfGetDB( DB_MASTER );
			$res1 = $dbw->delete( 'interwiki', array( 'iw_prefix' => $iw_prefix ) );
		}

		if ( $res1 === false ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = array( 'message' => wfMessage( 'bs-interwikilinks-nourl' )->plain() );
		}

		if ( $aAnswer['success'] ) {
			$aAnswer['message'][] = wfMessage( 'bs-interwikilinks-link-deleted' )->plain();
		}
		
		self::purgeTitles( $iw_prefix );

		return FormatJson::encode( $aAnswer );
	}

	protected static function purgeTitles($iw_prefix) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			'iwlinks',
			array('iwl_from', 'iwl_prefix'),
			array('iwl_prefix' => $iw_prefix)
		);
		
		foreach( $res as $row ) {
			$oTitle = Title::newFromID( $row->iwl_from );
			if( $oTitle instanceof Title == false ) continue;
			$oTitle->invalidateCache();
		}
	}

}