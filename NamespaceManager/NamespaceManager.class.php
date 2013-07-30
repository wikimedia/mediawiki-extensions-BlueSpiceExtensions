<?php

/**
 * This file is part of blue spice for MediaWiki.
 *
 * Administration interface for adding, editing and deletig user groups and their rights
 * @copyright Copyright (c) 2010, HalloWelt! Medienwerkstatt GmbH, All rights reserved.
 * @author Sebastian Ulbricht
 * @version 1.22.0
 *
 * $LastChangedDate: 2013-06-25 17:46:48 +0200 (Di, 25 Jun 2013) $
 * $LastChangedBy: mreymann $
 * $Rev: 9932 $
 * $Id: NamespaceManager.class.php 9932 2013-06-25 15:46:48Z mreymann $
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

	public $aSortConditions = array( 
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
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['NamespaceManager'] = dirname( __FILE__ ) . '/NamespaceManager.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;
		$this->mInfo = array(
			EXTINFO::NAME => 'NamespaceManager',
			EXTINFO::DESCRIPTION => 'Administration interface for adding, editing and deletig user groups and their rights',
			EXTINFO::AUTHOR => 'Sebastian Ulbricht',
			EXTINFO::VERSION => '1.22.0 ($Rev: 9932 $)',
			EXTINFO::STATUS => 'stable',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array( 
				'bluespice' => '1.22.0',
				'WikiAdmin' => '1.22.0',
				'Preferences' => '1.22.0'
			)
		);

		WikiAdmin::registerModule( 'NamespaceManager', array(
			'image' => '/extensions/BlueSpiceExtensions/WikiAdmin/images/bs-btn_namespaces_v1.png',
			'level' => 'editadmin'
			)
		);

		$this->registerScriptFiles( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/NamespaceManager/js', 'NamespaceManager', false, true, false, 'MW::NamespaceManagerShow' );
		$this->registerStyleSheet( BsConfig::get( 'MW::ScriptPath' ) . '/extensions/BlueSpiceExtensions/NamespaceManager/css/NamespaceManagerTreeview.css', true, 'MW::NamespaceManagerShow' );

		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'NamespaceManager', $this, 'getForm', 'wikiadmin' );

		global $wgDBtype;
		if ( $wgDBtype == 'oracle' ) {
			$this->registerExtensionSchemaUpdate( 'bs_ns_bak_page', dirname( __FILE__ ) . DS . 'bs_namespacemanager_backup_page.sql' );
			$this->registerExtensionSchemaUpdate( 'bs_ns_bak_revision', dirname( __FILE__ ) . DS . 'bs_namespacemanager_backup_revision.sql' );
			$this->registerExtensionSchemaUpdate( 'bs_ns_bak_text', dirname( __FILE__ ) . DS . 'bs_namespacemanager_backup_text.sql' );
		}
		else {
			$this->registerExtensionSchemaUpdate( 'bs_namespacemanager_backup_page', dirname( __FILE__ ) . DS . 'bs_namespacemanager_backup_page.sql' );
			$this->registerExtensionSchemaUpdate( 'bs_namespacemanager_backup_revision', dirname( __FILE__ ) . DS . 'bs_namespacemanager_backup_revision.sql' );
			$this->registerExtensionSchemaUpdate( 'bs_namespacemanager_backup_text', dirname( __FILE__ ) . DS . 'bs_namespacemanager_backup_text.sql' );
		}

		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'NamespaceManager', $this, 'getData', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'NamespaceManager', $this, 'addNamespace', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'NamespaceManager', $this, 'editNamespace', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'NamespaceManager', $this, 'deleteNamespace', 'wikiadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'NamespaceManager', $this, 'isNamespaceEmpty', 'wikiadmin' );

		BsCore::registerClass( 'NamespaceNuker', dirname( __FILE__ ), 'NamespaceNuker.php' );

		$this->setHook( 'NamespaceManager::getMetaFields', 'onGetMetaFields', true );
		$this->setHook( 'NamespaceManager::getNamespaceData', 'onGetNamespaceData', true );
		$this->setHook( 'NamespaceManager::editNamespace', 'onEditNamespace', true );
		$this->setHook( 'NamespaceManager::writeNamespaceConfiguration', 'onWriteNamespaceConfiguration', true );
		$this->setHook( 'LoadExtensionSchemaUpdates', 'onLoadExtensionSchemaUpdates', true );
		$GLOBALS['bsSystemNamespaces'] = BsAdapterMW::getMwNamespaceConstants();

		wfProfileOut( 'BS::NamespaceManager::__construct' );
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
		global $wgVersion, $wgExtNewFields, $wgDBtype;
		$dir = dirname( __FILE__ );

		if( $wgVersion < '1.19' ) {
            if ( $wgDBtype == 'postgres' ) {
                $wgExtNewFields[] = array( 'bs_namespacemanager_backup_revision', 'rev_sha1', $dir.DS.'bs_namespacemanager_backup_revision.patch.rev_sha1.pg.sql' );
            }
            else {
                $wgExtNewFields[] = array( 'bs_namespacemanager_backup_revision', 'rev_sha1', $dir.DS.'bs_namespacemanager_backup_revision.patch.rev_sha1.sql' );
            }
		}
		else {
            if ( $wgDBtype == 'postgres' ) {
                $wgExtNewFields[] = array( 'bs_namespacemanager_backup_revision', 'rev_sha1', $dir.DS.'bs_namespacemanager_backup_revision.patch.rev_sha1.pg.sql' );
            }
            else {
                $du->addExtensionField( 'bs_namespacemanager_backup_revision', 'rev_sha1', $dir.DS.'bs_namespacemanager_backup_revision.patch.rev_sha1.sql');
            }
		}
		return true;
	}
	
	/**
	 * returns if NS is empty or not
	 * @param type $iNamespaceId
	 * @return boolean 
	 */
	public function isNamespaceEmpty( $iNamespaceId ) {
		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
				'page',
				'page_namespace',
				array( 'page_namespace' => $iNamespaceId )
		);
		if( $res ) {
				return !$res->numRows();
		}
		return false;
	}
	
	/**
	 * Provides the form content for the WikiAdmin special page.
	 * @return string the form content
	 */
	public function getForm() {
		BsExtensionManager::setContext( 'MW::NamespaceManagerShow' );
		return '<div id="bs-namespacemanager-grid"></div>';
	}

	/**
	 * Calculate the data for the NamespaceManager store and put them to the ajax output.
	 * @param string $output the ajax output (have to be JSON)
	 */
	public function getData( &$output ) {
		global $wgContLang;

		$aMetaFields = array(
			array( 'name' => 'id', 'type' => 'int', 'sortable' => true, 'label' => wfMsg( 'bs-namespacemanager-label-id' ) ),
			array( 'name' => 'name', 'sortable' => true, 'label' => wfMsg( 'bs-namespacemanager-label-namespaces' ) )
		);

		wfRunHooks( 'NamespaceManager::getMetaFields', array( &$aMetaFields ) );

		$aResults = array( );
		$aNamespaces = $wgContLang->getNamespaces();
		foreach ( $aNamespaces as $iNs => $sNamespace ) {
			if( $sNamespace === '' ) {
				$sNamespace = BsAdapterMW::getNamespaceName($iNs);
			}
			if( $iNs === -2 || $iNs === -1 ) continue;
			$aResults[ ] = array(
				'id' => $iNs,
				'name' => $sNamespace
			);
		}

		wfRunHooks( 'NamespaceManager::getNamespaceData', array( &$aResults ) );
		
		$iLimit = BsCore::getParam( 'limit', 25, BsPARAM::REQUEST | BsPARAMTYPE::INT );
		$iStart = BsCore::getParam( 'start', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT );
		$sSort = BsCore::getParam( 'sort', 'id', BsPARAM::REQUEST | BsPARAMTYPE::STRING );
		$sDir = BsCore::getParam( 'dir', 'DESC', BsPARAM::REQUEST | BsPARAMTYPE::STRING );
		
		$this->aSortConditions['sort'] = $sSort;
		$this->aSortConditions['dir'] = $sDir;
		usort( $aResults, array( $this, 'namespaceManagerRemoteSort') );
		
		$aLimitedResults = array();
		$iResultCount = count( $aResults );
		$iMax = ( ($iStart + $iLimit) > $iResultCount )? $iResultCount:($iStart + $iLimit);
		for( $i = $iStart; $i < $iMax; $i++ ) {
			$aLimitedResults[] = $aResults[$i];
		}

		$aReturn = array(
			'total' => $iResultCount,
			'metaData' => array(
				'idProperty' => 'id',
				'root' => 'results',
				'totalProperty' => 'total',
				'successProperty' => 'success',
				'fields' => $aMetaFields,
				'sortInfo' => array(
					'field' => $sSort,
					'direction' => $sDir
				)
			),
			'success' => true,
			'results' => $aLimitedResults
		);
		$output = json_encode( $aReturn );
	}
	
	public function namespaceManagerRemoteSort( $value1, $value2 ) {
		$leftVal = $value1;
		$rightVal = $value2;
		if( $this->aSortConditions['dir'] === 'ASC' ) {
			$leftVal = $value2;
			$rightVal = $value1;
		}
		switch( $this->aSortConditions['sort'] ) {
			case 'name':
				if( strcasecmp( $leftVal['name'], $rightVal['name'] ) === 0 ) {
					return 0;
				} else {
					return strcasecmp( $leftVal['name'], $rightVal['name'] );
				}
			break;
			case 'id':
				if( $leftVal['id'] === $rightVal['id'] ) {
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
		$aMetaFields[ ] = array( 'name' => 'editable', 'type' => 'boolean', 'label' => wfMsg( 'bs-namespacemanager-label-editable' ) );
		$aMetaFields[ ] = array( 'name' => 'subpages', 'type' => 'boolean', 'label' => wfMsg( 'bs-namespacemanager-label-subpages' ) );
		$aMetaFields[ ] = array( 'name' => 'searchable', 'type' => 'boolean', 'label' => wfMsg( 'bs-namespacemanager-label-searchable' ) );
		$aMetaFields[ ] = array( 'name' => 'content', 'type' => 'boolean', 'label' => wfMsg( 'bs-namespacemanager-label-content' ) );
		
		return true;
	}

	public function onGetNamespaceData( &$aResults ) {
		global $wgExtraNamespaces, $wgNamespacesWithSubpages, $wgContentNamespaces, $wgNamespacesToBeSearchedDefault, $bsSystemNamespaces;
		wfRunHooks( 'BSNamespaceManagerBeforeSetUsernamespaces', array( $this, &$bsSystemNamespaces ) );
		$aUserNamespaces = $this->getUserNamespaces();

		$iResults = count( $aResults );
		for ( $i = 0; $i < $iResults; $i++ ) {

			$iNs = $aResults[ $i ][ 'id' ];		
			$aResults[ $i ][ 'editable' ] = ( isset($bsSystemNamespaces[$iNs] ) )? false : (array_search( $iNs, $aUserNamespaces ) !== false);
			$aResults[ $i ][ 'content' ] = (array_search( $iNs, $wgContentNamespaces ) !== false);
			$aResults[ $i ][ 'searchable' ] = (isset( $wgNamespacesToBeSearchedDefault[ $iNs ] ) && $wgNamespacesToBeSearchedDefault[ $iNs ]);
			$aResults[ $i ][ 'subpages' ] = (isset( $wgNamespacesWithSubpages[ $iNs ] ) && $wgNamespacesWithSubpages[ $iNs ]);
			if( array_search( $iNs, $aUserNamespaces ) !== false ) {
				$aResults[$i]['empty'] = $this->isNamespaceEmpty( $iNs );
			}
		}
		return true;
	}
	
	public function onEditNamespace( &$aNamespaceDefinition, $bUseInternalDefaults = false ) {
		if ( !$bUseInternalDefaults ) {
			$bContent = !!BsCore::getParam( 'content', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT );
			$bSubpages = !!BsCore::getParam( 'subpages', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT );
			$bSearched = !!BsCore::getParam( 'searchable', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT );
			if( empty($aNamespaceDefinition) ) $aNamespaceDefinition = array();
			$aNamespaceDefinition += array('content' => $bContent,'subpages' => $bSubpages,'searched' => $bSearched);
		}
		else {
			$aNamespaceDefinition += $this->_aDefaultNamespaceSettings;
		}
		return true;
	}

	public function onWriteNamespaceConfiguration( &$sSaveContent, $sConstName, $aDefinition ) {
		if ( isset( $aDefinition[ 'content' ] ) && $aDefinition[ 'content' ] ) {
			$sSaveContent .= "\$wgContentNamespaces[] = {$sConstName};\n";
		}
		if ( isset( $aDefinition[ 'subpages' ] ) && $aDefinition[ 'subpages' ] ) {
			$sSaveContent .= "\$wgNamespacesWithSubpages[{$sConstName}] = true;\n";
		}
		if ( isset( $aDefinition[ 'searched' ] ) && $aDefinition[ 'searched' ] ) {
			$sSaveContent .= "\$wgNamespacesToBeSearchedDefault[{$sConstName}] = true;\n";
		}
		return true;
	}

	/**
	 * Build the configuration for a new namespace and give it to the save method.
	 */
	public function addNamespace( &$output ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
			return;
		}

		$wgContLang = $this->mAdapter->get( 'ContLang' );

		$aNamespaces = $wgContLang->getNamespaces();
		$aUserNamespaces = $this->getUserNamespaces( true );
		end( $aNamespaces );
		$iNS = key( $aNamespaces ) + 1;
		reset( $aNamespaces );
		$sNamespace = BsCore::getParam( 'name', false, BsPARAM::REQUEST | BsPARAMTYPE::STRING );

		$sResult = true;
		foreach ( $aNamespaces as $sKey => $sNamespaceFromArray ) {
			if ( strtolower( $sNamespaceFromArray ) == strtolower( $sNamespace ) ) {
				$sResult = false;
				break;
			}
		}
		if ( $sResult ) {
			if ( strlen( $sNamespace ) < 2 ) {
				$output = json_encode( array(
					'success' => false,
					'msg' => wfMsg( 'bs-namespacemanager-namespace_name_length' )
					) );
				return;
			}
			else if ( !preg_match( '%^[a-zA-Z_\\x80-\\xFF][a-zA-Z0-9_\\x80-\\xFF]{1,99}$%i', $sNamespace ) ) {
				$output = json_encode( array(
					'success' => false,
					'msg' => wfMsg( 'bs-namespacemanager-wrong_namespace_name_format' )
					) );
				return;
			}
			else {
				$aUserNamespaces[ $iNS ] = array(
					'name' => $sNamespace
				);
				wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces[ $iNS ] ) );
				$aUserNamespaces[ ($iNS + 1) ] = array(
					'name' => $sNamespace . '_' . $wgContLang->getNsText( NS_TALK ),
					// TODO SU (04.07.11 12:13): Subpages in diskussionsnamespaces? Würd ich nicht machen. Diese drei Werte hätte ich eigentlich gerne in einer Einstellung, die im Konstruktor festgelegt wird. Gerne zunächst PRIVATE
					'alias' => $sNamespace . '_talk'
				);
				wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces[ $iNS ], true ) );
				$output = json_encode( $this->setUserNamespaces( $aUserNamespaces ) );
			}
		}
		else {
			// TODO SU (04.07.11 12:13): Aus Gründen der Lesbarkeit würde ich das direkt in die obige foreach-Schleife packen und den else-Zweig hir weglassen.
			$output = json_encode( array(
					'success' => false,
					'msg' => wfMsg( 'bs-namespacemanager-namespace_already_exists' )
				) );
		}
	}

	/**
	 * Change the configuration of a given namespace and give it to the save method.
	 */
	public function editNamespace( &$output ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
			return;
		}

		global $bsSystemNamespaces;
		$wgContLang = $this->mAdapter->get( 'ContLang' );
		wfRunHooks( 'BSNamespaceManagerBeforeSetUsernamespaces', array( $this, &$bsSystemNamespaces ) );
		$aNamespaces = $wgContLang->getNamespaces();
		$aUserNamespaces = $this->getUserNamespaces( true );
		$iNS = BsCore::getParam( 'id', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT );

		$sNamespace = BsCore::getParam( 'name', false, BsPARAM::REQUEST | BsPARAMTYPE::SQL_STRING );
		$bContent = !!BsCore::getParam( 'content', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT );
		$bSubpages = !!BsCore::getParam( 'subpages', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT );
		$bSearched = !!BsCore::getParam( 'searched', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT );

		if ( $iNS !== NS_MAIN && !$iNS ) {
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMsg( 'bs-namespacemanager-no_valid_namespace_id' )
				) );
			return;
		}
		if ( strlen( $sNamespace ) < 2 ) {
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMsg( 'bs-namespacemanager-namespace_name_length' )
				) );
			return;
		}
		if ( $iNS !== NS_MAIN && $iNS !== NS_PROJECT && $iNS !== NS_PROJECT_TALK && !preg_match( '%^[a-zA-Z_\\x80-\\xFF][a-zA-Z0-9_\\x80-\\xFF]{1,99}$%', $sNamespace ) ) {
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMsg( 'bs-namespacemanager-wrong_namespace_name_format' )
				) );
			return;
		}

		if ( !isset( $bsSystemNamespaces[($iNS)] ) && strstr( $sNamespace, '_' . $wgContLang->getNsText( NS_TALK ) ) ) {
				$aUserNamespaces[ $iNS ] = array(
					'name' => $aUserNamespaces[ $iNS ][ 'name' ],
					//'name' => $sNamespace . '_' . $wgContLang->getNsText( NS_TALK ),
					'alias' => str_replace( '_' . $wgContLang->getNsText( NS_TALK ), '_talk', $sNamespace ),
				);
			wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces[ $iNS ] ) );
		}
		else {
			$aUserNamespaces[ $iNS ] = array(
				'name' => $sNamespace,
			);
			
			if( !isset( $bsSystemNamespaces[($iNS)] ) ) {
				$aUserNamespaces[ ($iNS + 1) ][ 'name' ] = $sNamespace . '_' . $wgContLang->getNsText( NS_TALK );
				$aUserNamespaces[ ($iNS + 1) ][ 'alias' ] = $sNamespace . '_talk';
			}
			wfRunHooks( 'NamespaceManager::editNamespace', array( &$aUserNamespaces[ $iNS ] ) );
		}

		$output = json_encode( $this->setUserNamespaces( $aUserNamespaces ) );
	}

	/**
	 * Delete a given namespace.
	 */
	public function deleteNamespace( &$output ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
			return;
		}

		$wgContLang = $this->mAdapter->get( 'ContLang' );

		$aUserNamespaces = $this->getUserNamespaces( true );
		$iNS = BsCore::getParam( 'id', 0, BsPARAM::REQUEST | BsPARAMTYPE::INT );
		$iDoArticle = BsCore::getParam( 'doArticle', false, BsPARAMTYPE::INT | BsPARAM::POST );

		if ( !$iNS ) {
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMsg( 'bs-namespacemanager-no_valid_namespace_id' )
				) );
			return;
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
					}
					else {
						$aUserNamespaces[ $aNamespace[ 0 ] ] = false;
					}
				}
				break;
			case 1:
				foreach ( $aNamespacesToRemove as $aNamespace ) {
					if ( !NamespaceNuker::removeAllPages( $aNamespace[ 0 ], $sNamespace, $aNamespace[ 1 ] ) ) {
						$bErrors = true;
					}
					else {
						$aUserNamespaces[ $aNamespace[ 0 ] ] = false;
					}
				}
				break;
			case 2:
			default:
				foreach ( $aNamespacesToRemove as $aNamespace ) {
					if ( !NamespaceNuker::removeAllPagesWithSuffix( $aNamespace[ 0 ], $sNamespace, $aNamespace[ 1 ] ) ) {
						$bErrors = true;
					}
					else {
						$aUserNamespaces[ $aNamespace[ 0 ] ] = false;
					}
				}
				break;
		}

		if ( !$bErrors ) {
			$output = json_encode( $this->setUserNamespaces( $aUserNamespaces ) );
		}
		else {
			$output = json_encode( array(
				'success' => false,
				'msg' => wfMsg( 'bs-namespacemanager-error_on_remove_namespace' )
				) );
		}
	}

	/**
	 * Get all namespaces, which are created with the NamespaceManager.
	 * @param boolean $bFullDetails should the complete configuration of the namespaces be loaded
	 * @return array the namespace data 
	 */
	protected function getUserNamespaces( $bFullDetails = false ) {
		$wgExtraNamespaces = $this->mAdapter->get( 'ExtraNamespaces' );
		$wgNamespacesWithSubpages = $this->mAdapter->get( 'NamespacesWithSubpages' );
		$wgContentNamespaces = $this->mAdapter->get( 'ContentNamespaces' );
		$wgNamespacesToBeSearchedDefault = $this->mAdapter->get( 'NamespacesToBeSearchedDefault' );

		if ( !file_exists( BSROOTDIR . DS . 'config' . DS . 'nm-settings.php' ) ) {
			return array( );
		}
		$sConfigContent = file_get_contents( BSROOTDIR . DS . 'config' . DS . 'nm-settings.php' );
		$aUserNamespaces = array( );
//		if ( preg_match_all( '%// START Namespace ([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*).*define\("NS_\1", ([0-9]*)\).*?// END Namespace \1%s', $sConfigContent, $aMatches, PREG_PATTERN_ORDER ) ) {
		if ( preg_match_all( '%define\("NS_([a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*)", ([0-9]*)\)%s', $sConfigContent, $aMatches, PREG_PATTERN_ORDER ) ) {
			$aUserNamespaces = $aMatches[ 2 ];
		}
		if ( $bFullDetails ) {
			$aTmp = array( );
			foreach ( $aUserNamespaces as $iNS ) {
				$aTmp[ $iNS ] = array(
					'content' => (array_search( $iNS, $wgContentNamespaces ) !== false),
					'subpages' => (isset( $wgNamespacesWithSubpages[ $iNS ] ) && $wgNamespacesWithSubpages[ $iNS ]),
					'searched' => (isset( $wgNamespacesToBeSearchedDefault[ $iNS ] ) && $wgNamespacesToBeSearchedDefault[ $iNS ])
				);
				if( $iNS >= 100 ) {
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
	protected function setUserNamespaces( $aUserNamespaceDefinition ) {
		global $wgNamespacesWithSubpages, $wgContentNamespaces, $wgNamespacesToBeSearchedDefault, $bsSystemNamespaces;
		wfRunHooks( 'BSNamespaceManagerBeforeSetUsernamespaces', array( $this, &$bsSystemNamespaces ) );
		$sSaveContent = "<?php\n\n";

		foreach ( $aUserNamespaceDefinition as $iNS => $aDefinition ) {
			$bIsSystemNs = false;
			if( isset( $bsSystemNamespaces[$iNS] ) ) {
				$bIsSystemNs = true;
			}

			if ( $aDefinition ) {
				if ( isset( $aDefinition[ 'alias' ] ) && $aDefinition[ 'alias' ] ) {
					$sDefName = strtoupper( $aDefinition[ 'alias' ] );
				}
				else {
					if( $iNS >= 100 ) {
						$sDefName = strtoupper( $aDefinition[ 'name' ] );
					} else {
						$sDefName = $bsSystemNamespaces[$iNS];
					}
				}

				if( $bIsSystemNs ) {
					$sConstName = $bsSystemNamespaces[$iNS];
					$sSaveContent .= "// START Namespace {$sConstName}\n";
					$sSaveContent .= "if( !defined( \"{$sConstName}\" ) ) define(\"{$sConstName}\", {$iNS});\n";
					if( $iNS >= 100 ) {
						$sSaveContent .= "\$wgExtraNamespaces[{$sConstName}] = '" . $aDefinition[ 'name' ] . "';\n";
					}
				} else {
					$sConstName = 'NS_' . $sDefName;
					$sSaveContent .= "// START Namespace {$sDefName}\n";
					$sSaveContent .= "define(\"{$sConstName}\", {$iNS});\n";
					$sSaveContent .= "\$wgExtraNamespaces[{$sConstName}] = '" . $aDefinition[ 'name' ] . "';\n";
				}

				wfRunHooks('NamespaceManager::writeNamespaceConfiguration', array(&$sSaveContent, $sConstName, $aDefinition));
				if ( !$bIsSystemNs && isset( $aDefinition[ 'alias' ] ) && $aDefinition[ 'alias' ] ) {
					$sSaveContent .= "\$wgNamespaceAliases['{$aDefinition[ 'alias' ]}'] = {$sConstName};\n";
				}
				$sSaveContent .= "// END Namespace {$sDefName}\n\n";
			}
		}
		$res = file_put_contents( BSROOTDIR . DS . 'config' . DS . 'nm-settings.php', $sSaveContent );

		if ( $res ) {
			return array( 'success' => true );
		}
		return array(
			'success' => false,
			// TODO SU (04.07.11 12:05): i18n
			'msg' => 'Not able to create or write "' . BSROOTDIR . DS . 'config' . DS . 'nm-settings.php".'
		);
	}

}
