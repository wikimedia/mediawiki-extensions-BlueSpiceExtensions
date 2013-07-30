/**
 * FormattingHelp extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: PageTemplatesAdmin.js 1799 2011-04-29 13:39:02Z mglaser $
 * @package    Bluespice_Extensions
 * @subpackage FormattingHelp
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for all FormattingHelp related methods and properties
 */

FormattingHelp = {
	/**
	 * Is infopane already loaded per ajax?
	 * @var bool loaded or not
	 */
	loaded: false,
	/**
	 * Slides infopane in and out
	 */
	toggle: function() {
		if ($('#bs-editinfo-pane').css( 'display' ) == 'block') {
			$('#bs-editinfo-pane').slideToggle( 'fast' );
		} else {
			$('#bs-editinfo-pane').slideToggle( 'fast' );
			if (!this.loaded) {
			$.post(
				BsCore.buildRemoteString('FormattingHelp', 'getFormattingHelp'),
				function ( sResponseData ){
				$('#bs-editinfo-pane').html( sResponseData )
					.find('tr:nth-child(odd)').addClass('bs-zebra-table-row-odd')
					.find('tr:nth-child(even)').addClass('bs-zebra-table-row-even');;
				FormattingHelp.loaded = true;
				}
			);
			}
		}
	}
}

$(document).bind('BSVisualEditorToggleEditor', function(){
    if ($('#bs-editinfo-pane').css( 'display' ) == 'block') {
        $('#bs-editinfo-pane').css('display', 'none' );
    }
});