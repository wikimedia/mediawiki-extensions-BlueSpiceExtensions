<?php

/**
 * Provides readers-users-store api for BlueSpice.
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
class BSApiReadersUsersStore extends BSApiExtJSStoreBase {

	protected function makeData( $sQuery = '' ) {
		$oTitle = Title::newFromText( $sQuery );

		if ( $oTitle == null || !$oTitle->exists() ) {
			return array();
		}

		$oDbr = wfGetDB( DB_REPLICA );
		$res = $oDbr->select(
			'bs_readers',
			'*',
			array(
				'readers_page_id' => $oTitle->getArticleID()
			),
			__METHOD__,
			array(
				'ORDER BY' => 'readers_ts DESC'
			)
		);

		$aUsers = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			foreach ( $res as $row ) {
				$oUser = User::newFromId( (int) $row->readers_user_id );
				$oTitle = Title::makeTitle( NS_USER, $oUser->getName() );
				$oUserMiniProfile = BsCore::getInstance()->getUserMiniProfile( $oUser, array() );

				$sImage = $oUserMiniProfile->getUserImageSrc();

				$oSpecialReaders = SpecialPage::getTitleFor( 'Readers', $oTitle->getPrefixedText() );

				$aTmpUser = array();
				$aTmpUser[ 'user_image' ] = $sImage;
				$aTmpUser[ 'user_name' ] = $oUser->getName();
				$aTmpUser[ 'user_page' ] = $oTitle->getLocalURL();
				//TODO: Implement good "real_name" handling
				$aTmpUser[ 'user_page_link' ] = Linker::link( $oTitle, $oTitle->getText().' ' );
				$aTmpUser[ 'user_readers' ] = $oSpecialReaders->getLocalURL();
				$aTmpUser[ 'user_readers_link' ] = Linker::link(
					$oSpecialReaders,
					'',
					array(
						'class' => 'icon-bookmarks'
					)
				);

				$aTmpUser[ 'user_ts' ] = $this->getLanguage()->userAdjust( $row->readers_ts );
				$aTmpUser[ 'user_date' ] = $this->getLanguage()->timeanddate( $row->readers_ts, true );

				$aUsers[] = (object) $aTmpUser;
			}
		}

		return $aUsers;
	}
}