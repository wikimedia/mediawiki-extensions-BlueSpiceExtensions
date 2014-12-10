Ext.define( 'BS.Review.StepsGrid', {
	extend: 'BS.CRUDGridPanel',

	initComponent: function(){
		this.bbMain = {}; //Remove BottomBar from base class

		this.gpMainConf = {
			height: 215,
			autoscroll: true
		};

		this.colMainConf.columns = [
			Ext.create( 'Ext.grid.column.RowNumberer', {
				width: 20,
				flex: 0
			}),
			{
				dataIndex: 'status',
				header: mw.message('bs-review-colstatus' ).plain(),
				sortable: false,
				width: 60,
				flex: 0,
				renderer: this.renderStatus
			},
			{
				dataIndex: 'user_display_name',
				header: mw.message('bs-review-colreviewer' ).plain(),
				sortable: false
			},
			{
				dataIndex: 'comment',
				header: mw.message('bs-review-colcomment' ).plain(),
				sortable: false
			}
		];

		this.colMainConf.actions = [
			{
				icon: mw.config.get('wgScriptPath') + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_moveup_tn.png',
				tooltip: mw.message('bs-review-btnmoveup' ).plain(),
				iconCls: 'bs-extjs-actioncloumn-icon',
				handler: this.onActionUpClick,
				scope: this
			}, {
				icon: mw.config.get('wgScriptPath') + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-um_movedown_tn.png',
				tooltip: mw.message('bs-review-btnmovedown' ).plain(),
				iconCls: 'bs-extjs-actioncloumn-icon',
				handler: this.onActionDownClick,
				scope: this
			}
		];

		var strConf = {
			fields: [ 'user_id', 'user_name', 'user_display_name', 'status',
				'comment', 'sort_id' ]
		};

		$(document).trigger( 'bsspecialreviewbeforecreatecolumns', [this.colMainConf.columns] );
		$(document).trigger( 'BSReviewStepsPanelInitComponent', [this, this.gpMainConf, this.colMainConf, strConf] );

		this.strMain = Ext.create( 'Ext.data.JsonStore', strConf);

		this.on( 'afterrender', this.onAfterRender, this );

		this.callParent();
	},

	enableEditing: function( enable ) {
		if( enable ) {
			this.dockedItems.items[0].enable(); //TODO: not nice
			this.colActions.enable();
			this.colActions.show();
			return;
		}

		this.allowEdit = enable; //On first call we will need to store this for
		//onAfterRender. Otherwise the colum is not hidden properly
		//This is not good. We need to improve it
		//Maybe this is a BSF thing. See "ResponsibleEditors"

		this.dockedItems.items[0].disable(); //TODO: not nice
		this.colActions.disable();
		this.colActions.hide();
	},

	onAfterRender: function( sende, eOpts ) {
		if( this.allowEdit == false ) {
			this.colActions.disable();
			this.colActions.hide(); //"afterInitComponent" is too early...
		}
	},

	setData: function( obj ) {
		this.callParent( arguments );
		if( this.currentData.userCanEdit == false ) {
			this.tbar.hide();
		}

		this.strMain.loadData( this.currentData );
	},

	onBtnAddClick: function( oButton, oEvent ) {
		Ext.require('BS.Review.StepDialog', function(){
			BS.Review.StepDialog.clearListeners();
			BS.Review.StepDialog.on( 'ok', this.doAddStep, this );

			BS.Review.StepDialog.setData( { data: {} } );
			BS.Review.StepDialog.show( oButton.getEl() );
		}, this);
		this.callParent(arguments);
	},

	onBtnEditClick: function( oButton, oEvent ) {
		Ext.require('BS.Review.StepDialog', function(){
			BS.Review.StepDialog.clearListeners();
			BS.Review.StepDialog.on( 'ok', this.doEditStep, this );

			BS.Review.StepDialog.setData( this.getSingleSelection() || {} );
			BS.Review.StepDialog.show( oButton.getEl() );
		}, this);

		this.callParent(arguments);
	},

	onBtnRemoveClick: function( oButton, oEvent ) {
		bs.util.confirm(
			'bs-review-confirm-delete-step',
			{
				textMsg: 'bs-review-confirm-delete-step'
			},
			{
				ok: this.doRemoveSelectedStep,
				scope: this
			}
		);
		this.callParent(arguments);
	},

	doRemoveSelectedStep: function() {
		this.strMain.remove(this.getSingleSelection());
		this.grdMain.getSelectionModel().deselectAll();
		this.grdMain.getView().refresh();
	},

	doAddStep: function( sender, data ) {
		data.status = -1;
		this.strMain.add( data );
	},

	doEditStep: function(sender, data ) {
		var row = this.getSingleSelection();
		row.data = data;
		this.grdMain.getView().refresh();
	},

	onActionUpClick: function(grid, rowIndex, colIndex) {
		if ( rowIndex == 0 ) return;

		this.grdMain.getSelectionModel().select(
			this.grdMain.getStore().getAt( rowIndex )
		);

		record = this.strMain.getAt(rowIndex);
		this.strMain.removeAt(rowIndex);
		this.strMain.insert( rowIndex-1, record );
		this.grdMain.getSelectionModel().select( record );
		this.grdMain.getView().refresh();
	},

	onActionDownClick: function(grid, rowIndex, colIndex) {
		if ( rowIndex == this.strMain.getCount() - 1 ) return;

		this.grdMain.getSelectionModel().select(
			this.grdMain.getStore().getAt( rowIndex )
		);

		record = this.strMain.getAt(rowIndex);
		this.strMain.removeAt(rowIndex);
		this.strMain.insert( rowIndex+1, record );
		this.grdMain.getSelectionModel().select( record );
		this.grdMain.getView().refresh(); // Numbering of first column
	},

	getData: function() {
		var rows = this.grdMain.getStore().getRange();
		var data = [];
		for( var i = 0; i < rows.length; i++ ) {
			var row = rows[i];
			var step = {
				userid: row.get('user_id'),
				name: row.get('user_name'),
				comment: row.get('comment'),
				status: row.get('status')
			};
			data.push( step );
		}
		return data;
	},

	renderStatus: function ( cellValue, record ) {
		var content = '<div class="{0}"></div>';
			if( cellValue == 0 ) {
				content = content.format( 'rv_no' );
			}
			if( cellValue == 1 ) {
				content = content.format( 'rv_yes' );
			}
			if( cellValue == -1 ) {
				content = content.format( 'rv_unknown' );
			}
			if( cellValue == -2 ) { //Was '1' before reject, see BsReviewProcess::reset()
				content = '<div class="rv_yes">'+content.format( 'rv_invalid' )+'</div>';
			}
			if( cellValue == -3 ) { //Was '0' before reject
				content = '<div class="rv_no">'+content.format( 'rv_invalid' )+'</div>';
			}

		return content;
	}
});