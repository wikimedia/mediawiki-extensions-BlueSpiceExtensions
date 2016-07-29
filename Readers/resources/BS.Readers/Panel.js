/**
 * UserManager Panel
 *
 * Part of BlueSpice for MediaWiki
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
	id: 'bs-readers-panel',
	initComponent: function () {
		this.store = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-readers-users-store',
			proxy: {
				extraParams: {
					query: mw.config.get("bsReadersTitle")
				}
			},
			fields: [ 'user_image', 'user_name', 'user_page', 'user_readers', 'user_ts', 'user_date' ]
		} );

		this.colUserName = Ext.create( 'Ext.grid.column.Template', {
			id: 'username',
			header: mw.message( 'bs-readers-header-username' ).plain(),
			sortable: true,
			dataIndex: 'user_name',
			tpl: '<a href="{user_page}">{user_name}</a>',
			filterable: true,
			flex: 1
		} );
		this.colReadersPage = Ext.create( 'Ext.grid.column.Template', {
			id: 'userreaderspage',
			header: mw.message( 'bs-readers-header-readerspath' ).plain(),
			sortable: true,
			dataIndex: 'user_readers',
			tpl: '<a href="{user_readers}">{user_name}</a>',
			flex: 1
		} );
		this.colUserTs = Ext.create( 'Ext.grid.column.Template', {
			id: 'userts',
			header: mw.message( 'bs-readers-header-ts' ).plain(),
			sortable: true,
			dataIndex: 'user_ts',
			tpl: '{user_date}',
			flex: 1
		} );

		this.columns = [
			this.colUserName,
			this.colReadersPage,
			this.colUserTs
		];
		this.callParent( arguments );
	}
} );
