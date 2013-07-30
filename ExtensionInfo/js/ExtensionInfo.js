/**
 * ExtensionInfo extension
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    $Id: ExtensionInfo.js 9405 2013-05-16 06:41:50Z rvogel $
 * @package    Bluespice_Extensions
 * @subpackage ExtensionInfo
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

//Hint: http://dev.sencha.com/deploy/dev/examples/grid/grouping.html

/**
 * Base class for all ExtensionInfo related methods and properties
 */
BsExtensionInfo = {
	/**
	 * Basic canvas to be rendered to
	 * @var Ext.grid.GridPanel
	 */
	panel: false,
	/**
	 * Store for template data, displayed in grid. Note, this is a grouping store in order to group alpha, beta and stable extensions
	 * @var Ext.data.GroupingStore
	 */
	store: new Ext.data.GroupingStore({
		reader: new Ext.data.ArrayReader({}, [
			{name: 'name'},
			{name: 'version'},
			{name: 'description'},
			{name: 'status'}
		]),
		sortInfo: {
			field: 'name',
			direction: 'ASC'
		},
		groupField:'status'
	}),
	/**
	 * Renders the extension info grid and fills it with data
	 */
	show: function() {
		if(!this.panel) {
			this.panel = new Ext.grid.GridPanel({
				renderTo: 'bs-extensioninfo-grid',
				store: this.store,
				colModel: new Ext.grid.ColumnModel({
				columns: [
					{
						id: 'name',
						header: mw.msg('bs-extensioninfo-headerExtensionname'),
						sortable: true,
						dataIndex: 'name',
						renderer: this.renderName,
						width: 50
					},{
						header: mw.msg('bs-extensioninfo-headerDescription'),
						sortable: false,
						dataIndex: 'description'
					}, {
						header: mw.msg('bs-extensioninfo-headerVersion'),
						sortable: true,
						dataIndex: 'version',
						width: 25
					}, {
					id: 'status',
						header: mw.msg('bs-extensioninfo-headerStatus'),
						sortable: false,
						dataIndex: 'status',
						renderer: this.renderStatus,
						width: 15
					}
				]
				}),

				viewConfig: {
					forceFit: true
				},
				sm: new Ext.grid.RowSelectionModel({
					singleSelect: true
				}),
				view:new Ext.grid.GroupingView({
					forceFit:true,
					groupTextTpl: '{text} ({[values.rs.length]} {[values.rs.length > 1 ? "'
						+ mw.msg("bs-extensioninfo-groupingTemplateViewTextPlural")
						+'" : "'
						+ mw.msg("bs-extensioninfo-groupingTemplateViewTextSingular") +'"]})'
				}),
				border: false,
				columnLines: true,
				enableHdMenu: false,
				stripeRows: true,
				autoHeight: true
			});
			this.store.loadData( aExtensionData );
		}
		this.panel.show();
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
		return '<span class="'+ sCssClass +'">' + sValue + '</span>';;
	}
}

BsExtensionInfo.show();