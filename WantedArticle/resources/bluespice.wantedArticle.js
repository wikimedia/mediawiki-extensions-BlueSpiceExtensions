/**
 * WantedArticle extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @author     Robert Vogel <vogel@hallowelt.com>

 * @package    Bluespice_Extensions
 * @subpackage WantedArticle
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for all WantedArticle related methods and properties
 */
BsWantedArticle = {
	oForms:          null,
	oCreateButtons:  null,
	oSuggestButtons: null,
	oTextFields:     null,

	init: function() {
		//ExtendedSearch
		$( '#bs-extendedsearch-suggest' ).on( 'click', function() {
			var title = $( this ).attr( 'href' );
			title = title.substring( title.indexOf( '#' ) + 1 );
			title = title.replace( '+', ' ' );
			return BsWantedArticle.sendSuggestion( title );
		});

		this.oForms          = $( '.bs-wantedarticle-form' );
		this.oTextFields     = $( '.bs-wantedarticle-composite-textfield' );
		this.oCreateButtons  = $( '.bs-wantedarticle-createbutton' );
		this.oSuggestButtons = $( '.bs-wantedarticle-suggestbutton' );

		this.oTextFields.focus( function() {
			if( $(this).val() == $(this).attr('data-defaultvalue') ) $(this).val( '' );
			$(this).removeClass( 'bs-unfocused-textfield' );
		}).blur( function() {
			if( $(this).val() == '' ) $(this).val( $(this).attr('data-defaultvalue') );
			$(this).addClass( 'bs-unfocused-textfield' );
		});

		this.oCreateButtons.click( function() {
			var oForm = $('#bs-wantedarticle-form-' + $(this).attr('id').replace(/bs-wantedarticle-createbutton-/, ""));
			var oTextField = oForm.find($( '.bs-wantedarticle-composite-textfield' ));
			var sArticleTitle = oTextField.val();

			if (BsWantedArticle.checkArticleTitle( sArticleTitle, oTextField.attr('data-defaultvalue') ) != true )  return false;

			return BsWantedArticle.navigateToTarget(
				sArticleTitle
			);
		});

		this.oSuggestButtons.click( function() {
			var oForm = $('#bs-wantedarticle-form-' + $(this).attr('id').replace(/bs-wantedarticle-suggestbutton-/, ""));
			var oTextField = oForm.find('.bs-wantedarticle-composite-textfield' );
			var sArticleTitle = oTextField.val();

			if ( BsWantedArticle.checkArticleTitle( sArticleTitle, oTextField.attr('data-defaultvalue') ) != true )  return false;

			return BsWantedArticle.sendSuggestion(
				sArticleTitle
			);
		});

		this.oTextFields.keypress( function(e) {
			if(e.keyCode == 13) { //Enter
				e.preventDefault();
				$(this).parent().find('.bs-wantedarticle-createbutton').click();
			}
		});

		this.oForms.submit( function() {
			//Do not submit this forms!
			return false;
		});

		this.toggleMoreHandler();
	},

	checkArticleTitle: function( sArticleTitle, sDefault ) { // TODO RBV (06.10.10 09:42): Should be in common framework
		if ( sArticleTitle === '' || sArticleTitle === sDefault ) {
			bs.util.alert(
				'bs-wantedarticle-alert',
				{
					text: mw.message('bs-wantedarticle-info-nothing-entered').plain()
				}
			);
			return false;
		}
		return true;
	},

	navigateToTarget: function( sArticleTitle ) {
		sArticleTitle = sArticleTitle.replace( ' ', '_' );
		var sUrl = mw.util.wikiGetlink( sArticleTitle );
		document.location.href = sUrl;

		return false;
	},

	sendSuggestion: function( sArticleTitle ) {
		$.ajax({
			dataType: "json",
			url: mw.util.wikiScript( 'api' ),
			data: {
				action: 'bs-wantedarticle',
				task: 'addWantedArticle',
				format: 'json',
				token: mw.user.tokens.get( 'editToken', '' ),
				taskData: JSON.stringify({
					title: sArticleTitle
				})
			},
			success: function( oData, oTextStatus ) {
				bs.util.alert( 'WAsuc', {
					text: oData.message,
					titleMsg: 'bs-extjs-title-success'
				});
				if( oData.success == true ) {
					BsWantedArticle.resetDefaults();
					BsWantedArticle.reloadAllWantedArticleTags();
				}
			}
		});

		return false;
	},

	resetDefaults: function() {
		this.oTextFields.each( function() {
			$(this).val($(this).attr('data-defaultvalue'));
		});
	},

	reloadAllWantedArticleTags: function() {
		$( '.bs-wantedarticle-tag' ).each( function() {
			//hint: http://stackoverflow.com/questions/939032/jquery-pass-more-parameters-into-callback
			var callback = function( currentObject ) {
				return function( oData, oTextStatus ) {
					if( oData.success === true ) {
						currentObject.replaceWith( oData.payload.view );
						BsWantedArticle.toggleMoreHandler();
					}
				};
			};

			$.ajax({
				dataType: "json",
				url: mw.util.wikiScript( 'api' ),
				data: {
					action: 'bs-wantedarticle',
					task: 'getWantedArticles',
					format: 'json',
					token: mw.user.tokens.get( 'editToken', '' ),
					taskData: JSON.stringify({
						count: $(this).attr('data-count'),
						sort: $(this).attr('data-sort'),
						order: $(this).attr('data-order'),
						type: $(this).attr('data-type'),
						title: $(this).find('h3').text()
					})
				},
				success: callback($(this))
			});
		});
	},
	toggleMoreHandler: function() {
		if($('.togglemore').width() !== null) {
			$('.togglemore').click( function(){
				var isParagraph = $(this).parentsUntil('.bs-wantedarticle-tag').width();

				if( isParagraph !== null ) {
					$(this).parent().hide();
					$(this).parent().next().show();
				} else {
					$(this).hide();
					$(this).next().show();
				}
			});
		}

		if($('.togglemore-queue').width() !== null) {
			// taken from view => onclick="$(this).hide();$(this).next().show(); return false;"
			$('.togglemore-queue').click( function(){
				$(this).hide();
				$(this).next().show();
			});
		}

	}
};

$(document).ready( function() {
	mw.loader.using( 'ext.bluespice', function() {
		BsWantedArticle.init();
	});
});
// Register with ExtendedSearch Autocomplete
$(document).on('BSExtendedSearchAutocompleteItemSelect', function( event, selectEvent, ui, status ){
	if ( ui.item.attr !== 'bs-extendedsearch-suggest' ) return;

	BsWantedArticle.sendSuggestion( ui.item.value );
	status.skipFurtherProcessing = true;
});