Ext.define( 'BS.PageAssignments.panel.Overview', {
	extend: 'Ext.grid.Panel',
	requires: [ 'BS.store.BSApi', 'Ext.ux.grid.FiltersFeature' ],

	initComponent: function() {
		var storeFields = [ 'page_id', 'page_prefixedtext', 'page_link', 'assigned_by'];

		var cols = [
			{
				text: mw.message('bs-pageassignments-column-title').plain(),
				dataIndex: 'page_prefixedtext',
				sortable: true,
				filterable:true,
				renderer: function( value, metaData, record, rowIndex, colIndex, store, view ) {
					return record.get('page_link');
				}
			},
			{
				text: mw.message('bs-pageassignments-column-assignedby').plain(),
				dataIndex: 'assigned_by',
				sortable: true,
				filterable:true,
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
			}
		];

		$(document).trigger('BSPageAssignmentsOverviewPanelInit', [ this, cols, storeFields, this._actions ]);

		this.columns = {
			items: cols,
			defaults: {
				flex: 1
			}
		};

		this.store = new BS.store.BSApi({
			apiAction: 'bs-mypageassignment-store',
			fields: storeFields
		});

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
});