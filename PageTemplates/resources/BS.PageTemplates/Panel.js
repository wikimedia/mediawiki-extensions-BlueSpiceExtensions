/**
 * PageTemplates Panel
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage PageTemplates
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.PageTemplates.Panel', {
	extend: 'BS.CRUDGridPanel',
	initComponent: function() {
		this.strMain = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'PageTemplatesAdmin::getTemplates' ),
				reader: {
					type: 'json',
					root: 'templates',
					idProperty: 'id',
					totalProperty: 'totalCount'
				}
			},
			autoLoad: true,
			remoteSort: true,
			fields: [ 'id', 'label', 'desc', 'targetns', 'targetnsid', 'template', 'templatename', 'templatens' ],
			sortInfo: {
				field: 'id',
				direction: 'ASC'
			}
		});

		this.colLabel = Ext.create( 'Ext.grid.column.Template', {
			id: 'pg-label',
			header: mw.message('bs-pagetemplates-headerlabel').plain(),
			sortable: true,
			dataIndex: 'label',
			tpl: '{label}'
		} );
		this.colDesc = Ext.create( 'Ext.grid.column.Column', {
			id: 'pg-desc',
			header: mw.message('bs-pagetemplates-label-desc').plain(),
			xtype: 'templatecolumn',
			sortable: true,
			dataIndex: 'desc'
		} );
		this.colTargetns = Ext.create( 'Ext.grid.column.Column', {
			id: 'pg-targetns',
			header: mw.message('bs-pagetemplates-headertargetnamespace').plain(),
			xtype: 'templatecolumn',
			sortable: true,
			dataIndex: 'targetns'
		} );
		this.colTemplate = Ext.create( 'Ext.grid.column.Column', {
			id: 'pg-template',
			header: mw.message('bs-pagetemplates-label-article').plain(),
			xtype: 'templatecolumn',
			sortable: true,
			dataIndex: 'template'
		} );

		this.colMainConf.columns = [
			this.colLabel,
			this.colDesc,
			this.colTargetns,
			this.colTemplate
		];
		this.callParent( arguments );
	},
	makeSelModel: function(){
		this.smModel = Ext.create( 'Ext.selection.CheckboxModel', {
			mode: "MULTI",
			selType: 'checkboxmodel'
		});
		return this.smModel;
	},
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgTemplateAdd ) {
			this.dlgTemplateAdd = Ext.create( 'BS.PageTemplates.TemplateDialog' );
			this.dlgTemplateAdd.on( 'ok', this.onDlgTemplateAddOk, this );
		}

		this.active = 'add';
		this.dlgTemplateAdd.setTitle( mw.message( 'bs-pagetemplates-tipaddtemplate' ).plain() );
		this.dlgTemplateAdd.show();
		this.callParent( arguments );
	},
	onBtnEditClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		if ( !this.dlgTemplateEdit ) {
			this.dlgTemplateEdit = Ext.create( 'BS.PageTemplates.TemplateDialog' );
			this.dlgTemplateEdit.on( 'ok', this.onDlgTemplateEditOk, this );
		}

		this.active = 'edit';

		var editable = selectedRow[0].get( 'editable' );
		if ( editable === false ) {
			this.dlgTemplateEdit.tfNamespaceName.disable();
		}

		this.dlgTemplateEdit.setTitle( mw.message( 'bs-pagetemplates-tipeditdetails' ).plain() );
		this.dlgTemplateEdit.setData( selectedRow[0].getData() );
		this.dlgTemplateEdit.show();
		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		bs.util.confirm(
			'PTremove',
			{
				text: mw.message( 'bs-pagetemplates-confirm-deletetpl', selectedRow.length ).text(),
				title: mw.message( 'bs-pagetemplates-tipdeletetemplate', selectedRow.length ).text()
			},
			{
				ok: this.onDlgTemplateRemoveOk,
				cancel: function() {},
				scope: this
			}
		);
	},
	onDlgTemplateAddOk: function( data, template ) {
		this.dlgTemplateAdd.setLoading();
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'PageTemplatesAdmin::doEditTemplate',
				[
					template.id,
					template.template,
					template.label,
					template.desc,
					template.targetns,
					template.templatens
				]
			),
			method: 'post',
			scope: this,
			success: function( response, opts ) {
				this.dlgTemplateAdd.setLoading(false);
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
					this.dlgTemplateAdd.resetData();
					this.dlgTemplateAdd.close();
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {
				this.dlgTemplateAdd.setLoading(false);
				this.renderMsgFailure();
			}
		});
		return false;
	},
	onDlgTemplateEditOk: function( data, template ) {
		this.dlgTemplateEdit.setLoading();
		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'PageTemplatesAdmin::doEditTemplate',
				[
					template.id,
					template.template,
					template.label,
					template.desc,
					template.targetns,
					template.templatens
				]
			),
			method: 'post',
			scope: this,
			success: function( response, opts ) {
				this.dlgTemplateEdit.setLoading(false);
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.dlgTemplateEdit.resetData();
					this.renderMsgSuccess( responseObj );
					this.dlgTemplateEdit.close();
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {
				this.dlgTemplateAdd.setLoading(false);
				this.renderMsgFailure();
			}
		});
	},
	onDlgTemplateRemoveOk: function( data, namespace ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var ptIds = {};
		for (var i = 0; i < selectedRow.length; i++){
			ptIds[selectedRow[i].get( 'id' )] = selectedRow[i].get( 'label' );
		}

		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'PageTemplatesAdmin::doDeleteTemplates',
				[ ptIds ]
			),
			scope: this,
			method: 'post',
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( Object.keys(responseObj).length >= this.grdMain.getSelectionModel().getSelection().length ) {
					this.renderMsgSuccess( responseObj );
				} else {
					var failureObj = {success: false, message: mw.message("bs-pagetemplates-remove-message-unknown").plain()};
					this.renderMsgFailure( failureObj );
				}
			}
		});
	},
	reloadStore: function() {
		this.strMain.reload();
	},
	showDlgAgain: function() {
		if ( this.active === 'add' ) {
			this.dlgTemplateAdd.show();
		} else {
			this.dlgTemplateEdit.show();
		}
	},
	renderMsgSuccess: function( responseObj ) {
		var successText = "";
		if ( typeof(responseObj.message) !== "undefined" && typeof(responseObj.message.length) !== "undefined" && responseObj.message.length ) {
			successText = responseObj.message;
		} else {
			var success = "", failure = {}, counterSuccess = 0, counterFailure = 0;
			$.each(responseObj, function(i, response){
				if (response.success === true){
					success += "<li>"+i+"</li>";
					counterSuccess++;
				} else {
					if (typeof(failure [response.errors[0]]) === "undefined")
						failure [response.errors[0]] = {};
					failure [response.errors[0]][i] = "<li>"+i+"</li>";
					counterFailure++;
				}
			});
			if (counterFailure > 0){
				var failureMessage = "";
				$.each(failure, function(i, f){
					var failureMsg = "";
					$.each(f, function(index, template){
						failureMsg += template;
					});
					failureMessage += mw.message("bs-pagetemplates-remove-message-failure", f.length, "<ul>"+failureMsg+"</ul>", i) + "<br/><br/>";
				});
			}
			successText = counterSuccess > 0 ? (mw.message("bs-pagetemplates-remove-message-success", counterSuccess, "<ul>"+success+"</ul><br/>").text()) : "";
			successText += counterFailure > 0 ? failureMessage : "";
		}
		bs.util.alert( 'UMsuc', { text: successText, titleMsg: 'bs-extjs-title-success' }, { ok: this.reloadStore, cancel: function() {}, scope: this } );
	},
	renderMsgFailure: function( responseObj ) {
		if (typeof(responseObj) === 'undefined' || typeof(responseObj.errors) === 'undefined'){
			bs.util.alert( 'UMfail', { text: mw.message("bs-pagetemplates-remove-message-unknown").plain(), titleMsg: 'bs-extjs-title-warning' }, { ok: this.reloadStore, cancel: function() {}, scope: this } );
		}
		if ( responseObj.errors ) {
			var message = '';
			for ( i in responseObj.errors ) {
				if ( typeof( responseObj.errors[i] ) !== 'string') continue;
				message += responseObj.errors[i] + '<br />';
			}
			if (message === '') {
				message = mw.message("bs-pagetemplates-remove-message-unknown").plain();
			}
			bs.util.alert( 'UMfail', { text: message, titleMsg: 'bs-extjs-title-warning' }, { ok: this.showDlgAgain, cancel: function() {}, scope: this } );
		} else if ( responseObj.message.length ) {
			bs.util.alert( 'UMfail', { text: responseObj.message, titleMsg: 'bs-extjs-title-warning' }, { ok: this.reloadStore, cancel: function() {}, scope: this } );
		}
	}
} );