<?php

/**
 * Renders the Review special page.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: SpecialReview.class.php 9830 2013-06-20 12:54:10Z rvogel $
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
		$this->oExtension = BsExtensionMW::getInstanceFor('MW::Review');
		$this->oExtension->registerView('ViewReviewForm');
		$this->oExtension->registerView('ViewReviewStep');
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

		$oOutputPage = BsCore::getInstance('MW')->getAdapter()->get('Out');

		$oUser = BsAdapterMW::loadCurrentUser();
		if ( !$oUser->isAllowed('workflowview') ) {
			$sOut = wfMsg( 'bs-review-not-allowed' );
			$oOutputPage->addHTML($sOut);
			return true;
		}

		if ( $sParameter == '' ) {
			$sOut = $this->getOverviewForm();
			$oOutputPage->addHTML($sOut);
			return true;
		}

		$oRequestedTitle = Title::newFromText($sParameter); // TODO RBV (30.06.11 14:46): Use views.
		$oOutputPage->setPagetitle(wfMsg( 'bs-review-specialpage-title', $oRequestedTitle->getFullText() ));
		$oOutputPage->setSubtitle(wfMsg( 'bs-review-specialpage-subtitle', "<a href=" . $oRequestedTitle->getFullURL() . ">" . $oRequestedTitle->getFullText() . "</a>" ));
		if ( $oRequestedTitle->getNamespace() == NS_USER ) {
			$sOut .= $this->getReviewsForUserForm($oRequestedTitle->getText());
		} else {
			$oOutputPage->setSubtitle(wfMsg( 'bs-review-specialpage-subtitle', "<a href=" . $oRequestedTitle->getFullURL() . ">" . $oRequestedTitle->getFullText() . "</a>" ));
			$sOut .= $this->getCreateReviewForm($oRequestedTitle);
		}

		$oOutputPage->addHTML($sOut);
		return true;
	}

	/**
	 * imported from BlueCraft
	 * @global User $wgUser
	 * @global OutputPage $wgOut
	 * @return string 
	 */
	protected function getOverviewForm() {
		global $wgUser, $wgOut;

		$oLinker = new Linker();
		$iUserId = BsCore::getParam('user', $wgUser->mId, BsPARAM::GET | BsPARAMTYPE::INT);
		$bShowAssessor = BsConfig::get('MW::Review::ShowAssessor');

		if ( !in_array('workflowlist', $wgUser->getRights()) && $wgUser->mId != $iUserId ) {
			return wfMsg( 'bs-review-not-allowed' );
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
			$sOutput.= '>' . wfMsg( 'bs-review-mine' ) . '</option>';
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
//			$sOutput .= '<input type="hidden"'
			$sOutput.= '</form>';
		}

		$sOutput.= '<table width="100%" cellspacing="0" cellpadding="4" border="0">';
		$sOutput.= '<tr class="hw-th">';
		$sOutput.= '<th>' . wfMsg( 'bs-review-title_page' ) . '</th>';
		$sOutput.= '<th>' . wfMsg( 'bs-review-title_user' ) . '</th>';
		$sOutput.= '<th>' . wfMsg( 'bs-review-title_mode' ) . '</th>';

		if ($bShowAssessor) {
			$sOutput.= '<th>' . wfMsg('bs-review-title_userlist') . '</th>';
		}

		$sOutput.= '<th>' . wfMsg( 'bs-review-title_status' ) . '</th>';
		$sOutput.= '<th>' . wfMsg( 'bs-review-title_date' ) . '</th>';
		$sOutput.= '</tr>';

		if ( sizeof($aReviewList) == 0 ) {
			$sOutput.= '<tr><td colspan="6" align="center">' . wfMsg( 'bs-review-noworkflows' ) . '</td></tr>';
		} else {
			$i = 0;
//			var_dump( $aReviewList );die();
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
				$sOutput.= '<td valign="top">' . wfMsg( 'bs-review-' . $row['rev_mode']) . '</td>';

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
				$sOutput.= wfMsg( 'bs-review-accepted' ) . ': ' . $accepted . '/' . $arrValue['total'] . '<br />';
				if( $arrValue['revs_status'] == '' ) {
					$arrValue['revs_status'] = 'pending';
				}
				$sOutput.= wfMsg( 'bs-review-' . $arrValue['revs_status']);

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

		$sOut = '<div>' . wfMsg( 'bs-review-here-are-your-workflows' ) . '</div>';

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
	 * @param string $oRequestedTitle Name of the page.
	 * @return string HTML list output.
	 */
	protected function getCreateReviewForm($oRequestedTitle) {
		$oAdapterMW = BsCore::getInstance('MW')->getAdapter();
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
		$bUserIsSysop = in_array('sysop', $oAdapterMW->get('User')->getGroups());
		$bReadOnly = false;
		if (!$oAdapterMW->get('User')->isAllowed('workflowedit'))
			$bReadOnly = true;
		if (!$bUserIsSysop && $oActiveReview && BsConfig::get('MW::Review::CheckOwner')
				&& ( $oActiveReview->owner != $oAdapterMW->get('User')->getID() )) {
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
		$oUserOrig = $oAdapterMW->get('User');
		while ($row = $dbw->fetchRow($res)) {
			$oUser = User::newFromName($row['user_name']);
			$oAdapterMW->set('User', $oUser);
			if ($oRequestedTitle->userCan( 'read' )) {
				#$oReviewFormView->addAssessor( $oUser->getName(), BsAdapterMW::getUserDisplayName( $oUser ) );
			}
		}
		$oAdapterMW->set('User', $oUserOrig);

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
//				$oReviewStepView->setUserName( BsAdapterMW::getUserDisplayName( User::newFromId( $step->user ) ) );
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
				$aStep['name'] = BsAdapterMW::getUserDisplayName(User::newFromId($step->user));
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