<?php

/**
 * This file is part of blue spice for MediaWiki.
 *
 * Administration interface for adding, editing and deletig user groups and their rights
 * @copyright Copyright (c) 2013, HalloWelt! Medienwerkstatt GmbH, All rights reserved.
 * @author Sebastian Ulbricht
 * @version 2.22.0
 */
/* Changelog
 * v1.20.0
 * -raised to stable
 * v1.0.0
 * -raised to stable
 * v0.1
 * FIRST CHANGES
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
			EXTINFO::DESCRIPTION => 'Administration interface for adding, editing and deletig user groups and their rights',
			EXTINFO::AUTHOR => 'Sebastian Ulbricht',
			EXTINFO::VERSION => '2.22.0',
			EXTINFO::STATUS => 'beta',
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
		global $wgDBtype;
		if ( $wgDBtype == 'oracle' ) {
			$this->registerExtensionSchemaUpdate( 'bs_ns_bak_page', __DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_page.sql' );
			$this->registerExtensionSchemaUpdate( 'bs_ns_bak_revision', __DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_revision.sql' );
			$this->registerExtensionSchemaUpdate( 'bs_ns_bak_text', __DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_text.sql' );
		} else {
			$this->registerExtensionSchemaUpdate( 'bs_namespacemanager_backup_page', __DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_page.sql' );
			$this->registerExtensionSchemaUpdate( 'bs_namespacemanager_backup_revision', __DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_revision.sql' );
			$this->registerExtensionSchemaUpdate( 'bs_namespacemanager_backup_text', __DIR__ . DS . 'resources' . DS . 'bs_namespacemanager_backup_text.sql' );
		}

		BsConfig::registerVar( 'MW::NamespaceManager::NsOffset', 3000, BsConfig::TYPE_INT,  BsConfig::LEVEL_PRIVATE );

		$this->setHook( 'NamespaceManager::getMetaFields', 'onGetMetaFields', true );
		$this->setHook( 'NamespaceManager::getNamespaceData', 'onGetNamespaceData', true );
		$this->setHook( 'NamespaceManager::editNamespace', 'onEditNamespace', true );
		$this->setHook( 'NamespaceManager::writeNamespaceConfiguration', 'onWriteNamespaceConfiguration', true );
		$this->setHook( 'LoadExtensionSchemaUpdates', 'onLoadExtensionSchemaUpdates', true );
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
	public function onLoadExtensionSchemaUpdates( $du ) {
		parent::onLoadExtensionSchemaUpdates( $du );
		global $wgExtPGNewFields, $wgDBtype;
		$dir = __DIR__;

		if ( $wgDBtype == 'postgres' ) {
			$wgExtPGNewFields[] = array( 'bs_namespacemanager_backup_page', 'page_content_model', $dir.DS.'resources' . DS . 'bs_namespacemanager_backup_page.patch.pg.sql' );
			$wgExtPGNewFields[] = array( 'bs_namespacemanager_backup_revision', 'rev_sha1', $dir.DS.'resources' . DS . 'bs_namespacemanager_backup_revision.patch.rev_sha1.pg.sql' );
			$wgExtPGNewFields[] = array( 'bs_namespacemanager_backup_revision', 'rev_content_model', $dir.DS.'resources' . DS . 'bs_namespacemanager_backup_revision.patch2.pg.sql' );
		} else {
			$du->addExtensionField( 'bs_namespacemanager_backup_page', 'page_content_model', $dir.DS.'resources' . DS . 'bs_namespacemanager_backup_page.patch.sql' );
			$du->addExtensionField( 'bs_namespacemanager_backup_revision', 'rev_sha1', $dir.DS.'resources' . DS . 'bs_namespacemanager_backup_revision.patch.rev_sha1.sql');
			$du->addExtensionField( 'bs_namespacemanager_backup_revision', 'rev_content_model', $dir.DS.'resources' . DS . 'bs_namespacemanager_backup_revision.patch2.sql' );
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
		return '<div id="bs-namespacemanager-grid"></div>';
	}

	/**
	 * Calculate the data for the NamespaceManager store and put them to the ajax output.
	 * @param string $output the ajax output (have to be JSON)
	 */
	public static function getData() {
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;
		global $wgContLang;

		$aMetaFields = array(
			array( 'name' => 'id', 'type' => 'int', 'sortable' => true, 'label' => wfMessage( 'bs-namespacemanager-label-id' )->plain() ),
			array( 'name' => 'name', 'sortable' => true, 'label' => wfMessage( 'bs-namespacemanager-label-namespaces' )->plain() )
		);

		wfRunHooks( 'NamespaceManager::getMetaFields', array( &$aMetaFields ) );

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
		$sSort = $oRequest->getVal( 'sort', 'id' );
		$sDir = $oRequest->getVal( 'sir', 'DESC' );

		self::$aSortConditions['sort'] = $sSort;
		self::$aSortConditions['dir'] = $sDir;
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
		return json_encode( $aReturn );
	}

	public static function namespaceManagerRemoteSort( $value1, $value2 ) {
		$leftVal = $value1;
		$rightVal = $value2;
		if ( self::$aSortConditions['dir'] === 'ASC' ) {
			$leftVal = $value2;
			$rightVal = $value1;
		}
		switch( self::$aSortConditions['sort'] ) {
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
				wfRunHooks( 'NamespaceManager::namespaceManagerRemoteSort', array( $this, $value1, $value2, $this->aSortConditions ) );
		}

	}

	public function onGetMetaFields( &$aMetaFields ) {
		$aMetaFields[] = array( 'name' => 'editable', 'type' => 'boolean', 'label' => wfMessage( 'bs-namespacemanager-label-editable' )->plain() );
		$aMetaFields[] = array( 'name' => 'subpages', 'type' => 'boolean', 'label' => wfMessage( 'bs-namespacemanager-label-subpages' )->plain() );
		$aMetaFields[] = array( 'name' => 'searchable', 'type' => 'boolean', 'label' => wfMessage( 'bs-namespacemanager-label-searchable' )->plain() );
		$aMetaFields[] = array( 'name' => 'content', 'type' => 'boolean', 'label' => wfMessage( 'bs-namespacemanager-label-content' )->plain() );

		return true;
	}

	public function onGetNamespaceData( &$aResults ) {
		global $wgExtraNamespaces, $wgNamespacesWithSubpages, $wgContentNamespaces, $wgNamespacesToBeSearchedDefault, $bsSystemNamespaces;
		wfRunHooks( 'BSNamespaceManagerBeforeSetUsernamespaces', array( $this, &$bsSystemNamespaces ) );
		$aUserNamespaces = self::getUserNamespaces();

		$iResults = count( $aResults );
		for ( $i = 0; $i < $iResults; $i++ ) {

			$iNs = $aResults[$i]['id'];
			$aResults[ $i ][ 'editable' ] = ( isset($bsSystemNamespaces[$iNs] ) ) ? false : (array_search( $iNs, $aUserNamespaces ) !== false);
			$aResults[ $i ][ 'content' ] = (array_search( $iNs, $wgContentNamespaces ) !== false);
			$aResults[ $i ][ 'searchable' ] = (isset( $wgNamespacesToBeSearchedDefault[ $iNs ] ) && $wgNamespacesToBeSearchedDefault[ $iNs ]);
			$aResults[ $i ][ 'subpages' ] = (isset( $wgNamespacesWithSubpages[ $iNs ] ) && $wgNamespacesWithSubpages[ $iNs ]);
			if ( array_search( $iNs, $aUserNamespaces ) !== false ) {
				$aResults[$i]['empty'] = $this->isNamespaceEmpty( $iNs );
			}
		}
		return true;
	}

	/**
	 * Hook-Handler for NamespaceManager::editNamespace
	 * @return boolean Always true to kepp hook alive
	 */
	public function onEditNamespace( &$aNamespaceDefinition, $bSubpages, $bSearchable, $bEvalualbe, $bUseInternalDefaults = false ) {
		if ( !$bUseInternalDefaults ) {
			if ( empty( $aNamespaceDefinition ) ) $aNamespaceDefinition = array();
			$aNamespaceDefinition += array( 'content' => $bEvalualbe,'subpages' => $bSubpages,'searched' => $bSearchable );
		} else {
			$aNamespaceDefinition += $this->_aDefaultNamespaceSettings;
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
	public static function addNamespace( $sNamespace, $bSubpages, $bSearchable, $bEvaluable ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return json_encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
			) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		global $wgContLang;
		$aNamespaces = $wgContLang->getNamespaces();
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
				return json_encode( array(
					'success' => false,
					'message' => wfMessage( 'bs-namespacemanager-namespace_name_length' )->plain()
					) );
			} else if ( !preg_match( '%^[a-zA-Z_\\x80-\\xFF][a-zA-Z0-9_\\x80-\\xFF]{1,99}$%i', $sNamespace ) ) {
				return json_encode( array(
					'success' => false,
					'message' => wfMessage( 'bs-namespacemanager-wrong_namespace_name_format' )->plain()
					) );
			} else {
				$aUserNamespaces[$iNS] = array( 'name' => $sNamespace );

				wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces[$iNS], $bSubpages, $bSearchable, $bEvaluable ) );

				$aUserNamespaces[ ( $iNS + 1 ) ] = array(
					'name' => $sNamespace . '_' . $wgContLang->getNsText( NS_TALK ),
					// TODO SU (04.07.11 12:13): Subpages in diskussionsnamespaces? Würd ich nicht machen. Diese drei Werte hätte ich eigentlich gerne in einer Einstellung, die im Konstruktor festgelegt wird. Gerne zunächst PRIVATE
					'alias' => $sNamespace . '_talk'
				);
				wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces[$iNS], $bSubpages, $bSearchable, $bEvaluable, true ) );

				return json_encode( self::setUserNamespaces( $aUserNamespaces ) );
			}
		} else {
			// TODO SU (04.07.11 12:13): Aus Gründen der Lesbarkeit würde ich das direkt in die obige foreach-Schleife packen und den else-Zweig hir weglassen.
			return json_encode( array(
					'success' => false,
					'message' => wfMessage( 'bs-namespacemanager-namespace_already_exists' )->plain()
				) );
		}
	}

	/**
	 * Change the configuration of a given namespace and give it to the save method.
	 */
	public static function editNamespace( $iNS, $sNamespace, $bSubpages, $bSearchable, $bEvaluable ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return json_encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
			return;
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		global $bsSystemNamespaces, $wgContLang;
		$oNamespaceManager = BsExtensionManager::getExtension( 'NamespaceManager' );
		wfRunHooks( 'BSNamespaceManagerBeforeSetUsernamespaces', array( $oNamespaceManager, &$bsSystemNamespaces ) );
		$aUserNamespaces = self::getUserNamespaces( true );

		if ( $iNS !== NS_MAIN && !$iNS ) {
			return json_encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-namespacemanager-no_valid_namespace_id' )->plain()
			) );
		}
		if ( strlen( $sNamespace ) < 2 ) {
			return json_encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-namespacemanager-namespace_name_length' )->plain()
			) );
		}
		if ( $iNS !== NS_MAIN && $iNS !== NS_PROJECT && $iNS !== NS_PROJECT_TALK && !preg_match( '%^[a-zA-Z_\\x80-\\xFF][a-zA-Z0-9_\\x80-\\xFF]{1,99}$%', $sNamespace ) ) {
			return json_encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-namespacemanager-wrong_namespace_name_format' )->plain()
			) );
		}

		if ( !isset( $bsSystemNamespaces[($iNS)] ) && strstr( $sNamespace, '_' . $wgContLang->getNsText( NS_TALK ) ) ) {
				$aUserNamespaces[ $iNS ] = array(
					'name' => $aUserNamespaces[$iNS]['name'],
					//'name' => $sNamespace . '_' . $wgContLang->getNsText( NS_TALK ),
					'alias' => str_replace( '_' . $wgContLang->getNsText( NS_TALK ), '_talk', $sNamespace ),
				);
			wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces[$iNS], $bSubpages, $bSearchable, $bEvaluable ) );
		} else {
			$aUserNamespaces[$iNS] = array(
				'name' => $sNamespace,
			);

			if ( !isset( $bsSystemNamespaces[($iNS)] ) ) {
				$aUserNamespaces[($iNS + 1)]['name'] = $sNamespace . '_' . $wgContLang->getNsText( NS_TALK );
				$aUserNamespaces[($iNS + 1)]['alias'] = $sNamespace . '_talk';
			}
			wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces[$iNS], $bSubpages, $bSearchable, $bEvaluable ) );
		}

		$aResult = self::setUserNamespaces( $aUserNamespaces );
		$aResult['message'] = wfMessage( 'bs-namespacemanager-nsedited' )->plain();

		return json_encode( $aResult );
	}

	/**
	 * Delete a given namespace.
	 */
	public function deleteNamespace( $iNS, $iDoArticle ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return json_encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		global $wgContLang;

		$aUserNamespaces = self::getUserNamespaces( true );

		if ( empty( $iDoArticle ) ) $iDoArticle = 0;

		if ( !$iNS ) {
			return json_encode( array(
				'success' => false,
				'message' => wfMessage( 'bs-namespacemanager-no_valid_namespace_id' )->plain()
				) );
		}
		$aNamespacesToRemove = array( array( $iNS, 0 ) );
		$sNamespace = $aUserNamespaces[ $iNS ][ 'name' ];

		if ( !strstr( $sNamespace, '_'.$wgContLang->getNsText( NS_TALK ) ) ) {
			if ( isset( $aUserNamespaces[ ($iNS + 1) ] ) && strstr( $aUserNamespaces[ ($iNS + 1) ][ 'name' ], '_'.$wgContLang->getNsText( NS_TALK ) ) ) {
				$aNamespacesToRemove[ ] = array( ($iNS + 1), 1 );
				$sNamespace = $aUserNamespaces[ ($iNS + 1) ][ 'name' ];
			}
		}

		$bErrors = false;

		switch ( $iDoArticle ) {
			case 0:
				foreach ( $aNamespacesToRemove as $aNamespace ) {
					if ( !NamespaceNuker::nukeNamespaceWithAllPages( $aNamespace[ 0 ] ) ) {

						$bErrors = true;
					} else {
						$aUserNamespaces[ $aNamespace[ 0 ] ] = false;
					}
				}
				break;
			case 1:
				foreach ( $aNamespacesToRemove as $aNamespace ) {
					if ( !NamespaceNuker::removeAllPages( $aNamespace[ 0 ], $sNamespace, $aNamespace[ 1 ] ) ) {
						$bErrors = true;
					} else {
						$aUserNamespaces[ $aNamespace[ 0 ] ] = false;
					}
				}
				break;
			case 2:
			default:
				foreach ( $aNamespacesToRemove as $aNamespace ) {
					if ( !NamespaceNuker::removeAllPagesWithSuffix( $aNamespace[ 0 ], $sNamespace, $aNamespace[ 1 ] ) ) {
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
			return json_encode( $aResult );
		} else {
			return json_encode( array(
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
//		if ( preg_match_all( '%// START Namespace ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*).*define\("NS_\1", ([0-9]*)\).*?// END Namespace \1%s', $sConfigContent, $aMatches, PREG_PATTERN_ORDER ) ) {
		if ( preg_match_all( '%define\("NS_([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)", ([0-9]*)\)%s', $sConfigContent, $aMatches, PREG_PATTERN_ORDER ) ) {
			$aUserNamespaces = $aMatches[ 2 ];
		}
		if ( $bFullDetails ) {
			$aTmp = array();
			foreach ( $aUserNamespaces as $iNS ) {
				$aTmp[ $iNS ] = array(
					'content' => ( array_search( $iNS, $wgContentNamespaces ) !== false ),
					'subpages' => ( isset( $wgNamespacesWithSubpages[ $iNS ] ) && $wgNamespacesWithSubpages[ $iNS ] ),
					'searched' => ( isset( $wgNamespacesToBeSearchedDefault[ $iNS ] ) && $wgNamespacesToBeSearchedDefault[ $iNS ] )
				);
				if ( $iNS >= 100 ) {
					$aTmp[ $iNS ]['name'] = $wgExtraNamespaces[ $iNS ];
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
		global $wgNamespacesWithSubpages, $wgContentNamespaces, $wgNamespacesToBeSearchedDefault, $bsSystemNamespaces;

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
