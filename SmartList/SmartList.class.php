<?php

/**
 * SmartList extension for BlueSpice
 *
 * Displays a list of pages, i.e. recently changed articles.
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
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @author     Leonid Verhovskij <verhovskij@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage SmartList
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for SmartList extension
 * @package BlueSpice_Extensions
 * @subpackage SmartList
 */
class SmartList extends BsExtensionMW {
	/**
	 * Initialization of ShoutBox extension
	 */
	protected function initExt() {
		wfProfileIn('BS::' . __METHOD__);
		$this->setHook( 'ParserFirstCallInit', 'onParserFirstCallInit' );
		$this->setHook( 'PageContentSaveComplete' );
		$this->setHook( 'BSWidgetListHelperInitKeyWords' );
		$this->setHook( 'BSUserSidebarDefaultWidgets' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		$this->setHook( 'BSDashboardsAdminDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsAdminDashboardPortalPortlets' );
		$this->setHook( 'BSDashboardsUserDashboardPortalConfig' );
		$this->setHook( 'BSDashboardsUserDashboardPortalPortlets' );
		$this->setHook( 'BSUsageTrackerRegisterCollectors' );

		BsConfig::registerVar( 'MW::SmartList::Count', 5, BsConfig::LEVEL_USER | BsConfig::TYPE_INT, 'bs-smartlist-pref-count', 'int');
		BsConfig::registerVar( 'MW::SmartList::Namespaces', array(), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-smartlist-pref-namespaces', 'multiselectex');
		BsConfig::registerVar( 'MW::SmartList::Categories', array(), BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_ARRAY_STRING, 'bs-smartlist-pref-categories', 'multiselectplusadd');
		// possible values: -, day, week, month
		BsConfig::registerVar( 'MW::SmartList::Period', '-', BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-smartlist-pref-period', 'select');
		BsConfig::registerVar( 'MW::SmartList::ShowMinorChanges', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-smartlist-pref-showminorchanges', 'toggle');
		BsConfig::registerVar( 'MW::SmartList::ShowOnlyNewArticles', false, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-smartlist-pref-showonlynewarticles', 'toggle');
		BsConfig::registerVar( 'MW::SmartList::Trim', 20, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_INT, 'bs-smartlist-pref-trim', 'int');
		BsConfig::registerVar( 'MW::SmartList::ShowText', false, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-smartlist-pref-showtext', 'toggle');
		BsConfig::registerVar( 'MW::SmartList::TrimText', 50, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_INT, 'bs-smartlist-pref-trimtext', 'int');
		// possible values: title, time
		BsConfig::registerVar( 'MW::SmartList::Order', 'DESC', BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-smartlist-pref-order', 'select'); //title|time
		BsConfig::registerVar( 'MW::SmartList::Sort', 'time', BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_STRING | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-smartlist-pref-sort', 'select');
		BsConfig::registerVar( 'MW::SmartList::ShowNamespace', true, BsConfig::LEVEL_PUBLIC | BsConfig::TYPE_BOOL, 'bs-smartlist-pref-shownamespace', 'toggle');
		BsConfig::registerVar( 'MW::SmartList::Comments', false, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL | BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-smartlist-pref-comments', 'check');

		wfProfileOut('BS::' . __METHOD__);
	}

	/**
	 * Hook-Handler for Hook 'LoadExtensionSchemaUpdates'
	 * @param object §updater Updater
	 * @return boolean Always true
	 */
	public static function getSchemaUpdates( $updater ) {
		$odbw = wfGetDB( DB_MASTER );
		if ( $odbw->tableExists( 'bs_settings' ) === true ) {
			$res = $odbw->select(
				'bs_settings',
				$odbw->addIdentifierQuotes( 'value' ),
				array(
					$odbw->addIdentifierQuotes( 'key' ) => 'MW::SmartList::Order'
				)
			);
			if ( $odbw->numRows( $res ) === 1 ) {
				$obj = $odbw->fetchObject( $res );
				if ( $obj->value === serialize( 'time' ) || $obj->value === serialize( 'title' ) ) {
					$odbw->delete(
					'bs_settings',
					array(
						$odbw->addIdentifierQuotes( 'key' ) => 'MW::SmartList::Order'
					)
				);
				}
			}
		}
		return true;
	}

	/**
	 * Returns list of most visited pages called via Ajax
	 * @param integer $iCount number of items
	 * @param sring $sTime timespan
	 * @return string most visited pages
	 */
	public static function getMostVisitedPages( $iCount, $sTime ) {
		try {
			$sContent = BsExtensionManager::getExtension( 'SmartList' )->getToplist( '', array( 'count' => $iCount, 'portletperiod' => $sTime ), null );
		} catch ( Exception $e ) {
			$oErrorListView = new ViewTagErrorList();
			$oErrorListView->addItem( new ViewTagError( $e->getMessage() ) );
			$sContent = $oErrorListView->execute();
		}
		return $sContent;
	}

	/**
	 * Returns list of most edited pages called via Ajax
	 * @param integer $iCount number of items
	 * @param sring $sTime timespan
	 * @return string most edited pages
	 */
	public static function getMostEditedPages( $iCount, $sTime ) {
		return BsExtensionManager::getExtension( 'SmartList' )->getEditedPages( $iCount, $sTime );
	}

	/**
	 * Returns list of most edited pages called via Ajax
	 * @param integer $iCount number of items
	 * @param sring $sTime timespan
	 * @return string most edited pages
	 */
	public static function getMostActivePortlet( $iCount, $sTime ) {
		return BsExtensionManager::getExtension( 'SmartList' )->getActivePortlet( $iCount, $sTime );
	}

	/**
	 * Returns list of most edited pages called via Ajax
	 * @param integer $iCount number of items
	 * @param sring $sCaller caller
	 * @return string most edited pages
	 */
	public static function getYourEditsPortlet( $iCount ) {
		return BsExtensionManager::getExtension( 'SmartList' )->getYourEdits( $iCount );
	}

	/**
	 * Sets parameters for more complex options in preferences
	 * @param string $sAdapterName Name of the adapter, e.g. MW
	 * @param BsConfig $oVariable Instance of variable
	 * @return array Preferences options
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		$aPrefs = array();
		switch ( $oVariable->getName() ) {
			case 'CategoryMode':
				$aPrefs = array(
					'options' => array(
						wfMessage( 'bs-smartlist-and' )->plain() => 'AND',
						wfMessage( 'bs-smartlist-or' )->plain() => 'OR'
					)
				);
				break;
			case 'Period':
				$aPrefs = array(
					'options' => array(
						'-' => '-',
						wfMessage( 'bs-smartlist-day' )->plain() => 'day',
						wfMessage( 'bs-smartlist-week' )->plain() => 'week',
						wfMessage( 'bs-smartlist-month' )->plain() => 'month'
					)
				);
				break;
			case 'Sort':
				$aPrefs = array(
					'options' => array(
						wfMessage( 'bs-smartlist-time' )->plain() => 'time',
						wfMessage( 'bs-smartlist-title' )->plain() => 'title'
					)
				);
				break;
			case 'Order':
				$aPrefs = array(
					'options' => array(
						wfMessage( 'bs-smartlist-sort-asc' )->plain() => 'ASC',
						wfMessage( 'bs-smartlist-sort-desc' )->plain() => 'DESC'
					)
				);
				break;
		}

		return $aPrefs;
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = array(
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain()
			),
			'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mosteditedpagesdesc' )->plain()
		);
		$aPortlets[] = array(
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain()
			),
			'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mostvisitedpagesdesc' )->plain()
		);
		$aPortlets[] = array(
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain()
			),
			'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mostactiveusersdesc' )->plain()
		);

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsAdminDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param boolean $bIsDefault default
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsAdminDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = array(
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain()
			)
		);
		$aPortalConfig[1][] = array(
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain()
			)
		);
		$aPortalConfig[2][] = array(
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain()
			)
		);

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsUserDashboardPortalPortlets
	 *
	 * @param array &$aPortlets reference to array portlets
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalPortlets( &$aPortlets ) {
		$aPortlets[] = array(
			'type'  => 'BS.SmartList.YourEditsPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-lastedits' )->plain()
			),
			'title' => wfMessage( 'bs-smartlist-lastedits' )->plain(),
			'description' => wfMessage( 'bs-smartlist-lasteditsdesc' )->plain()
		);
		$aPortlets[] = array(
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain()
			),
			'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mosteditedpagesdesc' )->plain()
		);
		$aPortlets[] = array(
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain()
			),
			'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mostvisitedpagesdesc' )->plain()
		);
		$aPortlets[] = array(
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain()
			),
			'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain(),
			'description' => wfMessage( 'bs-smartlist-mostactiveusersdesc' )->plain()
		);

		return true;
	}

	/**
	 * Hook Handler for BSDashboardsUserDashboardPortalConfig
	 *
	 * @param object $oCaller caller instance
	 * @param array &$aPortalConfig reference to array portlet configs
	 * @param boolean $bIsDefault default
	 * @return boolean always true to keep hook alive
	 */
	public function onBSDashboardsUserDashboardPortalConfig( $oCaller, &$aPortalConfig, $bIsDefault ) {
		$aPortalConfig[0][] = array(
			'type'  => 'BS.SmartList.MostVisitedPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mostvisitedpages' )->plain()
			)
		);
		$aPortalConfig[0][] = array(
			'type'  => 'BS.SmartList.YourEditsPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-lastedits' )->plain()
			)
		);
		$aPortalConfig[1][] = array(
			'type'  => 'BS.SmartList.MostEditedPortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mosteditedpages' )->plain()
			)
		);
		$aPortalConfig[2][] = array(
			'type'  => 'BS.SmartList.MostActivePortlet',
			'config' => array(
				'title' => wfMessage( 'bs-smartlist-mostactiveusers' )->plain()
			)
		);

		return true;
	}

	/**
	 * Callback for WidgetListHelper. Adds the WhoIsOnline Widget to the list if Keyword is found.
	 * @return ViewWidget.
	 */
	public function onWidgetListKeywordYourEdits() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$oWidgetView = new ViewWidget();
		$oWidgetView
			->setId( 'bs-smartlist-edits' )
			->setTitle( wfMessage( 'bs-smartlist-lastedits' )->plain() )
			->setBody( $this->getYourEdits( 5, 'widget', 30 ) )
			->setTooltip( wfMessage( 'bs-smartlist-lastedits' )->plain() )
			->setAdditionalBodyClasses( array( 'bs-nav-links', 'bs-widgetbar-portlet' ) ); //For correct margin and fontsize

		wfProfileOut( 'BS::'.__METHOD__ );
		return $oWidgetView;
	}

	/**
	 * Purges aricle cache on save when smartlist tag is present.
	 * @param Article $article The article that is created.
	 * @param User $user User that saved the article.
	 * @param Content $content
	 * @param string $summary Edit summary.
	 * @param bool $minoredit Marked as minor.
	 * @param bool $watchthis Put on watchlist.
	 * @param int $sectionanchor Not in use any more.
	 * @param int $flags Bitfield.
	 * @param Revision $revision New revision object.
	 * @param Status $status Status object (since MW1.14)
	 * @param int $baseRevId Revision ID this edit is based on (since MW1.15)
	 * @param bool $redirect Redirect user back to page after edit (since MW1.17)
	 * @return bool allow other hooked methods to be executed. Always true
	 */
	public function onPageContentSaveComplete( &$article, &$user, $content, $summary, $minoredit, $watchthis, $sectionanchor, &$flags, $revision, &$status, $baseRevId ) {
		if ( stripos( $content->getNativeData(), "smartlist" ) || stripos( $content->getNativeData(), "infobox" ) ) {
			$article->doPurge();
		}
		return true;
	}

	/**
	 * Registers a tag "bs:smartlist" with the parser. For legacy reasons with
	 * HalloWiki, also "smartlist" is supported. Called by ParserFirstCallInit
	 * hook.
	 * @param Parser $parser MediaWiki parser object
	 * @return bool true to allow other hooked methods to be executed. Always true.
	 */
	public function onParserFirstCallInit( &$parser ) {
		// for legacy reasons
		$parser->setHook( 'infobox', array( $this, 'onTagSmartList' ) );
		$parser->setHook( 'bs:infobox', array( $this, 'onTagSmartList' ) );

		$parser->setHook( 'smartlist', array( $this, 'onTagSmartList' ) );
		$parser->setHook( 'bs:smartlist', array( $this, 'onTagSmartList' ) );
		$parser->setHook( 'newbies', array( $this, 'onTagBsNewbies' ) );
		$parser->setHook( 'bs:newbies', array( $this, 'onTagBsNewbies' ) );
		$parser->setHook( 'toplist', array( $this, 'onTagToplist' ) );
		$parser->setHook( 'bs:toplist', array( $this, 'onTagToplist' ) );

		return true;
	}

	/**
	 * Callback for UserSidebar. Adds the YourEdits Widget to the UserSidebar as default filling.
	 * @param BsEvent $oEvent The event to handle
	 * @param array $aWidgets An array of WidgetView objects
	 * @return array An array of WidgetView objects
	 */
	public function onBSUserSidebarDefaultWidgets( &$aViews, $oUser, $oTitle ) {
		$aViews['YOUREDITS'] = $this->onWidgetListKeywordYourEdits();

		return true;
	}

	/**
	 * Event-Handler for 'MW::Utility::WidgetListHelper::InitKeywords'. Registers a callback for the INFOBOX Keyword.
	 * @param array $aKeywords An array of Keywords array( 'KEYWORD' => $callable )
	 * @return array The appended array of Keywords array( 'KEYWORD' => $callable )
	 */
	public function onBSWidgetListHelperInitKeyWords( &$aKeywords, $oTitle ) {
		$aKeywords['YOUREDITS'] = array( $this, 'onWidgetListKeywordYourEdits' );
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
		$oDescriptor->id = 'bs:smartlist';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'bs:smartlist';
		$oDescriptor->desc = wfMessage( 'bs-smartlist-tag-smartlist-desc' )->plain();
		$oDescriptor->code = '<bs:smartlist />';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = array(
			array(
				'label' => wfMessage( 'bs-smartlist-tag-smartlist-example-rc' )->plain(),
				'code' => '<bs:smartlist new="true" count="7" ns="104" trim="false" />'
			),
			array(
				'label' => wfMessage( 'bs-smartlist-tag-smartlist-example-whatlinkshere' )->plain(),
				'code' => '<bs:smartlist mode="whatlinkshere" traget="ARTICLENAME" />'
			)
		);
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/SmartList';
		$oResponse->result[] = $oDescriptor;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:newbies';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'bs:newbies';
		$oDescriptor->desc = wfMessage( 'bs-smartlist-tag-newbies-desc' )->plain();
		$oDescriptor->code = '<bs:newbies />';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = array(
			array(
				'code' => '<bs:newbies count="3" />'
			)
		);
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/SmartList';
		$oResponse->result[] = $oDescriptor;

		$oDescriptor = new stdClass();
		$oDescriptor->id = 'bs:toplist';
		$oDescriptor->type = 'tag';
		$oDescriptor->name = 'bs:toplist';
		$oDescriptor->desc = wfMessage( 'bs-smartlist-tag-toplist-desc' )->plain();
		$oDescriptor->code = '<bs:toplist />';
		$oDescriptor->previewable = false;
		$oDescriptor->examples = array(
			array(
				'code' => '<bs:toplist count="4" cat="Wiki" period="month" />'
			)
		);
		$oDescriptor->helplink = 'https://help.bluespice.com/index.php/SmartList';
		$oResponse->result[] = $oDescriptor;

		return true;
	}

	/**
	 * Renders the SmartList tag. Called by parser function.
	 * @param string $sInput Inner HTML of SmartList tag. Not used.
	 * @param array $aArgs List of tag attributes.
	 * @param Parser $oParser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function onTagSmartList( $sInput, $aArgs, $oParser ) {
		$oParser->disableCache();
		$oParser->getOutput()->setProperty( 'bs-tag-smartlist', 1 );

		foreach( $aArgs as $sArg => $sVal ) {
			//Allow Magic Words (Variables) and Parser Functions as arguments
			$aArgs[$sArg] = $oParser->recursivePreprocess( $sVal );
		}

		//Get arguments
		$aArgs['count'] = BsCore::sanitizeArrayEntry( $aArgs, 'count', 5, BsPARAMTYPE::INT );
		$aArgs['namespaces'] = BsCore::sanitizeArrayEntry( $aArgs, 'ns', 'all', BsPARAMTYPE::SQL_STRING );
		$aArgs['categories'] = BsCore::sanitizeArrayEntry( $aArgs, 'cat', '-', BsPARAMTYPE::SQL_STRING );
		$aArgs['categoryMode'] = BsCore::sanitizeArrayEntry( $aArgs, 'catmode', 'OR', BsPARAMTYPE::SQL_STRING );
		$aArgs['showMinorChanges'] = BsCore::sanitizeArrayEntry( $aArgs, 'minor', true, BsPARAMTYPE::BOOL );
		$aArgs['period'] = BsCore::sanitizeArrayEntry( $aArgs, 'period', '-', BsPARAMTYPE::SQL_STRING );
		$aArgs['mode'] = BsCore::sanitizeArrayEntry( $aArgs, 'mode', 'recentchanges', BsPARAMTYPE::STRING );
		$aArgs['showOnlyNewArticles'] = BsCore::sanitizeArrayEntry( $aArgs, 'new', false, BsPARAMTYPE::BOOL );
		$aArgs['heading'] = BsCore::sanitizeArrayEntry( $aArgs, 'heading', '', BsPARAMTYPE::STRING );
		$aArgs['trim'] = BsCore::sanitizeArrayEntry( $aArgs, 'trim', 30, BsPARAMTYPE::NUMERIC );
		$aArgs['showtext'] = BsCore::sanitizeArrayEntry( $aArgs, 'showtext', false, BsPARAMTYPE::BOOL );
		$aArgs['trimtext'] = BsCore::sanitizeArrayEntry( $aArgs, 'trimtext', 50, BsPARAMTYPE::NUMERIC );
		$aArgs['sort'] = BsCore::sanitizeArrayEntry( $aArgs, 'sort', 'time', BsPARAMTYPE::SQL_STRING );
		$aArgs['order'] = BsCore::sanitizeArrayEntry( $aArgs, 'order', 'DESC', BsPARAMTYPE::SQL_STRING );
		$aArgs['showns'] = BsCore::sanitizeArrayEntry( $aArgs, 'showns', true, BsPARAMTYPE::BOOL );
		$aArgs['numwithtext'] = BsCore::sanitizeArrayEntry( $aArgs, 'numwithtext', 100, BsPARAMTYPE::INT );
		$aArgs['meta'] = BsCore::sanitizeArrayEntry( $aArgs, 'meta', false, BsPARAMTYPE::BOOL );
		$aArgs['target'] = BsCore::sanitizeArrayEntry( $aArgs, 'target', '', BsPARAMTYPE::STRING );
		$aArgs['excludens'] = BsCore::sanitizeArrayEntry( $aArgs, 'excludens', '', BsPARAMTYPE::SQL_STRING );

		$oSmartListView = new ViewBaseElement();
		if ( !empty( $aArgs['heading'] ) ) {
			$oSmartListView->setTemplate('<div class="bs-smartlist"><h3>{HEADING}</h3>{LIST}</div>');
		} else {
			$oSmartListView->setTemplate('<div class="bs-smartlist">{LIST}</div>');
		}

		$sCustomList = $this->getCustomList( $aArgs );

		if ( empty( $sCustomList ) ) {
			$sCustomList = wfMessage( 'bs-smartlist-no-entries' )->plain();
		}

		$oSmartListView->addData( array(
			'HEADING' => !empty( $aArgs['heading'] ) ? $aArgs['heading'] : wfMessage( 'bs-smartlist-recent-changes' )->plain(),
			'LIST' => $sCustomList
			)
		);

		$oParser->getOutput()->setProperty( 'bs-smartlist', FormatJson::encode( $aArgs ) );
		return $oSmartListView->execute();
	}

	/**
	 * Actually renders the SmartList list view.
	 * @param int $aArgs['count'] Maximum number of items in list.
	 * @param string $aArgs['namespaces'] Comma separated list of namespaces that should be considered.
	 * @param string $aArgs['categories'] Comma separated list of categories that should be considered.
	 * @param string $aArgs['period'] Period of time that should be considered (-|day|week|month)
	 * @param string $aArgs['mode'] Defines the basic criteria of pages that should be considered. Default: recentchanges. Other Extensions can hook into SmartList and define their own mode.
	 * @param bool $aArgs['showMinorChanges'] Should minor changes be considered
	 * @param bool $aArgs['showOnlyNewArtiles'] Should edits be considered or only page creations
	 * @param int $aArgs['trim'] Maximum number of title characters.
	 * @param bool $aArgs['showtext'] Also display article text.
	 * @param int $aArgs['trimtext'] Maximum number of text characters.
	 * @param string $aArgs['order'] Sort order for list. (time|title)
	 * @param string $aArgs['excludens'] Comma separated list of excluded namespaces.
	 * @param bool $aArgs['showns'] Show namespace befor title.
	 * @return string HTML output that is to be displayed.
	 */
	private function getCustomList( $aArgs ) {
		/*
		 * Contains the items that need to be displayed
		 * @var List of objects with three properties: title, namespace and timestamp
		 */
		$aObjectList = array();
		$aNamespaceIds = array();

		$oErrorListView = new ViewTagErrorList( $this );
		$oValidationResult = BsValidator::isValid( 'ArgCount', $aArgs['count'], array( 'fullResponse' => true ) );
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}
		/*
		 * Validation of namespaces and categories
		 */
		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$aArgs['categoryMode'],
			array(
				'fullResponse' => true,
				'setname' => 'catmode',
				'set' => array(
					'AND',
					'OR'
				)
			)
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$aArgs['period'],
			array(
				'fullResponse' => true,
				'setname' => 'period',
				'set' => array(
					'-',
					'day',
					'week',
					'month'
				)
			)
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'PositiveInteger',
			$aArgs['trim'],
			array( 'fullResponse' => true )
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'PositiveInteger',
			$aArgs['trimtext'],
			array( 'fullResponse' => true )
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$aArgs['sort'],
			array(
				'fullResponse' => true,
				'setname' => 'sort',
				'set' => array(
					'time',
					'title'
				)
			)
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		$oValidationResult = BsValidator::isValid(
			'SetItem',
			$aArgs['order'],
			array(
				'fullResponse' => true,
				'setname' => 'order',
				'set' => array(
					'ASC',
					'DESC'
				)
			)
		);
		if ( $oValidationResult->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( $oValidationResult->getI18N() ) );
		}

		if ( $aArgs['mode'] == 'recentchanges' ) {
			$dbr = wfGetDB( DB_SLAVE );
			$aConditions = array();

			switch ( $aArgs['period'] ) {
				case 'month': $sMinTimestamp = $dbr->timestamp( time() - 30 * 24 * 60 * 60 );
					break;
				case 'week': $sMinTimestamp = $dbr->timestamp( time() - 7 * 24 * 60 * 60 );
					break;
				case 'day': $sMinTimestamp = $dbr->timestamp( time() - 24 * 60 * 60 );
					break;
				default:
					break;
			}

			try {
				$aNamespaceIds = $this->makeNamespaceArrayDiff( $aArgs );
				$aConditions[] = 'rc_namespace IN (' . implode( ',', $aNamespaceIds ) . ')';
			} catch ( BsInvalidNamespaceException $ex ) {
				$sInvalidNamespaces = implode( ', ', $ex->getListOfInvalidNamespaces() );
				$oErrorListView->addItem(
					new ViewTagError(
						wfMessage( 'bs-smartlist-invalid-namespaces' )
							->numParams( count( $ex->getListOfInvalidNamespaces() ) )
							->params( $sInvalidNamespaces )
							->text()
					)
				);
			}

			$this->makeCategoriesFilterCondition( $aConditions, $aArgs, 'rc_cur_id' );

			switch ( $aArgs['sort'] ) {
				case 'title':
					$sOrderSQL = 'rc_title';
					break;
				default:
					// ORDER BY MAX() - this one was tricky. It makes sure, only the changes with the maximum date are selected.
					$sOrderSQL = 'MAX(rc_timestamp)';
					break;
			}

			switch ( $aArgs['order'] ) {
				case 'ASC':
					$sOrderSQL .= ' ASC';
					break;
				default:
					$sOrderSQL .= ' DESC';
					break;
			}

			if ( !$aArgs['showMinorChanges'] ) {
				$aConditions[] = 'rc_minor = 0';
			}
			if ( $aArgs['showOnlyNewArticles'] ) {
				$sOrderSQL = 'MIN(rc_timestamp) DESC';
				$aConditions[] = 'rc_new = 1';
			}
			if ( !empty( $aArgs['period'] ) && $aArgs['period'] !== '-' ) {
				$aConditions[] = "rc_timestamp > '" . $sMinTimestamp . "'";
			}

			$aConditions[] = 'rc_title = page_title AND rc_namespace = page_namespace'; //prevent display of deleted articles
			$aConditions[] = 'NOT ( rc_type = 3 AND NOT ( rc_namespace = 6 ) )'; //include files

			$aFields = array( 'rc_title as title', 'rc_namespace as namespace' );
			if ( isset( $aArgs['meta'] ) && $aArgs['meta'] == true ) {
				$aFields[] = 'MAX(rc_timestamp) as time, rc_user_text as username';
			}
			if ( BsConfig::get( 'MW::SmartList::Comments' ) ) {
				$aFields[] = 'MAX(rc_comment) as comment';
			}
			$res = $dbr->select(
				array(
					'recentchanges',
					'page'
				),
				$aFields,
				$aConditions,
				__METHOD__,
				array(
					'GROUP BY' => 'rc_title, rc_namespace',
					'ORDER BY' => $sOrderSQL
				)
			);

			$iCount = 0;
			foreach ( $res as $row ) {
				if ( $iCount == $aArgs['count'] ) break;

				$oTitle = Title::makeTitleSafe( $row->namespace, $row->title );

				if ( !$oTitle || !$oTitle->quickUserCan( 'read' ) ) continue;

				$aObjectList[] = $row;
				$iCount++;
			}
			$dbr->freeResult( $res );

		} elseif( $aArgs['mode'] == 'whatlinkshere' ) {
			//PW(25.02.2015) TODO:
			//There could be filters - see Special:Whatlinkshere
			$oTargetTitle = empty( $aArgs['target'] )
				? $this->getContext()->getTitle()
				: Title::newFromText( $aArgs['target'] )
			;

			if( is_null($oTargetTitle) ) {
				$oErrorListView->addItem(
					new ViewTagError(
						wfMessage( 'bs-smartlist-invalid-target' )->text()
					)
				);
				return $oErrorListView->execute();
			}

			$dbr = wfGetDB( DB_SLAVE );
			$aTables = array(
				'pagelinks',
				'page',
			);
			$aFields = array(
				'title' => 'page_title',
				'namespace' => 'page_namespace',
			);
			$aConditions = array(
				"page_id = pl_from",
				"pl_namespace = {$oTargetTitle->getNamespace()}",
				"pl_from NOT IN ({$oTargetTitle->getArticleID()})",
				"pl_title = '{$oTargetTitle->getDBkey()}'",
			);
			$aOptions = array();

			try {
				$aConditions['page_namespace'] = $this->makeNamespaceArrayDiff( $aArgs );
			} catch ( BsInvalidNamespaceException $ex ) {
				$sInvalidNamespaces = implode( ', ', $ex->getListOfInvalidNamespaces() );
				$oErrorListView->addItem(
					new ViewTagError(
						wfMessage( 'bs-smartlist-invalid-namespaces' )
							->numParams( count( $ex->getListOfInvalidNamespaces() ) )
							->params( $sInvalidNamespaces )
							->text()
					)
				);
				return $oErrorListView->execute();
			}

			$this->makeCategoriesFilterCondition( $aConditions, $aArgs, 'page_id' );

			//Default: time
			$aOptions['ORDER BY'] = $aArgs['sort'] == 'title'
				? 'page_title'
				: 'page_id'
			;

			//Default DESC
			$aOptions['ORDER BY'] .= $aArgs['order'] == 'ASC'
				? ' ASC'
				: ' DESC'
			;

			$res = $dbr->select(
				$aTables,
				$aFields,
				$aConditions,
				__METHOD__,
				$aOptions
			);

			$iCount = 0;
			foreach( $res as $row ) {
				if( $iCount == $aArgs['count'] ) {
					break;
				}

				$oTitle = Title::makeTitleSafe( $row->namespace, $row->title );
				if( !$oTitle || !$oTitle->quickUserCan( 'read' ) ) {
					continue;
				}

				$aObjectList[] = $row;
				$iCount++;
			}

			$dbr->freeResult( $res );

		} else {
			wfRunHooks(
				'BSSmartListCustomMode', array( &$aObjectList, $aArgs, $this )
			);
		}

		if ( $oErrorListView->hasEntries() ) {
			return $oErrorListView->execute();
		}

		$oSmartListListView = new ViewBaseElement();
		$oSmartListListView->setAutoElement( false );
		$iItems = 1;
		if ( count( $aObjectList ) ) {
			foreach ( $aObjectList as $row ) {
				$oTitle = Title::makeTitleSafe( $row->namespace, $row->title );

				// Security here: only show pages the user can read.
				$sText = '';
				$sMeta = '';
				$sComment = '';
				$sTitle = $oTitle->getText();
				if ( BsConfig::get('MW::SmartList::Comments' ) ) {
					$sComment = ( strlen( $row->comment ) > 50 ) ? substr( $row->comment, 0, 50 ) . '...' : $row->comment;
					$sComment = wfMessage( 'bs-smartlist-comment' )->params( $sComment )->escaped();
				}
				if ( isset( $aArgs['meta'] ) && $aArgs['meta'] == true ) {
					$sMeta = ' - <i>('.$row->username.', '.$this->getLanguage()->date( $row->time, true, true ).')</i>';
				}
				$oSmartListListEntryView = new ViewBaseElement();
				if ( $aArgs['showtext'] && ( $iItems <= $aArgs['numwithtext'] ) ) {
					$oSmartListListEntryView->setTemplate( '*[[:{NAMESPACE}:{TITLE}|{DISPLAYTITLE}]]{META}<br/>{TEXT}' . "\n" );
					$sText = BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle );
					$sText = Sanitizer::stripAllTags( $sText );
					$sText = BsStringHelper::shorten( $sText, array( 'max-length' => $aArgs['trimtext'], 'position' => 'end' ) );
					$sText = '<nowiki>' . $sText . '</nowiki>';
				} else {
					$oSmartListListEntryView->setTemplate( '*[[:{NAMESPACE}:{TITLE}|{DISPLAYTITLE}]] {COMMENT} {META}' . "\n" );
				}

				if ( $aArgs['showns'] == true ) {
					$sDisplayTitle = $oTitle->getFullText();
				} else {
					$sDisplayTitle = $oTitle->getText();
				}
				$sDisplayTitle = BsStringHelper::shorten( $sDisplayTitle, array( 'max-length' => $aArgs['trim'], 'position' => 'middle' ) );

				$sNamespaceText = '';

				if ( $row->namespace > 0 && $row->namespace != null ) {
					$sNamespaceText = MWNamespace::getCanonicalName( $row->namespace );
				}
				$aData = array(
					'NAMESPACE' => $sNamespaceText,
					'TITLE' => $sTitle,
					'DISPLAYTITLE' => $sDisplayTitle,
					'COMMENT' => $sComment,
					'META' => $sMeta,
					'TEXT' => $sText
				);
				wfRunHooks( 'BSSmartListBeforeEntryViewAddData', array( &$aData, $aArgs, $oSmartListListEntryView, $row ) );
				$oSmartListListEntryView->addData( $aData );
				$oSmartListListView->addItem( $oSmartListListEntryView );
				$iItems++;
			}
		} else {
			return '';
		}
		return $this->mCore->parseWikiText( $oSmartListListView->execute(), $this->getTitle() );
	}

	/**
	 * Generates list of your edits
	 * @return string list of edits
	 */
	public function getYourEdits( $iCount, $sOrigin = 'dashboard', $iDisplayLength = 18 ) {
		wfProfileIn( 'BS::'.__METHOD__ );
		$iCount = BsCore::sanitize( $iCount, 0, BsPARAMTYPE::INT );

		$oDbr = wfGetDB( DB_SLAVE );
		$res = $oDbr->select(
			'revision',
			'rev_page',
			array( 'rev_user' => $this->getUser()->getId() ),
			__METHOD__,
			array(
				'GROUP BY' => 'rev_page',
				'ORDER BY' => 'MAX(rev_timestamp) DESC',
				'LIMIT' => $iCount
			)
		);

		$aEdits = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			foreach ( $res as $row ) {
				$sHtml = '';
				$oTitle = Title::newFromID( $row->rev_page );
				if ( !( $oTitle instanceof Title ) ) continue;
				if ( $sOrigin === 'dashboard' ) {
					$sHtml = $oTitle->getPrefixedText();
				} else {
					$sHtml = BsStringHelper::shorten( $oTitle->getPrefixedText() , array( 'max-length' => $iDisplayLength, 'position' => 'middle' ) );
				}
				$sLink = Linker::link( $oTitle, $sHtml );
				$aEdits[] = Html::openElement( 'li' ) . $sLink . Html::closeElement( 'li' );
			}
		} else {
			 return '<ul><li>' . wfMessage( 'bs-smartlist-noedits' )->plain() . '</ul></li>';
		}

		$sEdits = '<ul>' . implode( '', $aEdits ) . '</ul>';

		wfProfileOut( 'BS::'.__METHOD__ );
		return $sEdits;
	}

	/**
	 * Renders the BsNewbies tag. Called by parser function.
	 * @param string $sInput Inner HTML of BsNewbies tag. Not used.
	 * @param array $aArgs List of tag attributes.
	 * @param Parser $oParser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function onTagBsNewbies( $sInput, $aArgs, $oParser ) {
		$oParser->disableCache();
		$iCount = BsCore::sanitizeArrayEntry( $aArgs, 'count', 5, BsPARAMTYPE::INT );

		$oDbr = wfGetDB( DB_SLAVE );
		$res = $oDbr->select(
			'user',
			'user_id',
			array(),
			__METHOD__,
			array(
				'ORDER BY' => 'user_id DESC',
				'LIMIT' => $iCount
			)
		);

		$aOut = array();
		foreach ( $res as $row ) {
			$oUser = User::newFromId( $row->user_id );
			$oTitle = Title::makeTitle( NS_USER, $oUser->getName() );
			$sLink = BsLinkProvider::makeLink( $oTitle, $oUser->getName() );
			$aOut[] = $sLink;
		}

		$oDbr->freeResult( $res );
		$oParser->getOutput()->setProperty( 'bs-newbies', FormatJson::encode( $aArgs ) );
		return implode( ', ', $aOut );
	}

	/**
	 * Renders the BsTagToplist tag. Called by parser function.
	 * @param string $sInput Inner HTML of BsTagMToplist tag. Not used.
	 * @param array $aArgs List of tag attributes.
	 * @param Parser $oParser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function onTagToplist( $sInput, $aArgs, $oParser ) {
		$oParser->disableCache();

		$oParser->getOutput()->setProperty( 'bs-toplist', FormatJson::encode( $aArgs ) );

		try {
			$sContent = $this->getToplist( $sInput, $aArgs, $oParser );
		} catch ( Exception $e ) {
			$oErrorListView = new ViewTagErrorList();
			$oErrorListView->addItem( new ViewTagError( $e->getMessage() ) );
			$sContent = $oErrorListView->execute();
		}
		return $sContent;
	}

	/**
	 * Deprecated! page.page_counter was completely removed in MediaWiki 1.25
	 * Generates a list of the most visisted pages
	 * @deprecated since version 2.23.3
	 * @param string $sInput Inner HTML of BsTagMToplist tag. Not used.
	 * @param array $aArgs List of tag attributes.
	 * @param Parser $oParser MediaWiki parser object
	 * @return string HTML output that is to be displayed.
	 */
	public function getToplist( $sInput, $aArgs, $oParser ) {
		global $wgDBprefix;

		if ( !ExtensionRegistry::getInstance()->isLoaded( 'HitCounters' ) ) {
			return wfMessage( 'bs-smartlist-hitcounter-missing' )->plain();
		}
		$sCat = BsCore::sanitizeArrayEntry( $aArgs, 'cat',           '', BsPARAMTYPE::STRING );
		$sNs = BsCore::sanitizeArrayEntry( $aArgs, 'ns',            '', BsPARAMTYPE::STRING );
		$iCount = BsCore::sanitizeArrayEntry( $aArgs, 'count',         10, BsPARAMTYPE::INT );
		$sPeriod = BsCore::sanitizeArrayEntry( $aArgs, 'period', 'alltime', BsPARAMTYPE::STRING );
		$iPortletPeriod = BsCore::sanitizeArrayEntry( $aArgs, 'portletperiod', 0, BsPARAMTYPE::INT );
		$bAlltime = true;

		$oDbr = wfGetDB( DB_SLAVE );
		if ( in_array( $sPeriod, array( 'week', 'month' ) ) || in_array( $iPortletPeriod, array( 7, 30 ) ) ) {
			$aTables = array( 'bs_whoisonline' );
			$aColumns = array(
				'COUNT( wo_page_title ) AS page_counter',
				'wo_page_title',
				'wo_page_namespace'
			);
			$aConditions = array( 'wo_action' => 'view' );
			$aOptions = array(
				'GROUP BY' => 'wo_page_title',
				'ORDER BY' => 'page_counter DESC'
			);
			$aJoinConditions = array();

			if ( $sPeriod === 'week' || $iPortletPeriod === 7 ) {
				$iTimestamp = wfTimestamp( TS_UNIX ) - ( 7 * 24 * 60 * 60 );
				$aConditions[] = 'wo_timestamp >= ' . $iTimestamp;
			}
			$bAlltime = false;
		} else {
			$aTables         = array( 'hit_counter', 'page' );
			$aColumns        = array( 'page_title', 'page_counter', 'page_namespace' );
			$aConditions     = array( $wgDBprefix . 'hit_counter.page_id = ' . $wgDBprefix . 'page.page_id' );
			$aOptions        = array( 'ORDER BY' => 'page_counter DESC' );
			$aJoinConditions = array();
		}

		if ( !empty( $sCat ) ) {
			$aCategories = explode( ',', $sCat );
			$aCategories = array_map('trim', $aCategories);
			$aCategories = str_replace(' ', '_',$aCategories);

			if ( $bAlltime === false ) {
				$aColumns[] = 'wo_page_id';
				$aJoinConditions = array( 'categorylinks' => array( 'INNER JOIN ', 'wo_page_id = cl_from' ) );
				$aTables[]            = 'categorylinks';
				$aConditions['cl_to'] = $aCategories;
			} else {
				$aTables[]            = 'categorylinks';
				$aConditions[]        = $wgDBprefix . 'page.page_id = cl_from';
				$aConditions['cl_to'] = $aCategories;
			}
		}

		if ( !empty( $sNs ) || $sNs === '0') { // string 0 is empty
			$aNamespaces = BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $sNs );
			if ( !empty( $aNamespaces ) ) {
				$sField = $bAlltime ? 'page_namespace' : 'wo_page_namespace';
				$aConditions[$sField] = $aNamespaces;
			}
		}

		$res = $oDbr->select(
			$aTables,
			$aColumns,
			$aConditions,
			__METHOD__,
			$aOptions,
			$aJoinConditions
		);

		if ( $oDbr->numRows( $res ) > 0 ) {
			$bCategories = false;
			if ( !empty( $aCategories ) ) {
				$bCategories = true;
				$aPrefixedCategories = array();
				foreach ( $aCategories As $sCategory ) {
					$sCategory = str_replace( ' ', '_', $sCategory );
					$sCat = Title::makeTitle( NS_CATEGORY, $sCategory );
					$aPrefixedCategories[] = $sCat->getPrefixedDBKey();
				}
			}

			$aList = array();
			$aInList = array();
			$iCurrCount = 0;
			if ( $bAlltime === false ) {
				foreach ( $res as $row ) {
					if ( $iCurrCount === $iCount ) break;
					if ( empty( $row->wo_page_title ) ) continue;
					$oTitle = Title::makeTitle( $row->wo_page_namespace, $row->wo_page_title );

					if ( !$oTitle->quickUserCan( 'read' ) ) continue;

					if ( $bCategories === true ) {
						$aParents = array_keys( $oTitle->getParentCategories() );
						$aResult  = array_diff( $aPrefixedCategories, $aParents );
						if ( !empty( $aResult ) ) {
							continue;
						}
					}
					if( in_array( $oTitle->getPrefixedText(), $aInList ) ) {
						continue;
					}
					$aInList[] = $oTitle->getPrefixedText();
					$sLink = BsLinkProvider::makeLink( $oTitle );
					$aList['<li>'. $sLink . ' (' . $row->page_counter . ')</li>'] = (int)$row->page_counter;
					$iCurrCount++;
				}
				arsort( $aList );
				$aList = array_keys( $aList );
				array_unshift( $aList, '<ol>');
			} else {
				$aList[] = '<ol>';
				foreach ( $res as $row ) {
					if ( $iCurrCount == $iCount ) break;
					if ( $row->page_counter == '0' ) continue;

					$oTitle  = Title::makeTitle( $row->page_namespace, $row->page_title );
					if ( !$oTitle->quickUserCan( 'read' ) ) continue;

					if ( $bCategories === true ) {
						$aParents = array_keys( $oTitle->getParentCategories() );
						$aResult  = array_diff( $aPrefixedCategories, $aParents );
						if ( !empty( $aResult ) ) continue;
					}

					if( in_array( $oTitle->getPrefixedText(), $aInList ) ) {
						continue;
					}
					$aInList[] = $oTitle->getPrefixedText();

					$sLink = BsLinkProvider::makeLink( $oTitle );
					$aList[] = '<li>' . $sLink . ' (' . $row->page_counter . ')</li>';
					$iCurrCount++;
				}
			}
			$aList[] = '</ol>';

			$oDbr->freeResult( $res );
			return "\n" . implode( "\n", $aList );
		}

		$oDbr->freeResult( $res );
		return wfMessage( 'bs-smartlist-no-entries' )->plain();
	}

	/**
	 * Generates list of most edited pages
	 * @return String list of pages or empty
	 */
	public function getEditedPages( $iCount, $iTime ) {
		$oDbr = wfGetDB( DB_SLAVE );
		$iCount = BsCore::sanitize( $iCount, 10, BsPARAMTYPE::INT );
		$iTime = BsCore::sanitize( $iTime, 0, BsPARAMTYPE::INT );

		$aConditions = array();
		if ( $iTime !== 0 ) {
			$this->getTimestampForQuery( $aConditions, $iTime );
		}

		$res = $oDbr->select(
				'revision',
				array(
					'COUNT(rev_page) as page_counter',
					'rev_page'
				),
				$aConditions,
				__METHOD__,
				array(
					'GROUP BY' => 'rev_page',
					'ORDER BY' => 'page_counter DESC',
					'LIMIT' => $iCount
				)
		);

		$aList = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			$aList[] = '<ol>';

			foreach ( $res as $row ) {
				$oTitle = Title::newFromID( $row->rev_page );
				$sLink = BsLinkProvider::makeLink( $oTitle );
				$aList[] = '<li>' . $sLink . ' (' . $row->page_counter . ')</li>';
			}

			$aList[] = '</ol>';
		}

		$oDbr->freeResult( $res );
		return implode( "\n", $aList );
	}

	/**
	 * Generates list of most edited pages
	 * @return String list of pages or empty
	 */
	public function getActivePortlet( $iCount, $iTime ) {
		$oDbr = wfGetDB( DB_SLAVE );
		$iCount = BsCore::sanitize( $iCount, 10, BsPARAMTYPE::INT );
		$iTime = BsCore::sanitize( $iTime, 0, BsPARAMTYPE::INT );

		$aConditions = array();
		if ( $iTime !== 0 ) {
			$this->getTimestampForQuery( $aConditions, $iTime );
		}

		$res = $oDbr->select(
				'revision',
				array(
					'COUNT(rev_user) as edit_count',
					'rev_user'
				),
				$aConditions,
				__METHOD__,
				array(
					'GROUP BY' => 'rev_user',
					'ORDER BY' => 'edit_count DESC'
				)
		);

		$aList = array();
		if ( $oDbr->numRows( $res ) > 0 ) {
			$aList[] = '<ol>';

			$i = 1;
			foreach ( $res as $row ) {
				if ( $i > $iCount ) break;
				$oUser = User::newFromId( $row->rev_user );
				if ( $oUser->isIP( $oUser->getName() ) ) continue;

				$oTitle = Title::makeTitle( NS_USER, $oUser->getName() );
				$sLink = BsLinkProvider::makeLink( $oTitle );
				$aList[] = '<li>' . $sLink . ' (' . $row->edit_count . ')</li>';
				$i++;
			}

			$aList[] = '</ol>';
		}

		$oDbr->freeResult( $res );
		return implode( "\n", $aList );
	}

	/**
	 * Returns timestamp for portlet queries, at at moment just for month
	 *
	 * @param aray &$aConditions reference to array of conditions
	 * @return boolean always true
	 */
	public function getTimestampForQuery( &$aConditions, $iTime ) {
		$iTimeInSec = $iTime * 24 * 60 * 60;
		$iTimeStamp = wfTimestamp( TS_UNIX ) - $iTimeInSec;
		$iTimeStamp = wfTimestamp( TS_MW, $iTimeStamp );
		$aConditions = array( 'rev_timestamp >= '.$iTimeStamp );

		return true;
	}

	public function makeCategoriesFilterCondition( &$aConditions, $aArgs, $sPageIdFileName) {
		if ( $aArgs['categories'] != '-' && $aArgs['categories'] != '' ) {
			$aCategories = explode( ',', $aArgs['categories'] );
			$iCnt = count( $aCategories );
			for ( $i = 0; $i < $iCnt; $i++ ) {
				$oCategory = Category::newFromName( trim( $aCategories[$i] ) );
				if( $oCategory === false ) {
					unset( $aCategories[$i] );
					continue;
				}
				$aCategories[$i] = "'" . $oCategory->getName() . "'" ;
			}
			$aArgs['categories'] = implode( ',', $aCategories );

			$dbr = wfGetDB( DB_SLAVE );
			if ( $aArgs['categoryMode'] == 'OR' ) {
				$aConditions[] = $sPageIdFileName.' IN ( SELECT cl_from FROM ' . $dbr->tableName( 'categorylinks' ) . ' WHERE cl_to IN (' . $aArgs['categories'] . ') )';
			} else {
				foreach ( $aCategories as $sCategory ) {
					$aConditions[] = $sPageIdFileName.' IN ( SELECT cl_from FROM ' . $dbr->tableName( 'categorylinks' ) . ' WHERE cl_to = ' . $sCategory . ' )';
				}
			}
		}
	}

	/*
	 * Remove the excluded namespaces from the list of namespaces.
	 *
	 * @param array &$aArgs Arguments of custom list
	 * @param array &$aNamespaceDiff
	 */
	public function makeNamespaceArrayDiff( &$aArgs ) {

		if ( isset( $aArgs['excludens'] ) &&  $aArgs['excludens'] !== '' ) {
			$aNamespaceDiff = array_diff(
				BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $aArgs['namespaces'] ),
				BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $aArgs['excludens'] )
			);
		}
		else {
			$aNamespaceDiff = BsNamespaceHelper::getNamespaceIdsFromAmbiguousCSVString( $aArgs['namespaces'] );
		}

		return $aNamespaceDiff;
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:smartlist'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-smartlist'
			)
		);
		return true;
	}

	/**
	 * UnitTestsList allows registration of additional test suites to execute
	 * under PHPUnit. Extensions can append paths to files to the $paths array,
	 * and since MediaWiki 1.24, can specify paths to directories, which will
	 * be scanned recursively for any test case files with the suffix "Test.php".
	 * @param array $paths
	 */
	public static function onUnitTestsList( array &$paths ) {
		$paths[] = __DIR__ . '/tests/phpunit/';
		return true;
	}
}
