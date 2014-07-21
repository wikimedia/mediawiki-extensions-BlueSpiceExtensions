<?php

/**
 * Describes a review.
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
/*
  DEFINE('RV_VOTE', 'vote');
  DEFINE('RV_SIGN', 'sign');
  DEFINE('RV_COMMENT', 'comment');
  DEFINE('RV_WORKFLOW', 'workflow');

  $rv_editable = array(RV_WORKFLOW, RV_COMMENT);
  $rv_sequential = array(RV_WORKFLOW, RV_SIGN);
  $rv_abortWhenDenied = array(RV_SIGN, RV_WORKFLOW);
 */

//$rv_access = array('user');
//Last Code Review RBV (30.0.2011)
// TODO RBV (30.06.11 15:10): Coding Conventions in all of the document
/**
 * Class that describes a review.
 * @package BlueSpice_Extensions
 * @subpackage Review
 */
class BsReviewProcess {

	/**
	 * Page id of article the review is associated with.
	 * @var int Article ID.
	 */
	var $pid;

	/**
	 * Defines if reviewers can edit the page.
	 * @var boolean|int true/false, 1/0
	 */
	var $editable;
	/**
	 * Defines if the order of reviewers should be respected
	 * @var boolean|int true/false, 1/0
	 */
	var $sequential;
	/**
	 * Defines if the review will be stopped, if a reviewer reject its review
	 * @var boolean|int true/false, 1/0
	 */
	var $abortable;
	/**
	 * Date when review starts.
	 * @var string yyyy-mm-dd
	 */
	// TODO MRG (13.06.11 01:00): Should be MWtimestamp.
	var $startdate;

	/**
	 * Date when review ends.
	 * @var string yyyy-mm-dd
	 */
	// TODO MRG (13.06.11 01:00): Should be MWtimestamp.
	var $enddate;

	/**
	 * Array of single steps the review has.
	 * @var array List of BsReviewProcessSteps.
	 */
	var $steps;

	/**
	 * User that initiated the review.
	 * @var int User id.
	 */
	var $owner;

	/**
	 * Save review as a template.
	 * @var bool. True if should be saved as template.
	 */
	var $tmpl_save = false;
	//var $tmpl_choice;
	//var $tmpl_name;

	protected $_aInjections = array();

	/**
	 * Constructor of BsReviewProcess
	 */
	function __construct() {
		$this->startdate = '';
		$this->enddate = '';
		$this->editable = false;
		$this->sequential = false;
		$this->abortable = false;
		$this->steps = array();
		$this->owner = -1;

		$this->_aInjections = array();
		wfRunHooks('BsReviewProcess::construct', array(&$this->_aInjections));
	}

	/**
	 * Is the process still ongoing?
	 * @return bool True if ongoing.
	 */
	function isActive() {
		if (($this->isStarted()) && (!$this->isFinished()))
			return true;
		else
			return false;
	}

	/**
	 * Is the process already started?
	 * @return bool True if started.
	 */
	function isStarted() {
		$curdate = strtotime("now");
		$startdate = strtotime($this->startdate);
		if ($curdate >= $startdate)
			return true;
		else
			return false;
	}

	//return values:
	//	false: 		not started or still running
	//	'date':		finished due to a deadline
	// 	'status':	finished due to confirmation of all participants
	//  'denied':   at least one participant has denied the workflow
	/**
	 * Is the review finished?
	 * @return string false: not started or still running; 'date': finished due to a deadline; 'status': finished due to confirmation of all participants; 'denied': at least one participant has denied the workflow.
	 */
	function isFinished() {
		$curdate = strtotime("now");
		$enddate = strtotime($this->enddate);
		if ($curdate > $enddate)
			return 'date';
		foreach ($this->steps as $st) {
			if ($st->status == -1) {
				return false;
			}
			if ($this->isAbortWhenDenied() &&
					($st->status == 0)) {
				return 'denied';
			}
		}
		return 'status';
	}

