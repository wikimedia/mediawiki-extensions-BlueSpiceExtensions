<?php
/**
 * This class serves as a backend for the data store of the InsertMagic
 * extension
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
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 *
 * Example request parameters of an ExtJS store
 */
class BSApiInsertMagicDataStore extends BSApiExtJSStoreBase {
	/**
	 * @param string $sQuery Potential query provided by ExtJS component.
	 * This is some kind of preliminary filtering. Subclass has to decide if
	 * and how to process it
	 * @return array - Full list of of data objects. Filters, paging, sorting
	 * will be done by the base class
	 */
	protected function makeData( $sQuery = '' ) {
		//Utilize?
		//MagicWord::getDoubleUnderscoreArray()
		//MagicWord::getVariableIDs()
		//MagicWord::getSubstIDs()

		$oResponse = (object) array(
			'result' => array(),
		);

		foreach ( InsertMagic::getTags() as $sTag => $aData ) {
			foreach ( $aData as $key => $value ) {
				$oDescriptor = new stdClass();
				$oDescriptor->id = $value;
				$oDescriptor->type = 'tag';
				$oDescriptor->name = $sTag;
				$oDescriptor->desc = wfMessage( $key )->text();
				$oDescriptor->code = $value;
				$oDescriptor->examples = array();
				$oDescriptor->helplink = '';
				$oDescriptor->previewable = true;
				$oResponse->result[] = $oDescriptor;
			}
		}

		foreach ( InsertMagic::getQuickAccess() as $sTag => $aData ) {
			foreach ( $aData as $key => $value ) {
				$oDescriptor = new stdClass();
				$oDescriptor->id = $value;
				$oDescriptor->type = 'quickaccess';
				$oDescriptor->name = $sTag;
				$oDescriptor->desc = wfMessage( $key )->text();
				$oDescriptor->code = $value;
				$oDescriptor->previewable = true;
				$oResponse->result[] = $oDescriptor;
			}
		}

		$aMagicWords = InsertMagic::getMagicWords();
		foreach ( $aMagicWords['variables'] as $aVariable ) {
			foreach ( $aVariable as $key => $value ) {
				$oDescriptor = new stdClass();
				$oDescriptor->id = $value;
				$oDescriptor->type = 'variable';
				$oDescriptor->name = substr( $value, 2, -2 );
				$oDescriptor->desc = wfMessage( $key )->text();
				$oDescriptor->code = $value;
				$oDescriptor->examples = array();
				$oDescriptor->helplink = '';
				$oDescriptor->previewable = true;
				$oResponse->result[] = $oDescriptor;
			}
		}

		foreach ( $aMagicWords['behavior-switches'] as $aSwitch ) {
			foreach ( $aSwitch as $key => $value ) {
				$oDescriptor = new stdClass();
				$oDescriptor->id = $value;
				$oDescriptor->type = 'switch';
				$oDescriptor->name = substr( $value, 2, -2 );
				$oDescriptor->desc = wfMessage( $key )->text();
				$oDescriptor->code = $value;
				$oDescriptor->examples = array();
				$oDescriptor->helplink = '';
				$oDescriptor->previewable = false;
				$oResponse->result[] = $oDescriptor;
			}
		}

		//Other extensions may inject their tags or MagicWords
		Hooks::run('BSInsertMagicAjaxGetData', array( &$oResponse, 'tags' ) );
		Hooks::run('BSInsertMagicAjaxGetData', array( &$oResponse, 'quickaccess' ) );
		Hooks::run('BSInsertMagicAjaxGetData', array( &$oResponse, 'variables' ) ); //For compatibility
		Hooks::run('BSInsertMagicAjaxGetData', array( &$oResponse, 'switches' ) ); //For compatibility

		//Check if all members of $oResponse->result are of type stdClass()
		foreach( $oResponse->result as $iKey => &$res ) {
			if( is_array ( $res ) ) {
				$res = (object) $res;
			}
		}

		return $oResponse->result;
	}
}