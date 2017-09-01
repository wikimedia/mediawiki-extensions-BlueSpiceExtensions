<?php

/**
 * BlueSpice MediaWiki
 * Extension: CountThings
 * Description: Counts all kinds of things.
 * Authors: Markus Glaser, Mathias Scheer
 *
 * Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
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
 * For further information visit http://www.bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage CountThings
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource

/**
 * CountThings adds 3 tags, used in WikiMarkup as follows:
 * absolute number of articles: <bs:countarticles />
 * Count of Characters, Words, and Pages (2000 chars/page) for article 'Test': <bs:countcharacters>Test</bs:countcharacters>
 * absolute number of users: <bs:countusers />
 */
class CountThings extends BsExtensionMW {

    protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		$this->setHook( 'BSUsageTrackerRegisterCollectors' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Creates the tag definitions for 'CountThings' extension
	 * @return array
	 */
	public function makeTagExtensionDefinitions() {
		return array(
			'bs:countarticles' => array(
				'descMsg' => 'bs-countthings-tag-countarticles-desc',
				//TODO: Maybe add other stuff from "onBSInsertMagicAjaxGetData" like "code"
				'callback' => array( $this, 'onMagicWordBsCountArticles' ),
				'element' => 'span'
			),
			'bs:countusers' => array(
				'descMsg' => 'bs-countthings-tag-countusers-desc',
				'callback' => array( $this, 'onMagicWordBsCountUsers' ),
				'element' => 'span'
			),
			'bs:countcharacters' => array(
				//Param definition for inner tag content
				'input' => array(
					'type' => 'titlelist',
					'required' => true,
					//specific to type "TitleList"
					'hastoexist' => true
				),
				//Param definitions for tag attributes
				'params' => array(
					'modes' => array(
						'aliases' => 'mode',
						'type' => 'string',
						'islist' => true,
						'values'  => array( 'chars', 'words', 'pages', 'all' ),
						'default' => array( 'all' ),
						'tolower' => true,
						'errormsg' => 'bs-countthings-error-invalid-mode'
					)
				),
				'disableParserCache' => true,
				'descMsg' =>'bs-countthings-tag-countcharacters-desc',
				'callback' => array( $this, 'onMagicWordBsCountCharacters' )
			),
		);
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object $oResponse reference
	 * $param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'tags' ) return true;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:countarticles';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'countarticles';
		$oDescriptor->desc = wfMessage( 'bs-countthings-tag-countarticles-desc' )->escaped();
		$oDescriptor->code = '<bs:countarticles />';
		$oDescriptor->previewable = false;
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/Count_Things';
		$oResponse->result[] = $oDescriptor;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:countusers';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'countusers';
		$oDescriptor->desc = wfMessage( 'bs-countthings-tag-countusers-desc' )->escaped();
		$oDescriptor->code = '<bs:countusers />';
		$oDescriptor->previewable = false;
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/Count_Things';
		$oResponse->result[] = $oDescriptor;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:countcharacters';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'countcharacters';
		$oDescriptor->desc = wfMessage( 'bs-countthings-tag-countcharacters-desc' )->escaped();
		$oDescriptor->code = '<bs:countcharacters>ARTICLENAME</bs:countcharacters>';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = array(
			array(
				'label' => wfMessage( 'bs-countthings-tag-countcharacters-example-1' )->escaped(),
				'code' => '<bs:countcharacters mode="words">ARTICLENAME</bs:countcharacters>'
			),
			array(
				'label' => wfMessage( 'bs-countthings-tag-countcharacters-example-2' )->escaped(),
				'code' => '<bs:countcharacters mode="chars">ARTICLENAME</bs:countcharacters>'
			),
		);
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/Count_Things';
		$oResponse->result[] = $oDescriptor;

		return true;
	}

	/**
	 * Handles <bs:countarticles /> tag
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public function onMagicWordBsCountArticles( $input, $args, $parser ) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'page', 'page_id' );
		$out = $dbr->numRows( $res );
		$dbr->freeResult( $res );

		return $out;
	}

	/**
	 * Handles <bs:countusers /> tag
	 * @param string $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public function onMagicWordBsCountUsers( $input, $args, $parser ) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'user', 'user_id' );
		$out = $dbr->numRows( $res );
		$dbr->freeResult( $res );

		return $out;
	}
	/**
	 * Handles <bs:countcharacters /> tag
	 * @param Title[] $input
	 * @param array $args
	 * @param Parser $parser
	 * @return string
	 */
	public function onMagicWordBsCountCharacters( $input, $args, $parser ) {
		$sOut = '';
		$aModes = $args['modes'];

		foreach( $input as $oTitle ) {
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

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:countarticles'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-bs:countarticles'
			)
		);
		$aCollectorsConfig['bs:countusers'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-bs:countusers'
			)
		);
		$aCollectorsConfig['bs:countcharacters'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-bs:countcharacters'
			)
		);
	}
}