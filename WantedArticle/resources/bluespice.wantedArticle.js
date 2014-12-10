/**
 * WantedArticle extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Robert Vogel <vogel@hallowelt.biz>

 * @package    Bluespice_Extensions
 * @subpackage WantedArticle
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
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
		$( '#bs-extendedsearch-suggest' ).on( 'click', function() { //Has to be "live" because #bs-extendedsearch-suggest may be changed via AJAX
			//TODO: $.live() is deprecated since v1.7. Replace with $.on() as soon as we drop MW 1.17 support.
			return BsWantedArticle.sendSuggestion( $( this ).attr( 'href' ).substr( 1 ) );
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
			return false;
			//not sure...
			/*var sTitle = $(this).find($('.bs-wantedarticle-composite-textfield')).val();
			if( bsWantedArticleShowCreate == true ){
				return BsWantedArticle.navigateToTarget( sTitle, $(this) );
			}
			else{
				return BsWantedArticle.sendSuggestion( sTitle );
			}*/
		});

		this.toggleMoreHandler();
	},

	checkArticleTitle: function( sArticleTitle, sDefault ) { // TODO RBV (06.10.10 09:42): Should be in common framework
		if ( sArticleTitle == '' || sArticleTitle == sDefault ) {
			bs.util.alert(
				'bs-wantedarticle-alert',
				{
					text: 'bs-wantedarticle-info-nothing-entered'
				}
			);
			return false;
		}

		var aFoundChars = [];
		for ( var i=0; i < bsForbiddenCharsInArticleTitle.length; i++ ) {
			if ( sArticleTitle.indexOf( bsForbiddenCharsInArticleTitle [i] ) != -1 ) {
				aFoundChars.push( '"' + bsForbiddenCharsInArticleTitle [i] + '"' );
			}
		}
		if( aFoundChars.length > 0 ) {
			bs.util.alert(
				'bs-wantedarticle-alert',
				{
					text: mw.message('bs-wantedarticle-title-invalid-chars', aFoundChars.length, aFoundChars.join( ', ' ) ).plain()
				}
			);
			return false;
		}
		return true;
	},

	navigateToTarget: function( sArticleTitle ) {
		sArticleTitle = sArticleTitle.replace( ' ', '_' );
		var sUrl = this.config.urlBase + '/index.php?title=' + encodeURIComponent( sArticleTitle );
		document.location.href = sUrl;

		return false;
	},

	sendSuggestion: function( sArticleTitle ) {
		$.getJSON(
			bs.util.getAjaxDispatcherUrl( 'WantedArticle::ajaxAddWantedArticle', [ sArticleTitle ] ),
			function( oData, oTextStatus ) {
				bs.util.alert( 'WAsuc', { text: oData.message, titleMsg: 'bs-extjs-title-success' } );
				if( oData.success == true ) {
					BsWantedArticle.resetDefaults();
					BsWantedArticle.reloadAllWantedArticleTags();
				}
			}
		);

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
			var callback = function(currentObject) {
				return function( oData, oTextStatus ) {
					if( oData.success == true ) {
						currentObject.replaceWith(oData.view);
						BsWantedArticle.toggleMoreHandler();
					}
				};
			};
			$.getJSON(
				bs.util.getAjaxDispatcherUrl(
					'WantedArticle::ajaxGetWantedArticles',
					[
						$(this).attr('data-count'),
						$(this).attr('data-sort'),
						$(this).attr('data-order'),
						$(this).attr('data-type'),
						$(this).find('h3').text()
					]
				),
				callback($(this))
			);
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

mw.loader.using( 'ext.bluespice',function() {
	BsWantedArticle.config = {
		urlBase: wgServer + wgScriptPath
	};
	BsWantedArticle.init();
} );

// Register with ExtendedSearch Autocomplete
$(document).on('BSExtendedSearchAutocompleteItemSelect', function( event, selectEvent, ui, status ){
	if ( ui.item.attr !== 'bs-extendedsearch-suggest' ) return;

	BsWantedArticle.sendSuggestion( ui.item.value );
	status.skipFurtherProcessing = true;
});