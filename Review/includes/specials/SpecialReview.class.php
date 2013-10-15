<?php

/**
 * Renders the Review special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage Review
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
//Last Code Review RBV (30.06.2011)

/**
 * Review special page that renders the review edit dialogue
 * @package BlueSpice_Extensions
 * @subpackage Review
 */
class SpecialReview extends BsSpecialPage {

	/**
	 * Constructor of SpecialReview class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		parent::__construct( 'Review', 'workflowview' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Renders special page output.
	 * @param string $sParameter Name of the article, who's review should be edited, or user whos review should be displayed.
	 * @return bool Allow other hooked methods to be executed. always true.
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );
		$sOut = '';

		$oOutputPage = $this->getOutput();
		$oOutputPage->addModules( 'ext.bluespice.review.overview' );

		//TODO: Redundant?
		if ( !$this->getUser()->isAllowed('workflowview') ) {
			$sOut = wfMessage( 'bs-review-not-allowed' )->plain();
			$oOutputPage->addHTML($sOut);
			return true;
		}
		
		$oOutputPage->addHTML($sOut);
		$oOutputPage->addHTML(
			Html::element( 'div', array( 'id' => 'bs-review-overview') )
		);
		return true;

		//DEPRECATED
		if ( $sParameter == '' ) {
			$sOut = $this->getOverviewForm();
			$oOutputPage->addHTML($sOut);
			return true;
		}

		$oRequestedTitle = Title::newFromText($sParameter); // TODO RBV (30.06.11 14:46): Use views.
		$oOutputPage->setPagetitle(wfMessage( 'bs-review-specialpage-title', $oRequestedTitle->getFullText() )->plain());
		$oOutputPage->setSubtitle(wfMessage( 'bs-review-specialpage-subtitle', "<a href=" . $oRequestedTitle->getFullURL() . ">" . $oRequestedTitle->getFullText() . "</a>" )->plain());
		if ( $oRequestedTitle->getNamespace() == NS_USER ) {
			$sOut .= $this->getReviewsForUserForm($oRequestedTitle->getText());
		} else {
			$oOutputPage->setSubtitle(wfMessage( 'bs-review-specialpage-subtitle', "<a href=" . $oRequestedTitle->getFullURL() . ">" . $oRequestedTitle->getFullText() . "</a>" )->plain());
			$sOut .= $this->getCreateReviewForm($oRequestedTitle);
		}

		$oOutputPage->addHTML($sOut);
		return true;
	}

	/**
	 * imported from BlueCraft
	 * @deprecated We now use a ExtJS GridPanel
	 * @global User $wgUser
	 * @global OutputPage $wgOut
	 * @return string 
	 */
	protected function getOverviewForm() {
		global $wgUser, $wgOut, $wgRequest;

		$oLinker = new Linker();
		$iUserId = $wgRequest->getInt( 'user', $wgUser->mId );
		$bShowAssessor = BsConfig::get('MW::Review::ShowAssessor');

		if ( !in_array('workflowlist', $wgUser->getRights()) && $wgUser->mId != $iUserId ) {
			return wfMessage( 'bs-review-not-allowed' )->plain();
		}

		$aReviewList = Review::getData();

		$sOutput = '<div class="reviewoverview">';

		// Userlist dropdown
		if ( in_array('workflowlist', $wgUser->getRights()) ) {
			$sOutput.= '<form name="reviewform" method="GET" action="">';
			$sOutput .= '<input type="hidden" name="title" value="'.$wgOut->getTitle()->getPrefixedText().'" />';
			$sOutput.= '<select name="user" onchange="reviewform.submit()">';
			//$sOutput.= '<option value="0">' . wfMsg( 'bs-review-all' ) . '</option>';
			$sOutput.= '<option value="' . $wgUser->mId . '"';
			if ($iUserId == $wgUser->mId) {
				$sOutput.= ' selected';
			}
			$sOutput.= '>' . wfMessage( 'bs-review-mine' )->plain() . '</option>';
			$sOutput.= '<option disabled>-</option>';

			$aUsers = $this->getUsers();
			foreach ($aUsers as $aUser) {
				if ($aUser['user_id'] != $wgUser->mId) {
					$sOutput.= '<option value="' . $aUser['user_id'] . '"';
					if ($iUserId == $aUser['user_id']) {
						$sOutput.= ' selected';
					}
					$sOutput.= '>' . $aUser['user_name'] . '</option>';
				}
			}

			$sOutput.= '</select>';
			$sOutput.= '</form>';
		}

		$sOutput.= '<table width="100%" cellspacing="0" cellpadding="4" border="0">';
		$sOutput.= '<tr class="hw-th">';
		$sOutput.= '<th>' . wfMessage( 'bs-review-title_page' )->plain() . '</th>';
		$sOutput.= '<th>' . wfMessage( 'bs-review-title_user' )->plain() . '</th>';
		$sOutput.= '<th>' . wfMessage( 'bs-review-title_mode' )->plain() . '</th>';

		if ($bShowAssessor) {
			$sOutput.= '<th>' . wfMessage('bs-review-title_userlist')->plain() . '</th>';
		}

		$sOutput.= '<th>' . wfMessage( 'bs-review-title_status' )->plain() . '</th>';
		$sOutput.= '<th>' . wfMessage( 'bs-review-title_date' )->plain() . '</th>';
		$sOutput.= '</tr>';

		if ( sizeof($aReviewList) == 0 ) {
			$sOutput.= '<tr><td colspan="6" align="center">' . wfMessage( 'bs-review-noworkflows' )->plain() . '</td></tr>';
		} else {
			$i = 0;

			foreach ($aReviewList AS $arrKey => $arrValue) {
				$row = $arrValue['array'];

				$i++;
				if ($i % 2 == 1) {
					$class = ' class="hw-oddrow"';
				} else {
					$class = ' class="hw-evenrow"';
				}

				$title = Title::newFromText($row['page_title'], $row['page_namespace']);

				$sOutput.= '<tr' . $class . '>';
				$sOutput.= '<td valign="top">' . $oLinker->makeLinkObj($title, $row['page_title']) . '</td>';
				$sOutput.= '<td valign="top">' . $row['owner_name'] . '</td>';
				$sOutput.= '<td valign="top">' . wfMessage( 'bs-review-' . $row['rev_mode'])->plain() . '</td>';

				if ($bShowAssessor) {
					$sOutput.= '<td valign="top">';
					foreach ($arrValue['assessors'] AS $arrAssessor) {
						switch ($arrAssessor['revs_status']) {
							case '-1':
								$class = '';
								$symbol = '&nbsp;&nbsp;';
								break;
							case '0':
								$class = ' class="red"';
								$symbol = 'X';
								break;
							case '1':
								$class = ' class="green"';
								$symbol = 'V';
								break;
							default:
								$symbol = '';
								$class = '';
						}

						$sOutput.= '<font' . $class . '>' . $symbol . '</font> <span>' . $arrAssessor['name'] . '</span> ';
						if ($arrAssessor['timestamp'] != '00.00' && $arrAssessor['revs_status'] >= 0) {
							$sOutput.= '<font color="#aaaaaa">(' . $arrAssessor['timestamp'] . ')</font>';
						}
						$sOutput.= '<br />';
					}
					$sOutput.= '</td>';
				}

				$accepted = isset($arrValue['accepted']) ? $arrValue['accepted'] : 0;

				$sOutput.= '<td valign="top">';
				$sOutput.= wfMessage( 'bs-review-accepted' )->plain() . ': ' . $accepted . '/' . $arrValue['total'] . '<br />';
				if( $arrValue['revs_status'] == '' ) {
					$arrValue['revs_status'] = 'pending';
				}
				$sOutput.= wfMessage( 'bs-review-' . $arrValue['revs_status'])->plain();

				$sOutput.= '</td>';

				//$rejected = isset($arrValue['rejected']) ? $arrValue['rejected'] : 0;
				//$html.= '<td valign="top">'.$hw->msg( 'reviewoverview', 'rejected' ).': '.$rejected.'/'.$arrValue['total'].'</td>';
				//$accepted = isset($arrValue['accepted']) ? $arrValue['accepted'] : 0;
				//$html.= '<td valign="top">'.$hw->msg( 'reviewoverview', 'accepted' ).': '.$accepted.'/'.$arrValue['total'].'</td>';

				if (($row['endtimestamp'] < time() && $accepted < $arrValue['total']) || (isset($arrValue['rejected']) && $arrValue['rejected'] > 0)) {
					$class = ' class="red"';
				} else {
					$class = ' class="green"';
				}

				$sOutput.= '<td' . $class . ' valign="top">' . $row['startdate'] . ' - ' . $row['enddate'] . '</td>';
				$sOutput.= '</tr>';
			}
		}

		$sOutput.= '</table>';
		$sOutput.= '</div>';

		return $sOutput;
	}
	
