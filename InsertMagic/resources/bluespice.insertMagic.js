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
			title : mw.message('bs-insertmagic-dlg-title').plain(),
			cmd : 'mceBsMagic',
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
		} else if( text.match(/\{\{(.*?)\}\}/gmi) ) {
			return 'variable';
		} else if( text.match(/__(.*?)__/gmi) ){
			return 'switch';
		} else {
			return false;
		}
	}
};

var BsInsertMagicWikiTextConnector = {
	getData: function() {
		var currentCode = bs.util.selection.save();
		var data = {
			code: currentCode
		};
		return data;
	},

	applyData: function( sender, data ) {
		bs.util.selection.restore( data.code );
	}
};

var BsInsertMagicVisualEditorConnector = {
	data: {},
	getData: function() {
		var me = BsInsertMagicVisualEditorConnector;
		var node = me.caller.selection.getNode();
		//me.selection = me.caller.selection.getBookmark();
		me.data.isInsert = false;
		me.data.id = node.getAttribute('data-bs-id');
		me.data.type = node.getAttribute('data-bs-type');
		me.data.name = node.getAttribute('data-bs-name');
		var currentCode = '';

		//TODO: Laufzeitproblem: onShow ist Store noch nicht unbedingt geladen
		//und Grid nicht unbedingt gerendert. --> selection speichern und
		//StoreOnLoad Fokus und Selection setzen!
		if ( me.data.type == 'template' ) {
			var templates = me.caller.plugins.bswikicode.getTemplateList();
			currentCode = templates[me.data.id];
		} else if ( me.data.type == 'tag' ) {
			var specialtags = me.caller.plugins.bswikicode.getSpecialTagList();
			currentCode = specialtags[me.data.id];
		} else if ( me.data.type == 'switch' ) {
			var switches = me.caller.plugins.bswikicode.getSwitchList();
			currentCode = switches[me.data.id];
		}

		if ( currentCode === '' ) {
			me.data.isInsert = true;
		}

		me.data.code = currentCode;
		return me.data;
	},

	applyData: function(  sender, data ) {
		var me = BsInsertMagicVisualEditorConnector;
		me.bookmark = me.caller.selection.getBookmark();
		me.caller.selection.moveToBookmark( me.bookmark );
		var selectedNode = me.caller.selection.getNode();
		var code = data.code;
		var spanAttrs = {};
		var spanContent = '';

		me.data.type = BsInsertMagicHelper.getTypeFromText( code );
		if ( me.data.type == 'switch' ) {
			var switches = me.caller.plugins.bswikicode.getSwitchList();
			if( me.data.id ) {
				switches[me.data.id] = code;
			} else {
				me.data.id = switches.length;
				switches.push(code);
			}
			spanAttrs = {
				'id': 'bs_switch:@@@SWT'+me.data.id+'@@@',
				'class': 'switch',
				'data-bs-name': me.data.name,
				'data-bs-type': 'switch',
				'data-bs-id': me.data.id
			};
			spanContent = '__ '+me.data.name+' __';
		} else if ( me.data.type == 'variable') {
			var templates = me.caller.plugins.bswikicode.getTemplateList();
			if ( me.data.id ) {
				templates[me.data.id] = code;
			} else {
				me.data.id = templates.length;
				templates.push(code);
			}
			spanAttrs = {
				'id': 'bs_template:@@@TPL'+me.data.id+'@@@',
				'class':'template',
				'data-bs-name': me.data.name,
				'data-bs-type':'template',
				'data-bs-id': me.data.id
			};
			spanContent = '{{ '+me.data.name+' }}';
		} else if ( me.data.type == 'tag' ) {
			var specialtags = me.caller.plugins.bswikicode.getSpecialTagList();
			if( me.data.id ) {
				specialtags[me.data.id] = code;
			} else {
				me.data.id = specialtags.length;
				specialtags.push(code);
			}
			spanAttrs = {
				'id': 'bs_specialtag:@@@ST'+me.data.id+'@@@',
				'class':'tag',
				'data-bs-name': me.data.name,
				'data-bs-type':'tag',
				'data-bs-id': me.data.id
			};
			spanContent = '&lt; '+me.data.name+' &gt;';
		}
		spanAttrs['class'] += ' mceNonEditable';

		var newSpanNode = null;
		if ( selectedNode.nodeName.toLowerCase() == 'span') {
			newSpanNode = me.caller.dom.create( 'span', spanAttrs, spanContent );
			me.caller.dom.replace(newSpanNode, selectedNode);
			//Place cursor to end
			me.caller.selection.select(newSpanNode, false);
		} else {
			if ( me.data.isInsert ) {
				newSpanNode = me.caller.dom.createHTML( 'span', spanAttrs, spanContent );
				me.caller.insertContent(newSpanNode);
			}
		}

		me.caller.selection.collapse(false);
		// remove old node to ensure that new one is not place within
		//me.caller.dom.remove(me.caller.selection.getNode());
		//me.caller.execCommand('mceInsertRawHTML', false, code );
	}
};