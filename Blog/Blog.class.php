<?php
/**
 * Blog extension for BlueSpice
 *
 * Displays a blog style list of pages.
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
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Sebastian Ulbricht
 * @version    2.22.0 stable

 * @package    BlueSpice_Extensions
 * @subpackage Blog
 * @copyright  Copyright (C) 2010 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 *
 * v1.1.0
 * - Implemented CR Comments
 * - Fixed bugs
 * v1.0.0
 * -reset version numbering
 * v2.2
 * - new image rendering (renders images as thumbs)
 * - entry fields with categories
 * v2.1.1
 * - new attributes ns, shownewentryfield and newentryfieldposition
 * v2.1.0
 * - use views for error and output
 * v2.0.1
 * - Code refactored / beautified
 * - Removed usage of global variables
 * - Improved use of MediaWiki database abstraction layer.
 * v2.0.0
 * - Migrate to Blue spice
 * v1.1.3
 * - $_GET, $_POST, $_REQUEST => $hw->getParam (for security reasons)
 * v1.1.2
 * - open more link in new window
 * v1.1.1
 * - prevent recursive parsing
 */

// Last Code Review RBV (30.06.2011)

/**
 * Base class for page template extension
 * @package BlueSpice_Extensions
 * @subpackage Blog
 */
class Blog extends BsExtensionMW {

