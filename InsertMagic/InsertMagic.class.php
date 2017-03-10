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
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage InsertMagic
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
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
	 * Initialization of InsertMagic extension
	 */
	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->setHook( 'VisualEditorConfig' );
		$this->setHook( 'BSExtendedEditBarBeforeEditToolbar' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public static function getMagicWords() {
		return self::$aMagicWords;
	}

	public static function getTags() {
		return self::$aTags;
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

	/**
	 * UnitTestsList allows registration of additional test suites to execute
	 * under PHPUnit. Extensions can append paths to files to the $paths array,
	 * and since MediaWiki 1.24, can specify paths to directories, which will
	 * be scanned recursively for any test case files with the suffix "Test.php".
	 * @param array $paths
	 */
	public static function onUnitTestsList ( array &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}
}