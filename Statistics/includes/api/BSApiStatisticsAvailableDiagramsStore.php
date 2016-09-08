<?php

class BSApiStatisticsAvailableDiagramsStore extends BSApiExtJSStoreBase {
	protected function makeData( $sQuery = '' ) {
		$aData = array();

		foreach ( Statistics::getAvailableDiagrams() as $oDiagram ) {
			$aFilterKeys = array();
			foreach( $oDiagram->getFilters() as $key => $oFilter ) $aFilterKeys[] = $key;

			$oTemplate = new stdClass();
			$oTemplate->key = $oDiagram->getDiagramKey();
			$oTemplate->displaytitle = $oDiagram->getTitle();
			$oTemplate->listable = $oDiagram->isListable();
			$oTemplate->filters = $aFilterKeys;
			$aData[] = $oTemplate;
		}

		return $aData;
	}
}