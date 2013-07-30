<?php
// Last review MRG (01.07.11 14:22)
class SpecialRSSFeeder extends BsSpecialPage {

	public function __construct() {
		parent::__construct( 'RSSFeeder' );
	}

	/**
	 *
	 * @global OutputPage $wgOut
	 * @param type $sParameter
	 * @return type 
	 */
	public function execute( $sParameter ) {
		parent::execute( $sParameter );
		global $wgOut;
		$extension = false;

		if ($sParameter) {
			$sParameter = $this->parseParams($sParameter);
		} else {
			$sParameter = array(
				'Page' => BsCore::getParam('Page', '', BsPARAM::REQUEST|BsPARAMTYPE::STRING)
			);
		}
		if (isset($sParameter['Page'])) {
			$extension = $sParameter['Page'];
		}
		$rssFeeds = RSSFeeder::getRegisteredFeeds();
		if ($extension && is_array($rssFeeds[$extension])) {
			$wgOut->disable();
			$runner = $rssFeeds[$extension]['method'];
			header( 'Content-Type: application/xml; charset=UTF-8' );
			echo $rssFeeds[$extension]['object']->$runner($sParameter);
			return;
		}

		BsExtensionManager::setContext('MW::RSSFeeder::loadScripts');
		$wgOut->setPageTitle('RSS');

		$form = new ViewBaseForm();
		$form->setId('RSSFeederForm');
		$form->setValidationUrl('index.php?&action=remote&mod=RSSFeeder&rf=validate');

		$label = new ViewFormElementLabel();
		$label->useAutoWidth();
		$label->setText( wfMsg( 'bs-rssstandards-description_rss' ));

		$form->addItem($label);

		foreach ($rssFeeds as $name => $feed) {
			$func = $feed['buildLinks'];
			$form->addItem($feed['object']->$func());
		}

		$wgOut->addHTML(
				'<script type="text/javascript">var page_label = "'.wfMsg( 'bs-rssfeeder-page_label' ).'";</script>'.
				$form->execute()
		);
	}

	protected function parseParams($sParameter) {
		$aParameters = array();
		$aTokens = explode('/', $sParameter);
		foreach ($aTokens as $vKeyValuePairs) {
			$vKeyValuePairs = explode(':', $vKeyValuePairs);
			$aParameters[$vKeyValuePairs[0]] = $vKeyValuePairs[1];
		}
		return $aParameters;
	}

	/*public function testRSS() {
		$oChannel = RSSCreator::createChannel('Testchannel', 'http://localhost/rss', 'Dies ist ein TestChannel in RSS');
		$oChannel->setImage('http://upload.wikimedia.org/wikipedia/mediawiki/b/bc/Wiki.png', 'MediaWiki', 'http://upload.wikimedia.org/wikipedia/mediawiki/b/bc/Wiki.png');
		$item = RSSItemCreator::createItem('TestItem', 'http://localhost', 'dies ist ein TestItem.');
		$oChannel->addItem($item);
		return $oChannel->buildOutput();
	}*/

}