	// TODO MRG (13.06.11 01:10): Unify return values with isFinished
	/**
	 * Returns the current status of the review.
	 * @param string $date Timestamp as produced by time().
	 * @return string '': ongoing; 'nothing': not everybody voted; 'denied': someone said no; 'status': passed.
	 */
	function getStatus($date) {
		$curdate = strtotime("now");  //TODO: variable not used => line 105 commented
		$enddate = strtotime($this->enddate); //TODO: same
		foreach ($this->steps as $st) {
			//if($st->status != 1 && $curdate > $enddate) return 'date';
			if ($st->status == -1) {
				if ($date > time()) {
					return '';
				}
				return 'nothing';
			}
			if ($st->status == 0) {
				return 'denied';
			}
		}
		return 'status';
	}

	// TODO MRG (13.06.11 01:12): JSON or Array
	/**
	 * Returns the current voting status of the review.
	 * @return string Format is "NUMBER_UNKNOWN;NUMBER_NO;NUMBER_YES"
	 */
	function currentStatus() {
		$unknown = 0;
		$yes = 0;
		$no = 0;
		foreach ($this->steps as $st) {
			if ($st->status == -1)
				$unknown++;
			if ($st->status == 0)
				$no++;
			if ($st->status == 1)
				$yes++;
		}
		return $unknown . ';' . $no . ';' . $yes;
	}

	/**
	 * Getter for startdate.
	 * @return string Localized date.
	 */
	function getStartdate() {
		return $this->formattedDate($this->startdate);
	}

	/**
	 * Getter for enddate.
	 * @return string Localized date.
	 */
	function getEnddate() {
		return $this->formattedDate($this->enddate);
	}

	/**
	 * Setter for owner.
	 * @param int $userId User ID of owner.
	 */
	function setOwner($userId) {
		$this->owner = $userId;
	}

	/**
	 * Getter for owner.
	 * @return int User ID of owner.
	 */
	function getOwner() {
		return $this->owner;
	}

	/**
	 * Getter for Page ID (pid)
	 * @return int Page ID.
	 */
	function getPid() {
		return $this->pid;
	}

	// TODO MRG (13.06.11 01:16): Use function in framework.
	/**
	 * Returns a localized date string.
	 * @param string $datestring Date in format yyyy-mm-dd
	 * @return string Localized date string in format dd.mm.yyyy
	 */
	function formattedDate($datestring) {
		$date = strtotime($datestring);
		return date("d.m.Y", $date);
	}

	/**
	 * Returns the step that is currently active for a given user.
	 * @param int $uid User ID.
	 * @return mixed false or BsReviewProcessStep
	 */
	function currentStep($uid) {
		foreach ($this->steps as $step) {
			$bResult = null;
			foreach ($this->_aInjections as $oInjection) {
				if ($oInjection->currentStepIsActive($step, $uid) && $bResult !== false) {
					$bResult = true;
				} else {
					$bResult = false;
				}
			}
			if (($step->user == $uid || (!is_null($bResult) && $bResult !== false)) &&
					($step->status == -1))
				return $step;
		}
		return false;
	}

	/**
	 * Are there any steps for the current review? There are none if the review has not been saved yet.
	 * @return bool True if there are steps.
	 */
	function hasSteps() {
		if (is_array($this->steps) && count($this->steps) > 0) {
			return true;
		} else {
			return false;
		}
	}

	/**
	 * Stores a review to database.
	 * @return bool Always true.
	 */
	function store($update = false) {
		if ($this->pid == 0)
			return false;
		$dbw = wfGetDB(DB_MASTER);
		$data = array();
		global $wgDBtype;
		if ($wgDBtype == 'postgres' || $update) {
			// TODO: TL (18.08.2011, 09:30)
			// replace global
			// id is serial and auto increments
			// can't we use this for mysql too?
		} else {
			$data['rev_id'] = 0;
		}
		$data['rev_pid'] = $this->pid;
		$data['rev_editable'] = $this->editable;
		$data['rev_sequential'] = $this->sequential;
		$data['rev_abortable'] = $this->abortable;
		$data['rev_startdate'] = $this->startdate;
		$data['rev_enddate'] = $this->enddate;
		$data['rev_owner'] = $this->owner;
		if (!$update) {
			$dbw->insert('bs_review', $data);
		} else {
			$dbw->update('bs_review', $data, array('rev_pid = ' . $update));
		}

		// Get Review-ID
		$res = $dbw->select('bs_review', 'rev_id', "rev_pid=" . $this->pid, '', array("ORDER BY" => "REV_ID DESC"));
		$row = $dbw->fetchRow($res);
		$dbw->freeResult($res);
		$review_id = $row['rev_id'];

		$tmp_users = array();
		$i = 0;
		foreach ($this->steps as $step) {
			$data = array();
			global $wgDBtype;
			if ($wgDBtype == 'postgres') {
				// TODO: TL (18.08.2011, 09:30)
				// replace global
				// id is serial and auto increments
				// can't we use this for mysql too?
			} else {
				$data['revs_id'] = 0;
			}
			$data['revs_review_id'] = $review_id;
			$data['revs_user_id'] = $tmp_users[] = $step->user;
			$data['revs_status'] = $step->status;
			$data['revs_sort_id'] = $i;
			$data['revs_comment'] = $step->comment;
			$dbw->insert('bs_review_steps', $data);
			$i++;
		}
		return true;
	}

