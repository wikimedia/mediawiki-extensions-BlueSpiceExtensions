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
 * @version    1.22.0
 * @version    $Id: InsertMagic.class.php 9745 2013-06-14 12:09:29Z pwirth $
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
			'{{CURRENTYEAR}}',
			'{{CURRENTMONTH}}',
			'{{CURRENTMONTHNAME}}',
			'{{CURRENTMONTHNAMEGEN}}',
			'{{CURRENTMONTHABBREV}}',
			'{{CURRENTDAY}}',
			'{{CURRENTDAY2}}',
			'{{CURRENTDOW}}',
			'{{CURRENTDAYNAME}}',
			'{{CURRENTTIME}}',
			'{{CURRENTHOUR}}',
			'{{CURRENTWEEK}}',
			'{{CURRENTTIMESTAMP}}',
			// {{LOCAL???}} variables are not really reasonable
			'{{SITENAME}}',
			'{{SERVER}}',
			'{{SERVERNAME}}',
			//'{{DIRMARK}}', '{{DIRECTIONMARK}}'
			'{{SCRIPTPATH}}',
			'{{STYLEPATH}}',
			'{{CURRENTVERSION}}',
			'{{CONTENTLANGUAGE}}', //'{{CONTENTLANG}}',
			'{{PAGEID}}',
			'{{PAGESIZE:"page name"}}', //'{{PAGESIZE:<page name>|R}}',
			'{{PROTECTIONLEVEL:"action"}}',
			'{{REVISIONID}}',
			'{{REVISIONDAY}}',
			'{{REVISIONDAY2}}',
			'{{REVISIONMONTH}}',
			'{{REVISIONMONTH1}}',
			'{{REVISIONYEAR}}',
			'{{REVISIONTIMESTAMP}}',
			'{{REVISIONUSER}}',
			'{{DISPLAYTITLE:"title"}}',
			'{{DEFAULTSORT:"sortkey"}}', //'{{DEFAULTSORTKEY:<sortkey>}}', '{{DEFAULTCATEGORYSORT:<sortkey>}}', '{{DEFAULTSORT:<sortkey>|noerror}}', '{{DEFAULTSORT:<sortkey>|noreplace}}',
		),
		//'parser-functions' => array(),
		'behavior-switches' => array(
			'__NOTOC__',
			'__FORCETOC__',
			'__TOC__',
			'__NOEDITSECTION__',
			'__NEWSECTIONLINK__',
			'__NONEWSECTIONLINK__',
			'__NOGALLERY__',
			'__HIDDENCAT__',
			'__NOCONTENTCONVERT__', //'__NOCC__',
			'__NOTITLECONVERT__', //'__NOTC__',
			'__END__',
			'__INDEX__',
			'__NOINDEX__',
			'__STATICREDIRECT__'
		)
	);

	public static $aTags = array(
		'gallery', 'nowiki', 'noinclude', 'includeonly'
	);

	/**
	 * Contructor of the InsertMagic class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['InsertMagic'] = dirname( __FILE__ ) . '/InsertMagic.i18n.php';

		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'InsertMagic',
			EXTINFO::DESCRIPTION => 'Provides a dialog box to add magicwords and tags to an articles content in edit mode.',
			EXTINFO::AUTHOR      => 'Robert Vogel',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9745 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
				'bluespice'    => '1.22.0',
				'VisualEditor' => '1.22.0'
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

		$this->setHook( 'VisualEditorConfig', 'onVisualEditorConfig' );
		$this->setHook( 'AlternateEdit', 'onAlternateEdit' );
		$this->setHook( 'BeforePageDisplay', 'onBeforePageDisplay' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}
	/*
	 * Adds module
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	*/
	public static function onBeforePageDisplay( $out, $skin) {
		$sAction = $out->getRequest()->getVal('action', 'view');
		if( $sAction != 'edit' && $sAction != 'preview' ) return true;
		$out->addModules('ext.bluespice.insertMagic');
		return true;
	}

	/**
	 * Hook Handler for VisualEditorConfig Hook
	 * @param Array $aConfigStandard reference
	 * @param Array $aConfigOverwrite reference
	 * @return boolean always true to keep hook alife
	 */
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite ) {
		$iIndexStandard = array_search( 'unlink',$aConfigStandard["theme_advanced_buttons2"] );
		array_splice( $aConfigStandard["theme_advanced_buttons2"], $iIndexStandard + 1, 0, "hwmagic" );

		$iIndexOverwrite = array_search( 'unlink',$aConfigOverwrite["theme_advanced_buttons1"] );
		array_splice( $aConfigOverwrite["theme_advanced_buttons1"], $iIndexOverwrite + 1, 0, "hwmagic" );
		return true;
	}

	/**
	 * Add the action button to MediaWiki editor.
	 * @param EditPage $EditPage
	 * @return type bool
	 */
	public function onAlternateEdit( $EditPage ) {
		$this->mAdapter->addEditButton(
			'InsertMagic',
			array(
				'id'      => 'im_button',
				'msg'     => wfMsg( 'bs-insertmagic' ),
				'image'   => '/extensions/BlueSpiceExtensions/InsertMagic/resources/images/btn_insertmagic_ed.png',
				'onclick' => 'return false;'
			)
		);

		return true;
	}

	public static function ajaxGetData() {
		$oResponse = new stdClass();
		//Utilize?
		//MagicWord::getDoubleUnderscoreArray()
		//MagicWord::getVariableIDs()
		//MagicWord::getSubstIDs()

		$oResponse->result = array();

		foreach( self::$aTags as $sTag ) {
			$oDescriptor = new stdClass();
			$oDescriptor->id = $sTag;
			$oDescriptor->type = 'tag';
			$oDescriptor->name = $sTag;
			$oDescriptor->desc = wfMessage( 'bs-insertmagic-'.$sTag )->parse();
			$oDescriptor->code = wfMessage( 'bs-insertmagic-'.$sTag.'-code' )->plain();
			$oDescriptor->previewable = true;
			$oResponse->result[] = $oDescriptor;
		}

		foreach( self::$aMagicWords['variables'] as $sVariable ) {
			$oDescriptor = new stdClass();
			$oDescriptor->id = $sVariable;
			$oDescriptor->type = 'variable';
			$oDescriptor->name = substr( $sVariable, 2, -2 );
			$oDescriptor->desc = wfMessage( 'bs-insertmagic-'.$sVariable )->parse();
			$oDescriptor->code = $sVariable;
			$oDescriptor->previewable = true;
			$oResponse->result[] = $oDescriptor;
		}

		foreach( self::$aMagicWords['behavior-switches'] as $sSwitch ) {
			$oDescriptor = new stdClass();
			$oDescriptor->id = $sSwitch;
			$oDescriptor->type = 'switch';
			$oDescriptor->name = substr( $sSwitch, 2, -2 );
			$oDescriptor->desc = wfMessage( 'bs-insertmagic-'.$sSwitch )->parse();
			$oDescriptor->code = $sSwitch;
			$oDescriptor->previewable = false;
			$oResponse->result[] = $oDescriptor;
		}
/*
		$oDescriptor = new stdClass();
		$oDescriptor->id = 'redirect';
		$oDescriptor->type = 'redirect';
		$oDescriptor->name = 'redirect';
		$oDescriptor->desc = wfMessage( 'bs-insertmagic-redirect' )->plain();
		$oDescriptor->code = wfMessage( 'bs-insertmagic-redirect-code' )->plain();
		$oDescriptor->previewable = false;
		$oResponse->result[] = $oDescriptor;
*/
		//Other extensions may inject their tags or MagicWords
		wfRunHooks('BSInsertMagicAjaxGetData', array( &$oResponse, 'tags' ) );
		wfRunHooks('BSInsertMagicAjaxGetData', array( &$oResponse, 'variables' ) ); //For compatibility
		wfRunHooks('BSInsertMagicAjaxGetData', array( &$oResponse, 'switches' ) ); //For compatibility

		return FormatJson::encode($oResponse);
	}
}