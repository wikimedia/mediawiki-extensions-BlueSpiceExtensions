$(document).on( 'click', '.bs-es-facetsettings', function( e ) {
	e.preventDefault();
	var me = this;

	if ( $( this ).data( 'fset').op == 'OR' ) {
		$( this ).data( 'fset').op = 'AND';
	} else {
		$( this ).data( 'fset').op = 'OR';
	}

	var fsets = {};
	$( '.bs-es-facetsettings' ).each( function(index, me){
		var fset = $(me).data( 'fset' );
		var fsetparam = $(me).data( 'fset-param' );
		fsets[fsetparam] = fset;
	});

	/* Unfortunately neither 'ExtendedSearchAjaxManager' in
	 * general nor 'changeRequestFacets' provide a way to
	 * _replace_ a url fragment part. Therefore we just clean
	 * it up manually */
	document.location.hash = document.location.hash.replace(/(&?)fset=.*?(&|$)/g, '$2');
	ExtendedSearchAjaxManager.changeRequestFacets( 'fset=' + JSON.stringify( fsets ), true );

	return false;
});
