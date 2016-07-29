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
		  array( 'bs_readers' ),
		  array( 'readers_user_id', 'MAX(readers_ts) as readers_ts' ),
		  array( 'readers_page_id' => $oTitle->getArticleID() )
		);

		$aUsers = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			$aParams = array();
			$oLanguage = RequestContext::getMain()->getLanguage();
			foreach ( $res as $row ) {
				$oUser = User::newFromId( ( int ) $row->readers_user_id );
				$oTitle = Title::makeTitle( NS_USER, $oUser->getName() );
				$oUserMiniProfile = BsCore::getInstance()->getUserMiniProfile( $oUser, $aParams );

				$sImage = $oUserMiniProfile->getUserImageSrc();
				if ( BsExtensionManager::isContextActive( 'MW::SecureFileStore::Active' ) ) {
					$sImage = SecureFileStore::secureStuff( $sImage, true );
				}

				$aTmpUser = array();
				$aTmpUser[ 'user_image' ] = $sImage;
				$aTmpUser[ 'user_name' ] = $oUser->getName();
				$aTmpUser[ 'user_page' ] = $oTitle->getLocalURL();
				$aTmpUser[ 'user_readers' ] = SpecialPage::getTitleFor( 'Readers', $oTitle->getPrefixedText() )->getLocalURL();
				$aTmpUser[ 'user_ts' ] = $row->readers_ts;
				$aTmpUser[ 'user_date' ] = $oLanguage->timeanddate( $row->readers_ts );

				$aUsers[] = (object) $aTmpUser;
			}
		}

		return $aUsers;
	}
}