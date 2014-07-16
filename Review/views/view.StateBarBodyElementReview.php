<?php
/**
 * Renders the StateBar rating body element.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    $Id: view.StateBarBodyElementRating.php 9050 2013-03-28 15:14:36Z pwirth $
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the rating from the Rating extension.
 * @package    BlueSpice_Extensions
 * @subpackage Rating
 */
class ViewStateBarBodyElementReview extends ViewStateBarBodyElement {

	protected $sStatusText = '';
	protected $sStatusReasonText = '';
	protected $bVotable = false;
	protected $sComment = '';
	protected $aComments = array();
	protected $oReview = null;
	protected $aButtons = array();

	public function __construct() {
		parent::__construct();
		$this->sKey = 'Review';
		$this->mOptions = array();
	}

	/**
	 * This method actually generates the output
	 * @param ms method actually generates the outpuixed $params Comes from base class definition. Not used in this implementation.
	 * @return string HTML output
	 */
	public function execute( $params = false ) {

		$aOut[] = '<div class="bs-statebar-body-item" id="sbb-'.$this->sKey.'">';
		$aOut[] =	'<h4 class="bs-statebar-body-itemheading" id="sbb-'.$this->sKey.'-heading">'.wfMessage( 'bs-review-review' )->plain().'</h4>';
		$aOut[] =	'<div class="bs-statebar-body-itembody" id="sbb-'.$this->sKey.'-text">';
		$aOut[] =		$this->sStatusText;
		$aOut[] =		$this->sStatusReasonText;
		if( !empty($this->aComments) ) {
			$aOut[] = '<br /><br /><u>'.wfMessage('bs-review-comments')->plain()."</u><br />";
			foreach( $this->aComments as $sComment ) {
				$aOut[] = $sComment.'<br />';
			}
		}
		$aOut[] =	'</div>';
		if( $this->bVotable ) {
			$aOut[] = '<h4 class="bs-statebar-body-itemheading" id="sbb-DoReview-heading">'.wfMessage( 'bs-review-statebar-body-do-review' )->plain().'</h4>';
			$aOut[] = '<div class="bs-statebar-body-itembody" id="sbb-DoReview-text">';
			$aOut[] =	'<div class="bs-statebar-body-reviewvotesection">';
			if( !empty($this->sComment) ) {
				$aOut[] = $this->sComment.'<br />';
			}
			$aOut[] =	'<label for="bs-review-voteresponse-comment">';
			$aOut[] =		wfMessage('bs-review-commentinputlabel')->plain();
			$aOut[] =	'</label>';
			$aOut[] =	XML::input( 
				'bs-review-voteresponse-comment',
				false,
				'',
				array('id' => 'bs-review-voteresponse-comment')
			);
			
			foreach($this->aButtons as $aButton) {
				$aOut[] = sprintf( 
					'<a id="%s" href="#" class="%s" title="%s">%s</a>',
					$aButton['id'],
					$aButton['class'],
					$aButton['title'],
					$aButton['text']
				);
			}
			$aOut[] =	'</div>';
			$aOut[] = '<div>';
		}
		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}
	
	public function addButton($sId, $sClass, $sTitle, $sText) {
		$this->aButtons[] = array(
			'id' => $sId,
			'class' => $sClass,
			'title' => $sTitle,
			'text' => $sText
		);
	}

	public function setStatusText( $sStatusText ) {
		$this->sStatusText = $sStatusText;
		return $this;
	}
	public function setStatusReasonText( $sStatusReasonText ) {
		$this->sStatusReasonText = $sStatusReasonText;
		return $this;
	}
	public function setComment( $sComment ) {
		$this->sComment = $sComment;
		return $this;
	}
	public function setPreceedingCommentsList( $aComments ) {
		$this->aComments = $aComments;
		return $this;
	}
	public function setVotable( $bVotable = true ) {
		$this->bVotable = $bVotable;
		return $this;
	}
	public function setReview( $oReview ) {
		$this->oReview = $oReview;
		return $this;
	}
}
