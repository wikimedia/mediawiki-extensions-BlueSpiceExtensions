<?php
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
			$this->setUserpageLink(
					$event,
					$message,
					array(
						'class' => 'mw-echo-userpage'
					)
			);
		}else {
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
		$title = $event->getAgent()->getUserPage();
		$this->buildLink($title, $message, $props, false);
		
	}
	
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