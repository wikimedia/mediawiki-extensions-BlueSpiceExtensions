Ext.namespace('biz', 'biz.hallowelt', 'biz.hallowelt.ResponsibleEditors');

biz.hallowelt.ResponsibleEditors.AssignmentWindow = Ext.extend(Ext.Window, {
	width: 500,
	closeAction: 'hide',
	y: 150,
	initComponent: function() {
	    this.title = mw.message('bs-responsibleeditors-title').plain();
	    this.btnSave = new Ext.Button({
			text: mw.message('bs-responsibleeditors-save').plain(),
			handler: this.onBtnSaveClicked,
			scope: this
	    });
	    
	    this.btnCancel = new Ext.Button({
			text: mw.message('bs-responsibleeditors-cancel').plain(),
			handler: this.onBtnCancelClicked,
			scope: this
	    });
	    
	    this.pnlAssignment = new biz.hallowelt.ResponsibleEditors.AssignmentPanel({
			articleData: { articleId: -1 },
			listeners: {
				'saved': this.pnlAssignmentSaved,
				scope: this
			}
	    });
	    
	    this.items = [
			this.pnlAssignment
	    ]
	    
	    this.buttons = [
			this.btnSave,
			this.btnCancel
	    ]
	    
	    biz.hallowelt.ResponsibleEditors.AssignmentWindow.superclass.initComponent.call(this);
	},
	
	contextStore : null,
	loadAndShow: function ( iArticleId, oCallerStore ) {
	    this.contextStore = oCallerStore;
	    this.pnlAssignment.loadArticle( iArticleId );
	    this.show();
	},
	
	pnlAssignmentSaved: function (){
	    this.hide();
	    this.contextStore.reload();
	},
	
	onBtnSaveClicked: function( oSender, oEvent ) {
	    this.pnlAssignment.save();
	},
	
	onBtnCancelClicked: function( oSender, oEvent ) {
	    this.hide();
	}
});