<?php
/**
 * Authors extension for BlueSpice
 *
 * Appends a new link to the wishlist page.
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
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
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Mathias Scheer <Scheer@hallowelt.com>
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Patric Wirth <wirth@hallowelt.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage WantedArticle
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for WantedArticle extension
 * @package BlueSpice_Extensions
 * @subpackage WantedArticle
 */
class WantedArticle extends BsExtensionMW {

	/**
	 * Initialization of WantedArticle extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::WantedArticle::initExt' );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'ArticleSaveComplete' );
		$this->setHook( 'BSExtendedSearchAdditionalActions' );
		$this->setHook( 'BSWidgetBarGetDefaultWidgets' );
		$this->setHook( 'BSWidgetListHelperInitKeyWords' );
		$this->setHook( 'BSExtendedSearchAutocomplete' );
		$this->setHook( 'BSInsertMagicAjaxGetData' );
		$this->setHook( 'BeforePageDisplay' );
		$this->setHook( 'BSUsageTrackerRegisterCollectors' );

		$this->mCore->registerPermission( 'wantedarticle-suggest', array(), array( 'type' => 'namespace' ) );

		BsConfig::registerVar( 'MW::WantedArticle::DataSourceTemplateTitle', 'WantedArticles', BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_STRING, 'bs-wantedarticle-pref-datasourcetemplatetitle' );
		BsConfig::registerVar( 'MW::WantedArticle::IncludeLimit', 10, BsConfig::LEVEL_USER|BsConfig::TYPE_INT, 'bs-wantedarticle-pref-includelimit', 'int' );
		BsConfig::registerVar( 'MW::WantedArticle::Sort', 'time', BsConfig::LEVEL_USER|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-wantedarticle-pref-sort', 'select' ); // 'time' | 'title'
		BsConfig::registerVar( 'MW::WantedArticle::Order', 'DESC', BsConfig::LEVEL_USER|BsConfig::TYPE_STRING|BsConfig::USE_PLUGIN_FOR_PREFS, 'bs-wantedarticle-pref-order', 'select' ); // 'ASC' | 'DESC'
		BsConfig::registerVar( 'MW::WantedArticle::DeleteExisting', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-wantedarticle-pref-deleteexisting', 'toggle' );
		BsConfig::registerVar( 'MW::WantedArticle::DeleteOnCreation', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL, 'bs-wantedarticle-pref-deleteoncreation', 'toggle' );
		BsConfig::registerVar( 'MW::WantedArticle::ShowCreate', true, BsConfig::LEVEL_PUBLIC|BsConfig::TYPE_BOOL|BsConfig::RENDER_AS_JAVASCRIPT, 'bs-wantedarticle-pref-showcreate', 'toggle' );

		wfProfileOut( 'BS::WantedArticle::initExt' );
	}

	/**
	* Adds the 'ext.bluespice.wantedarticle' module to the OutputPage
	* @param OutputPage $out
	* @param Skin $skin
	* @return boolean
	*/
	public function onBeforePageDisplay( $out, $skin) {
		$out->addModules( 'ext.bluespice.wantedarticle' );
		return true;
	}

	/**
	 * Callback for the preferences.
	 * @param string $sAdapterName The curren Adapter name
	 * @param BsConfig $oVariable The Variable
	 * @return array The (MediaWiki) config array
	 */
	public function runPreferencePlugin( $sAdapterName, $oVariable ) {
		switch ( $oVariable->getKey() ) {
			case 'MW::WantedArticle::Sort':
				return array(
					'options' => array(
						wfMessage( 'bs-wantedarticle-pref-sort-time' )->plain() => 'time',
						wfMessage( 'bs-wantedarticle-pref-sort-title' )->plain() => 'title',
					)
				);
				break;
			case 'MW::WantedArticle::Order':
				return array(
					'options' => array(
						wfMessage( 'bs-wantedarticle-pref-order-asc' )->plain() => 'ASC',
						wfMessage( 'bs-wantedarticle-pref-order-desc' )->plain() => 'DESC',
					)
				);
				break;
			default: return array();
				break;
		}
	}

