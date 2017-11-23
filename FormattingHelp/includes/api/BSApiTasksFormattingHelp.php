<?php
/**
 * Provides the formatting help api for BlueSpice.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 */

/**
 * FormattingHelp Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksFormattingHelp extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array( 'getFormattingHelp' );

	protected function task_getFormattingHelp(  ) {
		$oReturn = $this->makeStandardReturn();

		//TODO: Make this different!
		$oReturn->payload['html'] = "<table id='bs-formattinghelp-table' class='wikitable'>
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

		$oReturn->success = true;
		return $oReturn;
	}

	protected function getAllowedParams() {
		$aParams = parent::getAllowedParams();
		$aParams['token'][ApiBase::PARAM_REQUIRED] = false;
		return $aParams;
	}

	/**
	 * FormattingHelp is readonly
	 * @return boolean false
	 */
	public function needsToken() {
		return false;
	}

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'getFormattingHelp' => array( 'read' )
		);
	}
}