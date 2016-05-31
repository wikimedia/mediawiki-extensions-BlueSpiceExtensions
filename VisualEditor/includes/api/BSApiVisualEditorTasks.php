<?php
/**
 * Provides the visualeditor api for BlueSpice.
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
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * VisualEditor Api class
 * @package BlueSpice_Extensions
 */
class ApiVisualEditorTasks extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'checkLinks',
		'saveArticle'
	);

	/**
	 * Methods that can be executed even when the wiki is in read-mode, as
	 * they do not alter the state/content of the wiki
	 * @var array
	 */
	protected $aReadTasks = array(
		'checkLinks',
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'checkLinks' => array( 'read' ),
			'saveArticle' => array( 'edit' )
		);
	}

	/**
	 * Delivers a list of booleans against a list of links, indicating whether they exist
	 * @param stdClass $oTaskData contains params
	 * @return stdClass Standard task API return
	 */
	protected function task_checkLinks( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();
		$aResult = array();

		foreach ( $oTaskData as $sTitle ) {
			$oTitle = Title::newFromText( urldecode( $sTitle ) );
			$aResult[] = $oTitle instanceof Title ? $oTitle->exists() : false;
		}

		$oReturn->payload = $aResult;
		$oReturn->success = true;
		return $oReturn;
	}

	protected function task_saveArticle( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$aResult = array(
			'edittime' => '',
			'summary' => '',
			// Set starttime to a new time to avoid edit conflicts with oneself
			'starttime' => wfTimestamp( TS_MW, time() + 2 ),
		);

		$sArticleId = $oTaskData->articleId;
		$sText = $oTaskData->text;
		$sPageName = $oTaskData->pageName;
		$sSummary = $oTaskData->summary;
		$iSection = $oTaskData->editsection;

		$sReturnEditTime = wfTimestampNow();
		if ( $sSummary == 'false' ) {
			$sSummary = '/* '.wfMessage( 'bs-visualeditor-no-summary' )->plain().' */';
		}

		$oArticle = WikiPage::newFromID( $sArticleId );
		if( is_null( $oArticle ) ) {
			$oTitle = Title::newFromText( $sPageName );
			if( is_null( $oTitle ) ) {
				$oReturn->message = wfMessage( 'badtitle' )->plain();
				return $oReturn;
			}
			$oArticle = new WikiPage( $oTitle );
		}

		if ( $iSection ) {
			$sText = $oArticle->replaceSection( $iSection, $sText );
		}

		$oSaveResult = $oArticle->doEditContent(
			ContentHandler::makeContent( $sText, $oArticle->getTitle() ),
			$sSummary
		);

		if( $oSaveResult->isGood() ) {
			$sTime = $this->getLanguage()->timeanddate( $sReturnEditTime, true );
			$aResult['edittime'] = $sReturnEditTime;
			$aResult['summary'] = $sSummary;
			$oReturn->payload = $aResult;
			$oReturn->success = true;
			$oReturn->message = wfMessage( 'bs-visualeditor-save-message', $sTime, $sSummary )->plain();
		} else {
			$oReturn->message = $oSaveResult->getMessage()->plain();
		}

		return $oReturn;
	}
}