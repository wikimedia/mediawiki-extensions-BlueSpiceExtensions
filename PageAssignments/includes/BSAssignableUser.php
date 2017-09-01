<?php

class BSAssignableUser extends BSAssignableBase {
	protected $sType = 'user';
	protected $oUser = null;

	/**
	 *
	 * @param string $oUser
	 */
	public function __construct( $oUserName ) {
		$oUser = User::newFromName( $oUserName );
		$this->oUser = $oUser;
		$this->sId = 'user/'.$oUser->getName();
		$this->sText = BsUserHelper::getUserDisplayName( $oUser );
		$this->sAnchor = Linker::link( $oUser->getUserPage(), $this->sText );
	}

	/**
	 * 
	 * @param string $sQuery
	 * @param ContextSource $oContext
	 * @return type
	 */
	public static function getList($sQuery, $oContext) {
		$aResult = array();
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select( 'user', '*' );
		foreach( $res as $row ) {
			$oUser = User::newFromRow( $row );

			if( !empty( $sQuery ) ) {
				$sQuery = strtolower( $sQuery );
				$bIsInUserName = strpos( strtolower( $row->user_name ), $sQuery ) !== false;
				$sRealName = $row->user_name;
				if( !empty( $row->user_real_name ) ) {
					$sRealName = $row->user_real_name;
				}
				$bIsInUserRealName = strpos( strtolower( $sRealName ), $sQuery ) !== false;
				if( !$bIsInUserName && !$bIsInUserRealName ) {
					continue;
				}
			}

			//ATM this is always the title "API" from NS_MAIN. In future
			//releases it should be possible to have a real context in API calls
			/*if( !$oContext->getTitle()->userCan( 'pageassignable', $oUser ) ) {
				continue;
			}*/
			//$oContext->getTitle() returns Special:Badtitle since MW 1.27
			if( !$oUser->isAllowed( 'pageassignable' ) ) {
				continue;
			}
			$oAssignableUser = new BSAssignableUser( $oUser->getName() );
			$oAssignee = $oAssignableUser->toStdClass();
			$aResult[] = $oAssignee;
		}
		return $aResult;
	}

	public function getUserIds() {
		return array(
			$this->oUser->getId()
		);
	}

	protected static function getQueryConds( $oUser ) {
		return array(
			'pa_assignee_type' => 'user',
			'pa_assignee_key' => $oUser->getName()
		);
	}

}