	/**
	 * Constructor of Blog class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::VARIABLE; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'Blog',
			EXTINFO::DESCRIPTION => 'Display a blog style list of pages.',
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION     => '2.22.0',
			EXTINFO::STATUS      => 'beta',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::Blog';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of Blog extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'UnknownAction' );
		$this->setHook( 'SkinTemplateContentActions' );
		$this->setHook( 'EditFormPreloadText' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		$this->setHook( 'BSNamespaceManagerBeforeSetUsernamespaces', 'onBSNamespaceManagerBeforeSetUsernamespaces');
		$this->setHook( 'BSRSSFeederGetRegisteredFeeds' );
		$this->setHook( 'BeforePageDisplay' );

		// Trackback is not fully functional in MW and thus disabled.
		BsConfig::registerVar('MW::Blog::ShowTrackback', false, BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_BOOL, 'bs-blog-pref-ShowTrackback');
		// Show permalink link at end of a blog entry
		BsConfig::registerVar('MW::Blog::ShowPermalink', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-ShowPermalink', 'toggle');
		// Show info line below blog entry heading
		BsConfig::registerVar('MW::Blog::ShowInfo', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-ShowInfo', 'toggle');
		// Open more link in new window
		BsConfig::registerVar('MW::Blog::MoreInNewWindow', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-MoreInNewWindow', 'toggle');
		// Should a link to complete list of blog entries be rendered?
		BsConfig::registerVar('MW::Blog::ShowAll', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-ShowAll', 'toggle');
		// Place more link at end of blog entry instead of next line
		BsConfig::registerVar('MW::Blog::MoreAtEndOfEntry', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-MoreAtEndOfEntry', 'toggle');
		// Possible values are "creation" and "title"
		//BsConfig::registerVar('MW::Blog::SortBy',			'creation',	BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING, $this->mI18N);
		BsConfig::registerVar( 'MW::Blog::SortBy', 'creation', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-blog-pref-SortBy', 'select' );
		// Number of blog entries that shall be displayed initially
		BsConfig::registerVar('MW::Blog::ShowLimit', 10, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-blog-pref-ShowLimit', 'int');
		// Show form that allows to create a new blog entry
		BsConfig::registerVar('MW::Blog::ShowNewEntryField', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-ShowNewEntryField', 'toggle');
		// Position of new entry field. Possible values are "top" and "bottom"
		//BsConfig::registerVar('MW::Blog::NewEntryFieldPosition',	'top',	BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING, $this->mI18N);
		BsConfig::registerVar( 'MW::Blog::NewEntryFieldPosition', 'top', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-blog-pref-NewEntryFieldPosition', 'select' );
		// Maximum number of characters befor an entry is automatically cut
		BsConfig::registerVar('MW::Blog::MaxEntryCharacters', 1000, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-blog-pref-MaxEntryCharacters', 'int');
		// Defines how images should be rendered. Possible values: full|thumb|none
		//BsConfig::registerVar('MW::Blog::ImageRenderMode', 'thumb', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING, $this->mI18N);
		BsConfig::registerVar( 'MW::Blog::ImageRenderMode', 'thumb', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-blog-pref-ImageRenderMode', 'select' );
		BsConfig::registerVar( 'MW::Blog::ShowTagFormWhenNotLoggedIn', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-ShowTagFormWhenNotLoggedIn', 'toggle');

		global $wgServer, $wgScriptPath;
		//Register Application for ApplicationBar in BlueSpice-Skin
		$arRegisteredApplications = BsConfig::get( 'MW::Applications' );
		$arRegisteredApplications[] = array(
			'name'         => 'Blog',
			'displaytitle' => 'Blog',
			'url'          => $wgServer.$wgScriptPath.'/index.php?action=blog'
		);
		BsConfig::set( 'MW::Applications', $arRegisteredApplications );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		$oOutputPage->addModules( 'ext.bluespice.blog' );

		return true;
	}

	/**
	 * Defines the options for preferences settings of ImageRenderMode and NewEntryFieldPosition
	 * @param string $sAdapterName Key of the adapter, typically MW
	 * @param BsConfig $oVariable Contains the object of the preference that shall be rendered.
	 * @return array Array of preference settings.
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array();
		switch ( $oVariable->getName() ) {
			case 'ImageRenderMode' :
				$aPrefs = array( 
					'options' => array( 
						'thumb' => 'thumb', 
						'full'  => 'full',
						'none'  => 'none'	
					)
				);
				break;
			case 'NewEntryFieldPosition' :
				$aPrefs = array( 
					'options' => array( 
						'top' => 'top', 
						'bottom' => 'bottom'
					)
				);
				break;
			case 'SortBy' :
				$aPrefs = array( 
					'options' => array( 
						'creation' => 'creation', 
						'title' => 'title' 
					)
				);
				break;
		}
		return $aPrefs;
	}

	public function onBSNamespaceManagerBeforeSetUsernamespaces( $classInstance, &$bsSystemNamespaces ) {
		$bsSystemNamespaces[102] = 'NS_BLOG';
		$bsSystemNamespaces[103] = 'NS_BLOG_TALK';
		return true;
	}

	//Hint: http://svn.wikimedia.org/viewvc/mediawiki/trunk/extensions/examples/Content_action.php?view=markup
	/**
	 * Removes all content actions from action tabs and highlights blog in application context. Called by SkinTemplateContentActions hook.
	 * @param array $content_actions List of actions to be displayed in action tabs
	 * @return bool allow other hooked methods to be executed. Always false in order to prevent any content actions to be implemented. 
	 */
	public function onSkinTemplateContentActions( &$content_actions ) {
		$sAction = $this->getRequest()->getVal( 'action', '' );
		if ( ( $sAction != 'blog' ) ) {
			return true;
		} else {
			$content_actions = array();
		}
		BsConfig::set( 'MW::ApplicationContext', 'Blog' );
		return false;
	}

