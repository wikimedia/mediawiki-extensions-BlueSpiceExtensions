/**
 * InsertLink internal link panel
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

Ext.define( 'BS.InsertLink.FormPanelWikiPage', {
	extend: 'BS.InsertLink.FormPanelBase',
	linktype: 'internal_link',
	beforeInitComponent: function() {
		this.setTitle( mw.message('bs-insertlink-tab_wiki_page').plain() );

		this.cbNamespace = Ext.create( 'Ext.form.field.ComboBox', {
			store: this.storeNS,
			displayField:'name',
			fieldLabel: mw.message('bs-insertlink-label_namespace').plain(),
			name: 'inputNamespace',
			typeAhead: true,
			queryMode: 'local',
			triggerAction: 'all',
			forceSelection: true,
			width: 600,
			emptyText: mw.message('bs-insertlink-select_a_namespace').plain()
		});
		this.cbNamespace.on('select', this.onCbNamespaceSelect, this);

		this.cbPageName = Ext.create( 'Ext.form.field.ComboBox', {
			store: this.storePages,
			fieldLabel: mw.message('bs-insertlink-label_page').plain(),
			displayField:'name',
			typeAhead: true,
			queryMode: 'local',
			triggerAction: 'all',
			width: 600,
			allowBlank: false,
			emptyText:mw.message('bs-insertlink-select_a_page').plain()
		});

		this.pnlMainConf.items = [
			this.cbNamespace,
			this.cbPageName
		];

		this.callParent(arguments);
	},
	onCbNamespaceSelect: function( field, record ) {
		this.storePages.load({
			params:{ ns: record[0].get('ns') }
		});
	},
	resetData: function() {
		this.cbNamespace.reset();
		this.cbPageName.reset();

		this.callParent(arguments);
	},
	setData: function( obj ) {
		var bAcitve = false;
		var desc = false;

		if ( obj.type && obj.type == this.linktype ) {
			var link = String( obj.href );
			link = link.replace( wgServer+"/", "" );
			link = unescape(link);

			if ( obj.content.indexOf( '|' )!== -1 ) {
				var content = obj.content.split( '|' );
				if(content.length > 1 ) {
					desc = content[1];
					desc = desc.replace( ']]', '' );
				} else if ( content[0] != obj.href ) {
					desc = content[0];
				}
			}
			if ( link.match( ':' ) ) {
				var parts = link.split( ':' );
				if ( parts.length === 3 && parts[0] === ":" ) parts.shift();

				var namespace = parts.shift();
				if ( this.storeNS.findRecord( 'label', namespace ) == null ) {
					this.cbPageName.setValue( namespace + ":" + parts.join( ':' ) );
				} else {
					this.cbNamespace.setValue( namespace );
					this.cbPageName.setValue( parts.join( ':' ) );
				}
			} else {
				this.cbPageName.setValue( link );
			}
			bAcitve = true;
		} else if( obj.code !== false ) {
			if( obj.code.match(/\[\[[^\]]*\]\]/) ) {
				if( obj.code.indexOf("[[:") === 0 ) { //[[:Category:Title]]
					obj.code = '[[' + obj.code.substring( 3, obj.code.length );
				}
				var link = new bs.wikiText.Link(obj.code);

				this.cbPageName.setValue( link.getTitle() );
				this.cbNamespace.setValue( link.getNsText() );
				if( link.getTitle() != link.getDisplayText() ) {
					desc = link.getDisplayText();
				}
				bAcitve = true;
			} else {
				desc = obj.code;
			}
		} else if( obj.content && obj.content != '' ) {
			desc = obj.content;
		}

		this.callParent( [{desc: desc}] );
		return bAcitve;
	},
	getData: function() {
		var title = this.callParent();

		var desc = '';
		if ( title != '' ) {
			desc = '|'+title;
		}

		var ns = '';
		if( this.cbNamespace.getValue() ) {
			var index = this.cbNamespace.store.find( 'label', this.cbNamespace.getValue() );
			if( this.cbNamespace.store.getAt(index).get('ns') != 0 ) {
				var ns = this.cbNamespace.getValue() + ':';
			}
		}

		//var href = '';
		var page = '';
		if( this.cbPageName.getValue() ) {
			page = this.cbPageName.getValue();
			//href = mw.util.wikiGetlink(ns+page);
		}

		// Escape Kategory namespace (people want to link to the category page, not assign a category
		if( this.cbNamespace.getValue() && this.cbNamespace.getValue() == bs.util.getNamespaceText(14) ) { //[[:Category:Title]]
			ns = ':' + ns;
		}
		
		var code = ns + page + desc + ']]';
		code = '[[' + code;
		return { 
			title: title,
			href: ns+page,
			type: this.linktype,
			code: code
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