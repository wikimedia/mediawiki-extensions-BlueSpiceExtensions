<?php
/**
 * This is the GroupManager class.
 *
 * The GroupManager offers an easy way to manage the usergroups of the wiki.
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
 * This file is part of BlueSpice for MediaWiki
 * For further information visit http://www.blue-spice.org
 *
 * @author     Sebastian Ulbricht <sebastian.ulbricht@dragon-design.hk>
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @version    2.23.1
 * @package    Bluespice_Extensions
 * @subpackage GroupManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * the GroupManager class
 * @package BlueSpice_Extensions
 * @subpackage GroupManager
 */
class GroupManager extends BsExtensionMW {

	/**
	 * constructor for GroupManager class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
                WikiAdmin::registerModule('GroupManager', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_gruppe_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-groupmanager-label'
			)
		);
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->mCore->registerPermission( 'groupmanager-viewspecialpage', array( 'sysop' ), array( 'type' => 'global' ) );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * offers the formular for the group manager
	 * @return string the formular code
	 */
	public function getForm() {
		$this->getOutput()->addModules( 'ext.bluespice.groupManager' );
		BsExtensionManager::setContext( 'MW::GroupManagerShow' );
		$sForm = '<div id="bs-groupmanager-grid"></div>';
		return $sForm;
	}

	/**
	 * saves all groupspecific data to a config file
	 * @return array the json answer
	 */
	public static function saveData() {
		global $wgAdditionalGroups, $bsgConfigFiles;

		$sSaveContent = "<?php\n\$GLOBALS['wgAdditionalGroups'] = array();\n\n";
		foreach ( $wgAdditionalGroups as $sGroup => $mValue ) {
			$aInvalidChars = array();
			$sGroup = trim( $sGroup );
			if ( substr_count( $sGroup, '\'' ) > 0 ) $aInvalidChars[] = '\'';
			if ( substr_count( $sGroup, '"' ) > 0 ) $aInvalidChars[] = '"';
			if ( !empty( $aInvalidChars ) ) {
				return array(
					'success' => false,
					'message' => wfMessage( 'bs-groupmanager-invalid-name' )
						->numParams( count( $aInvalidChars ) )
						->params( implode( ',', $aInvalidChars ) )
						->text()
				);
			} elseif ( preg_match( "/^[0-9]+$/", $sGroup ) ) {
				return array(
					'success' => false,
					'message' => wfMessage( 'bs-groupmanager-invalid-name-numeric' )->plain()
				);
			} elseif ( strlen( $sGroup ) > 255 ) {
				return array(
					'success' => false,
					'message' => wfMessage( 'bs-groupmanager-invalid-name-length' )->plain()
				);
			} else {
				if ( $mValue !== false ) {
					$sSaveContent .= "\$GLOBALS['wgAdditionalGroups']['{$sGroup}'] = array();\n";
					self::checkI18N( $sGroup );
				} else {
					self::checkI18N( $sGroup, $mValue );
				}
			}
		}

		$sSaveContent .= "\n\$GLOBALS['wgGroupPermissions'] = array_merge(\$GLOBALS['wgGroupPermissions'], \$GLOBALS['wgAdditionalGroups']);";

		$res = file_put_contents( $bsgConfigFiles['GroupManager'], $sSaveContent );
		if ( $res ) {
			return array(
				'success' => true,
				'message' => wfMessage( 'bs-groupmanager-grpadded' )->plain()
			);
		} else {
			return array(
				'success' => false,
				// TODO SU (04.07.11 11:44): i18n
				'message' => 'Not able to create or write file "' . $bsgConfigFiles['GroupManager'] . '".'
			);
		}
	}

	public static function checkI18N( $sGroup, $bValue = true ) {
		$oTitle   = Title::newFromText( 'group-' . $sGroup, NS_MEDIAWIKI );
		$oArticle = null;

		if ( $bValue === false ) {
			if ( $oTitle->exists() ) {
				$oArticle = new Article( $oTitle );
				$oArticle->doDeleteArticle( 'Group does no more exist' );
			}
		} else {
			if ( !$oTitle->exists() ) {
				$oArticle = new Article( $oTitle );
				$oArticle->doEdit( $sGroup, '', EDIT_NEW );
			}
		}
	}

}
