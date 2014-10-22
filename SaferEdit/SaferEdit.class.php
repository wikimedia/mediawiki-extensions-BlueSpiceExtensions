<?php
/**
 * SaferEdit extension for BlueSpice
 *
 * Intermediate saving of wiki edits.
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
 * @version    2.22.0
 * @package    BlueSpice_Extensions
 * @subpackage SaferEdit
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - MediaWiki I18N
 * v1.1.0
 * - Added indexes to table for improved performance
 * v1.0.0
 * - Raised to stable, due to EXTINFO::STATUS
 * - Code Review
 * v0.1
 * - initial commit
 */

/**
 * Base class for SaferEdit extension
 * @package BlueSpice_Extensions
 * @subpackage SaferEdit
 */
class SaferEdit extends BsExtensionMW {

	private $aIntermediateEditsForCurrentTitle = null;
	/**
	 * Constructor of SaferEdit class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'SaferEdit',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-saferedit-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
										'bluespice'   => '2.22.0',
										'StateBar' => '2.22.0'
										)
		);
		$this->mExtensionKey = 'MW::SaferEdit';

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of SaferEdit extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );

		BsConfig::registerVar( 'MW::SaferEdit::UseSE', true, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-saferedit-pref-usese', 'toggle' );
		//BsConfig::registerVar( 'MW::SaferEdit::HasTexts', false, BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-saferedit-pref-HasTexts', 'toggle' );
		BsConfig::registerVar( 'MW::SaferEdit::EditSection', -1, BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_INT|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-saferedit-pref-EditSection', 'int' );
		BsConfig::registerVar( 'MW::SaferEdit::Interval', 10, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-saferedit-pref-interval', 'int' );
		BsConfig::registerVar( 'MW::SaferEdit::ShowNameOfEditingUser', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-saferedit-pref-shownameofeditinguser', 'toggle' );
		BsConfig::registerVar( 'MW::SaferEdit::WarnOnLeave', true, BsConfig::LEVEL_USER|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-saferedit-pref-warnonleave', 'toggle' );

		$this->setHook( 'ArticleSaveComplete', 'clearSaferEdit' );
		//$this->setHook( 'SkinTemplateOutputPageBeforeExec', 'parseSaferEdit' );
		$this->setHook( 'EditPage::showEditForm:initial', 'setEditSection' );
		$this->setHook( 'BSStateBarAddSortTopVars', 'onStatebarAddSortTopVars' );
		$this->setHook( 'BSStateBarBeforeTopViewAdd', 'onStateBarBeforeTopViewAdd' );
		$this->setHook( 'BeforeInitialize' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BsAdapterAjaxPingResult' );

		$this->mCore->registerBehaviorSwitch( 'NOSAFEREDIT', array( $this, 'noSaferEditCallback' ) ) ;

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		if ( BsExtensionManager::isContextActive( 'MW::SaferEdit' ) === false ) return true;
		$oOutputPage->addModules('ext.bluespice.saferedit.general');

		if ( BsExtensionManager::isContextActive( 'MW::SaferEditEditMode' ) === false ) return true;
		$oOutputPage->addModules('ext.bluespice.saferedit.editmode');

		$this->parseSaferEdit( $oOutputPage->getTitle() );
		return true;
	}

	/**
	 * Sets up required database tables
	 * @param DatabaseUpdater $updater Provided by MediaWikis update.php
	 * @return boolean Always true to keep the hook running
	 */
	public static function getSchemaUpdates( $updater ) {
		global $wgDBtype, $wgExtNewTables, $wgExtNewIndexes;
		$sDir = __DIR__.DS.'db'.DS.$wgDBtype.DS;

		if( $wgDBtype == 'mysql' ) {
			$wgExtNewTables[]  = array( 'bs_saferedit', $sDir . 'SaferEdit.sql' );
			$wgExtNewIndexes[] = array( 'bs_saferedit', 'se_page_title',     $sDir . 'SaferEdit.patch.se_page_title.index.sql' );
			$wgExtNewIndexes[] = array( 'bs_saferedit', 'se_page_namespace', $sDir . 'SaferEdit.patch.se_page_namespace.index.sql' );

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
		return true;
	}

	/**
	 * Callback function for NOSAFEREDIT behavior switch
	 * @return bool always true
	 */
	public function noSaferEditCallback() {
		BsExtensionManager::removeContext('MW::SaferEdit');
		BsExtensionManager::removeContext('MW::SaferEditEditMode');
		BsConfig::set( 'MW::SaferEdit::Use', false );
		return true;
	}

	/**
	 * Clear all previously saved intermediate edits when article is saved
	 * Called by ArticleSaveComplete hook
	 * @param Article $article The article that is created.
	 * @param User $user User that saved the article.
	 * @param string $text New text.
	 * @param string $summary Edit summary.
	 * @param bool $minoredit Marked as minor.
	 * @param bool $watchthis Put on watchlist.
	 * @param int $sectionanchor Not in use any more.
	 * @param int $flags Bitfield.
	 * @param Revision $revision New revision object.
	 * @return bool true do let other hooked methods be executed
	 */
	public function clearSaferEdit( $article, $user, $text, $summary, $minoredit, $watchthis, $sectionanchor, $flags, $revision ) {
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
		if ( empty( $aIntermediateEdits ) ) return true;

		foreach ( $aIntermediateEdits as $oEdit ) {
			if ( BsConfig::get( 'MW::SaferEdit::UseSE' ) !== false && $oEdit->se_user_name == $oUser->getName()
				&& trim( $oEdit->se_text ) != BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle ) ) {

				$aTopViews['statebartopsaferedit'] = $this->makeStateBarTopSaferEdit( Article::newFromID($oTitle->getArticleID()), $oEdit->se_edit_section );
			}

			$iTime = wfTimestamp( TS_MW, time() - BsConfig::get( 'MW::SaferEdit::Interval' ) * 10 );
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
		if ( is_array( $this->aIntermediateEditsForCurrentTitle ) ) return $this->aIntermediateEditsForCurrentTitle;

		if ( is_null( $oTitle ) || !$oTitle->exists() ) {
			return $this->aIntermediateEditsForCurrentTitle = array();
		}

		$dbr = wfGetDB( DB_SLAVE );

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

	function parseSaferEdit( $oTitle ) {
		$oUser = $this->getUser();

		$aIntermediateEdits = $this->getIntermediateEditsForCurrentTitle( $oTitle );
		if ( empty( $aIntermediateEdits ) ) return false;

		foreach ( $aIntermediateEdits as $oEdit ) {
			$sOrigText = trim( BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle ) );

			if ( $oEdit->se_edit_section != -1 ) {
				global $wgParser;
				$sOrigText = $wgParser->getSection( $sOrigText, $oEdit->se_edit_section );
			}

			if ( strcmp( $sOrigText, trim( $oEdit->se_text ) ) === 0 ) {
				$this->doClearSaferEdit( $oEdit->se_user_name, $oTitle->getPrefixedDBkey(), $oTitle->getNamespace() );
				$this->aIntermediateEditsForCurrentTitle = null; //force reload
				return false;
			}

			if ( $oEdit->se_user_name == $oUser->getName() ) {
				if ( $this->getRequest()->getVal( 'action', 'view' ) == 'edit' ) {
					$this->getOutput()->addJsConfigVars( 'bsSaferEditHasTexts', true );
				}
			}
		}

		return false;
	}

	/**
	 * Checks whether the current context is a section edit. Callback function for EditPage::showEditForm:initial hook.
	 * @param EditPage $editPage
	 * @return bool true do let other hooked methods be executed
	 */
	public function setEditSection( $editPage ) {
		BsConfig::set( 'MW::SaferEdit::EditSection', $this->getRequest()->getVal('section', -1) );
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
	public static function saveText( $sText, $sUsername, $oTitle, $iSection = -1 ) {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		$db = wfGetDB( DB_MASTER );

		$sTable = 'bs_saferedit';
		$aFields = array(
			"se_timestamp" => date( "YmdHis" ),
			"se_text" => $sText,
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
			if ( empty( $sText ) ) unset( $aFields['se_text'] );

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
	 * User decided not to use saved texts, so they are dismissed. Called as AJAX function
	 * @return bool true do let other hooked methods be executed
	 */
	public static function doCancelSaferEdit( $sUserName, $sPageTitle, $iPageNamespace ) {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;

		if ( BsExtensionManager::getExtension( 'SaferEdit' )->doClearSaferEdit( $sUserName, $sPageTitle, $iPageNamespace ) ) {
			return 'OK';
		} else {
			return 'ERR';
		}
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
		if( empty($oTitle) ) return false;

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

	/**
	 * Delivers an intermediately saved text. Called as AJAX function
	 * @param string $sOutput JSON encoded string with the renderd HTML and wiki text of a intermedia saving
	 * @return bool true do let other hooked methods be executed
	 */
	public static function getLostTexts( $sUname, $sPageTitle, $iPageNamespace, $iSection ) {
		if ( BsCore::checkAccessAdmission( 'edit' ) === false ) return true;
		$oTitle = Title::newFromText( $sPageTitle, $iPageNamespace );

		$oDbw = wfGetDB( DB_SLAVE );
		$res = $oDbw->select(
			'bs_saferedit',
			'se_text, se_timestamp, se_edit_section',
			array(
				"se_page_title" => $oTitle->getDBkey(),
				"se_page_namespace" => $iPageNamespace,
				"se_user_name" => $sUname
			),
			'',
			array( "ORDER BY" => "se_id DESC" )
		);

		if ( $oDbw->numRows( $res ) > 0 ) {
			$row = $oDbw->fetchRow( $res );

			$sOrigText = BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle );

			if ( $iSection != -1 ) {
				global $wgParser;
				$sOrigText = $wgParser->getSection( $sOrigText, $iSection );
			}

			$oLang = RequestContext::getMain()->getLanguage();
			if ( $iSection != $row['se_edit_section'] ) {
				if ( $row['se_edit_section'] == '-1' ) {
					$sEditUrl = $oTitle->getEditURL();
				} else {
					$sEditUrl = $oTitle->getEditURL()."&section=".$row['se_edit_section'];
				}
				$aData = array(
					"time" => $oLang->time( $row['se_timestamp'] ),
					"date" => $oLang->date( $row['se_timestamp'] ),
					"savedOtherSection" => "1",
					"redirect" => $sEditUrl
				);
			} elseif ( strcmp( $sOrigText, urldecode($row['se_text'] ) ) == 0 ) {
				$aData = array( "notexts" => "1" );
			} else {
				global $wgParser;
				$oParserOptions = new ParserOptions();
				$str = urldecode( $row['se_text'] );
				$aData = array(
					"time" => $oLang->time( $row['se_timestamp'] ),
					"date" => $oLang->date( $row['se_timestamp'] ),
					"html" => $wgParser->parse( $str, RequestContext::getMain()->getTitle(), $oParserOptions )->getText(), //breaks on Mainpage
					"wiki" => $str,
					"section" => $row['se_edit_section'],
					"notexts" => 0
				);
			}

			$oDbw->freeResult( $res );
		} else {
			$aData = array( "notexts" => "1" );
		}

		return json_encode( $aData ); // TODO RBV (19.05.11 09:05): XHRResponse. Or MediaWiki AjaxResponse...
	}

	/**
	 * Renders a note that there are lost texts to the statebar
	 * @param integer $iEditSection if lost text is from a section edit, number of the edited section
	 * @return ViewStateBarTopElement View that is to be displayed in StateBar Top
	 */
	public function makeStateBarTopSaferEdit( $oArticle, $iEditSection = -1 ) {
		global $wgScriptPath;
		$oSaferEditView = new ViewStateBarTopElement();

		if ( is_object( $oArticle ) ) {
			$sArticleEditPageLink = $oArticle->getTitle()->getEditURL();

			if ( $iEditSection != -1 ) {
				$sArticleEditPageLink .= '&section='.$iEditSection;
			}

			$oSaferEditView->setKey( 'SaferEdit' );
			$oSaferEditView->setIconSrc( $wgScriptPath.'/extensions/BlueSpiceExtensions/SaferEdit/resources/images/bs-saferedit.png' );
			$oSaferEditView->setIconAlt( wfMessage( 'bs-saferedit-safer-edit-tooltip' )->plain() );
			$oSaferEditView->setText( wfMessage( 'bs-saferedit-safer-edit-topbar' )->plain() );
			$oSaferEditView->setTextLink( $sArticleEditPageLink );
			$oSaferEditView->setTextLinkTitle( wfMessage( 'bs-saferedit-safer-edit-tooltip' )->plain() );
		}

		return $oSaferEditView;
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
	 * Hook-Handler for MW hook BeforeInitialize -  Used to set Context
	 * @param Title $oTitle
	 * @param Article $oArticle
	 * @param OutPutpage $oOutput
	 * @param User $oUser
	 * @param WebRequest $oRequest
	 * @param MediaWiki $oMediaWiki
	 * @return boolean - always true
	 */
	public function onBeforeInitialize( &$oTitle, $oArticle, &$oOutput, &$oUser, $oRequest, $oMediaWiki ) {
		if( !is_object( $oTitle ) ) return true;
		if( !$oTitle->userCan('read')) return true;
		if( $oTitle->getNamespace() === NS_SPECIAL ) return true;

		$sAction = $oRequest->getVal( 'action', 'view' );

		if( !in_array($sAction, array( 'edit', 'submit', 'view', ))) return true;

		BsExtensionManager::setContext( 'MW::SaferEdit' );

		if( !$oTitle->userCan('edit')) return true;
		if( !in_array($sAction, array( 'edit', 'submit'))) return true;

		BsExtensionManager::setContext( 'MW::SaferEditEditMode' );

		return true;
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
		if( !in_array($sRef, array('SaferEditIsSomeoneEditing', 'SaferEditSave')) ) return true;

		$oTitle = Title::newFromText( $sTitle, $iNamespace );
		if ( is_null($oTitle) || !$oTitle->userCan('read') ) return true;

		global $wgUser;

		switch( $sRef ) {
			case 'SaferEditIsSomeoneEditing':
				$aSingleResult['success'] = true;
				$aIntermediateEdits = $this->getIntermediateEditsForCurrentTitle( $oTitle );
				if( empty($aIntermediateEdits) ) return true;

				$aSingleResult['someoneEditingView'] = $aSingleResult['safereditView'] = '';
				$oArticle = Article::newFromID( $iArticleId );

				$bUseSE = BsConfig::get( 'MW::SaferEdit::UseSE' );
				$sText = BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle );
				foreach ( $aIntermediateEdits as $oEdit ) {
					if ( $bUseSE !== false && $oEdit->se_user_name == $wgUser->getName()
						&& trim( $oEdit->se_text ) != trim( $sText ) ) {
						$aSingleResult['safereditView'] = $this->makeStateBarTopSaferEdit( $oArticle, $oEdit->se_edit_section )->execute();
					}

					$iDate = wfTimestamp( TS_MW, time() - BsConfig::get( 'MW::SaferEdit::Interval' ) * 10 );
					if ( $oEdit->se_user_name != $wgUser->getName() && $oEdit->se_timestamp > $iDate ) {
						$aSingleResult['someoneEditingView'] = $this->makeStateBarTopSomeoneEditing( $oEdit->se_user_name )->execute();
					}
				}

				break;
			case 'SaferEditSave':
				$iSection = empty( $aData[0]['section'] ) ? -1 : $aData[0]['section'];
				$sText = empty( $aData[0]['text'] ) ? '' : $aData[0]['text'];

				$aSingleResult['success'] = $this->saveText(
					$sText,
					$wgUser->getName(),
					$oTitle,
					$iSection
				);

				break;
		}

		return true;
	}
}