/**
 * Review extension
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @version    1.0 beta
 * @version    $Id: SpecialReview.js 9755 2013-06-17 07:45:23Z pwirth $
 * @package    Bluespice_Extensions
 * @subpackage Review
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */
 
/* Changelog
  * v0.1
  * - initial commit
  */

// Last Code Review RBV (30.06.2011)

/**
 * Base class for all Review special page related methods and properties
 */
BsSpecialReview = {
	/**
	 * Internationalisation
	 * @var object list of i18n key value pairs
	 */
	i18n : {
		updateRow : 'Update',
		cancelRow: 'Cancel',
		btnAddReviewer : 'Add reviewer',
		btnEditReviewer : 'Edit reviewer',
		btnRemoveReviewer : 'Remove reviewer',
		btnMoveUp : 'Move up',
		btnMoveDown : 'Move down',
		btnOk: 'Ok',
		btnCancel: 'Cancel',
		colStatus : 'Status',
		colReviewer : 'Reviewer',
		colDelegatedTo: 'Delegated to',
		colComment : 'Comment',
		lblStartdate : 'Start date',
		lblEnddate : 'End date',
		btnCreate : 'Create',
		btnSave : 'Save',
		btnDelete : 'Delete',
		noReviewAssigned : 'This page has no review assigned.',
		headerActions : 'Actions',
		titleAddReviewer : 'Add reviewer',
		titleEditReviewer : 'Edit reviewer',
		labelUsername : 'Reviewer',
		labelComment : 'Comment',
		titleStatus: 'Status',
		labelTemplate: 'Template',
		labelTemplateLoad: 'Load',
		labelTemplateSaveForMe: 'Save for myself',
		labelTemplateSaveForAll: 'Save for everyone',
		labelTemplateDelete: 'Delete',
		templateName: 'Name of the template',
		mode: 'Mode',
		modeVote: 'No editing allowed / Ignore reviewer order',
		modeSign: 'No editing allowed / Follow reviewer order',
		modeComment: 'Editing allowed / Ignore reviewer order',
		modeWorkflow: 'Editing allowed / Follow reviewer order'
	},

	/**
	 * Data structure for a single reviewer.
	 * @var Ext.data.Record Has attributes status, name, userid and comment (all strings). Created in function showPanel.
	 */
	reviewer : null,

	/**
	 * Holds the current review for a page. Filled with data from PHP variable in function showPanel.
	 * @var object JSON encoded description of current review.
	 */
	currentReview : null, 

	/**
	 * Holds a list of users for dropdown box in Add Reviewer dialogue.
	 * @var Ext.data.JsonStore An ExtJS store that holds the users.
	 */
	storeUsers: new Ext.data.JsonStore({
		url: BlueSpice.buildRemoteString('Review', 'getUsers'),
		root: 'users',
		fields: ['username', 'displayname', 'userid'],
		autoLoad: true
	}),
	/* // TODO: BS docu
	storeTemplates: new Ext.data.JsonStore({
		url: BlueSpice.buildRemoteString('Review', 'getTemplateData'),
		root: 'templates',
		fields: ['name', 'users', 'mode']
	}),*/

	/**
	 * Holds a list of reviewers for data grid. Created and  filled in function showPanel.
	 * @var Ext.data.GroupingStore An ExtJS store that holds the reviewers.
	 */
	storeReviewers : null,
	
	/**
	 * Holds the reference to the ExtJS panel on the special page. Created and filled in functino showPanel.
	 * @var Ext.grid.GridPanel An ExtJS layout container.
	 */
	EditPanel : null,

	/**
	 * Renders the first column with the status information icon.
	 * @var Ext.XTemplate An HTML template that changes classes accordingly.
	 */
	statusTemplate : new Ext.XTemplate(
		'<div class="{[this.getStatusClass(values.status)]}">' +
		'</div>',
		{
			getStatusClass : function(trend) {
				var retValue = '';
				switch (trend) {
					case 'unknown':
						retValue = 'rv_unknown';
						break;
					case 'yes':
						retValue = 'rv_yes';
						break;
					case 'no':
						retValue = 'rv_no';
						break;
					default:
						retValue = 'rv_unknown';
						break;
				}
				if(retValue == 'rv_unknown') {
					$(document).trigger( 'bsspecialreviewgetstatustplclass', [trend, retValue] );
				}
				return retValue;
			}
		}
		),

	/**
	 * Prepares data and displays the review edit panel.
	 */
	showPanel: function() {
		BsSpecialReview.statusTemplate.compile();
		var reviewerRecordCfg = [{
			name: 'status',
			type: 'string'
		}, {
			name: 'name',
			type: 'string'
		}, {
			name: 'userid',
			type: 'string'
		}, {
			name: 'comment',
			type: 'string'
		}, {
			name: 'sort_id',
			type: 'int'
		}];
		$(document).trigger( 'bsspecialreviewbeforecreaterecord', [reviewerRecordCfg] );
		BsSpecialReview.reviewer = Ext.data.Record.create(reviewerRecordCfg);
		
		BsSpecialReview.currentReview = bsReviewCurrentReview;
		
		BsSpecialReview.storeReviewers = new Ext.data.GroupingStore({
			reader: new Ext.data.JsonReader({
				fields: this.reviewer
			}),
			data: this.currentReview.steps,
			sortInfo: {
				field: 'sort_id', 
				direction: 'ASC'
			}//,
		});
		
		var columnsCfg = [
		new Ext.grid.RowNumberer(),
		{
			xtype: 'templatecolumn',
			dataIndex: 'status',
			header: BsSpecialReview.i18n.colStatus,
			sortable: false,
			width: 50,
			tpl: BsSpecialReview.statusTemplate
		},
		{
			xtype: 'gridcolumn',
			dataIndex: 'name',
			header: BsSpecialReview.i18n.colReviewer,
			sortable: false,
			width: 150
		},
		{
			xtype: 'gridcolumn',
			dataIndex: 'comment',
			header: BsSpecialReview.i18n.colComment,
			sortable: false
		}, {
			header: this.i18n.headerActions,
			xtype: 'actioncolumn',
			width: 120,
			cls: 'hideAction',
			items: bsReviewReadOnly?null:[
			{
				icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config_tn.png',
				tooltip: this.i18n.btnEditReviewer,
				handler: function(grid, rowIndex, colIndex) {
					BsSpecialReview.showEditReviewer(BsSpecialReview.storeReviewers.getAt(rowIndex));
				},
				scope: this
			}, {
				icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_moveup_tn.png',
				tooltip: this.i18n.btnMoveUp,
				handler: function(grid, rowIndex, colIndex) {
					if ( rowIndex > 0 ) {
						record = BsSpecialReview.storeReviewers.getAt(rowIndex);
						BsSpecialReview.storeReviewers.removeAt(rowIndex);
						BsSpecialReview.storeReviewers.insert( rowIndex-1, record );
						EditPanel.getSelectionModel().selectRow( rowIndex-1 );
						EditPanel.getView().refresh();
					}
				},
				scope: this
			}, {
				icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_movedown_tn.png',
				tooltip: this.i18n.btnMoveDown,
				handler: function(grid, rowIndex, colIndex) {
					if ( rowIndex < BsSpecialReview.storeReviewers.getCount()-1 ) {
						record = BsSpecialReview.storeReviewers.getAt(rowIndex);
						BsSpecialReview.storeReviewers.removeAt(rowIndex);
						BsSpecialReview.storeReviewers.insert( rowIndex+1, record );
						EditPanel.getSelectionModel().selectRow( rowIndex+1 );
						EditPanel.getView().refresh(); // Numbering of first column
					}
				},
				scope: this
			}, {
				icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_userremove_tn.png',
				tooltip: this.i18n.btnRemoveReviewer,
				handler: function(grid, rowIndex, colIndex) {
					BsSpecialReview.storeReviewers.removeAt(rowIndex);
				},
				scope: this
			}
			],
			sortable: false
		}];
		$(document).trigger( 'bsspecialreviewbeforecreatecolumns', [columnsCfg] );

		EditPanel = new Ext.grid.GridPanel({
			store: BsSpecialReview.storeReviewers,
			title: '',
			height: 300,
			autoExpandColumn: 4,
			sm: new Ext.grid.RowSelectionModel({
				singleSelect: true,
				listeners: {
					'selectionchange': function( sm ) {
						if (sm.hasSelection()) {
							Ext.getCmp('btnRvUserRemove').setIcon( wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_userremove.png');
							Ext.getCmp('btnRvUserRemove').enable();
							Ext.getCmp('btnRvUserEdit').setIcon( wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config.png');
							Ext.getCmp('btnRvUserEdit').enable();
						} else {
							Ext.getCmp('btnRvUserRemove').setIcon( wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_userremove-0.png');
							Ext.getCmp('btnRvUserRemove').disable();
							Ext.getCmp('btnRvUserEdit').setIcon( wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png');
							Ext.getCmp('btnRvUserEdit').disable();
						}
					}
				}
			}),
			tbar: bsReviewReadOnly?null:new Ext.Toolbar({
				style: {
					backgroundColor: '#FFFFFF',
					backgroundImage: 'none'
				},
				items: [{
					icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_useradd.png',
					iconCls: 'btn44',
					tooltip: BsSpecialReview.i18n.btnAddReviewer,
					id : 'btnRvUserAdd',
					handler: function(btn, ev){
						BsSpecialReview.showAddReviewer();
					}
				},{
					icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config-0.png',
					iconCls: 'btn44',
					tooltip: BsSpecialReview.i18n.btnEditReviewer,
					id : 'btnRvUserEdit',
					handler: function(){
						BsSpecialReview.showEditReviewer(EditPanel.getSelectionModel().getSelected());
					}
				},{
					icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_userremove-0.png',
					iconCls: 'btn44',
					tooltip: BsSpecialReview.i18n.btnRemoveReviewer,
					id : 'btnRvUserRemove',
					handler: function(){
						var s = EditPanel.getSelectionModel().getSelections();
						for(var i = 0, r; r = s[i]; i++){
							BsSpecialReview.storeReviewers.remove(r);
						}
					}
				}]
			}),
			columns : columnsCfg
		});

		var formItemCfg = [{
			xtype: 'datefield',
			readOnly: bsReviewReadOnly,
			fieldLabel: BsSpecialReview.i18n.lblStartdate,
			name: 'startdate',
			id: 'startdate',
			value: BsSpecialReview.currentReview.startdate,
			listeners: {
				'select': function( self, newValue, oldValue  ) {
					Ext.getCmp('enddate').setMinValue( newValue );
					Ext.getCmp('enddate').validate();
				}
			}
		}, {
			xtype: 'datefield',
			readOnly: bsReviewReadOnly,
			fieldLabel: BsSpecialReview.i18n.lblEnddate,
			name: 'enddate',
			id: 'enddate',
			value: BsSpecialReview.currentReview.enddate,
			listeners: {
				'select': function( self, newValue, oldValue  ) {
					Ext.getCmp( 'startdate' ).setMaxValue( newValue );
					Ext.getCmp( 'startdate' ).validate();
				}
			}
		}
		];
		$(document).trigger( 'bsspecialreviewbeforecreateform', [formItemCfg] );
		formItemCfg.push(EditPanel);
		
		var panel = new Ext.FormPanel({
			title: '',
			id: 'panelFormEditReview',
			border: false,
			bodyBorder: false,
			//width: 400,
			//height: 250,
			renderTo: 'bs-review-panel',
			monitorValid:true,
			items: formItemCfg,
			buttons: bsReviewReadOnly?null:[{
				id: 'ok-btn',
				text: BsSpecialReview.currentReview.steps.length>0?BsSpecialReview.i18n.btnSave:BsSpecialReview.i18n.btnCreate, //BsSaferEdit.i18n.restore,
				formBind:true,
				handler: function(e) {
					//apply( Object params, Object baseParams, String action, Record/Record[] rs )
					var rs = BsSpecialReview.storeReviewers.getRange();
					var da = new Array();
					for ( var r in rs ) {
						da.push(rs[r].data);
					}
					var obj = {
						cmd: 'insert',
						pid: bsReviewArticleId,
						mode: 'workflow', // TODO: BS docu - Ext.getCmp('mode').getValue(),
						startdate: Ext.getCmp('startdate').getValue(),
						enddate: Ext.getCmp('enddate').getValue(),
						steps: da
					};
					//var participants = Ext.encode(obj);
					//console.log();
					//Ext.getCmp('panelFormEditReview').getForm().doAction('clientvalidation');
					Ext.getCmp('panelFormEditReview').getForm().doAction('submit', {
						//Ext.getCmp('enddate').validate();
						clientValidation: true,
						submitEmptyText: false,
						method: 'post',
						url: BlueSpice.buildRemoteString('Review', 'doEditReview'),
						params: {
							review: Ext.encode(obj),
							pid: bsReviewArticleId,
							cmd: 'insert',
							mode: 'workflow' // TODO: BS docu - 'comment'
						},
						success: function(form, action) {
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert(BsSpecialReview.i18n.titleStatus, tmp);
							};
						},
						failure: function(form, action) {
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert(BsSpecialReview.i18n.titleStatus, tmp);
							};
						//BsSpecialReview.storeReviewers.reload();
						}
					})
				},
				scope: this
			},{
				text: BsSpecialReview.i18n.btnDelete, //BsSaferEdit.i18n.cancel,
				disabled: BsSpecialReview.currentReview.steps.length>0?false:true,
				handler: function(){
					Ext.getCmp('panelFormEditReview').getForm().doAction('submit', {
						clientValidation: false,
						submitEmptyText: false,
						method: 'post',
						url: BlueSpice.buildRemoteString('Review', 'doEditReview'),
						params: {
							pid: bsReviewArticleId,
							cmd: 'delete'
						},
						success: function(form, action) {
							if(action.result.messages.length) {
								var tmp = '';
								for(i in action.result.messages) {
									if(typeof(action.result.messages[i]) != 'string') {
										continue;
									}
									tmp = tmp + action.result.messages[i] + '<br />';
								}
								Ext.Msg.alert('Status', tmp);
								window.location.reload();
							}
						}
					})
				}
			}]
		});
	},
	
	/**
	 * Pointer to ExtJS dialogue window
	 * @var Ext.Window Window object of ExtJS.
	 */
	window: false,
	
	/**
	 * Renders the add reviewer dialogue.
	 */
	showAddReviewer: function() {
		this.window = new Ext.Window({
			title: BsSpecialReview.i18n.titleAddReviewer,
			modal: true,
			width: 350,
			items: [
			{
				xtype: 'form',
				id: 'panelFormAddReviewer',
				labelWidth: 130,
				padding: 10,
				border: false,
				defaults: {
					msgTarget: 'under'
				},
				items: [
				{
					xtype:'combo',
					forceSelection: true,
					fieldLabel: BsSpecialReview.i18n.labelUsername,
					name: 'inputRvUsername',
					id: 'inputRvUsername',
					allowBlank: false,
					typeAhead: true,
					triggerAction: 'all',
					lazyRender:true,
					mode: 'local',
					store: BsSpecialReview.storeUsers,
					valueField: 'userid',
					displayField: 'displayname',
					width: 180,
					listeners: {
						'select' : function( cbx, record, index ){
							Ext.getCmp('addReviewerBtnOk').enable();
						}
					}
				}, {
					xtype: 'textfield',
					fieldLabel: BsSpecialReview.i18n.labelComment,
					name: 'inputRvComment',
					id: 'inputRvComment',
					width: 180
				}
				]
			}
			],
			buttons: [
			{
				text: BsSpecialReview.i18n.btnOk,
				id: 'addReviewerBtnOk',
				disabled: true,
				handler: function(){
					
					var e = new BsSpecialReview.reviewer({
						status: 'unknown',
						name: Ext.getCmp('inputRvUsername').getRawValue(),
						userid: Ext.getCmp('inputRvUsername').getValue(),
						comment: Ext.getCmp('inputRvComment').getValue()
					});
					//editor.stopEditing();
					BsSpecialReview.storeReviewers.add(e);
					EditPanel.getView().refresh();
					EditPanel.getSelectionModel().selectLastRow();
					this.window.close();
					//editor.startEditing(store.getCount()-1);
					return;

				},
				scope: this
			}, {
				text: this.i18n.btnCancel,
				handler: function(){
					this.ownerCt.ownerCt.close();
				}
			}
			]
		});
		this.window.show();
	},

	/**
	 * Renders the edit reviewer dialogue.
	 */
	showEditReviewer: function( record ) {
		//console.log( record );
		this.window = new Ext.Window({
			title: BsSpecialReview.i18n.titleEditReviewer,
			modal: true,
			width: 350,
			items: [
			{
				xtype: 'form',
				id: 'panelFormAddReviewer',
				labelWidth: 130,
				padding: 10,
				border: false,
				defaults: {
					msgTarget: 'under'
				},
				items: [
				{
					xtype:'combo',
					forceSelection: true,
					fieldLabel: BsSpecialReview.i18n.labelUsername,
					name: 'inputRvUsername',
					id: 'inputRvUsername',
					allowBlank: false,
					typeAhead: true,
					triggerAction: 'all',
					lazyRender:true,
					mode: 'local',
					store: BsSpecialReview.storeUsers,
					valueField: 'userid',
					displayField: 'displayname',
					value: record.data.name,
					width: 180
				}, {
					xtype: 'textfield',
					fieldLabel: BsSpecialReview.i18n.labelComment,
					name: 'inputRvComment',
					id: 'inputRvComment',
					value: record.data.comment,
					width: 180
				}
				]
			}
			],
			buttons: [
			{
				text: BsSpecialReview.i18n.btnOk,
				id: "btn-dlg-ok",
				handler: function(){
					
					record.data.name = Ext.getCmp('inputRvUsername').getRawValue();
					record.data.userid = Ext.getCmp('inputRvUsername').getValue();
					record.data.comment = Ext.getCmp('inputRvComment').getValue();
					EditPanel.getView().refresh();
					this.window.close();
					//editor.startEditing(store.getCount()-1);
					return;

				},
				scope: this
			}, {
				text: this.i18n.btnCancel,
				handler: function(){
					this.ownerCt.ownerCt.close();
				}
			}
			]
		});
		this.window.show();
	}
}

Ext.onReady(function() {
	//Give other extensions the chance to alter the object
	$(document).trigger( 'bsspecialreviewconfigready', [BsSpecialReview] );

	setTimeout("BsSpecialReview.showPanel()", 10);

});