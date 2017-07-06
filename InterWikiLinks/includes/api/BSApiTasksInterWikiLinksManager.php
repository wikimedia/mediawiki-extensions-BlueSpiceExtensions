<?php
/**
 * Provides the Interwiki links manager tasks api for BlueSpice.
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
 */

/**
 * InterWikiLinksManager Api class
 * @package BlueSpice_Extensions
 */
class BSApiTasksInterWikiLinksManager extends BSApiTasksBase {

	protected $aIWLexists = array();
	/**
	 * Methods that can be called by task param
	 * @var array
	 */
	protected $aTasks = array(
		'editInterWikiLink' => [
			'examples' => [
				[
					'prefix' => 'mywiki',
					'url' => 'http://some.wiki.com/$1'
				],
				[
					'oldPrefix' => 'old_name',
					'prefix' => 'new_name',
					'url' => 'http://some.wiki.com/$1'
				]
			],
			'params' => [
				'oldPrefix' => [
					'desc' => 'Old prefix',
					'type' => 'string',
					'required' => false,
					'default' => ''
				],
				'url' => [
					'desc' => 'Url of the wiki',
					'type' => 'string',
					'required' => true
				],
				'prefix' => [
					'desc' => 'Prefix to set',
					'type' => 'string',
					'required' => true
				]
			]
		],
		'removeInterWikiLink' => [
			'examples' => [
				[
					'prefix' => 'mywiki'
				]
			],
			'params' => [
				'prefix' => [
					'desc' => 'Prefix to remove',
					'type' => 'string',
					'required' => true
				]
			]
		],
	);

	/**
	 * Returns an array of tasks and their required permissions
	 * array( 'taskname' => array('read', 'edit') )
	 * @return array
	 */
	protected function getRequiredTaskPermissions() {
		return array(
			'editInterWikiLink' => array( 'wikiadmin' ),
			'removeInterWikiLink' => array( 'wikiadmin' )
		);
	}

	/**
	 * Creates or edits an interwiki link.
	 * @return stdClass Standard tasks API return
	 */
	protected function task_editInterWikiLink( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();
		$oPrefix = null;

		$sOldPrefix = isset( $oTaskData->oldPrefix )
			? (string) $oTaskData->oldPrefix
			: ''
		;
		$sUrl = isset( $oTaskData->url )
			? (string) $oTaskData->url
			: ''
		;
		$sPrefix = isset( $oTaskData->prefix )
			? (string) $oTaskData->prefix
			: ''
		;

		//Make sure we get the db result!
		if( !empty($sPrefix) ) {
			$sKey = wfMemcKey( 'interwiki', $sPrefix );
			wfGetMainCache()->delete( $sKey );
		}
		if( !empty($sOldPrefix) ) {
			$sKey = wfMemcKey( 'interwiki', $sOldPrefix );
			wfGetMainCache()->delete( $sKey );
		}

		if( !empty($sOldPrefix) && !$this->interWikiLinkExists( $sOldPrefix ) ) {
			$oReturn->errors[] = array(
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nooldpfx' )->plain(),
			);
		} elseif( !empty($sPrefix) && $this->interWikiLinkExists( $sPrefix ) && $sPrefix !== $sOldPrefix) {
			$oReturn->errors[] = array(
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-pfxexists' )->plain(),
			);
		}
		if( !empty($oReturn->errors) ) {
			return $oReturn;
		}

		if( !$oPrefix && empty($sUrl) ) {
			$oReturn->errors[] = array(
				'id' => 'iwediturl',
				'message' => wfMessage( 'bs-interwikilinks-nourl' )->plain(),
			);
		}
		if( !$oPrefix && empty($sPrefix) ) {
			$oReturn->errors[] = array(
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nopfx' )->plain(),
			);
		}
		if( !empty($sUrl) ) {
			$oValidationResult = BsValidator::isValid(
				'Url',
				$sUrl,
				array( 'fullResponse' => true
			));
			if( $oValidationResult->getErrorCode() ) {
				$oReturn->errors[] = array(
					'id' => 'iwediturl',
					'message' => $oValidationResult->getI18N()
				);
			}
			if( strpos( $sUrl, ' ' ) ) {
				$oReturn->errors[] = array(
					'id' => 'iwediturl',
					'message' => wfMessage(
						'bs-interwikilinks-invalid-url-spc'
					)->plain()
				);
			}
		}
		if( !empty($sPrefix) ) {
			if ( strlen( $sPrefix ) > 32 ) {
				$oReturn->errors[] = array(
					'id' => 'iweditprefix',
					'message' => wfMessage(
						'bs-interwikilinks-pfxtoolong'
					)->plain(),
				);
			}

			foreach( array( ' ', '"', '&', ':') as $sInvalidChar ) {
				if( substr_count( $sPrefix, $sInvalidChar ) === 0 ) {
					continue;
				}
				//TODO (PW 19.02.2016): Return the invalid char(s)
				$oReturn->errors[] = array(
					'id' => 'iweditprefix',
					'message' => wfMessage(
						'bs-interwikilinks-invalid-pfx-spc'
					)->plain()
				);
				break;
			}
		}

		if( !empty($oReturn->errors) ) {
			return $oReturn;
		}

		$oDB = $this->getDB();
		$sTable = 'interwiki';
		$aConditions = array(
			'iw_local' => '0',
		);
		$aValues = array(
			'iw_prefix' => $sPrefix,
			'iw_url' => $sUrl,
		);

		if( empty($sOldPrefix) ) {
			$oReturn->success = $oDB->insert(
				$sTable,
				array_merge( $aConditions, $aValues ),
				__METHOD__
			);
			$oReturn->message = wfMessage(
				'bs-interwikilinks-link-created'
			)->plain();

			//Make sure to invalidate as much as possible!
			$sKey = wfMemcKey( 'interwiki', $sPrefix );
			wfGetMainCache()->delete( $sKey );
			InterWikiLinks::purgeTitles( $sPrefix );
			return $oReturn;
		}

		$aConditions['iw_prefix'] = $sOldPrefix;
		$oReturn->success = $oDB->update(
			$sTable,
			$aValues,
			$aConditions,
			__METHOD__
		);
		$oReturn->message = wfMessage(
			'bs-interwikilinks-link-edited'
		)->plain();

		//Make sure to invalidate as much as possible!
		$sKey = wfMemcKey( 'interwiki', $sPrefix );
		wfGetMainCache()->delete( $sKey );
		$sKey = wfMemcKey( 'interwiki', $sOldPrefix );
		wfGetMainCache()->delete( $sKey );
		InterWikiLinks::purgeTitles( $sOldPrefix );

		return $oReturn;
	}