	/**
	 * This method registers the callbacks for the <bs:wantedarticles /> tag extension.
	 * @param Parser $oParser The MediaWiki Parser object
	 * @return bool Always true to keep the hook running.
	 */
	public function onParserFirstCallInit( $oParser ) {
		$oParser->setHook( 'wantedarticles', array( $this, 'onWantedArticlesTag' ) );
		$oParser->setHook( 'wantedarticle', array( $this, 'onWantedArticlesTag' ) );
		$oParser->setHook( 'wishlist', array( $this, 'onWantedArticlesTag' ) );
		$oParser->setHook( 'bs:wantedarticle', array( $this, 'onWantedArticlesTag' ) );
		$oParser->setHook( 'bs:wantedarticles', array( $this, 'onWantedArticlesTag' ) );
		$oParser->setHook( 'bs:wishlist', array( $this, 'onWantedArticlesTag' ) );
		$oParser->setHook( 'bs:wantedarticleform', array( $this, 'onWantedArticleFormTag' ) );
		return true;
	}

	/**
	 * Event-Handler for 'MW::Utility::WidgetListHelper::InitKeywords'. Registers a callback for the WATCHLIST Keyword.
	 * @param array $aKeywords An array of Keywords array( 'KEYWORD' => $callable )
	 * @return array The appended array of Keywords array( 'KEYWORD' => $callable )
	 */
	public function onBSWidgetListHelperInitKeyWords( &$aKeywords, $oTitle ) {
		$aKeywords['WANTEDARTICLES'] = array( $this, 'onWidgetListKeyword' );
		$aKeywords['WANTEDARTICLE']  = array( $this, 'onWidgetListKeyword' );
		$aKeywords['WISHLIST'] = array( $this, 'onWidgetListKeyword' );
		return true;
	}

	/**
	 * Creates a Widget and returns it
	 * @return ViewWidget
	 */
	public function onWidgetListKeyword() {
		$sTitle = wfMessage( 'bs-wantedarticle-tag-default-title' )->plain();
		$aWishList = $this->getTitleListFromTitle(
			$this->getDataSourceTemplateArticle()->getTitle()
		);

		$sSort = BsConfig::get( 'MW::WantedArticle::Sort' );
		switch ( $sSort ) {
			case 'title':
				$aTitleList = $this->sortWishListByTitle( $aWishList );
				break;
			case 'time':
			default:
				$aTitleList = $this->getDefaultTitleList( $aWishList );
		}

		if ( BsConfig::get( 'MW::WantedArticle::Order' ) == 'DESC' ) {
			$aTitleList = array_reverse( $aTitleList );
		}
		$iIncludeLimit = BsConfig::get( 'MW::WantedArticle::IncludeLimit' );
		$iCount = count( $aTitleList );
		$iCount = ( $iCount > $iIncludeLimit ) ? $iIncludeLimit : $iCount;
		$aWikiCodeList = array();
		$oTitle = null;
		$sWishTitle = '';
		for ( $i = 0; $i < $iCount; $i++ ) {
			$oTitle = $aTitleList[$i];
			$sWishTitle = BsStringHelper::shorten(
				$oTitle->getPrefixedText(),
				array( 'max-length' => 30, 'position' => 'middle' )
			);
			$aWikiCodeList[] = '*'.BsLinkProvider::makeEscapedWikiLinkForTitle( $oTitle, $sWishTitle );
		}

		$sBody = $this->mCore->parseWikiText( implode( "\n", $aWikiCodeList ), $this->getTitle() );

		$oWidgetView = new ViewWidget();
		$oWidgetView
			->setTitle( $sTitle )
			->setBody( $sBody )
			->setTooltip( $sTitle )
			->setAdditionalBodyClasses( array( 'bs-nav-links' ) ); //For correct margin and fontsize
		return $oWidgetView;
	}

