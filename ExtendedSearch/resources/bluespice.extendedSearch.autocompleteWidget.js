/**
 * ExtendedSearch extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage ExtendedSearch
 * @copyright  Copyright (C) 2012 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
( function ( mw, $ ) {
	$( function () {
		mw.loader.using( 'jquery.ui.autocomplete', function() {
			var cache = {};
			var lastXhr = {};

			if( $( "#bs-extendedsearch-input, .bs-autocomplete-field" ).length < 1 ) return;

			$( "#bs-extendedsearch-input, .bs-autocomplete-field" ).autocomplete( {
				position: { 
					my: "right top",
					at: "right bottom"
				},
				source: function( req, setList ) {
					if ( req.term in cache ) {
						setList( cache[ req.term ] );
					} else {
						var url = bs.util.getAjaxDispatcherUrl(
							'ExtendedSearchBase::getAutocompleteData',
							[ encodeURIComponent( req.term ) ]
						);
						var lastXhr = $.ajax( {
							url: url,
							dataType: 'json',
							success: function( response, textStatus, xhr ) {
								if ( response == null || response.length == '0' ) {
									$( '.ui-autocomplete' ).css( 'display', 'none' );
								} else {
									if ( xhr === lastXhr ) {
										setList( response );
									}
									cache[ req.term ] = response;
								}
							},
							failure: function() {}
						} );
					}
				},
				focus: function( event, ui ) {
					$( '.ui-corner-all' ).unbind( 'mouseleave' );
				},
				select: function( event, ui ) {
					var status = { skipFurtherProcessing: false };
					$(document).trigger( 'BSExtendedSearchAutocompleteItemSelect', [event, ui, status] );
					if ( status.skipFurtherProcessing ) return;
					document.location.href = "" + ui.item.link;
				}
			} ).data( "autocomplete" )._renderMenu = function( ul, items ) {
					ul.attr( 'id', 'bs-extendedsearch-autocomplete' );
					var self = this;
					$.each( items, function( index, item ) {
						self._renderItem( ul, item );
					});
				};

			$.ui.autocomplete.prototype._renderItem = function( ul, item ) {
				return $( "<li class='" + item.attr + "'></li>" )
					.data( "item.autocomplete", item )
					.append( "<a>" + item.label + "</a>" )
					.appendTo( ul );
			};
		} );
	});
}( mediaWiki, jQuery ) );