	/**
	 * This functions restarts the ended workflow.
	 */
	function restart() {
		$dbw = wfGetDB(DB_MASTER);

		// Get Review-ID
		$res = $dbw->select(
			'bs_review',
			'rev_id',
			"rev_pid=" . $this->pid,
			__METHOD__,
			array(
				'ORDER BY' => 'REV_ID DESC'
			)
		);
		$row = $dbw->fetchRow($res);
		$dbw->freeResult($res);
		$review_id = $row['rev_id'];

		$tbl = $dbw->tableName('bs_review');

		global $wgDBtype;
		if ($wgDBtype == 'postgres') {
			$dbw->query("UPDATE $tbl SET startdate=to_char(current_timestamp, 'YYYY-MM-DD HH24:MI:SS'), enddate=to_char(current_timestamp + interval '7 days', 'YYYY-MM-DD HH24:MI:SS') WHERE pid={$this->pid}");
		} else {
			$dbw->query("UPDATE $tbl SET rev_startdate=NOW(), rev_enddate=DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE rev_pid={$this->pid}");
		}

		foreach ($this->_aInjections as $oInjection) {
			$oInjection->restartProcess($review_id);
		}
	}

	/**
	 * This functions resets a interupted workflow.
	 */
	function reset($sComment = '') {
		$dbw = wfGetDB(DB_MASTER);

		// Get Review-ID and owner id
		$res = $dbw->select(
			'bs_review',
			array('rev_id', 'rev_owner'),
			"rev_pid=" . $this->pid,
			__METHOD__,
			array(
				'ORDER BY' => 'rev_id DESC'
			)
		);
		$row = $dbw->fetchRow($res);
		$dbw->freeResult($res);
		$review_id = $row['rev_id'];
		$owner_id = $row['rev_owner'];

		$tbl = $dbw->tableName('bs_review');

		global $wgDBtype;
		if ($wgDBtype == 'oracle') {
			$dbw->query("UPDATE $tbl SET rev_startdate=to_char(SYSDATE, 'YYYYMMDDHH24MISS'), rev_enddate=to_char(SYSDATE + interval '7 days', 'YYYYMMDDHH24MISS') WHERE rev_pid={$this->pid}");
		} elseif ($wgDBtype == 'postgres') {
			$dbw->query("UPDATE $tbl SET rev_startdate=to_char(current_timestamp, 'YYYYMMDDHH24MISS'), rev_enddate=to_char(current_timestamp + interval '7 days', 'YYYYMMDDHH24MISS') WHERE rev_pid={$this->pid}");
		} else {
			$dbw->query("UPDATE $tbl SET rev_startdate=NOW(), rev_enddate=DATE_ADD(NOW(), INTERVAL 7 DAY) WHERE rev_pid={$this->pid}");
		}

		$tbl = $dbw->tableName('bs_review_steps');
		$aUsersVoted = array();
		$iLastVotedId = 0;
		$iVoteAmount = 0;

		//Get all steps that have not been processed yet
		$res = $dbw->select(
			'bs_review_steps',
			array('revs_user_id', 'revs_sort_id', 'revs_comment'),
			array('revs_status > -1', 'revs_review_id = ' . $review_id),
			__METHOD__,
			array('ORDER BY revs_sort_id')
		);
		while ($row = $dbw->fetchRow($res)) {
			$aUsersVoted[] = $row;
			$iLastVotedId = $row['revs_sort_id'];
			$iVoteAmount++;
		}
		$iVoteAmount++;

		$dbw->query("UPDATE $tbl SET revs_sort_id=revs_sort_id+{$iVoteAmount} WHERE revs_review_id = {$review_id} AND revs_sort_id > {$iLastVotedId}");
		$dbw->query("UPDATE $tbl SET revs_status = -2 WHERE revs_review_id = {$review_id} AND revs_status = 1");
		$dbw->query("UPDATE $tbl SET revs_status = -3 WHERE revs_review_id = {$review_id} AND revs_status = 0");

		$data = array(
			'revs_review_id' => $review_id,
			'revs_user_id' => $owner_id,
			'revs_status' => -1,
			'revs_sort_id' => ++$iLastVotedId,
			'revs_comment' => "<u>".BsCore::getUserDisplayName().": </u>".$sComment,
		);
		foreach ($this->_aInjections as $oInjection) {
			$oInjection->createStepDefault($data);
		}
		$dbw->insert('bs_review_steps', $data);

		//Append the unprocessed steps to the list of steps
		$lastUserId = 0;
		foreach ($aUsersVoted as $aUser) {
			if ($aUser['revs_user_id'] == $lastUserId) {
				continue;
			}
			$lastUserId = $aUser['revs_user_id'];
			$lastInitialComment = $aUser['revs_comment'];
			//We remove the contributed parts of the comment and leave only
			//the initial part. Hacky hacky hacky...
			$matches = array();
			preg_match(
				'#.*?/em>(.*?) &rArr;.*?#si', $lastInitialComment, $matches
			);
			if( isset($matches[1]) ) {
				$lastInitialComment = trim($matches[1]);
			}

			$data = array(
				'revs_review_id' => $review_id,
				'revs_user_id'   => $aUser['revs_user_id'],
				'revs_status'    => -1,
				'revs_sort_id'   => ++$iLastVotedId,
				'revs_comment'   => $lastInitialComment,
			);

			foreach ($this->_aInjections as $oInjection) {
				$oInjection->createStepDefault($data);
			}
			$dbw->insert('bs_review_steps', $data);
		}
	}

