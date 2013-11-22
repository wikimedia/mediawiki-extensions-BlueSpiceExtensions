<?php
/**
 * Emoticons extension for BlueSpice
 *
 * Renders emoticons in a text as images.
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
 * @author     Alex Wollangk
 * @author     Marc Reymann <reymann@hallowelt.biz>
 * @author     Sebastian Ulbricht
 * @author     Mathias Scheer <scheer@hallowelt.biz>
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    2.22.0

 * @package    BlueSpice_Extensions
 * @subpackage Emoticons
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.20.0
 * - Added support for MediaWiki message system
 * - Removed LoadExtensionSchemaUpdates hook / code
 * - Moved Mapping to Emoticons.i18n.php
 * v1.1.0
 * - Added support for Memcached Server
 * - Removed configuration setting for mapping source article
 * - Mapping source article now is added with update.php
 * v1.0.1
 * - Added input validation on MappingSourceArticle
 * v1.0.0
 * - Works fine, no changes.
 * v0.2.0
 * - Added views
 * - Code refactored
 * v0.1.0
 * FIRST CHANGES
 */

// Last review MRG (30.06.11 10:34)

/**
 * Base class for Emoticons extension
 *  Created by Alex Wollangk (alex@wollangk.com)
 *  Considerably mangled by Marc Reymann
 * @package BlueSpice_Extensions
 * @subpackage Emoticons
 */
class Emoticons extends BsExtensionMW {

	/**
	 * Contructor of the Authors class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['Emoticons'] = __DIR__ . '/Emoticons.i18n.php';

		// Base settings
		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::PARSERHOOK;
		$this->mInfo = array(
			EXTINFO::NAME        => 'Emoticons',
			EXTINFO::DESCRIPTION => 'Renders emoticons in a text as images.',
			EXTINFO::AUTHOR      => 'Alex Wollangk, Marc Reymann, Sebastian Ulbricht, Mathias Scheer, Robert Vogel, Patric Wirth',
			EXTINFO::VERSION     => 'default',
			EXTINFO::STATUS      => 'default',
			EXTINFO::PACKAGE     => 'default',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array('bluespice' => '2.22.0')
		);
		$this->mExtensionKey = 'MW::Emoticons';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of Authors extension
	 */
	protected function initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
		$this->setHook( 'OutputPageBeforeHTML' );
		$this->setHook( 'ArticleSave' );

		BsConfig::registerVar( 'MW::Emoticons::PathToEmoticons', '/extensions/BlueSpiceExtensions/Emoticons/emoticons', BsConfig::LEVEL_PRIVATE|BsConfig::TYPE_STRING, 'bs-emoticons-pref-PathToEmoticons' );
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Hook-Handler for 'OutputPageBeforeHTML' (MediaWiki). Replaces Emoticon syntax with images.
	 * @param ParserOutput $oParserOutput The ParserOutput object that corresponds to the page.
	 * @param string $sText The text that will be displayed in HTML.
	 * @global MWMemcached $wgMemc The MediaWiki Memcached object
	 * @return bool Always true to keep hook running.
	 */
	public function onOutputPageBeforeHTML( &$oParserOutput, &$sText) {
		global $wgMemc; //http://www.mediawiki.org/wiki/Memcached

		$sCurrentAction = $this->getRequest()->getVal( 'action', 'view' );
		$oCurrentTitle  = $this->getTitle();

		if( in_array( $sCurrentAction, array('edit', 'history', 'delete', 'watch') ) ) return true;
		if( in_array( $oCurrentTitle->getNamespace(), array( NS_SPECIAL, NS_MEDIAWIKI ) ) ) return true;

		wfProfileIn( 'BS::'.__METHOD__ );
		$sKey = wfMemcKey( 'BlueSpice', 'Emoticons' );
		$aMapping = $wgMemc->get( $sKey );
		
		if( $aMapping == false ) {

			$sPathToEmoticons = BsConfig::get('MW::ScriptPath').BsConfig::get('MW::Emoticons::PathToEmoticons');

			// Get the list of emoticons from the message system.
			$sMappingContent = wfMsg('bs-emoticons-mapping');
			if( empty( $sMappingContent ) ) return true; // If the content successfully loaded, do the replacement

			$aMappingLines = explode( "\n", $sMappingContent );
			$aEmoticons         = array();
			$aImageReplacements = array();

			foreach( $aMappingLines as $sLine ) {
				$sLine = trim( $sLine ); //Remove leading space
				if( empty( $sLine ) )  continue; //Empty line?
				if( $sLine{0} == '#' ) continue; //Comment line?

				$aEmoticonHash = preg_split( '/ +/', $sLine ); // $aEmoticonHash = array('smile.png', ':-)', ':)');
				if( count($aEmoticonHash) > 1 ) {
					$sImageName  = array_shift( $aEmoticonHash ); // first element is image name, here 'smile.png'
					$oEmoticonImageView = new ViewBaseElement();
					$oEmoticonImageView->setTemplate( ' <img border="0" src="'.$sPathToEmoticons.'/{FILENAME}" alt="emoticon" />' );
					$oEmoticonImageView->addData( array( 'FILENAME' => $sImageName ) );
					foreach ($aEmoticonHash as $sEmote) {
						$aEmoticons[] = ' '.$sEmote;
						$aEmoticons[] = '&nbsp;'.$sEmote;
						$aEmoticons[] = '&#160;'.$sEmote;
						// (TL., 25.02.2011) das brauchen wir wirklich 3 mal, weil auch aEmoticons[] 3 mal gefüllt wird!
						$aImageReplacements[] = $oEmoticonImageView->execute();
						$aImageReplacements[] = $oEmoticonImageView->execute();
						$aImageReplacements[] = $oEmoticonImageView->execute();
					}
				}
			}
			
			$aMapping = array('emoticons' => $aEmoticons, 'replacements' => $aImageReplacements );
			$wgMemc->set( $sKey, $aMapping );
		}

		$sText = str_replace( $aMapping['emoticons'], $aMapping['replacements'], $sText );
		wfProfileOut( 'BS::'.__METHOD__ );
		return true;
	}

