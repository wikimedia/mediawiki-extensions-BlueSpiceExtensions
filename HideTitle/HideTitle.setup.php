<?php

BsExtensionManager::registerExtension('HideTitle',                       BsRUNLEVEL::FULL);

$wgExtensionMessagesFiles['HideTitle'] = dirname(__FILE__) . '/HideTitle.i18n.php';
$wgExtensionMessagesFiles['HideTitleMagic'] = dirname(__FILE__) . '/HideTitle.i18n.magic.php';