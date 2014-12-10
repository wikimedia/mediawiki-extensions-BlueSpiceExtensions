/**
 * InsertLink external link panel
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

Ext.define( 'BS.InsertLink.FormPanelExternalLink', {
	extend: 'BS.InsertLink.FormPanelBase',
	protocols : [
		'http://',
		'https://',
		'//'
	],
	linktype: 'external_link',
	origLabel: '',
	beforeInitComponent: function() {
		this.setTitle( mw.message('bs-insertlink-tab-ext-link').plain() );

		this.tfTargetUrl = Ext.create( 'Ext.form.field.Text', {
			name: 'inputTargetUrl',
			fieldLabel: mw.message('bs-insertlink-label-link').plain(),
			value: 'http://',
			allowBlank: false
		});

		this.tfTargetUrl.on('focus', this.onTargetUrlFocus, this);
		this.tfTargetUrl.on('change', this.onTargetUrlChange, this);

		this.pnlMainConf.items = [];
		this.pnlMainConf.items.push(this.tfTargetUrl);

		this.callParent(arguments);
	},
	onTargetUrlFocus: function( field ) {
		if ( field.getValue() == '' ) {
			field.setValue('http://');
			//set the cursor at the end, otherwise you'll write at the start of the line
			field.selectText(field.getValue().length);
		}
	},
	onTargetUrlChange: function( field, newValue, oldValue ) {
		for( var i = 0; i < this.protocols.length; i++ ) {
			if( newValue.match( 'http://' + this.protocols[i] ) ) {
				field.setValue( newValue.replace(
					'http://' + this.protocols[i],
					this.protocols[i] )
				);
			}
		}
	},
	resetData: function() {
		this.tfTargetUrl.setValue('');

		this.callParent();
	},
	setData: function( obj ) {
		var bAcitve = false;
		var desc = false;

		if ( obj.href ) {
			for ( var i = 0; i < this.protocols.length; i++ ) {
				if ( String( obj.href ).indexOf( this.protocols[i] ) === 0 ) {
					if ( String( obj.href ) !== String( obj.content ) ) {
						if ( obj.content.match( /\[\d\]/ ) === null ) {
							desc = obj.content;
						} else {
							this.origLabel = obj.content;
						}
					}
					var link = String(obj.href);//.replace(this.protocols[i], "");
					this.tfTargetUrl.setValue( unescape(link) );
					bAcitve = true;
					break;
				}
			}
		} else if( obj.code != false ) {
			if( obj.code.match(/\[[^\]]*\]/) && !obj.code.match(/\[\[[^\]]*\]\]/) ) {
				var link = new bs.wikiText.ExternalLink(obj.code);
				if( $.inArray(link.getProtocol(),this.protocols) > -1 ) {
					this.tfTargetUrl.setValue( link.toLink() );
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
		var title = this.callParent().trim();

		var desc = '';
		if( title != '' ) {
			desc = ' '+title;
		} else {
			if ( this.origLabel === '' ) {
				title = '[1]';
			} else {
				title = this.origLabel;
			}
		}

		var href = '';
		var target = '';
		if( this.tfTargetUrl.getValue() ) {
			target = this.tfTargetUrl.getValue();
		}

		return {
			title: title,
			href: target,
			type: this.linktype,
			code: '['+target + desc+']'
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