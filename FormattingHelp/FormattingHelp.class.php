<?php
/**
 * FormattingHelp extension for BlueSpice
 *
 * Displays a help screen in the wiki edit view.
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
 * @version    1.22.0 stable
 * @version    $Id: FormattingHelp.class.php 9745 2013-06-14 12:09:29Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage FormattingHelp
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
 * - inital release
 */

/**
 * Base class for FormattingHelp extension
 * @package BlueSpice_Extensions
 * @subpackage FormattingHelp
 */
class FormattingHelp extends BsExtensionMW {

	/**
	 * Constructor of FormattingHelp class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['FormattingHelp'] = dirname( __FILE__ ) . '/FormattingHelp.i18n.php';
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'FormattingHelp',
			EXTINFO::DESCRIPTION => 'Displays a help screen in the wiki edit view.',
			EXTINFO::AUTHOR      => 'Markus Glaser',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 9745 $)',
			EXTINFO::STATUS      => 'stable',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array('bluespice' => '1.22.0')
		);
		$this->mExtensionKey = 'MW::FormattingHelp';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of Blog extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook('AlternateEdit');

		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler('FormattingHelp', $this, 'getFormattingHelp', 'edit');
		$this->registerScriptFiles(
			BsConfig::get('MW::ScriptPath').'/extensions/BlueSpiceExtensions/FormattingHelp', 'FormattingHelp', true, false, true, 'MW::FormattingHelp::Show' 
		);
		$this->registerStyleSheet(
			BsConfig::get('MW::ScriptPath').'/extensions/BlueSpiceExtensions/FormattingHelp/FormattingHelp.css', false, 'MW::FormattingHelp::Show'
		);
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Registers edit button in wiki code view. Called by MediaWiki AlternateEdit hook
	 * @return bool allow other hooked methods to be executed. Always true. 
	 */
	public function onAlternateEdit() {
		BsExtensionManager::setContext( 'MW::FormattingHelp::Show' );
		$this->mAdapter->addEditButton( 'FormattingHelp', array(
			'id'	=> 'fh_button',
			'msg'	=> wfMsg( 'bs-formattinghelp-formatting' ),
			'image' => '/extensions/BlueSpiceExtensions/FormattingHelp/images/btn_format-help.gif',
			'onclick' => "FormattingHelp.toggle();"
		));

		return true;
	}

	/**
	 * Retrieves the text for display in FormattingHelp. Called via AJAX.
	 * @param string $sOutput rendered HTML output that is to be displayed.
	 * @return bool allow other hooked methods to be executed. Always true. 
	 */
	public function getFormattingHelp( &$sOutput ) {
		if ( isset( $this->mAdapter->get( 'User' )->mOptions['language'] ) ) $lang = $this->mAdapter->get( 'User' )->mOptions['language'];

		$oTitle = Title::makeTitle( 8, 'FormattingHelp/'.$lang );
		if ( !$oTitle->exists() ) {
			$oTitle = Title::makeTitle( 8, 'FormattingHelp' );
		}

		$oFormattinghelpArticle = new Article( $oTitle );
		$sOutput = $oFormattinghelpArticle->getContent();

		if ( $sOutput ) {
			$sOutput = $this->mAdapter->parseWikiText( $sOutput );
		} else {
			$sOutput = wfMsg( 'bs-formattinghelp-help-text' );
		}

		return true;
	}

}