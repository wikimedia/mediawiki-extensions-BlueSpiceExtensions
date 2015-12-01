<?php
/**
 * Renders the ResponsibleEditors special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>

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

	public function __construct() {
		parent::__construct( 'ResponsibleEditors', 'responsibleeditors-viewspecialpage' );
	}

	public function execute( $sParameter ) {
		parent::execute( $sParameter );

		$sOut = $this->renderOverviewGrid();

		$this->getOutput()->addHTML( $sOut );
	}

	private function renderOverviewGrid() {
		$sUserIsAllowedToChangeResponsibilities = false;
		if ( $this->getUser()->isAllowed( 'responsibleeditors-changeresponsibility' ) ) {
			$sUserIsAllowedToChangeResponsibilities = true;
		}

		$this->getOutput()->addJsConfigVars(
			'bsUserMayChangeResponsibilities',
			$sUserIsAllowedToChangeResponsibilities
		);
		$this->getOutput()->addModules('ext.bluespice.responsibleEditors.manager');

		return Html::element(
			'div',
			array(
				'id' => 'bs-responsibleeditors-container'
			)
		);
	}

	public static function ajaxGetPossibleEditors( $iArticleId = -1 ) {
		$aResult = array( 'users' => array() );
		if( $iArticleId == -1 ) return FormatJson::encode( $aResult );

		$oResponsibleEditors = BsExtensionManager::getExtension( 'ResponsibleEditors' );
		$aResult['users'] = $aEditors = $oResponsibleEditors->getListOfResponsibleEditorsForArticle($iArticleId);

		return FormatJson::encode( $aResult );
	}

	public static function ajaxSetResponsibleEditors( $sParams ) {

		$aParams = FormatJson::decode( $sParams, true );

		$iArticleId = $aParams['articleId'];
		$aEditors   = $aParams['editorIds'];

		ResponsibleEditors::deleteResponsibleEditorsFromCache($iArticleId );

		$oRequestedTitle = Title::newFromId($iArticleId);

		if (!$oRequestedTitle->userCan('responsibleeditors-changeresponsibility')) {
			return json_encode(array(
				'success' => false,
				'msg' => wfMessage( 'bs-responsibleeditors-error-ajax-not-allowed' )->plain()
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

		if( !empty($aNewEditorIds) && BsConfig::get('MW::ResponsibleEditors::AddArticleToREWatchLists') == true ) {
			foreach($aNewEditorIds as $iUserId) {
				$oNewEditorUser = User::newFromId($iUserId);
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
		$iPosition = 0;
		foreach( $aEditors as $iEditor ) {
			$dbw->insert(
					'bs_responsible_editors',
					array(
						're_page_id' => $iArticleId,
						're_user_id' => $iEditor,
						're_position' => $iPosition
					)
			);
			$iPosition++;
		}

		$dbw->commit();

		$oTitle = Title::newFromID( $iArticleId );
		$oUser = RequestContext::getMain()->getUser();

		BSNotifications::notify( 'bs-responsible-editors-assign', $oUser, $oTitle, array( 'affected-users' => $aNewEditorIds ) );
		BSNotifications::notify( 'bs-responsible-editors-revoke', $oUser, $oTitle, array( 'affected-users' => $aRemovedEditorIds ) );

		foreach( $aNewEditorIds as $iNewEditorId ) {
			$oEditor = User::newFromId( $iNewEditorId );
			$oLogger = new ManualLogEntry( 'bs-responsible-editors', 'add' );
			$oLogger->setPerformer( $oUser );
			$oLogger->setTarget( $oTitle );
			$oLogger->setParameters( array(
					'4::editor' => $oEditor->getName()
			) );
			$oLogger->insert();
		}
		foreach( $aRemovedEditorIds as $iRemovedEditorId ) {
			$oEditor = User::newFromId( $iRemovedEditorId );
			$oLogger = new ManualLogEntry( 'bs-responsible-editors', 'remove' );
			$oLogger->setPerformer( $oUser );
			$oLogger->setTarget( $oTitle );
			$oLogger->setParameters( array(
					'4::editor' => $oEditor->getName()
			) );
			$oLogger->insert();
		}

		$oRequestedTitle->invalidateCache();

		return FormatJson::encode( array( 'success' => true ) );
	}

	protected function getGroupName() {
		return 'bluespice';
	}
}