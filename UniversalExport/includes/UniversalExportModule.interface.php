<?php
/**
 * The interface for an UniversalExport Module.
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>

 * @package    BlueSpice_Extensions
 * @subpackage UniversalExport
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * UniversalExport Modue interface.
 * @package BlueSpice_Extensions
 * @subpackage UniversalExport
 */
interface BsUniversalExportModule {

	/*
	 * Creates a file, which can be returned in the HttpResponse
	 * @param SpecialUniversalExport $oCaller This object carries all needed information as public members
	 * @return array Associative array containing the file itself as well as the MIME-Type. I.e. array( 'mime-type' => 'text/html', 'content' => '<html>...' )
	 */
	public function createExportFile( &$oCaller );

	/**
	 * Creates a ViewExportModuleOverview to display on the SpecialUniversalExport page if no parameter is provided
	 * @return ViewExportModuleOverview
	 */
	public function getOverview();
}