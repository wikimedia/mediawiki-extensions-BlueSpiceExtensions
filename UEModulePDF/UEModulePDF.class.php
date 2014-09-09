<?php
/**
 * UniversalExport PDF Module extension for BlueSpice
 *
 * Enables MediaWiki to export pages into PDF format.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
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
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @version    2.22.0

 * @package    BlueSpice_Extensions
 * @subpackage UEModulePDF
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - Added feature "Suppress namespace"
 * v1.1.0
 * - Support for attachments
 * - Rebuild a lot of code
 * - Added template support
 * - Reduced use of deprecated BsEventDispatcher
 * v1.0.0
 * - Initial release
 */

/**
 * Base class for UniversalExport PDF Module extension
 * @package BlueSpice_Extensions
 * @subpackage UEModulePDF
 */
class UEModulePDF extends BsExtensionMW {

	/**
	 * Contructor of the UEModulePDF class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::SPECIALPAGE;

		$this->mInfo = array(
			EXTINFO::NAME        => 'UEModulePDF',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-uemodulepdf-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
				'bluespice'       => '2.22.0',
				'UniversalExport' => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::UEModulePDF';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of UEModulePDF extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//Variables
		BsConfig::registerVar( 'MW::UEModulePDF::PdfServiceURL', 'http://localhost:8080/BShtml2PDF', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING, 'bs-uemodulepdf-pref-pdfserviceurl' );
		BsConfig::registerVar( 'MW::UEModulePDF::DefaultTemplate', 'BlueSpice', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-uemodulepdf-pref-templatepath' );
		BsConfig::registerVar( 'MW::UEModulePDF::SuppressNS', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-uemodulepdf-pref-suppressns', 'toggle' );
		/* This setting is no longer needed. We do not provide the old bn2pdf.war anymore */
		BsConfig::registerVar( 'MW::UEModulePDF::Backend', 'BsPDFServlet', BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS );
		BsConfig::registerVar( 'MW::UEModulePDF::TemplatePath', 'extensions/BlueSpiceFoundation/data/PDFTemplates', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING, 'bs-uemodulepdf-pref-templatepath' );

		//Hooks
		$this->setHook('BSUniversalExportGetWidget');
		$this->setHook('BSUniversalExportSpecialPageExecute');
		$this->setHook('SkinTemplateOutputPageBeforeExec');
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array();
		switch( $oVariable->getName() ) {
			case 'DefaultTemplate':
				$aParams = array( 'template-path' => BsConfig::get('MW::UEModulePDF::TemplatePath') );
				$aPrefs = array(
					'type' => 'select',
					'options' => BsPDFTemplateProvider::getTemplatesForSelectOptions( $aParams )
				);
				break;
			default:
				break;
		}
		return $aPrefs;
	}

	/**
	 * Sets up requires directories
	 * @param DatabaseUpdater $updater Provided by MediaWikis update.php
	 * @return boolean Always true to keep the hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		//TODO: Create abstraction in Core/Adapter
		$sTmpDir = BSDATADIR.DS.'UEModulePDF';
		if( !file_exists( $sTmpDir ) ) {
			echo 'Directory "'.$sTmpDir.'" not found. Creating.'."\n";
			wfMkdirParents( $sTmpDir );
		} else {
			echo 'Directory "'.$sTmpDir.'" found.'."\n";
		}

		$sDefaultTemplateDir = BSDATADIR.DS.'PDFTemplates';
		if( !file_exists( $sDefaultTemplateDir ) ) {
			echo 'Default template directory "'.$sDefaultTemplateDir.'" not found. Copying.'."\n";
			BsFileSystemHelper::copyRecursive( __DIR__.DS.'data'.DS.'PDFTemplates', $sDefaultTemplateDir );
		}

		return true;
	}

	/**
	 *
	 * @param SpecialUniversalExport $oSpecialPage
	 * @param string $sParam
	 * @param array $aModules
	 * @return true
	 */
	public function onBSUniversalExportSpecialPageExecute( $oSpecialPage, $sParam, &$aModules ) {
		$aModules['pdf'] = new BsExportModulePDF();
		return true;
	}

	/**
	 * Event-Handler method for the 'BSUniversalExportCreateWidget' event.
	 * Registers the PDF Module with the UniversalExport Extension.
	 * @param BsEvent $oEvent
	 * @param array $aModules
	 * @return array
	 * @deprecated in 1.1.1
	 */
	public function onUniversalExportSpecialPageExecute( $oCurrentTitle, $oSpecialPage, $aCurrentQueryParams, $aModules) {
		$aModules['pdf'] = new BsExportModulePDF();
		return $aModules;
	}

	/**
	 * Hook-Handler method for the 'BSUniversalExportGetWidget' event.
	 * @param UniversalExport $oUniversalExport
	 * @param array $aModules
	 * @param Title $oSpecialPage
	 * @param Title $oCurrentTitle
	 * @param array $aCurrentQueryParams
	 * @return boolean Always true to keep hook running
	 */
	public function onBSUniversalExportGetWidget( $oUniversalExport, &$aModules, $oSpecialPage, $oCurrentTitle, $aCurrentQueryParams ) {
		$aCurrentQueryParams['ue[module]'] = 'pdf';
		$aLinks = array();
		$aLinks['pdf-single-no-attachments'] = array(
			'URL'     => htmlspecialchars( $oSpecialPage->getLinkUrl( $aCurrentQueryParams ) ),
			'TITLE'   => wfMessage( 'bs-uemodulepdf-widgetlink-single-no-attachments-title' )->plain(),
			'CLASSES' => 'bs-uemodulepdf-single',
			'TEXT'    => wfMessage( 'bs-uemodulepdf-widgetlink-single-no-attachments-text' )->plain(),
		);

		wfRunHooks( 'BSUEModulePDFBeforeCreateWidget', array( $this, $oSpecialPage, &$aLinks, $aCurrentQueryParams ) );

		$oPdfView = new ViewBaseElement();
		$oPdfView->setAutoWrap( '<ul>###CONTENT###</ul>' );
		$oPdfView->setTemplate( '<li><a href="{URL}" rel="nofollow" title="{TITLE}" class="{CLASSES}">{TEXT}</a></li>' );#

		foreach ( $aLinks as $sKey => $aData ) {
			$oPdfView->addData( $aData );
		}

		$aModules[] = $oPdfView;
		return true;
	}

	public function onSkinTemplateOutputPageBeforeExec(&$skin, &$template){
		$aCurrentQueryParams = $this->getRequest()->getValues();
		if ( isset( $aCurrentQueryParams['title'] ) ) {
			$sTitle = $aCurrentQueryParams['title'];
		} else {
			$sTitle = '';
		}
		$sSpecialPageParameter = BsCore::sanitize( $sTitle, '', BsPARAMTYPE::STRING );
		$oSpecialPage = SpecialPage::getTitleFor( 'UniversalExport', $sSpecialPageParameter );
		if ( isset( $aCurrentQueryParams['title'] ) ) {
			unset( $aCurrentQueryParams['title'] );
		}
		$aCurrentQueryParams['ue[module]'] = 'pdf';
		$aContentActions = array(
			'id' => 'bs-ta-uemodulepdf',
			'href' => $oSpecialPage->getLinkUrl( $aCurrentQueryParams ),
			'title' => wfMessage( 'bs-uemodulepdf-widgetlink-single-no-attachments-title' )->text(),
			'text' => wfMessage('bs-uemodulepdf-widgetlink-single-no-attachments-text')->text(),
			'class' => 'icon-file-pdf'
		);

		$template->data['bs_title_actions'][] = $aContentActions;
		return true;
	}
}
