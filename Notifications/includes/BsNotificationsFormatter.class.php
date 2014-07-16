<?php
/**
 * Formatter class for notifications
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stefan Widmann <widmann@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage Notifications
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
class BsNotificationsFormatter extends EchoBasicFormatter {

	public function __construct( $params ) {
		parent::__construct( $params );
	}

	/**
	 *
	 * @param EchoEvent $event
	 * @param type $param
	 * @param Message $message
	 * @param User $user
	 */
	protected function processParam($event, $param, $message, $user) {
		if( $param === 'summary' ) {
			$aEventData = $event->getExtra();
			$message->params( $aEventData['summary'] );
		} else if( $param === 'titlelink' ) {
			$this->setTitleLink(
				$event,
				$message,
				array(
					'class' => 'mw-echo-title',
				)
			);
		} else if ( $param === 'difflink' ) {
			$aEvent = $event->getExtra();
			$diffparams = $aEvent['difflink']['diffparams'];

			$this->setDiffLink(
				$event,
				$message,
				array(
					'class' => 'mw-echo-diff',
					'param' => $diffparams,
				)
			);
		} else if( $param === 'agentlink' ) {
			if( $event->getAgent()->isAnon() ) {
				$message->params( "'''".wfMessage( 'bs-echo-anon-user' )."'''" )->parse();
			} else {
				$this->setUserpageLink(
					$event,
					$message,
					array(
						'class' => 'mw-echo-userpage'
					)
				);
			}
		} else if( $param === 'userlink') {
			$this->setUserpageLink(
					$event,
					$message,
					array(
						'class' => 'mw-echo-userpage',
						'created' => true,
					)
				);
		} else if ( $param === 'newtitle' ) {
			$aExtra = $event->getExtra();
			$oNewTitle = $aExtra['newtitle'];
			$message->params( $oNewTitle->getPrefixedText() );
		} else if ( $param === 'newtitlelink' ) {
			$aExtra = $event->getExtra();
			$oNewTitle = $aExtra['newtitle'];
			$this->buildLink(
				$oNewTitle,
				$message,
				array(
					'class' => 'mw-echo-title',
					)
			);
		} else if( $param === 'deletereason' ) {
			$aExtra = $event->getExtra();
			$message->params( $aExtra['deletereason'] );
		} else if( $param === 'shoutmsg' ) {
			$aExtra = $event->getExtra();
			$sMessage = $aExtra['shoutmsg'];
			$message->params( $sMessage );
		} else {
			parent::processParam( $event, $param, $message, $user );
		}
	}

	/**
	 * Should create a difflink for the given title
	 * @param EchoEvent $event
	 * @param Message $message
	 * @param type $props
	 */
	public function setDiffLink( $event, $message, $props = array() ) {
		$title = $event->getAgent()->getUserPage();
		$this->buildLink($title, $message, $props);
	}


	/**
	 *  Creates a link to the user page (user given by event)
	 * @param EchoEvent $event
	 * @param Message $message
	 * @param type $props
	 * @return type
	 */
	public function setUserpageLink ( $event, $message, $props = array() ) {
		if( isset( $props['created'] ) && $props['created'] ) {
			unset( $props['created'] );
			$aExtra = $event->getExtra();
			$oUser = User::newFromName( $aExtra['user'] );
			if( is_object( $oUser ) ) {
				$title = $oUser->getUserPage();
			} else {
				$title = null;
			}
		} else {
			$title = $event->getAgent()->getUserPage();
		}

		if( $title === null ) {
			$message->params( "'''".wfMessage( 'bs-echo-unknown-user' )."'''" )->parse();
		} else {
			$this->buildLink($title, $message, $props, false );
		}
	}

	/**
	 *
	 * @param Title $title
	 * @param type $message
	 * @param type $props
	 * @param type $bLinkWithPrefixedText
	 */
	public function buildLink( $title, $message, $props, $bLinkWithPrefixedText = true ) {
		$param = array();
		if ( isset( $props['param'] ) ) {
			$param = (array)$props['param'];
		}

		if ( isset( $props['fragment'] ) ) {
			$title->setFragment( '#' . $props['fragment'] );
		}

		if ( $this->outputFormat === 'html' || $this->outputFormat === 'flyout' ) {
			$class = array();
			if ( isset( $props['class'] ) ) {
				$class['class'] = $props['class'];
			}

			if ( isset( $props['linkText'] ) ) {
				$linkText = $props['linkText'];
			} else {
				if( $bLinkWithPrefixedText ) {
					$linkText = htmlspecialchars( $title->getPrefixedText() );
				} else {
					$linkText = htmlspecialchars( $title->getText() );
				}
			}

			$message->rawParams( Linker::link( $title, $linkText, $class, $param ) );
		} elseif ( $this->outputFormat === 'email' ) {
			$message->params( $title->getCanonicalURL( $param ) );
		} else {
			$message->params( $title->getFullURL( $param ) );
		}
	}
}