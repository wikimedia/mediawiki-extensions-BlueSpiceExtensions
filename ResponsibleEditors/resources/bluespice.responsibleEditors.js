BsResponsibleEditorsSpecialPageGrid = {
	grid               : false,
	cbNamespaceFilter  : false,
	mnuAssignmentFilter: false,
	store: new Ext.data.JsonStore({
		url :BsCore.buildRemoteString('ResponsibleEditors', 'ajaxGetArticlesByNamespaceId'),
		root:'pages',
		fields: [ 'page_id', 'page_namespace', 'page_namespace_text', 'page_title', 'page_link_url', 'users' ],
		totalProperty: 'total',
		remoteSort: true,
		baseParams: {
			namespaceId: -99,
			displayMode: 'only-assigned'
		}
	}),

	show: function( iPageSize, bUserIsAllowedToAssign ){
		this.pageSize              = iPageSize;
		this.userIsAllowedToAssign = bUserIsAllowedToAssign;

		this.wdChange = new biz.hallowelt.ResponsibleEditors.AssignmentWindow( {
			renderTo: 'bs-responsibleeditors-assignmentwindow'
		} );

		if( this.grid == false){
			this.cbNamespaceFilter = new Ext.form.ComboBox({
					fieldLabel   : mw.message('bs-responsibleeditors-cbNamespacesLable').plain(),
					emptyText    : mw.message('bs-responsibleeditors-cbNamespacesEmptyText').plain(),
					displayField : 'namespace_text',
					valueField   : 'namespace_id',
					typeAhead    : true,
					triggerAction: 'all',
					store: new Ext.data.JsonStore({
						url   : BsCore.buildRemoteString('ResponsibleEditors', 'ajaxGetActivatedNamespacesForCombobox'),
						root  : 'namespaces',
						fields: ['namespace_id', 'namespace_text']
					}),
					tpl: '<tpl for="."><div class="x-combo-list-item">{namespace_text}</div><tpl if="xindex == 1"><hr /></tpl></tpl>'
				});

			this.cbNamespaceFilter.on( 'select', this.cbNamespaceFilterSelectionChanged, this );

			this.mnuAssignmentFilter = new Ext.menu.Menu({
				items: [
				{
					id     : 'bs-re-only-assigned',
					text   : mw.message('bs-responsibleeditors-rbDisplayModeOnlyAssignedText').plain(),
					checked: true,
					value  : 'only-assigned',
					group  : 'displayMode'
				},
				{
					id     : 'bs-re-only-not-assigned',
					text   : mw.message('bs-responsibleeditors-rbDisplayModeOnlyNotAssigned').plain(),
					checked: false,
					value  : 'only-not-assigned',
					group  : 'displayMode'
				},
				{
					id     : 'bs-re-all',
					text   : mw.message('bs-responsibleeditors-rbDisplayModeAll').plain(),
					checked: false,
					value  : 'all',
					group  : 'displayMode'
				}]
			});

			this.mnuAssignmentFilter.on( 'itemclick', this.mnuAssignmentFilterItemClick, this );

			this.btnAssignmentFilterMenu = new Ext.Button({
				text: mw.message('bs-responsibleeditors-rbDisplayModeOnlyAssignedText').plain(),
				menu: this.mnuAssignmentFilter
			});

			this.grid = new Ext.grid.GridPanel({
				renderTo   : 'bs-responsibleeditor-specialpage-grid',
				border     : true,
				autoHeight : true,
				loadMask   : true,
				columnLines: true,
				stripeRows : true,
				store      : this.store,
				viewConfig : {
					forceFit: true
				},
				colModel: new Ext.grid.ColumnModel({
					columns: [
						{
							id       : 'name',
							header   : mw.message('bs-responsibleeditors-columnHeaderArticle').plain(),
							sortable : true,
							dataIndex: 'page_title',
							renderer : this.renderArticleTitle,
							width    : 50
						},
						{
							header   : mw.message('bs-responsibleeditors-columnHeaderNamespace').plain(),
							sortable : true,
							dataIndex: 'page_namespace',
							renderer : this.renderNamespace,
							width    : 30
						},{
							header   : mw.message('bs-responsibleeditors-columnHeaderResponsibleEditor').plain(),
							sortable : false,
							dataIndex: 'users',
							renderer : this.renderResponsibleEditor
						},
						{
							header   : mw.message('bs-responsibleeditors-columnHeaderActions').plain(),
							xtype    : 'actioncolumn',
							width    : 20,
							sortable : false,
							cls      : 'hideAction',
							items    : [
								{
									icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_config_tn.png',
									tooltip: mw.message('bs-responsibleeditors-tipEditAssignment').plain(),
									handler: function(oGrid, iRowIndex, iColIndex) {
										if( this.userIsAllowedToAssign == false) return;
										var oRecord = oGrid.getStore().getAt( iRowIndex );
										this.wdChange.loadAndShow( oRecord.get('page_id'), oGrid.getStore() );
									},
									scope: this
								}, {
									icon: wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_userremove_tn.png',
									tooltip: mw.message('bs-responsibleeditors-tipRemoveAssignement').plain(),
									handler: function(oGrid, iRowIndex, iColIndex) {
										// TODO RBV (11.03.11 14:44): Prevent code duplication!
										if( this.userIsAllowedToAssign == false) return;
										var oRecord = oGrid.getStore().getAt( iRowIndex );

										var userIds = new Array();
										for(var i=0; i < oRecord.get('users').length; i++) {
											userIds.push(oRecord.get('users')[i].user_id);
										}
										Ext.Ajax.request({
											url    : BsCore.buildRemoteString( 'ResponsibleEditors', 'ajaxDeleteResponsibleEditorsForArticle' ),
											success: function(){ this.store.reload() },
											failure: function(){ this.store.reload() },
											params : {
												'articleId': oRecord.get('page_id'),
												'user_ids[]' : userIds
											},
											scope: this
										});
									},
									scope: this
								}
							]
						}
					]
				}),
				sm: new Ext.grid.RowSelectionModel({
					singleSelect: true
				}),
				tbar: {
					items: [
					this.btnAssignmentFilterMenu,
					'-',
					this.cbNamespaceFilter
					]
				},
				bbar: new Ext.PagingToolbar({
					pageSize      : this.pageSize,
					store         : this.store,
					displayInfo   : true,
					displayMsg    : mw.message('bs-responsibleeditors-ptbDisplayMsgText').plain(),
					emptyMsg      : mw.message('bs-responsibleeditors-ptbEmptyMsgText').plain(),
					beforePageText: mw.message('bs-responsibleeditors-ptbBeforePageText').plain(),
					afterPageText : mw.message('bs-responsibleeditors-ptbAfterPageText').plain(),
					items: [
						'-',
						mw.message('bs-responsibleeditors-pageSize').plain(),
						new Ext.form.TextField({
							width: 30,
							style: 'text-align: right',
							value: this.pageSize,
							enableKeyEvents: true,
							listeners: {
								keydown: function(t,e) {
									//HINT: http://ssenblog.blogspot.de/2009/12/extjs-grid-dynamic-page-size.html
									if( e.getKey() != 13) return;

									this.grid.getBottomToolbar().cursor = 0;
									this.grid.getBottomToolbar().pageSize = parseInt(t.getValue());
									this.store.load({
										params: {
											start: 0,
											limit: parseInt(t.getValue())
										}
									});
								},
								scope: this
							}
						})
					]
				})
		});

		this.grid.on( 'rowdblclick', this.onRowDoubleClick, this );
		if( this.userIsAllowedToAssign == false) this.grid.getColumnModel().setHidden(3, true); //Hide the actions column
	}

		this.store.load();
	},

	cbNamespaceFilterSelectionChanged: function ( oComboBox, oSelectedRecord, iSelectedIndex ) {
		this.store.setBaseParam( 'namespaceId', oSelectedRecord.get( 'namespace_id' ) );
		this.store.load();
	},

	mnuAssignmentFilterItemClick: function( oClickedItem, oEvent ) {
		this.btnAssignmentFilterMenu.setText( oClickedItem.text );
		this.store.setBaseParam( 'displayMode', oClickedItem.value );
		this.store.load();
	},

	onRowDoubleClick: function( oGrid, iRowIndex, oEvent ) {
		if( this.userIsAllowedToAssign == false) return;
		var oRecord = oGrid.getStore().getAt( iRowIndex );
		this.wdChange.loadAndShow( oRecord.get('page_id'), oGrid.getStore() );
	},

	renderNamespace: function( oValue, oMetaData, oRecord, iRowIndex, iColIndex, oStore ) {
		return oRecord.get( 'page_namespace_text' );
	},

	renderArticleTitle: function( oValue, oMetaData, oRecord, iRowIndex, iColIndex, oStore ) {
		var sDisplayTitle = oRecord.get( 'page_title' );
		var sUrl = oRecord.get('page_link_url');

		sDisplayTitle = sDisplayTitle.replace( /_/g, ' ' );

		return String.format(
			'<a href="javascript:BsResponsibleEditorsSpecialPageGrid.navigateToUrl(\'{0}\', \'{1}\')">{2}</a>',
			sUrl,
			sDisplayTitle,
			sDisplayTitle
		);
	},

	renderResponsibleEditor: function( aValue, oMetaData, oRecord, iRowIndex, iColIndex, oStore ) {
		var content = String.format(
			'<em style="color: #A0A0A0">{0}</em>',
			mw.message('bs-responsibleeditors-columnResponsibleEditorNotSet').plain()
		);
		
		if( aValue == undefined || aValue.length == 0) {
			return content;
		}
		
		content = '';
		
		for( var i = 0; i < aValue.length; i++) {
			var sDisplayName = aValue[i].user_displayname;
			var sUrl =  aValue[i].user_page_link_url;
			
			sDisplayName = sDisplayName.replace( /_/g, ' ' );
			if( i != 0 ) {
				content += ', ';
			}
			content += String.format(
				'<a href="javascript:BsResponsibleEditorsSpecialPageGrid.navigateToUrl(\'{0}\', \'{1}\')">{2}</a>',
				sUrl,
				sDisplayName,
				sDisplayName
			);
		}
		return content
	},

	// TODO RBV (10.01.11 11:41): Move to BsCore/Framework?
	navigateToUrl: function( sUrl, sArticleTitle ) {
		Ext.Msg.confirm(
			String.format( mw.message('bs-responsibleeditors-confirmNavigationTitle').plain(), sArticleTitle ),
			String.format( mw.message('bs-responsibleeditors-confirmNavigationText').plain(), sArticleTitle ),
			function( sButtonId ) {
			if( sButtonId == 'yes' ) window.location = sUrl;
			}
		);
	}
}