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
 * 
 * Version information
 * $LastChangedDate: 2013-06-14 15:22:51 +0200 (Fr, 14 Jun 2013) $
 * $LastChangedBy: tweichart $
 * $Rev: 9747 $
 * $Id: InsertLink.class.php 9747 2013-06-14 13:22:51Z tweichart $
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
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['InsertLink'] = dirname( __FILE__ ) . '/InsertLink.i18n.php';
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME => 'InsertLink',
			EXTINFO::DESCRIPTION => 'Dialogbox to enter a link.',
			EXTINFO::AUTHOR => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION => '1.22.0 ($Rev: 9747 $)',
			EXTINFO::STATUS => 'stable',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array('bluespice' => '1.22.0')
		);
		$this->mExtensionKey = 'MW::InsertLink';
		wfProfileOut('BS::' . __METHOD__);
	}

	/**
	 * Initialise the InsertLink extension
	 */
	protected function initExt() {
		wfProfileIn('BS::InsertLink::initExt');

		$this->setHook('AlternateEdit');
		$this->setHook('BeforePageDisplay');

		// TODO MRG (27.09.10 13:56): $this->mAdapter
		$this->mAdapter->addRemoteHandler('InsertLink', $this, 'getNamespace', 'edit');
		$this->mAdapter->addRemoteHandler('InsertLink', $this, 'getInterwiki', 'edit');
		$this->mAdapter->addRemoteHandler('InsertLink', $this, 'getLanguage', 'edit');
		$this->mAdapter->addRemoteHandler('InsertLink', $this, 'getTemplate', 'edit');
		$this->mAdapter->addRemoteHandler('InsertLink', $this, 'getPage', 'edit');

		BsConfig::registerVar('MW::InsertLink::ShowFilelink', true, BsConfig::LEVEL_PUBLIC | BsConfig::RENDER_AS_JAVASCRIPT, 'bs-insertlink-pref-ShowFilelink', 'toggle');
		BsConfig::registerVar('MW::InsertLink::UseFilelinkApplet', true, BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT);
		BsConfig::registerVar('MW::InsertLink::ExcludeNs', array(), BsConfig::LEVEL_PRIVATE);
		wfProfileOut('BS::InsertLink::initExt');
	}

	/**
	 * Adds the 'ext.bluespice.insertcategory' module to the OutputPage
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public function onBeforePageDisplay($out, $skin) {
		if ($out->getRequest()->getVal('action') != 'edit')
			return true;
		$out->addModules('ext.bluespice.insertlink');
		return true;
	}

	/**
	 * Add the action button to MediaWiki editor.
	 * @return type bool
	 */
	public function onAlternateEdit() {
		$this->mAdapter->addEditButton('InsertLink', array(
			'id' => 'il_button',
			'msg' => wfMsg('bs-insertlink'),
			'image' => '/extensions/BlueSpiceExtensions/InsertLink/resources/images/btn_link.gif',
			'onclick' => "LinkChooser.show();"
		));

		global $wgContLang;
		$aNamespaces = $wgContLang->getNamespaces();
		BsConfig::registerVar('MW::InsertLink::EscapeNs', array($aNamespaces[NS_FILE], $aNamespaces[NS_CATEGORY]), BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT);
		return true;
	}

	/**
	 * Get the pages of a given namespace and put it to ajax output.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public function getPage(&$output) {
		// TODO MRG (27.09.10 14:02): Security: Validator und Sanitizer und Fehlermeldung, wenns falsch ist.
		$iNs = BsCore::getParam('ns', false, BsPARAM::REQUEST | BsPARAMTYPE::INT);
		$wgContLang = $this->mAdapter->get('ContLang');
		$sNamespace = $wgContLang->getNsText($iNs);
		$oTestTitle = Title::newFromText($sNamespace . ':Test');

		if ($iNs === false) {
			$output = '{items: []}';
		}

		$output = '{items: [';
		if (is_object($oTestTitle) && $oTestTitle->userCan('read')) {
			$dbr = & wfGetDB(DB_SLAVE);
			$res = $dbr->select(array('page'), array('page_title', 'page_namespace', 'page_id'), array('page_namespace' => $iNs), null, array('ORDER BY' => 'page_title')); //, array('page_namespace'=>$ns)
			while ($page = $dbr->fetchRow($res)) {
				//TODO: User can see (and link to) pages whose very existence should be kept secret.
				$output .= '{name:"' . addslashes($page['page_title']) . '", label:"' . $page['page_id'] . '", ns:"' . $page['page_namespace'] . '"},';
			}
			if (strrpos($output, ',') == strlen($output) - 1)
				$output = substr($output, 0, strlen($output) - 1);
		}
		$output .= ']}';
		// TODO MRG (01.07.11 12:24): use json_encode
	}

	/**
	 * Get all visible namespaces and put it to ajax output.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public function getNamespace(&$output) {
		$contLang = $this->mAdapter->get('ContLang');

		$output = '{identifier:"name", items: [';
		foreach ($contLang->getNamespaces() as $ns) {
			$nsIndex = $contLang->getNsIndex($ns);
			if (in_array($nsIndex, BsConfig::get('MW::InsertLink::ExcludeNs')))
				continue;
			$oTestTitle = Title::newFromText($ns . ':Test');
			if (is_object($oTestTitle) && $oTestTitle->userCan('read')) {
				$output .= '{name:"' . BsAdapterMW::getNamespaceName($nsIndex) . '", label:"' . BsAdapterMW::getNamespaceName($nsIndex) . '", ns:"' . $nsIndex . '"},';
			}
		}
		if (strrpos($output, ',') == strlen($output) - 1)
			$output = substr($output, 0, strlen($output) - 1);
		$output .= ']}';
		// TODO MRG (01.07.11 12:24): Use json_encode
	}

	/**
	 * Get the interwiki dataset and put it to ajax output.
	 * @param type $output The ajax output which have to be valid JSON.
	 */
	public function getInterwiki(&$output) {
		// TODO MRG (27.09.10 14:06): coding convention: kein unterstrich
		$output = $this->getInterwikiData();
	}

	/**
	 * Calculates the interwiki dataset with ot without translation.
	 * @return string the dataset in JSON format
	 */
	protected function getInterwikiData() {
		$output = '{identifier:"name", items: [';
		$dbr = & wfGetDB(DB_SLAVE);
		$res = $dbr->select('interwiki', array('iw_prefix'));
		while ($iw = $dbr->fetchRow($res)) {
			$output .= '{name:"' . $iw['iw_prefix'] . '", label:"' . $iw['iw_prefix'] . '"},';
		}
		// TODO MRG (27.09.10 14:09): das kommt so oft vor, wir sollten das abstrahieren
		if (strrpos($output, ',') == strlen($output) - 1)
			$output = substr($output, 0, strlen($output) - 1);
		$output .= ']}';
		return $output;
		// TODO MRG (01.07.11 12:24): use json_encode
	}

}