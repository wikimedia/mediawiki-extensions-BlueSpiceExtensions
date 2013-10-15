// WYSIWYG mode
$(document).bind('BsVisualEditorActionsInit', function( events, plugin, buttons, commands ){
	var currentImagePath = mw.config.get('wgScriptPath') + '/extensions/BlueSpiceExtensions/InsertCategory/resources/images';
	buttons.push(
		{
			buttonId: 'hwinsertcategory',
			buttonConfig: {
				title : 'hwactions.insertcategory',
				cmd : 'mceHwCategory',
				image : currentImagePath+'/hwcategory.png'
			}
		}
	);

	commands.push({
		commandId: 'mceHwCategory',
		commandCallback: function() {
			events.preventDefault();
			var me = this;

			Ext.require('BS.InsertCategory.Dialog', function(){
				BS.InsertCategory.Dialog.clearListeners();
				BS.InsertCategory.Dialog.on('ok', function(sender, data){
					if( BS.InsertCategory.Dialog.isDirty )
					BsInsertCategoryWysiwygEditorHelper.setCategories( data );
				});
				BS.InsertCategory.Dialog.setData(
					BsInsertCategoryWysiwygEditorHelper.getCategories()
				);
				BS.InsertCategory.Dialog.show( me );
			});
		}
	});


});


$(document).ready(function(){
	// view mode
	$('#ca-insert_category').find('a').on( 'click', function( e ) {
		e.preventDefault();
		var me = this;
		Ext.require('BS.InsertCategory.Dialog', function(){
			BS.InsertCategory.Dialog.clearListeners();
			BS.InsertCategory.Dialog.on('ok', function(sender, data){
				if( BS.InsertCategory.Dialog.isDirty )
				BsInsertCategoryViewHelper.setCategories( data );
			});
			BS.InsertCategory.Dialog.setData(
				BsInsertCategoryViewHelper.getCategories()
			);
			BS.InsertCategory.Dialog.show( me );
		});
	});

	// wikieditor mode
	$('#bs-editbutton-insertcategory').on( 'click', function( e ) {
		e.preventDefault();
		var me = this;

		Ext.require('BS.InsertCategory.Dialog', function(){
			BS.InsertCategory.Dialog.clearListeners();
			BS.InsertCategory.Dialog.on('ok', function(sender, data){
				if( BS.InsertCategory.Dialog.isDirty )
				BsInsertCategoryWikiEditorHelper.setCategories( data );
			});
			BS.InsertCategory.Dialog.setData(
				BsInsertCategoryWikiEditorHelper.getCategories()
			);
			BS.InsertCategory.Dialog.show( me );
		});
	});
	
	
	return false;
});

var BsInsertCategoryViewHelper = {
	getCategories: function() {
		return mw.config.get("wgCategories");
	},
	
	setCategories: function( categories ) {
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl( 'InsertCategory::addCategoriesToArticle', [ wgArticleId ] ),
			success: function( response, opts ) {
				var obj = Ext.decode(response.responseText);
				if(obj.success) {
					Ext.Msg.alert('Status', mw.msg('bs-insertcategory-success'));
					window.location.reload( false );
				}
				else {
					Ext.Msg.alert(mw.msg('bs-insertcategory-failure'), obj.msg);
				}
			},
			failure: function() {},
			params: {
				page_name: wgPageName,
				categories: categories.join(',')
			}
		});
	}
}

var BsInsertCategoryWikiEditorHelper = {
	getCategories: function() {
		var text = $('#wpTextbox1').val();
		// this doesn't work: bsInsertCategoryCategoryNamespaceName TODO:Localize
		var myregexp = new RegExp('\\[\\['+ bs.util.getNamespaceText( bs.ns.NS_CATEGORY ) +':(.+?)\\]\\]', 'g');
		var match;
		var terms = [];
		
		match = myregexp.exec(text);
		while(match != null) {
			terms.push( match[1] );
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
			tags = tags + "\n" + '[[Kategorie:' + value + ']]';
		});
		
		$('#wpTextbox1').val( text + tags );
		
		//BsCore.restoreSelection(tags, 'append');
		//BsCore.restoreScrollPosition();
	}
}

var BsInsertCategoryWysiwygEditorHelper = {
	getCategories: function() {
		var text = tinyMCE.activeEditor.getContent();
		// this doesn't work: bsInsertCategoryCategoryNamespaceName TODO:Localize
		var myregexp = new RegExp('\\[\\['+bs.util.getNamespaceText( bs.ns.NS_CATEGORY )+':(.+?)\\]\\]', 'g');
		var match;
		var terms = [];
		
		match = myregexp.exec(text);
		while(match != null) {
			terms.push( match[1] );
			match = myregexp.exec(text);
		}

		return terms;
	},
	
	setCategories: function( categories ) {
		var regexCat = /(<br \/>)*\[\[(?:k|c)ategor(?:ie|y):(.)+?\]\]\n?/ig;
		var tags = '';
		var text = tinyMCE.activeEditor.getContent();
		text = text.replace(regexCat, "");
		
		$.each( categories, function( index, value ) {
			tags = tags + "\n\n" + '[[Kategorie:' + value + ']]';
		});
		
		tinyMCE.activeEditor.setContent( text + tags );
	}
}