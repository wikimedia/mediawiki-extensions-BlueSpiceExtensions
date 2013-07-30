<?php
/**
 * BlockButton extension for BlueSpice
 *
 * Adds a button to block the page to the toolbar.
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
 * @author     Stefan Widmann <widmann@hallowelt.biz>
 * @version    1.22.0
 * @version    $Id: BlockButton.class.php 7144 2012-11-07 08:13:09Z rvogel $
 * @package    BlueSpice_Extensions
 * @subpackage BlockButton
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 */

/**
 * Base class for ArticleInfo extension
 * @package BlueSpice_Extensions
 * @subpackage ArticleInfo
 */
global $wgExtraNamespaces, $wgNamespacePermissionLockdown, $wgGroupPermissions;
$wgGroupPermissions['user']['blockpage'] = true;
$wgNamespaceOffset = 10000;
if ( !defined( 'NS_SECURE' ) ) {
	define( 'NS_SECURE', $wgNamespaceOffset + 2 );
	define( 'NS_SECURE_TALK', $wgNamespaceOffset + 3 );
	global $wgExtraNamespaces;
	$wgExtraNamespaces[NS_SECURE] = 'Secure';
	$wgExtraNamespaces[NS_SECURE_TALK] = 'Secure_talk';
}

$wgNamespacePermissionLockdown[NS_SECURE]['edit'] = array('sysop');
$wgNamespacePermissionLockdown[NS_SECURE]['read'] = array('sysop');
$wgNamespacePermissionLockdown[NS_SECURE]['create'] = array('sysop');
$wgNamespacePermissionLockdown[NS_SECURE]['move'] = array('sysop');
$wgNamespacePermissionLockdown[NS_SECURE]['delete'] = array('sysop');

class BlockButton extends BsExtensionMW {

	/**
	 * Contructor of the BlockButton class
	 */
	public function __construct() {
		wfProfileIn( 'BS::'.__METHOD__ );
		//global $wgExtensionMessagesFiles;
		//$wgExtensionMessagesFiles['BlockButton'] = dirname( __FILE__ ) . '/BlockButton.i18n.php';

		$this->mExtensionFile = __FILE__;
		$this->mExtensionType = EXTTYPE::OTHER; //SPECIALPAGE/OTHER/VARIABLE/PARSERHOOK
		$this->mInfo = array(
			EXTINFO::NAME        => 'BlockButton',
			EXTINFO::DESCRIPTION => 'Adds a button to block the page to the toolbar.',
			EXTINFO::AUTHOR      => 'Stefan Widmann',
			EXTINFO::VERSION     => '1.22.0 ($Rev: 7144 $)',
			EXTINFO::STATUS      => 'beta',
			EXTINFO::URL         => 'http://www.hallowelt.biz',
			EXTINFO::DEPS        => array(
										'bluespice' => '1.22.0',
										)
		);
		$this->mExtensionKey = 'MW::BlockButton';
		wfProfileOut( 'BS::'.__METHOD__ );
	}

	/**
	 * Initialization of BlockButton extension
	 */
	public function  initExt() {
		wfProfileIn( 'BS::'.__METHOD__ );
//		BsConfig::registerVar( 'MW::BlockButton::EBlockpage', false, BsConfig::LEVEL_USER | BsConfig::TYPE_BOOL, 'bs-blockbutton-pref-EBlockpage', 'toggle' );
		$this->setHook( 'SkinTemplateTabs', 'addBlockbuttonTab' );
		$this->setHook( 'UnknownAction', 'doBlockAction');
		wfProfileOut( 'BS::'.__METHOD__ );
	}
	
	/**
	 * Add the blockpage link to the "more" menu
	 * 
	 * @global User $wgUser
	 * @global Title $wgTitle
	 * @global type $hwgBlockbuttonOverwriteExistingPages
	 * @param Skin $skin
	 * @param type $content_actions
	 * @return boolean 
	 */
	public function addBlockbuttonTab( $skin, &$content_actions ) {
		global $wgUser,$wgTitle, $hwgBlockbuttonOverwriteExistingPages;
		if ( $wgTitle->getNamespace() == NS_SECURE ) return true;
		if ( $wgTitle->getNamespace() == NS_IMAGE ) return true;
		$oTitle = Title::newFromText( $wgTitle->getText(), NS_SECURE );
		if( $oTitle->exists() && !$hwgBlockbuttonOverwriteExistingPages ) return true;
		if ( $wgTitle->getArticleID() == 0 || $wgTitle->isMainPage() ) return true;
		if ( $wgUser->isAllowed('blockpage') ) {
			$content_actions['blockbutton'] = array(
				"class" => "",
				"text" => wfMsg( 'bs-blockbutton-block-page'),
				"href" => $wgTitle->getLinkURL( array( 'action' => 'blockpage') ),
				"attributes" => ""
			);
		}
		return true;
	}
	
