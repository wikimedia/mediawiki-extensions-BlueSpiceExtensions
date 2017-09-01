<?php

class PageAssignmentsWikiExplorerHooks {

	public static function onBeforePageDisplay( $out, $skin ) {
		//Attach WikiExplorer plugin if in context
		$oWikiExplorer = SpecialPage::getTitleFor( 'WikiExplorer' );
		if( !$oWikiExplorer->equals( $out->getTitle() ) ) {
			return true;
		}
		$out->addModules( 'ext.bluespice.pageassignments.wikiexplorer' );

		return true;
	}

	public static function onWikiExplorerGetFieldDefinitions(&$aFields) {
		$aFields[] = array(
			'name' => 'page_assignments',
		);
		return true;
	}

	public static function onWikiExplorerGetColumnDefinitions(&$aColumns) {
		$aColumns[] = array(
			'header' => wfMessage( 'pageassignments' )->escaped(),
			'dataIndex' => 'page_assignments',
			'id' => 'page_assignments',
			'filter' => array(
				'type' => 'string'
			),
			'width' => 200,
			'hidden' => true
		);
		return true;
	}

	public static function onWikiExplorerQueryPagesWithFilter($aFilters, &$aTables, &$aFields, &$aConditions, &$aJoinConditions) {
		$dbr = wfGetDB(DB_REPLICA);
		$sTablePrefix = $dbr->tablePrefix();

		$aTables[] = "{$sTablePrefix}bs_pageassignments AS assigned";
		$aJoinConditions["{$sTablePrefix}bs_pageassignments AS assigned"] = array(
			'LEFT OUTER JOIN',
			"{$sTablePrefix}page.page_id=assigned.pa_page_id"
		);

		$aTables[] = "{$sTablePrefix}user AS page_assignments";
		$aJoinConditions["{$sTablePrefix}user AS page_assignments"] = array(
			'LEFT OUTER JOIN',
			"assigned.pa_assignee_key=page_assignments.user_name"
		);
		$aFields[] =
			"GROUP_CONCAT("
				."IF("
					."STRCMP(page_assignments.user_real_name,''),"
					."page_assignments.user_real_name,assigned.pa_assignee_key"
				.")"
			.") AS page_assignments"
		;
		$aFields[] = "assigned.pa_assignee_key";

		if( array_key_exists( 'page_assignments', $aFilters ) ) {
			WikiExplorer::filterStringsTable(
				"CONCAT_WS("
					."',',"
					."IF("
						."STRCMP(page_assignments.user_real_name,''),"
						."page_assignments.user_real_name,assigned.pa_assignee_key"
					.")"
				.")",
				$aFilters['page_assignments'],
				$aConditions
			);
		}

		return true;
	}

	public static function onWikiExplorerBuildDataSets( &$aRows ) {
		if (!count($aRows)) {
			return true;
		}

		$aPageIds = array_keys($aRows);

		$dbr = wfGetDB( DB_REPLICA );
		$aTables = array(
			'bs_pageassignments'
		);
		$sField = "pa_page_id, pa_position, pa_assignee_type, pa_assignee_key";
		$sCondition = "pa_page_id IN (" . implode(',', $aPageIds) . ")";
		$aOptions = array(
			'ORDER BY' => 'pa_page_id, pa_position'
		);

		$oRes = $dbr->select(
			$aTables,
			$sField,
			$sCondition,
			__METHOD__,
			$aOptions
		);

		$aData = array();
		$aUserIds = array();
		$aGroups = array();
		foreach($oRes as $oRow ) {
			if( $oRow->pa_assignee_type == 'group' ) {
				$aGroups[$oRow->pa_page_id] = $oRow->pa_assignee_key;
				$aData[$oRow->pa_page_id][] =
					'<li>'.
						'<a class="bs-pa-wikiexplorer-groups" href="#">'.
							$oRow->pa_assignee_key.
						'</a>'.
					'</li>'
				;
				continue;
			}
			$oUser = User::newFromName( $oRow->pa_assignee_key );
			if( !$oUser || $oUser->isAnon() ) {
				continue;
			}
			$aUserIds[$oRow->pa_page_id][] = $oUser->getId();
			$aData[$oRow->pa_page_id][] =
				'<li>'.
					'<a class="bs-pa-wikiexplorer-users" href="#">'.
						BsUserHelper::getUserDisplayName( $oUser ).
					'</a>'.
				'</li>'
			;
		}

		foreach( $aRows as $iKey => $aRowSet ) {
			if ( array_key_exists($iKey, $aData) ) {
				$aRows[$iKey]['page_assignments'] = Html::rawElement(
					'ul',
					array(
						'data-articleId' => $iKey,
						'data-assignees' => FormatJson::encode(
							$aUserIds[$iKey]
						)
					),
					implode( '', $aData[$iKey] )
				);
			}
		}

		return true;
	}
}