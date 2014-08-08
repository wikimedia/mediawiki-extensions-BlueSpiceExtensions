/**
 * Flexiskin Panel
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Tobias Weichart <weichart@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage Flexiskin
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define('BS.Flexiskin.Panel', {
	extend: 'BS.CRUDGridPanel',
	id: 'bs-flexiskin-panel',
	initComponent: function() {
		//this.gpMainConf = { cls: 'bs-extjs-flexiskin-grid' };
		this.strMain = Ext.create('Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl('Flexiskin::getFlexiskins'),
				reader: {
					type: 'json',
					root: 'flexiskin',
					idProperty: 'flexiskin_id',
					totalProperty: 'totalCount'
				}
			},
			autoLoad: true,
			remoteSort: true,
			fields: ['flexiskin_id', 'flexiskin_name', 'flexiskin_desc', 'flexiskin_active'],
			sortInfo: {
				field: 'flexiskin_id',
				direction: 'ASC'
			}
		});

		this.colName = Ext.create('Ext.grid.column.Template', {
			id: 'flexiskin_name',
			header: mw.message('bs-flexiskin-labelname').plain(),
			sortable: true,
			dataIndex: 'flexiskin_name',
			tpl: '{flexiskin_name}'
		});
		this.colDesc = Ext.create('Ext.grid.column.Template', {
			id: 'flexiskin_desc',
			header: mw.message('bs-flexiskin-labeldesc').plain(),
			sortable: true,
			dataIndex: 'flexiskin_desc',
			tpl: '{flexiskin_desc}'
		});
		this.colActive = Ext.create('Ext.grid.column.CheckColumn', {
			id: 'flexiskin_active',
			header: mw.message('bs-flexiskin-headeractive').plain(),
			sortable: true,
			dataIndex: 'flexiskin_active'
		});
		this.colMainConf.columns = [
		this.colName,
		this.colDesc,
		this.colActive
		];
		this.colActive.on('checkchange', this.onCheckActiveChange, this);
		this.callParent(arguments);
	},
	onCheckActiveChange: function(oCheckBox, rowindex, checked) {
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl('Flexiskin::activateFlexiskin'),
			params: {
				id: checked ? this.grdMain.getStore().getAt(rowindex).getData().flexiskin_id : ""
			},
			success: function(response) {
				window.location.reload();
			},
			scope: this
		});
	},
	onBtnAddClick: function(oButton, oEvent) {
		if (!this.dlgSkinAdd) {
			this.dlgSkinAdd = Ext.create('BS.Flexiskin.AddSkin');
			this.dlgSkinAdd.on('ok', this.onDlgSkinAdd, this);
		}

		this.dlgSkinAdd.setTitle(mw.message('bs-flexiskin-titleaddskin').plain());
		this.dlgSkinAdd.tfName.enable();
		this.dlgSkinAdd.show();
		this.callParent(arguments);
	},
	onBtnEditClick: function(oButton, oEvent) {
		this.selectedRow = this.grdMain.getSelectionModel().getSelection();
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl('Flexiskin::getFlexiskinConfig'),
			params: {
				id: this.selectedRow[0].getData().flexiskin_id
			},
			success: function(response) {
				var responseObj = Ext.decode(response.responseText);
				if (responseObj.success === false) {
					bs.util.alert('bs-flexiskin-get-config-error',
					{
						text: responseObj.msg,
						titleMsg: 'bs-extjs-error'
					}, {
						ok: function() {
						},
						cancel: function() {
						},
						scope: this
					}
					);
					return;
				}
				Ext.require('BS.Flexiskin.PreviewWindow', function(){
					var config = Ext.decode(responseObj.config);
					BS.Flexiskin.PreviewWindow.setData({
						skinId: this.selectedRow[0].get('flexiskin_id'),
						config: config
					});
					Ext.getCmp('bs-flexiskin-preview-menu').onItemStateChange();
					BS.Flexiskin.PreviewWindow.show();
				}, this);
			},
			scope: this
		});
		this.callParent(arguments);
	},
	onBtnRemoveClick: function(oButton, oEvent) {
		bs.util.confirm(
			'UMremove',
			{
				text: mw.message('bs-flexiskin-confirmdeleteskin').plain(),
				title: mw.message('bs-extjs-delete').plain()
			},
			{
				ok: this.onRemoveSkinOk,
				cancel: function() {
				},
				scope: this
			}
			);
	},
	onRemoveSkinOk: function() {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var skinId = selectedRow[0].get('flexiskin_id');

		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl('Flexiskin::deleteFlexiskin'),
			params: {
				skinId: skinId
			},
			scope: this,
			success: function(response, opts) {
				var responseObj = Ext.decode(response.responseText);
				if (responseObj.success === false) {
					bs.util.alert('bs-flexiskin-deleteskin-error',
					{
						text: responseObj.msg,
						titleMsg: 'bs-extjs-error'
					}, {
						ok: function() {
						},
						cancel: function() {
						},
						scope: this
					}
					);
				}
				this.reloadStore();
			}
		});
	},
	reloadStore: function() {
		this.strMain.reload();
	},
	onDlgSkinAdd: function(data, user) {
		var datas = this.getAddSkinData();
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl('Flexiskin::addFlexiskin'),
			params: {
				data: Ext.encode(datas)
			},
			scope: this,
			success: function(response, opts) {
				var responseObj = Ext.decode(response.responseText);
				if (responseObj.success === true) {
					this.dlgSkinAdd.resetData();
					this.reloadStore();
				} else {
					bs.util.alert('bs-flexiskin-addskin-error',
					{
						text: responseObj.msg,
						titleMsg: 'bs-extjs-error'
					}, {
						ok: function() {
							this.dlgSkinAdd.show();
						},
						cancel: function() {
						},
						scope: this
					}
					);
				}
			},
			failure: function(response, opts) {
			}
		});
	},
	getAddSkinData: function() {
		var data = [];
		data.push({
			name: this.dlgSkinAdd.tfName.getValue(),
			desc: this.dlgSkinAdd.tfDesc.getValue(),
			template: this.dlgSkinAdd.cbSkins.getValue()
		});
		return data;
	}
});