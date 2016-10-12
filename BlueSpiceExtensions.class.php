<?php

// entry point for extension.json, load all submodules of bluespiceextensions

class BlueSpiceExtensions{
	public static function onRegistration(){

		ExtensionRegistry::getInstance()->clearQueue();

		require_once( __DIR__ . "/BlueSpiceExtensions.php" );

		//now manualy start ExtensionRegistry queue to load later added extensions
		ExtensionRegistry::getInstance()->loadFromQueue();
	}
}
