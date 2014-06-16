<?php
/**
 * Renders a blog item.
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
 * This view renders a blog item.
 * @package    BlueSpice_Extensions
 * @subpackage Blog
 */
class ViewBlogItem extends ViewBaseElement {

	/**
	 * Title of a blog item
	 * @var string The rendered title 
	 */
	protected $mTitle;
	/**
	 * URL that points to the original wiki article
	 * @var string URL 
	 */
	protected $mUrl;
	/**
	 * Information about when the original wiki article was created
	 * @var string Already rendered localized time string. Currently something like "10 days ago" 
	 */
	protected $mEntryDate;
	/**
	 * Link that points to the author's page
	 * @var string PrefixedText of the author's page 
	 */
	protected $mAuthorPage;
	/**
	 * Name of the author that will be displayed
	 * @var string Name or display name of the author
	 */
	protected $mAuthorName;
	/**
	 * The content of a blog item
	 * @var string Rendered HTML of the blog item body
	 */
	protected $mContent;
	/**
	 * Link to the talk page of the wiki article. Used to place comments.
	 * @var string URL
	 */
	protected $mTalkUrl;
	/**
	 * Number of edits the talk page already had. Indicates how many comments there are.
	 * @var int Number of edits
	 */
	protected $mTalkCount;
	/**
	 * Id of current version of wiki article. Used to produce a permalink.
	 * @var int Id of current revision.
	 */
	protected $mRevId;
	/**
	 * Link to trackback of this post
	 * @var string URL
	 */
	protected $mTrackbackUrl;
	/**
	 * Character that is used to separate the links in the actions list below a blog item.
	 * @var string The string that shall be used
	 */
	protected $mActionDivisor;

	/**
	 * Constructor
	 */
	public function __construct() {
		parent::__construct();
		$this->mActionDivisor = '&nbsp;&bull;&nbsp;';
	}

	/**
	 * This method actually generates the output
	 * @return string HTML output
	 */
	public function execute( $params = false ) {
		$sOut = $this->renderArticle();
		$sOut .= $this->renderActions();

		$sOut  = $this->itemWrap( $sOut );
		return $sOut;
	}

	/**
	 * Setter method for mTitle
	 * @param string $title The rendered title
	 */
	public function setTitle( $title ) {
		$this->mTitle = str_replace( '_', ' ', $title );
	}

	/**
	 * Setter method for mUrl
	 * @param string $url URL
	 */
	public function setUrl( $url ) {
		$this->mUrl = $url;
	}

	/**
	 * Setter method for mEntryDate
	 * @param string $date Already rendered date string
	 */
	public function setEntryDate( $date ) {
		$this->mEntryDate = $date;
	}

	/**
	 * Setter method for mAuthorPage
	 * @param string $page PrefixedText form of a user page
	 */
	public function setAuthorPage( $page ) {
		$this->mAuthorPage = $page;
	}

	/**
	 * Setter method for mAuthorName
	 * @param string $name Name of the author. Used as link label.
	 */
	public function setAuthorName( $name ) {
		$this->mAuthorName = $name;
	}

	/**
	 * Setter method for mContent
	 * @param string $content HTML that is to be displayed
	 */
	public function setContent( $content ) {
		$this->mContent = $content;
	}

	/**
	 * Setter method for mTalkUrl
	 * @param string $url URL
	 */
	public function setTalkURL( $url ) {
		$this->mTalkUrl = $url;
	}

	/**
	 * Setter method for mTalkCount
	 * @param int $count Number of edits of talk page
	 */
	public function setTalkCount( $count ) {
		$this->mTalkCount = $count;
	}

	/**
	 * Setter method for mRevId
	 * @param int $revid Id of revision
	 */
	public function setRevId( $revid ) {
		$this->mRevId = $revid;
	}

	/**
	 * Setter method for mTrackbackURL
	 * @param string $url URL
	 */
	public function setTrackbackURL( $url ) {
		$this->mTrackbackUrl = $url;
	}

