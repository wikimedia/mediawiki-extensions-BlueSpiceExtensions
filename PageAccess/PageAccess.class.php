<?php

/**
 * BlueSpice for MediaWiki
 * Extension: PageAccess
 * Description: Controls access on page level.
 * Authors: Marc Reymann
 *
 * Copyright (C) 2010 Hallo Welt! – Medienwerkstatt GmbH, All rights reserved.
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
 * http://www.gnu.org/copyleft/gpl.html
 *
 * For further information visit http://www.blue-spice.org
 *
 * Version information
 * $LastChangedDate: 2013-06-21 15:41:20 +0200 (Fr, 21 Jun 2013) $
 * $LastChangedBy: mreymann $
 * $Rev: 9848 $

 */
/* Changelog
 * v1.20.0
 * - initial release
 */

/**
 * PageAccess adds a tag, used in WikiMarkup as follows:
 * Grant exclusive access to group "sysop": <bs:pageaccess groups="sysop" />
 * Separate multiple groups by commas.
 */
class PageAccess extends BsExtensionMW {

	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::PARSERHOOK; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME => 'PageAccess',
			EXTINFO::DESCRIPTION => wfMessage( 'bs-pageaccess-desc' )->escaped(),
			EXTINFO::AUTHOR => 'Marc Reymann',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL => 'http://www.hallowelt.biz',
			EXTINFO::DEPS => array( 'bluespice' => '2.22.0' )
		);
		$this->mExtensionKey = 'MW::PageAccess';

		wfProfileOut( 'BS::'.__METHOD__ );
	}

	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'ArticleSave' );
		$this->setHook( 'ParserFirstCallInit' );
		$this->setHook( 'userCan' );
		$this->setHook( 'BSInsertMagicAjaxGetData', 'onBSInsertMagicAjaxGetData' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	public function onArticleSave( &$article, &$user, &$text, &$summary, $minor, $watchthis, $sectionanchor, &$flags, &$status ) {
		# Prevent user from locking himself out of his own page
		$oEditInfo = $article->prepareTextForEdit( $text, null, $user ); 
		$sAccessGroups = $oEditInfo->output->getProperty( 'bs-page-access' );
		if ( !$this->checkAccessGroups( $user, $sAccessGroups ) ) {
			$err[0] = 'bs-pageaccess-error-not-member-of-given-groups';
			throw new PermissionsError( 'edit', array( $err ) ); # since MW 1.18
			return false;
		}

		# Also check if user includes forbidden templates
		$aTemplateTitles = $this->getTemplateTitles( $text );
		foreach ( $aTemplateTitles as $oTemplateTitle ) {
			if ( !$this->isUserAllowed( $oTemplateTitle, $user ) ) {
				$err[0] = 'bs-pageaccess-error-included-forbidden-template';
				$err[1] = $oTemplateTitle->getText();
				throw new PermissionsError( 'edit', array( $err ) ); # since MW 1.18
				return false;
			}
		}

		# All seems good. Let user save.
		return true;
	}

	/**
	 * Returns an array of title objects that are used as templates in the given Wikitext.
	 * @param string $sWikitext Wiki markup
	 * @return array Title objects
	 */
	public function getTemplateTitles( $sWikitext ) {
		$sRegex = '|{{:(.*?)}}|'; # not very sophisticated but only used for lockout prevention
		preg_match_all( $sRegex, $sWikitext, $aMatches );
		$aTemplateTitles = array();
		foreach ( $aMatches[1] as $sTemplateTitleText ) {
			$oTmpTitle = Title::newFromText( $sTemplateTitleText );
			if ( !is_null( $oTmpTitle ) ) $aTemplateTitles[] = $oTmpTitle;
		}
		return $aTemplateTitles;
	}

	/**
	 * Checks if user is in one of the given user groups
	 * @param object $oUser the current user
	 * @param string $sAccessGroups a comma separated list of user groups
	 * @return bool
	 */
	public function checkAccessGroups( $oUser, $sAccessGroups) {
		if ( !$sAccessGroups ) return true;
		$aAccessGroups = array_map("trim", explode( ',', $sAccessGroups ) );
		wfRunHooks( 'BSPageAccessAddAdditionalAccessGroups', array( &$aAccessGroups ) );
		$aUserGroups = $oUser->getEffectiveGroups();
		return (bool) array_intersect( $aAccessGroups, $aUserGroups );
	}

	private static $aAllowedPairs = array(); // <page_id>-<user_id>

	/**
	 * Checks if user is allowed to view page
	 * @param Title $oPage title or article object
	 * @param User $oUser the current user
	 * @return bool
	 */
	public function isUserAllowed( $oPage, $oUser ) {
		$oPage = ( $oPage instanceof Article ) ? $oPage->getTitle() : $oPage;
		$sPair = $oPage->getArticleId().'-'.$oUser->getId();
		if( isset( self::$aAllowedPairs[$sPair] ) ) return self::$aAllowedPairs[$sPair];

		$dbr = wfGetDB( DB_SLAVE );
		$bHasAccess = true;
		$aAllTitles = $oPage->getTemplateLinksFrom();
		$aAllTitles[] = $oPage;
		foreach ( $aAllTitles as $oTitleToCheck ) {
			$sAccessGroups = $dbr->selectField( 'page_props', 'pp_value',
					array( 'pp_page' => $oTitleToCheck->getArticleID(), 'pp_propname' => 'bs-page-access' ), __METHOD__ );
			if ( !$this->checkAccessGroups( $oUser, $sAccessGroups ) ) $bHasAccess = false;
		}
		self::$aAllowedPairs[$sPair] = $bHasAccess;
		return $bHasAccess;
	}

	public function onParserFirstCallInit( &$parser ) {
		$parser->setHook( 'bs:pageaccess', array( &$this, 'onTagBsPageAccess' ) );
		return true;
	}

	public function onUserCan( $title, $user, $action, &$result ) {
		// TODO MRG: Is this list really exhaustive enough?
		if( !in_array($action, array('read', 'edit', 'delete', 'move')) ) return true;
		if ( $this->isUserAllowed( $title, $user ) ) return true;
		$result = false;
		return false;
	}

	/**
	 *
	 * @param type $input
	 * @param string $args
	 * @param Parser $parser
	 * @return string
	 */
	public function onTagBsPageAccess( $input, $args, $parser ) {
		//ignore access tag on mainpage or it will break all ajax calls without title param
		if( $parser->getTitle()->equals( Title::newMainPage() ) === true ) return '';

		$parser->disableCache();

		if ( !isset( $args['groups'] ) ) {
			$oErrorView = new ViewTagError( wfMessage( 'bs-pageaccess-error-no-groups-given' )->escaped() );
			return $oErrorView->execute();
		}

		$sOldAccessGroups = $parser->getOutput()->getProperty( 'bs-page-access' );
		if ( $sOldAccessGroups ) $args['groups'] = $sOldAccessGroups . "," . $args['groups'];
		$parser->getOutput()->setProperty( 'bs-page-access', $args['groups'] );
		return '';
	}

	public function onBSInsertMagicAjaxGetData( &$oResponse, $type ) {
		if( $type != 'tags' ) return true;

		$oResponse->result[] = array(
			'id'   => 'bs:pageaccess',
			'type' => 'tag',
			'name' => 'pageaccess',
			'desc' => wfMessage( 'bs-pageaccess-tag-groups-desc' )->plain(),
			'code' => '<bs:pageaccess groups="" />',
		);

		return true;
	}
}
