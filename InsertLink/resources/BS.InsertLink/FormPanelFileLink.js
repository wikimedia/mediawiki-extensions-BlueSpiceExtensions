/**
 * InsertLink file link panel
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage InsertLink
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.InsertLink.FormPanelFileLink', {
	extend: 'BS.InsertLink.FormPanelBase',
	protocols: ['file:///'],
	beforeInitComponent: function() {
		this.setTitle( mw.message('bs-insertlink-tab-ext-file').plain() );
		this.on( 'beforeactivate', function(){
			var applet = $("<applet>");
			applet.attr('id', 'BsFileChooserApplet');
			applet.attr('name', 'BsFileChooserApplet');
			applet.attr('archive', wgScriptPath + '/extensions/BlueSpiceExtensions/InsertLink/vendor/BsFileChooserApplet.jar');
			applet.attr('code', 'biz.hallowelt.InsertLink.BsFileChooserApplet.class');
			applet.attr('width', '1');
			applet.attr('height', '1');
			applet.attr('mayscript', 'mayscript');
			$("body").append(applet);
		}, this);

		this.tfTargetUrl = Ext.create( 'Ext.form.field.Text', {
			id: 'BSInserLinkTargetUrl',
			name: 'inputTargetUrl',
			fieldLabel: mw.message('bs-insertlink-label-file').plain(),
			value: '',
			width: '75%'
		});
		this.btnSearchFile = Ext.create( 'Ext.button.Button', {
			id: 'inputSearchFile',
			text: mw.message('bs-insertlink-label-searchfile').plain(),
			handler: function() {
				try {
					var result = document.getElementById( 'BsFileChooserApplet' ).openDialog(mw.message( "bs-insertlink-select-a-page" ).plain());
				} catch ( e ) {
					bs.util.alert( 'bs-insertlink-no-applet',
							{
								text: mw.message( "bs-insertlink-no-applet" ).plain(),
								titleMsg: 'bs-extjs-error'
							}
					);
				}
				result = $.parseJSON( result );
				if ( result.success )
					this.tfTargetUrl.setValue( result.path );
			},
			scope: this,
			width: '25%'
		});

		this.fcTargetFields = Ext.create('Ext.form.FieldContainer', {
			 layout: 'hbox'
		});
		this.fcTargetFields.add(this.tfTargetUrl);
		this.fcTargetFields.add(this.btnSearchFile);

		this.pnlMainConf.items = [];
		this.pnlMainConf.items.push(this.fcTargetFields);

		this.callParent(arguments);
	},
	resetData: function() {
		this.tfTargetUrl.reset();

		this.callParent();
	},
	setData: function( obj ) {
		var bAcitve = false;
		var desc = false;

		if(obj.href) {
			if (String(obj.href).indexOf('file:///')>-1) {
				if(String(obj.href) !== String(obj.content)) {
					desc = obj.content;
				}
				var link = String(obj.href).replace("file:///", "");
				this.tfTargetUrl.setValue( unescape(link) );
				bAcitve = true;
			}
		} else if( obj.code !== false ) {
			if( obj.code.match(/\[[^\]]*\]/) && !obj.code.match(/\[\[[^\]]*\]\]/) ) {
				var link = new bs.wikiText.ExternalLink(obj.code);
				if( $.inArray(link.getProtocol(),this.protocols) > -1 ) {
					this.tfTargetUrl.setValue( link.getTarget() );
					if( link.getDisplayText() != '' && link.getDisplayText() != link.toLink() ) {
						desc = link.getDisplayText();
					}
					bAcitve = true;
				}
			} else {
				desc = obj.code;
			}
		} else if( obj.content && obj.content != '' ) desc = obj.content;

		this.callParent( [{desc: desc}] );
		return bAcitve;
	},
	getData: function() {
		var title = this.callParent();

		var desc = '';
		if( title != '' ) {
			desc = ' '+title;
		}

		var href = '';
		var target = '';
		if( this.tfTargetUrl.getValue() ) {
			target = this.tfTargetUrl.getValue();
		}

		target = target.replace(/\\/g, '/');
		target = target.replace(/ /g, '%20');

		return {
			title: title,
			href: 'file:///' + target,
			type: '',
			code: '[file:///' + target + desc + ']'
			//'class': ''
		};
	},
	setDescription: function( desc ) {
		this.callParent(arguments);
	},
	getDescription: function() {
		return this.callParent();
	}
});
