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
		this.setTitle( mw.message('bs-insertlink-tab-wiki-page').plain() );

		this.cbNamespace = Ext.create( 'BS.form.NamespaceCombo', {
			name: 'inputNamespace',
			excludeIds: [bs.ns.NS_MEDIA]
		});
		this.cbNamespace.on('select', this.onCbNamespaceSelect, this);

		this.cbPageName = Ext.create( 'Ext.form.field.ComboBox', {
			store: this.makePageStore(),
			fieldLabel: mw.message('bs-insertlink-label-page').plain(),
			displayField:'name',
			typeAhead: true,
			queryMode: 'local',
			triggerAction: 'all',
			allowBlank: false,
			emptyText:mw.message('bs-insertlink-select-a-page').plain()
		});

		this.pnlMainConf.items = [
			this.cbNamespace,
			this.cbPageName
		];

		this.callParent(arguments);
	},

	makePageStore: function() {
		return Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'InsertLink::getPage' ),
				reader: {
					type: 'json',
					root: 'items'
				}
			},
			autoLoad: true,
			fields: ['name', 'label', 'ns']
		});
	},

	onCbNamespaceSelect: function( field, record ) {
		this.cbPageName.getStore().load({
			params:{ ns: record[0].get('id') }
		});
	},
	resetData: function() {
		this.cbNamespace.reset();
		this.cbPageName.reset();

		this.callParent(arguments);
	},
	setData: function( obj ) {
		var bActive = false;
		var desc = false;

		if( obj.content && obj.content !== '' ) {
			desc = obj.content;
		}

		if ( obj.type && obj.type === this.linktype ) { //VisualEditor
			var link = String( obj.href );
			link = link.replace( wgServer + "/", "" );
			link = unescape(link);

			if( link === desc ) {
				desc = false;
			}

			if ( obj.content.indexOf( '|' ) !== -1 ) {
				var content = obj.content.split( '|' );
				if(content.length > 1 ) {
					desc = content[1];
					desc = desc.replace( ']]', '' );
				} else if ( content[0] !== obj.href ) {
					desc = content[0];
				}
			}
			if ( link.match( ':' ) ) {
				var parts = link.split( ':' );
				if ( parts.length > 2 && parts[0] === '' ) { //[[:Category:Title]]
					parts.shift();
				}

				var namespace = parts.shift();
				//Check if it is a available namespace or part of the title
				var normNsText = namespace.toLowerCase().replace(' ', '_' );
				var nsId = wgNamespaceIds[normNsText];
				if ( !nsId ) {
					this.cbPageName.setValue( namespace + ":" + parts.join( ':' ) );
				} else {
					this.cbNamespace.setValue( nsId );
					this.cbPageName.setValue( parts.join( ':' ) );
				}
			} else {
				this.cbPageName.setValue( link );
			}
			bActive = true;
		}
		else if( obj.code !== false ) { //WikiText editor
			if( obj.code.match(/\[\[[^\]]*\]\]/) ) {
				if( obj.code.indexOf("[[:") === 0 ) { //[[:Category:Title]]
					obj.code = '[[' + obj.code.substring( 3, obj.code.length );
				}
				var link = new bs.wikiText.Link(obj.code);

				this.cbPageName.setValue( link.getTitle() );
				this.cbNamespace.setValue( link.getNsText() );
				if( link.getTitle() !== link.getDisplayText() ) {
					desc = link.getDisplayText();
				}
				bActive = true;
			} else {
				desc = obj.code; //Just the selection made by the user
			}
		}

		this.callParent( [{desc: desc}] );
		return bActive;
	},
	getData: function() {
		var title = this.callParent();

		var desc = '';
		if ( title !== '' ) {
			desc = '|'+title;
		}

		var ns = '';
		var nsIndex = this.cbNamespace.getValue();
		if( nsIndex !== bs.ns.NS_MAIN ) {
			var ns = wgFormattedNamespaces[nsIndex]+':';
		}

		// Escape Category namespace (people want to link to the category page,
		// not assign a category
		if( nsIndex === bs.ns.NS_CATEGORY ) { //[[:Category:Title]]
			ns = ':' + ns;
		}

		//var href = '';
		var page = '';
		if( this.cbPageName.getValue() ) {
			page = this.cbPageName.getValue();
			//href = mw.util.wikiGetlink(ns+page);
		}

		var code = '[[' + ns + page + desc + ']]';
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