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
			EXTINFO::DESCRIPTION => 'Dialogbox to enter a category link.',
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
		$this->setHook( 'SkinTemplateContentActions' );
		$this->setHook( 'SkinTemplateNavigation::Universal', 'onSkinTemplateNavigationUniversal' );
		$this->setHook( 'BSExtendedEditBarBeforeEditToolbar' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'VisualEditorConfig' );

		BsConfig::registerVar( 'MW::InsertCategory::CategoryNamespaceName', 'Category', BsConfig::LEVEL_PRIVATE | BsConfig::RENDER_AS_JAVASCRIPT );
		BsConfig::registerVar( 'MW::InsertCategory::WithParents', false, BsConfig::LEVEL_PUBLIC | BsConfig::RENDER_AS_JAVASCRIPT | BsConfig::TYPE_BOOL, 'bs-insertcategory-pref-WithParents', 'toggle' );

		wfProfileOut( 'BS::' . __METHOD__ );
	}
	
	/**
	 * adds the button that was added in the javascript
	 * @param type $aConfigStandard
	 * @param type $aConfigOverwrite
	 * @return boolean 
	 */
	public function onVisualEditorConfig( &$aConfigStandard, &$aConfigOverwrite ) {
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
			'tip' => wfMessage( 'bs-insertcategory-insert_category' )->plain()
		);
		return true;
	}

	public static function addCategoriesToArticle( $iArticleId ) {
		if ( BsCore::checkAccessAdmission( 'read' ) === false )
				return json_encode( array( 'success' => false ) );

		if ( wfReadOnly() ) {
			global $wgReadOnly;
			return json_encode( array(
				'success' => false,
				'msg' => wfMessage( 'bs-readonly', $wgReadOnly )->plain()
				) );
		}
		global $wgRequest;
		
		$sTags = $wgRequest->getVal( 'categories', '' );
		$aTags = explode( ',', $sTags );

		$oTitle = Title::newFromID( $iArticleId );
		if ( $oTitle ) {
			$oArticle = new Article( $oTitle );
			$sText = $oArticle->getRawText();
			$sText = preg_replace( '%(<br \/>)*\[\['.BsNamespaceHelper::getNamespaceName( NS_CATEGORY ).':(.)+?\]\]\n?%i', '', $sText );
			$sText = trim( $sText );
			foreach( $aTags as $sTag ) {
				$sText .= "\n\n[[".BsNamespaceHelper::getNamespaceName( NS_CATEGORY ).":$sTag]]";
			}

			$oArticle->doEdit( $sText, '', EDIT_UPDATE | EDIT_MINOR );
		}

		return json_encode( array( 'success' => true ) );
	}

	/**
	 * MediaWiki ContentActions hook. For more information please refer to <mediawiki>/docs/hooks.txt
	 * @global Title $wgTitle
	 * @param Array $aContentActions This array is used within the skin to render the content actions menu
	 * @return Boolean Always true for it is a MediwWiki Hook callback.
	 */
	public function onSkinTemplateContentActions( &$aContentActions) {
		global $wgTitle;
		if( $this->getRequest()->getVal( 'action', 'view') != 'view' ) return true;
		if( !$wgTitle->userCan( 'edit' ) ) return true;

		$links = array( 'actions' => array() );
		$this->onSkinTemplateNavigationUniversal( null, $links );
		$aContentActions['insert_category'] = $links['actions']['insert_category'];
		
		return true;
	}
	
	/**
	 * 
	 * @param type $skin
	 * @param type $links
	 * @return boolean
	 */
	public function onSkinTemplateNavigationUniversal( $skin, &$links ) {
		if( $this->getRequest()->getVal( 'action', 'view') != 'view' ) return true;
		if ( !$this->getTitle()->isContentPage() ) return true;
		if ( !$this->getTitle()->userCan( 'edit' ) ) return true;
		$links['actions']['insert_category'] = array(
			"text" => wfMessage( 'bs-insertcategory-insert_category' )->plain(),
			"href" => '#',
			"class" => false
		);

		return true;
	}

	/**
	 * Calculate the dataset of the category tree and put it to ajax output.
	 */
