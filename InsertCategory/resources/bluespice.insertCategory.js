// WYSIWYG mode
$(document).bind('BsVisualEditorActionsInit', function( events, plugin, buttons, commands ){
	var t = plugin;
	var ed = t.getEditor();

	var currentImagePath = mw.config.get('wgScriptPath') + '/extensions/BlueSpiceExtensions/InsertCategory/resources/images';

	buttons.push( {
		buttonId: 'bsinsertcategory',
		buttonConfig: {
			title : mw.message('bs-insertcategory-title').plain(),
			cmd : 'mceHwCategory',
			onPostRender: function() {
				var self = this;

				tinyMCE.activeEditor.on('NodeChange', function(evt) {
					self.disabled(false);
					$(evt.parents).each(function(){
						if ( this.tagName.toLowerCase() == 'pre' ) {
							self.disabled(true);
						}
					});
				});
			}
		}
	} );

	commands.push({
		commandId: 'mceHwCategory',
		commandCallback: function() {
			events.preventDefault();
			var me = this;

			mw.loader.using( 'ext.bluespice.extjs' ).done(function(){
				Ext.require('BS.InsertCategory.Dialog', function(){
					BS.InsertCategory.Dialog.clearListeners();
					BS.InsertCategory.Dialog.on('ok', function(sender, data){
						if ( BS.InsertCategory.Dialog.isDirty ) {
							BsInsertCategoryWysiwygEditorHelper.setCategories( data );
						}
					});
					BS.InsertCategory.Dialog.setData(
						BsInsertCategoryWysiwygEditorHelper.getCategories()
					);
					BS.InsertCategory.Dialog.show( me );
				});
			});
		}
	});
});


// view mode
$(document).on('click', '#ca-insert_category', function(e) {
	e.preventDefault();
	var me = $(this).find('a');
	mw.loader.using( 'ext.bluespice.extjs' ).done(function(){
		Ext.require('BS.InsertCategory.Dialog', function(){
			BS.InsertCategory.Dialog.clearListeners();
			BS.InsertCategory.Dialog.on( 'ok', function ( sender, data ) {
				if ( BS.InsertCategory.Dialog.isDirty ) {
					BsInsertCategoryViewHelper.setCategories( data );
					return false;
				}
			} );
			BS.InsertCategory.Dialog.setData(
				BsInsertCategoryViewHelper.getCategories()
			);
			BS.InsertCategory.Dialog.show( me );
		});
	});
	return false;
});
// wikieditor mode
$(document).on('click', '#bs-editbutton-insertcategory', function(e) {
	e.preventDefault();
	var me = this;
	mw.loader.using( 'ext.bluespice.extjs' ).done(function(){
		Ext.require('BS.InsertCategory.Dialog', function(){
			BS.InsertCategory.Dialog.clearListeners();
			BS.InsertCategory.Dialog.on('ok', function(sender, data){
				if ( BS.InsertCategory.Dialog.isDirty ) {
					BsInsertCategoryWikiEditorHelper.setCategories( data );
				}
			});
			BS.InsertCategory.Dialog.setData(
				BsInsertCategoryWikiEditorHelper.getCategories()
			);
			BS.InsertCategory.Dialog.show( me );
		});
	});
	return false;
});

window.BsInsertCategoryViewHelper = {
	getCategories: function() {
		return mw.config.get("wgCategories");
	},

	setCategories: function( categories ) {
		mw.loader.using( 'ext.bluespice.extjs' ).done(function(){
			var api = new mw.Api();
			api.post({
				action: 'bs-wikipage-tasks',
				task: 'setCategories',
				token: mw.user.tokens.get( 'editToken', '' ),
				taskData: Ext.encode({
					page_id: mw.config.get( "wgArticleId" ),
					categories: categories
				})
			})
			.fail(function( code, response ) {
				BsInsertCategoryViewHelper.onSetCategoriesFailure( response.exception );
			})
			.done(function( response ) {
				if ( response.success === true ) {
					mw.notify( response.message, { title: mw.msg( 'bs-extjs-title-success' ) } );
					BsInsertCategoryViewHelper.onSetCategoriesOk();
				} else {
					BsInsertCategoryViewHelper.onSetCategoriesFailure( response.message );
				}
			});
		});
	},
	onSetCategoriesOk: function () {
		window.location.reload( true );
	},
	onSetCategoriesFailure: function ( msg ) {
		bs.util.alert(
			'ICsuc',
			{
				text: msg,
				titleMsg: 'bs-extjs-title-warning'
			},
			{
				ok: function() {
					BS.InsertCategory.Dialog.setLoading( false );
				}
			}
		);
	}
};

window.BsInsertCategoryWikiEditorHelper = {
	getCategories: function() {
		var text = $('#wpTextbox1').val();
		var myregexp = new RegExp('\\[\\[(Category|'+ bs.util.getNamespaceText( bs.ns.NS_CATEGORY ) +'):(.+?)\\]\\]', 'g');
		var match;
		var terms = [];

		match = myregexp.exec(text);
		while( match !== null ) {
			terms.push( match[2] );
			match = myregexp.exec(text);
		}

		return terms;
	},

	setCategories: function( categories ) {
		var regexCat = /(<br \/>)*\[\[(?:k|c)ategor(?:ie|y):(.)+?\]\]\n?/ig;
		var tags = '';
		var text = $('#wpTextbox1').val();
		text = text.replace(regexCat, "");

		$.each( categories, function( index, value ) {
			tags = tags + "\n" + '[[' + bs.util.getNamespaceText( bs.ns.NS_CATEGORY ) + ':' + value + ']]';
		});

		$('#wpTextbox1').val( text + tags );
	}
};

window.BsInsertCategoryWysiwygEditorHelper = {
	getCategories: function() {
		var text = tinyMCE.activeEditor.getContent();
		// this doesn't work: bsInsertCategoryCategoryNamespaceName TODO:Localize
		var myregexp = new RegExp('\\[\\[(Category|'+bs.util.getNamespaceText( bs.ns.NS_CATEGORY )+'):(.+?)\\]\\]', 'g');
		var match;
		var terms = [];

		match = myregexp.exec(text);
		while( match !== null ) {
			terms.push( match[2] );
			match = myregexp.exec(text);
		}

		return terms;
	},

	setCategories: function( categories ) {
		var regexCat = new RegExp('(<br \/>)*\\[\\[(Category|'+bs.util.getNamespaceText( bs.ns.NS_CATEGORY )+'):..*?\\]\\]', 'ig'); ///(<br \/>)*\[\['+bs.util.getNamespaceText( bs.ns.NS_CATEGORY )+':(.+?)\]\]/ig;
		var tags = '';
		var text = tinyMCE.activeEditor.getContent();
		text = text.replace(regexCat, "");

		$.each( categories, function( index, value ) {
			tags = tags + "\n\n" + '[[' + bs.util.getNamespaceText( bs.ns.NS_CATEGORY ) + ':' + value + ']]';
		});

		tinyMCE.activeEditor.setContent( text + tags );
	}
};