	/**
	 * Callback for WidgetBar. Adds the WantedArticle Widget to the WidgetBar as default filling.
	 * @param BsEvent $oEvent The event to handle
	 * @param array $aWidgets An array of WidgetView objects
	 * @return array An array of WidgetView objects
	 */
	public function onBSWidgetBarGetDefaultWidgets( &$aViews, $oUser, $oTitle ){
		$aViews['WANTEDARTICLES'] = $this->onWidgetListKeyword();
		return true;
	}

	/**
	 * Hook-Handler for MediaWiki 'ArticleSaveComplete' hook. Removes an article from wishlist when created.
	 * @param Article $oArticle Article modified
	 * @param User $oUser User performing the modification
	 * @param string $sText New content
	 * @param string $sSummary Edit summary/comment
	 * @param bool $bIsMinor Whether or not the edit was marked as minor
	 * @param bool $bIsWatch (No longer used)
	 * @param int $iSection (No longer used)
	 * @param mixed $vFlags Flags passed to Article::doEdit()
	 * @param Revision $oRevision New Revision of the article
	 * @param Object $oStatus
	 * @param mixed $vBaseRevId the rev ID (or false) this edit was based on
	 * @return bool Always true to keep hooks running.
	 */
	public function onArticleSaveComplete( $oArticle, $oUser, $sText, $sSummary, $bIsMinor, $bIsWatch, $iSection, $vFlags, $oRevision, $oStatus, $vBaseRevId ) {
		if( $oArticle->getTitle()->equals( $this->getDataSourceTemplateArticle()->getTitle() ) ) {
			BsCacheHelper::invalidateCache( BsCacheHelper::getCacheKey( 'BlueSpice', 'WantedArticle', $oArticle->getTitle()->getPrefixedText() ) );
		}
		if( $oStatus->value['new'] != true ) return true;
		if( BsConfig::get( 'MW::WantedArticle::DeleteOnCreation' ) === false ) return true;

		$oWantedArticleListTitle = $this->getDataSourceTemplateArticle()->getTitle();
		$aWishList = $this->getTitleListFromTitle( $oWantedArticleListTitle );

		$oNewTitle = $oArticle->getTitle();
		$bListChanged = false;
		foreach ( $aWishList as $key => $aWish ) {
			if ( !$oNewTitle->equals( $aWish['title'] ) ) continue;
			unset( $aWishList[$key] );
			$bListChanged  = true;
			break;

		}
		if( $bListChanged ){
			$this->saveTitleListToTitle(
				$aWishList,
				$oWantedArticleListTitle,
				wfMessage( 'bs-wantedarticle-page-removed', $oNewTitle->getPrefixedText() )->plain()
			);
		}
		return true;
	}

	/**
	 * Hook-Handler for BlueSpice 'BSExtendedSearchAdditionalActions' hook. Creates suggest & create link in the ExtendedSearch.
	 * @param string &$createsuggest contains links
	 * @param string &$searchUrlencoded Urlencoded search request
	 * @param string &$searchHtmlEntities link
	 * @param Title &$oTitle Title Object
	 * @return bool Always true to keep hooks running.
	 */
	public function onBSExtendedSearchAdditionalActions( &$sCreatesuggest, &$searchUrlencoded, &$searchHtmlEntities, &$oTitle ) {
		if ( !( $oTitle instanceof Title ) ) return true;

		if ( $oTitle->userCan( 'createpage' ) && $oTitle->userCan( 'edit' ) ) {
			$sCreatesuggest .= '<li><a href="' . $oTitle->getLinkURL() . '" >' . wfMessage( 'bs-wantedarticle-create-page', $searchHtmlEntities )->plain() . '</a></li>';
		}
		if ( $oTitle->userCan( 'wantedarticle-suggest' ) ) {
			$sCreatesuggest .= '<li><a id="bs-extendedsearch-suggest" href="#'.$searchUrlencoded.'" >'.wfMessage( 'bs-wantedarticle-suggest-page', $searchHtmlEntities )->plain().'</a></li>';
		}
		return true;
	}