	public static function ajaxGetOverview() {
		$oResponse = BsCAResponse::newFromPermission( 'workflowlist' );
		if( $oResponse->isSuccess() == false ) {
			return $oResponse;
		}
		
		$aReviews = array();
		$aReviewList = Review::getData();

		foreach ($aReviewList as $arrKey => $row) {
			$row += $row['array'];
			if( $row['revs_status'] == '' ) {
				$row['revs_status'] = 'pending';
			}
			
			$oReview = new stdClass();
			$oReview->rev_id = $row['rev_id'];
			$oReview->page_title = Title::makeTitle( $row['page_namespace'], $row['page_title'] )->getPrefixedText();
			$oReview->owner_name = $row['owner_name'];
			$oReview->rev_mode = $row['rev_mode'];
			$oReview->rev_mode_text = wfMessage( 'bs-review-' . $row['rev_mode'])->plain();
			$oReview->rev_status = $row['revs_status'];
			$oReview->rev_status_text = wfMessage( 'bs-review-' . $row['revs_status'])->plain();
			$oReview->rejected = isset($row['rejected']) ? $row['rejected'] : false;
			$oReview->accepted = isset($row['accepted']) ? $row['accepted'] : 0;
			$oReview->accepted_text = 
				wfMessage( 'bs-review-accepted' )->plain() . ': ' . 
				$oReview->accepted . '/' . $row['total'] . '<br />'.
				wfMessage( 'bs-review-' . $row['revs_status'])->plain();
			$oReview->total = $row['total'];
			$oReview->endtimetamp = $row['endtimestamp'];
			$oReview->startdate = $row['startdate'];
			$oReview->enddate = $row['enddate'];
			$oReview->assessors = array();

			foreach ($row['assessors'] AS $arrAssessor) {
				$oAssessor = new stdClass();
				$oAssessor->revs_status = $arrAssessor['revs_status'];
				$oAssessor->name = $arrAssessor['name'];
				$oAssessor->timestamp = false;

				if ($arrAssessor['timestamp'] != '00.00' && $arrAssessor['revs_status'] >= 0) {
					$oAssessor->timestamp = $arrAssessor['timestamp'];
				}
				$oReview->assessors[] = $oAssessor;
			}

			$aReviews[] = $oReview;
		}
			
		$oResponse->setPayload( $aReviews );
		return $oResponse;
	}

