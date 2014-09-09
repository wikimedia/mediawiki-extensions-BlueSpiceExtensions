<?php
/**
 * InsertMagic for BlueSpice
 *
 * Provides a dialog box to add magicwords and tags to an articles content in edit mode
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
 *
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    2.22.0
 * @package    BlueSpice_Extensions
 * @subpackage InsertMagic
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - initial release
*/

/**
 * Base class for InsertMagic extension
 * @package BlueSpice_Extensions
 * @subpackage InsertMagic
 */
class InsertMagic extends BsExtensionMW {

	//HINT: http://www.mediawiki.org/wiki/Help:Magic_words
	//HINT: http://de.wikipedia.org/wiki/Wikipedia:MagicWord
	public static $aMagicWords = array(
		'variables' => array(
			array( 'bs-insertmagic-currentyear' => '{{CURRENTYEAR}}' ),
			array( 'bs-insertmagic-currentmonth' => '{{CURRENTMONTH}}' ),
			array( 'bs-insertmagic-currentmonthname' => '{{CURRENTMONTHNAME}}' ),
			array( 'bs-insertmagic-currentmonthnamegen' => '{{CURRENTMONTHNAMEGEN}}' ),
			array( 'bs-insertmagic-currentmonthabbrev' => '{{CURRENTMONTHABBREV}}' ),
			array( 'bs-insertmagic-currentday' => '{{CURRENTDAY}}' ),
			array( 'bs-insertmagic-currentday2' => '{{CURRENTDAY2}}' ),
			array( 'bs-insertmagic-currentdow' => '{{CURRENTDOW}}' ),
			array( 'bs-insertmagic-currentdayname' => '{{CURRENTDAYNAME}}' ),
			array( 'bs-insertmagic-currenttime' => '{{CURRENTTIME}}' ),
			array( 'bs-insertmagic-currenthour' => '{{CURRENTHOUR}}' ),
			array( 'bs-insertmagic-currentweek' => '{{CURRENTWEEK}}' ),
			array( 'bs-insertmagic-currenttimestamp' => '{{CURRENTTIMESTAMP}}' ),
			array( 'bs-insertmagic-sitename' => '{{SITENAME}}' ),
			array( 'bs-insertmagic-server' => '{{SERVER}}' ),
			array( 'bs-insertmagic-servername' => '{{SERVERNAME}}' ),
			array( 'bs-insertmagic-scriptpath' => '{{SCRIPTPATH}}' ),
			array( 'bs-insertmagic-stylepath' => '{{STYLEPATH}}' ),
			array( 'bs-insertmagic-currentversion' => '{{CURRENTVERSION}}' ),
			array( 'bs-insertmagic-currentlanguage' => '{{CONTENTLANGUAGE}}' ), //'{{CONTENTLANG}}',
			array( 'bs-insertmagic-pageid' => '{{PAGEID}}' ),
			array( 'bs-insertmagic-pagesize' => '{{PAGESIZE:"pagename"}}' ), //'{{PAGESIZE:<page name>|R}}',
			array( 'bs-insertmagic-protectionlevel' => '{{PROTECTIONLEVEL:"action"}}' ),
			array( 'bs-insertmagic-revisionid' => '{{REVISIONID}}' ),
			array( 'bs-insertmagic-revisionday' => '{{REVISIONDAY}}' ),
			array( 'bs-insertmagic-revisionday2' => '{{REVISIONDAY2}}' ),
			array( 'bs-insertmagic-revisionmonth' => '{{REVISIONMONTH}}' ),
			array( 'bs-insertmagic-revisionmonth1' => '{{REVISIONMONTH1}}' ),
			array( 'bs-insertmagic-revisionyear' => '{{REVISIONYEAR}}' ),
			array( 'bs-insertmagic-revisiontimestamp' => '{{REVISIONTIMESTAMP}}' ),
			array( 'bs-insertmagic-revisionuser' => '{{REVISIONUSER}}' ),
			array( 'bs-insertmagic-displaytitle' => '{{DISPLAYTITLE:"title"}}' ),
			array( 'bs-insertmagic-defaultsort' => '{{DEFAULTSORT:"sortkey"}}' ), //'{{DEFAULTSORTKEY:<sortkey>}}', '{{DEFAULTCATEGORYSORT:<sortkey>}}', '{{DEFAULTSORT:<sortkey>|noerror}}', '{{DEFAULTSORT:<sortkey>|noreplace}}',
		),
		'behavior-switches' => array(
			array( 'bs-insertmagic-notoc' => '__NOTOC__' ),
			array( 'bs-insertmagic-forcetoc' => '__FORCETOC__' ),
			array( 'bs-insertmagic-toc' => '__TOC__' ),
			array( 'bs-insertmagic-noeditsection' => '__NOEDITSECTION__' ),
			array( 'bs-insertmagic-newsectionlink' => '__NEWSECTIONLINK__' ),
			array( 'bs-insertmagic-nonewsectionlink' => '__NONEWSECTIONLINK__' ),
			array( 'bs-insertmagic-nogallery' => '__NOGALLERY__' ),
			array( 'bs-insertmagic-hiddencat' => '__HIDDENCAT__' ),
			array( 'bs-insertmagic-nocontentconvert' => '__NOCONTENTCONVERT__' ), //'__NOCC__',
			array( 'bs-insertmagic-notitleconvert' => '__NOTITLECONVERT__' ), //'__NOTC__',
			array( 'bs-insertmagic-end' => '__END__' ),
			array( 'bs-insertmagic-index' => '__INDEX__' ),
			array( 'bs-insertmagic-noindex' => '__NOINDEX__' ),
			array( 'bs-insertmagic-staticredirect' => '__STATICREDIRECT__' )
		)
	);

