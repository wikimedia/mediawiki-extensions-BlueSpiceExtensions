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
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
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
	 * Creates or changes a template
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public static function doEditTemplate( $iOldId, $sTemplateName, $sLabel, $sDesc, $iTargetNs ) {
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

		$oTitle = Title::newFromText( $sTemplateName );
		// This is the add template part
		if ( empty( $iOldId ) ) {
			if ( $aAnswer['success'] === true ) {
				$oDbw->insert(
					'bs_pagetemplate',
					array(
						'pt_label' => $sLabel,
						'pt_desc' => $sDesc,
						'pt_template_title' => $sTemplateName,
						'pt_template_namespace' => $oTitle->getNamespace(),
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
							'pt_template_namespace' => $oTitle->getNamespace(),
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
}