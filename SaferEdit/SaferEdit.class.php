<?php
/**
 * SaferEdit extension for BlueSpice
 *
 * Intermediate saving of wiki edits.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, version 3.
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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Tobias Weichart <weichart@hallowelt.com>
 * @package    BlueSpice_Extensions
 * @subpackage SaferEdit
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
 * @filesource
 */

/**
 * Base class for SaferEdit extension
 * @package BlueSpice_Extensions
 * @subpackage SaferEdit
 */
class SaferEdit extends BsExtensionMW {

	private $aIntermediateEditsForCurrentTitle = null;

	/**
	 * Initialization of SaferEdit extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		BsConfig::registerVar( 'MW::SaferEdit::Interval', 10, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-saferedit-pref-interval', 'int' );
		BsConfig::registerVar( 'MW::SaferEdit::ShowNameOfEditingUser', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-saferedit-pref-shownameofeditinguser', 'toggle' );
		BsConfig::registerVar( 'MW::SaferEdit::WarnOnLeave', true, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-saferedit-pref-warnonleave', 'toggle' );

		$this->setHook( 'PageContentSaveComplete', 'clearSaferEdit' );
		$this->setHook( 'EditPage::showEditForm:initial', 'setEditSection' );
		$this->setHook( 'BSStateBarAddSortTopVars', 'onStatebarAddSortTopVars' );
		$this->setHook( 'BSStateBarBeforeTopViewAdd', 'onStateBarBeforeTopViewAdd' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BsAdapterAjaxPingResult' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		$sAction = $oSkin->getRequest()->getVal( 'action', 'view' );
		if ( !in_array( $sAction, array ( 'edit', 'submit', 'view', ) ) ) {
			return true;
		}

		$oOutputPage->addModules('ext.bluespice.saferedit.general');

		if( !in_array( $sAction, array ( 'edit', 'submit' ) ) ) {
			return true;
		}
		if ( !$oSkin->getTitle()->userCan( 'edit' ) ) {
			return true;
		}

		$oOutputPage->addModules('ext.bluespice.saferedit.editmode');
		return true;
	}

	/**
	 * Sets up required database tables
	 * @param DatabaseUpdater $updater Provided by MediaWikis update.php
	 * @return boolean Always true to keep the hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		global $wgDBtype, $wgExtNewTables, $wgExtNewIndexes;
		$sDir = __DIR__ . '/' . 'db' . '/' . $wgDBtype . '/';

		if( $wgDBtype == 'mysql') {
			$wgExtNewTables[]  = array( 'bs_saferedit', $sDir . 'SaferEdit.sql' );
			$wgExtNewIndexes[] = array( 'bs_saferedit', 'se_page_title',     $sDir . 'SaferEdit.patch.se_page_title.index.sql' );
			$wgExtNewIndexes[] = array( 'bs_saferedit', 'se_page_namespace', $sDir . 'SaferEdit.patch.se_page_namespace.index.sql' );

		} elseif( $wgDBtype == 'sqlite' ) {
			$sDir = __DIR__.DS.'db'.DS.'mysql'.DS;
			$wgExtNewTables[]  = array( 'bs_saferedit', $sDir . 'SaferEdit.sql' );
		} elseif( $wgDBtype == 'postgres' ) {
			$wgExtNewTables[]  = array( 'bs_saferedit', $sDir . 'SaferEdit.pg.sql' );
			/*
			$wgExtNewIndexes[] = array( 'bs_saferedit', 'se_page_title',     $sDir . 'SaferEdit.patch.se_page_title.index.pg.sql' );
			$wgExtNewIndexes[] = array( 'bs_saferedit', 'se_page_namespace', $sDir . 'SaferEdit.patch.se_page_namespace.index.pg.sql' );
			*/
		} elseif( $wgDBtype == 'oracle' ) {
			$wgExtNewTables[]  = array( 'bs_saferedit', $sDir . 'SaferEdit.oci.sql' );
			/*
			$wgExtNewIndexes[] = array( 'bs_saferedit', 'se_page_title',     $sDir . 'SaferEdit.patch.se_page_title.index.oci.sql' );
			$wgExtNewIndexes[] = array( 'bs_saferedit', 'se_page_namespace', $sDir . 'SaferEdit.patch.se_page_namespace.index.oci.sql' );
			*/
		}
		$updater->modifyExtensionField( 'bs_saferedit', 'se_text', $sDir . 'SaferEdit.patch.se_text.sql' );
		return true;
	}

	/**
	 * Clear all previously saved intermediate edits when article is saved
	 * Called by PageContentSaveComplete hook
	 * @param Article $article The article that is created.
	 * @param User $user User that saved the article.
	 * @param Content $content
	 * @param string $summary Edit summary.
	 * @param bool $minoredit Marked as minor.
	 * @param bool $watchthis Put on watchlist.
	 * @param int $sectionanchor Not in use any more.
	 * @param int $flags Bitfield.
	 * @param Revision $revision New revision object.
	 * @return bool true do let other hooked methods be executed
	 */
	public function clearSaferEdit( $article, $user, $content, $summary, $minoredit, $watchthis, $sectionanchor, $flags, $revision ) {
		$this->doClearSaferEdit( $user->getName(), $article->getTitle()->getDbKey(), $article->getTitle()->getNamespace() );
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStatebarAddSortTopVars'
	 * @param array $aSortTopVars
	 * @return boolean Always true to keep hook running
	 */
	public function onStatebarAddSortTopVars( &$aSortTopVars ) {
		$aSortTopVars['statebartopsaferedit'] = wfMessage( 'bs-saferedit-statebartopsaferedit' )->plain();
		$aSortTopVars['statebartopsafereditediting'] = wfMessage( 'bs-saferedit-statebartopsafereditediting' )->plain();
		return true;
	}

	/**
	 * Hook-Handler for Hook 'BSStateBarBeforeTopViewAdd'
	 * @param StateBar $oStateBar
	 * @param array $aTopViews
	 * @return boolean Always true to keep hook running
	 */
	public function onStateBarBeforeTopViewAdd( $oStateBar, &$aTopViews, $oUser, $oTitle ) {
		$aIntermediateEdits = $this->getIntermediateEditsForCurrentTitle( $oTitle );
		if ( empty( $aIntermediateEdits ) ) {
			return true;
		}

		foreach ( $aIntermediateEdits as $oEdit ) {
			//Please do not edit this agian! This is well calculated!
			//DO NOT WRITE RANDOM NUMBERS IN HERE
			$iInterval = BsConfig::get( 'MW::SaferEdit::Interval' )
				+ BsConfig::get( 'MW::PingInterval' )
				+ 1; //+1 secound response time is enought
			$iTime = wfTimestamp( TS_MW, time() - $iInterval );
			if ( $oEdit->se_user_name != $oUser->getName() && $oEdit->se_timestamp > $iTime ) {
				$aTopViews['statebartopsafereditediting'] = $this->makeStateBarTopSomeoneEditing( $oEdit->se_user_name );
			}
		}
		return true;
	}

	/**
	 * Loads intermediate edits
	 * @param Title $oTitle
	 * @return array
	 */
	public function getIntermediateEditsForCurrentTitle( $oTitle ) {
		if ( is_array( $this->aIntermediateEditsForCurrentTitle ) ) {
			return $this->aIntermediateEditsForCurrentTitle;
		}

		if ( is_null( $oTitle ) || !$oTitle->exists() ) {
			return $this->aIntermediateEditsForCurrentTitle = array();
		}

		$dbr = wfGetDB( DB_REPLICA );

		$rRes = $dbr->select(
			'bs_saferedit',
			'*',
			array(
				"se_page_title" => $oTitle->getDBkey(),
				"se_page_namespace" => $oTitle->getNamespace(),
			),
			__METHOD__,
			array( "ORDER BY" => "se_id DESC" )
		);

		while ( $row = $dbr->fetchObject( $rRes ) ) {
			$this->aIntermediateEditsForCurrentTitle[] = $row;
		}

		return $this->aIntermediateEditsForCurrentTitle;
	}

	/**
	 * Checks whether the current context is a section edit. Callback function for EditPage::showEditForm:initial hook.
	 * @param EditPage $editPage
	 * @return bool true do let other hooked methods be executed
	 */
	public function setEditSection( $editPage ) {
		$this->getOutput()->addJsConfigVars( 'bsSaferEditEditSection', $this->getRequest()->getVal( 'section', -1 ) );
		return true;
	}

	/**
	 *
	 * @param string $sText
	 * @param string $sUsername
	 * @param Title $oTitle
	 * @param integer $iSection
	 * @return boolean
	 */
	public static function saveUserEditing( $sUsername, $oTitle, $iSection = -1 ) {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		$db = wfGetDB( DB_MASTER );

		$sTable = 'bs_saferedit';
		$aFields = array(
			"se_timestamp" => wfTimestamp( TS_MW, time() )
		);
		$aConditions = array(
			"se_user_name" => $sUsername,
			"se_page_title" => $oTitle->getDBkey(),
			"se_page_namespace" => $oTitle->getNamespace(),
			"se_edit_section" => $iSection,
		);
		$aOptions = array( //needed for update reason
			'ORDER BY' => 'se_id DESC',
			'LIMIT' => 1,
		);

		if ( $oRow = $db->selectRow( $sTable, array( 'se_id' ), $aConditions, __METHOD__, $aOptions ) ) {
			$oTitle->invalidateCache();
			return $db->update(
				$sTable,
				$aFields,
				array( "se_id" => $oRow->se_id )
			);
		}

		$oTitle->invalidateCache();
		return $db->insert( $sTable, $aConditions + $aFields );
	}

	/**
	 * Actually delete all stored intermediate texts for a given user and page
	 * @param string $sUserName username of the user that edited a page
	 * @param string $sPageTitle title of the page
	 * @param int $iPageNamespace number of the namespace
	 * @return bool true do let other hooked methods be executed
	 */
	protected function doClearSaferEdit( $sUserName, $sPageTitle, $iPageNamespace ) {
		$oTitle = Title::newFromText( $sPageTitle, $iPageNamespace );
		if( empty($oTitle) ){
			return false;
		}

		$sPageTitle = str_replace( ' ', '_', $sPageTitle );
		$db = wfGetDB( DB_MASTER );
		$db->delete(
			'bs_saferedit',
			array(
				"se_user_name" => $sUserName,
				"se_page_title" => $oTitle->getDBkey(),
				"se_page_namespace" => $iPageNamespace,
			)
		);

		Title::newFromText( $sPageTitle, $iPageNamespace )->invalidateCache();
		return true;
	}

	// TODO MRG (04.05.11 01:09): Consider case where more than one editors are editing the page
	/**
	 * Renders a note that someone is editing a page to the statebar
	 * @param string $sUserName name of the user that is editing the page
	 * @return ViewStateBarTopElement View that is to be displayed in StateBar Top
	 */
	public function makeStateBarTopSomeoneEditing( $sUserName ) {
		global $wgScriptPath;
		$oSaferEditView = new ViewStateBarTopElement();

		$oSaferEditView->setKey( 'SaferEditSomeoneEditing' );
		$oSaferEditView->setIconSrc( $wgScriptPath.'/extensions/BlueSpiceExtensions/SaferEdit/resources/images/bs-infobar-editing-orange.png' );
		if ( BsConfig::get( 'MW::SaferEdit::ShowNameOfEditingUser' ) ) {
			$oSaferEditView->setIconAlt( wfMessage( 'bs-saferedit-user-editing', $sUserName )->text() );
			$oSaferEditView->setText( wfMessage( 'bs-saferedit-user-editing', $sUserName )->text() );
		} else {
			$oSaferEditView->setIconAlt( wfMessage( 'bs-saferedit-someone-editing' )->plain() );
			$oSaferEditView->setText( wfMessage( 'bs-saferedit-someone-editing' )->plain() );
		}

		return $oSaferEditView;
	}

	/**
	 * Hook-Handler for BS hook BsAdapterAjaxPingResult
	 * @global User $wgUser
	 * @param string $sRef
	 * @param array $aData
	 * @param integer $iArticleId
	 * @param array $aSingleResult
	 * @return boolean
	 */
	public function onBsAdapterAjaxPingResult( $sRef, $aData, $iArticleId, $sTitle, $iNamespace, $iRevision, &$aSingleResult ) {
		if ( !in_array( $sRef, array ( 'SaferEditIsSomeoneEditing', 'SaferEditSave' ) ) ) {
			return true;
		}

		$oTitle = Title::newFromText( $sTitle, $iNamespace );
		if ( is_null( $oTitle ) || !$oTitle->userCan( 'read' ) ) {
			return true;
		}
		$oUser = $this->getUser();

		switch( $sRef ) {
			case 'SaferEditIsSomeoneEditing':
				$aSingleResult['success'] = true;
				$aIntermediateEdits = $this->getIntermediateEditsForCurrentTitle( $oTitle );
				if ( empty( $aIntermediateEdits ) ) {
					return true;
				}

				$aSingleResult['someoneEditingView'] = $aSingleResult['safereditView'] = '';

				foreach ( $aIntermediateEdits as $oEdit ) {
					//Please do not edit this agian! This is well calculated!
					//DO NOT WRITE RANDOM NUMBERS IN HERE
					$iInterval = BsConfig::get( 'MW::SaferEdit::Interval' )
						+ BsConfig::get( 'MW::PingInterval' )
						+ 1; //+1 secound response time is enought
					$iDate = wfTimestamp( TS_MW, time() - $iInterval );
					if ( $oEdit->se_user_name != $oUser->getName() && $oEdit->se_timestamp > $iDate ) {
						$aSingleResult['someoneEditingView'] = $this->makeStateBarTopSomeoneEditing( $oEdit->se_user_name )->execute();
					}
				}

				break;
			case 'SaferEditSave':
				if( !isset($aData[0]['bUnsavedChanges']) ) {
					return true;
				}
				if( $aData[0]['bUnsavedChanges'] !== true ) {
					return true;
				}

				$iSection = empty( $aData[0]['section'] ) ? -1 : $aData[0]['section'];

				$aSingleResult['success'] = $this->saveUserEditing(
					$oUser->getName(), $oTitle,
					$iSection
				);

				break;
		}

		return true;
	}
}