	/**
	 * Hook-Handler for BlueSpice 'BSExtendedSearchAutocomplete' hook. Creates suggest & create link in autocomplete.
	 * @param array &$aResults contains results
	 * @param string &$sSearchString search string
	 * @param int &$iID number of last item
	 * @return bool Always true to keep hooks running.
	 */
	public function onBSExtendedSearchAutocomplete( &$aResults, $sSearchString, &$iID, $bTitleExists, $sEcpSearchString ) {
		if ( empty( $sSearchString ) ) return true;
		if ( $bTitleExists === true ) return true;

		if ( BsConfig::get( 'MW::ExtendedSearch::ShowCreSugInAc' ) == false ) return true;

		$sShortAndEscapedString = BsStringHelper::shorten(
			$sEcpSearchString,
			array(
				'max-length' => '30',
				'position' => 'middle',
				'ellipsis-characters' => '...'
			)
		);

		$oTitle = Title::newFromText( $sEcpSearchString );
		if ( is_object( $oTitle ) ) {
			if ( $oTitle->userCan( 'createpage' ) && $oTitle->userCan( 'edit' ) ) {
				$oItemCreate = new stdClass();
				$oItemCreate->id = ++$iID;
				$oItemCreate->value = $sEcpSearchString;
				$oItemCreate->label = wfMessage( 'bs-wantedarticle-create-page', '<b>' . $sShortAndEscapedString . '</b>' )->plain() . '';
				$oItemCreate->type = '';
				$oItemCreate->link = $oTitle->getFullURL();
				$oItemCreate->attr = 'bs-extendedsearch-create';
				$aResults[] = $oItemCreate;
			}

			if ( $oTitle->userCan( 'wantedarticle-suggest' ) ) {
				$oItemSuggest = new stdClass();
				$oItemSuggest->id = ++$iID;
				$oItemSuggest->value = $sEcpSearchString;
				$oItemSuggest->label = wfMessage( 'bs-wantedarticle-suggest-page', '<b>' . $sShortAndEscapedString . '</b>' )->plain() . '';
				$oItemSuggest->type = '';
				$oItemSuggest->link = '#' . $sEcpSearchString;
				$oItemSuggest->attr = 'bs-extendedsearch-suggest';
				$aResults[] = $oItemSuggest;
			}
		}

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
			'id' => 'bs:wantedarticle',
			'type' => 'tag',
			'name' => 'wantedarticle',
			'desc' => wfMessage( 'bs-wantedarticle-tag-wantedarticle-desc' )->plain(),
			'code' => '<bs:wantedarticle />',
			'examples' => array(
				array(
					'code' => '<bs:wantedarticle count="15" sort="time" title="Wanted articles" />'
				)
			),
			'helplink' => 'https://help.bluespice.com/index.php/WantedArticles'
		);

