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
 * @version    2.22.0 stable
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

		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'FormattingHelp',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-formattinghelp-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array('bluespice' => '2.22.0')
		);
		$this->mExtensionKey = 'MW::FormattingHelp';

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of Blog extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook('BSExtendedEditBarBeforeEditToolbar');
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function onBSExtendedEditBarBeforeEditToolbar( &$aRows, &$aButtonCfgs ) {
		$this->getOutput()->addModuleStyles('ext.bluespice.formattinghelp.styles');
		$this->getOutput()->addModules('ext.bluespice.formattinghelp');

		$aRows[0]['editing'][20] = 'bs-editbutton-formattinghelp';

		$aButtonCfgs['bs-editbutton-formattinghelp'] = array(
			'tip' => wfMessage( 'bs-formattinghelp-formatting' )->plain()
		);
		return true;
	}

	/**
	 * Retrieves the text for display in FormattingHelp. Called via AJAX.
	 * @param string $sOutput rendered HTML output that is to be displayed.
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public static function getFormattingHelp() {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;

		$sOutput = "<table id='bs-formattinghelp-table' class='wikitable'>
			<thead>
				<tr>
					<th></th>
					<th>".wfMessage( 'bs-formattinghelp-help-syntax' )->escaped()."</th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td width='20%'><strong>".wfMessage( 'bs-formattinghelp-help-bold' )->escaped()."</strong></td>
					<td><nowiki>'''".wfMessage( 'bs-formattinghelp-help-example-text' )->escaped()."'''</nowiki></td>
				</tr>
				<tr>
					<td width='20%'><strong>".wfMessage( 'bs-formattinghelp-help-italic' )->escaped()."</strong></td>
					<td><nowiki>''".wfMessage( 'bs-formattinghelp-help-example-text' )->escaped()."''</nowiki></td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-whitespace' )->escaped()."</strong></td>
					<td>&amp;nbsp;</td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-nowiki' )->escaped()."</strong></td>
					<td>&lt;nowiki&gt;'''".wfMessage( 'bs-formattinghelp-help-example-text' )->escaped()."'''&lt;/nowiki&gt;</td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-color' )->escaped()."</strong></td>
					<td>&lt;font color=\"#DDBB65\"&gt;".wfMessage( 'bs-formattinghelp-help-example-text' )->escaped()."&lt;/font&gt;</td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-headline' )->escaped()."</strong></td>
					<td>= ".wfMessage( 'bs-formattinghelp-help-headline' )->escaped()." 1 =<br/>
						== ".wfMessage( 'bs-formattinghelp-help-headline' )->escaped()." 2 ==<br/>
						=== ".wfMessage( 'bs-formattinghelp-help-headline' )->escaped()." 3 ===<br/>
						==== ".wfMessage( 'bs-formattinghelp-help-headline' )->escaped()." 4 ====<br/>
						===== ".wfMessage( 'bs-formattinghelp-help-headline' )->escaped()." 5 =====<br/>
						====== ".wfMessage( 'bs-formattinghelp-help-headline' )->escaped()." 6 ======</td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-linebreak' )->escaped()."</strong></td>
					<td>&lt;br /&gt;</td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-ul' )->escaped()."</strong></td>
					<td>* ".wfMessage( 'bs-formattinghelp-help-listitem' )->escaped()."<br/>
						** ".wfMessage( 'bs-formattinghelp-help-subitem' )->escaped()."<br/>
						* ".wfMessage( 'bs-formattinghelp-help-listitem' )->escaped()."</td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-numberedlist' )->escaped()."</strong></td>
					<td># ".wfMessage( 'bs-formattinghelp-help-listitem' )->escaped()."<br/>
						## ".wfMessage( 'bs-formattinghelp-help-subitem' )->escaped()."<br/>
						# ".wfMessage( 'bs-formattinghelp-help-listitem' )->escaped()."</td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-link' )->escaped()."</strong></td>
					<td><nowiki>[[".wfMessage( 'bs-formattinghelp-help-example-text' )->escaped()."]]</nowiki></td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-link-alt' )->escaped()."</strong></td>
					<td><nowiki>[[".wfMessage( 'bs-ns' )->escaped().":".wfMessage( 'bs-formattinghelp-help-example-text' )->escaped().
						"|".wfMessage( 'bs-formattinghelp-help-caption' )->escaped()."]]</nowiki></td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-extlink' )->escaped()."</strong></td>
					<td><nowiki>[http://www.hallowelt.biz http://www.hallowelt.biz]</nowiki></td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-hr' )->escaped()."</strong></td>
					<td>----</td>
				</tr>
				<tr>
					<td><strong>".wfMessage( 'bs-formattinghelp-help-template' )->escaped()."</strong></td>
					<td><nowiki>{{".wfMessage( 'bs-formattinghelp-help-templatename' )->escaped()."}}</nowiki></td>
				</tr>
			</tbody>
			</table>";

		return $sOutput;
	}
}
