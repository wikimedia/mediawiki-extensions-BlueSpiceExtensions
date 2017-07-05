Ext.define( 'BS.UsageTracker.panel.Manager', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi' ],

	initComponent: function() {

		this._gridCols = [
			{
				text: mw.message( 'bs-usagetracker-col-identifier' ).plain(),
				dataIndex: 'identifier',
				sortable: true,
				filterable: true,
				flex: 1
			},
			{
				text: mw.message( 'bs-usagetracker-col-desc' ).plain(),
				dataIndex: 'description',
				sortable: true,
				filterable: true,
				width: '50%'
			},
			{
				text: mw.message( 'bs-usagetracker-col-last-updated' ).plain(),
				dataIndex: 'updateDate',
				sortable: true,
				filterable: false,
				filter: {
					type: 'date'
				},
				flex: 1
			},
			{
				text: mw.message( 'bs-usagetracker-col-count' ).plain(),
				dataIndex: 'count',
				sortable: true,
				filter: {
					type: 'int'
				},
				filterable: true,
				width: '20px'
			}
		];

		this._storeFields = [
			'identifier',
			'description',
			'descriptionKey',
			'updateDate',
			'count',
			'type'
		];

		this.callParent( arguments );
	},

	makeGridColumns: function(){
		this.colMainConf.columns = this._gridCols;
		return this.colMainConf.columns;
		return this.callParent( arguments );
	},

	makeRowActions: function() {
		return [];
	},

	makeMainStore: function() {
		this.strMain = new BS.store.BSApi({
			apiAction: 'bs-usagetracker-store',
			fields: this._storeFields
		});
		return this.callParent( arguments );
	},

	makeTbarItems: function() {
		return [];
	}
});