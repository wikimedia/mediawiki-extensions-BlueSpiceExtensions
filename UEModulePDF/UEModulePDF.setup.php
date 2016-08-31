<?php
wfLoadExtension( 'BlueSpiceExtensions/UEModulePDF' );

/**
 * Allows modification for CURL request. E.g. setting an CA file for HTTPS
 */
$bsgUEModulePDFCURLOptions = array();

/**
 * This value is considered when asseta are being uploaded to the PDF service
 */
$bsgUEModulePDFUploadThreshold = 50 * 1024 * 1024;

// Remove if minimal system requirements of MW changes to PHP <= 5.5
if( !defined( 'CURLOPT_SAFE_UPLOAD' ) ) {
	define( 'CURLOPT_SAFE_UPLOAD', -1 );
}