<?php

class ViewExportSearch extends ViewBaseElement {
	public function __construct () {
		parent::__construct ();
	}

	public function execute ( $params = false ) {
		$sInputFields[] = Html::openElement(
			'div',
			array( 'id' => 'bs-extendedsearch-export' )
		);
		$sInputFields[] = Html::element(
			'a',
			array(
				'href' => '',
				'id' => 'bs-extendedsearch-link-export'
			),
			wfMessage( 'bs-extendedsearch-specialpage-form-export' )->plain()
		);
		$sInputFields[] = Html::closeElement( 'div' );

		return implode( "\n", $sInputFields );
	}
}