<?php

/**
 * BlueSpice for MediaWiki
 * Extension: Checklist
 * Description: Provides checklist functions.
 * Authors: Markus Glaser
 *
 * Copyright (C) 2013 Hallo Welt! – Medienwerkstatt GmbH, All rights reserved.
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
 * v0.1
 * - initial release
 */

/**
 * Checklist adds a tag, used in WikiMarkup as follows:
 * checkbox: <bs:checklist />
 */
class Checklist extends BsExtensionMW {

	public $iCheckboxCounter = 0;
	public $bCheckboxFound = false;

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::PARSERHOOK; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME => 'Checklist',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-checklist-desc' )->escaped(),
			EXTINFO::AUTHOR => 'Markus Glaser',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::Checklist';

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'BeforePageDisplay');
		$this->setHook( 'BSExtendedEditBarBeforeEditToolbar' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		$this->setHook( 'VisualEditorConfig' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook Handler for VisualEditorConfig Hook
	 * @param Array $aConfigStandard reference
	 * @param Array $aConfigOverwrite reference
	 * @param Array &$aLoaderUsingDeps reference
	 * @return boolean always true to keep hook alife
	 */
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite, &$aLoaderUsingDeps ) {
		$aLoaderUsingDeps[] = 'ext.bluespice.checklist';

		$iIndexStandard = array_search( 'unlink',$aConfigStandard["toolbar1"] );
		array_splice( $aConfigStandard["toolbar1"], $iIndexStandard + 1, 0, "bscheckbox" );

		// Add context menu entry
		$aConfigStandard["contextmenu"] = str_replace('bsContextMenuMarker', 'bsContextMenuMarker bsChecklist', $aConfigStandard["contextmenu"] );
		return true;
	}

	public static function doChangeCheckItem() {
		$oRequest = RequestContext::getMain()->getRequest();
		$iPos = $oRequest->getInt( 'pos', 0 );
		if ( $iPos == 0 ) return 'false';
		$sValue = $oRequest->getVal( 'value', '' );
		if ( $sValue == '' ) return 'false';
		$sArticleId = $oRequest->getInt( 'articleId', 0 );
		if ( $sArticleId == 0 ) return 'false';

		$oWikiPage = WikiPage::newFromID( $sArticleId );
		$oContent = $oWikiPage->getContent();
		$sContent = $oContent->getNativeData();

		// Maybe a sanity-check is just enough here
		$sNewValue = 'value="';
		if ($sValue == 'true' )
			$sNewValue .= "checked";
		else if ($sValue == 'false' )
			$sNewValue .= "";
		else
			$sNewValue .= $sValue;
		#$sNewValue .= $iPos;
		$sNewValue .= '" ';

		$sContent = self::preg_replace_nth( "/(<bs:checklist )([^>]*?>)/", "$1".$sNewValue."$2", $sContent, $iPos );

		#return $sContent;

		$sSummary = "Modified Check";
		$oContentHandler = $oContent->getContentHandler();
		$oNewContent = $oContentHandler->makeContent($sContent, $oWikiPage->getTitle());
		$oResult = $oWikiPage->doEditContent( $oNewContent, $sSummary );

		return 'true';
	}

	public static function ajaxGetTemplateData() {
		$aTemplateData = array();
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->select(
			array( 'page' ),
			array( 'page_namespace', 'page_title' ),
			array(
				'page_namespace' => NS_TEMPLATE
			)
		);

		$aTitles = array();
		foreach( $res as $row ) {
			$oTitle = Title::makeTitle(
				$row->page_namespace,
				$row->page_title
			);
			// only add those titles that do have actual lists
			$aListOptions = self::getListOptions( $oTitle->getFullText() );
			if (sizeof( $aListOptions ) > 0 ) {
				$aTitles[] = $oTitle->getText();
			}
		}
		foreach ($aTitles as $sTitle ) {
			$oTemplate = new stdClass();
			$oTemplate->text = $sTitle;
			$oTemplate->leaf = true;
			$oTemplate->id = $sTitle;
			$aTemplateData[] = $oTemplate;
		}

		return FormatJson::encode( $aTemplateData );
	}

	public static function ajaxGetItemStoreData() {
		return FormatJson::encode( array() );
	}

	public static function ajaxSaveOptionsList( $sTitle, $aRecords ) {
		$oTitle = Title::newFromText( $sTitle, NS_TEMPLATE );

		$sContent = '';
		foreach( $aRecords as $record ) {
			$sContent .= '* '.$record."\n";
		}

		// TODO: i18n
		$sSummary = "Updated list";

		$oWikiPage = WikiPage::factory( $oTitle );
		$oContentHandler = $oWikiPage->getContentHandler();
		$oNewContent = $oContentHandler->makeContent($sContent, $oWikiPage->getTitle());
		$oResult = $oWikiPage->doEditContent( $oNewContent, $sSummary );

		//TODO: proper json answer
		return FormatJson::encode( "OK" );
	}

	public static function getOptionsList() {
		$oRequest = RequestContext::getMain()->getRequest();
		$sList = $oRequest->getVal( 'listId', '' );
		$theList = self::getListOptions( $sList );
		return FormatJson::encode( $theList );
	}

	public static function getAvailableOptions() {
		$aTemplateData = array();
		$dbr = wfGetDB(DB_SLAVE);
		$res = $dbr->select(
			array( 'page' ),
			array( 'page_namespace', 'page_title' ),
			array(
				'page_namespace' => NS_TEMPLATE
			)
		);

		$aAvailableOptions = array();
		foreach( $res as $row ) {
			$oTitle = Title::makeTitle(
				$row->page_namespace,
				$row->page_title
			);
			// only add those titles that do have actual lists
			$aListOptions = self::getListOptions( $oTitle->getFullText() );
			if (sizeof( $aListOptions ) > 0 ) {
				$aAvailableOptions = array_merge($aAvailableOptions, $aListOptions);
			}
		}
		foreach ($aAvailableOptions as $sOption ) {
			$oTemplate = new stdClass();
			$oTemplate->text = $sOption;
			$oTemplate->leaf = true;
			$oTemplate->id = $sOption;
			$aTemplateData[] = $oTemplate;
		}

		return FormatJson::encode( $aTemplateData );
	}

	/*http://www.php.net/manual/en/function.preg-replace.php#112400*/
	protected static function preg_replace_nth($pattern, $replacement, $subject, $nth=1) {
		return preg_replace_callback($pattern,
			function($found) use (&$pattern, &$replacement, &$nth) {
					$nth--;
					if ($nth==0) {
						$sResult = preg_replace( '/value=".*?" /', '', reset($found) );
						$sResult = preg_replace($pattern, $replacement, $sResult );
						return $sResult;
					}
					return reset($found);
			}, $subject,$nth  );
	}

	/**
	 *
	 * @param Parser $parser
	 * @return boolean
	 */
	public function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'bs:checklist', array( &$this, 'onMagicWordBsChecklist' ) );
		return true;
	}

	public function onBSExtendedEditBarBeforeEditToolbar( &$aRows, &$aButtonCfgs ) {
		$this->getOutput()->addModuleStyles('ext.bluespice.checklist.styles');
		$this->getOutput()->addModules('ext.bluespice.checklist');

		$aRows[0]['dialogs'][60] = 'bs-editbutton-checklist';

		$aButtonCfgs['bs-editbutton-checklist'] = array(
			'tip' => wfMessage( 'bs-checklist-menu-insert-checkbox' )->plain(),
			'open' => '<bs:checklist />'
		);
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

		$aMessage = array();
		$aMessage[] = wfMessage( 'bs-checklist-tag-checklist-desc' )->plain().'<br />';
		$aMessage[] = wfMessage( 'bs-checklist-tag-checklist-param-type' )->plain();
		$aMessage[] = wfMessage( 'bs-checklist-tag-checklist-param-list' )->plain();
		$aMessage[] = wfMessage( 'bs-checklist-tag-checklist-param-value' )->plain();

		$oResponse->result[] = array(
			'id' => 'bs:checklist',
			'type' => 'tag',
			'name' => 'checklist',
			'desc' => implode( '<br />', $aMessage ),
			'code' => '<bs:checklist />',
		);

		return true;
	}

	public static function getListOptions( $listTitle ) {
		$aOptions = array();
		$oTitle = Title::newFromText( $listTitle, NS_TEMPLATE );
		//echo $args['list']." ".$oTitle->getArticleID();
		if ( is_object( $oTitle )) {
			$oWikiPage = WikiPage::newFromID( $oTitle->getArticleID() );
			if ( is_object( $oWikiPage ) ) {
				$sContent = $oWikiPage->getContent()->getNativeData();
				$aLines = explode( "\n", $sContent );
				foreach ( $aLines as $sLine ) {
					if ( strpos( $sLine, '*' ) !== 0 ) continue;
					$sNewLine = trim(substr($sLine, 1));
					$aOptions[] = $sNewLine;
				}
			}
		}
		return $aOptions;
	}

	public function onMagicWordBsChecklist( $input, $args, $parser ) {
		/*
		 *16:37:57: Echt? Ich dachte du machst ein Edit auf der Seite. Da müsste der Cache doch automatisch invalidiert werden, oder?
		 *16:38:56: Und falls das nicht geht sollte ein $oTitle-&gt;invalidateCache(); den gleichen Effekt haben.
		 */
		$parser->disableCache();

		$this->bCheckboxFound = true;
		$sOut = array();

		if (isset($args['list'])) {
			$aOptions = $this->getListOptions( $args['list'] );
		}

		//$aOptions = array("grün", "blau", "gelb", "rot");
		$sSelectColor = '';
		if (isset($args['type']) && $args['type'] == 'list' ) {
			$sOut[] = "<select {color} ";
			$sOut[] = "id='bs-cb-".$this->getNewCheckboxId()."' ";
			$sOut[] = "onchange='BsChecklist.change(this);' ";
			$sOut[] = ">";

			foreach ( $aOptions as $sOption ) {
				$aOptionSet = explode("|", $sOption);

				if (!$sSelectColor && isset ($aOptionSet[1])) {
					$sSelectColor = "style='color:".$aOptionSet[1].";' ";
				}

				$sOption = $aOptionSet[0];
				$sOut[] = "<option ";
				if (isset ($aOptionSet[1])) {
					$sOut[] = "style='color:".$aOptionSet[1].";' ";
				}
				if (isset ($args['value'] ) && $args['value'] == $sOption ) {
					$sOut[] = "selected='selected'";
					if (isset ($aOptionSet[1])) {
						$sSelectColor = "style='color:".$aOptionSet[1].";' ";
					}
				}
				$sOut[] = ">";
				$sOut[] = $sOption;
				$sOut[] = "</option>";
			}
			$sOut[] = "</select>";
		} else {
			$sOut[] = "<input type='checkbox' ";
			$sOut[] = "id='bs-cb-".$this->getNewCheckboxId()."' ";
			$sOut[] = "onclick='BsChecklist.click(this);' ";
			if (isset ($args['value'] ) && $args['value'] == 'checked') {
				$sOut[] = "checked='checked' ";
			}
			$sOut[] = "/>";
		}
		$sOut = implode($sOut, '');
		$sOut = str_replace('{color}', $sSelectColor, $sOut);
		return $sOut;
	}

	protected function getNewCheckboxId() {
		$this->iCheckboxCounter++;
		return $this->iCheckboxCounter;
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		// also needed in edit mode
		//if ( $this->bCheckboxFound ) {
			$oOutputPage->addModules('ext.bluespice.checklist');
		//}
		return true;
	}

}
