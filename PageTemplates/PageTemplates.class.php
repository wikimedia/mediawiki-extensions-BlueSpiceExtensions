<?php
/**
 * PageTemplates extension for BlueSpice
 *
 * Displays a list of templates marked as page templates when creating a new article.
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
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @version    1.22.0 stable
 * @package    BlueSpice_Extensions
 * @subpackage PageTemplates
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 *
 * v1.0.0
 * - Code Review
 * - Raised to stable
 * v0.1
 * - initial commit
 */

/**
 * Base class for PageTemplates extension
 * @package BlueSpice_Extensions
 * @subpackage PageTemplates
 */
class PageTemplates extends BsExtensionMW {

	/**
	 * Constructor of PageTemplates class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'PageTemplates',
			EXTINFO::DESCRIPTION => 'Displays a list of templates marked as page templates.',
			EXTINFO::AUTHOR      => 'Markus Glaser, Stephan Muggli',
			EXTINFO::VERSION     => '1.22.0',
			EXTINFO::STATUS      => 'beta',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
										'bluespice'   => '1.22.0'
										)
		);
		$this->mExtensionKey = 'MW::PageTemplates';
		$this->registerExtensionSchemaUpdate( 'bs_pagetemplate', __DIR__.DS.'PageTemplates.sql' );

		WikiAdmin::registerModuleClass( 'PageTemplatesAdmin', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_templates_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-pagetemplatesadmin-label'
		) );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of PageTemplates extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		//Hooks
		$this->setHook( 'LinkBegin' );
		$this->setHook( 'EditPage::showEditForm:initial', 'onEditPageShowEditFormInitial' );
		$this->setHook( 'MessagesPreLoad' );
		$this->setHook( 'ParserFirstCallInit' );

		// Show pages with similar titles when creating pages
		BsConfig::registerVar( 'MW::PageTemplates::ShowSimilar',             false, BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_BOOL,        'bs-pagetemplates-ShowSimilar' );
		// Do not use page template mechanism for these pages
		BsConfig::registerVar( 'MW::PageTemplates::ExcludeNs',               array( -2,-1,6,7,8,9,10,11,14,15 ),
																					BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_ARRAY_INT|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-pagetemplates-ExcludeNs', 'multiselectex' );
		// Hide line after the empty page entry
		BsConfig::registerVar( 'MW::PageTemplates::HideLinesAfterEmptyPage', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL,      'bs-pagetemplates-HideLinesAfterEmptyPage', 'toggle' );
		// Force page to be created in target namespace
		BsConfig::registerVar( 'MW::PageTemplates::ForceNamespace',          false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL,      'bs-pagetemplates-ForceNamespace', 'toggle' );
		// Hide template if page is not in target namespace
		BsConfig::registerVar( 'MW::PageTemplates::HideIfNotInTargetNs',     true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL,      'bs-pagetemplates-HideIfNotInTargetNs', 'toggle' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aNamespaces = array();
		global $wgContLang;

		foreach ( $wgContLang->getNamespaces() as $ns ) {
			$nsIndex = $wgContLang->getNsIndex( $ns );
			$aNamespaces[$nsIndex] = BsNamespaceHelper::getNamespaceName( $nsIndex );
		}

		$aPrefs = array(
				'type' => 'multiselectex',
				'options' => $aNamespaces,
		);
		return $aPrefs;
	}

	/**
	 * Automatically modifies "noarticletext" message. Otherwise, you would have to modify MediaWiki:noarticletext in the wiki, wich causes installation overhead.
	 * @param string $sKey The message key. Note that it comes ucfirst and can be an i18n version (e.g. Noarticletext/de-formal)
	 * @param string $sMessage This variable is called by reference and modified.
	 * @return bool Success marker for MediaWiki Hooks. The message itself is returned in referenced variable $sMessage. Note that it cannot contain pure HTML.
	 */
	public function onMessagesPreLoad( $sKey, &$sMessage ) {
		if ( strstr( $sKey, 'Noarticletext' ) === false ) {
			return true;
		}
		global $wgTitle, $wgOut;
		if ( !is_object( $wgTitle ) ) {
			return true;
		}
		if ( !$wgTitle->userCan( 'edit' ) ) {
			$wgOut->permissionRequired( 'edit' );
			$sMessage = null;
			return false;
		} else if( !$wgTitle->userCan( 'createpage' ) ) {
			$wgOut->permissionRequired( 'createpage' );
			$sMessage = null;
			return false;
		} else {
			$sMessage = '<bs:pagetemplates />';
		}

		return true;
	}

