<?php

/**
 * NamespaceManager extension for BlueSpice
 *
 * Administration interface for adding, editing and deleting namespaces
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
 * @author     Sebastian Ulbricht
 * @author     Stefan Widmann <widmann@hallowelt.biz>
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @version    2.23.2
 * @package    Bluespice_Extensions
 * @subpackage NamespaceManager
 * @copyright  Copyright (C) 2016 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Class for namespace management assistent
 * @package BlueSpice_Extensions
 * @subpackage WikiAdmin
 */
class NamespaceManager extends BsExtensionMW {

	private $_aDefaultNamespaceSettings = array(
		'content' => false,
		'subpages' => true,
		'searched' => false
	);

	public static $aSortConditions = array(
		'sort' => '',
		'dir' => ''
	);

	/**
	 * Constructor of NamespaceManager
	 */
	public function __construct() {
		wfProfileIn( 'BS::NamespaceManager::__construct' );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME => 'NamespaceManager',
			EXTINFO::DESCRIPTION => 'bs-namespacemanager-desc',
			EXTINFO::AUTHOR => 'Sebastian Ulbricht, Stefan Widmann, Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'https://help.bluespice.com/index.php/Namespacemanager',
		);

		WikiAdmin::registerModule( 'NamespaceManager', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/resources/images/bs-btn_namespaces_v1.png',
			'level' => 'wikiadmin',
			'message' => 'bs-namespacemanager-label'
			)
		);
		wfProfileOut( 'BS::NamespaceManager::__construct' );
	}

	/**
	 * Initialization of NamespaceManager extension
	 */
	public function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		BsConfig::registerVar( 'MW::NamespaceManager::NsOffset', 2999, BsConfig::TYPE_INT, BsConfig::LEVEL_PRIVATE );

		$this->setHook( 'NamespaceManager::editNamespace', 'onEditNamespace', true );
		$this->setHook( 'NamespaceManager::writeNamespaceConfiguration', 'onWriteNamespaceConfiguration', true );

		//CR, RBV: This is suposed to return all constants! Not just system NS.
		//At the moment the implementation relies on an hardcoded mapping,
		//which is bad. We need to change this and make it more generic!
		$GLOBALS['bsSystemNamespaces'] = BsNamespaceHelper::getMwNamespaceConstants();

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	* Add the sql file to database by executing the update.php
	* @global type $wgDBtype
	* @global array $wgExtNewTables
	* @param DatabaseUpdater $du
	* @return boolean
	*/
	public static function onLoadExtensionSchemaUpdates( $updater ) {
		global $wgExtPGNewFields, $wgDBtype;
		$dir = __DIR__.DS.'db'.DS;

		if ( $wgDBtype == 'oracle' ) {
			$updater->addExtensionTable(
				'bs_ns_bak_page',
				$dir . 'bs_namespacemanager_backup_page.sql'
			);
			$updater->addExtensionTable(
				'bs_ns_bak_revision',
				$dir . 'bs_namespacemanager_backup_revision.sql'
			);
			$updater->addExtensionTable(
				'bs_ns_bak_text',
				$dir . 'bs_namespacemanager_backup_text.sql'
			);
		} else {
			$updater->addExtensionTable(
				'bs_namespacemanager_backup_page',
				$dir . 'bs_namespacemanager_backup_page.sql'
			);
			$updater->addExtensionTable(
				'bs_namespacemanager_backup_revision',
				$dir . 'bs_namespacemanager_backup_revision.sql'
			);
			$updater->addExtensionTable(
				'bs_namespacemanager_backup_text',
				$dir . 'bs_namespacemanager_backup_text.sql'
			);
		}

		if ( $wgDBtype == 'postgres' ) {
			$wgExtPGNewFields[] = array(
				'bs_namespacemanager_backup_page',
				'page_content_model',
				$dir . 'bs_namespacemanager_backup_page.patch.pg.sql'
			);
			$wgExtPGNewFields[] = array(
				'bs_namespacemanager_backup_revision',
				'rev_sha1',
				$dir . 'bs_namespacemanager_backup_revision.patch.rev_sha1.pg.sql'
			);
			$wgExtPGNewFields[] = array(
				'bs_namespacemanager_backup_revision',
				'rev_content_model',
				$dir . 'bs_namespacemanager_backup_revision.patch2.pg.sql'
			);
		} elseif ( $wgDBtype != 'sqlite' ) { /* Do not apply patches to sqlite */
			$updater->addExtensionField(
				'bs_namespacemanager_backup_page',
				'page_content_model',
				$dir . 'bs_namespacemanager_backup_page.patch.sql'
			);
			$updater->addExtensionField(
				'bs_namespacemanager_backup_revision',
				'rev_sha1',
				$dir . 'bs_namespacemanager_backup_revision.patch.rev_sha1.sql'
			);
			$updater->addExtensionField(
				'bs_namespacemanager_backup_revision',
				'rev_content_model',
				$dir . 'bs_namespacemanager_backup_revision.patch2.sql'
			);
		}

		return true;
	}

	/**
	 * Provides the form content for the WikiAdmin special page.
	 * @return string the form content
	 */
	public function getForm() {
		$this->getOutput()->addModules( 'ext.bluespice.namespaceManager' );
		$aMetaFields = array(
			array(
				'name' => 'id',
				'type' => 'int',
				'sortable' => true,
				'filter' => array(
					'type' => 'numeric'
				),
				'label' => wfMessage( 'bs-namespacemanager-label-id' )->plain()
			),
			array(
				'name' => 'name',
				'type' => 'string',
				'sortable' => true,
				'filter' => array(
					'type' => 'string'
				),
				'label' => wfMessage( 'bs-namespacemanager-label-namespaces' )->plain()
			),
			array(
				'name' => 'pageCount',
				'type' => 'int',
				'sortable' => true,
				'hidden' => true,
				'filter' => array(
					'type' => 'numeric'
				),
				'label' => wfMessage( 'bs-namespacemanager-label-pagecount' )->plain()
			),
			array(
				'name' => 'editable',
				'type' => 'boolean',
				'label' => wfMessage( 'bs-namespacemanager-label-editable' )->plain(),
				'hidden' => true,
				'sortable' => true,
				'filter' => array(
					'type' => 'bool'
				),
			),
			array(
				'name' => 'subpages',
				'type' => 'boolean',
				'label' => wfMessage( 'bs-namespacemanager-label-subpages' )->plain(),
				'sortable' => true,
				'filter' => array(
					'type' => 'bool'
				),
			),
			array(
				'name' => 'searched',
				'type' => 'boolean',
				'label' => wfMessage( 'bs-namespacemanager-label-searchable' )->plain(),
				'sortable' => true,
				'filter' => array(
					'type' => 'bool'
				),
			),
			array(
				'name' => 'content',
				'type' => 'boolean',
				'label' => wfMessage( 'bs-namespacemanager-label-content' )->plain(),
				'sortable' => true,
				'filter' => array(
					'type' => 'bool',
					'value' => true,
					#'active' => true
				),
			)/*,
			array(
				'name' => 'isSystemNS',
				'hidden' => true,
				'type' => 'boolean',
				'label' => wfMessage( 'bs-namespacemanager-label-system' )->plain(),
				'filter' => array(
					'type' => 'bool'
				)
			), array(
				'name' => 'isTalkNS',
				'hidden' => true,
				'type' => 'boolean',
				'label' => wfMessage( 'bs-namespacemanager-label-talk' )->plain(),
				'filter' => array(
					'type' => 'bool',
				)
			)*/
		);

		Hooks::run( 'NamespaceManager::getMetaFields', array( &$aMetaFields ) );
		$this->getOutput()->addJsConfigVars('bsNamespaceManagerMetaFields', $aMetaFields);

		return '<div id="bs-namespacemanager-grid"></div>';
	}

	/**
	 * Hook-Handler for NamespaceManager::editNamespace
	 * @return boolean Always true to kepp hook alive
	 */
	public function onEditNamespace( &$aNamespaceDefinition, &$iNs, $aAdditionalSettings, $bUseInternalDefaults ) {
		if ( !$bUseInternalDefaults ) {
			if ( empty( $aNamespaceDefinition[$iNs] ) ) {
				$aNamespaceDefinition[$iNs] = array();
			}
			$aNamespaceDefinition[$iNs] += array(
				'content'  => $aAdditionalSettings['content'],
				'subpages' => $aAdditionalSettings['subpages'],
				'searched' => $aAdditionalSettings['searched'] );
		} else {
			$aNamespaceDefinition[$iNs] += $this->_aDefaultNamespaceSettings;
		}
		return true;
	}

	public function onWriteNamespaceConfiguration( &$sSaveContent, $sConstName, $aDefinition ) {
		if ( isset( $aDefinition[ 'content' ] ) && $aDefinition['content'] === true ) {
			$sSaveContent .= "\$GLOBALS['wgContentNamespaces'][] = {$sConstName};\n";
		}
		if ( isset( $aDefinition[ 'subpages' ] ) && $aDefinition['subpages'] === true ) {
			$sSaveContent .= "\$GLOBALS['wgNamespacesWithSubpages'][{$sConstName}] = true;\n";
		}
		if ( isset( $aDefinition[ 'searched' ] ) && $aDefinition['searched'] === true ) {
			$sSaveContent .= "\$GLOBALS['wgNamespacesToBeSearchedDefault'][{$sConstName}] = true;\n";
		}
		return true;
	}

	/**
	 * Get all namespaces, which are created with the NamespaceManager.
	 * @param boolean $bFullDetails should the complete configuration of the namespaces be loaded
	 * @return array the namespace data
	 */
	public static function getUserNamespaces( $bFullDetails = false ) {
		global $wgExtraNamespaces, $wgNamespacesWithSubpages,
			$wgContentNamespaces, $wgNamespacesToBeSearchedDefault,
			$bsgConfigFiles;

		if ( !file_exists( $bsgConfigFiles['NamespaceManager'] ) ) {
			return array();
		}
		$sConfigContent = file_get_contents( $bsgConfigFiles['NamespaceManager'] );
		$aUserNamespaces = array();
		if ( preg_match_all( '%define\("NS_([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)", ([0-9]*)\)%s', $sConfigContent, $aMatches, PREG_PATTERN_ORDER ) ) {
			$aUserNamespaces = $aMatches[ 2 ];
		}
		if ( $bFullDetails ) {
			$aTmp = array();
			foreach ( $aUserNamespaces as $iNS ) {
				$aTmp[$iNS] = array(
					'content' => in_array( $iNS, $wgContentNamespaces ),
					'subpages' => ( isset( $wgNamespacesWithSubpages[$iNS] ) && $wgNamespacesWithSubpages[$iNS] ),
					'searched' => ( isset( $wgNamespacesToBeSearchedDefault[$iNS] ) && $wgNamespacesToBeSearchedDefault[$iNS] )
				);
				if ( $iNS >= 100 ) {
					$aTmp[$iNS]['name'] = $wgExtraNamespaces[$iNS];
				}
			}

			$aUserNamespaces = $aTmp;
		}

		return $aUserNamespaces;
	}

	/**
	 * Saves a given namespace configuration to bluespice-core/config/nm-settings.php
	 * @param array $aUserNamespaceDefinition the namespace configuration
	 */
	public static function setUserNamespaces( $aUserNamespaceDefinition ) {
		global $bsSystemNamespaces, $bsgConfigFiles;

		$oNamespaceManager = BsExtensionManager::getExtension( 'NamespaceManager' );
		Hooks::run( 'BSNamespaceManagerBeforeSetUsernamespaces', array( $oNamespaceManager, &$bsSystemNamespaces ) );

		$sSaveContent = "<?php\n\n";
		foreach ( $aUserNamespaceDefinition as $iNS => $aDefinition ) {
			$bIsSystemNs = false;
			if ( isset( $bsSystemNamespaces[$iNS] ) ) {
				$bIsSystemNs = true;
			}

			if ( $aDefinition ) {
				if ( isset( $aDefinition['alias'] ) && $aDefinition['alias'] ) {
					$sDefName = strtoupper( $aDefinition['alias'] );
				} else {
					if( $iNS >= 100 ) {
						$sDefName = strtoupper( $aDefinition['name'] );
					} else {
						$sDefName = $bsSystemNamespaces[$iNS];
					}
				}

				if ( $bIsSystemNs ) {
					$sConstName = $bsSystemNamespaces[$iNS];
				} else {
					$sConstName = 'NS_' . $sDefName;
				}
				$sSaveContent .= "// START Namespace {$sConstName}\n";
				$sSaveContent .= "if( !defined( \"{$sConstName}\" ) ) define(\"{$sConstName}\", {$iNS});\n";
				if ( $iNS >= 100 ) {
					$sSaveContent .= "\$GLOBALS['wgExtraNamespaces'][{$sConstName}] = '" . $aDefinition['name'] . "';\n";
				}

				Hooks::run( 'NamespaceManager::writeNamespaceConfiguration', array( &$sSaveContent, $sConstName, $aDefinition ) );
				if ( !$bIsSystemNs && isset( $aDefinition['alias'] ) && $aDefinition['alias'] ) {
					$sSaveContent .= "\$GLOBALS['wgNamespaceAliases']['{$aDefinition['alias']}'] = {$sConstName};\n";
				}
				$sSaveContent .= "// END Namespace {$sDefName}\n\n";
			}
		}

		$res = file_put_contents( $bsgConfigFiles['NamespaceManager'], $sSaveContent );

		if ( $res ) {
			return array(
				'success' => true,
				'message' => wfMessage( 'bs-namespacemanager-ns-config-saved' )->plain()
			);
		}
		return array(
			'success' => false,
			'message' => wfMessage( 'bs-namespacemanager-error-ns-config-not-saved' , $bsgConfigFiles['NamespaceManager'] )->plain()
		);
	}

}
