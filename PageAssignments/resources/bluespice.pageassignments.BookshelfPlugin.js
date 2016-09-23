$(document).bind('BSBookshelfUIManagerPanelInit', function( event, sender, oConf, storeFields ){
	storeFields.push( 'assignments' );
	storeFields.push( 'flat_assignments' );

	oConf.columns.push( {
		header: mw.message( 'bs-pageassignments-column-assignments' ).plain(),
		dataIndex: 'flat_assignments',
		filter: {
			type: 'string'
		},
		renderer: function(value, metaData, record, rowIndex, colIndex, store) {
			var assignments = record.get( 'assignments' );
			if( assignments.length === 0 ) {
				return '<em>' + mw.message( 'bs-pageassignments-no-assignments' ).plain() + '</em>';
			}
			var sOut = "<table>\n";
			for(var i = 0; i < assignments.length; i++) {
				sOut = sOut + "<tr><td><span class=\"bs-icon-" + assignments[i]['type'] +"\"></span></td><td>" + assignments[i]['anchor'] + "</td></tr>\n";
			}
			sOut = sOut + "</table>\n";
			return sOut;
		}
	});

	oConf.actions.push(
		{
			iconCls: 'bs-extjs-actioncolumn-icon bs-icon-group progressive',
			glyph: true,
			tooltip: mw.message( 'bs-pageassignments-menu-label' ).plain(),
			handler: function( grid, rowIndex, colIndex, btn, event, record, rowElement ) {
				Ext.require( 'BS.PageAssignments.dialog.PageAssignment', function() {
					var dlg = new BS.PageAssignments.dialog.PageAssignment();
					dlg.on( 'ok', function() {
						grid.getStore().reload();
					} );
					dlg.setData({
						pageId: +record.get( 'page_id' ),
						pageAssignments: record.get( 'assignments' )
					});
					dlg.show();
				});
			}
		}
	);
});