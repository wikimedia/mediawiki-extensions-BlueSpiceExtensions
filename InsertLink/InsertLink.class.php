<?php

/**
 * BlueSpice for MediaWiki
 * Extension: InsertLink
 * Description: Dialogbox to enter a link.
 * Authors: Markus Glaser, Sebastian Ulbricht
 *
 * Copyright (C) 2010 Hallo Welt! GmbH, All rights reserved.
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
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Tobias Weichart <weichart@hallowelt.biz>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage InsertFile
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

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
			EXTINFO::DESCRIPTION => 'bs-insertlink-desc',
			EXTINFO::AUTHOR => 'Markus Glaser, Sebastian Ulbricht, Patric Wirth',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'https://help.bluespice.com/index.php/InsertLink',
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

		BsConfig::registerVar( 'MW::InsertLink::EnableJava', false, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-insertlink-pref-enable-java', 'toggle' );

		wfProfileOut('BS::InsertLink::initExt');
	}

	/**
	 * Hook Handler for VisualEditorConfig Hook
	 * @param Array $aConfigStandard reference
	 * @param Array $aConfigOverwrite reference
	 * @param Array &$aLoaderUsingDeps reference
	 * @return boolean always true to keep hook alife
	 */
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite, &$aLoaderUsingDeps ) {
		$aLoaderUsingDeps[] = 'ext.bluespice.insertlink';

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
		$this->getOutput()->addJsConfigVars( 'bsInsertLinkEnableJava', BsConfig::get( 'MW::InsertLink::EnableJava' ) );
		return true;
	}

	public static function onLoadExtensionSchemaUpdates(DatabaseUpdater $updater) {
		//Create bsFileLnkChooser.jnlp File with proper path
		//Important: $wgServer should be public url, not localhost
		global $wgServer, $wgScriptPath, $IP;
		$bsFileLinkChooser = '
		<?xml version="1.0" encoding="utf-8"?>
		<jnlp codebase="" href="" >
		  <information>
			<title>BSFileLinkChooser</title>
			<vendor>HalloWelt GmbH</vendor>
			<homepage href="http://hallowelt.com"/>
			<description>Simple FileChooser Application</description>
			<description kind="short">BSFileLinkChooser</description>
			<offline-allowed/>
		  </information>
		  <security>
			  <all-permissions/>
		  </security>
		  <resources>
			<j2se version="1.6+"/>
			<jar href="'.$wgServer.$wgScriptPath.'/extensions/BlueSpiceExtensions/InsertLink/vendor/bsFileLinkChooser.jar" main="true"/>
		  </resources>
		  <application-desc main-class="bsFileLinkChooser.JWSFileChooser"/>
		</jnlp>';
		file_put_contents( __DIR__ . "/../../BlueSpiceFoundation/data/bsFileLinkChooser.jnlp", $bsFileLinkChooser);
		$updater->output( "InsertLink jnlp file created.\n" );
	}
}