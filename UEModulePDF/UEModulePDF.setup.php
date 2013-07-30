<?php

BsExtensionManager::registerExtension('UEModulePDF', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['UEModulePDF'] = dirname( __FILE__ ) . '/UEModulePDF.i18n.php';