	public static $aTags = array(
		'gallery' => array( 'bs-insertmagic-gallery' => '<gallery></gallery>' ),
		'nowiki' => array( 'bs-insertmagic-nowiki' => '<nowiki></nowiki>' ),
		'noinclude' => array( 'bs-insertmagic-noinclude' => '<noinclude></noinclude>' ),
		'includeonly' => array( 'bs-insertmagic-includeonly' => '<includeonly></includeonly>' )
	);

	/**
	* Contructor of the InsertMagic class
	*/
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER;
		$this->mInfo = array(
			EXTINFO::NAME        => 'InsertMagic',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-insertmagic-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Robert Vogel, Stephan Muggli',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
				'bluespice'    => '2.22.0',
				'VisualEditor' => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::InsertMagic';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of InsertMagic extension
	 */
	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->setHook( 'VisualEditorConfig' );
		$this->setHook( 'BSExtendedEditBarBeforeEditToolbar' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function onBSExtendedEditBarBeforeEditToolbar( &$aRows, &$aButtonCfgs ) {
		$this->getOutput()->addModuleStyles('ext.bluespice.insertMagic.styles');
		$this->getOutput()->addModules('ext.bluespice.insertMagic');

		$aRows[0]['dialogs'][50] = 'bs-editbutton-insertmagic';

		$aButtonCfgs['bs-editbutton-insertmagic'] = array(
			'tip' => wfMessage( 'bs-insertmagic' )->plain()
		);
		return true;
	}

	/**
	* Hook Handler for VisualEditorConfig Hook
	* @param Array $aConfigStandard reference
	* @param Array $aConfigOverwrite reference
	* @param Array &$aLoaderUsingDeps reference
	* @return boolean always true to keep hook alife
	*/
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite, &$aLoaderUsingDeps ) {
		$aLoaderUsingDeps[] = 'ext.bluespice.insertMagic';

		$iIndexStandard = array_search( 'unlink',$aConfigStandard["toolbar1"] );
		array_splice( $aConfigStandard["toolbar1"], $iIndexStandard + 1, 0, "bsmagic" );

		$iIndexOverwrite = array_search( 'unlink',$aConfigOverwrite["toolbar2"] );
		array_splice( $aConfigOverwrite["toolbar2"], $iIndexOverwrite + 1, 0, "bsmagic" );
		return true;
	}

	public static function ajaxGetData() {
		$oResponse = new stdClass();
		//Utilize?
		//MagicWord::getDoubleUnderscoreArray()
		//MagicWord::getVariableIDs()
		//MagicWord::getSubstIDs()

		$oResponse->result = array();

		foreach ( self::$aTags as $sTag => $aData ) {
			foreach ( $aData as $key => $value ) {
				$oDescriptor = new stdClass();
				$oDescriptor->id = $value;
				$oDescriptor->type = 'tag';
				$oDescriptor->name = $sTag;
				$oDescriptor->desc = wfMessage( $key )->text();
				$oDescriptor->code = $value;
				$oDescriptor->previewable = true;
				$oResponse->result[] = $oDescriptor;
			}
		}

		foreach ( self::$aMagicWords['variables'] as $aVariable ) {
			foreach ( $aVariable as $key => $value ) {
				$oDescriptor = new stdClass();
				$oDescriptor->id = $value;
				$oDescriptor->type = 'variable';
				$oDescriptor->name = substr( $value, 2, -2 );
				$oDescriptor->desc = wfMessage( $key )->text();
				$oDescriptor->code = $value;
				$oDescriptor->previewable = true;
				$oResponse->result[] = $oDescriptor;
			}
		}

		foreach ( self::$aMagicWords['behavior-switches'] as $aSwitch ) {
			foreach ( $aSwitch as $key => $value ) {
				$oDescriptor = new stdClass();
				$oDescriptor->id = $value;
				$oDescriptor->type = 'switch';
				$oDescriptor->name = substr( $value, 2, -2 );
				$oDescriptor->desc = wfMessage( $key )->text();
				$oDescriptor->code = $value;
				$oDescriptor->previewable = false;
				$oResponse->result[] = $oDescriptor;
			}
		}

		//Other extensions may inject their tags or MagicWords
		wfRunHooks('BSInsertMagicAjaxGetData', array( &$oResponse, 'tags' ) );
		wfRunHooks('BSInsertMagicAjaxGetData', array( &$oResponse, 'variables' ) ); //For compatibility
		wfRunHooks('BSInsertMagicAjaxGetData', array( &$oResponse, 'switches' ) ); //For compatibility

		return FormatJson::encode($oResponse);
	}
}