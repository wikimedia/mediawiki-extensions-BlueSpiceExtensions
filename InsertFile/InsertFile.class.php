<?php

/**
 * InsertFile extension for BlueSpice
 *
 * Dialogbox to upload files and enter a file link.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
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
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbricht
 * @author     Tobias Weichart <weichart@hallowelt.biz>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage InsertFile
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Class for file upload and management assistent
 * @package BlueSpice_Extensions
 * @subpackage InsertFile
 */
class InsertFile extends BsExtensionMW {

	/**
	 * Constructor of InsertFile
	 */
	public function __construct() {
		wfProfileIn( 'BS::' . __METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'InsertFile',
			EXTINFO::DESCRIPTION => 'bs-insertfile-desc',
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht, Robert Vogel',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'https://help.bluespice.com/index.php/InsertFile',
			EXTINFO::DEPS        => array(
				'bluespice' => '2.22.0'
			)
		);
		$this->mExtensionKey = 'MW::InsertFile';

		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * Initialise the InsertFile extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::' . __METHOD__ );

		$this->setHook( 'VisualEditorConfig' );
		$this->setHook( 'BSExtendedEditBarBeforeEditToolbar' );

		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * Hook Handler for VisualEditorConfig Hook
	 * @param Array $aConfigStandard reference
	 * @param Array $aConfigOverwrite reference
	 * @param Array &$aLoaderUsingDeps reference
	 * @return boolean always true to keep hook alife
	 */
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite, &$aLoaderUsingDeps ) {
		$aLoaderUsingDeps[] = 'ext.bluespice.insertFile';

		// TODO SW: use string as parameter !!
		$iIndexStandard = array_search( 'unlink',$aConfigStandard["toolbar1"] );
		array_splice( $aConfigStandard["toolbar1"], $iIndexStandard + 1, 0, "bsimage" );
		array_splice( $aConfigStandard["toolbar1"], $iIndexStandard + 2, 0, "bsfile" );

		$iIndexOverwrite = array_search( 'unlink',$aConfigOverwrite["toolbar2"] );
		array_splice( $aConfigOverwrite["toolbar2"], $iIndexOverwrite + 1, 0, "bsimage" );

		// Add context menu entry
		$aConfigStandard["contextmenu"] = str_replace('bsContextMenuMarker', 'bsContextMenuMarker bsContextImage', $aConfigStandard["contextmenu"] );
		return true;
	}

	public function onBSExtendedEditBarBeforeEditToolbar( &$aRows, &$aButtonCfgs ) {
		$this->getOutput()->addModuleStyles('ext.bluespice.insertFile.styles');
		$this->getOutput()->addModules('ext.bluespice.insertFile');

		$aRows[0]['dialogs'][20] = 'bs-editbutton-insertimage';
		$aRows[0]['dialogs'][30] = 'bs-editbutton-insertfile';

		$aButtonCfgs['bs-editbutton-insertimage'] = array(
			'tip' => wfMessage( 'bs-insertfile-insert-image' )->plain()
		);
		$aButtonCfgs['bs-editbutton-insertfile'] = array(
			'tip' => wfMessage( 'bs-insertfile-insert-file' )->plain()
		);
		return true;
	}
}