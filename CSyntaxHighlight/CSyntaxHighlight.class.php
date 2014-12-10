<?php
/**
 * CSyntaxHighlight extension for BlueSpice
 *
 * Adds customizable syntax highlighting functionality to BlueSpice. Based on SyntaxHighlighter by Alex Gorbatchev (http://alexgorbatchev.com/SyntaxHighlighter/).
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
 * @version    2.22.0

 * @package    BlueSpice_Extensions
 * @subpackage CSyntaxHighlight
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 *
 * FIRST CHANGES
 * v1.0.0
 * - Using SyntaxHighligter from Alex Gorbatchev in version 3.0.83
 * FIRST CHANGES
*/

/**
 * Base class for CSyntaxHighlight extension
 * @package BlueSpice_Extensions
 * @subpackage CSyntaxHighlight
 */
class CSyntaxHighlight extends BsExtensionMW {

	protected $aBrushes = array(
				'AppleScript' => array( 'applescript' ),
				'AS3'         => array( 'actionscript3', 'as3' ),
				'Bash'        => array( 'bash', 'shell' ),
				'ColdFusion'  => array( 'coldfusion', 'cf' ),
				'Cpp'         => array( 'cpp', 'c' ),
				'CSharp'      => array( 'c#', 'c-sharp', 'csharp' ),
				'Css'         => array( 'css' ),
				'Delphi'      => array( 'delphi', 'pascal' ),
				'Diff'        => array( 'diff', 'patch', 'pas' ),
				'Erlang'      => array( 'erl', 'erlang' ),
				'Groovy'      => array( 'groovy' ),
				'Java'        => array( 'java' ),
				'JavaFX'      => array( 'jfx', 'javafx' ),
				'JScript'     => array( 'js', 'jscript', 'javascript' ),
				'Lilypond'    => array( 'ly', 'lilypond' ),
				'Perl'        => array( 'perl', 'pl' ),
				'Php'         => array( 'php' ),
				'Plain'       => array( 'text', 'plain' ),
				'Python'      => array( 'py', 'python' ),
				'Ruby'        => array( 'ruby', 'rails', 'ror', 'rb' ),
				'Sass'        => array( 'sass', 'scss' ),
				'Scala'       => array( 'scala' ),
				'Sql'         => array( 'sql' ),
				'Vb'          => array( 'vb', 'vbnet' ),
				'Xml'         => array( 'xml', 'xhtml', 'xslt', 'html' )
			);

	protected $aThemes = array(
		'Default',
		'Django',
		'Eclipse',
		'Emacs',
		'FadeToGrey',
		'MDUltra',
		'Midnight',
		'RDark',
	);

	/**
	 * Contructor of the CSyntaxHighlight class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER;
		$this->mInfo = array(
			EXTINFO::NAME        => 'CSyntaxHighlight',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-csyntaxhighlight-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
										'bluespice' => '2.22.0'
										)
		);
		$this->mExtensionKey = 'MW::CSyntaxHighlight';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of CSyntaxHighlight extension
	 */
	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'OutputPageBeforeHTML' );
		$this->setHook( 'SkinAfterBottomScripts' );

		// TODO RBV (12.04.11 15:47): Provide all config possibilities of SyntaxHighlighter...
		BsConfig::registerVar( 'MW::CSyntaxHighlight::Theme',     'Default', BsConfig::LEVEL_USER|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-csyntaxhighlight-pref-theme', 'select' );
		BsConfig::registerVar( 'MW::CSyntaxHighlight::Gutter',    true,      BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-csyntaxhighlight-pref-gutter', 'toggle' );
		BsConfig::registerVar( 'MW::CSyntaxHighlight::AutoLinks', true,      BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-csyntaxhighlight-pref-autolinks', 'toggle' );
		BsConfig::registerVar( 'MW::CSyntaxHighlight::Toolbar',   false,     BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-csyntaxhighlight-pref-toolbar', 'toggle' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Called by Preferences and UserPreferences
	 * @param string $sAdapterName Name of the adapter. Probably MW.
	 * @param BsConfig $oVariable The variable that is to be specified.
	 * @return array Option array of specifications.
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aOptions = array();
		foreach( $this->aThemes as $sTheme ) {
			$aOptions[$sTheme] = $sTheme;
		}

		return array(
				'options' => $aOptions,
		);
	}

	public function onOutputPageBeforeHTML( $oParserOutput, $sText ) {
		global $wgScriptPath;
		// TODO RBV (13.07.11 15:44): Better recognition...
		if ( strpos( $sText, '<pre class="brush:' ) === false ) return true;

		BsExtensionManager::setContext( 'MW::CSyntaxHighlight' );

		$sBrushScriptPath = $wgScriptPath.'/extensions/BlueSpiceExtensions/CSyntaxHighlight/resources';

		$sTheme = BsConfig::get('MW::CSyntaxHighlight::Theme');
		$sStyleBlock = '<link rel="stylesheet" href="' . $sBrushScriptPath .
				'/shTheme'. $sTheme .'.css" />';
		$sStyleBlock .= '<link rel="stylesheet" href="' . $sBrushScriptPath .
				'/shCore.css" />';

		$this->getOutput()->addHeadItem( 'BrushTheme', $sStyleBlock );
		return true;
	}

	public function onSkinAfterBottomScripts( $oSkin, &$bottomScriptText ) {
		global $wgScriptPath;
		$sBrushScriptPath = $wgScriptPath.'/extensions/BlueSpiceExtensions/CSyntaxHighlight/resources';

		$aAutoloaderParams = array();
		foreach ( $this->aBrushes as $sBrushName => $aAliases ) {
			//HINT: http://alexgorbatchev.com/SyntaxHighlighter/manual/api/autoloader.html
			$aAutoloaderParams[] = '["'.implode( '","', $aAliases ).'","'.$sBrushScriptPath.'/shBrush'.$sBrushName.'.js" ]';
		}

		$aScriptBlock = array();
		$aScriptBlock[] = '<script type="text/javascript" src="' . $sBrushScriptPath . '/shCore.js"></script>';
		$aScriptBlock[] = '<script type="text/javascript" src="' . $sBrushScriptPath . '/shAutoloader.js"></script>';
		$aScriptBlock[] = '<script type="text/javascript">';
		$aScriptBlock[] = 'mw.loader.using("ext.bluespice", function(){';
		$aScriptBlock[] = 'SyntaxHighlighter.autoloader( ';
		$aScriptBlock[] = implode( ",\n", $aAutoloaderParams );
		$aScriptBlock[] = ');';
		$aScriptBlock[] = 'SyntaxHighlighter.defaults["toolbar"] = bsCSyntaxHighlightToolbar;';
		$aScriptBlock[] = 'SyntaxHighlighter.defaults["auto-links"] = bsCSyntaxHighlightAutoLinks;';
		$aScriptBlock[] = 'SyntaxHighlighter.defaults["gutter"] = bsCSyntaxHighlightGutter;';
		$aScriptBlock[] = 'SyntaxHighlighter.all();';
		$aScriptBlock[] = '});';
		$aScriptBlock[] = '</script>';

		$bottomScriptText .= implode( "\n", $aScriptBlock );
		return true;
	}

}