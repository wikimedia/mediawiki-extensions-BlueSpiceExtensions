<?php
/**
 * Renders the ResponsibleEditors special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: SpecialResponsibleEditors.class.php 8255 2013-01-22 13:12:55Z smuggli $
 * @package    BlueSpice_Extensions
 * @subpackage ResponsibleEditors
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

// Last review MRG (01.07.11 11:48)

/**
 * ResponsibleEditors SpecialPage
 * @package BlueSpice_Extensions
 * @subpackage ResponsibleEditors
 */
class SpecialResponsibleEditors extends BsSpecialPage {

	protected static $messagesLoaded = false;

	function __construct() {
		parent::__construct( 'ResponsibleEditors', 'responsibleeditors-viewspecialpage' );
	}

	function execute( $sParameter ) {
		parent::execute( $sParameter );

		if( !empty( $sParameter ) ) {
			$oRequestedTitle = Title::newFromText( $sParameter );
			if( $oRequestedTitle->exists() ) {
				 $sOut = $this->renderChangeAssignmentDialog( $oRequestedTitle );
			}
			else {
				$oErrorView = new ViewTagErrorList();
				$oErrorView->addItem(
								new ViewTagError(
									wfMsg( 'bs-responsibleeditors-error-specialpage-given-article-does-not-exist' )
								)
							);
				$sOut = $oErrorView->execute();
			}
		}
		else {
			$sOut = $this->renderOverviewGrid();
		}

		$oOutputPage = BsCore::getInstance( 'MW' )->getAdapter()->get( 'Out' );
		$oOutputPage->addHTML( $sOut );
	}
	

	private function renderOverviewGrid() {

		$oCurrentUser = BsCore::getInstance( 'MW' )->getAdapter()->get( 'User' );
		BsExtensionManager::setContext('MW::ResponsibleEditorsShow');
		BsExtensionManager::setContext( 'MW::ResponsibleEditorsAssignmentPanel' );
		BsExtensionManager::setContext( 'MW::ResponsibleEditorsSpecialPageAssignmentWindow' );

		$sUserIsAllowedToChangeResponsibilities = 'false';
		if ( $oCurrentUser->isAllowed( 'responsibleeditors-changeresponsibility' ) ) {
			$sUserIsAllowedToChangeResponsibilities = 'true';
		}

		$sClientScriptBlock = '<script>mw.loader.using( "ext.bluespice.responsibleEditors", function(){ BsResponsibleEditorsSpecialPageGrid.show( %s, %s ); });</script>';
		$sClientScriptBlock = 'mw.loader.using( "ext.bluespice.responsibleEditors", function(){ BsResponsibleEditorsSpecialPageGrid.show( %s, %s ); });';

		$sClientScriptBlock = sprintf( $sClientScriptBlock,
			BsConfig::get('MW::ResponsibleEditors::SpecialPageDefaultPageSize'),
			$sUserIsAllowedToChangeResponsibilities
			);
		
		$oOut = RequestContext::getMain()->getOutput();
//		$oOut->addScript( $sClientScriptBlock );
		BsScriptManager::registerClientScriptBlock( 'MW::ResponsibleEditors', $sClientScriptBlock );

		$sOut = '<div id="bs-responsibleeditor-specialpage-grid"></div>';
		$sOut .= '<div id="bs-responsibleeditors-assignmentwindow"></div>';
		return $sOut;
	}

