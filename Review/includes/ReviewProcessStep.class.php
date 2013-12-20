<?php 
/**
 * Describes a single review step.
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
 * Class that describes a single review step.
 * @package BlueSpice_Extensions
 * @subpackage Review
 */
class BsReviewProcessStep {
	public static $_aFieldMap = array(
		'revs_user_id' => 'user',
		'revs_comment' => 'comment',
		'revs_status' => 'status',
		'revs_sort_id' => 'sort_id',
		'revs_delegate_to' => 'delegate_to'
	);
	/**
	 * The user that is in charge of the step.
	 * @var int User id
	 */
	var $user;
	/**
	 * Comment for the user of the current step.
	 * @var string Text of the comment.
	 */
	var $comment;
	/**
	 * Voting status of the current user.
	 * @var int -1 not voted, 0 no, 1 yes.
	 */
	var $status;
	var $sort_id;
	var $delegate_to;
	
	public static function newFromRow($row) {
		$oStep = new self();
		$oStep->status = -1;
		$oStep->sort_id = 0;
		foreach($row as $prop_key => $prop_value) {
			if(!isset(self::$_aFieldMap[$prop_key])) {
				continue;
			}
			$field = self::$_aFieldMap[$prop_key];
			$oStep->$field = $prop_value;
		}
		return $oStep;
	}
	
	public static function newFromData($user, $comment, $status=-1, $sort_id=0, $delegate_to=0) {
		$oStep = new self();
		$oStep->user    = $user;
		$oStep->comment = $comment;
		$oStep->status  = $status;
		$oStep->sort_id = $sort_id;
		$oStep->delegate_to = $delegate_to;
		return $oStep;
	}

	/**
	 * Constructor for BsReviewProcessStep class.
	 * @param int $user ID of the user.
	 * @param string $comment Comment for the user.
	 * @param int $status Voting status of the step.
	 */
	function __construct() {}
}