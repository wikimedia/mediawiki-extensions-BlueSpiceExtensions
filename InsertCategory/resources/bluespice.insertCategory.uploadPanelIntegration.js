(function( mw, $, bs, d ){
	$(d).on( 'BSUploadPanelInitComponent', function( e, sender, panelItems, detailsItems ) {
		if( !sender.bsCategories ) {
			return;
		}

		/**
		 * If the upload panel is in a window, it might be closed and reopened (e.g. in edit mode). In that case, it is not
		 * enough to set the categories on init time, but we have to set them every time the window with the panel opens,
		 * as the categories might have changed in the meantime.
		 */
		sender.on( 'afterrender', function() {
			var window = sender.up( 'window' );
			if( !window ) {
				return;
			}

			window.on( 'show', function() {
				sender.bsCategories.setValue( fetchCategories() );
			}, sender );
		});

		sender.bsCategories.setValue( fetchCategories() );
	} );

	/**
	 * There are three possible sources for categories of a page:
	 * 1. In WikiText edit mode we can grab all the explicitly set categories from '#wpTextbox1'
	 * 2. In BlueSpiceVisualEditor edit mode we can grab the explicitly set categories from 'tinyMCE.activeEditor'
	 * 3. In view mode we can get explicitly _and_ implicitly set categories by accessing 'wgCategories' variable
	 */
	function fetchCategories () {
		var categories = mw.config.get( 'wgCategories', [] );
		var action = mw.config.get( 'wgAction' );
		if( action === 'edit' ) {
			categories = BsInsertCategoryWikiEditorHelper.getCategories();

			if( tinyMCE && tinyMCE.activeEditor ) {
				categories = BsInsertCategoryWysiwygEditorHelper.getCategories();
			}
		}

		return categories;
	}
}( mediaWiki, jQuery, blueSpice, document ));