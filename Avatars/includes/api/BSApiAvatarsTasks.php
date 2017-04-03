<?php

class BSApiAvatarsTasks extends BSApiTasksBase {

	protected $aTasks = array(
		'uploadFile' => [
			'examples' => [],
			'params' => []
		],
		'generateAvatar' => [
			'examples' => [],
			'params' => []
		],
		'setUserImage' => [
			'examples' => [
				[
					'userImage' => 'ProfileImage.png'
				]
			],
			'params' => [
				'userImage' => [
					'desc' => 'Name of the image to set',
					'type' => 'string',
					'required' => true
				]
			]
		]
	);

	protected function getRequiredTaskPermissions() {
		return array(
			'uploadFile' => array( 'read' ),
			'generateAvatar' => array( 'read' ),
			'setUserImage' => array( 'read' )
		);
	}

	public function task_uploadFile( $oTaskData, $aParams ) {
		global $wgRequest;
		$oResponse = $this->makeStandardReturn();
		$oUser = $this->getUser();
		Avatars::unsetUserImage( $oUser );
		$oAvatars = BsExtensionManager::getExtension( 'Avatars' );
		$sAvatarFileName = Avatars::$sAvatarFilePrefix . $oUser->getId() . ".png";
		$oStatus = BsFileSystemHelper::uploadAndConvertImage(
			$this->getRequest()->getVal( 'name' ),
			'Avatars',
			$sAvatarFileName
		);
		if ( !$oStatus->isGood() ) {
			$oResponse->message = $oStatus->getMessage()->text();
			return $oResponse;
		}

		# found no way to regenerate thumbs. just delete thumb folder if it exists
		$oStatus = BsFileSystemHelper::deleteFolder( 'Avatars' . DS . 'thumb' . DS . $sAvatarFileName, true );
		if ( !$oStatus->isGood() ) {
			throw new MWException( 'FATAL: Avatar thumbs could no be deleted!' );
		}

		$oResponse->message = wfMessage( 'bs-avatars-upload-complete' )->plain();
		$oResponse->success = true;
		return $oResponse;
	}

	public function task_setUserImage( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();
		$sUserImage = $oTaskData->userImage;
		// check if string is URL or valid file
		$oFile = wfFindFile( $sUserImage );
		$bIsImage = is_object( $oFile ) && $oFile->canRender();
		if ( !wfParseUrl( $sUserImage ) && !$bIsImage ) {
			$oResponse->message = wfMessage( 'bs-avatars-set-userimage-failed' )->plain();
			return $oResponse;
		}

		$oUser = $this->getUser();
		$oUser->setOption( 'MW::UserImage', $sUserImage );
		$oUser->saveSettings();

		$oResponse->success = true;
		$oResponse->message = wfMessage( 'bs-avatars-set-userimage-saved' )->plain();
		return $oResponse;
	}

	public function task_generateAvatar( $oTaskData, $aParams ) {
		$oResponse = $this->makeStandardReturn();

		$oUser = $this->getUser();
		Avatars::unsetUserImage($oUser);
		$oAvatars = BsExtensionManager::getExtension( 'Avatars' );
		$sNewPath = $oAvatars->generateAvatar( $oUser, array(), true );

		$oResponse->success = true;
		$oResponse->message = wfMessage( 'bs-avatars-generate-complete' )->plain();
		return $oResponse;
	}

}
