<?php
/**
 * This class serves as a backend for the responsible editors possible editors
 * store.
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
class BSApiResponsibleEditorsPossibleEditorsStore extends BSApiExtJSStoreBase {
	/**
	 * @param string $sQuery Potential query provided by ExtJS component.
	 * This is some kind of preliminary filtering. Subclass has to decide if
	 * and how to process it
	 * @return array - Full list of of data objects. Filters, paging, sorting
	 * will be done by the base class
	 */
	protected function makeData( $sQuery = '' ) {
		$iArticleId = $this->getParameter( 'articleId' );
		if( empty($iArticleId) ) {
			return array();
		}

		$aData = array();
		$aPossRespEditors = ResponsibleEditors::
			getListOfPossibleResponsibleEditorsForArticle( $iArticleId );

		if( empty( $aPossRespEditors ) ) {
			return $aData;
		}

		foreach( $aPossRespEditors as $a ) {
			$aData[] = (object) $a;
		}

		return $aData;
	}

	public function getAllowedParams() {
		return parent::getAllowedParams() + array(
			'articleId' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_DFLT => 1,
				10 /*ApiBase::PARAM_HELP_MSG*/ => 'apihelp-bs-responsibleeditorsposibleeditors-store-param-articleid',
			),
		);
	}
}