	/**
	 * blocks a page or shows the form to block a page
	 * 
	 * @global OutputPage $wgOut
	 * @global WebRequest $wgRequest
	 * @global User $wgUser
	 * @param type $action
	 * @param Article $oArticle
	 * @return boolean 
	 */
	public function doBlockAction( $action, $oArticle ) {
		global $wgOut, $wgRequest, $wgUser;
		if($action == 'blockpage') {
			$wgOut->setPagetitle( sprintf( wfMsg('bs-blockbutton-block-page-title'), $oArticle->getTitle()->getPrefixedText() ) );
			$wgOut->setRobotpolicy( 'noindex,nofollow' );
			$confirm = $wgRequest->wasPosted() && $wgUser->matchEditToken( $wgRequest->getVal( 'wpEditToken' ) );
			if( $oArticle->getTitle()->getNamespace() == NS_SECURE) {
				$wgOut->addHTML('<p>'.wfMsg('bs-blockbutton-error-secure').'</p>');
				return false;
			}
			if(!$confirm) {
				$this->showConfirm( $oArticle );
			}
			else {
				$this->blockPage( $oArticle );
			}
		}
		return false;
	}
	
	/**
	 * Renders the form on a page with the action "blockpage"
	 * 
	 * @global OutputPage $wgOut
	 * @global User $wgUser
	 * @global WebRequest $wgRequest
	 * @param Article $oArticle 
	 */
	function showConfirm( $oArticle ) {
		global $wgOut, $wgUser, $wgRequest;
		$align = 'center';
		$list = wfMsg('bs-blockbutton-reason-unreleased').'
	'.wfMsg('bs-blockbutton-reason-copyright').'
	'.wfMsg('bs-blockbutton-reason-legislative').'
	'.wfMsg('bs-blockbutton-reason-confidential');
		$form = Xml::openElement( 'form', array( 'method' => 'post', 'action' => $oArticle->getTitle()->getLocalURL( 'action=blockpage' ), 'id' => 'blockpageconfirm' ) ) .
			Xml::openElement( 'fieldset', array( 'id' => 'mw-block-table' ) ) .
			Xml::tags( 'legend', null, wfMsg('bs-blockbutton-confirm-block-page') ) .
			Xml::openElement( 'table' ) .
			"<tr id=\"wpBlockReasonListRow\">
					<td align='$align'>" .
			Xml::label( wfMsg('bs-blockbutton-reason-block-page'), 'wpBlockReasonList' ) .
			"</td>
					<td>" .
			Xml::listDropDown( 'wpBlockReasonList',
			$list,
			wfMsg('bs-blockbutton-reason-choice'), $wgRequest->getVal('wpBlockReasonList'), 'wpReasonDropDown', 1 ) .
			"</td>
				</tr>
				<tr id=\"wpBlockReasonRow\">
					<td align='$align'>" .
			Xml::label( wfMsg('bs-blockbutton-reason-specify'), 'wpReasonSpecify' ) .
			"</td>
					<td>" .
			Xml::input( 'wpReasonSpecify', 60,  $wgRequest->getVal('wpReasonSpecify'), array( 'type' => 'text', 'maxlength' => '255', 'tabindex' => '2', 'id' => 'wpReasonpecify' ) ) .
			"</td>
				</tr>
				<tr>
					<td></td>
					<td>" .
			Xml::submitButton( wfMsg('bs-blockbutton-confirm-block-action'), array( 'name' => 'wpConfirmB', 'id' => 'wpConfirmB', 'tabindex' => '4' ) ) .
			"</td>
				</tr>" .
			Xml::closeElement( 'table' ) .
			Xml::closeElement( 'fieldset' ) .
			Html::hidden( 'wpEditToken', $wgUser->editToken() ) .
			Xml::closeElement( 'form' );

		$wgOut->addHTML( $form );
	}
	
