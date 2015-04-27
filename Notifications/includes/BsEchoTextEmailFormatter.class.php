<?php
/**
 * TextEmailFormatter class for notifications
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

class BsEchoTextEmailFormatter extends EchoTextEmailFormatter {
	/**
	 * @param $emailMode EchoEmailMode
	 */
	public function __construct( EchoEmailMode $emailMode ) {
		parent::__construct( $emailMode );
		$this->emailMode->attachDecorator( new BsEchoTextEmailDecorator() );
	}

	/**
	 * Remove extra newline from a text content
	 * @param $text string
	 * @return string
	 */
	protected function removeExtraNewLine( $text ) {
		return parent::removeExtraNewLine($text);
		//return preg_replace( "/(^\s?$){1,}/s", "\r\n", $text );
	}
}