	/**
	 * Hook-Handler for 'ArticleSave' (MediaWiki). Validates provided mapping syntax for Emoticons.
	 * @param Article $oArticle The article object being saved
	 * @param User $oUser The user object saving the article
	 * @param string $sText The new article text
	 * @param string $sSummary The article summary (comment)
	 * @param bool $bIsMinor Minor flag
	 * @param bool $bIsWatch Watch flag
	 * @param int $iSection Number of edited section
	 * @param int &$iFlags
	 * @param Status $oStatus The Status object
	 * @global MWMemcached $wgMemc The MediaWiki Memcached object
	 * @return mixed Boolean true if syntax is okay or the saved article is not the MappingSourceArticle, String 'error-msg' if an error occurs.
	 */
	public function onArticleSave( $oArticle, $oUser, $sText, $sSummary, $bIsMinor, $bIsWatch, $iSection, &$iFlags, $oStatus ) {
		
		//TODO: error view does not work
		
		global $wgMemc;
		$oMappingSourceTitle = Title::newFromText( 'bs-emoticons-mapping', NS_MEDIAWIKI );
		if( !$oMappingSourceTitle->equals( $oArticle->getTitle() ) ) return true;
		
		$aLines = explode( "\n" , $sText );
		
		foreach( $aLines as $iLineNumber => $sLine ) {
			$iLineNumber++;
			$sLine = trim( $sLine ); //Remove leading space
			if( empty( $sLine ) )  continue; //Empty line?
			if( $sLine{0} == '#' ) continue; //Comment line?

			$aEmoticonHash = preg_split( '/ +/', $sLine );

			$oErrorView = new ViewErrorMessage();
			if( !isset( $aEmoticonHash[1] ) ) {
				$oErrorView->addData(
					array( wfMsg( 'bs-emoticons-error-validation-missing-symbol', $iLineNumber, $aEmoticonHash[0] ) )
					);
				
				return $oErrorView->execute();
			}
			if( preg_match('#^.*?\.(jpg|jpeg|gif|png)$#si', $aEmoticonHash[0] ) === 0 ) {
				//$oStatus->fatal ( 'edit-no-change' );
				$oErrorView->addData(
					array( wfMsg( 'bs-emoticons-error-validation-imagename', $iLineNumber, $aEmoticonHash[0] ) )
				);
				
				return $oErrorView->execute();
			}

			foreach( $aEmoticonHash as $sPart ) {
				if( $sPart == $aEmoticonHash[0] ) continue; //Skip imagename
				$iSymbolLength = strlen( $sPart );
				if( $iSymbolLength < 2 || $iSymbolLength > 10 ) {
					$oErrorView->addData(
						array( wfMsg( 'bs-emoticons-error-validation-symbol', $iLineNumber, $sPart ) )
						);
					return $oErrorView->execute();
				}
			}
		}
		
		$sKey = wfMemcKey( 'BlueSpice', 'Emoticons' );
		$wgMemc->delete( $sKey );
		return true;
	}
}