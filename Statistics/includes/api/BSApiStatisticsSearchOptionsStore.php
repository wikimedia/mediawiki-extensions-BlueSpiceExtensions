<?php

class BSApiStatisticsSearchOptionsStore extends BSApiExtJSStoreBase {
	protected function makeData( $sQuery = '' ) {
		$aData = array();
		foreach( array( 'title', 'text', 'files', 'all' ) as $sOption ) {
			$oTemplate = new stdClass();
			$oTemplate->key = $sOption;
			$oTemplate->leaf = true;
			$oTemplate->displaytitle = $sOption;
			$aData[] = $oTemplate;
		}

		return $aData;
	}
}