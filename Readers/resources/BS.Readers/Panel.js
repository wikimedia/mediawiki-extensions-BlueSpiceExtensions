/**
 * UserManager Panel
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage Readers
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */


Ext.define( 'BS.Readers.Panel', {
	extend: 'Ext.grid.Panel',
	requires: [ 'Ext.ux.grid.FiltersFeature', 'BS.store.BSApi' ],
	id: 'bs-readers-panel',
	initComponent: function () {
		this.store = new BS.store.BSApi( {
			apiAction: 'bs-readers-users-store',
			proxy: {
				extraParams: {
					query: mw.config.get("bsReadersTitle")
				}
			},
			fields: [ 'user_image', 'user_name', 'user_page', 'user_page_link',
				'user_readers', 'user_readers_link', 'user_ts', 'user_date' ]
		} );

		this.colUserName = Ext.create( 'Ext.grid.column.Template', {
			id: 'username',
			header: mw.message( 'bs-readers-header-username' ).plain(),
			sortable: true,
			dataIndex: 'user_name',
			tpl: '{user_readers_link} {user_page_link}',
			filterable: true,
			flex: 1
		} );
		this.colUserTs = Ext.create( 'Ext.grid.column.Template', {
			id: 'userts',
			header: mw.message( 'bs-readers-header-ts' ).plain(),
			sortable: true,
			dataIndex: 'user_ts',
			tpl: '{user_date}',
			filter: {
				type: 'date'
			},
			flex: 1
		} );

		this.columns = [
			this.colUserName,
			this.colUserTs
		];

		this.bbar = new Ext.PagingToolbar({
			store : this.store,
			displayInfo : true
		});

		this.features = [
			new Ext.ux.grid.FiltersFeature({
				encode: true
			})
		];

		this.callParent( arguments );
	}
} );
