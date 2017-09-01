<?php

abstract class BSAssignableBase implements JsonSerializable {
	protected $sType = 'base';
	protected $sId = 'base/';
	protected $sText = 'BASE';
	protected $sAnchor = null;

	/**
	 *
	 * @param $sKey The key a assignable can be identified by
	 */
	public function __construct( $sKey ) {
	}

	public function jsonSerialize() {
		return array(
			'type' => $this->sType,
			'id' => $this->sId,
			'text' => $this->sText,
			'anchor' => $this->sAnchor
		);
	}

	//Needed for ExtJSStoreBase implementation
	public function toStdClass() {
		return (object) $this->jsonSerialize();
	}

	public static function getList( $sQuery, $oContext ) {
		global $bsgPageAssigneeTypes;
		$aResult = array();

		foreach( $bsgPageAssigneeTypes as $sTypeKey => $sClassName ) {
			$aSubResult = call_user_func_array( "$sClassName::getList", array( $sQuery, $oContext ) );
			foreach( $aSubResult as $oAssignable ) {
				$aResult[] = $oAssignable;
			}
		}

		return $aResult;
	}

	/**
	 *
	 * @global array $bsgPageAssigneeTypes
	 * @param User $oUser
	 * @return BSAssignableBase[]
	 */
	public final static function getForUser( $oUser ) {
		global $bsgPageAssigneeTypes;
		$aResult = array();

		foreach( $bsgPageAssigneeTypes as $sTypeKey => $sClassName ) {
			$aSubResult = call_user_func_array( "$sClassName::doGetForUser", array( $oUser ) );
			foreach( $aSubResult as $iPageId => $aAssignables ) {
				foreach( $aAssignables as $oAsignee ) {
					self::addOrAppend( $aResult, $iPageId, $oAsignee );
				}
			}
		}

		return $aResult;
	}

	/**
	 *
	 * @param User $oUser
	 * @return array in form of [ <page_id> => [ <AssignableBase>, <AssignableBase>, ... ] ]
	 */
	protected static function doGetForUser( $oUser ) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'bs_pageassignments',
			'*',
			static::getQueryConds( $oUser )
		);

		$aResult = [];
		foreach( $res as $row ) {
			$oAssignee = self::factory( $row->pa_assignee_type, $row->pa_assignee_key );
			static::addOrAppend( $aResult, $row->pa_page_id, $oAssignee );
		}
		return $aResult;
	}

	/**
	 * Just a little helper function to stay DRY
	 * @param array $aResult
	 * @param mixed $mKey
	 * @param mixed $mValue
	 */
	protected static function addOrAppend( &$aResult, $mKey, $mValue ) {
		if( isset( $aResult[$mKey] ) ) {
			$aResult[$mKey][] = $mValue;
		}
		else {
			$aResult[$mKey] = [ $mValue ];
		}
	}

	public function __toString() {
		return $this->sText;
	}

	/**
	 * @return array - of user ids
	 */
	public function getUserIds() {
		return array();
	}

	public function getId() {
		return $this->sId;
	}

	public function getText() {
		return $this->sText;
	}

	/**
	 *
	 * @param string $sType
	 * @param string $sKey
	 * @return BSAssignableBase
	 */
	public static final function factory( $sType, $sKey ) {
		global $bsgPageAssigneeTypes;
		if( isset( $bsgPageAssigneeTypes[$sType])) {
			return new $bsgPageAssigneeTypes[$sType]( $sKey );
		}
		else {
			throw new MWException( "Assignee type '$sType' not registered" );
		}
	}

	/**
	 * Gets the class related query condition to get the assignments from
	 * database for a specific user
	 * @param type $oUser
	 * @throws MWException, when not overwritten
	 */
	protected static function getQueryConds( $oUser ) {
		throw new MWException( "MIssing method: ".__CLASS__.":".__METHOD__ );
	}
}