	/**
	 * Load a review from database for a givn page id.
	 * @param int $pid Page ID the review should be loaded for.
	 * @return mixed BsReviewProcess if there is one, otherwise false.
	 */
	static function newFromPid($pid) {
		$oReviewProcess = false;
		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->select(
			'bs_review',
			'*',
			array(
				"rev_pid" => $pid
			),
			__METHOD__,
			array(
				"ORDER BY" => "REV_ID DESC"
			)
		);
		if ($row = $dbw->fetchRow($res)) {
			$oReviewProcess = new BsReviewProcess();
			$dbw->freeResult($res);

			//$hw_review->tag_txt = $tag;
			$oReviewProcess->pid = $pid;

			// starttag
			$oReviewProcess->editable = $row['rev_editable'];
			$oReviewProcess->sequential = $row['rev_sequential'];
			$oReviewProcess->abortable = $row['rev_abortable'];
			$oReviewProcess->startdate = $row['rev_startdate'];
			$oReviewProcess->enddate = $row['rev_enddate'];
			$oReviewProcess->owner = $row['rev_owner'];

			$rid = $row['rev_id'];

			$res = $dbw->select(
				'bs_review_steps',
				'*',
				array(
					"revs_review_id" => $rid
				),
				__METHOD__,
				array(
					"ORDER BY" => "revs_sort_id ASC"
				)
			);
			while ($row = $dbw->fetchRow($res)) {
				$oReviewProcess->steps[] = BsReviewProcessStep::newFromRow($row);
			}

			$dbw->freeResult($res);
		}

		return $oReviewProcess;
	}

