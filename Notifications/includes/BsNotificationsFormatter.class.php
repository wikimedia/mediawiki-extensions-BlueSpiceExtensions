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
			$aEventData = $event->getExtra();
			$this->setTitleLink(
				$event,
				$message,
				array(
					'class' => 'mw-echo-title',
				)
			);
		} else if ( $param === 'difflink' ) {
			$aEventData = $event->getExtra();

			$this->setTitleLink(
				$event,
				$message,
				array(
					'class' => 'mw-echo-diff',
				)
			);
		} else {
			parent::processParam( $event, $param, $message, $user );
		}
	}
}