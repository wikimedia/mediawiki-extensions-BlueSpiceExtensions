<?php

BsExtensionManager::registerExtension('HideTitle',                       BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['HideTitle'] = __DIR__ . '/HideTitle.i18n.php';
$wgExtensionMessagesFiles['HideTitleMagic'] = __DIR__ . '/HideTitle.i18n.magic.php';
