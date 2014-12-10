/**
 * VisualEditor extension
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    1.20.0
 
 * @package    Bluespice_Extensions
 * @subpackage VisualEditor
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

offsetTop = 0;
$(window).scroll(function(){
	var toobar = $('.mce-stack-layout-item').first();
	if( toobar.length == 0 ) return;
	if(offsetTop == 0){
		offsetTop = $('#editform').position().top; //toobar.position().top;
	}
	
	if( $(document).scrollTop() > offsetTop ) { //window.scrollY
		if( toobar.hasClass('bs-ve-fixed') == false ) {
			
			toobar.addClass('bs-ve-fixed');
			toobar.width( toobar.parent().width() );
		}
	}
	else {
		toobar.removeClass('bs-ve-fixed');
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

$(document).on('click', '#bs-editbutton-visualeditor', function(e) {
	e.preventDefault();
	//todo: check ob richtig, denke durch 'wpTextbox1' wird in tinymce.startup.js ln 95
	//eine Instanz des tiny erzeugt, der mit seiner id den MW-Editor überschreibt => kein speichern möglich
	//VisualEditor.toggleEditor('wpTextbox1');
	$(document).trigger('VisualEditor::instanceShow', ['wpTextbox1']);
	VisualEditor.startEditors();
	return false;
});

$(document).ready( function() {
	var BsVisualEditorLoaderUsingDeps = mw.config.get('BsVisualEditorLoaderUsingDeps');
	mw.loader.using(BsVisualEditorLoaderUsingDeps, bs_initVisualEditor);
});
