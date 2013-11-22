/**
 * InsertLink Window
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

Ext.define( 'BS.InsertLink.Window', {
	extend: 'BS.Window',
	requires:[
		'Ext.Button'
	],
	id: 'bs-InsertLink-dlg-window',
	modal: true,
	width: 630,
	height: 220,
	layout: 'border',
	singleton: true,
	border: false,
	defaultObj: {
		href: false,
		raw: false,
		content: false,
		type: false,
		selection: false,
		code: false,
		desc: false
	},
	afterInitComponent: function() {
		this.setTitle( mw.message('bs-insertlink-dlg_title').plain() );

		this.storePages = Ext.create( 'Ext.data.JsonStore', {
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

		this.storeNS = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'InsertLink::getNamespace'),
				reader: {
					type: 'json',
					root: 'items'
				}
			},
			autoLoad: true,
			fields: ['name', 'label', 'ns']
		});

		this.fpnlWikiPage     = Ext.create( 'BS.InsertLink.FormPanelWikiPage', { storePages: this.storePages, storeNS: this.storeNS } );
		this.fpnlExternalLink = Ext.create( 'BS.InsertLink.FormPanelExternalLink', { } );
		this.fpnlMailTo       = Ext.create( 'BS.InsertLink.FormPanelMailTo', { } );

		var items = [
			this.fpnlWikiPage,
			this.fpnlExternalLink,
			this.fpnlMailTo,
		]

		if(bsInsertLinkUseFilelinkApplet) {
			this.fpnlFileLink = Ext.create( 'BS.InsertLink.FormPanelFileLink', { } );
			items.push(this.fpnlFileLink);
		}

		$(document).trigger('BsInsertLinkWindowBeforeAddTabs', [this, items]);

		this.pnlTabs = Ext.create( 'Ext.tab.Panel', {
			border: false,
			activeTab: 0,
			region: 'center',
			items: items
		});
		//this.pnlTabs.on('beforetabchange', this.onBeforeTabChange, this);

		this.items = [
			this.pnlTabs
		]

		this.callParent(arguments);
	},
	resetData: function() {
		this.defaultObj = {
			href: false,
			raw: false,
			content: false,
			type: false,
			selection: false,
			code: false,
			desc: false
		}
		for(var i=0; i < this.pnlTabs.items.length; i++) {
			this.pnlTabs.items.getAt(i).resetData();
		}
		this.pnlTabs.un('beforetabchange', this.onBeforeTabChange, this);
	},
	setData: function( obj ) {
		obj = $.extend( this.defaultObj, obj );
		var defaultTab = true;
		for(var i=0; i < this.pnlTabs.items.length; i++) {
			if(this.pnlTabs.items.getAt(i).setData(obj)) {
				this.pnlTabs.setActiveTab(i);
				defaultTab = false;
			}
		}
		if(defaultTab) this.pnlTabs.setActiveTab(0);

		this.pnlTabs.on('beforetabchange', this.onBeforeTabChange, this);

		this.callParent( arguments );
	},
	getData: function() {
		return this.pnlTabs.getActiveTab().getData();
	},
	onBeforeTabChange: function( tabPanel, newCard, oldCard, eOpts ) {
		newCard.setDescription(
			oldCard.getDescription()
		);
	}
});