<?php

/**
 * InsertCategory extension for BlueSpice
 *
 * Dialogbox to enter a category link.
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
 * @author     Stefan Widmann <widmann@hallowelt.biz>
 * @version    2.22.0
 * @package    BlueSpice_Extensions
 * @subpackage InsertCategory
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
/* Changelog
 * v1.20.0
 *
 * v1.0.0
 * - Raised to stable
 * - Code review
 * v0.1
 * FIRST CHANGES
 */

// Last review RBV (30.06.11 8:40)

/**
 * Class for category management assistent
 * @package BlueSpice_Extensions
 * @subpackage InsertCategory
 */
class InsertCategory extends BsExtensionMW {

	/**
	 * Constructor of InsertCategory
	 */
	public function __construct() {
		wfProfileIn( 'BS::' . __METHOD__ );
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'InsertCategory',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-insertcategory-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht, Stefan Widmann',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::InsertCategory';
		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * Initialise the InsertCategory extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::' . __METHOD__ );
		$this->setHook( 'SkinTemplateNavigation' );
		$this->setHook( 'BSExtendedEditBarBeforeEditToolbar' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'VisualEditorConfig' );

		BsConfig::registerVar( 'MW::InsertCategory::WithParents', false, BsConfig::LEVEL_PUBLIC | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_BOOL, 'bs-insertcategory-pref-withparents', 'toggle' );

		wfProfileOut( 'BS::' . __METHOD__ );
	}

	/**
	 * adds the button that was added in the javascript
	 * @param type $aConfigStandard
	 * @param type $aConfigOverwrite
	 * @param Array &$aLoaderUsingDeps reference
	 * @return boolean
	 */
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite, &$aLoaderUsingDeps ) {
		$aLoaderUsingDeps[] = 'ext.bluespice.insertcategory';

		$iIndexStandard = array_search( 'unlink',$aConfigStandard["toolbar1"] );
		array_splice( $aConfigStandard["toolbar1"], $iIndexStandard + 1, 0, "hwinsertcategory" );

		$iIndexOverwrite = array_search( 'unlink',$aConfigOverwrite["toolbar2"] );
		array_splice( $aConfigOverwrite["toolbar2"], $iIndexOverwrite + 1, 0, "hwinsertcategory" );
		return true;
	}

	/**
	 *
	 * @param OutputPage $out
	 * @param Skin $skin
	 * @return boolean
	 */
	public static function onBeforePageDisplay( &$out, &$skin ) {
		$out->addModuleStyles('ext.bluespice.insertcategory.styles');
		$out->addModules('ext.bluespice.insertcategory');
		return true;
	}

	public function onBSExtendedEditBarBeforeEditToolbar( &$aRows, &$aButtonCfgs ) {
		$this->getOutput()->addModuleStyles('ext.bluespice.insertcategory.styles');
		$this->getOutput()->addModules('ext.bluespice.insertcategory');

		$aRows[0]['dialogs'][10] = 'bs-editbutton-insertcategory';

		$aButtonCfgs['bs-editbutton-insertcategory'] = array(
			'tip' => wfMessage( 'bs-insertcategory-insertcat' )->plain()
		);
		return true;
	}

	public static function addCategoriesToArticle( $iArticleId ) {
		if ( BsCore::checkAccessAdmission( 'read' ) === false ) {
			return FormatJson::encode( array( 'success' => false ) );
		}

		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return FormatJson::encode(
				array(
					'success' => false,
					'msg' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				)
			);
		}

		$sTags = RequestContext::getMain()->getRequest()->getVal( 'categories', '' );
		$aTags = explode( ',', $sTags );

		$oTitle = Title::newFromID( $iArticleId );
		if ( $oTitle->exists() ) {
			$sCat = BsNamespaceHelper::getNamespaceName( NS_CATEGORY );
			$sText = BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle, Revision::RAW );

			foreach ( $aTags as $sTag ) {
				if ( preg_match( '#\[\['.$sCat.':'.$sTag.'\]\]#i', $sText ) ) continue;
				$sText .= "\n[[".$sCat.":$sTag]]";
			}

			$oArticle = new Article( $oTitle );
			$oArticle->doEdit( $sText, '', EDIT_UPDATE | EDIT_MINOR );
		}

		return FormatJson::encode( array( 'success' => true ) );
	}

	/**
	 * Adds the "Insert category" menu entry in view mode
	 * @param SkinTemplate $sktemplate
	 * @param array $links
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateNavigation( &$sktemplate, &$links ) {
		if ( $this->getRequest()->getVal( 'action', 'view') != 'view' ) {
			return true;
		}
		if ( !$this->getTitle()->userCan( 'edit' ) ) {
			return true;
		}
		$links['actions']['insert_category'] = array(
			'text' => wfMessage( 'bs-insertcategory-insertcat' )->text(),
			'href' => '#',
			'class' => false,
			'id' => 'ca-insertcategory'
		);

		return true;
	}
}