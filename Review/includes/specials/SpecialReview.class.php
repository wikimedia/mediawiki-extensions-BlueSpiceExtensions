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

		$oUser = User::newFromName( $sParameter );
		if( $oUser && $oUser->getId() > 0 ) {
			$sName = $oUser->getRealName();
			$sName = empty( $sName ) ? $oUser->getName() : $oUser->getRealName().' ('.$oUser->getName().')';
			$oOutputPage->setPagetitle( wfMessage( 'bs-review-specialreview-header', 1, $sName )->text() );
			$oOutputPage->addJsConfigVars( 'bsSpecialReviewUserID', $oUser->getId() );
			$oOutputPage->addJsConfigVars( 'bsSpecialReviewUserName', $oUser->getName() );
		} else {
			$oOutputPage->setPagetitle( wfMessage( 'bs-review-specialreview-header', 0 )->text() );
		}

		$oOutputPage->addHTML( $sOut );
		$oOutputPage->addHTML(
			Html::element( 'div', array( 'id' => 'bs-review-overview') )
		);
		return true;
	}

	/**
	 *
	 * @global WebRequest $wgRequest
	 * @return type
	 */
	public static function ajaxGetOverview() {
		$oResponse = BsCAResponse::newFromPermission( 'workflowlist' );
		if( $oResponse->isSuccess() == false ) {
			return $oResponse;
		}

		global $wgRequest;
		$iUserID = $wgRequest->getInt( 'userID', 0 );
		$aFilter = FormatJson::decode( $wgRequest->getVal('filter', '[]') );

		$oUser = User::newFromId( $iUserID );
		if( $oUser->getId() > 0 ) {
			$aFilter[] = (object) array(
				'type' => 'int',
				'value' => $oUser->getName(),
				'field' => 'user_id',
			);
		}

		$aReviewList = self::filterReviewList(Review::getData(), $aFilter);
		$aReviews = array();

		foreach ( $aReviewList as $arrKey => $row ) {
			$row += $row['array'];
			if( $row['revs_status'] == '' ) {
				$row['revs_status'] = 'pending';
			}

			$oReview = new stdClass();
			$oReview->rev_id = $row['rev_id'];
			$oReview->page_title = Title::makeTitle( $row['page_namespace'], $row['page_title'] )->getPrefixedText();
			$oReview->owner_name = $row['owner_name'];
			$oReview->owner_real_name = $row['owner_real_name'];
			$oReview->rev_status = $row['revs_status'];
			$oReview->rev_status_text = wfMessage( 'bs-review-' . $row['revs_status'])->plain();
			$oReview->rejected = isset($row['rejected']) ? $row['rejected'] : false;
			$oReview->accepted = isset($row['accepted']) ? $row['accepted'] : 0;
			if ( !wfMessage( 'bs-review-' . $row['revs_status'] )->exists() ) {
				wfDebugLog( 'BS::Review' , 'message key does not exist'. wfMessage( 'bs-review-' . $row['revs_status'] )->plain() );
			}
			$oReview->accepted_text =
				wfMessage( 'bs-review-accepted', $oReview->accepted . '/' . $row['total'] )->plain() . '<br />'.
				wfMessage( 'bs-review-' . $row['revs_status'])->plain();
			$oReview->total = $row['total'];
			$oReview->endtimetamp = $row['endtimestamp'];
			$oReview->startdate = $row['startdate'];
			$oReview->enddate = $row['enddate'];
			$oReview->assessors = array();

			$bRejected = false;
			foreach ($row['assessors'] AS $arrAssessor) {
				$oAssessor = new stdClass();
				$oAssessor->revs_status = $arrAssessor['revs_status'];
				$oAssessor->name = $arrAssessor['name'];
				$oAssessor->real_name = $arrAssessor['real_name'];
				$oAssessor->timestamp = false;

				if( $arrAssessor['timestamp'] != '00.00' && $arrAssessor['revs_status'] >= 0) {
					$oAssessor->timestamp = $arrAssessor['timestamp'];
				}

				if( $oReview->accepted === 0 && $oAssessor->revs_status === '-3' ) {
					$bRejected = true;
				}

				$oReview->assessors[] = $oAssessor;
			}

			$oCurrentDate = DateTime::createFromFormat('YmdHis', wfTimestampNow());
			$oEndDate = DateTime::createFromFormat('d.m.Y', $oReview->enddate);
			if( $oEndDate && $oEndDate && $oEndDate < $oCurrentDate ) {
				$oReview->accepted_text =
					wfMessage( 'bs-review-accepted' )->plain() . ': ' .
					$oReview->accepted . '/' . $row['total'] . '<br />'.
					wfMessage( 'bs-review-expired' )->plain();
			} elseif( $bRejected ) {
				$oReview->accepted_text =
					wfMessage( 'bs-review-accepted' )->plain() . ': ' .
					$oReview->accepted . '/' . $row['total'] . '<br />'.
					wfMessage( 'bs-review-denied' )->plain();
			}

			$aReviews[] = $oReview;
		}

		$oResponse->setPayload( $aReviews );
		return $oResponse;
	}

	private static function filterReviewList($aReviewList, $aFilter) {
		$aUKeys = array();
		foreach ($aReviewList as $iKey => $row) {
			$row += $row['array'];
			if( $row['revs_status'] == '' ) {
				$row['revs_status'] = 'pending';
			}
			foreach( $aFilter as $oF ) {
				if( empty($oF->field) ) continue;

				if( !is_callable( __CLASS__."::{$oF->field}Filter")) continue;
				if( !call_user_func(__CLASS__."::{$oF->field}Filter", $oF, $row) ) {
					$aUKeys[] = $iKey;
					break;
				}
			}
		}

		foreach( $aUKeys as $iKey ) unset($aReviewList[$iKey]);
		return $aReviewList;
	}

	private static function page_titleFilter( $oFilter, $row ) {
		if( strpos(
				strtolower($row['page_title']),
				strtolower($oFilter->value)
			) !== false ) return true;
		return false;
	}

	private static function owner_nameFilter( $oFilter, $row ) {
		if( strpos(
				strtolower($row['owner_name']),
				strtolower($oFilter->value)
			) !== false ||
			strpos(
				strtolower($row['owner_real_name']),
				strtolower($oFilter->value)
			) !== false
		) return true;
		return false;
	}

	private static function assessorsFilter( $oFilter, $row ) {
		foreach($row['assessors'] as $arrAssessor) {
			if( strpos(
					strtolower($arrAssessor['name']),
					strtolower($oFilter->value)
				) === false &&
				strpos(
					strtolower($arrAssessor['real_name']),
					strtolower($oFilter->value)
				) === false
			) continue;
			return true;
		}
		return false;
	}

	private static function user_idFilter( $oFilter, $row ) {
		foreach($row['assessors'] as $arrAssessor) {
			if( $arrAssessor['name'] != $oFilter->value && $arrAssessor['real_name'] != $oFilter->value ) continue;
			return true;
		}
		return false;
	}
}