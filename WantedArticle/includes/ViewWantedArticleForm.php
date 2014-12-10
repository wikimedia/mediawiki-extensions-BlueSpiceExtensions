<?php
/**
 * Renders the wanted article form in the left column.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage WantedArticle
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the wanted article form in the left column.
 * @package    BlueSpice_Extensions
 * @subpackage WantedArticle
 */
class ViewWantedArticleForm extends ViewBaseElement {
	static $iFormCounter = 0;

	protected $bShowCreateArticle  = false;
	protected $bShowSuggestArticle = false;
	protected $bDisplayHeading = false;
	protected $bDisplayLabels  = false;
	protected $sFormVariant    = 'single-textfield'; //Possible values 'single-textfield', 'seperate-textfields'

	public function execute( $params = false ) {
		$sControls = '';
		$aAdditionalClassAttr = array();
		self::$iFormCounter++;
		switch( $this->sFormVariant ) {
			case 'single-textfield': $sControls = $this->renderSingleTextField();
				break;
			case 'seperate-textfields': $sControls = $this->renderSeperateTextFields();
				break;
			case 'tag-form' : $sControls = $this->renderTagForm();
				$aAdditionalClassAttr[] = 'bs-wanted-article-tag';
				break;
			default:
				break;
		}
		return $this->renderForm( $sControls, $aAdditionalClassAttr );
	}

	/**
	 *
	 * @return string
	 */
	private function renderSingleTextField() {
		$aOut = array();

		$aOut[] = '<input id="bs-wantedarticle-composite-textfield-'.self::$iFormCounter.'"';
		$aOut[] = '       name="bs-wantedarticle-composite-textfield"';
		$aOut[] = '       type="text"';
		$aOut[] = '       class="bs-unfocused-textfield bs-wantedarticle-composite-textfield"';
		$aOut[] = '       value="'.wfMessage( 'bs-wantedarticle-single-textfield-defaulttext' )->plain().'"';
		$aOut[] = '       data-defaultvalue="'.wfMessage( 'bs-wantedarticle-single-textfield-defaulttext' )->plain().'" />';
		$aOut[] = '<button id="bs-wantedarticle-suggestbutton-'.self::$iFormCounter.'" class="bs-linkbutton bs-wantedarticle-suggestbutton">';
		$aOut[] = wfMessage( 'bs-wantedarticle-single-textfield-suggestbutton-text' )->plain();
		$aOut[] = '</button>';
		if( $this->bShowCreateArticle === true ) {
			$aOut[] = '<button id="bs-wantedarticle-createbutton-'.self::$iFormCounter.'" class="bs-linkbutton bs-wantedarticle-createbutton">';
			$aOut[] = wfMessage( 'bs-wantedarticle-single-textfield-createbutton-text' )->plain();
			$aOut[] = '</button>';
		}

		return implode( "\n", $aOut );
	}

	/**
	 *
	 * @return string
	 */
	private function renderTagForm() {
		$aOut = array();

		$aOut[] = '<input id="bs-wantedarticle-composite-textfield-'.self::$iFormCounter.'"';
		$aOut[] = '       name="bs-wantedarticle-composite-textfield-'.self::$iFormCounter.'"';
		$aOut[] = '       type="text"';
		$aOut[] = '       class="bs-unfocused-textfield bs-wantedarticle-composite-textfield bs-wanted-article-tag"';
		$aOut[] = '       value="'.wfMessage( 'bs-wantedarticle-single-textfield-defaulttext' )->plain().'" ';
		$aOut[] = '       data-defaultvalue="'.wfMessage( 'bs-wantedarticle-single-textfield-defaulttext' )->plain().'" />';
		$aOut[] = '<input type="submit" id="bs-wantedarticle-suggestbutton-'.self::$iFormCounter.'" class="bs-wantedarticle-suggestbutton bs-wantedarticle-tag-suggestbutton"';
		$aOut[] = '       value = "'.wfMessage( 'bs-wantedarticle-single-textfield-suggestbutton-text' )->plain().'"';
		$aOut[] = '/>';

		return implode( "\n", $aOut );
	}


	/**
	 *
	 */
	private function renderSeperateTextFields() {
		throw new BsException( 'Not implemented' ); // TODO RBV (06.10.10 11:06): implement
	}

	/**
	 *
	 * @param string $sControls
	 * @return string
	 */
	private function renderForm( $sControls, $aAdditionalClassAttr = array() ) {
		$aOut = array();

		$sAdditionalClassAttr = '';
		foreach($aAdditionalClassAttr as $sClass) $sAdditionalClassAttr .= ' '.$sClass;

		$aOut[] = '<div id="bs-wantedarticle-container-'.self::$iFormCounter.'" class="bs-wantedarticle-container">';
		$aOut[] = ' <form action="#" id="bs-wantedarticle-form-'.self::$iFormCounter.'" class="bs-wantedarticle-form'.$sAdditionalClassAttr.'">';
		$aOut[] = '  <div>';
		$aOut[] = $sControls;
		$aOut[] = '  </div>';
		$aOut[] = ' </form>';
		$aOut[] = '</div>'; // #bs-wantedarticle-container

		return implode( "\n", $aOut );
	}

	/**
	 *
	 * @param <type> $bShowCreateArticle
	 * @return ViewWantedArticleForm
	 */
	public function setShowCreateArticle( $bShowCreateArticle ) {
		$this->bShowCreateArticle = $bShowCreateArticle;
		return $this;
	}

	/**
	 *
	 * @param <type> $bShowSuggestArticle
	 * @return ViewWantedArticleForm
	 */
	public function setShowSuggestArticle( $bShowSuggestArticle ) {
		$this->bShowSuggestArticle = $bShowSuggestArticle;
		return $this;
	}

	/**
	 *
	 * @param <type> $bDisplayHeading
	 * @return ViewWantedArticleForm
	 */
	public function setDisplayHeading( $bDisplayHeading ) {
		$this->bDisplayHeading = $bDisplayHeading;
		return $this;
	}

	/**
	 *
	 * @param <type> $bDisplayLables
	 * @return ViewWantedArticleForm
	 */
	public function setDisplayLabels( $bDisplayLables ) {
		$this->bDisplayLabels = $bDisplayLables;
		return $this;
	}

	/**
	 * Influences the rendering of the form element
	 * @param string $sFormVariant Possible values 'single-textfield', 'seperate-textfields', 'tag-form'
	 * @return ViewWantedArticleForm
	 */
	public function setFormVariant( $sFormVariant ) {
		$this->sFormVariant = $sFormVariant;
		return $this;
	}
}
