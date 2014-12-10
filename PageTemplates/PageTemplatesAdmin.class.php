<?php
/**
 * Admin section for PageTemplates
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>

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

		$this->oExtension = BsExtensionManager::getExtension( 'PageTemplates' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Renders the HTML for the admin section within WikiAdmin
	 * @return string HTML output to be displayed
	 */
	public function getForm() {
		global $wgOut;
		$wgOut->addModules('ext.bluespice.pageTemplates');
		$sForm = '<div id="bs-pagetemplates-grid"></div>';
		return $sForm;
	}

	/**
	 * Returns a json encoded list of available namespaces
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public static function getNamespaces( $bShowAll ) {
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		global $wgCanonicalNamespaceNames, $wgRequest;
		$bShowPseudo = $wgRequest->getFuzzyBool( 'showPseudo', false );

		if ( $bShowAll ) {
			$wgCanonicalNamespaceNames[-99] = 'all';
		}
		$wgCanonicalNamespaceNames[0] = 'main';
		ksort( $wgCanonicalNamespaceNames );

		$aData = array();
		$aData['items'] = array();
		foreach ( $wgCanonicalNamespaceNames as $iNsIndex => $sNsCanonicalName ) {
			if ( !$bShowPseudo && ( $iNsIndex == -1 || $iNsIndex == -2 ) ) continue;
			$nsName = BsNamespaceHelper::getNamespaceName( $iNsIndex, true );
			$aData['items'][] = array( "name"=>$nsName, "id"=>$iNsIndex );
		}

		return json_encode( $aData );
	}

	/**
	 * Returns a json encoded list of available templates
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public static function getTemplates() {
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		global $wgRequest;
		$iLimit     = $wgRequest->getInt( 'limit', 25 );
		$iStart     = $wgRequest->getInt( 'start', 0 );
		$sSort      = $wgRequest->getVal( 'sort', 'user_name' );
		$sDirection = $wgRequest->getVal( 'dir', 'ASC' );

		switch ( $sSort ) {
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
			__METHOD__,
			array( 'ORDER BY' => $sSortField . ' ' . $sDirection, 'LIMIT' => $iLimit, 'OFFSET' => $iStart )
		);

		$aData = array();
		$aData['templates'] = array();

		while( $row = $res->fetchObject() ) {
				$tmp = array();
				$tmp['id']       = $row->pt_id;
				$tmp['label']    = $row->pt_label;
				$tmp['desc']     = $row->pt_desc;
				$tmp['targetns'] = BsNamespaceHelper::getNamespaceName( $row->pt_target_namespace, true );
				$tmp['targetnsid'] = $row->pt_target_namespace;
				$oTitle = Title::newFromText( $row->pt_template_title, $row->pt_template_namespace );
				$tmp['template']  = '<a href="'.$oTitle->getFullURL().'" target="_blank" '.($oTitle->exists()?'':'class="new"').'>'.$oTitle->getFullText().'</a>';
				$tmp['templatename'] = $row->pt_template_title;
				$tmp['templatens'] = $row->pt_template_namespace;
				$aData['templates'][] = $tmp;
		}

		$rescount = $dbr->selectRow( 'bs_pagetemplate', 'COUNT( pt_id ) AS cnt', array() );
		$aData['totalCount'] = $rescount->cnt;

		return json_encode( $aData );
	}

	/**
	 * Creates or changes a template
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public static function doEditTemplate( $iOldId, $sTemplateName, $sLabel, $sDesc, $iTargetNs, $iTemplateNs ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return json_encode( array(
				'success' => false,
				'errors' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() ) // errors not messages otherwise no message will be displayed
			) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'message' => array()
		);

		if ( empty( $sDesc ) ) $sDesc = ' ';

		// TODO RBV (18.05.11 09:19): Use validators
		if ( strlen( $sDesc ) >= 255 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['desc'] = wfMessage( 'bs-pagetemplates-tpl-desc-toolong' )->plain();
		}

		if ( strlen( $sLabel ) >= 255 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['label'] = wfMessage( 'bs-pagetemplates-tpl-label-toolong' )->plain();
		}

		if ( strlen( $sLabel ) == 0 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['label'] = wfMessage( 'bs-pagetemplates-tpl-label-empty' )->plain();
		}

		if ( strlen( $sTemplateName ) >= 255 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['templateName'] = wfMessage( 'bs-pagetemplates-tpl-name-toolong' )->plain();
		}

		if ( strlen( $sTemplateName ) == 0 ) {
			$aAnswer['success'] = false;
			$aAnswer['errors']['templateName'] = wfMessage( 'bs-pagetemplates-tpl-name-empty' )->plain();
		}

		$oDbw = wfGetDB( DB_MASTER );

		// This is the add template part
		if ( empty( $iOldId ) ) {
			if ( $aAnswer['success'] === true ) {
				$oDbw->insert(
					'bs_pagetemplate',
					array(
						'pt_label' => $sLabel,
						'pt_desc' => $sDesc,
						'pt_template_title' => $sTemplateName,
						'pt_template_namespace' => $iTemplateNs,
						'pt_target_namespace' => $iTargetNs,
						'pt_sid' => 0,
					));
				$aAnswer['message'][] = wfMessage( 'bs-pagetemplates-tpl-added' )->plain();
			}
		// and here we have edit template
		} else {
			$rRes = $oDbw->select( 'bs_pagetemplate', 'pt_id', array( 'pt_id' => $iOldId ) );
			$iNumRow = $oDbw->numRows( $rRes );
			if ( !$iNumRow ) {
				$aAnswer['success'] = false;
				$aAnswer['errors'][] = wfMessage( 'bs-pagetemplates-nooldtpl' )->plain();
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
					$aAnswer['errors'][] = wfMessage( 'bs-pagetemplates-dberror' )->plain();
				}
			}

			if ( $aAnswer['success'] ) {
				$aAnswer['message'][] = wfMessage( 'bs-pagetemplates-tpl-edited' )->plain();
			}
		}

		return json_encode( $aAnswer );
	}

	/**
	 * Deletes a template
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public static function doDeleteTemplate( $iId ) {
		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return json_encode( array(
				'success' => false,
				'message' => array( wfMessage( 'bs-readonly', $wgReadOnly )->plain() )
				) );
		}
		if ( BsCore::checkAccessAdmission( 'wikiadmin' ) === false ) return true;

		$aAnswer = array(
			'success' => true,
			'errors' => array(),
			'message' => array()
		);

		if ( empty( $iId ) ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = wfMessage( 'bs-pagetemplates-no-id' )->plain();
		}

		$dbw = wfGetDB( DB_MASTER );
		$res = $dbw->delete( 'bs_pagetemplate', array( 'pt_id' => $iId ) );

		if ( $res === false ) {
			$aAnswer['success'] = false;
			$aAnswer['errors'][] = wfMessage( 'bs-pagetemplates-dberror' )->plain();
		}

		if ( $aAnswer['success'] ) {
			$aAnswer['message'][] = wfMessage( 'bs-pagetemplates-tpl-deleted' )->plain();
		}

		return json_encode( $aAnswer );
	}

	public static function doDeleteTemplates($aId){
		$output = array();
		if (is_array($aId) && count($aId) > 0){
			foreach($aId as $sId => $sName){
				$output [$sName] = FormatJson::decode(self::doDeleteTemplate($sId));
			}
		}
		return FormatJson::encode($output);
	}

}