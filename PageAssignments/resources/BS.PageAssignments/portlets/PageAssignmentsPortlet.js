/**
 * ExtendedSearch extension
 *
 * @author     Wirth Patric <Wirth@hallowelt.com>
 * @version    2.27.0
 * @package    Bluespice_Extensions
 * @subpackage PageAssignments
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.PageAssignments.portlets.PageAssignmentsPortlet', {
	extend: 'BS.portal.GridPortlet',
	requires: [ 'BS.store.BSApi' ],
	portletConfigClass:
		'BS.PageAssignments.portlets.PageAssignmentsPortletConfig',

	beforeInitComponent: function() {
		this.store = new BS.store.BSApi({
			apiAction: 'bs-mypageassignment-store',
			fields: [
				'page_id',
				'page_prefixedtext',
				'page_link',
				'assigned_by'
			]
		});
		this.gdMainConfig = {
			store: this.store,
			columns: [{
				text : mw.message('bs-pageassignments-column-title').plain(),
				dataIndex: 'page_link',
				width: '40%'
			},{
				text : mw.message('bs-pageassignments-column-assignedby').plain(),
				dataIndex: 'assigned_by',
				sortable: false,
				width: '60%',
				renderer: function( value, metaData, record, rowIndex, colIndex, store, view ) {
					var html = [];
					for( var id in value ) {
						if( value[id].type === 'user' ) {
							html.push( '<em>' +  mw.message('bs-pageassignments-directly-assigned').plain() + '</em>' );
						}
						else {
							html.push( "<span class=\'bs-icon-"+value[id].type+" bs-typeicon\'></span>" + value[id].anchor );
						}
					}
					return html.join(', ');
				}
			}]
		};
	},
	initComponent: function() {
		this.callParent(arguments);
	},

	setPortletConfig: function( cfg ) {
		this.callParent(arguments);
		this.store.reload();
	}
} );