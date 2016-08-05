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
}