	/**
	 * All blog items are individually wrapped by the HTML defined here.
	 * @param string $innerText The HTML of a blog item
	 * @return string HTML of blog item.
	 */
	protected function itemWrap( $innerText ) {

		$aOut = array();
		$aOut[] = '<div class="bs-blog-item">';
		$aOut[] = '  <h2 class="bs-blog-item-headline">';
		$aOut[] = $this->renderLink( array( 'href'   => $this->mUrl,
											'title'  => $this->mTitle ),
									$this->mTitle );
		$aOut[] = '  </h2>';

		$aOut[] = '  <div class="bs-blog-item-body">';
		$aOut[] = $innerText;
		$aOut[] = '  </div>';

		$aOut[] = '</div>';

		return implode( "\n", $aOut );
	}

	/**
	 * Renders the body of a blog item
	 * @return string Rendered HTML
	 */
	protected function renderArticle() {
		$sOut = '__NOTOC__ __NOEDITSECTION__';

		if ( $this->getOption('showInfo') ) {
			if ( $this->mAuthorPage ) {
				$sAuthorUserPageWikiLink =
					'[['.$this->mAuthorPage.'|'.$this->mAuthorName.']]';
			} else {
				$sAuthorUserPageWikiLink = $this->mAuthorName;
			}
			$sOut .= "\n".'<div class="bs-blog-item-info">'.$this->mEntryDate.' - '.$sAuthorUserPageWikiLink.'</div>'."\n";
		}
		$sOut .= "\n";

		$sOut .= $this->mContent;

		if ( $this->getOption( 'moreAtEndOfEntry' ) ) $sOut .= '&nbsp;';
		else $sOut .= "\n";

		$sParsedOut = BsCore::getInstance()->parseWikiText( $sOut, RequestContext::getMain()->getTitle() );

		$sOut = $sParsedOut;

		if ( $this->getOption( 'more' ) ) {
			$aLinkOptions = array(
				'href'   => $this->mUrl,
				'class'  => 'bs-blog-item-read-more',
				'title'  => wfMessage( 'bs-blog-read-more' )->plain()
			);
			if ( $this->getOption( 'moreInNewWindow' ) )
				$aLinkOptions['openInNewWindow'] = true;
			$sContent = wfMessage( 'bs-blog-read-more' )->plain();
			if ( $this->getOption( 'moreAtEndOfEntry' ) )
				$sContent .= '...';
			$sOut .= $this->renderLink( $aLinkOptions, $sContent );

			if ( !$this->getOption( 'moreAtEndOfEntry' ) ) $sOut .= $this->mActionDivisor;
			else $sOut .= '<br />';
		}



		return $sOut;
	}

	/**
	 * Renders the action bar below a blog item
	 * @return string HTML of action bar.
	 */
	protected function renderActions() {
		$sOut = '';
		$sOut .= '<div class="bs-blog-item-actions">';
		// Comments
		$sOut .= $this->renderLink(
			array(
				'href' => $this->mTalkUrl,
				'class' => 'bs-blog-item-comments',
				'title' => wfMessage( 'bs-blog-comments' )->plain() ),
			wfMessage( 'bs-blog-comments' )->plain() ) .' ('.$this->mTalkCount.')';

		// Trackback
		if ( $this->getOption( 'showTrackback' ) ) {
			$sOut .= $this->mActionDivisor;
			$sOut .= $this->renderLink(
				array(
					'href' => $this->mTrackbackURL,
					'class' => 'bs-blog-item-trackback',
					'title' => wfMessage( 'bs-blog-trackback' )->plain()
				),
				wfMessage( 'bs-blog-trackback' )->plain()
			);
		}

		// Permalink
		if ( $this->getOption( 'showPermalink' ) ) {
			$sOut .= $this->mActionDivisor;
			$sOut .= $this->renderLink(
				array(
					'href' => $this->mUrl,
					'query' => 'oldid='.$this->mRevId,
					'class' => 'bs-blog-item-permalink',
					'title' => wfMessage( 'bs-blog-permalink' )->plain()
				),
				wfMessage( 'bs-blog-permalink' )->plain()
			);
		}
		$sOut .= '</div>';
		return $sOut;
	}

}