	/**
	 * Actually the function makes a new article in the secure namespace and prepares a new text
	 * for the article in the original namespace
	 * 
	 * @global OutputPage $wgOut
	 * @global WebRequest $wgRequest
	 * @global type $hwgBlockbuttonOverwriteExistingPages
	 * @param Article $oArticle
	 * @return type 
	 */
	function blockPage( $oArticle ) {
		global $wgOut, $wgRequest, $hwgBlockbuttonOverwriteExistingPages;

		$reason = $wgRequest->getVal( 'wpBlockReasonList' );
		$specify = $wgRequest->getVal( 'wpReasonSpecify' );

		$error = false;
		if( !$reason || $reason == 'other' ) {
			$wgOut->addHTML( '<p>'.wfMsg( 'bs-blockbutton-error-reason' ).'</p>' );
			$error = true;
		}
		if( trim( $specify ) == '' ) {
			$wgOut->addHTML('<p>'.wfMsg('bs-blockbutton-error-specify').'</p>');
			$error = true;
		}
		if( $error ) {
			return $this->showConfirm( $oArticle );
		}
		$orgTitleText = $oArticle->getTitle()->getText();
		$orgNamespace = $oArticle->getTitle()->getNamespace();
		if( $orgNamespace == NS_IMAGE || $orgNamespace == NS_MEDIA ) {
			$wgOut->addHTML('<p>'.wfMsg( 'bs-blockbutton-error-image' ).'</p>');
			return;
		} else {
			$newTitle = Title::makeTitle( NS_SECURE, $oArticle->getTitle()->getText() );
			if ( $hwgBlockbuttonOverwriteExistingPages && !$oArticle->getTitle()->isValidMoveTarget( $newTitle ) ) {
				$newTitle = Title::makeTitle( NS_SECURE, $oArticle->getTitle()->getText().'_'.date( "YmdHis" ) );
			}
			$res = $oArticle->getTitle()->moveTo( $newTitle, false, $reason.': '.$specify, true );
			if( is_array( $res ) && !$hwgBlockbuttonOverwriteExistingPages ) {
				foreach( $res as $msg ) {
					if( $msg[0] == 'articleexists' ) {
						$wgOut->addHTML( '<p>'.wfMsg('bs-blockbutton-error-secure').'</p>' );
						return;
					}
				}
				foreach( $res as $msg ) {
					$wgOut->addWikiMsg( $msg[0]);
				}
			}
			else {
				$oldTitle = Title::makeTitle( $orgNamespace, $orgTitleText );
				$oldArticle = new Article( $oldTitle );
				$oldArticle->doEdit(
					wfMsg('bs-blockbutton-block-msg'),
					'',
					EDIT_SUPPRESS_RC | EDIT_FORCE_BOT
				);
				/*$dbw = wfGetDB( DB_MASTER );
				$table = $dbw->tableName('searchindex');
				$res = $dbw->query("SELECT MAX(si_page) as id FROM {$table}");
				if($res) {
					$row = $res->fetchObject();
					$dbw->query("DELETE FROM {$table} WHERE si_page = {$row->id}");
				}*/
				$wgOut->addHTML( '<p>'.wfMsg( 'bs-blockbutton-block-page-success' ).'</p>' );
				$this->sendInfoMail( $oArticle, $orgTitleText, $reason.': '.$specify );
			}
		}
	}
	/**
	 *
	 * @global array $wgGroupPermissions
	 * @global type $wgPasswordSender
	 * @global User $wgUser
	 * @param Article $oArticle
	 * @param Title $title
	 * @param type $reason 
	 */
	function sendInfoMail( $oArticle, $title, $reason ) {
		global $wgGroupPermissions, $wgPasswordSender,$wgUser;

		$groups = array();

		foreach( $wgGroupPermissions as $group => $perms ) {
			if( isset( $perms['blockinform'] ) && $perms['blockinform'] ) {
				$groups[] = $group;
			}
		}

		$set = "'".join( "','", $groups )."'";
		$dbr = wfGetDB( DB_SLAVE );
		$tableU = $dbr->tableName( 'user' );
		$tableUG = $dbr->tableName( 'user_groups' );
		$res = $dbr->query( "SELECT u.*
							FROM {$tableU} u,  {$tableUG} g
							WHERE g.ug_user = u.user_id
							AND g.ug_group IN ({$set})
							GROUP BY u.user_id"
		);
		$userArray = UserArray::newFromResult( $res );
		$time = date( "Y-m-d H:i:s" );
		foreach( $userArray as $user ) {
			if( $user->isEmailConfirmed() ) {
				$address = $user->getEmail();
				$name = $user->getRealName();

				$msg = sprintf( wfMsg('bs-blockbutton-blockinform_body' ),
					$name,
					$wgUser->getName(),
					$time,
					$title,
					$reason
				);
				$this->sendMail( $address,
					wfMsg( 'bs-blockbutton-blockinform-subject' ),
					$msg,
					$wgPasswordSender
				);
			}
		}
	}
	
	public function sendMail( $to, $subject, $message, $from ) {
		global $wgPasswordSender;
		if (!$from) $from = $wgPasswordSender;
		//TODO: utf8_decode is not a good way for exotic languages...
		$sent = @mail($to, utf8_decode($subject), utf8_decode($message), 'From:'.$from);
		return $sent;
	}
}