	public function renderChangeAssignmentDialog( Title $oRequestedTitle ) {
		$oOutputPage  = BsCore::getInstance( 'MW' )->getAdapter()->get( 'Out' );
		$oCurrentUser = BsCore::getInstance( 'MW' )->getAdapter()->get( 'User' );
		$oRespEdExt   = BsExtensionManager::getExtension( 'ResponsibleEditors' );

		if( $oRespEdExt->userIsAllowedToChangeResponsibility( $oCurrentUser, $oRequestedTitle ) === false ) {
			$oErrorView = new ViewTagErrorList();
				$oErrorView->addItem(
								new ViewTagError(
									wfMsg( 'bs-responsibleeditors-error-ajax-not-allowed' )
								)
							);
			$sOut = $oErrorView->execute();
		}
		else {
			BsExtensionManager::setContext('MW::ResponsibleEditorsSpecialPageAssignmentDialog');
			BsExtensionManager::setContext('MW::ResponsibleEditorsAssignmentPanel');
			$oOutputPage->setPageTitle( $oOutputPage->getPageTitle().': '.$oRequestedTitle->getFullText() );
			$aResponsibleEditorIds = $oRespEdExt->getResponsibleEditorIdsByArticleId( $oRequestedTitle->getArticleID() );
			$oData = new stdClass();
			$oData->articleId = $oRequestedTitle->getArticleID();
			$oData->editorIds = $aResponsibleEditorIds;
			$oData->returnUrl = $oRequestedTitle->getFullURL();
			$oOutputPage->addHtml(
				'<script type="text/javascript">
					bsResponsibleEditorsData = '.json_encode($oData).'
				</script>'
			);

			$sOut = '<div id="bs-responsibleeditors-assignmentdialog"></div>';
		}
		return $sOut;
	}

	static function ajaxGetResponsibleEditors( $iArticleId ) {
		if( $iArticleId == -1 ) return json_encode( array() );

		$oResponsibleEditors = BsExtensionManager::getExtension('ResponsibleEditors');
		$aEditors = $oResponsibleEditors->getResponsibleEditorIdsByArticleId($iArticleId);

		$aResponsibleEditors = array();
		foreach ($aEditors as $iUserId) {
			$aResponsibleEditors[] = array(
				$iUserId,
				BsAdapterMW::getUserDisplayName(
					User::newFromId($iUserId)
				),
				'X'
			);
		}
		return json_encode($aResponsibleEditors);
	}

	static function ajaxGetPossibleEditors( $iArticleId ) {
		if( $iArticleId == -1 ) return json_encode( array() );

		$oResponsibleEditors = BsExtensionManager::getExtension('ResponsibleEditors');
		$aEditors = $oResponsibleEditors->getListOfResponsibleEditorsForArticle($iArticleId);
		array_shift($aEditors);

		$aPossibleEditors = array();
		foreach ($aEditors as $aEditor) {
			$aPossibleEditors[] = array(
				'id'       => 'editors/' . $aEditor['user_displayname'],
				'text'     => $aEditor['user_displayname'],
				'editorId' => $aEditor['user_id'],
				'leaf'     => true
			);
		}

		return json_encode($aPossibleEditors);
	}

	static function ajaxSetResponsibleEditors( $sParams ) {

		$aParams = json_decode( $sParams, true );

		$iArticleId = $aParams['articleId'];
		$aEditors   = $aParams['editorIds'];

		$oRequestedTitle = Title::newFromId($iArticleId);

		if (!$oRequestedTitle->userCan('responsibleeditors-changeresponsibility')) {
			return json_encode(array(
						'success' => false,
						'msg' => wfMsg( 'bs-responsibleeditors-error-ajax-not-allowed' )
					));
		}

		$dbw = wfGetDB( DB_MASTER );
		$dbw->begin();
		$res = $dbw->select(
			'bs_responsible_editors',
			're_user_id',
			array( 're_page_id' => $iArticleId )
		);

		$aCurrentEditorIds = array();
		foreach( $res as $row) {
			$aCurrentEditorIds[] = $row->re_user_id;
		}

		$aRemovedEditorIds   = array_diff($aCurrentEditorIds, $aEditors);
		$aNewEditorIds       = array_diff($aEditors, $aCurrentEditorIds);
		$aUntouchedEditorIds = array_intersect($aCurrentEditorIds, $aEditors);

		if( !empty($aNewEditorIds) && BsConfig::get('MW::ResponsibleEditors::AddArticleToREWatchLists') == true ) {
			foreach($aNewEditorIds as $iUserId) {
				$oNewEditorUser=USER::newFromId($iUserId);
				if(!$oNewEditorUser->isWatched($oRequestedTitle)) {
					$oNewEditorUser->addWatch($oRequestedTitle);
				}
			}
		}
		//Remove all
		$dbw->delete(
				'bs_responsible_editors', 
				array(
					're_page_id' => $iArticleId
				)
		);

		//Add all --> to maintain position! As log as re_position field is not used properly...
		foreach( $aEditors as $iEditor ) {
			$dbw->insert(
					'bs_responsible_editors', 
					array(
						're_page_id' => $iArticleId,
						're_user_id' => $iEditor
					)
			);
		}

		$dbw->commit();
		self::notifyAffectedUsers( $aNewEditorIds, $aRemovedEditorIds, $aUntouchedEditorIds, $iArticleId );

		$oRequestedTitle->invalidateCache();

		return json_encode(array('success' => true));
	}

