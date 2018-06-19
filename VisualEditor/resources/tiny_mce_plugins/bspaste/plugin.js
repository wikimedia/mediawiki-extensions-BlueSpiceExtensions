var BsPaste = function() {
	this.content = '';

	this.init = function( editor, url ) {
		var me = this;
		editor.on( 'PastePreProcess', function ( e ) {
			me.content = e.content;

			if( me.setContentHTML() ) {
				e.content = me.preprocessHTML();
			}
		} );
	};

	this.setContentHTML = function() {
		var cnt = $( this.content );
		if( cnt.length > 0 ) {
			this.content = cnt;
			return true;
		}

		return false;
	}

	this.preprocessHTML = function() {
		return this.content[0].outerHTML;
	}
}

tinymce.PluginManager.add( 'bspaste', BsPaste );
