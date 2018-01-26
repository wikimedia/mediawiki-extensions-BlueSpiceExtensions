<?php

class ResourceLoaderFlexiskinPreviewModule extends ResourceLoaderFlexiskinModule {


	/**
	 *
	 * @param ResourceLoaderContext $context
	 * @return string
	 */
	public function makeFlexiSkinID( $context ) {
		//E.g. $modulename "ext.bluespice.flexiskin.skin.preview.467...543"
		$modulename =  $this->getName();
		$parts = explode( '.', $modulename );
		return array_pop( $parts );
	}

	public function makeSourceFileName() {
		return 'conf.tmp.json';
	}

	public function getModifiedTime( \ResourceLoaderContext $context ) {
		return time();
	}

	public function getGroup() {
		return 'user';
	}
}