//	public static function getCategory() {
//		if ( BsCore::checkAccessAdmission( 'read' ) === false ) return true;
//		$aTreeData = array( );
//		$aTempData = array(
//			'parents' => array( ),
//			'childs' => array( )
//		);
//		$aReferences = array( );
//		$aCategories = array( );
//
//		$dbr = wfGetDB( DB_SLAVE );
//
//		/**
//		 * Select all categories and their parent categories from categorylinks
//		 */
//		$res = $dbr->select(
//			// Tables
//			array( 'page', 'categorylinks' ),
//			// Fields
//			array( 'page_title AS cat_title', 'cl_to AS parent_title' ),
//			// Conditions
//			array( 'page_namespace' => NS_CATEGORY),
//			__METHOD__,
//			// Options
//			array( 'ORDER BY page_title' ),
//			// Joins
//			array( 'categorylinks' => array( 'JOIN', 'page_id = cl_from' ), )
//		);
//
//		while ( $row = $res->fetchRow() ) {
//			// when a category don't have a parent category it is a parent category by itself
//			if ( !$row[ 'parent_title' ] ) {
//				$aTempData[ 'parents' ][ $row[ 'cat_title' ] ] = null;
//			}
//			// otherwise it has to be places under its parent category
//			else {
//				$aTempData[ 'childs' ][ $row[ 'cat_title' ] ][ ] = $row[ 'parent_title' ];
//				// we save, which categories we allready did sort
//			}
//			$aCategories[ ] = "'" . addslashes( $row[ 'cat_title' ] ) . "'";
//		}
//
//			$aCond = array(
//			'page_namespace' => NS_CATEGORY
//			);
//		// categories we allready did sort, we don't want to get with the next queries
//		$sCategoryCondition = '1';
//		if ( count( $aCategories ) ) {
//			$aCond[] = "page_title NOT IN (" . implode( ', ', $aCategories ) . ")";
//		}
//		/**
//		 * Select all categories which have a category page and were not sorted allready before.
//		 */
//		$res = $dbr->select(
//			// Tables
//			array( 'page' ),
//			// Fields
//			array(
//			'page_title AS cat_title'
//			),
//			// Conditions
//			$aCond,
//			__METHOD__
//		);
//
//		/**
//		 * Because we sorted all sub categories allready, all categories, we've found now, have to be parent categories
//		 */
//		while ( $row = $res->fetchRow() ) {
//			$aTempData[ 'parents' ][ $row[ 'cat_title' ] ] = NULL;
//			// we save the categories we found too
//			$aCategories[ ] = "'" . addslashes( $row[ 'cat_title' ] ) . "'";
//		}
//
//		// categories we allready did sort, we don't want to get with the next query
//		$sCategoryCondition = '';
//		$aCond = array();
//		if ( count( $aCategories ) ) {
//			$aCond[] = "cl_to NOT IN (" . implode( ', ', $aCategories ) . ")";
//		}
//
//		/**
//		 * At last we get all categories from categorylinks, we've not sorted now.
//		 * That have to be parent categories too but they seems to have no category page yet.
//		 */
//		$res = $dbr->select(
//			// Tables
//			array( 'categorylinks' ),
//			// Fields
//			array( 'cl_to AS cat_title' ),
//			// Conditions
//			$aCond, __METHOD__,
//			// Options
//			array( 'GROUP BY' => 'cl_to' )
//		);
//
//		/**
//		 * Because we sorted all sub categories allready, all categories, we've found now, have to be parent categories
//		 */
//		while ( $row = $res->fetchRow() ) {
//			$aTempData[ 'parents' ][ $row[ 'cat_title' ] ] = NULL;
//			// we save the categories we found too
//			$aCategories[ ] = "'" . addslashes( $row[ 'cat_title' ] ) . "'";
//		}
//
//		// now we sort the categories alphabetical
//		if ( isset( $aTempData[ 'parents' ] ) && is_array( $aTempData[ 'parents' ] ) ) {
//			ksort( $aTempData[ 'parents' ], SORT_LOCALE_STRING );
//		}
//		if ( isset( $aTempData[ 'childs' ] ) && is_array( $aTempData[ 'childs' ] ) ) {
//			ksort( $aTempData[ 'childs' ], SORT_LOCALE_STRING );
//		}
//
//		// initial id for the category tree entries
//		$iId = 1;
//
//		// first we add the parent categories to the tree
//		if ( isset( $aTempData[ 'parents' ] ) ) {
//			foreach ( $aTempData[ 'parents' ] as $sName => $sParentName ) {
//				// we create an entry for this category in the tree
//				$aTreeData[ $sName ] = array(
//					'id' => $iId,
//					'name' => addslashes( $sName ),
//					'children' => null
//				);
//				// and save a reference to the entry in references
//				$aReferences[ $sName ] = & $aTreeData[ $sName ];
//
//				$iId++;
//			}
//		}
//
//		/**
//		 * Now we sort all child categories to their parents.
//		 * Because all child categories should have at least one parent, we don't put the entries
//		 * directly to the tree but to the children array of the reference of their parents.
//		 */
//		if ( count( $aTempData[ 'childs' ] ) ) {
//			foreach ( $aTempData[ 'childs' ] as $sName => $aParentNames ) {
//				// if there is no entry for this category in references, we create one
//				if ( !isset( $aReferences[ $sName ] ) ) {
//					$aReferences[ $sName ] = array(
//						'id' => $iId,
//						'name' => addslashes( $sName ),
//						'children' => null
//					);
//				}
//				// otherwise we update the entry data
//				else {
//					$aReferences[ $sName ][ 'id' ] = $iId;
//					$aReferences[ $sName ][ 'name' ] = $sName;
//				}
//				// we save a reference of this category as a children of every of its parents
//				foreach ( $aParentNames as $sParentName ) {
//					$aReferences[ $sParentName ][ 'children' ][ ] = &$aReferences[ $sName ];
//				}
//				$iId++;
//			}
//		}
//
//		/**
//		 * The treedata array should hold all categories now, which are linked to another over references.
//		 * BsCore::buildTree build up the data to the right format for an ExtJS TreePanel now.
//		 */
//		return BsCore::buildTree( $aTreeData );
//	}

}