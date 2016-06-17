<?php
/**
 * This class serves as a backend for the review overview store.
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
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @package    Bluespice_Extensions
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 *
 */
class BSApiReviewOverviewStore extends BSApiExtJSStoreBase {

	public function getAllowedParams() {
		return parent::getAllowedParams() + array(
			'userID' => array(
				ApiBase::PARAM_TYPE => 'integer',
				ApiBase::PARAM_REQUIRED => false,
				ApiBase::PARAM_DFLT => 0,
				ApiBase::PARAM_HELP_MSG =>
					'apihelp-bs-reviewoverview-store-param-userid',
			),
		);
	}

	public function getParameter( $paramName, $parseLimit = true ) {
		$param = parent::getParameter( $paramName, $parseLimit );
		if( $paramName !== 'filter' ) {
			return $param;
		}
		//Force filter when Special:ReviewOverview/<username>
		$iUserID = $this->getParameter( 'userID' );
		$oUser = User::newFromId( $iUserID );
		if( $oUser->getId() < 1 ) {
			return $param;
		}
		$param[] = (object) array(
			'type' => 'int',
			'value' => $oUser->getName(),
			'field' => 'user_id',
		);
		return $param;
	}

	public function makeData( $sQuery = '' ) {
		$aReviewList = Review::getData();
		$aReviews = array();

		foreach ( $aReviewList as $arrKey => $row ) {
			$row += $row['array'];
			if( !$oDataRow = $this->makeDataRow( $row ) ) {
				continue;
			}
			$aReviews[] = $oDataRow;
		}

		return $aReviews;
	}

	protected function makeDataRow( $row ) {
		if( $row['revs_status'] == '' ) {
			$row['revs_status'] = 'pending';
		}

		$oTitle = Title::makeTitle(
			$row['page_namespace'],
			$row['page_title']
		);
		$oReview = new stdClass();
		$oReview->rev_id = $row['rev_id'];
		$oReview->page_title = $oTitle->getPrefixedText();
		$oReview->owner_name = $row['owner_name'];
		$oReview->owner_real_name = $row['owner_real_name'];
		$oReview->rev_status = $row['revs_status'];
		$oReview->rev_sequential = $row['rev_sequential'];
		$oReview->rejected = isset($row['rejected']) ? $row['rejected'] : false;
		$oReview->accepted = isset($row['accepted']) ? $row['accepted'] : 0;
		$oReview->total = $row['total'];
		$oReview->endtimetamp = $row['endtimestamp'];
		$oReview->startdate = $row['startdate'];
		$oReview->enddate = $row['enddate'];
		$oReview->assessors = array();

		$bRejected = false;
		foreach ($row['assessors'] AS $arrAssessor) {
			$oAssessor = $this->makeDataRowAssessor(
				$oTitle,
				$oReview,
				$arrAssessor
			);
			if( !$oAssessor ) {
				continue;
			}
			if( $oReview->accepted === 0 && $oAssessor->revs_status === '-3' ) {
				$bRejected = true;
			}
			$oReview->assessors[] = $oAssessor;
		}
		$oReview->rev_status_text = $this->makeStatusText(
			$oTitle,
			$oReview,
			$row,
			$bRejected
		);
		$oReview->accepted_text = $this->makeAcceptedText(
			$oTitle,
			$oReview,
			$row,
			$bRejected
		);

		return $oReview;
	}

	protected function makeStatusText( Title $oTitle, $oReview, $row, $bRejected, $sText = '' ) {
		//TODO: Do not generate msg keys - there shoud be a config class
		//where res_status and associated keys could be registered via hook.
		//Known keys are: bs-review-expired, bs-review-denied
		$sText = wfMessage( 'bs-review-' . $row['revs_status'])->plain();
		return $sText;
	}

	protected function makeAcceptedText( Title $oTitle, $oReview, $row, $bRejected, $sText = '' ) {
		$oCurrentDate = DateTime::createFromFormat(
			'YmdHis',
			wfTimestampNow()
		);
		$oEndDate = DateTime::createFromFormat(
			'd.m.Y',
			$oReview->enddate
		);

		$sacceptedMsg = wfMessage( 'bs-review-accepted' )->plain();
		$sText = "$sacceptedMsg: $oReview->accepted / {$row['total']}<br />";
		if( $oEndDate && $oEndDate && $oEndDate < $oCurrentDate ) {
			$sText .= wfMessage( 'bs-review-expired' )->plain();
		} elseif( $bRejected ) {
			$sText .= wfMessage( 'bs-review-denied' )->plain();
		} else {
			//TODO: Do not generate msg keys - there shoud be a config class
			//where res_status and associated keys could be registered via hook.
			//Known keys are: bs-review-expired, bs-review-denied
			$oStatusMsg = wfMessage( "bs-review-{$row['revs_status']}" );
			if ( !$oStatusMsg->exists() ) {
				wfDebugLog(
					'BS::Review',
					'message key does not exist'. $oStatusMsg->plain()
				);
			}
			$sText .= $oStatusMsg->plain();
		}
		return $sText;
	}

	protected function makeDataRowAssessor( Title $oTitle, $oReview, $arrAssessor ) {
		$oAssessor = new stdClass();
		$oAssessor->revs_status = $arrAssessor['revs_status'];
		$oAssessor->name = $arrAssessor['name'];
		$oAssessor->real_name = $arrAssessor['real_name'];
		$oAssessor->timestamp = false;

		$bTimestap =
			$arrAssessor['timestamp'] != '00.00'
			&& $arrAssessor['revs_status'] >= 0
		;
		if( $bTimestap ) {
			$oAssessor->timestamp = $arrAssessor['timestamp'];
		}

		return $oAssessor;
	}

	public static function assessorsFilter( $oFilter, $row ) {
		foreach( $row['assessors'] as $arrAssessor ) {
			if( strpos(
					strtolower( $arrAssessor['name'] ),
					strtolower( $oFilter->value )
				) === false &&
				strpos(
					strtolower( $arrAssessor['real_name'] ),
					strtolower( $oFilter->value )
				) === false
			) continue;
			return true;
		}
		return false;
	}

	/**
	 * Performs string filtering based on given filter of type string on a dataset
	 * @param object $oFilter
	 * @param oject $aDataSet
	 * @return boolean true if filter applies, false if not
	 */
	public function filterString( $oFilter, $aDataSet ) {
		if( !is_string( $oFilter->value ) ) {
			return true; //TODO: Warning
		}
		if( $oFilter->field !== 'assessors' ) {
			return parent::filterString( $oFilter, $aDataSet );
		}
		$aFieldValue = $aDataSet->{$oFilter->field};
		//Assessors is an array of object, so every entry needs to be handled
		foreach( $aFieldValue as $oAssessor ) {
			$sFilterField = empty($oAssessor->real_name)
				? $oAssessor->name
				: $oAssessor->real_name
			;
			$bRes = BsStringHelper::filter(
				$oFilter->comparison,
				$sFilterField,
				$oFilter->value
			);
			if( !$bRes ) {
				continue;
			}
			return true;
		}
		return false;
	}
}