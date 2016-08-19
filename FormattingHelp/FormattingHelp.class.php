<?php
/**
 * FormattingHelp extension for BlueSpice
 *
 * Displays a help screen in the wiki edit view.
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
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage FormattingHelp
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for FormattingHelp extension
 * @package BlueSpice_Extensions
 * @subpackage FormattingHelp
 */
class FormattingHelp extends BsExtensionMW {
	/**
	 * Initialization of Blog extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook('BSExtendedEditBarBeforeEditToolbar');
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function onBSExtendedEditBarBeforeEditToolbar( &$aRows, &$aButtonCfgs ) {
		$this->getOutput()->addModuleStyles('ext.bluespice.formattinghelp.styles');
		$this->getOutput()->addModules('ext.bluespice.formattinghelp');

		$aRows[0]['editing'][20] = 'bs-editbutton-formattinghelp';

		$aButtonCfgs['bs-editbutton-formattinghelp'] = array(
			'tip' => wfMessage( 'bs-formattinghelp-formatting' )->plain()
		);
		return true;
	}
}
