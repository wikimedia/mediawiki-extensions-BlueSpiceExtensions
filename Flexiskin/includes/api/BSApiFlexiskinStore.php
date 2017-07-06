<?php

/**
 * Provides the flexiskin store api for BlueSpice.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along
 * with this program; if not, write to the Free Software Foundation, Inc.,
 * 51 Franklin Street, Fifth Floor, Boston, MA 02110-1301, USA.
 *
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Daniel Vogel
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

class BSApiFlexiskinStore extends BSApiExtJSStoreBase {
	protected function makeData( $sQuery = '' ) {

		$sActiveSkin = BsConfig::get( 'MW::Flexiskin::Active' );
		$oStatus = BsFileSystemHelper::ensureDataDirectory( "flexiskin" . DS );
		if ( !$oStatus->isGood() ){
			return array();
		}
		$aData = array ();
		if ( $handle = opendir( $oStatus->getValue() ) ) {
			while ( false !== ( $entry = readdir( $handle ) ) ) {
				if ( $entry != "." && $entry != ".." ) {
					$oStatus = BsFileSystemHelper::getFileContent( "conf.json", "flexiskin" . DS . $entry );
					if ( !$oStatus->isGood() ) {
						continue;
					}
					$aFile = FormatJson::decode( $oStatus->getValue() );
					//PW(27.11.2013) TODO: this should not be needed!
					if ( !isset( $aFile[0] ) || !is_object( $aFile[0] ) ) {
						continue;
					}
					$aData[] = ( object )array(
						'flexiskin_id' => $entry,
						'flexiskin_name' => $aFile[0]->name,
						'flexiskin_desc' => $aFile[0]->desc,
						'flexiskin_active' => $sActiveSkin == $entry ? true : false,
						'flexiskin_config' => Flexiskin::getFlexiskinConfig( $entry )
					);
				}
			}
			closedir( $handle );
		}
		return $aData;
	}
}