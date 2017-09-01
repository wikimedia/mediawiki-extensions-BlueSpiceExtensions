<?php

/**
 * Provides readers-data-store api for BlueSpice.
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2017 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * GroupManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiReadersDataStore extends BSApiExtJSStoreBase {

	protected function makeData( $sQuery = '' ) {

		$oDbr = wfGetDB( DB_REPLICA );

		$res = $oDbr->select(
			array( 'page', 'bs_readers' ),
			array(
				'page_title', 'readers_page_id', 'readers_user_name',
				'readers_ts'
			),
			array(
				'readers_page_id = page_id',
				'readers_user_id' => (int) $sQuery
			),
			__METHOD__,
			array(
				'ORDER BY' => 'readers_ts DESC'
			)
		);

		$aPages = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->readers_page_id );
				$oSpecialReaders = SpecialPage::getTitleFor( 'Readers', $oTitle->getPrefixedText() );

				$aTmpPage = array();
				$aTmpPage['pv_page'] = $oTitle->getLocalURL();
				$aTmpPage['pv_page_link'] = Linker::link( $oTitle );
				$aTmpPage['pv_page_title'] = $oTitle->getPrefixedText();
				$aTmpPage['pv_ts'] = $this->getLanguage()->userAdjust( $row->readers_ts );
				$aTmpPage['pv_date'] = $this->getLanguage()->timeanddate( $row->readers_ts, true );
				$aTmpPage['pv_readers_link'] = Linker::link(
					$oSpecialReaders,
					'',
					array(
						'class' => 'icon-list'
					)
				);

				$aPages[] = (object) $aTmpPage;
			}
		}
		$oDbr->freeResult( $res );

		return $aPages;
	}
}
