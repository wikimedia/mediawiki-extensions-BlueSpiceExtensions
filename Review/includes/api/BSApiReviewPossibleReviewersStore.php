<?php
/**
 * Provides the possible reviewers user store api for BlueSpice.
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
 * For further information visit http://bluespice.com
 *
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Api base class for possible reviewers user store in BlueSpice
 * @package BlueSpice_Extensions
 */
class BSApiReviewPossibleReviewersStore extends BSApiUserStore {
	const PERMISSION = 'workflowview';
	protected $oTitle = null;

	public function getAllowedParams() {
		return parent::getAllowedParams() + array(
			'articleId' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => true,
				ApiBase::PARAM_DFLT => 0,
				ApiBase::PARAM_HELP_MSG =>
					'apihelp-bs-reviewpossiblereviewers-store-param-articleid',
			),
		);
	}

	protected function makeData($sQuery = '') {
		$iArticleId = $this->getParameter( 'articleId' );
		if( !$oTitle = Title::newFromId( $iArticleId ) ) {
			return array();
		}
		//TODO: Context could be used
		$this->oTitle = $oTitle;
		return parent::makeData($sQuery);
	}

	protected function makeResultRow( $row, $aGroups = array() ) {
		$aResult = parent::makeResultRow( $row, $aGroups );
		$oUser = User::newFromRow( $row );

		return $this->oTitle->userCan( self::PERMISSION, $oUser )
			? $aResult
			: false
		;
	}
}
