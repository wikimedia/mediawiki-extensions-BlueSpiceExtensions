<?php

BsExtensionManager::registerExtension('VisualEditor',                    BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE);

$wgExtensionMessagesFiles['VisualEditor']      = dirname( __FILE__ ) . '/VisualEditor.i18n.php';
$wgExtensionMessagesFiles['VisualEditorMagic'] = dirname( __FILE__ ) . '/VisualEditor.i18n.magic.php';