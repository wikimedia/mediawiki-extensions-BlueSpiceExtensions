<?php

/**
 * Review Extension for BlueSpice
 *
 * Adds workflow functionality to pages.
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
 * @author     Sebastian Ulbricht <o0lilu0o1980@gmail.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage Review
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
class ReviewFormatter extends EchoBasicFormatter {

	/**
	 * @param string    $payload
	 * @param EchoEvent $event
	 * @param User      $user
	 *
	 * @return string
	 */
	protected function formatPayload( $payload, $event, $user ) {
		switch ( $payload ) {
			case 'comment':
				if ( $sComment = $event->getExtraParam( 'comment' ) ) {
					return $this->getMessage( 'notification-bs-review-payload-comment' )
						->params( $sComment )
						->text();
				}

				$iCurrentUserId = $user->getId();
				$aNextUsers = $event->getExtraParam( 'next-users' );

				if ( !is_array( $aNextUsers ) ) {
					return '';
				}

				foreach ( $aNextUsers as $aUser ) {
					if ( $aUser[ 'id' ] == $iCurrentUserId ) {
						return $this->getMessage( 'notification-bs-review-payload-comment' )
							->params( $aUser[ 'comment' ] )
							->text();
					}
				}

				return '';
				break;
			default:
				return parent::formatPayload( $payload, $event, $user );
				break;
		}
	}

}