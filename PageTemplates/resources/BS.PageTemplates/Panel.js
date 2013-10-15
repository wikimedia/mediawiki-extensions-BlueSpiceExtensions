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
			header: mw.message('bs-pagetemplates-headerLabel').plain(),
			sortable: true,
			dataIndex: 'label',
			tpl: '{label}'
		} );
		this.colDesc = Ext.create( 'Ext.grid.column.Column', {
			id: 'pg-desc',
			header: mw.message('bs-pagetemplates-headerDescription').plain(),
			xtype: 'templatecolumn',
			sortable: true,
			dataIndex: 'desc'
		} );
		this.colTargetns = Ext.create( 'Ext.grid.column.Column', {
			id: 'pg-targetns',
			header: mw.message('bs-pagetemplates-headerTargetNamespace').plain(),
			xtype: 'templatecolumn',
			sortable: true,
			dataIndex: 'targetns'
		} );
		this.colTemplate = Ext.create( 'Ext.grid.column.Column', {
			id: 'pg-template',
			header: mw.message('bs-pagetemplates-headerTemplate').plain(),
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
	onBtnAddClick: function( oButton, oEvent ) {
		if ( !this.dlgTemplateAdd ) {
			this.dlgTemplateAdd = Ext.create( 'BS.PageTemplates.TemplateDialog' );
			this.dlgTemplateAdd.on( 'ok', this.onDlgNamespaceAddOk, this );
		}

		this.active = 'add';
		this.dlgTemplateAdd.setTitle( mw.message( 'bs-pagetemplates-tipAddTemplate' ).plain() );
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

		this.dlgTemplateEdit.setTitle( mw.message( 'bs-pagetemplates-tipEditDetails' ).plain() );
		this.dlgTemplateEdit.setData( selectedRow[0].getData() );
		this.dlgTemplateEdit.show();
		this.callParent( arguments );
	},
	onBtnRemoveClick: function( oButton, oEvent ) {
		bs.util.confirm(
			'PTremove',
			{
				text: mw.message( 'bs-pagetemplates-confirmDeleteTemplate' ).plain(),
				title: mw.message( 'bs-pagetemplates-tipDeleteTemplate' ).plain()
			},
			{
				ok: this.onDlgTemplateRemoveOk,
				cancel: function() {},
				scope: this
			}
		);
	},
	onDlgNamespaceAddOk: function( data, template ) {
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
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
					this.dlgTemplateAdd.resetData();
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {}
		});
	},
	onDlgTemplateEditOk: function( data, template ) {
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
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.dlgTemplateEdit.resetData();
					this.renderMsgSuccess( responseObj );
				} else {
					this.renderMsgFailure( responseObj );
				}
			},
			failure: function( response, opts ) {}
		});
	},
	onDlgTemplateRemoveOk: function( data, namespace ) {
		var selectedRow = this.grdMain.getSelectionModel().getSelection();
		var ptId = selectedRow[0].get( 'id' );

		Ext.Ajax.request( {
			url: bs.util.getAjaxDispatcherUrl(
				'PageTemplatesAdmin::doDeleteTemplate',
				[ ptId ]
			),
			scope: this,
			method: 'post',
			success: function( response, opts ) {
				var responseObj = Ext.decode( response.responseText );
				if ( responseObj.success === true ) {
					this.renderMsgSuccess( responseObj );
				} else {
					this.renderMsgFailure( responseObj );
				}
			}
		});
	},
	reloadStore: function() {
		this.strMain.reload();
	},
	showDlgAgain: function() {
		if ( this.active === 'add' ) {
			this.dlgUserAdd.show();
		} else {
			this.dlgUserEdit.show();
		}
	},
	renderMsgSuccess: function( responseObj ) {
		if ( responseObj.message.length ) {
			bs.util.alert( 'UMsuc', { text: responseObj.message, title: 'Status' }, { ok: this.reloadStore, cancel: function() {}, scope: this } );
		}
	},
	renderMsgFailure: function( responseObj ) {
		if ( responseObj.errors ) {
			var message = '';
			for ( i in responseObj.errors ) {
				if ( typeof( responseObj.errors[i].message ) !== 'string') continue;
				message = message + responseObj.errors[i].message + '<br />';
			}
			bs.util.alert( 'UMfail', { text: message, title: 'Status' }, { ok: this.showDlgAgain, cancel: function() {}, scope: this } );
		}
	}
} );