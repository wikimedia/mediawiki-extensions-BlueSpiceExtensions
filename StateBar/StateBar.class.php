<?php
/**
 * StateBar extension for BlueSpice
 *
 * Provides a statebar.
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage StateBar
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for StateBar extension
 * @package BlueSpice_Extensions
 * @subpackage StateBar
 */
class StateBar extends BsExtensionMW {

	protected $aTopViews  = array();
	protected $aBodyViews = array();

	protected $aSortTopVars = array();
	protected $aSortBodyVars = array();

	protected $oRedirectTargetTitle = null;
	/**
	 * Initialization of StateBar extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'SkinTemplateOutputPageBeforeExec' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );

		BsConfig::registerVar( 'MW::StateBar::Show', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-statebar-pref-show', 'toggle' );

		$this->mCore->registerBehaviorSwitch( 'bs_nostatebar' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Registers StateBar sort variables
	 */
	public function registerSortVars() {
		wfRunHooks( 'BSStateBarAddSortTopVars', array( &$this->aSortTopVars ) );

		$aDefaultSortTopVars = array(
			'statebartopresponsibleeditorsentries' => '',
			'statebartopreview' => '',
			'statebartopsaferedit' => '',
			'statebartopsafereditediting' => '',
			'statebartoplastedited' => '',
			'statebartoplasteditor' => '',
			'statebartopcategories' => '',
			'statebartopsubpages' => '',
		);
		$this->aSortTopVars = array_merge( $aDefaultSortTopVars, $this->aSortTopVars );
		$this->aSortTopVars = array_filter( $this->aSortTopVars ); //removes entries without value

		wfRunHooks( 'BSStateBarAddSortBodyVars', array( &$this->aSortBodyVars ) );

		$aDefaultSortBodyVars = array (
			'statebarbodyresponsibleeditorsentries' => '',
			'statebarbodyreview' => '',
			'statebarbodyeditsummary' => '',
			'statebarbodysubpages' => '',
			'statebarbodycategories' => '',
		);
		$this->aSortBodyVars = array_merge( $aDefaultSortBodyVars, $this->aSortBodyVars );
		$this->aSortBodyVars = array_filter( $this->aSortBodyVars ); //removes entries without value

		BsConfig::registerVar( 'MW::StateBar::SortTopVars', $this->aSortTopVars , BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_INT | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-statebar-pref-sorttopvars', 'multiselectsort' );
		BsConfig::registerVar( 'MW::StateBar::SortBodyVars', $this->aSortBodyVars , BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_INT | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-statebar-pref-sortbodyvars', 'multiselectsort' );
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		wfProfileIn( 'BS::' . __METHOD__ );

		$aPrefs = array();

		switch ($oVariable->getName()) {
			case 'SortTopVars':
				$aPrefs['type']    = 'multiselectsort';
				$aPrefs['options'] = $this->aSortTopVars;
				break;
			case 'SortBodyVars':
				$aPrefs['type']    = 'multiselectsort';
				$aPrefs['options'] = $this->aSortBodyVars;
				break;
		}

		wfProfileOut( 'BS::' . __METHOD__ );

		return $aPrefs;
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object $oResponse reference
	 * $param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'switches' ) return true;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:statebar';
		$oDescriptor->type = 'switch';
		$oDescriptor->name = 'NOSTATEBAR';
		$oDescriptor->desc = wfMessage( 'bs-statebar-switch-description' )->plain();
		$oDescriptor->code = '__NOSTATEBAR__';
		$oDescriptor->previewable = false;
		$oResponse->result[] = $oDescriptor;

		return true;
	}

	// TODO MRG (06.11.13 21:10): Does this also work in edit mode? It seems, there is no parser
	/**
	 * ParserFirstCallInit Hook is called when the parser initialises for the first time.
	 * @param Parser $parser MediaWiki Parser object
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$parser ) {
		wfProfileIn( 'BS::'.__METHOD__ );

		$this->registerSortVars();

		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Checks wether to set Context or not.
	 * @param Title $oTitle
	 * @param bool $bRedirect
	 * @return Title - null when context check fails
	 */
	public function checkContext( $oTitle, $bRedirect = false ) {
		if ( is_null( $oTitle ) ) return null;
		if ( $oTitle->exists() === false ) return null;
		if ( $oTitle->getNamespace() === NS_SPECIAL ) return null;
		if ( $oTitle->userCan( 'read' ) === false ) return null;

		if ( $bRedirect ) {
			$vNoStatebar = BsArticleHelper::getInstance( $oTitle )->getPageProp( 'bs_nostatebar' );
			if( $vNoStatebar === '' ) {
				return null;
			}
			return $oTitle;
		}

		global $wgRequest;
		if ( $oTitle->isRedirect() && $wgRequest->getVal( 'redirect' ) != 'no' ) {
			//check again for redirect target
			$this->oRedirectTargetTitle =
				BsArticleHelper::getInstance( $oTitle )->getTitleFromRedirectRecurse();
			/* If redirect points to none existing article
			* you don't get redirected, so display StateBar.
			* See HW#2014010710000128
			*/
			if( is_null($this->oRedirectTargetTitle) || !$this->oRedirectTargetTitle->exists() ) {
				return $oTitle;
			}

			return $this->checkContext( $this->oRedirectTargetTitle, true );
		}

		$vNoStatebar = BsArticleHelper::getInstance( $oTitle )->getPageProp( 'bs_nostatebar' );
		if( $vNoStatebar === '' ) {
			return null;
		}
		return $oTitle;
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if ( BsConfig::get( 'MW::StateBar::Show' ) === false ) {
			return true;
		}

		global $wgTitle;
		//PW(24.06.2014):
		//make sure to use wgTitle to get possible redirect as early as possible!
		//also prevents from get wrong data in redirect redirect
		//please do not change!
		$oTitle = $this->checkContext( $wgTitle );
		/* PLEASE DO NOT CHANGE !!!!
			$oTitle = $this->checkContext( $this->getTitle() );
		*/

		if ( is_null( $oTitle ) ) {
			return true;
		}

		$oOutputPage->addModules( 'ext.bluespice.statebar' );
		$oOutputPage->addModuleStyles( 'ext.bluespice.statebar.style' );

		return true;
	}

	/**
	 * Creates the StateBar. on articles.
	 * @param SkinTemplate $sktemplate
	 * @param BaseTemplate $tpl
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateOutputPageBeforeExec( &$sktemplate, &$tpl ) {
		//Check if the context of the statebar is set. If not, we do not have
		//to do anything
		$aModules = $sktemplate->getOutput()->getModules();
		if( !in_array( 'ext.bluespice.statebar', $aModules ) ) {
			return true;
		}

		if ( !is_null( $this->oRedirectTargetTitle ) ) {
			$oTitle = $this->oRedirectTargetTitle;
		}
		wfRunHooks( 'BSStateBarBeforeTopViewAdd', array(
			$this, &$this->aTopViews, $sktemplate->getUser(),
			$sktemplate->getTitle(), $sktemplate )
		);

		if ( count( $this->aTopViews ) == 0 ) {
			return true;
		}

		$aSortTopVars = BsConfig::get('MW::StateBar::SortTopVars');
		if ( !empty( $aSortTopVars ) ) {
			$this->aTopViews = $this->reorderViews( $this->aTopViews, $aSortTopVars );
		}

		$oViewStateBar = new ViewStateBar();
		foreach ( $this->aTopViews as $mKey => $oTopView ) {
			$oViewStateBar->addStateBarTopView( $oTopView );
		}

		if ( $tpl instanceof BsBaseTemplate ) {
			$tpl->data['bs_dataBeforeContent']['bs-statebar'] = array(
				'position' => 20,
				'label' => wfMessage( 'prefs-statebar' )->text(),
				'content' => $oViewStateBar
			);
		} else {
			//this is the case when BlueSpice Skin is not active, so use vector methods.
			$tpl->data['prebodyhtml'] .= $oViewStateBar;
		}

		return true;
	}

	/**
	 * Private Method to reorder views
	 * @param array $aViews
	 * @param array $aViewSort
	 * @return array
	 */
	public function reorderViews( $aViews, $aViewSort ) {
		$aReorderedViews = array();

		foreach( $aViewSort as $sViewKey ) {
			if( isset($aViews[$sViewKey]) ) {
				$aReorderedViews[] = $aViews[$sViewKey];
				unset( $aViews[$sViewKey] );
			}
		}
		foreach( $aViews as $key => $oView ) {
			$aReorderedViews[$key] = $oView;
		}

		return $aReorderedViews;
	}

	/**
	 * Adder-Method for the internal $aTopView field.
	 * @param ViewStateBarTopElement $oTopView
	 * @param int $iSortId
	 */
	public function addTopView( $oTopView, $iSortId = null ) {
		if ( $iSortId === null ) {
			$this->aTopViews[] = $oTopView;
		} else {
			$this->aTopViews[$iSortId] = $oTopView;
		}
	}

	/**
	 * Adder-Method for the internal $aBodyViews field.
	 * @param ViewStateBarBodyElement $oBodyView
	 * @param int $iSortId
	 */
	public function addBodyView( $oBodyView, $iSortId = null ) {
		if ( $iSortId === null ) {
			$this->aBodyViews[] = $oBodyView;
		} else {
			$this->aBodyViews[$iSortId] = $oBodyView;
		}
	}
}