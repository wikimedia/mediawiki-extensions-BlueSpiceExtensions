//PW(28.09.2013) TODO: use FormPanelFileLink context 
onFileDialogFile = function(path) {
	Ext.getCmp('BSInserLinkTargetUrl').setValue(path);
}
//PW(28.09.2013) TODO: use FormPanelFileLink context 
onFileDialogCancel = function() {}

$(document).ready(function(){
	if (bsInsertLinkUseFilelinkApplet) {
		if ( navigator.appName == 'Microsoft Internet Explorer' ) {
			bsInsertLinkUseFilelinkApplet = false;
		}
	}

	$('#bs-editbutton-insertlink').on('click', function(){
		var me = this;
		Ext.require('BS.InsertLink.Window', function(){
			BS.InsertLink.Window.resetData();
			BS.InsertLink.Window.clearListeners();
			BS.InsertLink.Window.on( 'ok', BsInsertLinkWikiTextConnector.applyData, this );
			BsInsertLinkWikiTextConnector.getData();
			BS.InsertLink.Window.setData(
				BsInsertLinkWikiTextConnector.getData()
			);
			BS.InsertLink.Window.show( me );
		});
	});
});

$(document).bind('BsVisualEditorActionsInit', function( event, plugin, buttons, commands ){
	buttons.push({
		buttonId: 'bslink',
		buttonConfig: {
			icon: 'link',
			title : mw.message('bs-insertlink-button_title').plain(),
			cmd : 'mceBsLink'
		}
	});

	//We use the standard TinyMCE functionality for this!
	buttons.push({
		buttonId: 'unlink',
		buttonConfig:{
			icon: 'unlink',
			tooltip: 'Remove link',
			cmd: 'unlink',
			stateSelector: 'a[href]'
		}
	});

	commands.push({
		commandId: 'mceBsLink',
		commandCallback: function() {
			var editor = tinyMCE.activeEditor;

			Ext.require('BS.InsertLink.Window', function(){
				BS.InsertLink.Window.clearListeners();
				BS.InsertLink.Window.on( 'ok', BsInsertLinkVisualEditorConnector.applyData, this, plugin, editor );
				BS.InsertLink.Window.resetData();
				BS.InsertLink.Window.setData(
					BsInsertLinkVisualEditorConnector.getData( plugin, editor )
				);
				BS.InsertLink.Window.show( 'bslink' );
			}, this);
		}
	});
});

var BsInsertLinkWikiTextConnector = {

	getData: function() {
		return { code: bs.util.selection.save() }
	},
	applyData: function( window, data ) {
		bs.util.selection.restore( data.code );
	}
}

var BsInsertLinkVisualEditorConnector = {
	getData: function( plugin, editor ) {
		var data = {};
		var node = editor.selection.getNode();
		var link = editor.dom.getParent(node, "a");

		if ( !link && node ) {
			// Maybe link is already included in selection
			var nodeName = node.nodeName.toLowerCase();
			if ( nodeName == 'a' ) link = node;
		}
		if (link) {
			editor.selection.select(link);

			data.href = decodeURIComponent(editor.dom.getAttrib(link, "href"));
			data.raw = editor.dom.getOuterHTML(link);
			data.type = editor.dom.getAttrib(link, "data-bs-type");
			data.content = link.innerHTML;
			data.link = link;
		}
		else {
			var hwcontent = editor.selection.getContent();
			data.content=hwcontent.replace(/<.*?>/ig, '');
		}
		//data.selection = editor.selection.getBookmark();

		// Fix bug with cursor after table. IE will place everything within the table otherwise.
		// Solution: move selectionstart one step further
		var parentTag = editor.dom.getParent(node);
		if (parentTag.nodeName.toLowerCase() == 'body') {
			data.selection.start++;
		}

		return data;
	},

	applyData: function( window, data, plugin ) {
		var editor = plugin.getEditor();

		var newAnchor = null

		if( editor.selection.getNode().nodeName.toLowerCase() == 'a' ) {
			newAnchor = editor.dom.create(
				'a',
				{
					'title': data.title ? data.title : data.href,
					'href': data.href,
					//'class': data.class,
					'data-bs-type': data.type,
					'data-bs-wikitext': data.code
				},
				data.title ? data.title : data.href
			);
			editor.dom.replace(newAnchor, editor.selection.getNode());
			editor.selection.select(newAnchor, false);
			editor.selection.collapse(false);

			return;
		}

		newAnchor = editor.dom.createHTML(
			'a',
			{
				'title': data.title ? data.title : data.href,
				'href': data.href,
				//'class': data.class,
				'data-bs-type': data.type,
				'data-bs-wikitext': data.code
			},
			data.title ? data.title : data.href
		);

		editor.insertContent(newAnchor);
		//editor.dom.inserAfter(newAnchor, editor.selection.getSel());
		//editor.selection.getEnd().remove();
		//this.dom.insertAfter(newAnchor, editor.selection.getNode());
		

		//Place cursor to new element
		//editor.selection.select(newAnchor, false);
		//editor.selection.collapse(false);
	}
}
