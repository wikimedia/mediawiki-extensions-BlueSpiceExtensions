$(document).ready(function(){
	Ext.Loader.setPath( 'BS.InsertMagic', wgScriptPath+'/extensions/BlueSpiceExtensions/InsertMagic/resources/BS.InsertMagic');

	$('a#bs-editbutton-insertmagic').on('click', function(){
		var me = this;
		Ext.require('BS.InsertMagic.Window', function(){
			BS.InsertMagic.Window.clearListeners();
			BS.InsertMagic.Window.on( 'ok', BsInsertMagicWikiTextConnector.applyData );

			BS.InsertMagic.Window.setData(
				BsInsertMagicWikiTextConnector.getData()
			);
			BS.InsertMagic.Window.show( me );
		});
	});
});

$(document).bind('BsVisualEditorActionsInit', function( event, plugin, buttons, commands ){
	buttons.push({
		buttonId: 'bsmagic',
		buttonConfig: {
			title : mw.message('bs-insertmagic-dlg_title').plain(),
			cmd : 'mceBsMagic'
		}
	});
	commands.push({
		commandId: 'mceBsMagic',
		commandCallback: function() {
			Ext.require('BS.InsertMagic.Window', function(){
				BS.InsertMagic.Window.clearListeners();
				BsInsertMagicVisualEditorConnector.caller = this;
				BS.InsertMagic.Window.on( 'ok', BsInsertMagicVisualEditorConnector.applyData );

				BS.InsertMagic.Window.setData(
					BsInsertMagicVisualEditorConnector.getData()
				);
				BS.InsertMagic.Window.show( 'bsmagic' );
			}, this);
		}
	});
});

$(document).bind('hwbehavior-_setDisabled', function( event, plugin, setValue, leaveEnabledIds ){
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

var BsInsertMagicWikiTextConnector = {
	
	getData: function() {
		var currentCode = $('#wpTextbox1').textSelection('getSelection');
		var data = {
			code: currentCode
		}
		return data;
	},

	applyData: function( sender, data ) {
		//TODO: not very nice. Maybe use $('#wpTextbox1').textSelection('encapsulateSelection')
		mw.toolbar.insertTags( data.code, '', '', '' );
	}
}

var BsInsertMagicVisualEditorConnector = {
	data: {},
	getData: function() {
		var me = BsInsertMagicVisualEditorConnector;
		var node = me.caller.selection.getNode();
		me.selection = me.caller.selection.getBookmark();
		me.data.id   = node.getAttribute('data-bs-id');
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
		var me = BsInsertMagicVisualEditorConnector;
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

		me.caller.dom.remove(me.caller.selection.getNode()); // remove old node to ensure that new one is not place within
		me.caller.execCommand('mceInsertRawHTML', false, code );
	}
};
