/**
 * Readers path Panel
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

Ext.define( 'BS.Readers.PathPanel', {
	extend: 'Ext.grid.Panel',
	id: 'bs-readers-pathpanel',
	initComponent: function () {
		this.store = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-readers-data-store',
			proxy: {
				extraParams: {
					query: mw.config.get("bsReadersUserID")
				}
			},
			fields: [ 'pv_page', 'pv_page_title', 'pv_ts' ]
		} );

		this.colPage = Ext.create( 'Ext.grid.column.Template', {
			id: 'pvpage',
			header: mw.message( 'bs-readers-header-page' ).plain(),
			sortable: true,
			dataIndex: 'pv_page',
			tpl: '<a href="{pv_page}">{pv_page_title}</a>',
			filterable: true,
			flex: 1
		} );
		this.colTs = Ext.create( 'Ext.grid.column.Template', {
			id: 'pvts',
			header: mw.message( 'bs-readers-header-ts' ).plain(),
			sortable: true,
			dataIndex: 'pv_ts',
			tpl: '{pv_ts}',
			flex: 1
		} );

		this.columns = [
			this.colPage,
			this.colTs
		];

		this.callParent( arguments );
	}
} );