	/**
	 * Registers bs:blog and bs:blog:more tag. Called by ParserFirstCallInit hook.
	 * @param Parser $parser MediaWiki Parser object
	 * @return bool allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$parser ) {
		// used for legacy reasons
		$parser->setHook( 'blog', array( &$this, 'onBlog' ) );
		$parser->setHook( 'more', array( &$this, 'onMore' ) );
		$parser->setHook( 'bs:blog', array( &$this, 'onBlog' ) );
		$parser->setHook( 'bs:blog:more', array( &$this, 'onMore' ) );
		return true;
	}

	/**
	 * Inject tags into InsertMagic
	 * @param Object $oResponse reference
	 * $param String $type
	 * @return always true to keep hook running
	 */
	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'tags' ) return true;

		$oResponse->result[] = array(
			'id' => 'bs:blog',
			'type' => 'tag',
			'name' => 'blog',
			'desc' => wfMessage( 'bs-blog-tag-blog-desc' )->plain(),
			'code' => '<bs:blog />',
		);

		return true;
	}

	/**
	 * Hides the bs:blog:more tag in output. Called by parser function.
	 * @param string $input Inner HTML of bs:blog:more tag. Not used.
	 * @param array $args List of tag attributes.
	 * @param Parser $parser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function onMore( $input, $args, $parser ) {
		$parser->disableCache();
		return '';
	}

	/**
	 * Renders blog output when called via topbar and action=blog. Called by UnkownAction hook.
	 * @param string $action Value of the action parameter as determined by MediaWiki
	 * @param Article $article MediaWiki Article object of current article
	 * @return bool false to prevent other actions to bind on 'blog'.
	 */
	public function onUnknownAction( $action, $article ) {
		if ( $action != 'blog' ) return true;

		BsExtensionManager::setContext( 'MW::Blog::ShowBlog' );
		BsConfig::set( 'MW::ApplicationContext', 'Blog' );

		$oMwOut = $this->getOutput();
		$oMwOut->setPageTitle( 'Blog' ); // set page content
		$oMwOut->addHTML( $this->onBlog( '', array(), null ) );

		return false; // return false to prevent other actions to bind on 'blog'
	}

	/**
	 * Prefills new article with category. This is used when a new blog entry is created via the new entry form and the blog article base is derived from a category. Called by EditFormPreloadText hook.
	 * @param string $sText Prefill text.
	 * @param Title $oTitle Current MediaWiki title object.
	 * @return bool true to allow other hooked methods to be executed.
	 */
	public function onEditFormPreloadText( &$sText, &$oTitle ) {
		$sBlogCat =  $this->getRequest()->getVal( 'blogcat', '' );
		if ( $sBlogCat ) {
			$sText = wfMessage( 'bs-blog-preload-cat', $sBlogCat )->plain();
		}

		return true;
	}

	/**
	 * Renders the blog. Called by parser function for bs:blog tag and also from Blog::onUnknownAction.
	 * @param string $input Inner HTML of bs:blog tag. Not used.
	 * @param array $args List of tag attributes.
	 * @param Parser $parser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function onBlog( $input, $args, $parser ) {
		$oTitle = null;
		if( $parser instanceof Parser ) {
			$oTitle = $parser->getTitle();
			$parser->disableCache();
		} else {
			$oTitle = $this->getTitle();
		}
		// initialize local variables
		$sOut = '';
		$oErrorListView = new ViewTagErrorList( $this );
		BsExtensionManager::setContext( 'MW::Blog::ShowBlog' );

		// get all config options
		$iShowLimit             = BsConfig::get( 'MW::Blog::ShowLimit' );
		//$blogShowTrackback    = BsConfig::get('MW::Blog::ShowTrackback');  // see comment below
		$bShowPermalink         = BsConfig::get( 'MW::Blog::ShowPermalink' );
		$bShowInfo              = BsConfig::get( 'MW::Blog::ShowInfo' );
		$sSortBy                = BsConfig::get( 'MW::Blog::SortBy' );
		$bMoreInNewWindow       = BsConfig::get( 'MW::Blog::MoreInNewWindow' );
		$bShowAll               = BsConfig::get( 'MW::Blog::ShowAll' );
		$bMoreAtEndOfEntry      = BsConfig::get( 'MW::Blog::MoreAtEndOfEntry' );
		$bShowNewEntryField     = BsConfig::get( 'MW::Blog::ShowNewEntryField' );
		$bNewEntryFieldPosition = BsConfig::get( 'MW::Blog::NewEntryFieldPosition' );
		$sImageRenderMode       = BsConfig::get( 'MW::Blog::ImageRenderMode' );
		$iMaxEntryCharacters    = BsConfig::get( 'MW::Blog::MaxEntryCharacters' );
		$iNamespace = NS_BLOG;

		// Trackbacks are not supported the way we intend it to be. From http://www.mediawiki.org/wiki/Manual:$wgUseTrackbacks
		// When MediaWiki receives a trackback ping, a box will show up at the bottom of the article containing a link to the originating page
		//if (!$wgUseTrackbacks)
		$bShowTrackback = false;

		// get tag attributes
		$argsIShowLimit              = BsCore::sanitizeArrayEntry( $args, 'count', $iShowLimit, BsPARAMTYPE::NUMERIC|BsPARAMOPTION::DEFAULT_ON_ERROR );
		$argsSCategory               = BsCore::sanitizeArrayEntry( $args, 'cat',   false,          BsPARAMTYPE::STRING );
		$argsINamespace              = BsNamespaceHelper::getNamespaceIndex( BsCore::sanitizeArrayEntry( $args, 'ns',   $iNamespace, BsPARAMTYPE::STRING ));
		$argsBNewEntryField          = BsCore::sanitizeArrayEntry( $args, 'newentryfield',         $bShowNewEntryField,     BsPARAMTYPE::BOOL );
		$argsSNewEntryFieldPosition  = BsCore::sanitizeArrayEntry( $args, 'newentryfieldposition', $bNewEntryFieldPosition, BsPARAMTYPE::STRING );
		$argsSImageRenderMode        = BsCore::sanitizeArrayEntry( $args, 'imagerendermode',       $sImageRenderMode,       BsPARAMTYPE::STRING );
		$argsIMaxEntryCharacters     = BsCore::sanitizeArrayEntry( $args, 'maxchars',              $iMaxEntryCharacters,    BsPARAMTYPE::INT );
		$argsSSortBy                 = BsCore::sanitizeArrayEntry( $args, 'sort',            $sSortBy,          BsPARAMTYPE::STRING );
		$argsBShowInfo               = BsCore::sanitizeArrayEntry( $args, 'showinfo',        $bShowInfo,        BsPARAMTYPE::BOOL );
		$argsBMoreInNewWindow        = BsCore::sanitizeArrayEntry( $args, 'moreinnewwindow', $bMoreInNewWindow, BsPARAMTYPE::BOOL );
		$argsBShowPermalink          = BsCore::sanitizeArrayEntry( $args, 'showpermalink',   $bShowPermalink,   BsPARAMTYPE::BOOL );
		$argsModeNamespace           = BsCore::sanitizeArrayEntry( $args, 'mode',   null,   BsPARAMTYPE::STRING );

		if( $argsModeNamespace === 'ns' && is_object( $oTitle ) ) {
			$argsINamespace = $oTitle->getNamespace();
		}

		// validate tag attributes
		$validateIShowLimit = BsValidator::isValid( 'ArgCount', $argsIShowLimit, array('fullResponse' => true) );
		if ( $validateIShowLimit->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $validateIShowLimit->getI18N() ) );
		}

		if ( $argsSCategory ) {
			$validateSCategory = BsValidator::isValid( 'Category', $argsSCategory, array( 'fullResponse' => true ) );
			if ( $validateSCategory->getErrorCode() ) {
				$oErrorListView->addItem( new ViewTagError( $validateSCategory->getI18N() ) );
			}
		}

		$oValidationResult = BsValidator::isValid( 'SetItem', $argsSImageRenderMode, array( 'fullResponse' => true, 'setname' => 'imagerendermode', 'set' => array( 'full', 'thumb', 'none' ) ) );
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid( 'SetItem', $argsSSortBy, array( 'fullResponse' => true, 'setname' => 'sort', 'set' => array( 'title', 'creation' ) ) );
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		// if there are errors, abort with a message
		if ( $oErrorListView->hasEntries() ) {
			return $oErrorListView->execute();
		}

		if( BsConfig::get( 'MW::Blog::ShowTagFormWhenNotLoggedIn' ) != true ) {
			$oPermissionTest = Title::newFromText( 'PermissionTest', $argsINamespace );
			if( !$oPermissionTest->userCan( 'edit' ) ) {
				$argsBNewEntryField = false;
			}
		}

		// get array of article ids from Blog/subpages
		$oBlogTitle = Title::makeTitleSafe( $oTitle->getNamespace(), 'Blog' );

		$aSubpages = $oBlogTitle->getSubpages();
		$iLimit = 0; // for later use

		$aArticleIds = array();
		foreach( $aSubpages as $oSubpage ) {
			$aArticleIds[] = $oSubpage->getArticleID();
			$iLimit++;  // for later use
		}

		if( count( $aArticleIds ) < 1 ) {
			$aArticleIds = 0;
		}

		// get blog entries
		$aOptions = array();
		if ( !$argsSSortBy || $argsSSortBy == 'creation' ) {
			$aOptions['ORDER BY'] = 'page_id DESC';
		}
		else if ( $argsSSortBy == 'title' ) {
			$aOptions['ORDER BY'] = 'page_title ASC';
		}

		$aTables = array( 'page' );
		$sFiels = '';
		$aConditions = array();

		$dbr = wfGetDB( DB_SLAVE );
		if ( $argsSCategory ) {
			$aTables[] = 'categorylinks';
			$sFiels = 'cl_from AS entry_page_id';
			$aConditions['cl_to'] = $argsSCategory;
			$aConditions[] = 'cl_from = page_id';
		} else {
			$sFiels = 'page_id AS entry_page_id';
			if ( $argsModeNamespace === 'ns' ) {
				$aConditions['page_id'] = $aArticleIds;
			}
			$aConditions['page_namespace'] = $argsINamespace;
		}

		$res = $dbr->select(
			$aTables,
			$sFiels,
			$aConditions,
			__METHOD__,
			$aOptions
		);

		$iNumberOfEntries = $dbr->numRows( $res );
		$iLimit = $iNumberOfEntries; //All
		// Sole importance is the existence of param 'showall'
		$paramBShowAll = $this->getRequest()->getFuzzyBool( 'showall', false );
		if ( $paramBShowAll == false ) $iLimit = $argsIShowLimit;

		// abort if there are no entries
		if ( $iNumberOfEntries < 1 ) {
			$oBlogView = new ViewBlog();
			$oBlogView->setOption( 'shownewentryfield', $argsBNewEntryField );
			$oBlogView->setOption( 'newentryfieldposition', $argsSNewEntryFieldPosition );
			if ( $argsSCategory ) {
				$oBlogView->setOption( 'blogcat', $argsSCategory );
			} else {
				$oBlogView->setOption( 'namespace', BsNamespaceHelper::getNamespaceName( $argsINamespace ) );
			}
			// actually create blog output
			$sOut = $oBlogView->execute();
			$sOut .= wfMessage( 'bs-blog-no-entries' )->plain();
			return $sOut;
		}

		$oBlogView = new ViewBlog();

		// prepare views per blog item
		$iLoop = 0;
		foreach( $res as $row ) {
			// prepare data for view class
			$oTitle   = Title::newFromID( $row->entry_page_id );
			$oArticle = new Article( $oTitle, 0 );
			if ( !$oTitle->userCan( 'read' ) ) { $iNumberOfEntries--; continue; }

			$bMore = false;
			$aContent = preg_split( '#<(bs:blog:)?more */>#', $oArticle->getContent() );
			if ( sizeof( $aContent ) > 1 ) $bMore = true;
			$aContent = trim( $aContent[0] );
			// Prevent recursive rendering of blog tag
			$aContent = preg_replace( '/<(bs:)blog[^>]*?>/', '', $aContent );
			// Thumbnail images
			$sNamespaceRegEx = implode( '|', BsNamespaceHelper::getNamespaceNamesAndAliases( NS_IMAGE ) );
			switch ( $argsSImageRenderMode ) {
				case 'none':
					$aContent = preg_replace( '/(\[\[('.$sNamespaceRegEx.'):[^\|\]]*)(\|)?(.*?)(\]\])/', '', $aContent );
					break;
				case 'full':
					// do nothing
					break;
				case 'thumb':
				default:
					$aContent = preg_replace( '/(\[\[('.$sNamespaceRegEx.'):[^\|\]]*)(\|)?(.*?)(\]\])/', '$1|thumb|none$3$4|150px$5', $aContent );
					break;
			}

			if ( strlen( $aContent ) > $argsIMaxEntryCharacters ) $bMore = true;
			$aContent = BsStringHelper::shorten( 
							$aContent, 
							array( 
									'max-length' => $argsIMaxEntryCharacters, 
									'ignore-word-borders' => false, 
									'position' => 'end'
							) 
						);
			$resComment = $dbr->selectRow( 
				'revision',
				'COUNT( rev_id ) AS cnt',
				array( 'rev_page' => $oTitle->getTalkPage()->getArticleID() )
			);
			$iCount = $resComment->cnt;
			// set data for view class
			$oBlogItemView = new ViewBlogItem();

			// use magic set
			$oBlogItemView->setOption( 'showInfo', $argsBShowInfo );
			$oBlogItemView->setOption( 'showLimit', $argsIShowLimit );
			$oBlogItemView->setOption( 'showTrackback', $bShowTrackback );
			$oBlogItemView->setOption( 'showPermalink', $argsBShowPermalink );
			$oBlogItemView->setOption( 'moreInNewWindow', $argsBMoreInNewWindow );
			$oBlogItemView->setOption( 'showAll', $bShowAll );
			$oBlogItemView->setOption( 'moreAtEndOfEntry', $bMoreAtEndOfEntry );
			$oBlogItemView->setOption( 'more', $bMore );

			//TODO: magic_call?
			
			if( $argsModeNamespace === 'ns' ) {
				$sTitle = substr( $oTitle->getText(), 5 );
			} else {
				$sTitle = $oTitle->getText();
			}
			$oBlogItemView->setTitle( $sTitle );
			$oBlogItemView->setRevId( $oArticle->getRevIdFetched() );
			$oBlogItemView->setURL( $oTitle->getFullURL() );
			$oBlogItemView->setTalkURL( $oTitle->getTalkPage()->getFullURL() );
			$oBlogItemView->setTalkCount( $iCount );
			$oBlogItemView->setTrackbackUrl( $oTitle->getFullURL() );

			if ( $bShowInfo ) {
				$oFirstRevision = $oTitle->getFirstRevision();
				$sTimestamp = $oFirstRevision->getTimestamp();
				$sLocalDateTimeString = BsFormatConverter::timestampToAgeString( wfTimestamp( TS_UNIX,$sTimestamp ) );
				$oBlogItemView->setEntryDate( $sLocalDateTimeString );
				$iUserId = $oFirstRevision->getUser();

				if ( $iUserId != 0 ) {
					$oAuthorUser = User::newFromId( $iUserId );
					$oBlogItemView->setAuthorPage( $oAuthorUser->getUserPage()->getFullText() );
					$oBlogItemView->setAuthorName( $this->mCore->getUserDisplayName( $oAuthorUser ) );
				} else {
					$oBlogItemView->setAuthorName( $oFirstRevision->getUserText() );
				}
			}

			$oBlogItemView->setContent( $aContent );
			$oBlogView->addItem( $oBlogItemView );
			$iLoop++;
			if( $iLoop >= $iLimit ) break;
		}
		$dbr->freeResult( $res );

		// prepare complete blog output
		if ( $bShowAll && !$paramBShowAll && ( $iNumberOfEntries > $argsIShowLimit ) ) {
			$oBlogView->setOption( 'showall', true );
		}
		$oBlogView->setOption( 'shownewentryfield', $argsBNewEntryField );
		$oBlogView->setOption( 'newentryfieldposition', $argsSNewEntryFieldPosition );
		$oBlogView->setOption( 'namespace', BsNamespaceHelper::getNamespaceName( $argsINamespace, false ) );
		$oBlogView->setOption( 'blogcat', $argsSCategory );
		if ( $argsModeNamespace === 'ns' ) {
			$oBlogView->setOption( 'parentpage', 'Blog/' );
		}

		// actually create blog output
		$sOut = $oBlogView->execute();
		return $sOut;
	}

	public function onBSRSSFeederGetRegisteredFeeds( $aFeeds ) {
		RSSFeeder::registerFeed('blog',
			wfMessage( 'bs-blog-Blog' )->plain(),
			wfMessage( 'bs-blog-extension-description' )->plain(),
			$this,
			'buildRssNsBlog',
			null,
			'buildLinksNs'
		);
		return true;
	}

	public function buildRssNsBlog() {
		global $wgSitename, $wgContLang;

		$oRequest = $this->getRequest();
		$sTitle = $oRequest->getVal( 'p', '' );
		$iNSid = $oRequest->getInt( 'ns', 0 );
		$aNamespaces = $wgContLang->getNamespaces();

		if( $iNSid != 0 ) {
			$sPageName = $aNamespaces[$iNSid].':'.$sTitle;
		} else {
			$sPageName = $sTitle;
		}

		$dbr = wfGetDB( DB_SLAVE );
		$res = $dbr->select(
			array( 'page', 'recentchanges' ),
			'*',
			array( 
				'page_title'     => $sTitle,
				'page_namespace' => $iNSid,
				'rc_timestamp > '. $dbr->timestamp( time() - intval( 7 * 86400 ) )
			),
			__METHOD__,
			array( 'ORDER BY' => 'rc_timestamp DESC' ),
			array( 
				'page'=> array( 'LEFT JOIN', 'rc_cur_id = page_id' ) 
			)
		);

		$oChannel = RSSCreator::createChannel(RSSCreator::xmlEncode( $wgSitename . ' - ' . $sPageName), 'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'], wfMsg( 'bs-rssstandards-description_page' ) );

		$oTitle = Title::makeTitle( $iNSid , 'Blog' );
		$aSubpages = $oTitle->getSubpages();

		foreach( $aSubpages as $oSubpage ) {
//			$oPageCP = new BsPageContentProvider(); 
			if( $oSubpage instanceof Title ) {}
			$entry = RSSItemCreator::createItem(
				$oSubpage->getText(),
				$oSubpage->getFullURL(),
				BsPageContentProvider::getInstance()->getContentFromTitle( $oSubpage )
//				$oPageCP->getHTMLContentFor( $oSubpage )
			);
			$entry->setPubDate( wfTimestamp( TS_UNIX, $oSubpage->getTouched() ) );
			$oChannel->addItem($entry);
		}
		return $oChannel->buildOutput();
	}

	// TODO: make RSSStandards methods more generic
	public function buildLinksNs() {
		$oUser = $this->getUser();
		$set = new ViewFormElementFieldset();
		$set->setLabel( wfMessage( 'bs-blog-Blog' )->plain() );

		$select = new ViewFormElementSelectbox();
		$select->setId( 'selFeedNsBlog' );
		$select->setName( 'selFeedNsBlog' );
		$select->setLabel( wfMessage( 'bs-rssfeeder-field_title_ns' )->plain() );

		$aNamespacesTemp = BsNamespaceHelper::getNamespacesForSelectOptions( array( NS_SPECIAL, NS_MEDIA, NS_BLOG, NS_BLOG_TALK, NS_FILE ) );
		$aNamespaces = array();
		foreach( $aNamespacesTemp as $index => $name ) {
			if ( $index % 2 == 0 ) {
				$aNamespaces[$index] = $name;
			}
		}

		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName   = $oUser->getName();
		$sUserToken  = $oUser->getToken();

		foreach( $aNamespaces as $key => $name ) {
			$select->addData(
				array(
					'value' => $oSpecialRSS->getLinkUrl(
						array(
							'Page' => 'blog',
							'ns'   => $key,
							'u'    => $sUserName,
							'h'    => $sUserToken
						)
					),
					'label' => $name
				)
			);
		}

		$btn = new ViewFormElementButton();
		$btn->setId( 'btnFeedNsBlog' );
		$btn->setName( 'btnFeedNsBlog' );
		$btn->setType( 'button' );
		$btn->setLabel( wfMessage( 'bs-rssfeeder-submit_title' )->plain() );

		$set->addItem( $select );
		$set->addItem( $btn );

		return $set;
	}

}
