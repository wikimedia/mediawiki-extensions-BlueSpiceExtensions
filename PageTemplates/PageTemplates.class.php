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
 * @version    2.22.0 stable
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
			EXTINFO::DESCRIPTION => wfMessage( 'bs-pagetemplates-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser, Stephan Muggli',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
										'bluespice'   => '2.22.0'
										)
		);
		$this->mExtensionKey = 'MW::PageTemplates';

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

		// Do not use page template mechanism for these pages
		BsConfig::registerVar( 'MW::PageTemplates::ExcludeNs', array( -2,-1,6,7,8,9,10,11,14,15 ), BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_ARRAY_INT|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-pagetemplates-pref-excludens', 'multiselectex' );
		// Force page to be created in target namespace
		BsConfig::registerVar( 'MW::PageTemplates::ForceNamespace', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-pagetemplates-pref-forcenamespace', 'toggle' );
		// Hide template if page is not in target namespace
		BsConfig::registerVar( 'MW::PageTemplates::HideIfNotInTargetNs', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-pagetemplates-pref-hideifnotintargetns', 'toggle' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for Hook 'LoadExtensionSchemaUpdates'
	 * @param object §updater Updater
	 * @return boolean Always true
	 */
	public static function getSchemaUpdates( $updater ) {
		$updater->addExtensionTable(
			'bs_pagetemplate',
			__DIR__.DS.'db'.DS.'PageTemplates.sql'
		);
		return true;
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
	 * Automatically modifies "noarticletext" message. Otherwise, you would
	 * have to modify MediaWiki:noarticletext in the wiki, wich causes
	 * installation overhead.
	 * @param string $sKey The message key. Note that it comes ucfirst and can be an i18n version (e.g. Noarticletext/de-formal)
	 * @param string $sMessage This variable is called by reference and modified.
	 * @return bool Success marker for MediaWiki Hooks. The message itself is returned in referenced variable $sMessage. Note that it cannot contain pure HTML.
	 * @throws PermissionsError
	 */
	public function onMessagesPreLoad( $sKey, &$sMessage ) {
		if ( strstr( $sKey, 'Noarticletext' ) === false ) {
			return true;
		}

		$oTitle = $this->getTitle();
		if ( !is_object( $oTitle ) ) {
			return true;
		}

		/*
		 * As we are in view mode but we present the user only links to
		 * edit/create mode we do a preemptive check wether or not th user
		 * also has edit/create permission
		 */
		if ( !$oTitle->userCan( 'edit' ) ) {
			throw new PermissionsError( 'edit' );
		} elseif ( !$oTitle->userCan( 'createpage' ) ) {
			throw new PermissionsError( 'createpage' );
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
		global $wgDBtype;

		$oTitle = $this->getTitle();
		// if we are not on a wiki page, return. This is important when calling import scripts that try to create nonexistent pages, e.g. importImages
		if ( !is_object( $oTitle ) ) return true;

		$aRes = array();
		$aOutNs = array();
		$dbr = wfGetDB( DB_SLAVE );

		$aConds = array();
		if ( BsConfig::get( 'MW::PageTemplates::HideIfNotInTargetNs' ) ) {
			if ( $wgDBtype == 'postgres' ) {
				$aConds[] = "pt_target_namespace IN ('" . $oTitle->getNamespace() . "', '-99')";
			} else {
				$aConds[] = 'pt_target_namespace IN (' . $oTitle->getNamespace() . ', -99)';
			}
		}

		if ( $wgDBtype == 'postgres' ) {
			$aFields = array( "pt_template_title, pt_template_namespace, pt_label, pt_desc, pt_target_namespace" );
		} else {
			$aFields = array( 'pt_template_title', 'pt_template_namespace', 'pt_label', 'pt_desc', 'pt_target_namespace' );
		}

		$res = $dbr->select(
			array( 'bs_pagetemplate' ),
			$aFields,
			$aConds,
			__METHOD__,
			array( 'ORDER BY' => 'pt_label' )
		);

		// There is always one template for empty page it is added some lines beneath that
		$iCount = $dbr->numRows( $res ) + 1;
		$sOut = wfMessage( 'bs-pagetemplates-choose-template', $iCount )->text();
		$sOutAll = '';
		$oTargetNsTitle = null;

		$sOut .= '<br /><br /><ul><li>';
		$sOut .= BsLinkProvider::makeLink( $oTitle, wfMessage( 'bs-pagetemplates-empty-page' )->plain(), array(), array( 'preload' => '' ) );
		$sOut .= '<br />' . wfMessage( 'bs-pagetemplates-empty-page-desc' )->plain();
		$sOut .= '</li></ul>';

		$oSortingTitle = Title::makeTitle( NS_MEDIAWIKI, 'PageTemplatesSorting' );
		$vOrder = BsPageContentProvider::getInstance()->getContentFromTitle( $oSortingTitle );
		$vOrder = explode( '*', $vOrder );
		$vOrder = array_map( 'trim', $vOrder );

		if ( $res && $dbr->numRows( $res ) > 0 ) {
			while ( $row = $dbr->fetchObject( $res ) ) {
				$aRes[] = $row;
			}
		}
		$dbr->freeResult( $res );

		foreach( $aRes as $row ) {
			$oNsTitle = Title::makeTitle( $row->pt_template_namespace, $row->pt_template_title );

			// TODO MRG (06.09.11 12:53): -99 is "all namespaces". Pls use a more telling constant
			if ( ( BsConfig::get( 'MW::PageTemplates::ForceNamespace' ) && $row->pt_target_namespace != "-99" )
				|| $row->pt_target_namespace == $oTitle->getNamespace()
				|| BsConfig::get( 'MW::PageTemplates::HideIfNotInTargetNs' ) == false ) {

				$sNamespaceName = BsNamespaceHelper::getNamespaceName( $row->pt_target_namespace );
				if ( !isset( $aOutNs[$sNamespaceName] ) ) {
					$aOutNs[$sNamespaceName] = array();
				}

				if ( BsConfig::get( 'MW::PageTemplates::ForceNamespace' ) ) {
					$oTargetNsTitle = Title::makeTitle( $row->pt_target_namespace, $oTitle->getText() );
				} else {
					$oTargetNsTitle = $oTitle;
				}

				$sLink = BsLinkProvider::makeLink(
					$oTargetNsTitle,
					$row->pt_label,
					array(),
					array( 'preload' => $oNsTitle->getPrefixedText() )
				);
				$sLink = '<li>' . $sLink;
				if ( $row->pt_desc ) $sLink .= '<br/>' . $row->pt_desc;
				$sLink .= '</li>';

				$aOutNs[$sNamespaceName][] = array(
					'link' => $sLink,
					'id' => $row->pt_target_namespace
				);
			} elseif ( $row->pt_target_namespace == "-99" ) {
				$sLink = BsLinkProvider::makeLink(
					$oTitle,
					$row->pt_label,
					array(),
					array( 'preload' => $oNsTitle->getPrefixedText() )
				);
				$sOutAll .= '<li>' . $sLink;

				if ( $row->pt_desc ) $sOutAll .= '<br />' . $row->pt_desc;

				$sOutAll .= '</li>';
			}
		}

		if ( !empty( $vOrder ) ) {
			$aTmp = array();
			foreach ( $vOrder as $key => $value ) {
				if ( empty( $value ) ) continue;
				if ( array_key_exists( $value, $aOutNs ) ) {
					$aTmp[$value] = $aOutNs[$value];
				}
			}

			$aOutNs = $aTmp + array_diff_key( $aOutNs, $aTmp );
		}

		$aLeftCol = array();
		$aRightCol = array();
		foreach ( $aOutNs as $sNs => $aTmpOut ) {
			foreach ( $aTmpOut as $key => $aAttribs ) {
				$sNamespaceName = BsNamespaceHelper::getNamespaceName( $aAttribs['id'] );
				if ( $aAttribs['id'] == $oTitle->getNamespace() || $aAttribs['id'] == -99 ) {
					$aLeftCol[$sNamespaceName][] = '<ul>' . $aAttribs['link'] . '</ul>';
				} else {
					$aRightCol[$sNamespaceName][] = '<ul>' . $aAttribs['link'] . '</ul>';
				}
			}
		}

		if ( $sOutAll !== '' ) {
			$sSectionGeneral = wfMessage( 'bs-pagetemplates-general-section' )->plain();
			$aLeftCol[$sSectionGeneral][] = '<ul>' . $sOutAll . '</ul>';
		}

		$sOut .= '<br />';

		if ( !empty( $aLeftCol ) || ( !empty( $aRightCol ) && BsConfig::get( 'MW::PageTemplates::HideIfNotInTargetNs' ) == false ) ) {
			$sOut .= '<table><tr>';

			if ( !empty( $aLeftCol ) ) {
				$sOut .= '<td style="vertical-align:top;">';
				foreach ( $aLeftCol as $sNamespace => $aHtml ) {
					if ( $sNamespace == wfMessage( 'bs-ns_all' )->plain() ) {
						$sNamespace = wfMessage( 'bs-pagetemplates-general-section' )->plain();
					}

					$sOut .= '<br />';
					$sOut .= '<h3>' . $sNamespace . '</h3>';
					$sOut .= implode( '', $aHtml );
				}
				$sOut .= '</td>';
			}

			if ( BsConfig::get( 'MW::PageTemplates::HideIfNotInTargetNs' ) == false ) {
				if ( !empty( $aRightCol ) ) {
					$sOut .= '<td style="vertical-align:top;">';
					foreach ( $aRightCol as $sNamespace => $aHtml ) {
						$sOut .= '<br />';
						$sOut .= '<h3>' . $sNamespace . '</h3>';
						$sOut .= implode( '', $aHtml );
					}
					$sOut .= '</td>';
				}
			}

			$sOut .= '</tr></table>';
		}

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