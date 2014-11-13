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
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v2.23.0
 */

/*
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
		$this->mExtensionType = EXTTYPE::VARIABLE;
		$this->mInfo = array(
			EXTINFO::NAME        => 'Blog',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-blog-desc' )->escaped(),
			EXTINFO::AUTHOR      => 'Markus Glaser, Sebastian Ulbricht',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
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
		$this->setHook( 'SkinTemplateNavigation::Universal', 'onSkinTemplateNavigationUniversal' );
		$this->setHook( 'EditFormPreloadText' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		$this->setHook( 'BSNamespaceManagerBeforeSetUsernamespaces', 'onBSNamespaceManagerBeforeSetUsernamespaces');
		$this->setHook( 'BSRSSFeederGetRegisteredFeeds' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSTopMenuBarCustomizerRegisterNavigationSites' );
		$this->setHook( 'PageContentSaveComplete' );

		// Trackback is not fully functional in MW and thus disabled.
		BsConfig::registerVar( 'MW::Blog::ShowTrackback', false, BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_BOOL );
		// Show permalink link at end of a blog entry
		BsConfig::registerVar( 'MW::Blog::ShowPermalink', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-showpermalink', 'toggle' );
		// Show info line below blog entry heading
		BsConfig::registerVar( 'MW::Blog::ShowInfo', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-showinfo', 'toggle');
		// Open more link in new window
		BsConfig::registerVar( 'MW::Blog::MoreInNewWindow', false, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-moreinnewwindow', 'toggle' );
		// Should a link to complete list of blog entries be rendered?
		BsConfig::registerVar( 'MW::Blog::ShowAll', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-showall', 'toggle' );
		// Place more link at end of blog entry instead of next line
		BsConfig::registerVar( 'MW::Blog::MoreAtEndOfEntry', true, BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_BOOL, 'toggle' );
		// Possible values are "creation" and "title"
		BsConfig::registerVar( 'MW::Blog::SortBy', 'creation', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-blog-pref-sortby', 'select' );
		// Number of blog entries that shall be displayed initially
		BsConfig::registerVar( 'MW::Blog::ShowLimit', 10, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-blog-pref-showlimit', 'int' );
		// Show form that allows to create a new blog entry
		BsConfig::registerVar( 'MW::Blog::ShowNewEntryField', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-blog-pref-shownewentryfield', 'toggle' );
		// Position of new entry field. Possible values are "top" and "bottom"
		BsConfig::registerVar( 'MW::Blog::NewEntryFieldPosition', 'top', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-blog-pref-newentryfieldposition', 'select' );
		// Maximum number of characters befor an entry is automatically cut
		BsConfig::registerVar( 'MW::Blog::MaxEntryCharacters', 1000, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_INT, 'bs-blog-pref-maxentrycharacters', 'int' );
		// Defines how images should be rendered. Possible values: full|thumb|none
		BsConfig::registerVar( 'MW::Blog::ImageRenderMode', 'thumb', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-blog-pref-imagerendermode', 'select' );
		// Defines float direction of images when ImageRenderMode is thumb. Possible values: left|right|none
		BsConfig::registerVar( 'MW::Blog::ThumbFloatDirection', 'right', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-blog-pref-imagefloatdirection', 'select' );

		BsConfig::registerVar( 'MW::Blog::ShowTagFormWhenNotLoggedIn', false, BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_BOOL, 'toggle' );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Adds entry to navigation sites
	 * @global string $wgScriptPath
	 * @param array $aNavigationSites
	 * @return boolean - always true
	 */
	public function onBSTopMenuBarCustomizerRegisterNavigationSites( &$aNavigationSites ) {
		global $wgScriptPath;

		// Reset all other active markers if Blog is active
		if ( BsExtensionManager::isContextActive( 'MW::Blog::ShowBlog' ) ) {
			for ($i = 0; $i < sizeof($aNavigationSites); $i++ ) {
				$aNavigationSites[$i]["active"] = false;
			}
		}

		$aNavigationSites[] = array(
			'id' => 'nt-blog',
			'href' => wfAppendQuery( $wgScriptPath.'/index.php', array(
				'action' => 'blog'
			)),
			'active' => BsExtensionManager::isContextActive( 'MW::Blog::ShowBlog' ),
			'text' => wfMessage('bs-blog-blog')->plain(),
		);
		return true;
	}

	/**
	 * Hook-Handler for MediaWiki 'BeforePageDisplay' hook. Sets context if needed.
	 * @param OutputPage $oOutputPage
	 * @param Skin $oSkin
	 * @return bool
	 */
	public function onBeforePageDisplay( &$oOutputPage, &$oSkin ) {
		$oOutputPage->addModuleStyles( 'ext.bluespice.blog' );

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
			case 'ImageRenderMode':
				$aPrefs = array(
					'options' => array(
						'thumb' => 'thumb',
						'full' => 'full',
						'none' => 'none'
					)
				);
				break;
			case 'ThumbFloatDirection':
				$aPrefs = array(
					'options' => array(
						'left' => 'left',
						'right' => 'right',
						'none' => 'none'
					)
				);
				break;
			case 'NewEntryFieldPosition':
				$aPrefs = array(
					'options' => array(
						'top' => 'top',
						'bottom' => 'bottom'
					)
				);
				break;
			case 'SortBy':
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

	/**
	 * Invalidates blog caches
	 * @param Article $article
	 * @param User $user
	 * @param Content $content
	 * @param type $summary
	 * @param type $isMinor
	 * @param type $isWatch
	 * @param type $section
	 * @param type $flags
	 * @param Revision $revision
	 * @param Status $status
	 * @param type $baseRevId
	 * @return boolean
	 */
	public function onPageContentSaveComplete( $article, $user, $content, $summary,
			$isMinor, $isWatch, $section, $flags, $revision, $status, $baseRevId ) {
		if ( $article->getTitle()->getNamespace() !== NS_BLOG ) return true;

		$sTagsKey = BsCacheHelper::getCacheKey( 'BlueSpice', 'Blog', 'Tags' );
		$aTagsData = BsCacheHelper::get( $sTagsKey );

		// Invalidate all blog tag caches
		BsCacheHelper::invalidateCache( $aTagsData );
		// Invalidate blog tag cache
		BsCacheHelper::invalidateCache( $sTagsKey );

		return true;
	}

	public function onBSNamespaceManagerBeforeSetUsernamespaces( $classInstance, &$bsSystemNamespaces ) {
		$bsSystemNamespaces[102] = 'NS_BLOG';
		$bsSystemNamespaces[103] = 'NS_BLOG_TALK';
		return true;
	}

	/**
	 * Removes all content actions from action tabs and highlights blog in
	 * application context. Called by SkinTemplateNavigationUniversal hook.
	 * @param SkinTemplate $sktemplate
	 * @param type $links
	 * @return boolean Always true to keep hook running
	 */
	public function onSkinTemplateNavigationUniversal( &$sktemplate, &$links ) {
		$sAction = $this->getRequest()->getVal( 'action', '' );
		if ( ( $sAction != 'blog' ) ) {
			return true;
		} else {
			$links = array();
		}
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
		if ( $type != 'tags' ) return true;

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

		$oOut = $this->getOutput();
		$oOut->setPageTitle( 'Blog' ); // set page content
		$oOut->addHTML( $this->onBlog( '', array(), null ) );

		return false; // return false to prevent other actions to bind on 'blog'
	}

	/**
	 * Prefills new article with category. This is used when a new blog entry is created via the new entry form and the blog article base is derived from a category. Called by EditFormPreloadText hook.
	 * @param string $sText Prefill text.
	 * @param Title $oTitle Current MediaWiki title object.
	 * @return bool true to allow other hooked methods to be executed.
	 */
	public function onEditFormPreloadText( &$sText, &$oTitle ) {
		$sBlogCat = $this->getRequest()->getVal( 'blogcat', '' );
		if ( $sBlogCat ) {
			$sText = "\n[[".BsNamespaceHelper::getNamespaceName( NS_CATEGORY ).':'.$sBlogCat.']]';
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
		if ( $parser instanceof Parser ) {
			$oTitle = $parser->getTitle();
			$parser->disableCache();
		} else {
			$oTitle = $this->getTitle();
		}

		$sKey = BsCacheHelper::getCacheKey( 'BlueSpice', 'Blog', $oTitle->getArticleID() );
		$aData = BsCacheHelper::get( $sKey );

		if ( $aData !== false ) {
			return $aData;
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
		$sImageFloatDirection   = BsConfig::get( 'MW::Blog::ThumbFloatDirection' );
		$iMaxEntryCharacters    = BsConfig::get( 'MW::Blog::MaxEntryCharacters' );

		// Trackbacks are not supported the way we intend it to be. From http://www.mediawiki.org/wiki/Manual:$wgUseTrackbacks
		// When MediaWiki receives a trackback ping, a box will show up at the bottom of the article containing a link to the originating page
		//if (!$wgUseTrackbacks)
		$bShowTrackback = false;

		// get tag attributes
		$argsIShowLimit              = BsCore::sanitizeArrayEntry( $args, 'count', $iShowLimit, BsPARAMTYPE::NUMERIC|BsPARAMOPTION::DEFAULT_ON_ERROR );
		$argsSCategory               = BsCore::sanitizeArrayEntry( $args, 'cat',   false,          BsPARAMTYPE::STRING );
		$argsINamespace              = BsNamespaceHelper::getNamespaceIndex( BsCore::sanitizeArrayEntry( $args, 'ns',   NS_BLOG, BsPARAMTYPE::STRING ));
		$argsBNewEntryField          = BsCore::sanitizeArrayEntry( $args, 'newentryfield',         $bShowNewEntryField,     BsPARAMTYPE::BOOL );
		$argsSNewEntryFieldPosition  = BsCore::sanitizeArrayEntry( $args, 'newentryfieldposition', $bNewEntryFieldPosition, BsPARAMTYPE::STRING );
		$argsSImageRenderMode        = BsCore::sanitizeArrayEntry( $args, 'imagerendermode',       $sImageRenderMode,       BsPARAMTYPE::STRING );
		$argsSImageFloatDirection    = BsCore::sanitizeArrayEntry( $args, 'imagefloatdirection',   $sImageFloatDirection,   BsPARAMTYPE::STRING );
		$argsIMaxEntryCharacters     = BsCore::sanitizeArrayEntry( $args, 'maxchars',              $iMaxEntryCharacters,    BsPARAMTYPE::INT );
		$argsSSortBy                 = BsCore::sanitizeArrayEntry( $args, 'sort',            $sSortBy,          BsPARAMTYPE::STRING );
		$argsBShowInfo               = BsCore::sanitizeArrayEntry( $args, 'showinfo',        $bShowInfo,        BsPARAMTYPE::BOOL );
		$argsBMoreInNewWindow        = BsCore::sanitizeArrayEntry( $args, 'moreinnewwindow', $bMoreInNewWindow, BsPARAMTYPE::BOOL );
		$argsBShowPermalink          = BsCore::sanitizeArrayEntry( $args, 'showpermalink',   $bShowPermalink,   BsPARAMTYPE::BOOL );
		$argsModeNamespace           = BsCore::sanitizeArrayEntry( $args, 'mode',   null,   BsPARAMTYPE::STRING );

		if ( $argsModeNamespace === 'ns' && is_object( $oTitle ) ) {
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

		$oValidationResult = BsValidator::isValid( 'SetItem', $argsSImageFloatDirection, array( 'fullResponse' => true, 'setname' => 'imagefloatdirection', 'set' => array( 'left', 'right', 'none' ) ) );
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

		if ( BsConfig::get( 'MW::Blog::ShowTagFormWhenNotLoggedIn' ) != true ) {
			$oPermissionTest = Title::newFromText( 'PermissionTest', $argsINamespace );
			if ( !$oPermissionTest->userCan( 'edit' ) ) {
				$argsBNewEntryField = false;
			}
		}

		// get array of article ids from Blog/subpages
		$oBlogTitle = Title::makeTitleSafe( $oTitle->getNamespace(), 'Blog' );

		$aSubpages = $oBlogTitle->getSubpages();
		$iLimit = 0; // for later use

		$aArticleIds = array();
		foreach ( $aSubpages as $oSubpage ) {
			$aArticleIds[] = $oSubpage->getArticleID();
			$iLimit++;  // for later use
		}

		if ( count( $aArticleIds ) < 1 ) {
			$aArticleIds = 0;
		}

		// get blog entries
		$aOptions = array();
		if ( !$argsSSortBy || $argsSSortBy == 'creation' ) {
			$aOptions['ORDER BY'] = 'page_id DESC';
		} elseif ( $argsSSortBy == 'title' ) {
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
			$oBlogView->setOption( 'namespace', BsNamespaceHelper::getNamespaceName( $argsINamespace ) );
			if ( $argsSCategory ) {
				$oBlogView->setOption( 'blogcat', $argsSCategory );
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
			$oTitle = Title::newFromID( $row->entry_page_id );
			if ( !$oTitle->userCan( 'read' ) ) { $iNumberOfEntries--; continue; }

			$bMore = false;
			$aContent = preg_split( '#<(bs:blog:)?more */>#', BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle ) );
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
					$aContent = preg_replace( '/(\[\[('.$sNamespaceRegEx.'):[^\|\]]*)(\|)?(.*?)(\]\])/', "$1|thumb|$argsSImageFloatDirection$3$4|150px$5", $aContent );
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

			if ( $argsModeNamespace === 'ns' ) {
				$sTitle = substr( $oTitle->getText(), 5 );
			} else {
				$sTitle = $oTitle->getText();
			}

			$aTalkParams = array();
			if ( !$oTitle->getTalkPage()->exists() ) {
				$aTalkParams = array( 'action' => 'edit' );
			}

			$oRevision = Revision::newFromTitle( $oTitle );
			$oBlogItemView->setTitle( $sTitle );
			$oBlogItemView->setRevId( $oRevision->getId() );
			$oBlogItemView->setURL( $oTitle->getLocalURL() );
			$oBlogItemView->setTalkURL( $oTitle->getTalkPage()->getLocalURL( $aTalkParams ) );
			$oBlogItemView->setTalkCount( $iCount );
			$oBlogItemView->setTrackbackUrl( $oTitle->getLocalURL() );

			if ( $bShowInfo ) {
				$oFirstRevision = $oTitle->getFirstRevision();
				$sTimestamp = $oFirstRevision->getTimestamp();
				$sLocalDateTimeString = BsFormatConverter::timestampToAgeString( wfTimestamp( TS_UNIX,$sTimestamp ) );
				$oBlogItemView->setEntryDate( $sLocalDateTimeString );
				$iUserId = $oFirstRevision->getUser();

				if ( $iUserId != 0 ) {
					$oAuthorUser = User::newFromId( $iUserId );
					$oBlogItemView->setAuthorPage( $oAuthorUser->getUserPage()->getPrefixedText() );
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

		$aKey = array( $sKey );
		$sTagsKey = BsCacheHelper::getCacheKey( 'BlueSpice', 'Blog', 'Tags' );
		$aTagsData = BsCacheHelper::get( $sTagsKey );

		if ( $aTagsData !== false ) {
			if ( !in_array( $sKey, $aTagsData ) ) {
				$aTagsData = array_merge( $aTagsData, $aKey );
			}
		} else {
			$aTagsData = $aKey;
		}

		BsCacheHelper::set( $sTagsKey, $aTagsData, 86400 ); // one day

		// actually create blog output
		$sOut = $oBlogView->execute();
		BsCacheHelper::set( $sKey, $sOut, 86400 ); // one day

		return $sOut;
	}

	public function onBSRSSFeederGetRegisteredFeeds( $aFeeds ) {
		RSSFeeder::registerFeed('blog',
			wfMessage( 'bs-blog-blog' )->plain(),
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

		/*$dbr = wfGetDB( DB_SLAVE );
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
		);*/

		$oChannel = RSSCreator::createChannel(
			RSSCreator::xmlEncode( $wgSitename . ' - ' . $sPageName ),
			'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'], wfMessage( 'bs-rssstandards-description_page' )->plain()
		);

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
		$set->setLabel( wfMessage( 'bs-blog-blog' )->plain() );

		$select = new ViewFormElementSelectbox();
		$select->setId( 'selFeedNsBlog' );
		$select->setName( 'selFeedNsBlog' );
		$select->setLabel( wfMessage( 'bs-ns' )->plain() );

		$aNamespacesTemp = BsNamespaceHelper::getNamespacesForSelectOptions( array( NS_SPECIAL, NS_MEDIA, NS_BLOG, NS_BLOG_TALK, NS_FILE ) );
		$aNamespaces = array();
		foreach( $aNamespacesTemp as $index => $name ) {
			if ( $index % 2 == 0 ) {
				$aNamespaces[$index] = $name;
			}
		}

		$oSpecialRSS = SpecialPage::getTitleFor( 'RSSFeeder' );
		$sUserName = $oUser->getName();
		$sUserToken = $oUser->getToken();

		foreach( $aNamespaces as $key => $name ) {
			$select->addData(
				array(
					'value' => $oSpecialRSS->getLinkUrl(
						array(
							'Page' => 'blog',
							'ns' => $key,
							'u' => $sUserName,
							'h' => $sUserToken
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
		$btn->setLabel( wfMessage( 'bs-rssfeeder-submit' )->plain() );

		$set->addItem( $select );
		$set->addItem( $btn );

		return $set;
	}

}
