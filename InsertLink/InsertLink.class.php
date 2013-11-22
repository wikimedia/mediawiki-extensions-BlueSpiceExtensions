<?php

/**
 * BlueSpice for MediaWiki
 * Extension: InsertLink
 * Description: Dialogbox to enter a link.
 * Authors: Markus Glaser, Sebastian Ulbricht
 *
 * Copyright (C) 2010 Hallo Welt! � Medienwerkstatt GmbH, All rights reserved.
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
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit http://www.blue-spice.org
 */
/* Changelog
 * v1.20.0
 *
 * v1.0.0
 * -raised to stable
 * v0.1
 * -initial commit
 */

// Last review MRG (01.07.11 12:22)

/**
 * Class for link assistent
 * @package BlueSpice_Extensions
 * @subpackage InsertLink
 */
class InsertLink extends BsExtensionMW {

	/**
	 * Constructor of InsertLink
	 */
	public function __construct() {
		wfProfileIn('BS::' . __METHOD__);
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME => 'InsertLink',
			EXTINFO::DESCRIPTION => 'Dialogbox to enter a link.',
			EXTINFO::AUTHOR => 'Markus Glaser, Sebastian Ulbricht, Patric Wirth',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array('bluespice' => '2.22.0')
		);
		$this->mExtensionKey = 'MW::InsertLink';
		wfProfileOut('BS::' . __METHOD__);
	}

	/**
	 * Initialise the InsertLink extension
	 */
	protected function initExt() {
		wfProfileIn('BS::InsertLink::initExt');

		$this->setHook( 'BSExtendedEditBarBeforeEditToolbar' );
		$this->setHook( 'VisualEditorConfig' );

		BsConfig::registerVar('MW::InsertLink::ShowFilelink', true, BsConfig::LEVEL_PUBLIC | BsConfig::RENDER_AS_JAVASCRIPT, 'bs-insertlink-pref-ShowFilelink', 'toggle');
		BsConfig::registerVar('MW::InsertLink::UseFilelinkApplet', true, BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT);
		BsConfig::registerVar('MW::InsertLink::ExcludeNs', array(), BsConfig::LEVEL_PRIVATE);

		wfProfileOut('BS::InsertLink::initExt');
	}
	
	/**
	 * Hook Handler for VisualEditorConfig Hook
	 * @param Array $aConfigStandard reference
	 * @param Array $aConfigOverwrite reference
	 * @return boolean always true to keep hook alife
	 */
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite ) {
		$iIndexStandard = array_search( 'bssignature',$aConfigStandard["toolbar1"] );
		array_splice( $aConfigStandard["toolbar1"], $iIndexStandard + 1, 0, "bslink" );
		
		// Add context menu entry
		$aConfigStandard["contextmenu"] = str_replace('bsContextMenuMarker', 'bsContextMenuMarker bsContextLink bsContextUnlink', $aConfigStandard["contextmenu"] );
		return true;
	}

	public function onBSExtendedEditBarBeforeEditToolbar( &$aRows, &$aButtonCfgs ) {
		$this->getOutput()->addModuleStyles('ext.bluespice.insertlink.styles');
		$this->getOutput()->addModules('ext.bluespice.insertlink');
		
		$aRows[0]['dialogs'][40] = 'bs-editbutton-insertlink';

		$aButtonCfgs['bs-editbutton-insertlink'] = array(
			'tip' => wfMessage( 'bs-insertlink' )->plain()
		);
		return true;
	}

	/**
	 * Get the pages of a given namespace and put it to ajax output.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public static function getPage() {
		global $wgUser, $wgRequest;

		$aResult = array(
			'items' => array(),
			'success' => false
		);
		if( !$wgUser->isAllowed('edit') ) {
			return json_encode($aResult);
		}

		$iNs = $wgRequest->getInt( 'ns', 0 );
		$dbr = wfGetDB(DB_SLAVE);

		$rRes = $dbr->select(
			'page',
			array( 'page_id' ),
			array( 'page_namespace' => $iNs ),
			__METHOD__,
			array( 'ORDER BY' => 'page_title' )
		);

		while( $o = $dbr->fetchObject($rRes) ) {
			$oTitle = Title::newFromID($o->page_id);
			if( !$oTitle || !$oTitle->userCan('read')) continue;

			$aResult['items'][] = array(
				'name' => $oTitle->getText(),
				'label' => $o->page_id,
				'ns' => $iNs,
			);
		}
		$aResult['success'] = true;

		return json_encode( $aResult );
	}

	/**
	 * Get all visible namespaces and put it to ajax output.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public static function getNamespace() {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		global $wgContLang;

		$output = '{identifier:"name", items: [';
		foreach ($wgContLang->getNamespaces() as $ns) {
			$nsIndex = $wgContLang->getNsIndex( $ns );
			if (in_array($nsIndex, BsConfig::get('MW::InsertLink::ExcludeNs')))
				continue;
			$oTestTitle = Title::newFromText($ns . ':Test');
			if (is_object($oTestTitle) && $oTestTitle->userCan('read')) {
				$output .= '{name:"' . BsNamespaceHelper::getNamespaceName($nsIndex) . '", label:"' . BsNamespaceHelper::getNamespaceName($nsIndex) . '", ns:"' . $nsIndex . '"},';
			}
		}
		if (strrpos($output, ',') == strlen($output) - 1)
			$output = substr($output, 0, strlen($output) - 1);
		$output .= ']}';

		return $output;
		// TODO MRG (01.07.11 12:24): Use json_encode
	}
}