	/**
	 * Creates a new review object from Input parameters.
	 * @global WebRequest $wgRequest
	 * @return BsReviewProcess The created review.
	 */
	static function newFromRequest() {
		global $wgRequest;
		$oReviewProcess = new BsReviewProcess();

		// rv_pid is stored in table hw_review as a smallint(5) unsigned
		$oReviewProcess->pid = $wgRequest->getInt('rv_pid', 0);

		// starttag
		$oReviewProcess->editable = $wgRequest->getBool('rv_editable');
		$oReviewProcess->sequential = $wgRequest->getBool('rv_sequential');
		$oReviewProcess->abortable = $wgRequest->getBool('rv_abortable');

		$oReviewProcess->startdate = date("Y-m-d", strtotime($wgRequest->getVal('rv_startdate', '')));
		$oReviewProcess->enddate = date("Y-m-d", strtotime($wgRequest->getVal('rv_enddate', '')));

		$paramRvStepName = $wgRequest->getArray('rv_step_name', array());

		$countParamRvStepName = count($paramRvStepName);
		if ($countParamRvStepName > 0) { // count($var) == 0 <==> Variable not set OR Array is empty
			$paramRvStepComment = $wgRequest->getArray('rv_step_comment', array());
			$paramRvStepStatus = $wgRequest->getIntArray('rv_step_status', array());
		}

		for ($i = 0; $i < $countParamRvStepName; $i++) {
			$oReviewProcess->steps[] = BsReviewProcessStep::newFromData($paramRvStepName[$i], $paramRvStepComment[$i], $paramRvStepStatus[$i]);
		}

		return $oReviewProcess;
	}

	/**
	 * Creates a new review from JSON imput.
	 * @param string $sJSON JSON input the review should be created from.
	 * @param array $aErrors List of errors in case anything goes wrong.
	 * @return mixed BsReviewProcess or false if anything goes wrong.
	 */
	static function newFromJSON($sJSON = '', &$aErrors) {
		$aErrors = array();
		$oJsonReview = json_decode($sJSON);
		$oReviewProcess = new BsReviewProcess();
		// rv_pid is stored in table hw_review as a smallint(5) unsigned
		$oReviewProcess->pid = BsCore::sanitize($oJsonReview->pid, -1, BsPARAMTYPE::NUMERIC);

		$oReviewProcess->editable = !!$oJsonReview->editable;
		$oReviewProcess->sequential = !!$oJsonReview->sequential;
		$oReviewProcess->abortable = !!$oJsonReview->abortable;

		if (!$oJsonReview->startdate) {
			$aErrors[] = 'startdate-missing';
		};
		$oReviewProcess->startdate = date("YmdHis", strtotime(BsCore::sanitize($oJsonReview->startdate, '', BsPARAMTYPE::STRING)));
		if (!$oJsonReview->enddate) {
			$aErrors[] = 'enddate-missing';
		};
		$oReviewProcess->enddate = date("YmdHis", strtotime(BsCore::sanitize($oJsonReview->enddate, '', BsPARAMTYPE::STRING)));

		if ($oReviewProcess->startdate > $oReviewProcess->enddate) {
			$aErrors[] = 'startdate-after-enddate';
		};

		$paramRvSteps = $oJsonReview->steps;
		if (count($paramRvSteps) <= 0) {
			$aErrors[] = 'no-reviewers';
		};
		// TODO an MRG: wir dieser block noch benötigt oder ist er inzwischen deprecated ??
		// Fixed mit isset
		if (isset($oJsonReview->tmpl_save) && $oJsonReview->tmpl_save) {
			$oReviewProcess->tmpl_save = true;
			$oReviewProcess->tmpl_name = addslashes($oJsonReview->tmpl_name);
			$oReviewProcess->tmpl_choice = $oJsonReview->tmpl_choice;
		}

		foreach ($paramRvSteps as $oStep) {
			if ($oStep->status == '' || $oStep->status == 'unknown')
				$oStep->status = '-1';
			if ($oStep->status == 'yes')
				$oStep->status = '1';
			if ($oStep->status == 'no')
				$oStep->status = '0';
			$sComment = BsCore::sanitize($oStep->comment, '', BsPARAMTYPE::STRING);
			if (strlen($sComment) > 255) {
				$aErrors[] = 'comment-too-long';
			};
			if (!$oStep->userid || !is_numeric($oStep->userid)) {
				//TODO: make sure you get a valid id
				//PW: wont work with realname!
				$oStep->userid = User::idFromName($oStep->name);
				if (!$oStep->userid) {
					$aErrors[] = 'user-not-found';
				}
			} else {
				$oTmpUser = User::newFromId($oStep->userid);
				if (!$oTmpUser) {
					$aErrors[] = 'user-not-found';
				}
			}

			$oReviewProcess->steps[] = BsReviewProcessStep::newFromData(
							BsCore::sanitize($oStep->userid, '', BsPARAMTYPE::INT), $sComment, BsCore::sanitize($oStep->status, '-1', BsPARAMTYPE::STRING)
			);
		}


		if (is_array($aErrors) && count($aErrors) > 0) {
			return false;
		} else {
			return $oReviewProcess;
		}
	}

