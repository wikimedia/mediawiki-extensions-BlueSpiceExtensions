<?php
/**
 * Text Email Decorator class for notifications
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @package    BlueSpice_Extensions
 * @subpackage Notifications
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Text email decorator
 */
class BsEchoTextEmailDecorator extends EchoTextEmailDecorator {

	/**
	 * Adds a user based greeting to the text mail
	 * @param Message $message
	 * @param User $oUser
	 * @return String
	 */
	public function userBasedDecorateIntro( $message, $oUser ) {
		$sRealname = BsCore::getUserDisplayName( $oUser );
		$sReturn = wfMessage( 'bs-email-greeting-receiver' )
			->params( $oUser->getName(), $sRealname )
			->inLanguage( $oUser->getOption( 'language' ) )
			->text()
		;
		return $sReturn."\n\n".$message->text();
	}

	/**
	 * Adds the BS default footer to the text mail
	 * @global String $wgSitename
	 * @param type $address
	 * @param User $user
	 * @return String
	 */
	public function decorateFooter( $address, $user ) {
		global $wgSitename;

		$sFooter = parent::decorateFooter( $address, $user );
		return $sFooter.
			"\n---------------------\n\n"
			.wfMessage( 'bs-email-footer', $wgSitename )->text()
			."\n\n---------------------"
		;
	}
}