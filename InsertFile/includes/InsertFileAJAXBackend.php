<?php

class InsertFileAJAXBackend {

	//There is an api action "licences" in mw 2.24, but it is described as
	//"Get media license dropdown HTML."
	public static function getLicenses() {
		$oLicenses = new JsonLicenses();
		return $oLicenses->getJsonOutput();
	}
}