<?php
/**
 * Provides the wanted article api for BlueSpice.
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
 * For further information visit http://www.bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * WantedArticle Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksWantedArticle extends BSApiTasksBase {

	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'addWantedArticle' => [
			'examples' => [
				[
					'title' => 'Page I am suggesting'
				]
			],
			'params' => [
				'title' => [
					'desc' => '',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'getWantedArticles' => [
			'examples' => [
				[
					'count' => 12,
					'sort' => 'title',
					'order' => '',
					'type' => '',
					'title' => 'Whishlist title'

				]
			],
			'params' => [
				'count' => [
					'desc' => 'Number of articles to retrieve',
					'type' => 'integer',
					'required' => false,
					'default' => 10
				],
				'sort' => [
					'desc' => 'Sorting option - "title" or "time"',
					'type' => 'string',
					'required' => true
				],
				'order' => [
					'desc' => '',
					'type' => 'string',
					'required' => true
				],
				'type' => [
					'desc' => '',
					'type' => 'string',
					'required' => true
				],
				'title' => [
					'desc' => 'Title for the Wishlist',
					'type' => 'string',
					'required' => true
				]
			]
		],
	);

	/**
	 * Methods that can be executed even when the wiki is in read-mode, as
	 * they do not alter the state/content of the wiki
	 * @var array
	 */
	protected $aReadTasks = array(
		'getWantedArticles',
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'addWantedArticle' => array( 'wantedarticle-suggest' ),
			'getWantedArticles' => array( 'read' ),
		);
	}

	/**
	 * Handles the suggestion ajax request.
	 * A new title is entered into the list. Depending on configuration,
	 * already existing articles are deleted.
	 * @return stdClass
	 */
	protected function task_addWantedArticle( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$sTitle = isset( $oTaskData->title )
			? $oTaskData->title
			: ''
		;

		if ( empty( $sTitle ) ) {
			$oReturn->message = wfMessage(
				'bs-wantedarticle-ajax-error-no-parameter'
			)->plain();
			return $oReturn;
		}

		//Check suggestion for invalid characters
		$aFoundChars = array();
		foreach( BsCore::getForbiddenCharsInArticleTitle() as $sChar ) {
			if( false == strpos($sTitle, $sChar) ) {
				continue;
			}
			$aFoundChars[] = "'$sChar'";
		}

		$oTitle = Title::newFromText( $sTitle );
		if( !empty( $aFoundChars ) || !$oTitle ) {
			$sChars = implode( ', ', $aFoundChars );
			$oReturn->message = wfMessage(
				'bs-wantedarticle-title-invalid-chars',
				count( $aFoundChars ),
				$sChars
			)->plain();
			return $oReturn;
		}

		if ( $oTitle->exists() ) {
			$oReturn->message = wfMessage(
				'bs-wantedarticle-ajax-error-suggested-page-already-exists',
				$oTitle->getPrefixedText()
			)->plain();
			return $oReturn;
		}

		$oWantedArticle = BsExtensionManager::getExtension( 'WantedArticle' );
		$oDataSourceArticle = $oWantedArticle->getDataSourceTemplateArticle();
		$aWishList = $oWantedArticle->getTitleListFromTitle(
			$oDataSourceArticle->getTitle()
		);

		$bDeleteExisting = BsConfig::get( 'MW::WantedArticle::DeleteExisting' );

		foreach( $aWishList as $key => $a ) {
			if( $oTitle->equals( $a['title'] ) ){
				$oReturn->message = wfMessage(
					'bs-wantedarticle-ajax-error-suggested-page-already-on-list',
					$oTitle->getPrefixedText()
				)->plain();
				return $oReturn;
			}
			if( $bDeleteExisting && $a['title']->exists() === true ){
				unset( $aWishList[$key] );
				continue;
			}
		}
		array_unshift( $aWishList, array(
			'title' => $oTitle,
			'signature' => '--~~~~',
		));

		// Write new content
		$oEditStatus = $oWantedArticle->saveTitleListToTitle(
			$aWishList,
			$oDataSourceArticle->getTitle(),
			wfMessage(
				'bs-wantedarticle-edit-comment-suggestion-added',
				$oTitle->getPrefixedText()
			)->plain()
		);

		if( !$oEditStatus->isOK() ) {
			$oReturn->message = $oEditStatus->getHTML();
			return $oReturn;
		}

		$oReturn->message = wfMessage(
			'bs-wantedarticle-success-suggestion-entered',
			$oTitle->getPrefixedText()
		)->plain();
		$oReturn->success = true;

		return $oReturn;
	}

	/**
	 * Handles the get wanted articles ajax request.
	 * @return stdClass
	 */
	protected function task_getWantedArticles( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();

		$iCount = isset( $oTaskData->count )
			? $oTaskData->count
			: 10
		;
		$sSort = isset( $oTaskData->sort )
			? $oTaskData->sort
			: ''
		;
		$sOrder = isset( $oTaskData->order )
			? $oTaskData->order
			: ''
		;
		$sType = isset( $oTaskData->type )
			? $oTaskData->type
			: ''
		;
		$sTitle = isset( $oTaskData->title )
			? (string) $oTaskData->title
			: ''
		;

		//Validation
		$oValidationResult = BsValidator::isValid(
			'IntegerRange',
			$iCount,
			array(
				'fullResponse' => true,
				'lowerBoundary' => 1,
				'upperBoundary' => 30
			)
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oReturn->message = $oValidationResult->getI18N();
			return $oReturn;
		}
		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$sSort,
			array(
				'fullResponse' => true,
				'set' => array( '', 'time', 'title' )
			)
		);
		if( $oValidationResult->getErrorCode() ) {
			$oReturn->message = $oValidationResult->getI18N();
			return $oReturn;
		}
		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$sOrder,
			array(
				'fullResponse' => true,
				'set' => array( '', 'ASC', 'DESC' )
			)
		);
		if( $oValidationResult->getErrorCode() ) {
			$oReturn->message = $oValidationResult->getI18N();
			return $oReturn;
		}

		$oWantedArticle = BsExtensionManager::getExtension( 'WantedArticle' );
		//Create list
		$aWishList = $oWantedArticle->getTitleListFromTitle(
			$oWantedArticle->getDataSourceTemplateArticle()->getTitle()
		);

		switch( $sSort ) {
			case 'title':
				$aTitleList = $oWantedArticle->sortWishListByTitle(
					$aWishList
				);
				break;
			case 'time':
			default:
				$aTitleList = $oWantedArticle->getDefaultTitleList(
					$aWishList
				);
		}
		if ( $sOrder == 'ASC' ) {
			$aTitleList = array_reverse( $aTitleList );
		}

		$oWishListView = new ViewWantedArticleTag();
		$oWishListView
			->setTitle( $sTitle )
			->setType ( $sType )
			->setOrder( $sOrder )
			->setSort ( $sSort )
			->setCount( $iCount )
			->setList ( $aTitleList )
		;

		//result
		$oReturn->success = true;
		$oReturn->payload['view'] = $oWishListView->execute();
		$oReturn->payload_count = count( $aTitleList );

		return $oReturn;
	}

	public function needsToken() {
		return parent::needsToken();
	}
}