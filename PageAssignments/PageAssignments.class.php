<?php

class PageAssignments extends BsExtensionMW {

	protected function initExt() {
		BsConfig::registerVar(
			'MW::PageAssignments::Permissions',
			$GLOBALS['bsgDefaultAssignedUsersAdditionalPermissions'],
			BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING | BsConfig::USE_PLUGIN_FOR_PREFS,
			'bs-pageassignments-pref-permissions',
			'multiselectex'
		);

		$this->mCore->registerPermission(
			'pageassignable',
			array( 'user' )
		);
		$this->mCore->registerPermission(
			'pageassignments',
			array( 'sysop' )
		);
	}

	/**
	 * extension.json callback
	 */
	public static function onRegistration() {
		$GLOBALS['bsgPageAssigneeTypes'] = array(
			//'specialeveryone' => 'BSAssignableEveryone', //Can be activated in LocalSettings.php if needed
			'group' => 'BSAssignableGroup',
			'user' => 'BSAssignableUser'
		);

		$GLOBALS['wgExtensionFunctions'][] = function() {
			PageAssignmentsNotificationHooks::setup();
		};

		if( !isset( $GLOBALS['bsgDefaultAssignedUsersAdditionalPermissions'] ) ) {
			$GLOBALS['bsgDefaultAssignedUsersAdditionalPermissions'] = array(
				'read',
			);
		}

		$GLOBALS["bssDefinitions"]["_PAGEASSIGN"] = array(
			"id" => "___PAGEASSIGN",
			"type" => 9,
			"show" => false,
			"msgkey" => "prefs-pageassign",
			"alias" => "prefs-pageassign",
			"label" => "Pageassign",
			"mapping" => "PageAssignments::smwDataMapping"
		);
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return BSAssignableBase[]
	 */
	public static function getAssignments( Title $oTitle ) {
		$dbr = wfGetDB( DB_REPLICA );
		$res = $dbr->select(
			'bs_pageassignments',
			'*',
			array(
				'pa_page_id' => $oTitle->getArticleID()
			),
			__METHOD__,
			array(
				'ORDER BY' => 'pa_position ASC'
			)
		);

		$aAsignees = array();
		foreach( $res as $row ) {
			$aAsignees[] = BSAssignableBase::factory(
				$row->pa_assignee_type,
				$row->pa_assignee_key
			);
		}

		return $aAsignees;
	}

	public static $aAssigneeMap = array();
	/**
	 *
	 * @param Title $oTitle
	 * @return UserArrayFromResult
	 */
	public static function resolveAssignmentsToUsers( $oTitle ) {
		$aUserIds = array();
		$aAssignees = self::getAssignments( $oTitle );
		foreach( $aAssignees as $oAssignee ) {
			$aUserIds = array_merge_recursive( $aUserIds, $oAssignee->getUserIds());
		}

		return UserArray::newFromIDs( $aUserIds );
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return int[]
	 */
	public static function resolveAssignmentsToUserIds( $oTitle ) {
		$aUsers = self::resolveAssignmentsToUsers( $oTitle );
		$aUserIds = array();
		foreach( $aUsers as $oUser ) {
			$aUserIds[] = $oUser->getID();
		}

		return $aUserIds;
	}

	/**
	 *
	 * @param type $oTitle
	 * @return array
	 */
	public static function resolveAssignmentsToUserIdsWithSource( $oTitle ) {
		$aUserIDSourceMap = array();
		$oAssignees = self::getAssignments( $oTitle );
		foreach( $oAssignees as $oAssignee ) {
			$aUserIds = $oAssignee->getUserIds();
			foreach( $aUserIds as $iUserId ) {
				if( isset( $aUserIDSourceMap[$iUserId] ) ) {
					$aUserIDSourceMap[$iUserId][] = $oAssignee;
				}
				else {
					$aUserIDSourceMap[$iUserId] = array( $oAssignee );
				}
			}
		}
		return $aUserIDSourceMap;
	}

	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPermissions = array_diff(
			User::getAllRights(),
			WikiAdmin::get( 'ExcludeRights' )
		);
		return array(
			'type' => 'multiselectex',
			'options' => array_combine( $aPermissions, $aPermissions ),
		);
	}

	/**
	 * Callback for BlueSpiceSMWConnector that adds a semantic special property
	 * @param SMW\SemanticData $oSemanticData
	 * @param WikiPage $oWikiPage
	 * @param SMW\DIProperty $oProperty
	 */
	public static function smwDataMapping( SMW\SemanticData $oSemanticData, WikiPage $oWikiPage, SMW\DIProperty $oProperty ) {
		$oTitle = $oWikiPage->getTitle();
		$aUsers = PageAssignments::resolveAssignmentsToUsers( $oTitle );

		foreach( $aUsers as $oUser ) {
			$oSemanticData->addPropertyObjectValue(
				$oProperty, SMWDIWikiPage::newFromTitle( $oUser->getUserPage() )
			);
		}
	}
}