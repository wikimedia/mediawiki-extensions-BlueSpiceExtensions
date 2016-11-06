Ext.define( 'BS.PageAssignments.panel.Manager', {
	extend: 'BS.CRUDGridPanel',
	requires: [ 'BS.store.BSApi' ],

	initComponent: function() {

		this._gridCols = [
			{
				text: mw.message('bs-pageassignments-column-title').plain(),
				dataIndex: 'page_prefixedtext',
				sortable: true,
				filterable:true,
				renderer: function( value, metaData, record, rowIndex, colIndex, store, view ) {
					var title = new mw.Title( value );
					return mw.html.element(
						'a',
						{
							'href': title.getUrl(),
							'data-bs-title': value,
							'target': '_blank'
						},
						value
					);
				}
			},
			{
				text: mw.message('bs-pageassignments-column-assignments').plain(),
				dataIndex: 'assignments',
				sortable: true,
				filterable:true,
				renderer: function( value, metaData, record, rowIndex, colIndex, store, view ) {
					var html = [];
					for( var i = 0; i < value.length; i++ ) {
						html.push( "<span class=\'bs-icon-"+value[i].type+" bs-typeicon\'></span>" + value[i].anchor );
					}
					if( html.length === 0 ) {
						html.push( '<em>' + mw.message('bs-pageassignments-no-assignments').plain() + '</em>' );
					}

					return html.join(', ');
				}
			}
		];

		this._storeFields = [ 'page_id', 'page_prefixedtext', 'page_link', 'assignments' ];
		this._actions = [{
			iconCls: 'bs-icon-text bs-extjs-actioncolumn-icon',
			glyph: true,
			tooltip: mw.message('bs-pageassignments-action-log').plain(),
			handler: function( view, rowIndex, colIndex,item, e, record, row ) {
				window.open(
					bs.util.wikiGetlink( {
						page: record.get( 'page_prefixedtext' ),
						type: 'bs-pageassignments'
					}, 'Special:Log' )
				);
			},
			scope: this
		}];

		$(document).trigger('BSPageAssignmentsManagerPanelInit', [ this, this._gridCols, this._storeFields, this._actions ]);

		this.callParent( arguments );
	},

	makeGridColumns: function(){
		this.colMainConf.columns = this._gridCols;
		return this.callParent( arguments );
	},

	makeRowActions: function() {
		this.callParent( arguments );

		for( var i = 0; i < this._actions.length; i++ ) {
			this.colMainConf.actions.push( this._actions[i] );
		}

		return this.colMainConf.actions;
	},

	makeMainStore: function() {
		this.strMain = new BS.store.BSApi({
			apiAction: 'bs-pageassignment-store',
			fields: this._storeFields
		});
		return this.callParent( arguments );
	},

	makeTbarItems: function() {
		this.callParent( arguments );
		return [
			//this.btnAdd,
			this.btnEdit,
			this.btnRemove
		];
	},

/* //For future use
	onBtnAddClick: function( oButton, oEvent ) {
		this.callParent( arguments );
	},
*/
	onBtnEditClick: function( oButton, oEvent ) {
		var records = this.grdMain.getSelectionModel().getSelection();
		var record = records[0]; //ATM there is no MULTI selection model
		var dlg = Ext.create( 'BS.PageAssignments.dialog.PageAssignment' );
		dlg.on( 'ok', function() {
			this.strMain.reload();
		}, this );
		dlg.setData({
			pageId: record.get( 'page_id' ),
			pageAssignments: record.get( 'assignments' )
		});
		dlg.show();
		this.callParent( arguments );
	},

	onBtnRemoveClick: function( oButton, oEvent ) {
		var records = this.grdMain.getSelectionModel().getSelection();
		bs.util.confirm( 'bs-pa-remove', {
			textMsg: 'bs-pageassignments-action-delete-confirm'
		}, {
			ok: function() {
				var me = this;
				//TODO: use batch actions dialog
				for( var i = 0; i < records.length; i++ ) {
					bs.api.tasks.exec( 'pageassignment', 'edit', {
						pageId: records[i].get( 'page_id' ),
						pageAssignments: []
					} )
					.done(function() {
						me.strMain.reload();
					});
				}
			},
			scope: this
		});
		this.callParent( arguments );
	}
});