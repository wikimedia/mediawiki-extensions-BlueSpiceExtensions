<?php
/**
 * This class serves as a backend for the responsible editors pages store.
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
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2015 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 *
 * Example request parameters of an ExtJS store
 */
class BSApiResponsibleEditorsPagesStore extends BSApiExtJSStoreBase {
	protected static $aAllowedSorters = array(
		'user_displayname',
		'page_title',
		'page_namespace',
	);
	const ASSIGNED = 'assigned';
	const NOTASSIGNED = 'notassigned';
	const BOTH = 'all';

	public function makeData( $sQuery = '' ) {
		//This is just a copy of ajaxGetArticlesByNamespaceId

		$iStart       = $this->getParameter( 'start' );
		$aSort        = $this->getSort();
		$sSort        = $aSort['property'];
		$sDirection   = $aSort['direction'];
		$iLimit       = $this->getParameter( 'limit' );
		$iNamespaceId = $this->getParameter( 'namespaceId' );
		if( !isset($iNamespaceId) ) {
			//cause PARAM_DFLT does not work here?
			$iNamespaceId = -99;
		}
		$sDisplayMode = $this->getDisplayMode( self::BOTH );

		$aActivatedNamespaces = BsConfig::get('MW::ResponsibleEditors::ActivatedNamespaces');

		$oResult = new stdClass();

		$aTables     = array( 'bs_responsible_editors', 'user', 'page' );
		$aVariables  = array( 'page_id', 'page_title', 'page_namespace' );
		$aConditions = array( 'page_namespace' => $aActivatedNamespaces );

		if ($sDisplayMode == self::ASSIGNED)
			$aConditions[] = 're_user_id IS NOT NULL ';
		else if ($sDisplayMode == self::NOTASSIGNED)
			$aConditions[] = 're_user_id IS NULL ';
		if ($iNamespaceId != -99)
			$aConditions['page_namespace'] = $iNamespaceId;

		$aOptions = array(
			'ORDER BY' => $sSort . ' ' . $sDirection,
			'LIMIT' => $iLimit,
			'OFFSET' => $iStart,
			'GROUP BY' => 'page_id'
		);
		if ($sSort == 'user_displayname') {
			$aOptions['ORDER BY'] = 'user_real_name, user_name ' . $sDirection;
		}
		$aJoinOptions = array(
			'user' => array('JOIN', 'user_id = re_user_id'),
			'page' => array('RIGHT JOIN', 'page_id = re_page_id')
		);

		$dbr = wfGetDB(DB_SLAVE);

		//TODO: Rework "total" calculation. This seems very complicated but it
		//should be as easy as excuting the main query without LIMIT/OFFSET.
		if ($sDisplayMode == self::ASSIGNED || $sDisplayMode == self::NOTASSIGNED) {
			$row = $dbr->select(
				array('page', 'bs_responsible_editors'),
				'page_id AS cnt',
				$aConditions,
				__METHOD__,
				array('GROUP BY' => 'page_id'),
				array('page' => array(
					'RIGHT JOIN', 'page_id = re_page_id'
				))
			);
			$oResult->total = $row->numRows();
		}
		if ($sDisplayMode == self::BOTH) {
			$aConditionsWithoutRePageID = $aConditions;
			unset($aConditionsWithoutRePageID[0]);
			$row = $dbr->selectRow(
				'page', 'COUNT( page_id ) AS cnt', $aConditionsWithoutRePageID
			);
			$oResult->total = $row->cnt;
		}

		$res = $dbr->select(
			$aTables,
			$aVariables,
			$aConditions,
			__METHOD__,
			$aOptions,
			$aJoinOptions
		);

		$oResult->pages = array();
		foreach ($res as $row) {
			$oTitle = Title::newFromId($row->page_id);

			$iPageId = $row->page_id;
			$sPageNsId = (!empty($row->page_namespace) )
				? $row->page_namespace
				: 0;
			$sPageTitle = $row->page_title;

			$oPage = new stdClass();
			$oPage->page_id = $iPageId;
			$oPage->page_namespace = $sPageNsId;
			$oPage->page_title = $sPageTitle;
			$oPage->page_prefixedtext = $oTitle->getPrefixedText();
			$oPage->users = array();

			$aEditorIDs = BsExtensionManager::getExtension( 'ResponsibleEditors' )
				->getResponsibleEditorIdsByArticleId($row->page_id);
			$aEditorIDs = array_unique($aEditorIDs);

			foreach ($aEditorIDs as $iEditorID) {
				$oUser = User::newFromId($iEditorID);
				if ($oUser == null) continue;

				$oPage->users[] = array(
					'user_id'            => $iEditorID,
					'user_page_link_url' => $oUser->getUserPage()->getFullUrl(),
					'user_displayname'   => BsCore::getUserDisplayName( $oUser )
				);

			}

			$oResult->pages[] = $oPage;
		}

		$this->iFinalDataSetCount = $oResult->total;
		return $oResult->pages;
	}

	public function getAllowedParams() {
		parent::getAllowedParams() + array(
			'namespaceId' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => -99,
				10 /*ApiBase::PARAM_HELP_MSG*/ => 'apihelp-bs-responsibleeditorspages-store-param-namespaceid',
			),
		);
	}

	protected function getDisplayMode( $sDefault = '' ) {
		$aFilter = $this->getParameter( 'filter' );
		foreach( $aFilter as $oFilter ) {
			if( $oFilter->type != 'list' || $oFilter->field != 'users' ) {
				continue;
			}
			if( !is_array($oFilter->value) || empty($oFilter->value) ) {
				continue;
			}
			$bAssigend = in_array( self::ASSIGNED, $oFilter->value );
			$bUnAssigend = in_array( self::NOTASSIGNED, $oFilter->value );
			if( $bAssigend && $bUnAssigend ) {
				return self::BOTH;
			}
			if( $bAssigend ) {
				return self::ASSIGNED;
			}
			if( $bUnAssigend ) {
				return self::NOTASSIGNED;
			}
		}
		return $sDefault;
	}

	public function getSort() {
		if( !$aSort = $this->getParameter( 'sort' ) ) {
			$aSort = array();
		} else {
			$aSort = (array) $aSort[0];
		}
		if( empty($aSort['property']) ) {
			$aSort['property'] = 'page_title';
		} elseif( !in_array($aSort['property'], static::$aAllowedSorters) ) {
			$aSort['property'] = 'page_title';
		}
		if( empty($aSort['direction']) || $aSort['direction'] != 'DESC' ) {
			$aSort['direction'] = 'ASC';
		}
		return $aSort;
	}

	public function execute() {
		$sQuery = $this->getParameter( 'query' );
		$aData = $this->makeData( $sQuery );
		//Ignore all the fancy stuff and do the existing query!
		//$aMetaData = $this->makeMetaData( $sQuery );
		//$FinalData = $this->postProcessData( $aData );
		$this->returnData( $aData, array() );
	}
}