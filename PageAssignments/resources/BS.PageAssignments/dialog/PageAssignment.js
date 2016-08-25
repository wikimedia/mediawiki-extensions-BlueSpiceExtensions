Ext.define( 'BS.PageAssignments.dialog.PageAssignment', {
	extend: 'BS.Window',
	requires: [ 'BS.form.field.ItemList', 'BS.PageAssignments.action.ApiTaskEdit' ],
	title: mw.message('bs-pageassignments-dlg-title').plain(),

	makeItems: function() {
		this.itmList = new BS.form.field.ItemList({
			labelAlign: 'top',
			model: 'BS.PageAssignments.model.Assignable',
			apiStore: 'bs-pageassignable-store',
			apiFields: [ 'text', 'type', 'id', 'anchor' ]
		});

		return [
			this.itmList
		];
	},

	onBtnOKClick: function() {
		var me = this;
		me.setLoading( true );

		var assignees = this.itmList.getValue();
		var assigneeIds = [];
		for( var i = 0; i < assignees.length; i++ ) {
			assigneeIds.push( assignees[i].id );
		}

		var action = new BS.PageAssignments.action.ApiTaskEdit({
			pageId: this.currentData.pageId,
			pageAssignments: assigneeIds
		});

		var $dfd = action.execute();
		$dfd.fail(function( sender, data, resp ){
				bs.util.alert(
					'bs-pa-error',
					{
						text: resp.message
					}
				);
				me.setLoading( false );
			})
			.done(function( sender ){
				me.setLoading( false );
				if ( me.fireEvent( 'ok', me, action ) ) {
					me.close();
				}
			});
	},

	setData: function( data ) {
		this.itmList.setValue( data.pageAssignments );
		this.callParent(arguments);
	}
} );