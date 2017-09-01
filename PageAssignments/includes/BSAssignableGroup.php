<?php

class BSAssignableGroup extends BSAssignableBase {
	protected $sType = 'group';
	protected $sGroupName = null;

	public function __construct( $sGroupName ) {
		$this->sGroupName = $sGroupName;
		$this->sId = 'group/'.$sGroupName;
		$oMessage = wfMessage( 'group-'.$sGroupName );
		$this->sText = $oMessage->exists() ? $oMessage->plain() : $sGroupName;

		$oTitle = Title::makeTitle( NS_PROJECT, $this->sText );
		$this->sAnchor = Linker::link( $oTitle, $this->sText );

	}

	public static function getList( $sQuery, $oContext ) {
		global $wgImplicitGroups;

		$aResult = array();
		$aGroups = BsGroupHelper::getAvailableGroups();
		foreach( $aGroups as $sGroupname ) {
			if( in_array( $sGroupname, $wgImplicitGroups ) ) {
				continue;
			}

			$oAssignableGroup = new BSAssignableGroup( $sGroupname );
			$oAssignee = $oAssignableGroup->toStdClass();

			if( !empty( $sQuery ) ) {
				$sQuery = strtolower( $sQuery );
				$bIsInGroupI18N = strpos( strtolower( $oAssignee->text ), $sQuery ) !== false;
				$bIsInGroupName = strpos( strtolower( $sGroupname ), $sQuery ) !== false;

				if( !$bIsInGroupI18N && !$bIsInGroupName ) {
					continue;
				}
			}
			$aResult[] = $oAssignee;
		}

		return $aResult;
	}

	protected static $aUserIdCache = array();
	public function getUserIds() {
		if( !isset( self::$aUserIdCache[$this->sGroupName] ) ) {
			$dbr = wfGetDB( DB_REPLICA );
			$res = $dbr->select(
				'user_groups',
				'ug_user',
				array(
					'ug_group' => $this->sGroupName
				)
			);
			$aUserIds = array();
			foreach ( $res as $row ) {
				$aUserIds[] = (int)$row->ug_user;
			}
			self::$aUserIdCache[$this->sGroupName] = $aUserIds;
		}

		return self::$aUserIdCache[$this->sGroupName];


	}

	protected static function getQueryConds($oUser) {
		$aGroups = $oUser->getEffectiveGroups();
		return array(
			'pa_assignee_type' => 'group',
			'pa_assignee_key' => $aGroups
		);
	}

}