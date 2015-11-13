<?php

/**
 * ResponsibleEditors extension for BlueSpice
 *
 * Enables MediaWiki to manage responsible editors for articles.
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
 * @author     Sebastian Ulbricht <o0lilu0o1980@gmail.com>
 * @version    2.23.1
 * @package    BlueSpice_Extensions
 * @subpackage ResponsibleEditors
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

class ResponsibleEditorFormatter extends EchoBasicFormatter {

	/**
	 * @param EchoEvent $event
	 * @param $param
	 * @param $message Message
	 * @param $user User
	 */
	protected function processParam( $event, $param, $message, $user ) {
		if ( $param === 'newtitle' ) {
			$titles = $event->getExtraParam('titles', array(0 => false, 1 => false));
			$title = $titles[1];
			if ( !$title ) {
				$message->params( $this->getMessage( 'echo-no-title' )->text() );
			} else {
				if ( $this->outputFormat === 'htmlemail' ) {
					$props = array (
						'attribs' => array( 'style' => $this->getHTMLLinkStyle() )
					);
					$this->setNewTitleLink( $event, $message, $props );
				} else {
					$message->params( $this->formatTitle( $title ) );
				}
			}
		} elseif ( $param === 'newtitlelink' ) {
			$this->setNewTitleLink( $event, $message );
		} else {
			parent::processParam( $event, $param, $message, $user );
		}
	}

	/**
	 * Generate links based on output format and passed properties
	 * @param $event EchoEvent
	 * @param $message Message
	 * @param $props array
	 */
	protected function setNewTitleLink( $event, $message, $props = array() ) {
		$titles = $event->getExtraParam('titles', array(0 => false, 1 => false));
		$title = $titles[1];
		if ( !$title ) {
			$message->params( $this->getMessage( 'echo-no-title' )->text() );
			return;
		}

		if ( !isset( $props['fragment'] ) ) {
			$props['fragment'] = $this->formatSubjectAnchor( $event );
		}

		$link = $this->buildLinkParam( $title, $props );
		$message->params( $link );
	}
}