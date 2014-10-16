(function(mw, $, bs){

	var makePageItems = function( anchor ) {
		var title = anchor.data('bs-title');
		var items = [];

		if( !title || anchor.hasClass('new') ) {
			return items;
		}

		items.push({
			text: mw.message('bs-contextmenu-page-edit').plain(),
			href: bs.util.wikiGetlink(
				{
					action: 'edit'
				},
				title
			),
			id: 'bs-cm-item-edit',
			iconCls: 'icon-pencil'
		});
		items.push({
				text: mw.message('bs-contextmenu-page-history').plain(),
				href: bs.util.wikiGetlink(
					{
						action: 'history'
					},
					title
				),
				id: 'bs-cm-item-history',
				iconCls: 'icon-history'
			});

		items.push({
			text: mw.message('bs-contextmenu-page-delete').plain(),
			href: bs.util.wikiGetlink(
				{
					action: 'delete'
				},
				title
			),
			id: 'bs-cm-item-delete',
			iconCls: 'icon-trash'
		});

		items.push({
			text: mw.message('bs-contextmenu-page-move').plain(),
			href: mw.util.getUrl( 'Special:Movepage/'+title ),
			id: 'bs-cm-item-move',
			iconCls: 'icon-shuffle'
		});

		items.push({
			text: mw.message('bs-contextmenu-page-protect').plain(),
			href: bs.util.wikiGetlink(
				{
					action: 'protect'
				},
				title
			),
			id: 'bs-cm-item-protect',
			iconCls: 'icon-shield'
		});

		return items;
	};

	var makeUserItems = function( anchor ) {
		var items = [];
		if( !anchor.data('bs-username') ) {
			return items;
		}

		var username = anchor.data('bs-username');

		if( bsUserCanSendMail && anchor.data('bs-user-has-email') ) {
			items.push({
				text: mw.message('bs-contextmenu-user-mail').plain(),
				href: bs.util.wikiGetlink(
					{
						target: username
					},
					'Special:EmailUser'
				),
				id: 'bs-cm-item-usermail',
				iconCls: 'icon-mail'
			});
		}
		items.push({
			text: mw.message('bs-contextmenu-user-talk').plain(),
			href: bs.util.wikiGetlink(
					{
						action: 'edit'
					},
					'User_talk:'+username
				),
			id: 'bs-cm-item-usertalk',
			iconCls: 'icon-bubbles'
		});
		return items;
	};

	var makeMediaItems = function( anchor ) {
		/*
		* Unfotunately "Media" links do not have any special class or data
		* attribute to recognize them. But the 'title' attribute always
		* contains the original file name.
		* AND: There is no data-bs-title attribute like on "File" links
		* (Links that aim to the description page)
		* This logic will need a rewrite when MW 1.24 is supported.
		*/
		var title = anchor.attr('title');
		var dataTitle = anchor.data('bs-title');
		if( !title || dataTitle ) {
			return true;
		}

		var titleParts = title.split('.');
		var fileExtension = titleParts[titleParts.length-1];

		if( wgFileExtensions.indexOf(fileExtension) === -1 ) {
			return true;
		}

		var items = [
			{
				iconCls: 'icon-arrow-right',
				text: mw.message('bs-contextmenu-media-view-page').plain(),
				href: mw.util.getUrl( 'File:'+title )
			},
			{
				iconCls: 'icon-upload',
				text: mw.message('bs-contextmenu-media-reupload').plain(),
				href: bs.util.wikiGetlink(
					{
						wpDestFile: title
					},
					'Special:Upload'
				)
			}
		];

		return items;
	};

	var makeFileItems = function( anchor ) {
		var items = [];
		var fileurl = anchor.data('bs-fileurl');
		var filename = anchor.data('bs-filename');

		if( fileurl ) {
			items.push({
				iconCls: 'icon-download',
					text: mw.message('bs-contextmenu-file-download').plain(),
					href: fileurl
			});
		}

		if( filename ) {
			items.push({
				iconCls: 'icon-upload',
				text: mw.message('bs-contextmenu-media-reupload').plain(),
				href: bs.util.wikiGetlink(
					{
						wpDestFile: filename
					},
					'Special:Upload'
				)
			});
		}

		return items;
	};

	var showMenu = function( anchor, items, e ) {
		$(document).trigger( 'BSContextMenuBeforeCreate', [anchor, items]);

		var menu = new Ext.menu.Menu({
			id: 'bs-cm-menu',
			items: items,
			listeners: {
				/*
				 * Unfortunately ExtJS does not use "close" when the context
				 * menu disappears, but hide. Therefore closeAction: 'destroy',
				 * wich is default does not work. But as we use DOM IDs we
				 * really need to remove the tem from the DOM, otherwise we
				 * get ugly collisions when a secon menu is opened.
				 */
				hide:function(menu, opt){
					Ext.destroy(menu);
				}
			}
		});
		menu.showAt(e.pageX, e.pageY);

		e.preventDefault();
		return false;
	};

	var appendSection = function(base, additions, sectionkey) {
		if( base.length > 0 ) {
			base.push('-');
		}
		var additionsLength = additions.length;
		for( var i = 0; i < additionsLength; i++ ) {
			base.push( additions[i] );
		}

		return base;
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

		var items = [];

		var mediaItems = makeMediaItems( anchor );
		if( mediaItems.length > 0 ) {
			items = appendSection( items, mediaItems );
		}

		var userItems = makeUserItems( anchor );
		if( userItems.length > 0 ) {
			items = appendSection( items, userItems );
		}

		var fileItems = makeFileItems( anchor );
		if( fileItems.length > 0 ) {
			items = appendSection( items, fileItems );
		}

		var pageItems = makePageItems( anchor );
		if( pageItems.length > 0 ) {
			items = appendSection( items, pageItems );
		}

		if( items.length === 0 ) {
			return true;
		}

		return showMenu( anchor, items, e );
	});

})( mediaWiki, jQuery, blueSpice);