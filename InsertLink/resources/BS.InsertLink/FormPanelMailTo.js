/**
 * InsertLink mail to panel
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

Ext.define( 'BS.InsertLink.FormPanelMailTo', {
	extend: 'BS.InsertLink.FormPanelBase',
	protocols: ['mailto:'],
	beforeInitComponent: function() {
		this.setTitle( mw.message('bs-insertlink-tab-email').plain() );
		
		this.tfTargetMail = Ext.create( 'Ext.form.field.Text', {
			name: 'inputTargetMail',
			fieldLabel: mw.message('bs-insertlink-label-mail').plain(),
			value: '',
			allowBlank: false
		});

		this.pnlMainConf.items = [];
		this.pnlMainConf.items.push(this.tfTargetMail);

		this.callParent(arguments);
	},
	resetData: function() {
		this.tfTargetMail.reset();

		this.callParent();
	},
	setData: function( obj ) {
		var bAcitve = false;
		var desc = false;
		if(obj.href) {
			if( String(obj.href).indexOf(this.protocols[0]) > -1 ) {
				if(String(obj.href) !== String(obj.content)) {
					desc = obj.content;
				}
				var link = String(obj.href).replace(this.protocols[0], "");
				this.tfTargetMail.setValue( unescape(link) );
				bAcitve = true;
			}
		} else if( obj.code ) {
			if( obj.code.match(/\[[^\]]*\]/) && !obj.code.match(/\[\[[^\]]*\]\]/) ) {
				var link = new bs.wikiText.ExternalLink(obj.code);
				if( $.inArray(link.getProtocol(),this.protocols) > -1 ) {
					this.tfTargetMail.setValue( link.getTarget() );
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
		if( this.tfTargetMail.getValue() ) {
			target = this.tfTargetMail.getValue();
		}

		return { 
			title: title,
			href: 'mailto:' + target,
			type: '',
			code: '[mailto:' + target + desc + ']'
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