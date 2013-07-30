<?php
/**
 * Admin section for PageTemplates
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: PageTemplatesAdmin.class.php 9900 2013-06-25 06:38:06Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage PageTemplates
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for page template admin
 * @package BlueSpice_Extensions
 * @subpackage PageTemplates
 */
class PageTemplatesAdmin {

	/**
	 * Back reference to base extension.
	 * @var BsExtensionMW 
	 */
	protected $oExtension;

	/**
	 * Constructor of PageTemplatesAdmin class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'PageTemplatesAdmin', $this, 'getTemplates', 'editadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'PageTemplatesAdmin', $this, 'getLanguages', 'editadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'PageTemplatesAdmin', $this, 'getNamespaces', 'editadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'PageTemplatesAdmin', $this, 'doEditTemplate', 'editadmin' );
		BsCore::getInstance( 'MW' )->getAdapter()->addRemoteHandler( 'PageTemplatesAdmin', $this, 'doDeleteTemplate', 'editadmin' );

		$this->oExtension = BsExtensionMW::getInstanceFor( 'MW::PageTemplates' ); 
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Renders the HTML for the admin section within WikiAdmin
	 * @return string HTML output to be displayed
	 */
	public function getForm() {
		global $wgOut;
		$wgOut->addModules('ext.bluespice.pageTemplates');
		$sForm = '<div id="bs-pagetemplates-admingrid"></div>';
		return $sForm;
	}

