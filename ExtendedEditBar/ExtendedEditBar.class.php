<?php
/**
 * ExtendedEditBar extension for BlueSpice
 *
 * Provides additional buttons to the wiki edit field.
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
 * @author     MediaWiki Extension
 * @version    2.22.0 stable
 * @package    BlueSpice_Extensions
 * @subpackage ExtendedEditBar
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 *
 * v1.0.0
 * - raised to stable
 * v0.1
 * - initial release
 */

/**
 * Base class for ExtendedEditBar extension
 * @package BlueSpice_Extensions
 * @subpackage ExtendedEditBar
 */
class ExtendedEditBar extends BsExtensionMW {

	/**
	 * Constructor of ExtendedEditBar class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'ExtendedEditBar',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-extendededitbar-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'MediaWiki Extension, packaging by Markus Glaser',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.blue-spice.org',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::ExtendedEditBar';
		wfProfileOut('BS::'.__METHOD__ );
	}

	/**
	 * Initialization of ExtendedEditBar extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook('EditPageBeforeEditToolbar');
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 *
	 * @global type $wgStylePath
	 * @global type $wgContLang
	 * @global type $wgLang
	 * @global OutputPage $wgOut
	 * @global type $wgUseTeX
	 * @global type $wgEnableUploads
	 * @global type $wgForeignFileRepos
	 * @param string $toolbar
	 * @return boolean
	 */
	public function onEditPageBeforeEditToolbar( &$toolbar ) {
		$this->getOutput()->addModuleStyles( 'ext.bluespice.extendeditbar.styles' );
		$this->getOutput()->addModules( 'ext.bluespice.extendeditbar' );

		//This is copy-code from EditPage::getEditToolbar(). Sad but neccesary
		//until we suppot WikiEditor and this get's obsolete.
		global $wgContLang, $wgUseTeX, $wgEnableUploads, $wgForeignFileRepos;

		$imagesAvailable = $wgEnableUploads || count( $wgForeignFileRepos );

		$sNs = $this->getLanguage()->getNsText( NS_IMAGE );
		$sCaption = wfMessage( 'bs-extendededitbar-gallerysamplecaption' )->plain();
		$sPicture = wfMessage( 'bs-extendededitbar-gallerysamplepicture' )->plain();
		$sGallery = "{$sNs}:{$sPicture}.jpg|{$sCaption}\n{$sNs}:{$sPicture}.jpg|{$sCaption}";

		$sHeader = wfMessage( 'bs-extendededitbar-tablesampleheader' )->plain();
		$sRow = wfMessage( 'bs-extendededitbar-tablesamplerow' )->plain();
		$sCell = wfMessage( 'bs-extendededitbar-tablesamplecell' )->plain();
		$sTable = "! {$sHeader} 1\n! {$sHeader} 2\n! {$sHeader} 3\n|-\n| {$sRow} 1, ".
			"{$sCell} 1\n| {$sRow} 1, {$sCell} 2\n| {$sRow} 1, {$sCell} 3\n|-\n|".
			" {$sRow} 2, {$sCell} 1\n| {$sRow} 2, {$sCell} 2\n| {$sRow} 2, {$sCell} 3";

		$aMWButtonCfgs = array(
			'mw-editbutton-bold' => array(
				'open'   => '\'\'\'',
				'close'  => '\'\'\'',
				'sample' => wfMessage( 'bold_sample' )->text(),
				'tip'    => wfMessage( 'bold_tip' )->text(),
				'key'    => 'B'
			),
			'mw-editbutton-italic' => array(
				'open'   => '\'\'',
				'close'  => '\'\'',
				'sample' => wfMessage( 'italic_sample' )->text(),
				'tip'    => wfMessage( 'italic_tip' )->text(),
				'key'    => 'I'
			),
			'mw-editbutton-link' => array(
				'open'   => '[[',
				'close'  => ']]',
				'sample' => wfMessage( 'link_sample' )->text(),
				'tip'    => wfMessage( 'link_tip' )->text(),
				'key'    => 'L'
			),
			'mw-editbutton-extlink' => array(
				'open'   => '[',
				'close'  => ']',
				'sample' => wfMessage( 'extlink_sample' )->text(),
				'tip'    => wfMessage( 'extlink_tip' )->text(),
				'key'    => 'X'
			),
			'mw-editbutton-headline' => array(
				'open'   => "\n== ",
				'close'  => " ==\n",
				'sample' => wfMessage( 'headline_sample' )->text(),
				'tip'    => wfMessage( 'headline_tip' )->text(),
				'key'    => 'H'
			),
			'mw-editbutton-image' => $imagesAvailable ? array(
				'open'   => '[[' . $wgContLang->getNsText( NS_FILE ) . ':',
				'close'  => ']]',
				'sample' => wfMessage( 'image_sample' )->text(),
				'tip'    => wfMessage( 'image_tip' )->text(),
				'key'    => 'D',
			) : false,
			'mw-editbutton-media' => $imagesAvailable ? array(
				'open'   => '[[' . $wgContLang->getNsText( NS_MEDIA ) . ':',
				'close'  => ']]',
				'sample' => wfMessage( 'media_sample' )->text(),
				'tip'    => wfMessage( 'media_tip' )->text(),
				'key'    => 'M'
			) : false,
			'mw-editbutton-math' => $wgUseTeX ? array(
				'open'   => "<math>",
				'close'  => "</math>",
				'sample' => wfMessage( 'math_sample' )->text(),
				'tip'    => wfMessage( 'math_tip' )->text(),
				'key'    => 'C'
			) : false,
			'mw-editbutton-signature' => array(
				'open'   => '--~~~~',
				'close'  => '',
				'sample' => '',
				'tip'    => wfMessage( 'sig_tip' )->text(),
				'key'    => 'Y'
			),
		);

		$aBSButtonCfgs = array(
			'bs-editbutton-redirect' => array(
				'tip' => wfMessage('bs-extendededitbar-redirecttip')->plain(),
				'open' => "#REDIRECT [[",
				'close' => "]]",
				'sample' => wfMessage('bs-extendededitbar-redirectsample')->plain()
			),
			'bs-editbutton-strike' => array(
				'tip' => wfMessage('bs-extendededitbar-striketip')->plain(),
				'open' => "<s>",
				'close' => "</s>",
				'sample' => wfMessage('bs-extendededitbar-strikesample')->plain()
			),
			'bs-editbutton-linebreak' => array(
				'tip' => wfMessage('bs-extendededitbar-entertip')->plain(),
				'open' => "<br />\n",
				'close' => "",
				'sample' => ''
			),
			'bs-editbutton-sup' => array(
				'tip' => wfMessage('bs-extendededitbar-uppertip')->plain(),
				'open' => "<sup>",
				'close' => "</sup>",
				'sample' => wfMessage('bs-extendededitbar-uppersample')->plain()
			),
			'bs-editbutton-sub' => array(
				'tip' => wfMessage('bs-extendededitbar-lowertip')->plain(),
				'open' => "<sub>",
				'close' => "</sub>",
				'sample' => wfMessage('bs-extendededitbar-lowersample')->plain()
			),
			'bs-editbutton-small' => array(
				'tip' => wfMessage('bs-extendededitbar-smalltip')->plain(),
				'open' => "<small>",
				'close' => "</small>",
				'sample' => wfMessage('bs-extendededitbar-smallsample')->plain()
			),
			'bs-editbutton-comment' => array(
				'tip' => wfMessage('bs-extendededitbar-commenttip')->plain(),
				'open' => "<!-- ",
				'close' => " -->",
				'sample' => wfMessage('bs-extendededitbar-commentsample')->plain()
			),
			'bs-editbutton-gallery' => array(
				'tip' => wfMessage('bs-extendededitbar-gallerytip')->plain(),
				'open' => "\n<gallery>\n",
				'close' => "\n</gallery>",
				'sample' => $sGallery
			),
			'bs-editbutton-blockquote' => array(
				'tip' => wfMessage('bs-extendededitbar-quotetip')->plain(),
				'open' => "\n<blockquote>\n",
				'close' => "\n</blockquote>",
				'sample' => wfMessage('bs-extendededitbar-quotesample')->plain()
			),
			'bs-editbutton-table' => array(
				'tip' => wfMessage('bs-extendededitbar-tabletip')->plain(),
				'open' => "{| class=\"wikitable\"\n|-\n",
				'close' => "\n|}",
				'sample' => $sTable
			),
		);

		$aButtonCfgs = $aMWButtonCfgs + $aBSButtonCfgs;

		$aRows = array(
			array('editing' => array(), 'dialogs' => array(), 'table' => array( 10 => 'bs-editbutton-table' )), //this is reserverd for BlueSpice dialogs
			array(
				'formatting' => array(
					10 => 'mw-editbutton-bold',
					20 => 'mw-editbutton-italic',
					30 => 'bs-editbutton-strike',
					40 => 'mw-editbutton-headline',
					50 => 'bs-editbutton-linebreak',
				),
				'content' => array(
					//10 => 'mw-editbutton-link',
					//20 => 'mw-editbutton-extlink',
					30 => 'mw-editbutton-strike',
					//40 => 'mw-editbutton-image',
					//50 => 'mw-editbutton-media',
					60 => 'bs-editbutton-gallery',
				),
				'misc' => array(
					10 => 'mw-editbutton-signature',
					20 => 'bs-editbutton-redirect',
					30 => 'bs-editbutton-comment',
				)
			)
		);

		wfRunHooks( 'BSExtendedEditBarBeforeEditToolbar', array( &$aRows, &$aButtonCfgs ));

		$aContent = array();
		foreach( $aRows as $aRow ) {
			$sRow = Html::openElement( 'div', array( 'class' => 'row' ) );
			foreach( $aRow as $sGroupId => $aButtons ) {
				$sGroup = Html::openElement( 'div', array( 'class' => 'group' ) );
				ksort( $aButtons );
				foreach ( $aButtons as $iButtonSort => $sButtonId ) {
					if( !isset( $aButtonCfgs[$sButtonId] ) ) continue;
					$aButtonCfg = $aButtonCfgs[$sButtonId];
					if( !is_array( $aButtonCfg ) ) continue;

					$aDefaultAttributes = array(
						'href' => '#',
						'class' => 'bs-button-32 mw-toolbar-editbutton'
					);

					$aAttributes = array(
						'title' => $aButtonCfg['tip'],
						'id' => $sButtonId
					) + $aDefaultAttributes;

					if( isset( $aButtonCfg['open'] ) )   $aAttributes['data-open']   = $aButtonCfg['open'];
					if( isset( $aButtonCfg['close'] ) )  $aAttributes['data-close']  = $aButtonCfg['close'];
					if( isset( $aButtonCfg['sample'] ) ) $aAttributes['data-sample'] = $aButtonCfg['sample'];

					$sButton = Html::element(
						'a',
						$aAttributes,
						$aButtonCfg['tip']
					);
					$sGroup .= $sButton;
				}
				$sGroup .= Html::closeElement('div');
				$sRow.= $sGroup;
			}
			$sRow.= Html::closeElement('div');
			$aContent[] = $sRow;
		}

		//We have to keep the old toolbar (the one with ugly icons) because
		//some extensions (i.e. MsUpload) rely on it to add elements to the DOM.
		//Unfortunately VisualEditor wil set it to visible when toggled.
		//Therefore we move it out of sight using CSS positioning. Some buttons
		//May be not visible though.
		//TODO: Take contents of div#toolbar as base
		$toolbar .= Html::rawElement(
			'div',
			array( 'id' => 'bs-extendededitbar' ),
			implode( '', $aContent)
		);

		return true;
	}
}