	/**
	 * Registers the pagetemplate tag with the parser
	 * @param Parser $parser The parser object of MediaWiki
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'pagetemplates', array( &$this, 'onTagPageTemplates' ) );
		$parser->setHook( 'bs:pagetemplates', array( &$this, 'onTagPageTemplates' ) );
		return true;
	}

	/**
	 * Callback function that is triggered when the parser encounters a pagetemplate tag
	 * @param string $input innerHTML of the tag
	 * @param array $args tag attributes
	 * @param Parser $parser the parser object of MediaWiki
	 * @return string replacement HTML for the tag
	 */
	public function onTagPageTemplates( $input, $args, $parser ) {
		return $this->renderPageTemplates();
	}

	/**
	 * Renders the pagetemplates form which is displayed when creating a new article
	 * @param bool $bReturnHTML If set, the form is returned as HTML, otherwise as wiki code.
	 * @return string The rendered output
	 */
	protected function renderPageTemplates() {
		global $wgDBtype, $wgTitle;

		// if we are not on a wiki page, return. This is important when calling import scripts that try to create nonexistent pages, e.g. importImages
		if ( !is_object( $wgTitle ) ) return true;

		// TODO RBV (18.05.11 08:53): Coding Conventions bei Variablen. View? BaseView mit Template?
		$sOut = wfMessage( 'bs-pagetemplates-choose-template' )->plain();
		$aOutNs = array();
		$sOutAll = '';
		$sDivAll = '';
		$sDivNs = '';
		$oTargetNsTitle = null;

		$dbr = wfGetDB( DB_SLAVE );

		$sOut .= '<br /><br /><ul><li>';
		$sOut .= BsLinkProvider::makeLink( $wgTitle, wfMessage( 'bs-pagetemplates-empty-page' )->plain(), $aCostumAttr = array(), array( 'preload' => '' ) );
		$sOut .= '<br />' . wfMessage( 'bs-pagetemplates-empty-page-desc' )->plain();
		$sOut .= '</li></ul>';

		if ( BsConfig::get( 'MW::PageTemplates::HideIfNotInTargetNs' ) ) {
			if ( $wgDBtype == 'postgres' ) {
				$aConds = array( "pt_target_namespace IN ('" . $wgTitle->getNamespace() . "', '-99')" );
			} else {
				$aConds = array( 'pt_target_namespace IN (' . $wgTitle->getNamespace() . ', -99)' );
			}
		} else {
			$aConds = array();
		}

		if ( $wgDBtype == 'postgres' ) {
			$res = $dbr->select(
				array( 'bs_pagetemplate' ),
				array( "pt_template_title, pt_template_namespace, pt_label, pt_desc, pt_target_namespace" ),
				$aConds,
				__METHOD__,
				array( 'ORDER BY' => 'pt_label' )
			);
		} else {
			$res = $dbr->select(
				array( 'bs_pagetemplate' ),
				array( 'pt_template_title', 'pt_template_namespace', 'pt_label', 'pt_desc', 'pt_target_namespace' ),
				$aConds,
				__METHOD__,
				array( 'ORDER BY' => 'pt_label' )
			);
		}

		if ( $res && $dbr->numRows( $res ) > 0 ) {
			while ( $row = $dbr->fetchObject( $res ) ) {
				$oTitle = Title::makeTitle( $row->pt_template_namespace, $row->pt_template_title );
				// TODO MRG (06.09.11 12:53): -99 is "all namespaces". Pls use a more telling constant
				if ( ( BsConfig::get( 'MW::PageTemplates::ForceNamespace' ) && $row->pt_target_namespace != "-99" )
						|| $row->pt_target_namespace == $wgTitle->getNamespace() || BsConfig::get( 'MW::PageTemplates::HideIfNotInTargetNs' ) == false ) {
					if ( !isset( $aOutNs[$row->pt_target_namespace] ) ) {
						$aOutNs[$row->pt_target_namespace] = '';
					}

					if ( BsConfig::get( 'MW::PageTemplates::ForceNamespace' ) ) {
						$sTargetNamespace = BsNamespaceHelper::getNamespaceName( $row->pt_target_namespace, false );
						$oTargetNsTitle = Title::makeTitle( $row->pt_target_namespace, $wgTitle->getText() );
					} else {
						$sTargetNamespace = '';
						$oTargetNsTitle = $wgTitle;
					}

					$aOutNs[$row->pt_target_namespace] .= '<li>' . BsLinkProvider::makeLink( $oTargetNsTitle, $row->pt_label, $aCostumAttr = array(), array( 'preload' => $oTitle->getPrefixedText() ) );
					if ( $row->pt_desc ) $aOutNs[$row->pt_target_namespace] .= "<br/>".$row->pt_desc;
					$aOutNs[$row->pt_target_namespace] .= '</li>';
				} else if ( $row->pt_target_namespace == "-99" ) {
					$sOutAll .= '<li>' . BsLinkProvider::makeLink( $wgTitle, $row->pt_label, $aCostumAttr = array(), array( 'preload' => $oTitle->getPrefixedText() ) );
					if ( $row->pt_desc ) $sOutAll .= "<br />" . $row->pt_desc;
					$sOutAll .= '</li>';
				}
			}
			$dbr->freeResult( $res );
		}

		foreach ( $aOutNs as $iNs => $sTmpOut ) {
			if ( !BsConfig::get( 'MW::PageTemplates::HideLinesAfterEmptyPage' ) ) $sDivNs .= "<br />";
			$sDivNs .= "<br /><h3>" . BsNamespaceHelper::getNamespaceName( $iNs ) . '</h3>';
			$sDivNs .= '<ul>' . $sTmpOut . '</ul>';
		}

		if ( $sOutAll != '' ) {
			if ( !BsConfig::get( 'MW::PageTemplates::HideLinesAfterEmptyPage' ) ) $sDivAll = "<br />";
			$sDivAll .= '<br /><h3>' . wfMessage( 'bs-pagetemplates-general-section' )->plain() . '</h3>';
			$sDivAll .= '<ul>' . $sOutAll . '</ul>';
		} else {
			$sDivAll = "<br />";
		}

		$sOut .= $sDivNs.$sDivAll;

		return $sOut;
	}

