//This script injects fields, columns and contet menu items into the biz.hallowelt.Bookshelf.BookManager
Ext.namespace('biz', 'biz.hallowelt', 'biz.hallowelt.ResponsibleEditors');

//HINT (Legacy, but okay): http://www.sencha.com/learn/legacy/Tutorial:Writing_Ext_2_Plugins
biz.hallowelt.ResponsibleEditors.BookshelfPlugin = function( oConfig ) {
	 Ext.apply(this, oConfig);
}

Ext.extend( biz.hallowelt.ResponsibleEditors.BookshelfPlugin, Ext.util.Observable, {
	oBookManager: null,
	wdChange: null,

	init: function( oBookManger ) {
	this.oBookManger = oBookManger;
	this.wdChange = new biz.hallowelt.ResponsibleEditors.AssignmentWindow( {
		renderTo: 'bs-responsibleeditors-assignmentwindow'
	} );
	},

	modifyConfig: function( oConf ) {
		oConf.fields.push( 'editors' );
		oConf.columns.push(
		{
			header: mw.message('bs-responsibleeditors-titleEditors').plain(),
			dataIndex: 'editors',
			renderer: this.renderEditors
		}
		);
		oConf.contextMenuItems.push(
		{ 
			text: mw.message('bs-responsibleeditors-cmChangeRespEditors').plain(), 
			iconCls: 'icon-user_edit',
			handler: this.onChangeEditorsClicked,
			scope: this
		}
		);

		oConf.actionColumnItems.push(
		{
			icon: wgScriptPath+'/extensions/BlueSpiceExtensions/ResponsibleEditors/resources/images/user_edit.png',
			tooltip: mw.message('bs-responsibleeditors-cmChangeRespEditors').plain(),
			handler: this.onChangeEditorsActionColumnItemClicked,
			scope: this
		}
		);
	},

	renderEditors: function(value, metaData, record, rowIndex, colIndex, store) {
	var sOut = "<ul>\n";
	for(var i=0; i < value.length; i++) {
		sOut = sOut + "<li>" + value[i]['name'] + "</li>\n";
	}
	sOut = sOut + "</ul>\n";
	return sOut;
	},

	onChangeEditorsClicked: function( item, event ) {
	var page_id = this.oBookManger.cmBook.contextRecord.get('page_id');
	if( typeof page_id != 'undefined' ) {
		this.wdChange.loadAndShow( page_id, this.oBookManger.jstrBookData );
	}
	},

	onChangeEditorsActionColumnItemClicked: function(grid, rowIndex, colIndex) {
	var page_id = this.oBookManger.jstrBookData.getAt(rowIndex).get('page_id');
	if( typeof page_id != 'undefined' ) {
		this.wdChange.loadAndShow( page_id, this.oBookManger.jstrBookData );
	}
	}
});