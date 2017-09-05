<?php

namespace BlueSpice\Flexiskin\Data;

class AvailableConfigs {

	/**
	 * ATTENTION: This code was copied from BSApiFlexiskinStore and probably
	 * needs some refactoring
	 * @return array
	 */
	public function read() {
		$activeSkinId = \BsConfig::get( 'MW::Flexiskin::Active' );
		$status = \BsFileSystemHelper::ensureDataDirectory( "flexiskin" . DS );
		if ( !$status->isGood() ){
			return [];
		}
		$data = [];
		if ( $handle = opendir( $status->getValue() ) ) {
			while ( false !== ( $skinId = readdir( $handle ) ) ) {
				if ( $skinId != "." && $skinId != ".." ) {
					$status = \BsFileSystemHelper::getFileContent( "conf.json", "flexiskin" . DS . $skinId );
					if ( !$status->isGood() ) {
						continue;
					}
					$file = \FormatJson::decode( $status->getValue() );
					//PW(27.11.2013) TODO: this should not be needed!
					if ( !isset( $file[0] ) || !is_object( $file[0] ) ) {
						continue;
					}
					$data[] = ( object )array(
						'flexiskin_id' => $skinId,
						'flexiskin_name' => $file[0]->name,
						'flexiskin_desc' => $file[0]->desc,
						'flexiskin_active' => $activeSkinId == $skinId ? true : false,
						'flexiskin_config' => \Flexiskin::getFlexiskinConfig( $skinId )
					);
				}
			}
			closedir( $handle );
		}
		return $data;
	}
}