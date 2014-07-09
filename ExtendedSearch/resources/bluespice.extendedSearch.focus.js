( function( mw, $ ) {
	$( function() {
		if ( typeof bsExtendedSearchSetFocus === "boolean" ) {
			//$(document).scrollTop(): prevent loosing last scroll position on history back
			if ( wgIsArticle === true && bsExtendedSearchSetFocus  === true && $( document ).scrollTop() < 1 ) {
				if ( window.location.hash === '' ) {
					$( '#bs-extendedsearch-input' ).focus();
				}
			}
		}
	});
})( mediaWiki, jQuery );