$(document).ready(function(){
	Ext4.Loader.setPath( 'BS.InsertMagic', wgScriptPath+'/extensions/BlueSpiceExtensions/InsertMagic/resources/BS.InsertMagic');

	$('#im_button').on('click', function(){
		Ext4.require('BS.InsertMagic.Window', function(){
			BS.InsertMagic.Window.clearListeners();
			BS.InsertMagic.Window.on( 'ok', BsInserMagicWikTextConnector.applyData );

			BS.InsertMagic.Window.setData(
				BsInserMagicWikTextConnector.getData()
			);
			BS.InsertMagic.Window.show( 'im_button' );
		});
	});
});

$(document).bind('hwactions-init', function( event, plugin, buttons, commands ){
	buttons.push({
		buttonId: 'hwmagic',
		buttonConfig: {
			title : mw.message('bs-insertmagic-dlg_title').plain(),
			cmd : 'mceHwMagic',
			image : wgScriptPath+'/extensions/BlueSpiceExtensions/InsertMagic/resources/images/btn_insertmagic.png'
		}
	});
	commands.push({
		commandId: 'mceHwMagic',
		commandCallback: function() {
			Ext4.require('BS.InsertMagic.Window', function(){
				BS.InsertMagic.Window.clearListeners();
				BsInserMagicVisualEditorConnector.caller = this;
				BS.InsertMagic.Window.on( 'ok', BsInserMagicVisualEditorConnector.applyData );

				BS.InsertMagic.Window.setData(
					BsInserMagicVisualEditorConnector.getData()
				);
				BS.InsertMagic.Window.show( 'im_button' );
			}, this);
		}
	});
});

$(document).bind('hwbehavior-_setDisabled', function( event, plugin, setValue, leaveEnabledIds, selectedNode ){
	leaveEnabledIds.push( plugin.editor.editorId+'_'+'hwmagic');
});

var BsInsertMagicHelper = {
	
	getTypeFromText: function( text ) {
		//What about tags containing variables? Or variables containing tags?
		if( text.match(/<(.*?)>/gmi) ) { //TODO: find better test
			return 'tag';
		}
		else if( text.match(/\{\{(.*?)\}\}/gmi) ) {
			return 'variable';
		}
		else if( text.match(/__(.*?)__/gmi) ){
			return 'switch';
		}
		else {
			return false;
		}
	}
}

var BsInserMagicWikTextConnector = {
	
	getData: function() {
		BsCore.saveScrollPosition();
		var currentCode = BsCore.saveSelection();
		var data = {
			code: currentCode
		}
		return data;
	},

	applyData: function( sender, data ) {
		BsCore.restoreSelection( data.code );
		BsCore.restoreScrollPosition();
	}
}

var BsInserMagicVisualEditorConnector = {
	data: {},
	getData: function() {
		var me = BsInserMagicVisualEditorConnector;
		var node = me.caller.selection.getNode();
		me.selection = me.caller.selection.getBookmark();
		me.data.id    = node.getAttribute('data-bs-id');
		me.data.type = node.getAttribute('data-bs-type');
		me.data.name = node.getAttribute('data-bs-name');
		var currentCode = '';

		//TODO: Laufzeitproblem: onShow ist Store noch nicht unbedingt geladen 
		//und Grid nicht unbedingt gerendert. --> selection speichern und 
		//StoreOnLoad Fokus und Selection setzen!
		if( me.data.type == 'template' ) {
			currentCode = me.caller.plugins.hwcode._templates[me.data.id];
		}
		else if(me.data.type == 'tag') {
			currentCode = me.caller.plugins.hwcode._specialtags[me.data.id];
		}
		else if(me.data.type == 'switch') {
			currentCode = me.caller.plugins.hwcode._switches[me.data.id];
		}
		me.data.code = currentCode
		return me.data;
	},
	
	applyData: function(  sender, data ) {
		var me = BsInserMagicVisualEditorConnector;
		me.caller.selection.moveToBookmark(me.selection);
		var code = data.code;
		var html = '<span id="{0}" class="mceNonEditable {1}" data-bs-name="{2}" data-bs-type="{3}" data-bs-id="{4}">{5}</span>';

		me.data.type = BsInsertMagicHelper.getTypeFromText( code );
		if( me.data.type == 'switch' ) {
			if( me.data.id ) {
				me.caller.plugins.hwcode._switches[me.data.id] = code;
			}
			else {
				me.data.id = me.caller.plugins.hwcode._switches.length;
				me.caller.plugins.hwcode._switches.push(code);
			}
			code = html.format(
				'hw_switch:@@@SWT'+me.data.id+'@@@',
				'switch',
				me.data.name,
				'switch',
				me.data.id,
				'__ '+me.data.name+' __'
			);
		}
		else if( me.data.type == 'variable') {
			if( me.data.id ) {
				me.caller.plugins.hwcode._templates[me.data.id] = code;
			}
			else {
				me.data.id = me.caller.plugins.hwcode._templates.length;
				me.caller.plugins.hwcode._templates.push(code);
			}
			code = html.format(
				'hw_template:@@@TPL'+me.data.id+'@@@',
				'template',
				me.data.name,
				'template',
				me.data.id,
				'{{ '+me.data.name+' }}'
			);
		}
		else if( me.data.type == 'tag' ) {
			if( me.data.id ) {
				me.caller.plugins.hwcode._specialtags[me.data.id] = code;
			}
			else {
				me.data.id = me.caller.plugins.hwcode._specialtags.length;
				me.caller.plugins.hwcode._specialtags.push(code);
			}
			code = html.format(
				'hw_specialtag:@@@ST'+me.data.id+'@@@',
				'tag',
				me.data.name,
				'tag',
				me.data.id,
				'&lt; '+me.data.name+' &gt;'
			);
		}
		//me.caller.activeEditor.dom.setOuterHTML(me.data.link, ""); //This is important for IE
		//me.caller.dom.remove( me.caller.selection.getNode() );
		me.caller.dom.remove(me.caller.selection.getNode()); // remove old node to ensure that new one is not place within
		me.caller.execCommand('mceInsertRawHTML', false, code );
	}
};
