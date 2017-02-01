<?php

class BSApiContextMenuTasks extends BSApiTasksBase {
	protected $aTasks = array( 'getMenuItems' );

	protected function task_getMenuItems ( $oData, $aParams ) {
		$oResult = $this->makeStandardReturn();

		$aItems = array();

		if ( !isset( $oData->title ) || empty( $oData->title ) ){
			return $this->returnItems( $oResult, $aItems );
		}

		$oTitle = Title::newFromText( $oData->title );

		if( $oTitle->getNamespace() === NS_USER ) {
			$oTargetUser = User::newFromName( $oTitle->getPrefixedDBKey() );
			if( $oTargetUser ) {
				$this->makeUserItems( $aItems, $oTitle, $oTargetUser );
			}
		}

		$this->makePageItems( $aItems, $oTitle );

		if( $oTitle->getNamespace() === NS_FILE && $oTitle->exists() ) {
			$oFile = wfFindFile( $oTitle );
			$this->makeFileItems( $aItems, $oTitle, $oFile );
			return $this->returnItems( $oResult, $aItems );
		}

		return $this->returnItems( $oResult, $aItems );
	}

	protected function makePageItems( &$aItems, $oTitle ) {
		if( $oTitle->userCan( 'edit' ) ) {
			$aItems['bs-cm-item-edit'] = array (
				'text' => wfMessage( 'bs-contextmenu-page-edit' )->plain(),
				'href' => $oTitle->getLocalUrl( array( 'action' => 'edit' ) ),
				'id'   => 'bs-cm-item-edit',
				'iconCls' => 'icon-pencil'
			);
		}

		if( !$oTitle->exists() ) {
			return;
		}

		if( $oTitle->userCan( 'read' ) ) {
			$aItems['bs-cm-item-history'] = array(
				'text' => wfMessage( 'bs-contextmenu-page-history' )->plain(),
				'href' => $oTitle->getLocalUrl( array( 'action' => 'history' ) ),
				'id'   => 'bs-cm-item-history',
				'iconCls' => 'icon-history'
			);
		}

		if( $oTitle->userCan( 'delete' ) ) {
			$aItems['bs-cm-item-delete'] = array(
				'text' => wfMessage( 'bs-contextmenu-page-delete' )->plain(),
				'href' => $oTitle->getLocalUrl( array( 'action' => 'delete' ) ),
				'id'   => 'bs-cm-item-delete',
				'iconCls' => 'icon-trash'
			);
		}

		if( $oTitle->userCan( 'move' ) ) {
			$oTitleMove = SpecialPage::getTitleFor( 'Movepage' );
			$aItems['bs-cm-item-move'] = array(
				'text' => wfMessage( 'bs-contextmenu-page-move' )->plain(),
				'href' => $oTitleMove->getLocalUrl() . '/' . $oTitle->getFullText() ,
				'id'   => 'bs-cm-item-move',
				'iconCls' => 'icon-shuffle'
			);
		}

		if( $oTitle->userCan( 'protect' ) ) {
			$aItems['bs-cm-item-protect'] = array(
				'text' => wfMessage( 'bs-contextmenu-page-protect' )->plain(),
				'href' => $oTitle->getLocalUrl( array( 'action' => 'protect' ) ),
				'id'   => 'bs-cm-item-protect',
				'iconCls' => 'icon-shield'
			);
		}
	}

	protected function makeUserItems( &$aItems, $oTitle, $oTargetUser ) {
		$oUser = $this->getUser();
		$mEMailPermissioErrors = SpecialEmailUser::getPermissionsError(
			$oUser, $oUser->getEditToken()
		);
		if( $mEMailPermissioErrors === null ) {
			$oTitleSendMail = SpecialPage::getTitleFor( 'EmailUser' );
			$aItems['bs-cm-item-usermail'] = array(
				'text' => wfMessage( 'bs-contextmenu-user-mail' )->plain(),
				'href' => $oTitleSendMail->getLocalUrl( array( 'target' => $oTargetUser->getName() ) ),
				'id'   => 'bs-cm-item-usermail',
				'iconCls' => 'icon-mail'
			);
		}

		$oTargetUserTalkPage = $oTargetUser->getTalkPage();
		if( $oTargetUserTalkPage->exists() && $oTargetUserTalkPage->userCan( 'edit' ) ) {
			$aItems['bs-cm-item-usertalk'] = array(
				'text' => wfMessage( 'bs-contextmenu-user-talk' )->plain(),
				'href' => $oTargetUserTalkPage->getLocalUrl( array( 'action' => 'edit' ) ),
				'id'   => 'bs-cm-item-usertalk',
				'iconCls' => 'icon-bubbles'
			);
		}
	}

	protected function makeFileItems( &$aItems, $oTitle, $oFile ) {
		if( $oTitle->userCan( 'read' ) ) {
			$aItems['bs-cm-item-viewmediapage'] = array (
				'text' => wfMessage( 'bs-contextmenu-media-view-page' )->plain(),
				'href' => $oTitle->getLocalURL(),
				'iconCls' => 'icon-arrow-right',
				'id' => 'bs-cm-item-viewmediapage'
			);

			$aItems['bs-cm-item-download'] = array(
				'text' => wfMessage( 'bs-contextmenu-file-download' )->plain(),
				'href' => $oFile->getURL(),
				'iconCls' => 'icon-download',
				'id' => 'bs-cm-item-download'
			);
		}

		if( $oTitle->userCan( 'reupload' ) ) {
			$oUploadSpecialPage = SpecialPage::getTitleFor( 'Upload' );
			$aItems['bs-cm-item-reupload'] = array(
				'text' => wfMessage( 'bs-contextmenu-media-reupload' )->plain(),
				'href' => $oUploadSpecialPage->getLocalUrl( array( 'wpDestFile' => $oTitle->getText() ) ),
				'iconCls' => 'icon-upload',
				'id' => 'bs-cm-item-reupload'
			);
		}
}

	protected function returnItems( &$oResult, $aItems ) {
		$oResult->success = true;
		$oResult->payload_count = count( $aItems );
		$oResult->payload = array( 'items' => $aItems );
		return $oResult;
	}
}
