<?php
BsExtensionManager::registerExtension('FormattingHelp', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['FormattingHelp'] = dirname( __FILE__ ) . '/FormattingHelp.i18n.php';
