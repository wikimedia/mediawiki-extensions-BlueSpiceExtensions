<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

class BSAPIReadersUsersStore extends BSApiExtJSStoreBase {

	protected function makeData( $sQuery = '' ) {
		$oTitle = Title::newFromText( $sQuery );

		if ( $oTitle == null || !$oTitle->exists() ) {
			return array();
		}

		$oDbr = wfGetDB( DB_SLAVE );
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
				if ( BsExtensionManager::isContextActive( 'MW::SecureFileStore::Active' ) ) {
					$sImage = SecureFileStore::secureStuff( $sImage, true );
				}

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
				$aTmpUser[ 'user_ts' ] = $row->readers_ts;
				$aTmpUser[ 'user_date' ] = $this->getLanguage()->timeanddate( $row->readers_ts );

				$aUsers[] = (object) $aTmpUser;
			}
		}

		return $aUsers;
	}
}