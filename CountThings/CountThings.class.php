<?php

/**
 * BlueSpice for MediaWiki
 * Extension: CountThings
 * Description: Counts all kinds of things.
 * Authors: Markus Glaser, Mathias Scheer
 *
 * Copyright (C) 2010 Hallo Welt! – Medienwerkstatt GmbH, All rights reserved.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit http://www.blue-spice.org
 *
 * Version information
 * $LastChangedDate: 2013-06-14 14:09:29 +0200 (Fr, 14 Jun 2013) $
 * $LastChangedBy: pwirth $
 * $Rev: 9745 $

 */
/* Changelog
 * v1.20.0
 * - Added hook handler fpr InsertMagic
 * v0.1
 * - initial release
 */

/**
 * CountThings adds 3 tags, used in WikiMarkup as follows:
 * absolute number of articles: <bs:countarticles />
 * Count of Characters, Words, and Pages (2000 chars/page) for article 'Test': <bs:countcharacters>Test</bs:countcharacters>
 * absolute number of users: <bs:countusers />
 */
class CountThings extends BsExtensionMW {

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::PARSERHOOK; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME => 'CountThings',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-countthings-desc' )->escaped(),
			EXTINFO::AUTHOR => 'Markus Glaser, Mathias Scheer',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::CountThings';

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}
	
	/**
	 *
	 * @param Parser $parser
	 * @return boolean 
	 */
	public function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'bs:countarticles', array( &$this, 'onMagicWordBsCountArticles' ) );
		$parser->setHook( 'bs:countusers', array( &$this, 'onMagicWordBsCountUsers' ) );
		$parser->setHook( 'bs:countcharacters', array( &$this, 'onMagicWordBsCountCharacters' ) );
		return true;
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object $oResponse reference
	 * $param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'tags' ) return true;

		$oResponse->result[] = array(
			'id' => 'bs:countarticles',
			'type' => 'tag',
			'name' => 'countarticles',
			'desc' => wfMessage( 'bs-countthings-tag-countarticles-desc' )->escaped(),
			'code' => '<bs:countarticles />',
		);
		
		$oResponse->result[] = array(
			'id' => 'bs:countusers',
			'type' => 'tag',
			'name' => 'countusers',
			'desc' => wfMessage( 'bs-countthings-tag-countusers-desc' )->escaped(),
			'code' => '<bs:countusers />',
		);
		
		$oResponse->result[] = array(
			'id' => 'bs:countcharacters',
			'type' => 'tag',
			'name' => 'countcharacters',
			'desc' => wfMessage( 'bs-countthings-tag-countcharacters-desc' )->escaped(),
			'code' => '<bs:countcharacters>Article One,Article Two,Article Three</bs:countcharacters>',
		);

		return true;
	}

	public function onMagicWordBsCountArticles( $input, $args, $parser ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'page', 'page_id' );
		$out = $dbr->numRows( $res );
		$dbr->freeResult( $res );

		return $out;
	}

	/**
	 *
	 * @param type $input
	 * @param type $args
	 * @param Parser $parser
	 * @return type 
	 */
	public function onMagicWordBsCountUsers( $input, $args, $parser ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select( 'user', 'user_id' );
		$out = $dbr->numRows( $res );
		$dbr->freeResult( $res );
		
		//This is a bugfix for the case that somebody writes a wrong empty tag.
		// <bs:countusers> instead of <bs:countusers />
		//TODO: Do we really need to catch errors like this?
		if( !empty($input) ){
			$out .= $parser->recursiveTagParse( $input );
		}

		return $out;
	}
	/**
	 *
	 * @param type $input
	 * @param type $args
	 * @param Parser $parser
	 * @return type 
	 */
	public function onMagicWordBsCountCharacters( $input, $args, $parser ) {
		$parser->disableCache();
		if ( empty( $input ) ) {
			$oErrorView = new ViewTagError( wfMessage( 'bs-countthings-error-no-input' )->plain() );
			return $oErrorView->execute();
		}

		$sMode = isset($args['mode']) ? str_replace( ' ', '', $args['mode'] ) : 'all';
		$aModes = explode( ',', $sMode );
		$aAvailableModes = array( 'chars', 'words', 'pages', 'all' );

		$sOut = '';
		$bValidModeProvided = false;
		foreach( $aModes as $sMode ) {
			if( !in_array( $sMode, $aAvailableModes ) ){
				$oErrorView = new ViewTagError( wfMessage( 'bs-countthings-error-invalid-mode', $sMode )->plain() );
				$sOut .= $oErrorView->execute();
				continue;
			}
			$bValidModeProvided = true;
		}
		if( $bValidModeProvided == false ) $aModes = array( 'all' );

		$aTitleTexts = explode( ',', $input );
		foreach( $aTitleTexts as $sTitleText ) {
			$oTitle = Title::newFromText( trim( $sTitleText ) );
			if( $oTitle == null || $oTitle->exists() == false ) {
				$oErrorView = new ViewTagError( wfMessage( 'bs-countthings-error-not-exist', $sTitleText )->plain() );
				$sOut .= $oErrorView->execute();
				continue;
			}

			$sContent = BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle ); //Old: last revision

			$oCountView = new ViewCountCharacters();
			$oCountView->setTitle( $oTitle );

			if( in_array( 'all', $aModes ) ) {
				$iChars = strlen( preg_replace( "/\s+/", " ", $sContent ) );
				$iWords = sizeof( explode( ' ', $sContent ) );
				$iPages = ceil( $iChars / 2000 );

				$oCountView->setChars( $iChars );
				$oCountView->setWords( $iWords );
				$oCountView->setPages( $iPages );

				$sOut .= $oCountView->execute();
				continue;
			}

			// TODO RBV (17.02.12 15:34): Find better logic for this...
			if( in_array( 'chars', $aModes ) ) {
				$iChars = strlen( preg_replace( "/\s+/", " ", $sContent ) );

				$oCountView->setChars( $iChars );
			}

			if( in_array( 'words', $aModes ) ) {
				$iChars = strlen( preg_replace( "/\s+/", " ", $sContent ) );
				$iWords = sizeof( explode( ' ', $sContent ) );

				$oCountView->setWords( $iWords );
			}

			if( in_array( 'pages', $aModes ) ) {
				$iChars = strlen( preg_replace( "/\s+/", " ", $sContent ) );
				$iWords = sizeof( explode( ' ', $sContent ) );
				$iPages = ceil( $iChars / 2000 );

				$oCountView->setPages( $iPages );
			}

			$sOut .= $oCountView->execute();
		}

		return $sOut;
	}
}