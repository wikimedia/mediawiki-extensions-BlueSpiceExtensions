Ext.define( 'BS.ResponsibleEditors.ManagerPanel', {
	extend: 'BS.CRUDGridPanel',

	//Custom Settings
	allowEdit: true,
	id: 'bs-resped-manager-panel',

	initComponent: function() {
		this.allowEdit = mw.config.get('bsUserMayChangeResponsibilities');

		this.strMain = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-responsibleeditorspages-store',
			fields: [
				'page_id',
				'page_namespace',
				'page_title',
				'page_prefixedtext',
				'users'
			]
		});
		this.strMain.proxy.reader.idProperty = 'page_id';

		this.strMain.on( 'load', function() {
			this.btnAdd.disable();
			this.btnRemove.disable();
		}, this);

		this.colMainConf.columns = [{
			id: 'name',
			header: mw.message( 'bs-responsibleeditors-columnpage' ).plain(),
			sortable: true,
			dataIndex: 'page_id',
			renderer: this.renderArticleTitle
		},{
			header: mw.message( 'bs-responsibleeditors-columnresponsibleeditor' ).plain(),
			sortable: false,
			dataIndex: 'users',
			renderer: this.renderResponsibleEditor,
			active: true,
			filter: {
				type: 'list',
				dataIndex: 'users',
				labelField: 'name',
				options: [{
					id:'assigned',
					name: mw.message(
						'bs-responsibleeditors-pagestorefilter-assigned'
					).plain()
				}, {
					id:'notassigned',
					name: mw.message(
						'bs-responsibleeditors-pagestorefilter-notassigned'
					).plain()
				}],
				value: [ 'assigned', 'notassigned' ]
			}
		}];

		this.callParent( arguments );
	},

	afterInitComponent: function() {
		this.callParent();

		this.btnAdd.disable();
		this.btnEdit.disable();
		this.btnRemove.disable();

		this.strNamespaceFilter = Ext.create( 'BS.store.BSApi', {
			apiAction: 'bs-responsibleeditorsactivenamespaces-store',
			fields: ['namespace_id', 'namespace_text'],
			autoLoad: true
		});

		this.cbNamespaceFilter = Ext.create( 'Ext.form.ComboBox', {
			emptyText: mw.message( 'bs-responsibleeditors-cbnamespacesemptytext' ).plain(),
			displayField: 'namespace_text',
			valueField: 'namespace_id',
			typeAhead: true,
			triggerAction: 'all',
			store: this.strNamespaceFilter,
			tpl: '<ul class="x-list-plain">'+
				'<tpl for=".">'+
					'<li role="option" unselectable="on" class="x-boundlist-item">'+
						'{namespace_text}'+
					'</li>'+
					'<tpl if="xindex == 1">'+
						'<li role="option" unselectable="on"><hr /></li>'+
					'</tpl>'+
				'</tpl>'+
				'</ul>'
		});

		this.cbNamespaceFilter.on( 'select', this.cbNamespaceFilterSelectionChanged, this );

		this.tbar.add( '->' );
		this.tbar.add( this.cbNamespaceFilter );

		if( this.allowEdit === false ) {
			this.btnAdd.hide();
			this.btnEdit.hide();
			this.btnRemove.hide();

			this.colActions.hide();
		}

		this.on( 'afterrender', this.onAfterRender, this );
	},

	onAfterRender: function( sende, eOpts ) {
		if( this.allowEdit === false ) {
			this.colActions.disable();
			this.colActions.hide(); //"afterInitComponent" is too early...
		}
	},

	cbNamespaceFilterSelectionChanged: function ( combo, records, eOpts ) {
		Ext.apply(this.strMain.proxy.extraParams, {
			'namespaceId':records[0].get( 'namespace_id' )
		});
		this.strMain.load();
	},

	onGrdMainRowClick: function( oSender, iRowIndex, oEvent ) {
		var record = this.getSingleSelection();

		this.btnAdd.disable();

		if( record.get('users').length === 0 ) {
			this.btnAdd.enable();
		}

		this.callParent();
	},

	onBtnAddClick: function( oButton, oEvent ) {
		this.showAssignDialog( oButton.getEl(), this.getRowData() );
		this.callParent(arguments);
	},

	onBtnEditClick: function(  oButton, oEvent ) {
		this.showAssignDialog( oButton.getEl(), this.getRowData() );
		this.callParent(arguments);
	},

	onBtnRemoveClick: function( oButton, oEvent ) {
		var data = this.getRowData();
		data.editorIds = [];
		//TODO: Duplicate code in BS.ResponsibleEditors.AssignmentDialog!
		var me = this;
		bs.api.tasks.exec(
			'responsibleeditors',
			'setResponsibleEditors',
			data
		).done( function() {
			me.strMain.reload();
		});

		this.callParent(arguments);
	},

	getRowData: function() {
		var record = this.getSingleSelection();
		var data = {
			articleId: record.get('page_id'),
			editorIds: []
		}
		var users = record.get('users');
		for( var i = 0; i < users.length; i++ ) {
			data.editorIds.push( users[i].user_id );
		}
		return data;
	},

	showAssignDialog: function( source, data ) {
		var me = this;
		Ext.require( 'BS.ResponsibleEditors.AssignmentDialog', function(){
			BS.ResponsibleEditors.AssignmentDialog.clearListeners();
			BS.ResponsibleEditors.AssignmentDialog.on( 'ok', function( btn, data ){
				this.strMain.reload();
			}, me);
			BS.ResponsibleEditors.AssignmentDialog.setData( data );
			BS.ResponsibleEditors.AssignmentDialog.show( source );
		});
	},

	renderArticleTitle: function( oValue, oMetaData, oRecord, iRowIndex, iColIndex, oStore ) {
		var sDisplayTitle = oRecord.get( 'page_prefixedtext' );

		sDisplayTitle = sDisplayTitle.replace( /_/g, ' ' );

		return '<a href="{0}" title="{1}" class="bs-confirm-nav">{1}</a>'.format(
			mw.util.wikiGetlink( oRecord.get('page_prefixedtext') ),
			sDisplayTitle
		);
	},

	renderResponsibleEditor: function( aValue, oMetaData, oRecord, iRowIndex, iColIndex, oStore ) {
		if( typeof(aValue) === 'undefined' || aValue.length === 0) {
			return '<em style="color: #A0A0A0">{0}</em>'.format(
				mw.message('bs-responsibleeditors-columneesponsibleeditornotset').plain()
			);
		}

		var content = '';

		for( var i = 0; i < aValue.length; i++) {
			var sDisplayName = aValue[i].user_displayname;
			var sUrl =  aValue[i].user_page_link_url;

			sDisplayName = sDisplayName.replace( /_/g, ' ' );
			if( i !== 0 ) {
				content += ', ';
			}
			content += '<a href="{0}" title="{1}" class="bs-confirm-nav">{1}</a>'.format(
				sUrl,
				sDisplayName
			);
		}

		return content;
	}
});