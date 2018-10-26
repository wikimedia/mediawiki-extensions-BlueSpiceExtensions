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
 * This file is part of BlueSpice MediaWiki
 * For further information visit http://www.bluespice.com
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Sebastian Ulbricht
 * @author     Leonid Verhovskij
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage Blog
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/*
 * Base class for page template extension
 * @package BlueSpice_Extensions
 * @subpackage Blog
 */
class Blog extends BsExtensionMW {

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
		$this->setHook( 'BSUsageTrackerRegisterCollectors' );

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

		BsConfig::registerVar(
			'MW::Blog::Preload',
			'',
			BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING,
			'bs-blog-pref-preload'
		);

		$this->mCore->registerPermission( 'blog-viewspecialpage', array('user'), array( 'type' => 'global' ) );

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * extension.json callback
	 */
	public static function onRegistration() {
		global $wgExtraNamespaces, $bsgSystemNamespaces;
		if( !defined( 'NS_BLOG' ) ) {
			define( 'NS_BLOG', 1502 );
			$wgExtraNamespaces[NS_BLOG] = 'Blog';
			$bsgSystemNamespaces[1502] = 'NS_BLOG';
		}

		if( !defined( 'NS_BLOG_TALK' ) ) {
			define( 'NS_BLOG_TALK', 1503 );
			$wgExtraNamespaces[NS_BLOG_TALK] = 'Blog_talk';
			$bsgSystemNamespaces[1503] = 'NS_BLOG_TALK';
		}
	}