	/**
	 * 
	 * @global User $wgUser
	 * @param array $aNewEditorIds
	 * @param array $aRemovedEditorIds
	 * @param array $aUntouchedEditorIds
	 * @param int $iArticleId
	 * @return void
	 */
	static function notifyAffectedUsers( $aNewEditorIds, $aRemovedEditorIds, $aUntouchedEditorIds, $iArticleId ) {
		global $wgUser;
		if( BsConfig::get( 'MW::ResponsibleEditors::EMailNotificationOnResponsibilityChange' ) != true ) return;

		$oArticleTitle = Title::newFromID( $iArticleId );
		$sArticleName  = $oArticleTitle->getPrefixedText();
		$sArticleLink  = $oArticleTitle->getFullURL();
		$sChangingUserName = BsAdapterMW::getUserDisplayName( $wgUser );

		//Notify new editors
		$aNewEditors = array();
		foreach($aNewEditorIds as $iUserId ) {
			if( $wgUser->getId() == $iUserId ) continue; //Skip notification if user changes responsibility himself
			$oUser = User::newFromId( $iUserId );
			$aNewEditors[] = $oUser;
		}

		$sSubject = wfMessage(
			'bs-responsibleeditors-mail-subject-new-editor',
			$sArticleName
		)->plain();
		$sMessage = wfMessage(
			'bs-responsibleeditors-mail-text-new-editor',
			$sChangingUserName,
			$sArticleName,
			$sArticleLink
		)->plain();

		BsMailer::getInstance('MW')->send( $aNewEditors, $sSubject, $sMessage );
		
		//Notify untouched editors
		$aUntouchedEditors = array();
		foreach( $aUntouchedEditorIds as $iUserId ) {
			if( $wgUser->getId() == $iUserId ) continue; //Skip notification if user changes responsibility himself
			$aUntouchedEditors[] = User::newFromId( $iUserId );
		}
		/*
		//For future use...
		BsMailer::getInstance('MW')->send( $aUntouchedEditors, $sSubject, $sBody );
		*/

		//Notify removed editors
		$aRemovedEditors = array();
		if( empty( $aRemovedEditorIds ) ) return;
		foreach( $aRemovedEditorIds as $iUserId ) {
			if( $wgUser->getId() == $iUserId ) continue; //Skip notification if user changes responsibility himself
			$oUser = User::newFromId( $iUserId );
			$aRemovedEditors[] = $oUser;
		}

		$aCurrentRespEdNames = array();
		$aCurrentRespEdIds = $aUntouchedEditorIds + $aNewEditorIds;
		foreach( $aCurrentRespEdIds as $oCurrentRespEdUserId ) {
			$aCurrentRespEdNames[] = BsAdapterMW::getUserDisplayName(
				User::newFromId( $oCurrentRespEdUserId )
			);
		}

		$sSubject = wfMessage(
			'bs-responsibleeditors-mail-subject-former-editor',
			$sArticleName
		)->plain();
		$sMessage = wfMessage(
			'bs-responsibleeditors-mail-text-former-editor',
			$sChangingUserName,
			$sArticleName,
			implode( ', ', $aCurrentRespEdNames ),
			count( $aCurrentRespEdNames ),
			$sArticleLink
		)->parse();
		
		BsMailer::getInstance('MW')->send( $aRemovedEditors, $sSubject, $sMessage );
	}
}