	// TODO RBV (18.05.11 09:13): Move to adapter
	/**
	 * Returns a json encoded list of available namespaces
	 * @param string $sOutput json encoded list
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function getNamespaces( &$sOutput ) {
		$aNamespaces = $this->oExtension->mAdapter->CanonicalNamespaceNames;
		$bShowAll    = BsCore::getParam( 'showAll', false, BsPARAM::REQUEST|BsPARAMTYPE::BOOL );
		$bShowPseudo = BsCore::getParam( 'showPseudo', false, BsPARAM::REQUEST|BsPARAMTYPE::BOOL );
		if ( $bShowAll ) {
			$aNamespaces[-99] = 'all';
		}
		$aNamespaces[0] = 'main';
		ksort( $aNamespaces );

		$aData = array();
		$aData['items'] = array();
		foreach ( $aNamespaces as $iNsIndex => $sNsCanonicalName ) {
			if ( !$bShowPseudo && ($iNsIndex == -1 || $iNsIndex == -2) ) continue;
			$nsName = BsAdapterMW::getNamespaceName( $iNsIndex, true );
			$aData['items'][] = array( "name"=>$nsName, "id"=>$iNsIndex );
		}

		$sOutput = json_encode($aData);
		return true;
	}	

	/**
	 * Returns a json encoded list of available templates
	 * @param string $output json encoded list
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function getTemplates( &$output ) {
		$iLimit     = BsCore::getParam( 'limit', 25, BsPARAM::REQUEST|BsPARAMTYPE::INT);
		$iStart     = BsCore::getParam( 'start', 0, BsPARAM::REQUEST|BsPARAMTYPE::INT);
		$sSort      = BsCore::getParam( 'sort',  'user_name', BsPARAM::POST|BsPARAMTYPE::SQL_STRING );
		$sDirection = BsCore::getParam( 'dir',   'ASC', BsPARAM::POST|BsPARAMTYPE::SQL_STRING );

		switch( $sSort ) {
			case 'label':
				$sSortField = 'pt_label';
				break;
			case 'desc':
				$sSortField = 'pt_desc';
				break;
			case 'targetns':
				$sSortField = 'pt_target_namespace';
				break;
			case 'template':
				$sSortField = 'pt_template_title';
				break;
			default: 
				$sSortField = 'pt_label';
				break;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'bs_pagetemplate' ), 
			array( 'pt_id', 'pt_label', 'pt_desc', 'pt_target_namespace', 'pt_template_title', 'pt_template_namespace'  ), 
			array(), 
			'', 
			array( 'ORDER BY' => $sSortField . ' ' . $sDirection, 'LIMIT' => $iLimit, 'OFFSET' => $iStart )
		);

		$aData = array();
		$aData['templates'] = array();

		while( $row = $res->fetchObject() ) {
				$tmp = array();
				$tmp['id']       = $row->pt_id;
				$tmp['label']    = $row->pt_label;
				$tmp['desc']     = $row->pt_desc;
				$tmp['targetns'] = BsAdapterMW::getNamespaceName( $row->pt_target_namespace, true );
				$tmp['targetnsid'] = $row->pt_target_namespace;
				$oTitle = Title::newFromText( $row->pt_template_title, $row->pt_template_namespace );
				$tmp['template']  = '<a href="'.$oTitle->getFullURL().'" target="_blank" '.($oTitle->exists()?'':'class="new"').'>'.$oTitle->getFullText().'</a>';
				$tmp['templatename'] = $row->pt_template_title;
				$tmp['templatens'] = $row->pt_template_namespace;
				$aData['templates'][] = $tmp;
		}

		$rescount = $dbr->selectRow( 'bs_pagetemplate', 'COUNT( pt_id ) AS cnt', array() );
		$aData['totalCount'] = $rescount->cnt;

		$output = json_encode($aData);
	}
	
	/**
	 * Creates or changes a template 
	 * @param string $sOutput json encoded answer array with success status
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function doEditTemplate( &$sOutput ) {
		// TODO RBV (18.05.11 09:15): Use XHRResponse Abstraction from Core.
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$sOutput = json_encode( array(
				'success' => false,
				'errors' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() ) // errors not messages otherwise no message will be displayed
				) );
			return;
		}

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'messages' => array()
		);

		$iOldId = BsCore::getParam( 'oldId', 0, BsPARAM::REQUEST|BsPARAMTYPE::INT );
		$sTemplateName = BsCore::getParam( 'templateName', '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		$sLabel = BsCore::getParam( 'label', '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		$sDesc = BsCore::getParam( 'desc', '', BsPARAM::REQUEST|BsPARAMTYPE::STRING );
		$iTargetNs = BsCore::getParam( 'targetNs', -99, BsPARAM::REQUEST|BsPARAMTYPE::INT );
		$iTemplateNs = BsCore::getParam( 'templateNs', -99, BsPARAM::REQUEST|BsPARAMTYPE::INT );

		if ( empty( $sDesc ) ) $sDesc = ' ';

		// TODO RBV (18.05.11 09:19): Use validators
		if ( strlen( $sDesc ) >= 255 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['desc'] = wfMsg( 'bs-pagetemplates-desc_2long' );
		}

		if ( strlen( $sLabel ) >= 255 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['label'] = wfMsg( 'bs-pagetemplates-label_2long' );
		}

		if ( strlen( $sLabel ) == 0 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['label'] = wfMsg( 'bs-pagetemplates-label_empty' );
		}

		if ( strlen( $sTemplateName ) >= 255 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['templateName'] = wfMsg( 'bs-pagetemplates-templatename_2long' );
		}

		if ( strlen( $sTemplateName ) == 0 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['templateName'] = wfMsg( 'bs-pagetemplates-templatename_empty' );
		}

		$aNamespaces = $this->oExtension->mAdapter->CanonicalNamespaceNames;
		$aNamespaces[0] = 'main';

		if ( !in_array( $iTemplateNs, array_keys($aNamespaces) ) ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['templateNamespace'] = wfMsg( 'bs-pagetemplates-template_namespace_doesnt_exist' );
		}

		$aNamespaces[-99] = 'all';
		if ( !in_array( $iTargetNs, array_keys($aNamespaces) ) ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['target'] = wfMsg( 'bs-pagetemplates-target_namespace_doesnt_exist' );
		}

		if ( $sLabel == '' ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = wfMsg( 'bs-pagetemplates-no_label' );
		}

		$oDbw = wfGetDB( DB_MASTER );

		// This is the add template part
		if ( $iOldId == 0 ) {
			if ( $aAnswer['success'] === true ) {
				//$oDbw = wfGetDB( DB_MASTER );
				$oDbw->insert( 'bs_pagetemplate',
						array( 
							'pt_label' => $sLabel,
							'pt_desc' => $sDesc,
							'pt_template_title' => $sTemplateName,
							'pt_template_namespace' => $iTemplateNs,
							'pt_target_namespace' => $iTargetNs,
							'pt_sid' => 0,
							));
				$aAnswer['messages'][] = wfMsg( 'bs-pagetemplates-tpl_added' );
			}
		// and here we have edit template
		} else {
			$rRes = $oDbw->select( 'bs_pagetemplate', 'pt_id', array( 'pt_id' => $iOldId ) );
			$iNumRow = $oDbw->numRows( $rRes );
			if ( !$iNumRow ) {
				$aAnswer['success'] = false;
				$aAnswer['errors'][] = wfMsg( 'bs-pagetemplates-no_old_tpl' );
			}

			if ( $aAnswer['success'] === true ) {
				//$oDbw = wfGetDB( DB_MASTER );
				$rRes = $oDbw->update( 'bs_pagetemplate',
						array( 'pt_id' => $iOldId,
							'pt_label' => $sLabel,
							'pt_desc' => $sDesc,
							'pt_template_title' => $sTemplateName,
							'pt_template_namespace' => $iTemplateNs,
							'pt_target_namespace' => $iTargetNs
							),
						array( 'pt_id' => $iOldId )
					);

				if ( $rRes === false ) {
					$aAnswer['success'] = false;
					$aAnswer['errors'][] = wfMsg( 'bs-pagetemplates-db_error' );
				}
			}

			if ( $aAnswer['success'] ) {
				$aAnswer['messages'][] = wfMsg( 'bs-pagetemplates-tpl_edited' );
			}
		}

		$sOutput = json_encode( $aAnswer ); // TODO RBV (18.05.11 09:23): Core XHRResponse Abstraction
		return;
	}

	/**
	 * Deletes a template 
	 * @param string $sOutput json encoded answer array with success status
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	function doDeleteTemplate( &$sOutput ) {
		// TODO RBV (18.05.11 09:25): XHRResponse Abstraction
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			$sOutput = json_encode( array(
				'success' => false,
				'messages' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
			return;
		}

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'messages' => array()
		);

		$iId = BsCore::getParam( 'id', null, BsPARAM::REQUEST|BsPARAMTYPE::INT );

		if ( $iId === null ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = wfMsg( 'bs-pagetemplates-no_id' );
		}

		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->delete( 'bs_pagetemplate', array( 'pt_id' => $iId ) );

		if ( $res === false ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = wfMsg( 'bs-pagetemplates-db_error' );
		}

		if ( $aAnswer['success'] ) {
			$aAnswer['messages'][] = wfMsg( 'bs-pagetemplates-tpl_deleted' );
		}

		$sOutput = json_encode( $aAnswer );
		return;
	}

}