<?php

/**
 * This file is part of BlueSpice for MediaWiki.
 *
 * Administration interface for adding, editing and deleting namespaces
 * @copyright Copyright (c) 2013, HalloWelt! Medienwerkstatt GmbH, All rights reserved.
 * @author Sebastian Ulbricht
 * @author Stefan Widmann <widmann@hallowelt.biz>
 * @version 2.22.0
 */

// Last review: MRG (01.07.11 01:35)
// NamespaceName-Format
// A valid namespace name has to start with a letter from a-z in any case or with an underscore
// followed by numbers, the letters a-z and underscore in any case.
// A valid namespace name has a length between 4 and 100 chars.

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

	public $aSortHelper = array(
		'negative' => 0,
		'positive' => 0
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
			EXTINFO::DESCRIPTION => wfMessage( 'bs-namespacemanager-desc' )->escaped(),
			EXTINFO::AUTHOR => 'Sebastian Ulbricht, Stefan Widmann',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array(
				'bluespice' => '2.22.0',
				'WikiAdmin' => '2.22.0',
				'Preferences' => '2.22.0'
			)
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

		BsConfig::registerVar( 'MW::NamespaceManager::NsOffset', 2999, BsConfig::TYPE_INT,  BsConfig::LEVEL_PRIVATE );

		$this->setHook( 'NamespaceManager::getMetaFields', 'onGetMetaFields', true );
		$this->setHook( 'NamespaceManager::getNamespaceData', 'onGetNamespaceData', true );
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
	public static function getSchemaUpdates( $updater ) {
		global $wgExtPGNewFields, $wgDBtype;
		$dir = __DIR__.DS.'resources'.DS;

		if ( $wgDBtype == 'oracle' ) {
			$updater->addExtensionTable(
				'bs_ns_bak_page',
				__DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_page.sql'
			);
			$updater->addExtensionTable(
				'bs_ns_bak_revision',
				__DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_revision.sql'
			);
			$updater->addExtensionTable(
				'bs_ns_bak_text',
				__DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_text.sql'
			);
		} else {
			$updater->addExtensionTable(
				'bs_namespacemanager_backup_page',
				__DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_page.sql'
			);
			$updater->addExtensionTable(
				'bs_namespacemanager_backup_revision',
				__DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_revision.sql'
			);
			$updater->addExtensionTable(
				'bs_namespacemanager_backup_text',
				__DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_text.sql'
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
		} else {
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
	 * returns if NS is empty or not
	 * @param type $iNamespaceId
	 * @return boolean
	 */
	public function isNamespaceEmpty( $iNamespaceId ) {
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
				'page',
				'page_namespace',
				array( 'page_namespace' => $iNamespaceId )
		);
		if ( $res ) {
			return !$res->numRows();
		}
		return false;
	}

	/**
	 * Provides the form content for the WikiAdmin special page.
	 * @return string the form content
	 */
	public function getForm() {
		$this->getOutput()->addModules( 'ext.bluespice.namespaceManager' );
		BsExtensionManager::setContext( 'MW::NamespaceManagerShow' );
		$aMetaFields = array(
			array(
				'name' => 'id',
				'type' => 'int',
				'sortable' => true,
				'label' => wfMessage( 'bs-namespacemanager-label-id' )->plain()
			),
			array(
				'name' => 'name',
				'sortable' => true,
				'label' => wfMessage( 'bs-namespacemanager-label-namespaces' )->plain()
			)
		);

		wfRunHooks( 'NamespaceManager::getMetaFields', array( &$aMetaFields ) );
		$this->getOutput()->addJsConfigVars('bsNamespaceManagerMetaFields', $aMetaFields);

		return '<div id="bs-namespacemanager-grid"></div>';
	}

	/**
	 * Calculate the data for the NamespaceManager store and put them to the ajax output.
	 * @param string $output the ajax output (have to be JSON)
	 */
	public static function getData() {
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;
		global $wgContLang;

		$aResults = array();
		$aNamespaces = $wgContLang->getNamespaces();
		foreach ( $aNamespaces as $iNs => $sNamespace ) {
			if ( $sNamespace === '' ) {
				$sNamespace = BsNamespaceHelper::getNamespaceName( $iNs );
			}
			if ( $iNs === -2 || $iNs === -1 ) continue;
			$aResults[] = array(
				'id' => $iNs,
				'name' => $sNamespace
			);
		}

		wfRunHooks( 'NamespaceManager::getNamespaceData', array( &$aResults ) );

		$oRequest = RequestContext::getMain()->getRequest();
		$iLimit = $oRequest->getInt( 'limit', 25 );
		$iStart = $oRequest->getInt( 'start', 0 );
		$sSort = $oRequest->getVal( 'sort', '[{"property":"id","direction":"DESC"}]' );

		self::$aSortConditions = FormatJson::decode($sSort);
		self::$aSortConditions = self::$aSortConditions[0];
		usort( $aResults, 'NamespaceManager::namespaceManagerRemoteSort' );

		$aLimitedResults = array();
		$iResultCount = count( $aResults );
		$iMax = ( ( $iStart + $iLimit ) > $iResultCount ) ? $iResultCount : ( $iStart + $iLimit );
		for ( $i = $iStart; $i < $iMax; $i++ ) {
			$aLimitedResults[] = $aResults[$i];
		}

		$aReturn = array(
			'totalCount' => $iResultCount,
			'success' => true,
			'results' => $aLimitedResults
		);
		return FormatJson::encode( $aReturn );
	}

	public static function namespaceManagerRemoteSort( $value1, $value2 ) {
		$leftVal = $value1;
		$rightVal = $value2;
		if ( self::$aSortConditions->direction === 'ASC' ) {
			$leftVal = $value2;
			$rightVal = $value1;
		}
		switch( self::$aSortConditions->property ) {
			case 'name':
				if ( strcasecmp( $leftVal['name'], $rightVal['name'] ) === 0 ) {
					return 0;
				} else {
					return strcasecmp( $leftVal['name'], $rightVal['name'] );
				}
			break;
			case 'id':
				if ( $leftVal['id'] === $rightVal['id'] ) {
					return 0;
				} else {
					return ( (int)$leftVal['id'] < (int)$rightVal['id'] ) ? -1 :  1;
				}
			break;
			default:
				wfRunHooks( 'NamespaceManager::namespaceManagerRemoteSort', array( $value1, $value2, self::$aSortConditions ) );
		}

	}

	public function onGetMetaFields( &$aMetaFields ) {
		$aMetaFields[] = array(
			'name' => 'editable',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-namespacemanager-label-editable' )->plain()
		);
		$aMetaFields[] = array(
			'name' => 'subpages',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-namespacemanager-label-subpages' )->plain()
		);
		$aMetaFields[] = array(
			'name' => 'searchable',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-namespacemanager-label-searchable' )->plain()
		);
		$aMetaFields[] = array(
			'name' => 'content',
			'type' => 'boolean',
			'label' => wfMessage( 'bs-namespacemanager-label-content' )->plain()
		);

		return true;
	}

	public function onGetNamespaceData( &$aResults ) {
		global $wgNamespacesWithSubpages, $wgContentNamespaces,
				$wgNamespacesToBeSearchedDefault, $bsSystemNamespaces;
		wfRunHooks( 'BSNamespaceManagerBeforeSetUsernamespaces', array( $this, &$bsSystemNamespaces ) );
		$aUserNamespaces = self::getUserNamespaces();

		$iResults = count( $aResults );
		for ( $i = 0; $i < $iResults; $i++ ) {

			$iNs = $aResults[$i]['id'];
			$aResults[$i][ 'editable' ] = ( isset( $bsSystemNamespaces[$iNs] ) )
				? false
				: in_array( $iNs, $aUserNamespaces );
			$aResults[$i]['content'] = in_array( $iNs, $wgContentNamespaces );
			$aResults[$i]['searchable'] = (isset( $wgNamespacesToBeSearchedDefault[$iNs] ) && $wgNamespacesToBeSearchedDefault[$iNs]);
			$aResults[$i]['subpages'] = ( isset( $wgNamespacesWithSubpages[$iNs] ) && $wgNamespacesWithSubpages[$iNs] );
			if ( in_array( $iNs, $aUserNamespaces ) ) {
				$aResults[$i]['empty'] = $this->isNamespaceEmpty( $iNs );
			}
		}
		return true;
	}

	/**
	 * Hook-Handler for NamespaceManager::editNamespace
	 * @return boolean Always true to kepp hook alive
	 */
	public function onEditNamespace( &$aNamespaceDefinition, &$iNs, $aAdditionalSettings, $bUseInternalDefaults ) {
		if ( !$bUseInternalDefaults ) {
			if ( empty( $aNamespaceDefinition[$iNs] ) ) $aNamespaceDefinition[$iNs] = array();
			$aNamespaceDefinition[$iNs] += array(
				'content'  => $aAdditionalSettings['content'],
				'subpages' => $aAdditionalSettings['subpages'],
				'searched' => $aAdditionalSettings['searchable'] );
		} else {
			$aNamespaceDefinition[$iNs] += $this->_aDefaultNamespaceSettings;
		}
		return true;
	}

	public function onWriteNamespaceConfiguration( &$sSaveContent, $sConstName, $aDefinition ) {
		if ( isset( $aDefinition[ 'content' ] ) && $aDefinition['content'] == 'true' ) {
			$sSaveContent .= "\$wgContentNamespaces[] = {$sConstName};\n";
		}
		if ( isset( $aDefinition[ 'subpages' ] ) && $aDefinition['subpages'] == 'true' ) {
			$sSaveContent .= "\$wgNamespacesWithSubpages[{$sConstName}] = true;\n";
		}
		if ( isset( $aDefinition[ 'searched' ] ) && $aDefinition['searched'] == 'true' ) {
			$sSaveContent .= "\$wgNamespacesToBeSearchedDefault[{$sConstName}] = true;\n";
		}
		return true;
	}

	/**
	 * Build the configuration for a new namespace and give it to the save method.
	 */
	public static function addNamespace( $sNamespace, $aAdditionalSettings ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
			) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		global $wgContLang;
		$aNamespaces = $wgContLang->getNamespaces();
		$aAdditionalSettings = FormatJson::decode($aAdditionalSettings, true);
		$aUserNamespaces = self::getUserNamespaces( true );
		end( $aNamespaces );
		$iNS = key( $aNamespaces ) + 1;
		reset( $aNamespaces );

		if ( $iNS < BsConfig::get( 'MW::NamespaceManager::NsOffset' ) ) {
			$iNS = BsConfig::get( 'MW::NamespaceManager::NsOffset' ) + 1;
		}

		$sResult = true;
		foreach ( $aNamespaces as $sKey => $sNamespaceFromArray ) {
			if ( strtolower( $sNamespaceFromArray ) == strtolower( $sNamespace ) ) {
				$sResult = false;
				break;
			}
		}
		if ( $sResult ) {
			if ( strlen( $sNamespace ) < 2 ) {
				return FormatJson::encode( array(
					'success' => false,
					'message' => wfMessage( 'bs-namespacemanager-ns-length' )->plain()
					) );
			// TODO MRG (06.11.13 11:17): Unicodefähigkeit?
			} else if ( !preg_match( '%^[a-zA-Z_\\x80-\\xFF][a-zA-Z0-9_\\x80-\\xFF]{1,99}$%i', $sNamespace ) ) {
				return FormatJson::encode( array(
					'success' => false,
					'message' => wfMessage( 'bs-namespacemanager-wrong-name' )->plain()
					) );
			} else {
				$aUserNamespaces[$iNS] = array( 'name' => $sNamespace );

				$bUseInternalDefaults = false;
				wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces, &$iNS, $aAdditionalSettings, $bUseInternalDefaults ) );

				++$iNS;
				$aUserNamespaces[ ( $iNS ) ] = array(
					'name' => $sNamespace . '_' . $wgContLang->getNsText( NS_TALK ),
					// TODO SU (04.07.11 12:13): Subpages in diskussionsnamespaces? Würd ich nicht machen. Diese drei Werte hätte ich eigentlich gerne in einer Einstellung, die im Konstruktor festgelegt wird. Gerne zunächst PRIVATE
					'alias' => $sNamespace . '_talk'
				);
				$bUseInternalDefaults = true;
				wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces, &$iNS, $aAdditionalSettings, $bUseInternalDefaults ) );

				return FormatJson::encode( self::setUserNamespaces( $aUserNamespaces ) );
			}
		} else {
			// TODO SU (04.07.11 12:13): Aus Gründen der Lesbarkeit würde ich
			// das direkt in die obige foreach-Schleife packen und den else-
			// Zweig hier weglassen.
			return FormatJson::encode( array(
					'success' => false,
					'message' => wfMessage( 'bs-namespacemanager-ns-exists' )->plain()
				) );
		}
	}

	/**
	 * Change the configuration of a given namespace and give it to the save method.
	 */
	public static function editNamespace( $iNS, $sNamespace, $aAdditionalSettings ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
			) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		global $bsSystemNamespaces, $wgContLang;
		$iNS = (int)$iNS;
		$aAdditionalSettings = FormatJson::decode($aAdditionalSettings, true);
		$oNamespaceManager = BsExtensionManager::getExtension( 'NamespaceManager' );
		wfRunHooks( 'BSNamespaceManagerBeforeSetUsernamespaces', array( $oNamespaceManager, &$bsSystemNamespaces ) );
		$aUserNamespaces = self::getUserNamespaces( true );

		if ( $iNS !== NS_MAIN && !$iNS ) {
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-namespacemanager-invalid-id' )->plain()
			) );
		}
		if ( strlen( $sNamespace ) < 2 ) {
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-namespacemanager-ns-length' )->plain()
			) );
		}
		if ( $iNS !== NS_MAIN && $iNS !== NS_PROJECT && $iNS !== NS_PROJECT_TALK && !preg_match( '%^[a-zA-Z_\\x80-\\xFF][a-zA-Z0-9_\\x80-\\xFF]{1,99}$%', $sNamespace ) ) {
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-namespacemanager-wrong-name' )->plain()
			) );
		}

		if ( !isset( $bsSystemNamespaces[($iNS)] ) && strstr( $sNamespace, '_' . $wgContLang->getNsText( NS_TALK ) ) ) {
				$aUserNamespaces[$iNS] = array(
					'name' => $aUserNamespaces[$iNS]['name'],
					'alias' => str_replace( '_' . $wgContLang->getNsText( NS_TALK ), '_talk', $sNamespace ),
				);
			wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces, &$iNS, $aAdditionalSettings, false ) );
		} else {
			$aUserNamespaces[$iNS] = array(
				'name' => $sNamespace,
			);

			if ( !isset( $bsSystemNamespaces[($iNS)] ) ) {
				$aUserNamespaces[($iNS + 1)]['name'] = $sNamespace . '_' . $wgContLang->getNsText( NS_TALK );
				$aUserNamespaces[($iNS + 1)]['alias'] = $sNamespace . '_talk';
			}
			wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces, &$iNS, $aAdditionalSettings, false ) );
		}

		$aResult = self::setUserNamespaces( $aUserNamespaces );
		$aResult['message'] = wfMessage( 'bs-namespacemanager-nsedited' )->plain();

		return FormatJson::encode( $aResult );
	}

	/**
	 * Delete a given namespace.
	 */
	public function deleteNamespace( $iNS, $iDoArticle ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		$iNS = BsCore::sanitize( $iNS, '', BsPARAMTYPE::INT );

		if ( !$iNS ) {
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-namespacemanager-invalid-id' )->plain()
				) );
		}

		global $wgContLang;
		$aUserNamespaces = self::getUserNamespaces( true );
		$aNamespacesToRemove = array( array( $iNS, 0 ) );
		$sNamespace = $aUserNamespaces[$iNS][ 'name' ];

		if ( !strstr( $sNamespace, '_'.$wgContLang->getNsText( NS_TALK ) ) ) {
			if ( isset( $aUserNamespaces[ ($iNS + 1) ] ) && strstr( $aUserNamespaces[ ($iNS + 1) ][ 'name' ], '_'.$wgContLang->getNsText( NS_TALK ) ) ) {
				$aNamespacesToRemove[] = array( ($iNS + 1), 1 );
				$sNamespace = $aUserNamespaces[ ($iNS + 1) ][ 'name' ];
			}
		}

		$bErrors = false;
		if ( empty( $iDoArticle ) ) $iDoArticle = 0;

		switch ( $iDoArticle ) {
			case 0:
				foreach ( $aNamespacesToRemove as $aNamespace ) {
					$iNs = $aNamespace[0];
					if ( !NamespaceNuker::removeAllNamespacePages( $iNs, $aUserNamespaces[$iNs]['name'] ) ) {
						$bErrors = true;
					} else {
						$aUserNamespaces[ $aNamespace[ 0 ] ] = false;
					}
				}
				break;
			case 1:
				foreach ( $aNamespacesToRemove as $aNamespace ) {
					$iNs = $aNamespace[0];
					if ( !NamespaceNuker::moveAllPagesIntoMain( $iNs, $aUserNamespaces[$iNs]['name'] ) ) {
						$bErrors = true;
					} else {
						$aUserNamespaces[ $aNamespace[ 0 ] ] = false;
					}
				}
				break;
			case 2:
			default:
				foreach ( $aNamespacesToRemove as $aNamespace ) {
					$iNs = $aNamespace[0];
					if ( !NamespaceNuker::moveAllPagesIntoMain( $iNs, $aUserNamespaces[$iNs]['name'], true ) ) {
						$bErrors = true;
					} else {
						$aUserNamespaces[ $aNamespace[ 0 ] ] = false;
					}
				}
				break;
		}

		if ( !$bErrors ) {
			$aResult = self::setUserNamespaces( $aUserNamespaces );
			$aResult['message'] = wfMessage( 'bs-namespacemanager-nsremoved' )->plain();
			return FormatJson::encode( $aResult );
		} else {
			return FormatJson::encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-namespacemanager-error_on_remove_namespace' )->plain()
				) );
		}
	}

	/**
	 * Get all namespaces, which are created with the NamespaceManager.
	 * @param boolean $bFullDetails should the complete configuration of the namespaces be loaded
	 * @return array the namespace data
	 */
	protected static function getUserNamespaces( $bFullDetails = false ) {
		global $wgExtraNamespaces, $wgNamespacesWithSubpages,
				$wgContentNamespaces, $wgNamespacesToBeSearchedDefault;

		if ( !file_exists( BSROOTDIR . DS . 'config' . DS . 'nm-settings.php' ) ) {
			return array();
		}
		$sConfigContent = file_get_contents( BSROOTDIR . DS . 'config' . DS . 'nm-settings.php' );
		$aUserNamespaces = array();
		/*if ( preg_match_all(
			'%// START Namespace ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*).*define\("NS_\1", ([0-9]*)\).*?// END Namespace \1%s',
			$sConfigContent,
			$aMatches,
			PREG_PATTERN_ORDER ) ) {
		 */
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
	protected static function setUserNamespaces( $aUserNamespaceDefinition ) {
		global $wgNamespacesWithSubpages, $wgContentNamespaces,
			$wgNamespacesToBeSearchedDefault, $bsSystemNamespaces;

		$oNamespaceManager = BsExtensionManager::getExtension( 'NamespaceManager' );
		wfRunHooks( 'BSNamespaceManagerBeforeSetUsernamespaces', array( $oNamespaceManager, &$bsSystemNamespaces ) );

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
					$sSaveContent .= "// START Namespace {$sConstName}\n";
					$sSaveContent .= "if( !defined( \"{$sConstName}\" ) ) define(\"{$sConstName}\", {$iNS});\n";
					if ( $iNS >= 100 ) {
						$sSaveContent .= "\$wgExtraNamespaces[{$sConstName}] = '" . $aDefinition['name'] . "';\n";
					}
				} else {
					$sConstName = 'NS_' . $sDefName;
					$sSaveContent .= "// START Namespace {$sDefName}\n";
					$sSaveContent .= "define(\"{$sConstName}\", {$iNS});\n";
					$sSaveContent .= "\$wgExtraNamespaces[{$sConstName}] = '" . $aDefinition['name'] . "';\n";
				}

				wfRunHooks( 'NamespaceManager::writeNamespaceConfiguration', array( &$sSaveContent, $sConstName, $aDefinition ) );
				if ( !$bIsSystemNs && isset( $aDefinition['alias'] ) && $aDefinition['alias'] ) {
					$sSaveContent .= "\$wgNamespaceAliases['{$aDefinition['alias']}'] = {$sConstName};\n";
				}
				$sSaveContent .= "// END Namespace {$sDefName}\n\n";
			}
		}
		$res = file_put_contents( BSROOTDIR . DS . 'config' . DS . 'nm-settings.php', $sSaveContent );

		if ( $res ) {
			return array(
				'success' => true,
				'message' => wfMessage( 'bs-namespacemanager-nsadded' )->plain()
			);
		}
		return array(
			'success' => false,
			// TODO SU (04.07.11 12:05): i18n
			'message' => 'Not able to create or write "' . BSROOTDIR . DS . 'config' . DS . 'nm-settings.php".'
		);
	}

}