		return true;
	}

	/**
	 * Callback for MediaWiki Parser. Renders the list of wanted articles
	 * @param string $sInput The content of the tag. Usually empty string.
	 * @param array $aAttributes An Array of given attributes
	 * @param Parser $oParser The MediaWiki parser object
	 * @return string The rendered <bs:wantedarticles /> tag
	 */
	public function onWantedArticleFormTag( $sInput, $aAttributes, $oParser ) {
		$oParser->getOutput()->setProperty( 'bs-tag-wantedarticleform', 1 );

		$oFormView = new ViewWantedArticleForm();
		$oFormView->setFormVariant( 'tag-form' );
		$oFormView->setShowCreateArticle( false );
		return str_replace("\n", " ", $oFormView->execute());
	}

	/**
	 * Callback for MediaWiki Parser. Renders the list of wanted articles
	 * @param string $sInput The content of the tag. Usually empty string.
	 * @param array $aAttributes An Array of given attributes
	 * @param Parser $oParser The MediaWiki parser object
	 * @return string The rendered <bs:wantedarticles /> tag
	 */
	public function onWantedArticlesTag( $sInput, $aAttributes, $oParser ) {
		$oParser->disableCache();
		$oParser->getOutput()->setProperty( 'bs-tag-wantedarticles', 1 );

		$oErrorListView = new ViewTagErrorList( $this );

		$sDefaultTitle = wfMessage( 'bs-wantedarticle-tag-default-title' )->plain();

		$iCount = BsCore::sanitizeArrayEntry( $aAttributes, 'count', 5,              BsPARAMTYPE::INT );
		$sSort  = BsCore::sanitizeArrayEntry( $aAttributes, 'sort',  'time',         BsPARAMTYPE::STRING );
		$sOrder = BsCore::sanitizeArrayEntry( $aAttributes, 'order', 'DESC',          BsPARAMTYPE::STRING );
		$sTitle = BsCore::sanitizeArrayEntry( $aAttributes, 'title', $sDefaultTitle, BsPARAMTYPE::STRING );
		$sType  = BsCore::sanitizeArrayEntry( $aAttributes, 'type',  'list',         BsPARAMTYPE::STRING );

		//Validation
		$oValidationICount = BsValidator::isValid( 'IntegerRange', $iCount, array('fullResponse' => true, 'lowerBoundary' => 1, 'upperBoundary' => 30) );
		if ( $oValidationICount->getErrorCode() ) {
			$oErrorListView->addItem( new ViewTagError( 'count: '.$oValidationICount->getI18N() ) );
		}

		if ( !in_array( $sSort, array( '', 'time', 'title' ) ) ) {
			$oErrorListView->addItem( new ViewTagError( 'sort: '.wfMessage( 'bs-wantedarticle-sort-value-unknown' )->plain() ) );
		}

		if ( !in_array( $sOrder, array( '', 'ASC', 'DESC' ) ) ) {
			$oErrorListView->addItem( new ViewTagError( 'order: '.wfMessage( 'bs-wantedarticle-order-value-unknown' )->plain() ) );
		}

		if ( $oErrorListView->hasItems() ) {
			return $oErrorListView->execute();
		}

		//Create list
		$aWishList = $this->getTitleListFromTitle(
			$this->getDataSourceTemplateArticle()->getTitle()
		);

		switch( $sSort ) {
			case 'title':
				$aTitleList = $this->sortWishListByTitle( $aWishList );
				break;
			case 'time':
			default:
				$aTitleList = $this->getDefaultTitleList( $aWishList );
		}

		if( $sOrder == 'ASC' ) {
			$aTitleList = array_reverse( $aTitleList );
		}

		$oWishListView = new ViewWantedArticleTag();
		$oWishListView
			->setTitle( $sTitle )
			->setType ( $sType )
			->setOrder( $sOrder )
			->setSort ( $sSort )
			->setCount( $iCount )
			->setList ( $aTitleList );

		return $oWishListView->execute();
	}

	/**
	 *
	 * @param Title $oTitle
	 * @return array An Array of Title objects
	 */
	public function getTitleListFromTitle( $oTitle ) {
		$sKey = BsCacheHelper::getCacheKey( 'BlueSpice', 'WantedArticle', $oTitle->getPrefixedText() );
		$aData = BsCacheHelper::get( $sKey );

		if( $aData !== false ) {
			wfDebugLog( 'BsMemcached', __CLASS__.': Fetching WantedArticle list from cache' );
			$aTitleList = $aData;
		} else {
			wfDebugLog( 'BsMemcached', __CLASS__.': Fetching WantedArticle list from DB');
			$oArticleContent = BsPageContentProvider::getInstance()->getContentFromTitle( $oTitle );

			$aTitleList = array();
			$aLines = explode( "\n", $oArticleContent );

			foreach( $aLines as $sLine ){
				$sLine = trim( $sLine );
				if( empty( $sLine ) || $sLine[0] != '*' ) continue;
				$aMatches = array();
				#*[[Title]] --[[Spezial:Beiträge/0:0:0:0:0:0:0:1|0:0:0:0:0:0:0:1]] 12:31, 7. Jan. 2013 (AST)
				#*[[Title2]]--[[Benutzer:WikiSysop|WikiSysop]] ([[Benutzer Diskussion:WikiSysop|Diskussion]]) 17:47, 4. Jan. 2013 (AST)
				preg_match('#\*.*?\[\[(.*?)\]\]( ?--\[\[.*?:(.*?/)?(.*?)\|.*?\]\].*?\)? (\(.*?\))? ?(.*?))?$#si', $sLine, $aMatches);
				if( empty($aMatches) || !isset($aMatches[1]) ) continue;

				$sTitle = $aMatches[1];
				$sUsername = isset($aMatches[4]) ? $aMatches[4] : '';
				$sSignature = isset($aMatches[2]) ? $aMatches[2] : '';

				$oT = Title::newFromText( $sTitle );
				if( $oT === null ) continue;
				$aTitleList[] = array(
					'title'       => $oT,
					//'mwtimestamp' => $sTime, // MW timestamp not currently not in use
					'username'    => $sUsername,
					'signature'   => $sSignature,
				);
			}
			BsCacheHelper::set( $sKey, $aTitleList );
		}
		return $aTitleList;
	}

	/**
	 *
	 * @param array $aWishList An array of wishes
	 * @param Title $oTitle
	 * @param string $sSummary
	 * @return Status The Status object of the Article::doEdit() operation
	 */
	public function saveTitleListToTitle( $aWishList, $oTitle, $sSummary = 'WantedArticle Extension' ) {
		$oArticle = new Article( $oTitle );

		$aWikiLinks = array();
		foreach( $aWishList as $aWish ){
			if( $aWish['title'] instanceof Title == false  ) continue;
			$sLinkText = $aWish['title']->getPrefixedText();
			if( in_array( $aWish['title']->getNamespace(), array( NS_IMAGE, NS_CATEGORY ) ) ) {
				$sLinkText = ':'.$sLinkText;
			}

			$aWikiLinks[] = '*[['.$sLinkText.']]'.( !empty($aWish['signature']) ? $aWish['signature'] : ' ');
		}

		return $oArticle->doEditContent(
			ContentHandler::makeContent( implode( "\n", $aWikiLinks ), $oTitle ),
			$sSummary,
			EDIT_FORCE_BOT
		);
	}

	/**
	 *
	 * @return Article
	 */
	public function getDataSourceTemplateArticle() {
		$sDataSourceTemplateTitle = BsConfig::get('MW::WantedArticle::DataSourceTemplateTitle');
		$oDataSourceTemplateTitle = Title::makeTitle( NS_TEMPLATE, $sDataSourceTemplateTitle );
		return new Article( $oDataSourceTemplateTitle );
	}

	/**
	 * Compares MediaWiki Title objects for sorting
	 * @param Title $oT1
	 * @param Title $oT2
	 * @return bool
	 */
	public function compareTitles( $oT1, $oT2 ){
		return strcmp( $oT1->getPrefixedText(), $oT2->getPrefixedText() );
	}


	/**
	 *
	 * @param array $aWishList
	 * @param array $aTitleList
	 * @return array - Array of sorted title objects
	 */
	public function sortWishListByTitle( $aWishList, $aTitleList = array() ) {
		foreach($aWishList as $aWish) $aTitleList[] = $aWish['title'];
		usort( $aTitleList,  array( $this, 'compareTitles' ) );
		return $aTitleList;
	}



	/**
	 *
	 * @param array $aWishList
	 * @param array $aTitleList
	 * @return array - Array of title objects
	 */
	public function getDefaultTitleList( $aWishList, $aTitleList = array() ) {
		foreach( $aWishList as $aWish ) {
			$aTitleList[] = $aWish['title'];
		}

		return $aTitleList;
	}

	/**
	 * Register tag with UsageTracker extension
	 * @param array $aCollectorsConfig
	 * @return Always true to keep hook running
	 */
	public function onBSUsageTrackerRegisterCollectors( &$aCollectorsConfig ) {
		$aCollectorsConfig['bs:wantedarticleform'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-wantedarticleform'
			)
		);
		$aCollectorsConfig['bs:wantedarticles'] = array(
			'class' => 'Property',
			'config' => array(
				'identifier' => 'bs-tag-wantedarticles'
			)
		);
		return true;
	}
}
