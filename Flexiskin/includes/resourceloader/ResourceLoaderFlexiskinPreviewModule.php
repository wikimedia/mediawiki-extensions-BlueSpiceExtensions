<?php

class ResourceLoaderFlexiskinPreviewModule extends ResourceLoaderFlexiskinModule {


	/**
	 *
	 * @param ResourceLoaderContext $context
	 * @return type
	 */
	public function makeFlexiSkinID( $context ) {
		return $context->getRequest()->getSessionData( 'flexiskin' );
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