	/**
	 * Returns a list of all users in the review process.
	 * @return array List of arrays: 'id'=> USERID, 'comment'=>COMMENT
	 */
	function getAllUsers() {
		$usersList = array();

		foreach ($this->steps as $step) {
			$usersList[] = array('id' => $step->user, 'comment' => $step->comment);
		}
		return $usersList;
	}

	/**
	 * Returns a list of users that are next in review process.
	 * @return array List of arrays: 'id'=> USERID, 'comment'=>COMMENT
	 */
	function getNextUsers() {
		$usersList = array();

		foreach ($this->steps as $step) {
			if ($step->status != -1)
				continue;
			if ($this->isSequential()) {
				$usersList[] = array('id' => $step->user, 'comment' => $step->comment);
				break;
			} else {
				$usersList[] = array('id' => $step->user, 'comment' => $step->comment);
			}
		}
		return $usersList;
	}

	/**
	 * Are the steps to be executed in sequential order?
	 * @return bool True if yes.
	 */
	function isSequential() {
		return $this->sequential;
	}

	/**
	 * Is the review editable?
	 * @return bool True if yes.
	 */
	function isEditable() {
		return $this->editable;
	}

	/**
	 * Should the review be stopped when at least on person denies?
	 * @return bool True if yes.
	 */
	function isAbortWhenDenied() {
		return $this->abortable;
	}

	static function userHasWaitingReviews($oUser) {
		$iUserId = $oUser->getId();
		$dbw = wfGetDB(DB_MASTER);

		$sTblReview = $dbw->tableName('bs_review');
		$sTblReviewSteps = $dbw->tableName('bs_review_steps');

		$aTables = array(
			'bs_review',
			'bs_review_steps'
		);
		$aConditions = array(
			$sTblReviewSteps . '.revs_review_id = ' . $sTblReview . '.rev_id',
			$sTblReview . '.rev_owner' => $oUser->getId()
		);
		$aOptions = array(
			'ORDER BY' => '' . $sTblReview . '.rev_id, ' . $sTblReviewSteps . '.revs_sort_id'
		);

		$aReviews = array();
		$iReviewsWaiting = 0;
		$res = $dbw->select($aTables, array('rev_id', 'revs_status'), $aConditions, __METHOD__, $aOptions);

		while ($row = $dbw->fetchRow($res)) {
			if (!isset($aReviews[$row['rev_id']])) {
				$aReviews[$row['rev_id']] = true;
			}
			if ($row['revs_status'] == -1) {
				$aReviews[$row['rev_id']] = false;
			} else if ($aReviews[$row['rev_id']] != false) {
				$aReviews[$row['rev_id']] = true;
			}
		}

		foreach ($aReviews as $bFinished) {
			if ($bFinished) {
				$iReviewsWaiting++;
			}
		}

		return $iReviewsWaiting;
	}