	/**
	 * imported from BlueCraft
	 */
	protected function getUsers() {
		$dbw = wfGetDB(DB_SLAVE);
		$res = $dbw->select('user', '*');

		$arrUsers = array();
		while ($row = $dbw->fetchRow($res)) {
			$arrUsers[] = $row;
		}

		return $arrUsers;
	}

	/**
	 * Renders a list of reviews for a given user.
	 * @param string $sUserName Name of the user.
	 * @return string HTML list output.
	 */
	protected function getReviewsForUserForm($sUserName) {
		$oUser = User::newFromName($sUserName);

		if( !is_object( $oUser ) ) {
			return '<div>' . wfMsg( 'bs-review-no-valid-user' ) . '</div>';
		}
		$aReviews = BsReviewProcess::listReviews($oUser->getId(), false);

		$sOut = '<div>' . wfMessage( 'bs-review-here-are-your-workflows' )->plain() . '</div>';

		$sOut .= '<ul>';
		foreach ($aReviews as $iReviewId) {
			$oReview = BsReviewProcess::newFromPid($iReviewId);
			$oTitle = Title::newFromID($oReview->getPid());
			$sOut .= '<li><a href="' . $oTitle->getFullUrl() . '">' . $oTitle->getFullText() . '</a></li>';
		}
		$sOut .= '</ul>';

		return $sOut;
	}

