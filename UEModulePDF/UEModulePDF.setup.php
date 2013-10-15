<?php

BsExtensionManager::registerExtension('UEModulePDF', BsRUNLEVEL::FULL|BsRUNLEVEL::REMOTE, BsACTION::LOAD_SPECIALPAGE);

$wgExtensionMessagesFiles['UEModulePDF'] = __DIR__ . '/UEModulePDF.i18n.php';

$wgAutoloadClasses['BsPDFPageProvider'] = __DIR__ . '/lib/PDFPageProvider.class.php';
$wgAutoloadClasses['BsPDFTemplateProvider'] = __DIR__ . '/lib/PDFTemplateProvider.class.php';
$wgAutoloadClasses['BsPDFWebService'] = __DIR__ . '/lib/PDFWebService.class.php';
$wgAutoloadClasses['BsPDFServlet'] = __DIR__ . '/lib/PDFServlet.class.php';
$wgAutoloadClasses['BsExportModulePDF'] = __DIR__ . '/lib/ExportModulePDF.class.php';
