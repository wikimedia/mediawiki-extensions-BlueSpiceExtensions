<?php

class BSApiPageAssignableStore extends BSApiExtJSStoreBase {

	protected function makeData( $sQuery = '' ) {
		return BSAssignableBase::getList( $sQuery, $this->getContext() );
	}
}