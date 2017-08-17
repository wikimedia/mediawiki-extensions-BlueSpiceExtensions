(function(mw, $, bs){
	var menu = null;

	var showMenu = function( anchor, items, e ) {
		$(document).trigger( 'BSContextMenuBeforeCreate', [anchor, items]);

		/*
		 * Unfortunately ExtJS does not use "close" when the context
		 * menu disappears, but "hide". Therefore closeAction: 'destroy',
		 * which is default does not work. But as we use DOM IDs we
		 * really need to remove the them from the DOM, otherwise we
		 * get ugly collisions when a second menu is opened.
		 */
		Ext.destroy(menu);

		menu = new Ext.menu.Menu({
			id: 'bs-cm-menu',
			items: items
		});
		menu.showAt(e.pageX, e.pageY);

		e.preventDefault();
		return false;
	};

	var modus = mw.user.options.get('MW::ContextMenu::Modus', 'ctrl');

	$(document).on( 'contextmenu', 'a', function( e ) {
		if( (modus === 'no-ctrl' && e.ctrlKey) || (modus === 'ctrl' && !e.ctrlKey) ) {
			return true;
		}

		var anchor = $(this);

		if( anchor.hasClass('external') ) {
			return true;
		}

		mw.loader.using( 'ext.bluespice.extjs', function() {
			var items = [];

			bs.api.tasks.exec(
				'contextmenu',
				'getMenuItems',
				{
					title: anchor.data('bs-title')
				}
			).done( function( response )  {
				if( response.payload_count > 0 ) {
					for( var item in response.payload.items ){
						items.push(response.payload.items[item]);
					}
					showMenu( anchor, items, e );
				}
			});
		});

		return false;
	});

})( mediaWiki, jQuery, blueSpice);