/**
 * VisualEditor extension
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @version    1.20.0

 * @package    Bluespice_Extensions
 * @subpackage VisualEditor
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

offsetTop = 0;
$(document).on('change', '#wpTextbox1' ,function() {
	$(this).data("text-changed", true);
});
$(window).scroll(function(){
	var toobar = $('.mce-stack-layout-item').first();
	var firstHeading = $( '#firstHeading' );

	var previewMode = ( $( '#wikiPreview' ).css( 'display' ) === 'none' ) ? false : true;

	if( toobar.length == 0 ) return;

	if( offsetTop === 0 && $( '#editform' ).length > 0 && firstHeading.length > 0 && previewMode === false ) {
		offsetTop = firstHeading.position().top;
	}
	else if( offsetTop === 0 && $( '#editform' ).length > 0 && firstHeading.length > 0 && previewMode === true ) {
		offsetTop = $( '#wikiPreview' ).position().top + $( '#wikiPreview' ).height() - firstHeading.height();
	}
	else if( offsetTop === 0 && $( '#editform' ).length > 0 ) {
		offsetTop = $( '#editform' ).position().top;
	}

	if( $(document).scrollTop() > offsetTop ) { //window.scrollY
		if( toobar.hasClass( 'bs-ve-fixed' ) == false ) {

			toobar.addClass( 'bs-ve-fixed' );
			toobar.width( toobar.parent().width() );

			if( firstHeading.length > 0 ){
				toobar.css( 'top', firstHeading.height() );

				firstHeading.addClass( 'bs-ve-heading-fixed' );
				firstHeading.width( firstHeading.parent().width() );
				firstHeading.css(
					'background-color',
					$( '#content' ).css( 'background-color' )
				);
			}

			$( '#wpTextbox1_ifr' ).css(
				'padding-top',
				toobar.height() + firstHeading.height()
			);
		}
	}
	else {
		toobar.removeClass( 'bs-ve-fixed' );

		if( firstHeading.length > 0 ){
			firstHeading.removeClass( 'bs-ve-heading-fixed' );
			firstHeading.css( 'background-color', 'transparent' );
			firstHeading.width( 'auto' );
		}

		$( '#wpTextbox1_ifr' ).css( 'padding-top', '0px');
	}
});

// TODO MRG (09.10.13 10:28): This is deprecated
$(document).on('VisualEditor::instanceShow', function(event, editorId) {
	if (editorId === 'wpTextbox1') {
		$('#toolbar').hide();
		$('#bs-extendededitbar').hide();
	}
});
$(document).on('VisualEditor::instanceHide', function(event, editorId) {
	if (editorId === 'wpTextbox1') {
		$('#toolbar').show();
		$('#bs-extendededitbar').show();
	}
});

function bs_initVisualEditor() {
	var currentSiteCSS = [];
	//We collect the CSS Links from this document and set them as content_css
	//for TinyMCE

	$('link[rel=stylesheet]').each(function(){
		var cssBaseURL = '';
		var cssUrl = $(this).attr('href');
		//Conditionally make urls absolute to avoid conflict with tinymce.baseURL
		if( cssUrl.indexOf('/') === 0 ) {
			cssBaseURL = mw.config.get('wgServer');
		}
		//need to check, if the stylesheet is already included
		if (jQuery.inArray(cssBaseURL + cssUrl, currentSiteCSS) === -1)
			currentSiteCSS.push( cssBaseURL + cssUrl );
	});
	//IE9 fix
	if ( typeof VisualEditor != "undefined" ) {
		VisualEditor.setConfig('editpage', {
			height: 550,
			content_css: currentSiteCSS.join(',')
		});
	}

	if ( mw.config.get('bsVisualEditorUse') !== false
		&& mw.user.options.get('MW::VisualEditor::Use') === true ) {
			VisualEditor.startEditors();
			$(document).trigger('VisualEditor::instanceShow', ['wpTextbox1']);
	}
}

$( document ).ready( function() {
	var BsVisualEditorLoaderUsingDeps = mw.config.get( 'BsVisualEditorLoaderUsingDeps' );

	$( '#firstHeading' ).css( 'display', 'block' );

	mw.loader.using( BsVisualEditorLoaderUsingDeps, bs_initVisualEditor ).done( function() {
		$(document).on('click', '#bs-editbutton-visualeditor', function(e) {
			e.preventDefault();
			//todo: check ob richtig, denke durch 'wpTextbox1' wird in tinymce.startup.js ln 95
			//eine Instanz des tiny erzeugt, der mit seiner id den MW-Editor überschreibt => kein speichern möglich
			//VisualEditor.toggleEditor('wpTextbox1');
			$(document).trigger('VisualEditor::instanceShow', ['wpTextbox1']);
			VisualEditor.startEditors();
			return false;
		});
		$( '#bs-editbutton-visualeditor' ).removeClass( 'bs-editbutton-disabled' );
	});
});