	/**
	 * Hook handler for LinkBegin
	 * @param Linker $oLinker
	 * @param Title $oTarget
	 * @param string $sHtml
	 * @param array $aCustomAttribs
	 * @param array $aQuery
	 * @param array $aOptions
	 * @param string $sRet
	 * @return boolean Always true to keep hook running
	 */
	public function onLinkBegin( $oLinker, $oTarget, &$sHtml, &$aCustomAttribs, &$aQuery, &$aOptions, &$sRet ) {
		if ( in_array( 'known', $aOptions ) ) return true;
		if ( !in_array( 'broken', $aOptions ) ){ //It's not marked as "known" and not as "broken" so we have to check
			if ( $oTarget->isKnown() ) return true;
		}

		$aExNs = BsConfig::get( 'MW::PageTemplates::ExcludeNs' );
		if ( in_array( $oTarget->getNamespace(), $aExNs ) ) {
			return true;
		}

		if ( !isset( $aQuery['preload'] ) ) {
			$aQuery['action'] = 'view';
		}

		return true;
	}

	/**
	 * Removes noinclude parts from templates
	 * @param EditPage $oEdit MediaWiki EditPage object
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	function onEditPageShowEditFormInitial( &$oEdit ) {
		if ( RequestContext::getMain()->getRequest()->getVal( 'preload', '' ) ) {
			// TODO MRG (27.09.11 10:28): Put replacement of noinclude into core
			$oEdit->textbox1 = preg_replace( '/<noinclude>.*?<\/noinclude>/s', '',  $oEdit->textbox1 );
		}

		return true;
	}
}