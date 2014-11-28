/**
 * ExtendedSearch extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for all extended search related methods and properties
 */
BsExtendedSearchSpecialPage = {

	/**
	 * Initialization method for search result view.
	 */
	init: function() {
		$( '#bs-extendedsearch-inputfieldtext-specialpage' ).focus( function() {
			if ( $( this ).val() == $( this ).attr( 'defaultvalue' ) ) $( this ).val( '' );
		}).blur( function() {
			if ( $( this ).val() == '' ) $( this ).val( $( this ).attr( 'defaultvalue' ) );
		});

		$( '#bs-search-button-specialpage' ).click( function( event ) {
			$(this).parent().find('*[name=search_scope]').val( 'title' );
			$(this).parent().submit();
		});

		$( '#bs-search-fulltext-specialpage' ).click( function() {
			$(this).parent().find('*[name=search_scope]').val( 'text' );
			$(this).parent().submit();
		});
	}

};

/**
 * Class for AJAX search result updates
 */
BsExtendedSearchAjaxManager = function() {};

/**
 * Class definition for AJAX search result updates
 */
BsExtendedSearchAjaxManager.prototype = {
	/**
	 * Script base that is prepended to any local url.
	 * @var string Base part of URL
	 */
	oUrlBase: null,
	/**
	 * URL parameters.
	 * @var array With key value pairs.
	 */
	oUrlParams: {},
	/**
	 * The AJAX-Request-Object is stored here. If new Request is done with oAjaxQuery != null oAjaxQuery is aborted first. On successful request it is reset to null
	 * @var object AXAY request object.
	 */
	oAjaxQuery: null,

	/**
	 * Builds URI from oUrlParams
	 * @return string Compiled URL.
	 */
	getUri: function() {
		var out = [];
		for ( key in ExtendedSearchAjaxManager.oUrlParams ) {
			if ( typeof( ExtendedSearchAjaxManager.oUrlParams[key] ) == 'object'
				&& ( ExtendedSearchAjaxManager.oUrlParams[key] instanceof Array ) ) {

				for ( key2 in ExtendedSearchAjaxManager.oUrlParams[key] ){
					if ( typeof( ExtendedSearchAjaxManager.oUrlParams[key][key2] ) != 'string' ) continue;
					out.push( key + '[]=' + ExtendedSearchAjaxManager.oUrlParams[key][key2] );
				}
			} else {
				out.push( key + '=' + ExtendedSearchAjaxManager.oUrlParams[key] );
			}
		}
		var newUri = ExtendedSearchAjaxManager.oUrlBase + '?' + out.join( '&' );
		return newUri;
	},

	/**
	 * Convert any URI in a format that talks to AJAX handler.
	 * @param string uriIn Original URI.
	 * @return string Modified URI.
	 */
	ajaxifyUri: function( uriIn ) {
		var uriParts = uriIn.split( '?' );
		var uriOut = bs.util.getAjaxDispatcherUrl( 'ExtendedSearch::getRequestJson' );

		if ( 1 in uriParts ) {
			var uriParams = uriParts[1].split( '&' );
			var key;
			for ( i in uriParams ) {
				if ( typeof( uriParams[i] ) != 'string' ) continue;
				key = uriParams[i].split( '=' )[0].toLowerCase();
				if ( key == 'title' ) continue;
				uriOut += '&' + uriParams[i];
			}
		}
		return uriOut;
	},

	/* executed once on $(document).ready */
	/**
	 * Initializes AJAX functions.
	 */
	init: function() {
		this.renewUriForAjaxRequest();
		this.reloadForHash();
		this.modifyLoadedElements();

		$( window ).resize(function() {
			ExtendedSearchAjaxManager.spinnerResize();
		});
	},

	/**
	 *Collapse facetboxes
	 */
	collapseFacets: function() {
		var imagepath = wgScriptPath+'/extensions/BlueSpiceExtensions/ExtendedSearch/resources/images/';

		$( '.bs-extendedsearch-facetbox-container' ).each( function() {
			var elements = $(this).find('div[class=facetBarEntry]').length;
			if ( elements > bsExtendedSearchNumFacets ) {
				$('div', this).eq( bsExtendedSearchNumFacets - 1 ).nextAll().addClass( 'bs-extendedsearch-hiddenfacet' );
				$(this).parent().append('<div class="bs-extendedsearch-morefacets"><img src="' + imagepath + 'arrow_down.png" /> ' + mw.msg( 'bs-extendedsearch-more' ) + '</div>');
			}
		});

		$( '.bs-extendedsearch-morefacets' ).click( function() {
			var elements = $(this).prev().find('div[class=facetBarEntry]').length;
			if ( elements == bsExtendedSearchNumFacets ) {
				$(this).prev().find('div').each( function() {
					if ( $(this).hasClass( 'bs-extendedsearch-hiddenfacet' ) ) {
						$(this).removeClass( 'bs-extendedsearch-hiddenfacet' );
					}
				});
				$(this).html( '<img src="' + imagepath + 'arrow_up.png" /> ' + mw.msg( 'bs-extendedsearch-fewer' ) + '' );
			} else {
				$(this).prev().find( 'div' ).eq( bsExtendedSearchNumFacets - 1 ).nextAll().addClass( 'bs-extendedsearch-hiddenfacet' );
				$(this).html( '<img src="' + imagepath + 'arrow_down.png" /> ' + mw.msg( 'bs-extendedsearch-more' ) + '' );
			}
		});
	},

	/**
	 * Reloads results for given fragment
	 */
	reloadForHash: function() {
		if ( document.location.hash != '' ) {
			var urldiff = document.location.hash;
			urldiff = urldiff.replace( '#', '' );
			ExtendedSearchAjaxManager.changeRequestFacets( urldiff, true );
		}
	},

	/**
	 * Updates URI parameters.
	 */
	renewUriForAjaxRequest: function() {
		if ( typeof( $( '#bs-extendedsearch-siteuri' ).attr( 'siteuri' ) ) == "undefined" ) return;
		ExtendedSearchAjaxManager.oUrlParams = {};
		var uriParts = $( '#bs-extendedsearch-siteuri' ).attr( 'siteuri' ).split( '?' );
		/* the ajax-Request is different:
		 *  - no title=Special:SpecialExtendedSearch nor index.php/Special:Specialextendedsearch
		 *  - instead action=remote&mod=ExtendedSearch&rf=getRequestJson
		 *  - value of search_origin is overriden with 'ajax'
		 */
		ExtendedSearchAjaxManager.oUrlBase = wgScriptPath + '/';
		if ( 1 in uriParts ) {
			var uriParams = uriParts[1].split( '&' );
			var aKeyValue;
			for ( i in uriParams ) {
				if ( typeof( uriParams[i] ) != 'string' ) continue;
				aKeyValue = uriParams[i].split( '=' );
				if ( aKeyValue[0].toLowerCase() == 'title' ) continue;
				ExtendedSearchAjaxManager.addParamToUrl( aKeyValue[0], aKeyValue[1] );
			}
		}
		ExtendedSearchAjaxManager.addParamToUrl( 'action', 'ajax' );
		ExtendedSearchAjaxManager.addParamToUrl( 'rs', 'ExtendedSearch::getRequestJson' );
	},

	/**
	 * Updates search result view.
	 */
	modifyLoadedElements: function() {
		this.searchAsYouType();
		this.collapseFacets();

		if ( $( '.bs-extendedsearch-paging A' ).length === 0 ) {
			$( '.bs-extendedsearch-paging' ).css( 'visibility', 'hidden' );
		}

		// facets armed with attribute urldiff...
		$( '[urldiff]' ).click( function() {
			ExtendedSearchAjaxManager.changeRequestFacets( $( this ).attr( 'urldiff' ), $( this ).attr( 'checked' ) );
		});

		$('#bs-extendedsearch-filters-results-paging A').click( function( event ) {
			// if middle button is pressed don't use ajax (allows opening in new tab)
			if ( event.which == 2 ) {
				return true;
			}
		});
		this.spinnerResize();
	},

	/**
	 * Requests a new search result when facet was clicked.
	 * @param string urldiff Difference to URI tha was triggered by this facet.
	 * @param bool checked True if facet was checked.
	 */
	changeRequestFacets: function( urldiff, checked ) {
		var aAllParams = urldiff.split( '&' );
		var aKeyValue = '';
		var length = aAllParams.length;
		var urlParams = urldiff;
		var hash = document.location.hash;

		for ( var i = 0; i < length; i++ ) {
			aKeyValue = aAllParams[i].split( '=' );
			if ( i != length && document.location.hash != '' ) {
				urlParams = '&' + urldiff;
			}
			if ( checked ) {
				ExtendedSearchAjaxManager.addParamToUrl( aKeyValue[0], aKeyValue[1] );
				if ( hash == '' ) {
					document.location.hash = urlParams;
				} else {
					if ( hash.indexOf( urldiff ) == -1 ) {
						document.location.hash += urlParams;
					}
				}
			} else {
				if ( hash.indexOf( '&' + urldiff ) >= 0 ) {
					document.location.hash = hash.replace( '&' + urldiff, '' );
				} else {
					document.location.hash = hash.replace( urldiff, '' );
				}
				ExtendedSearchAjaxManager.stripParamFromUrl( aKeyValue[0], aKeyValue[1] );
			}
		}
		if ( !checked ) {
			ExtendedSearchAjaxManager.addParamToUrl( 'nosel', '1' );
		}
		ExtendedSearchAjaxManager.ajaxMeANewResultsPlz();
	},

	/**
	 * AJAX call that actually fetches a new search result.
	 * @param string paramUri URL that describes the search.
	 */
	ajaxMeANewResultsPlz: function( paramUri ) {
		if ( paramUri == undefined ) {
			paramUri = ExtendedSearchAjaxManager.getUri();
		} else {
			paramUri = this.ajaxifyUri( paramUri );
		}
		if ( ExtendedSearchAjaxManager.oAjaxQuery != null )
			ExtendedSearchAjaxManager.oAjaxQuery.abort();
		$( '#bs-extendedsearch-spinner' ).show();
		ExtendedSearchAjaxManager.oAjaxQuery = $.ajax({
			url: paramUri,
			dataType: 'json',
			success: function( response, textStatus ){
				$( '#bs-extendedsearch-form-specialpage' ).parent().siblings().after( response.contents ).remove();
				$( '#bs-extendedsearch-spinner' ).hide();
				ExtendedSearchAjaxManager.oAjaxQuery = null;
				ExtendedSearchAjaxManager.renewUriForAjaxRequest();
				ExtendedSearchAjaxManager.modifyLoadedElements();
			}
		});
	},

	/**
	 * This function places the loading spinner all over the search result div.
	 */
	spinnerResize: function() {
		var spinner = $( '#bs-extendedsearch-spinner' );
		spinner.height( $( '#bs-extendedsearch-specialpage-body' ).height());
		var offset = $( '#bs-extendedsearch-specialpage-body' ).offset();
		if ( !offset ) return;
		offset.top += $( window ).scrollTop();
		offset.left += $( window ).scrollLeft();
		spinner.offset( offset );
		spinner.width($( '#bs-extendedsearch-specialpage-body' ).width());
	},

	/**
	 * Adds another parameter to oUrlParams
	 * @param string key Name of URI parameter
	 * @param string value Value of URI parameter
	 */
	addParamToUrl: function( key, value ) {
		if ( key.indexOf('[]') != -1 ) {
			key = key.replace( '[]', '' );
			if ( ExtendedSearchAjaxManager.oUrlParams[key] == null ) {
				ExtendedSearchAjaxManager.oUrlParams[key] = [ value ];
			} else {
				if ( $.inArray( value, ExtendedSearchAjaxManager.oUrlParams[key] )== -1 ) {
					ExtendedSearchAjaxManager.oUrlParams[key].push( value );
				}
			}
		} else {
			ExtendedSearchAjaxManager.oUrlParams[key] = value;
		}
	},

	/**
	 * Removes a parameter to oUrlParams
	 * @param string key Name of URI parameter
	 * @param string value Value of URI parameter
	 */
	stripParamFromUrl: function( key, value ) {
		if ( key.indexOf( '[]' ) != -1 ) {
			key = key.replace( '[]', '' );
		}
		if ( ExtendedSearchAjaxManager.oUrlParams[key] == null ) return;

		if ( typeof( ExtendedSearchAjaxManager.oUrlParams[key] ) == 'object'
			&& ( ExtendedSearchAjaxManager.oUrlParams[key] instanceof Array ) ) {

			for ( i in ExtendedSearchAjaxManager.oUrlParams[key] ) {
				if ( ExtendedSearchAjaxManager.oUrlParams[key][i] == value ) {
					ExtendedSearchAjaxManager.oUrlParams[key].splice( i, 1 );
				}
			}
			if ( ExtendedSearchAjaxManager.oUrlParams[key].length == 0 ) {
				delete ExtendedSearchAjaxManager.oUrlParams[key];
			}
		} else {
			delete ExtendedSearchAjaxManager.oUrlParams[key];
		}
	},

	/**
	 * Search as you type results
	 */
	searchAsYouType: function() {
		var inputField = $( '#bs-extendedsearch-inputfieldtext-specialpage' ),
			url,
			thread,
			keys = [ 13, 16, 17, 18, 20, 27, 37, 38, 39, 40, 91, 112,
					113, 114, 115, 116, 117, 118, 119, 120, 121, 122, 123 ];

		inputField.keydown( function() {
			clearTimeout( thread );
		} );

		inputField.keyup( function( event ) {
			clearTimeout( thread );
			if ( $.inArray( event.which, keys ) > 0 ) return false;
			if ( inputField.val() === '' ) return false;
			url = wgServer + wgScriptPath +
					'?search_scope=text&search_submit=1&q=' + encodeURIComponent( inputField.val() );

			if ( typeof bsExtendedSearchSearchFiles !== 'undefined' && bsExtendedSearchSearchFiles ) {
				url += '&search_files=1';
			}

			thread = setTimeout( function() { ExtendedSearchAjaxManager.ajaxMeANewResultsPlz( url ) }, 300 );
		} );
	}
};

ExtendedSearchAjaxManager = null;

// Can not use mw.using here because for some methods it is to early
$(document).ready( function() {
	BsExtendedSearchSpecialPage.init();
	ExtendedSearchAjaxManager = new BsExtendedSearchAjaxManager();
	ExtendedSearchAjaxManager.init();

	$("#bs-extendedsearch-checkbox-searchfiles").change( function() {
		if ( $( this ).is( ':checked' ) ) {
			$( '#bs-extendedsearch-input-searchfiles' ).val( '1' );
		} else {
			$('#bs-extendedsearch-input-searchfiles').val( '0' );
		}
	});
});