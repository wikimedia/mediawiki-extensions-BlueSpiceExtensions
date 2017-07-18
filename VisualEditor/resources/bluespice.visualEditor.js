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
	var $toolbar = $('.mce-stack-layout-item').first();
	var $firstHeading = $( '#firstHeading' );

	var previewMode = ( $( '#wikiPreview' ).css( 'display' ) === 'none' ) ? false : true;

	if( previewMode && $( '#bs-ve-editoptions' ).length == 0 ) {
		bs_editOptionsBarAdd();
	}

	if(  $toolbar.length == 0 ) return;

	if( offsetTop === 0 && $( '#editform' ).length > 0 && $firstHeading.length > 0 && previewMode === false ) {
		offsetTop =  $('#content').position().top;
	}
	else if( offsetTop === 0 && $( '#editform' ).length > 0 && $firstHeading.length > 0 && previewMode === true ) {
		offsetTop = $( '#wikiPreview' ).position().top + $( '#wikiPreview' ).height() - $firstHeading.height();
	}
	else if( offsetTop === 0 && $( '#editform' ).length > 0 ) {
		offsetTop = $( '#editform' ).position().top;
	}

	if( $(document).scrollTop() > offsetTop ) { //window.scrollY
		if( $toolbar.hasClass( 'bs-ve-fixed' ) == false ) {

			if( $( '#bs-ve-editoptions' ).length == 0 ) {
				bs_editOptionsBarAdd();
			}

			$toolbar.addClass( 'bs-ve-fixed' );
			$toolbar.width( $toolbar.parent().width() );

			bs_firstHeadingFixedAdd();

			var textboxPaddingTop=  $toolbar.height();
			if( $firstHeading.length > 0 ){
				$toolbar.css( 'top', $firstHeading.height() );
				textboxPaddingTop = $toolbar.height() + $firstHeading.height();
			}
			$( '#wpTextbox1_ifr' ).css( 'padding-top', textboxPaddingTop );
		}
	}
	else {
		$toolbar.removeClass( 'bs-ve-fixed' );

		if( $( '#bs-ve-editoptions' ).length > 0 && !previewMode ) {
			bs_editOptionsBarRemove();
		}

		bs_firstHeadingFixedRemove();

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
		bs_firstHeadingFixedRemove();
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

// add some editOptions form editor footer to bs-ve toolbar
function bs_editOptionsBarAdd() {
	var $toolbar = $( '.mce-stack-layout-item' ).first();
	var $editOptionsContainer = $( '<div id="bs-ve-editoptions"></div>' ).prependTo( $toolbar );

	var $editSummaryContainer = $( '<div id="bs-ve-editsummary-group"></div>' ).appendTo( $editOptionsContainer );

	var $bsTfSummaryLabel = $( '#wpSummaryLabel' ).clone( true );
	$bsTfSummaryLabel.attr( 'id', 'bs-ve-wpSummaryLabel' );
	$bsTfSummaryLabel.appendTo( $editSummaryContainer );

	var $bsTfSummary = $( '#wpSummary' ).clone( true );
	$bsTfSummary.attr( 'id', 'bs-ve-wpSummary' );
	$bsTfSummary.attr( 'name', 'bs-wpSummary' );
	$bsTfSummary.attr( 'size', '30' );
	$bsTfSummary.attr( 'placeholder', mw.message('bs-visualeditor-editoptions-summary').plain() );
	$bsTfSummary.appendTo( $editSummaryContainer );

	var $editBtnContainer = $( '<div id="bs-ve-editbtn-group"></div>' ).appendTo( $editOptionsContainer );

	var $bsBtnSave = $( '#wpSave' ).clone( true );
	$bsBtnSave.attr( 'id', 'bs-ve-wpSave' );
	$bsBtnSave.appendTo( $editBtnContainer );

	var $bsBtnPreview = $( '#wpPreview' ).clone( true );
	$bsBtnPreview.attr( 'id', 'bs-ve-wpPreview' );
	$bsBtnPreview.appendTo( $editBtnContainer );

	var $bsBtnChanges = $( '#wpDiff' ).clone( true );
	$bsBtnChanges.attr( 'id', 'bs-ve-wpDiff' );
	$bsBtnChanges.appendTo( $editBtnContainer );

	var $bsBtnCancel = $( '#mw-editform-cancel' ).clone( true );
	$bsBtnCancel.attr( 'id', 'bs-ve-mw-editform-cancel' );
	$bsBtnCancel.appendTo( $editBtnContainer );
};

function bs_editOptionsBarRemove() {
	$( '#bs-ve-editoptions' ).remove();
};

function bs_firstHeadingFixedAdd() {
	var $firstHeading = $( '#firstHeading' );

	if( $firstHeading.length > 0 ){
		$firstHeading.addClass( 'bs-ve-heading-fixed' );
		$firstHeading.width( $firstHeading.parent().width() );
		$firstHeading.css( 'display', 'block' );
		$firstHeading.css( 'background-color', $( '#content' ).css( 'background-color' ) );
	}
}

function bs_firstHeadingFixedRemove() {
	var $firstHeading = $( '#firstHeading' );

	if( $firstHeading.length > 0 ){
		$firstHeading.removeClass( 'bs-ve-heading-fixed' );
		$firstHeading.css( 'background-color', 'transparent' );
		$firstHeading.css( 'display', 'initial' );
		$firstHeading.width( 'auto' );
	}
}

// event handler for editOptions in bs-ve toolbar
$( document ).on( 'click', '#bs-ve-wpSummary', function(){
	$( '#wpSummary' ).removeClass( 'wpSummary-active' );
	$( '#bs-ve-wpSummary' ).addClass( 'wpSummary-active' );
});

$( document ).on( 'click', '#wpSummary', function(){
	$( '#bs-ve-wpSummary' ).removeClass( 'wpSummary-active' );
	$( '#wpSummary' ).addClass( 'wpSummary-active' );
});

$( document ).on( 'keyup', '#bs-ve-wpSummary.wpSummary-active', function(){
	$( '#wpSummary' ).val( $( '#bs-ve-wpSummary' ).val() );
});

$( document ).on( 'keyup', '#wpSummary.wpSummary-active' , function(){
	$( '#bs-ve-wpSummary' ).val( $( '#wpSummary' ).val() );
});

$( document ).on( 'paste', '#bs-ve-wpSummary', function(){
	$( '#wpSummary' ).removeClass( 'wpSummary-active' );
	$( '#bs-ve-wpSummary' ).addClass( 'wpSummary-active' );
});

$( document ).on( 'paste', '#wpSummary', function(){
	$( '#bs-ve-wpSummary' ).removeClass( 'wpSummary-active' );
	$( '#wpSummary' ).addClass( 'wpSummary-active' );
});

$( document ).on( 'blur', '#bs-ve-wpSummary.wpSummary-active', function(){
	$( '#wpSummary' ).val( $( '#bs-ve-wpSummary' ).val() );
	$( '#bs-ve-wpSummary' ).removeClass( 'wpSummary-active' );
});

$( document ).on( 'blur', '#wpSummary.wpSummary-active' , function(){
	$( '#bs-ve-wpSummary' ).val( $( '#wpSummary' ).val() );
	$( '#wpSummary' ).removeClass( 'wpSummary-active' );
});