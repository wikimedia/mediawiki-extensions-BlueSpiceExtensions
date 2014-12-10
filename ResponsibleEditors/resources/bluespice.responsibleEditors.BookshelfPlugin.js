$(document).bind('BSBookshelfBookManagerInitComponent', function( event, sender, oConf){

	wdRespEd = Ext.create( 'BS.ResponsibleEditors.Dialog' );
	//TODO: register events handler!

	oConf.fields.push( 'editors' );

	oConf.columns.push( {
		header: mw.message( 'bs-responsibleeditors-titleeditors' ).plain(),
		dataIndex: 'editors',
		renderer: function(value, metaData, record, rowIndex, colIndex, store) {
			var sOut = "<ul>\n";
			for(var i=0; i < value.length; i++) {
				sOut = sOut + "<li>" + value[i]['name'] + "</li>\n";
			}
			sOut = sOut + "</ul>\n";
			return sOut;
		}
	});

	oConf.contextMenuItems.push({
		text: mw.message( 'bs-responsibleeditors-cmchangerespeditors' ).plain(),
		iconCls: 'icon-user_edit',
		handler: function( item, event ) {
			var page_id = sender.cmBook.contextRecord.get('page_id');
			if( typeof page_id != 'undefined' ) {
				wdRespEd.loadAndShow( page_id, sender.jstrBookData );
			}
		}
	});

	oConf.actionColumnItems.push(
		{
			icon: wgScriptPath+'/extensions/BlueSpiceExtensions/ResponsibleEditors/resources/images/user_edit.png',
			tooltip: mw.message( 'bs-responsibleeditors-cmchangerespeditors' ).plain(),
			handler: function(grid, rowIndex, colIndex) {
				var page_id = sender.jstrBookData.getAt( rowIndex ).get( 'page_id' );
				if ( typeof page_id != 'undefined' ) {
					wdRespEd.loadAndShow( page_id, sender.jstrBookData );
				}
			}
		}
	);
});