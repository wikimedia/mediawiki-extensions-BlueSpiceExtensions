<?php
/**
 * Provides index progress information for ExtendedSearch
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Mathias Scheer <scheer@hallowelt.com>
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
// todo: breaks if constant BSDATADIR points to different directory in some special installation.
$sFileName = dirname( dirname( dirname( dirname( __DIR__ ) ) ) ) . '/BlueSpiceFoundation/data/index_prog.txt';

if ( file_exists( $sFileName ) ) {
	$lines = file( $sFileName );
	if ( !empty( $lines[0] ) ) {
		echo $lines[0];
	}
}
else echo "['', '', 0]";