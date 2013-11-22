<?php

BsExtensionManager::registerExtension('UEModulePDF', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['UEModulePDF'] = __DIR__ . '/UEModulePDF.i18n.php';

$wgAutoloadClasses['BsPDFPageProvider']     = __DIR__ . '/includes/PDFPageProvider.class.php';
$wgAutoloadClasses['BsPDFTemplateProvider'] = __DIR__ . '/includes/PDFTemplateProvider.class.php';
$wgAutoloadClasses['BsPDFWebService']       = __DIR__ . '/includes/PDFWebService.class.php';
$wgAutoloadClasses['BsPDFServlet']          = __DIR__ . '/includes/PDFServlet.class.php'; //TODO: This is deprecated. Remove.
$wgAutoloadClasses['BsExportModulePDF']     = __DIR__ . '/includes/ExportModulePDF.class.php';