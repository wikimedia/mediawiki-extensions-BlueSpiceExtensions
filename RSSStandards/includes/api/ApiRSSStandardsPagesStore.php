<?php

/**
 * Provides rss-standards extjs store api for BlueSpice.
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
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

class ApiRSSStandardsPagesStore extends BSApiExtJSStoreBase {

	protected function makeData( $sQuery = '' ) {

		$aPageRSS = array();

		$dbr = wfGetDB( DB_SLAVE );

		$res = $dbr->select(
			'page',
			'*',
			array(),
			__METHOD__,
			array( 'ORDER BY' => 'page_title' )
		);

		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName   = $this->getUser()->getName();
		$sUserToken  = $this->getUser()->getToken();

		while ( $row = $res->fetchObject() ) {
			$oTitle = Title::newFromRow($row);
			$aPageRSS[] = (object) array(
				'page' => $oTitle->getPrefixedText(),
				'url'  => $oSpecialRSS->getLinkUrl(
					array(
						'Page' => 'followPage',
						'p'    => $row->page_title,
						'ns'   => $row->page_namespace,
						'u'    => $sUserName,
						'h'    => $sUserToken
					)
				)
			);
		}

		return $aPageRSS;
	}
}