	/**
	 * Returns a list of pages that are being reviewed by a given user.
	 * @param int $uid ID if given user.
	 * @return array List of pages that are being reviewed.
	 */
	static function listReviews($uid, $bIgnoreStatus = true) {
		if (!$uid) {
			return array();
		}
		global $wgDBtype;

		$dbw = wfGetDB(DB_MASTER);

		$tables = array();
		$tables[] = 'bs_review';
		$tables[] = 'bs_review_steps';

		$tbl_rev = $dbw->tableName('bs_review');
		$tbl_step = $dbw->tableName('bs_review_steps');

		//PW: SYSDATE needed for Oracle - current_timestamp needed for postgres
		//$sStartdate = $wgDBtype == 'oracle' ? "to_char(SYSDATE, 'YYYYMMDDHH24MISS')" : $dbw->timestamp(time());
		switch ($wgDBtype) {
			case 'oracle': {
					$sStartdate = " AND rev_startdate <= to_char(SYSDATE, 'YYYYMMDDHH24MISS')";
					break;
				}
			case 'postgres': {
					$sStartdate = " AND to_date(rev_startdate,'YYYYMMDDHH24MISS') <= to_date(to_char(CURRENT_DATE,'YYYYMMDD'),'YYYYMMDD') ";
					break;
				}
			default: {
					$sStartdate = " AND rev_startdate <= " . $dbw->timestamp(time());
				}
		}
		$conds = array();
		$conds[] = $tbl_step . '.revs_review_id = ' . $tbl_rev . '.rev_id';   // implode tables

		$conds[] = '((' . $tbl_step . '.revs_user_id = ' . $uid . ' AND ' . $tbl_step . '.revs_delegate_to = 0) OR ' . $tbl_step . '.revs_delegate_to = ' . $uid . ')' .
				$sStartdate .
				' AND ' . $tbl_step . '.revs_status=-1)' .
				' OR (' . $tbl_rev . '.rev_owner = ' . $uid . '';

		//PW (14.06.2012): all fields need to be listet on GROUP BY for Oracle
		//                  (wont work on "*")
		//$aOptions = array('GROUP BY' => $tbl_rev.'.rev_id');
		//$sFields = $tbl_rev . '.*';
		$aRevColumns = array(
			'rev_id',
			'rev_pid',
			'rev_editable',
			'rev_sequential',
			'rev_abortable',
			'rev_startdate',
			'rev_enddate',
			'rev_owner'
		);

		$aRevsColumns = array(
			'revs_id',
			'revs_review_id',
			'revs_user_id',
			'revs_status',
			'revs_sort_id',
			'revs_comment',
			'revs_delegate_to',
			'revs_timestamp'
		);


		$aOptions = array('GROUP BY' => implode(',', $aRevColumns) . ',' . implode(',', $aRevsColumns));
		$sFields = implode(',', $aRevColumns) . ',' . implode(',', $aRevsColumns);

		$res = $dbw->select($tables, $sFields, $conds, __METHOD__, $aOptions);
		$pages = array();

		if ($res) {
			while ($row = $dbw->fetchRow($res)) {
				if ($row['rev_sequential']) {
					if ($wgDBtype == 'oracle') {
						$seq_res = $dbw->query('SELECT * FROM (
													SELECT revs_id, revs_user_id, revs_delegate_to, ROW_NUMBER() OVER (ORDER BY revs_sort_id) lmt
														FROM ' . $dbw->tableName('bs_review_steps') . '
														WHERE revs_review_id=' . $row['rev_id'] . '
														AND revs_status=-1
													)
													WHERE lmt BETWEEN 0 AND 1 '
						);
					} else {
						$seq_res = $dbw->select('bs_review_steps', 'revs_id, revs_user_id, revs_delegate_to', array('revs_review_id=' . $row['rev_id'],
							'revs_status=-1'), '', array("ORDER BY" => "revs_sort_id",
							"LIMIT" => "1")
						);
					}
					if ($dbw->numRows($seq_res) > 0) {
						$seq_row = $dbw->fetchRow($seq_res);
						if ($seq_row['revs_user_id'] == $uid || $seq_row['revs_delegate_to'] == $uid)
							$pages[] = $row['rev_pid'];
					}
					else if (!$bIgnoreStatus && $row['rev_owner'] == $uid) {
						$pages[] = $row['rev_pid'];
					}
				} else
					$pages[] = $row['rev_pid'];
			}
		}
		return array_unique($pages);
	}

	/**
	 * Removes all reviews for a given page.
	 * @param int $pid Page ID for which all reviews should be removed.
	 */
	static function removeReviews($pid) {
		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->select('bs_review', 'rev_id', array("rev_pid" => $pid));
		while ($row = $dbw->fetchRow($res)) {
			$dbw->delete('bs_review_steps', array('revs_review_id' => $row['rev_id']));
			//$dbw->delete('review', array('id'=>$row['id']));#
			$dbw->delete('bs_review', array('rev_pid' => $pid));
		}
	}

	static function removeReviewSteps($pid) {
		$dbw = wfGetDB(DB_MASTER);
		$res = $dbw->select('bs_review', 'rev_id', array("rev_pid" => $pid));
		while ($row = $dbw->fetchRow($res)) {
			$dbw->delete('bs_review_steps', array('revs_review_id' => $row['rev_id']));
		}
	}

}
