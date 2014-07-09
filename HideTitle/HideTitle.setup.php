<?php

BsExtensionManager::registerExtension('HideTitle', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgMessagesDirs['HideTitle'] = __DIR__ . '/i18n';

$wgExtensionMessagesFiles['HideTitle'] = __DIR__ . '/languages/HideTitle.i18n.php';
$wgExtensionMessagesFiles['HideTitleMagic'] = __DIR__ . '/languages/HideTitle.i18n.magic.php';