	/**
	 * Renders the current edit form for the review of a given page.
	 * @deprecated: We now use a ExtJS Dialog
	 * @param string $oRequestedTitle Name of the page.
	 * @return string HTML list output.
	 */
	protected function getCreateReviewForm($oRequestedTitle) {
		$sOut = '';

		BsConfig::registerVar(
				'MW::Review::ArticleId', $oRequestedTitle->getArticleID(), BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_INT | BsConfig::RENDER_AS_JAVASCRIPT, 'bs-review-pref-ArticleId'
		);

//		$aJsonOut = array(
//			"pid" => "1",
//			"mode" => "comment",
//			"startdate" => "2011-02-02",
//			"enddate" => "2011-02-17",
//			"steps" => array(
//				array(
//					"status" => "-1",
//					"name" => "MarkusGlaser",
//					"comment" => "Yess!!"
//				)
//			)
//		);

		$oActiveReview = BsReviewProcess::newFromPid($oRequestedTitle->getArticleID());

		// TODO MRG (04.02.11 10:52): Put in Framework
		$bUserIsSysop = in_array('sysop', $this->getUser()->getGroups());
		$bReadOnly = false;
		if (!$this->getUser()->isAllowed('workflowedit'))
			$bReadOnly = true;
		if (!$bUserIsSysop && $oActiveReview && BsConfig::get('MW::Review::CheckOwner')
				&& ( $oActiveReview->owner != $this->getUser()->getID() )) {
			$bReadOnly = true;
		}

		BsConfig::registerVar(
				'MW::Review::ReadOnly', $bReadOnly, BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_BOOL | BsConfig::RENDER_AS_JAVASCRIPT, 'bs-review-pref-ReadOnly'
		);
		//$oReviewFormView = new ViewReviewForm( $this->oExtension->mI18N );
		#$oReviewFormView->setOption( 'readonly', $bReadOnly );
		#$oReviewFormView->setOption( 'usetemplates', BsConfig::get( 'MW::Review::UserTemplates' ) );
		//$oReviewFormView->setPageId( $oRequestedTitle->getArticleID() );

		$aJsonOut['pid'] = $oRequestedTitle->getArticleID();

		//if ( $oActiveReview ) {
		//	$oReviewFormView->setActive( true );
		//}
		//if ( $oActiveReview ) {
		//	$oReviewFormView->setMode( $oActiveReview->mode );
		//}
		//if ( $oActiveReview ) {
		//	$oReviewFormView->setStartDate( $oActiveReview->getStartdate() );
		//	$oReviewFormView->setEndDate( $oActiveReview->getEnddate() );
		//} else {
		//	$oReviewFormView->setStartDate( date( wfMsg( 'dateformat' ) ) );
		//	$oReviewFormView->setEndDate( date( wfMsg( 'dateformat' ), time() + 7 * 24 * 60 * 60 ) );
		//}

		if ($oActiveReview) {
			$aJsonOut['startdate'] = $oActiveReview->getStartdate();
			$aJsonOut['enddate'] = $oActiveReview->getEnddate();
			$aJsonOut['mode'] = $oActiveReview->getMode();
		} else {
			$aJsonOut['startdate'] = date('Y-m-d');
			$aJsonOut['enddate'] = date('Y-m-d', time() + 7 * 24 * 60 * 60);
			$aJsonOut['mode'] = 'comment';
		}

		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->select('user', 'user_name', '', '', array("ORDER BY" => "user_name"));

		global $wgUser;
		$oUserOrig = $wgUser;
		while ($row = $dbw->fetchRow($res)) {
			$oUser = User::newFromName($row['user_name']);
			$wgUser = $oUser;
			if ($oRequestedTitle->userCan( 'read' )) {
				#$oReviewFormView->addAssessor( $oUser->getName(), $this->mCore->getUserDisplayName( $oUser ) );
			}
		}
		$wgUser = $oUserOrig;

		$aJsonOut['steps'] = array();

//		if ( $oActiveReview ) {
//			foreach ( $oActiveReview->steps as $step ) {
//				$oReviewStepView = new ViewReviewStep( $this->oExtension->mI18N );
//				$oReviewStepView->setOption( 'readonly', $bReadOnly );
//
//				switch ( $step->status ) {
//					case -1 : $oReviewStepView->setStatus( 'unknown' ); break;
//					case 0  : $oReviewStepView->setStatus( 'no' ); break;
//					case 1  : $oReviewStepView->setStatus( 'yes' ); break;
//				}
//				$oReviewStepView->setUserName( $this->mCore->getUserDisplayName( User::newFromId( $step->user ) ) );
//				$oReviewStepView->setComment( $step->comment );
//
//				$oReviewFormView->addItem( $oReviewStepView );
//			}
//		}

		if ($oActiveReview) {
			foreach ($oActiveReview->steps as $step) {
				$aStep = array();
				#$oReviewStepView = new ViewReviewStep( $this->oExtension->mI18N );
				#$oReviewStepView->setOption( 'readonly', $bReadOnly );

				switch ($step->status) {
					case -1 : $aStep['status'] = 'unknown';
						break;
					case 0 : $aStep['status'] = 'no';
						break;
					case 1 : $aStep['status'] = 'yes';
						break;
				}

				$aStep['name'] = BsCore::getInstance()->getUserDisplayName(User::newFromId($step->user));
				wfRunHooks('SpecialReview::getCreateReviewForm::BuildData', array($step, &$aStep));
				$aStep['userid'] = $step->user;
				$aStep['comment'] = $step->comment;
				$aStep['sort_id'] = $step->sort_id;


				$aJsonOut['steps'][] = $aStep;
				//$oReviewFormView->addItem( $oReviewStepView );
			}
		}

		//$sOut = $oReviewFormView->execute();
		//BsConfig::registerVar('MW::Review::CurrentReview', json_encode($aJsonOut), BsConfig::LEVEL_PRIVATE | BsConfig::TYPE_STRING | BsConfig::RENDER_AS_JAVASCRIPT, 'bs-review-pref-CurrentReview');

		BsExtensionManager::setContext('MW::ReviewSpecialPage');
		$sOut .= '<div id="bs-review-panel"></div>';
		$sOut .= '<script type="text/javascript">'
				.	'var bsReviewCurrentReview = '.json_encode($aJsonOut).';'
				.'</script>';

		return $sOut;
	}

}