	/**
	 * Creates or edits an interwiki link.
	 * @return stdClass Standard tasks API return
	 */
	protected function task_removeInterWikiLink( $oTaskData ) {
		$oReturn = $this->makeStandardReturn();
		$oPrefix = null;

		$sPrefix = isset( $oTaskData->prefix )
			? addslashes( $oTaskData->prefix )
			: ''
		;

		if( empty($sPrefix) ) {
			$oReturn->errors[] = array(
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nopfx' )->plain(),
			);
			return $oReturn;
		}

		//Make sure we get the db result!
		if( !empty($sPrefix) ) {
			$sKey = wfMemcKey( 'interwiki', $sPrefix );
			wfGetMainCache()->delete( $sKey );
		}

		if( !$this->interWikiLinkExists( $sPrefix ) ) {
			$oReturn->errors[] = array(
				'id' => 'iweditprefix',
				'message' => wfMessage( 'bs-interwikilinks-nooldpfx' )->plain(),
			);
			return $oReturn;
		}

		$oReturn->success = (bool) $this->getDB()->delete(
			'interwiki',
			array( 'iw_prefix' => $sPrefix ),
			__METHOD__
		);

		if( $oReturn->success ) {
			$oReturn->message = wfMessage(
				'bs-interwikilinks-link-deleted'
			)->plain();
		}

		//Make sure to invalidate as much as possible!
		$sKey = wfMemcKey( 'interwiki', $sPrefix );
		wfGetMainCache()->delete( $sKey );
		InterWikiLinks::purgeTitles( $sPrefix );

		return $oReturn;
	}

	protected function interWikiLinkExists( $sPrefix ) {
		if ( isset( $this->aIWLexists[$sPrefix] ) ) {
			return $this->aIWLexists[$sPrefix];
		}
		if ( version_compare( $GLOBALS['wgVersion'], '1.28c', '>' ) ) {
			$this->aIWLexists[$sPrefix] = \MediaWiki\MediaWikiServices::getInstance()->getInterwikiLookup()->isValidInterwiki( $sPrefix );
		} else {
			$row = $this->getDB()->selectRow(
				'interwiki',
				Interwiki::selectFields(),
				[ 'iw_prefix' => $sPrefix ],
				__METHOD__
			);

			if( !$row ) {
				$this->aIWLexists[$sPrefix] = false;
			} else {
				$this->aIWLexists[$sPrefix] = true;
			}
		}

		return $this->aIWLexists[$sPrefix];
	}
}