Ext.define( 'BS.ExtensionInfo.Panel', {
	extend: 'Ext.grid.Panel',

	initComponent: function() {
		this.store = Ext.create('Ext.data.Store', {
			fields:[ {name: 'name'},
				{name: 'version'},
				{name: 'description'},
				{name: 'status'},
				{name: 'package'}
			],
			groupField:'package',
			data: aExtensionInfo
		});

		this.columns = [
			{
				id: 'name',
				header: mw.message('bs-extensioninfo-headerextname').plain(),
				sortable: true,
				dataIndex: 'name',
				renderer: this.renderName,
				groupable: false,
				width: 250
			},{
				header: mw.message('bs-extensioninfo-headerdesc').plain(),
				sortable: false,
				dataIndex: 'description',
				groupable: false,
				flex: 1
			}, {
				header: mw.message('bs-extensioninfo-headerversion').plain(),
				sortable: true,
				dataIndex: 'version',
				groupable: false,
				width: 100
			}, {
				id: 'status',
				header: mw.message('bs-extensioninfo-headerstatus').plain(),
				sortable: false,
				dataIndex: 'status',
				renderer: this.renderStatus,
				width: 75
			}, {
				id: 'package',
				header: mw.message('bs-extensioninfo-headerpackage').plain(),
				sortable: false,
				dataIndex: 'package',
				renderer: this.renderStatus,
				width: 120
			}
		];

		this.features = [
			Ext.create('Ext.grid.feature.Grouping',{
				groupHeaderTpl: '{text} ({[values.rows.length]} ' + mw.message( 'bs-extensioninfo-groupingtemplateviewtext', '{[values.rows.length]}' ).text() + ')'
			})
		];

		this.callParent( arguments );
	},


	/**
	 * Renders the name of an extension
	 * @param array aValue An array with name => url
	 * @return string The rendered link to the extension helpdesk entry
	 */
	renderName: function( aValue ) {
		return '<a href=\"'+aValue[1]+'\" title=\"'+aValue[0]+'\" target="_blank">'+aValue[0]+'</a>';
	},

	/**
	 * Renders the status of an extension
	 * @param string sValue The value of the status field
	 * @return string The rendered HTML of a status of an extension
	 */
	renderStatus: function( sValue ) {
		var sCssClass = 'undefined';
		if (sValue == 'alpha') {
			sCssClass = 'alpha';
		} else if (sValue == 'beta') {
			sCssClass = 'beta';
		} else if ( sValue == 'stable') {
			sCssClass = 'stable';
		}
		return '<span class="'+ sCssClass +'">' + sValue + '</span>';
	}
});