	/**
	 * Adds entry to navigation sites
	 * @global string $wgScriptPath
	 * @param array $aNavigationSites
	 * @return boolean - always true
	 */
	public function onBSTopMenuBarCustomizerRegisterNavigationSites( &$aNavigationSites ) {
		$aNavigationSites[] = array(
			'id' => 'nt-blog',
			'href' => SpecialPage::getTitleFor( 'Blog' )->getLinkURL(),
			'active' => false, //Flag is not properly evaluated anyways. 'TopMenuBarCustomizer' does heavy caching.
			'text' => wfMessage( 'bs-blog-blog' )->plain(),
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
		$oOutputPage->addModuleStyles( 'ext.bluespice.blog.styles' );

		if( $oOutputPage->getTitle()->isSpecial( 'RSSFeeder' ) ) {
			$oOutputPage->addModules( 'ext.bluespice.blog.rssfeeder.integration' );
		}

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
		$parser->setHook( 'blog', array( $this, 'onBlog' ) );
		$parser->setHook( 'more', array( $this, 'onMore' ) );
		$parser->setHook( 'bs:blog', array( $this, 'onBlog' ) );
		$parser->setHook( 'bs:blog:more', array( $this, 'onMore' ) );
		// timestamp for custom sorting
		$parser->setHook( 'blog:time', array( $this, 'onBlogTime' ) );
		$parser->setHook( 'bs:blog:time', array( $this, 'onBlogTime' ) );
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

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:blog';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'bs:blog';
		$oDescriptor->desc = wfMessage( 'bs-blog-tag-blog-desc' )->text();
		$oDescriptor->code = '<bs:blog />';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = array(
			array(
				'code' => '<bs:blog count="5" cat="Wiki" newentryfieldposition="bottom" />'
			)
		);
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/Blog';
		$oResponse->result[] = $oDescriptor;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:blog:more';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'bs:blog:more';
		$oDescriptor->desc = wfMessage( 'bs-blog-tag-blogmore-desc' )->text();
		$oDescriptor->code = '<bs:blog:more />';
		$oDescriptor->previewable = false;
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/Blog';
		$oResponse->result[] = $oDescriptor;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:blog:time';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'bs:blog:time';
		$oDescriptor->desc = wfMessage( 'bs-blog-tag-blogtime-desc' )->text();
		$oDescriptor->code = '<bs:blog:time time="YYYYMMDDHHmm" />';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = array(
			array(
				'code' => '<bs:blog:time time="201601010000" />'
			)
		);
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/Blog';
		$oResponse->result[] = $oDescriptor;

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
	 * Called by parser function for bs:blog:time tag
	 * @param String $input Inner HTML of bs:blog:time tag. Not used.
	 * @param Array $args List of tag attributes.
	 * @param Parser $parser MediaWiki parser object
	 * @return String - empty | error
	 */
	public function onBlogTime( $input, $args, $parser ) {
		$oDate = null;
		if( !isset($args['time']) ) {
			//Deprecated: <bs:blog:time timestamp />
			//Use: <bs:blog:time time=timestamp />
			$aKeys = array_keys($args);
			foreach( $aKeys as $sKey ) {
				if( !is_numeric($sKey) || strlen( $sKey ) !== 12 ) {
					continue;
				}
				if( !$oDate = DateTime::createFromFormat('YmdHi', $sKey) ) {
					continue;
				} else {
					wfDeprecated(__METHOD__, '2.22.2');
					break;
				}
			}
		} else {
			$oDate = DateTime::createFromFormat( 'YmdHi', $args['time'] );
		}
		if( empty($oDate) ) {
			$oErrorListView = new ViewTagErrorList( $this );
			$oErrorListView->addItem( new ViewTagError(
				wfMessage('bs-blog-tag-blogtime-err')->plain() )
			);
			return $oErrorListView->execute();
		}

		$parser->getOutput()->setProperty( 'blogtime', $oDate->format('YmdHis') );
		return '';
	}

	/**
	 * Renders blog output when called via topbar and action=blog. Called by UnkownAction hook.
	 * @deprecated since 2.27.0, use Special:Blog instead.
	 * @param string $action Value of the action parameter as determined by MediaWiki
	 * @param Article $article MediaWiki Article object of current article
	 * @return bool false to prevent other actions to bind on 'blog'.
	 */
	public function onUnknownAction( $action, $article ) {
		if ( $action != 'blog' ) return true;
		wfDeprecated( __METHOD__, '2.27.0' );

		// redirect to Special:Blog
		RequestContext::getMain()->getOutput()->redirect( SpecialPage::getTitleFor( 'Blog' )->getLinkURL() );

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

		if ( $parser instanceof Parser ) {
			$parser->getOutput()->setProperty( 'bs-tag-blog', 1 );
		}

		// initialize local variables
		$oErrorListView = new ViewTagErrorList( $this );

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
		$sPreload               = BsConfig::get( 'MW::Blog::Preload' );

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
		$argsPreload = BsCore::sanitizeArrayEntry(
			$args,
			'preload',
			$sPreload,
			BsPARAMTYPE::STRING
		);

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
		$oBlogTitle = Title::makeTitleSafe( $argsINamespace, 'Blog' );

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

		$aTables = array( 'page' );
		$aFields = array( 'entry_page_id' => 'page_id' );
		$aConditions = array();
		$aOptions = array();
		$aJoins = array();

		$dbr = wfGetDB( DB_SLAVE );

		if ( $argsSCategory ) {
			$aTables[] = 'categorylinks';
			$aConditions['cl_to'] = $argsSCategory;
			$aConditions[] = 'cl_from = page_id';
		} else {
			if ( $argsModeNamespace === 'ns' ) {
				$aConditions['page_id'] = $aArticleIds;
			}
			$aConditions['page_namespace'] = $argsINamespace;
		}

		// get blog entries
		if( $argsSSortBy == 'title' ) {
			$aOptions['ORDER BY'] = 'page_title ASC';
		} else {
			//Creation: Also fetch possible custom timestamps from page_props table
			$aOptions['ORDER BY'] = 'entry_timestamp DESC';
			$aOptions['GROUP BY'] = 'page_id';

			global $wgDBtype;
			switch( $wgDBtype ) {
				case 'oracle':
					$aFields['entry_timestamp'] = "NVL( pp_value, rev_timestamp )";
					$aConditions[] = "NVL( pp_value, rev_timestamp ) < ".wfTimestampNow();
					break;
				case 'mssql':
					$aFields['entry_timestamp'] = "ISNULL( pp_value, rev_timestamp )";
					$aConditions[] = "ISNULL( pp_value, rev_timestamp ) < ".wfTimestampNow();
					break;
				case 'postgres':
					$aFields['entry_timestamp'] = "NULLIF( pp_value, rev_timestamp )";
					$aConditions[] = "NULLIF( pp_value, rev_timestamp ) < ".wfTimestampNow();
					break;
				default: //MySQL, SQLite
					//use pp_value if exists
					$aFields['entry_timestamp'] = "IFNULL( pp_value, rev_timestamp )";
					//also do not list future entries
					$aConditions[] = "IFNULL( pp_value, rev_timestamp ) < ".wfTimestampNow();
			}
			$aTables[] = 'revision';
			$aTables[] = 'page_props';
			$aConditions[] = 'rev_page = page_id';
			$aJoins['page_props'] = array( 'LEFT JOIN', "pp_page = rev_page AND pp_propname = 'blogtime'" );
		}

		$res = $dbr->select(
			$aTables,
			$aFields,
			$aConditions,
			__METHOD__,
			$aOptions,
			$aJoins
		);

		$iNumberOfEntries = $dbr->numRows( $res );
		$iLimit = $iNumberOfEntries; //All
		// Sole importance is the existence of param 'showall'
		$paramBShowAll = $this->getRequest()->getFuzzyBool( 'showall', false );
		if ( $paramBShowAll == false ) $iLimit = $argsIShowLimit;

		$oBlogView = new ViewBlog();
		$oBlogView->setOption( 'preload', $argsPreload );

		// abort if there are no entries
		if ( $iNumberOfEntries < 1 ) {
			$oBlogView->setOption( 'preload', $argsPreload );
			$oBlogView->setOption( 'shownewentryfield', $argsBNewEntryField );
			$oBlogView->setOption( 'newentryfieldposition', $argsSNewEntryFieldPosition );
			$oBlogView->setOption( 'namespace', BsNamespaceHelper::getNamespaceName( $argsINamespace ) );
			if ( $argsSCategory ) {
				$oBlogView->setOption( 'blogcat', $argsSCategory );
			}
			if ( $argsModeNamespace === 'ns' ) {
				$oBlogView->setOption( 'parentpage', 'Blog/' );
			}
			// actually create blog output
			$sOut = $oBlogView->execute();
			$sOut .= wfMessage( 'bs-blog-no-entries' )->plain();
			return $sOut;
		}

		// prepare views per blog item
		$iLoop = 0;
		foreach( $res as $row ) {
			// prepare data for view class
			$oEntryTitle = Title::newFromID( $row->entry_page_id );
			if ( !$oEntryTitle->userCan( 'read' ) ) { $iNumberOfEntries--; continue; }

			$bMore = false;
			$aContent = preg_split( '#<(bs:blog:)?more */>#', BsPageContentProvider::getInstance()->getContentFromTitle( $oEntryTitle ) );
			if ( sizeof( $aContent ) > 1 ) $bMore = true;
			$aContent = trim( $aContent[0] );
			// Prevent recursive rendering of blog tag
			$aContent = preg_replace( '/<(bs:)blog[^>]*?>/', '', $aContent );
			// Thumbnail images
			$sNamespaceRegEx = implode( '|', BsNamespaceHelper::getNamespaceNamesAndAliases( NS_FILE ) );

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
				array( 'rev_page' => $oEntryTitle->getTalkPage()->getArticleID() )
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
				$sTitle = substr( $oEntryTitle->getText(), 5 );
			} else {
				$sTitle = $oEntryTitle->getText();
			}

			$aTalkParams = array();
			if ( !$oEntryTitle->getTalkPage()->exists() ) {
				$aTalkParams = array( 'action' => 'edit' );
			}

			$oRevision = Revision::newFromTitle( $oEntryTitle );
			$oBlogItemView->setTitle( $sTitle );
			$oBlogItemView->setRevId( $oRevision->getId() );
			$oBlogItemView->setURL( $oEntryTitle->getLocalURL() );
			$oBlogItemView->setTalkURL( $oEntryTitle->getTalkPage()->getLocalURL( $aTalkParams ) );
			$oBlogItemView->setTalkCount( $iCount );
			$oBlogItemView->setTrackbackUrl( $oEntryTitle->getLocalURL() );

			if ( $bShowInfo ) {
				$oFirstRevision = $oEntryTitle->getFirstRevision();
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

		// actually create blog output
		$sOut = $oBlogView->execute();

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
		$iNS = $oRequest->getInt( 'ns', 0 );
		$aNamespaces = $wgContLang->getNamespaces();

		if( $iNS != 0 ) {
			$sPageName = $aNamespaces[$iNS] . ':' . $sTitle;
		} else {
			$sPageName = $sTitle;
		}

		$oChannel = RSSCreator::createChannel(
			RSSCreator::xmlEncode( $wgSitename . ' - ' . $sPageName ),
			'http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF'], wfMessage( 'bs-blog-rss-desc-blog' )->plain()
		);

		$aSubpages = $this->getBlogPages( $iNS );

		foreach( $aSubpages as $oSubpage ) {
			if( $oSubpage instanceof Title ) {}
			$entry = RSSItemCreator::createItem(
				$oSubpage->getText(),
				$oSubpage->getFullURL(),
				BsPageContentProvider::getInstance()->getContentFromTitle( $oSubpage )
			);
			$entry->setPubDate( wfTimestamp( TS_UNIX, $oSubpage->getTouched() ) );
			$oChannel->addItem($entry);
		}
		return $oChannel->buildOutput();
	}

	protected function getBlogPages( $iNS ) {
		$aSubpages = array();
		if( $iNS == NS_BLOG ) {
			$dbr = wfGetDB( DB_SLAVE );
			$res = $dbr->select(
				array( 'page' ),
				array( 'page_title', 'page_namespace'),
				array(
					'page_namespace' => $iNS
				),
				__METHOD__
			);
			foreach( $res as $row ) {
				$oTmpTitle = Title::newFromRow( $row );
				$aSubpages[] = $oTmpTitle;
			}
		} else {
			$oTitle = Title::newFromText( 'Blog', $iNS );
			if( $oTitle && $oTitle instanceof Title ) {
				$aSubpages = $oTitle->getSubpages();
				BSDebug::logVar($aSubpages);
			}

		}

		return $aSubpages;
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

		$aNamespacesTemp = BsNamespaceHelper::getNamespacesForSelectOptions( array( NS_SPECIAL, NS_MEDIA, NS_FILE ) );
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

	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:blog'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-blog'
			)
		);
		$aCollectorsConfig['bs:blog:new'] = array(
			'class' => 'Tag',
			'config' => array(
				'identifier' => 'bs:blog:new'
			)
		);
		$aCollectorsConfig['bs:blog:more'] = array(
			'class' => 'Tag',
			'config' => array(
				'identifier' => 'bs:blog:more'
			)
		);
	}
}
