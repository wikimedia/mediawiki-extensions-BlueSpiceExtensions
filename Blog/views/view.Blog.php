<?php
/**
 * Renders the blog list.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage Blog
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * This view renders the blog list.
 * @package    BlueSpice_Extensions
 * @subpackage Blog
 */
class ViewBlog extends ViewBaseElement {

	/**
	 * Counter that increments with every ViewBlog instance. Used to disambiguate ids when more than one blog tag is used on a page.
	 * @var int Number of instances.
	 */
	private static $iFormElementCount = 0;

	/**
	 * Constructor
	 * @param I18n $I18N Internationalisation object that is used for all messages
	 */
	public function __construct() {
		parent::__construct();
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sOut = '';
		if ( $this->getOption( 'shownewentryfield' ) && $this->getOption( 'newentryfieldposition' ) == 'top' )
			$sOut .= $this->renderShowNewEntryField();
		$sOut .= parent::execute();
		$sOut .= $this->renderShowAll();
		if ( $this->getOption( 'shownewentryfield' ) && $this->getOption( 'newentryfieldposition' ) == 'bottom' )
			$sOut .= $this->renderShowNewEntryField();
		return $sOut;
	}

	/**
	 * Renders a link that points to a complete list of blog entries
	 * @return string HTML of link. 
	 */
	public function renderShowAll() {
		if ( !$this->getOption( 'showall' ) )
			return '';
		$sOut = $this->renderLink(
				array(
					'href' => BsCore::getRequestURI(),
					'query' => 'showall=true',
					'title' => wfMessage( 'bs-blog-show-all' )->plain()
				),
				wfMessage( 'bs-blog-show-all' )->plain()
		);
		return $sOut;
	}

	/**
	 * Renders a form field that allows to create a new blog entry.
	 * @return string HTML of form. 
	 */
	public function renderShowNewEntryField() {
		global $wgScriptPath, $wgUser;
		$sId = 'blog'.self::$iFormElementCount;
		self::$iFormElementCount++;
		$sParentpage = ( $this->getOption('parentpage') ) ? $this->getOption('parentpage') : '';
		$aOut = array();
		$aOut[] = '<script type="text/javascript">';
		$aOut[] = 'hw_'.$sId.'_submit = function() {';
		$aOut[] = '  pagename = "'.$sParentpage.'" + document.getElementById("'.$sId.'Input").value;';
		//$aOut[] = '  if(!check_pagename(pagename)) return false;';
		$aOut[] = '  pagename = pagename.replace(" ", "_");';
		if ( $wgUser->isLoggedIn() || BsConfig::get( 'MW::Blog::ShowTagFormWhenNotLoggedIn' ) != true ) {
			$aOut[] = '  url = unescape("'.$wgScriptPath.'/index.php?title='.( $this->getOption( 'namespace' ) ? $this->getOption( 'namespace' ).':' : '' ).'"+pagename+"%26action"+"=edit"+"%26blogcat='.$this->getOption( 'blogcat' ).'");';
		} else {
			$aOut[] = '  url = unescape("'.$wgScriptPath.'/index.php%3Ftitle=special:userlogin%26returnto='.$this->getOption( 'namespace' ).':"+pagename);';
		}
		$aOut[] = '  window.location.href = url;';
		$aOut[] = '}';
		$aOut[] = '</script>';

		$aOut[] = '<div class="bs-blog-wrapper clearfix">';
		$aOut[] = '  <form action="#" id="'.$sId.'form" action="get" onsubmit="hw_'.$sId.'_submit();return false;">';
		$aOut[] = '    <div class="bs-blog-form-center">';
		$aOut[] = '      <h2 class="bs-blog-header">'.wfMessage( 'bs-blog-form-title-text' )->plain().'</h2>';
		$aOut[] = '      <input id="'.$sId.'Input" class="bs-blog-newentry-input" name="newpage" type="text" value="'.wfMessage( 'bs-blog-form-inline-text' )->plain().'" onfocus="if (this.value==\''.wfMessage( 'bs-blog-form-inline-text' )->plain().'\') this.value=\'\';" />';
		$aOut[] = '      <input type="hidden" name="blogcat" value="'.$this->getOption('blogcat').'" />';
		$aOut[] = '      <input type="submit" name="go" class="bs-blog-newentry-submit" id="'.$sId.'Button" value="'.wfMessage( 'bs-blog-form-button-text' )->plain().'" onclick="hw_'.$sId.'_submit(); return false;"/>&nbsp;';
		$aOut[] = '    </div>';
		$aOut[] = '  </form>';
		$aOut[] = '</div>';

		return implode( "", $aOut );
	}

}