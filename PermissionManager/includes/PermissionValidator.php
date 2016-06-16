<?php

class PermissionValidator {

	/**
	 * check if read permission on global level is set minimum for group sysop
	 * @param array $aLockdown
	 * @param array $aGroupPermissions
	 */
	public static function beforeSavePermissionsValidateGlobalRead( &$aLockdown, &$aGroupPermissions, &$aResult ) {
		$arrGroupPermissions = ( array ) $aGroupPermissions; //important for access, because object->* wouldnt work
		$boolReadGlobal = (isset( $arrGroupPermissions[ '*' ]->read )) ? $arrGroupPermissions[ '*' ]->read : false;
		$boolReadUser = (isset( $arrGroupPermissions[ 'user' ]->read )) ? $arrGroupPermissions[ 'user' ]->read : false;
		$boolReadSysop = (isset( $arrGroupPermissions[ 'sysop' ]->read )) ? $arrGroupPermissions[ 'sysop' ]->read : false;
		$boolGlobalRead = ($boolReadGlobal || $boolReadUser || $boolReadSysop);

		if(!$boolGlobalRead) {
			$aResult = array(
					'success' => false,
					'msg' => wfMessage( 'bs-permissionmanager-error-lockout', 'read' )->plain()
			);
		}
		return true;
	}

}
