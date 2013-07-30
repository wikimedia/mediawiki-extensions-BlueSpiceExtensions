Ext.namespace('biz', 'biz.hallowelt', 'biz.hallowelt.ResponsibleEditors');

biz.hallowelt.ResponsibleEditors.AssignmentDialog = Ext.extend(Ext.Panel, {
	dirty: false,
	border: false,
	initComponent: function() {
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
			articleData: this.articleData,
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
		
		biz.hallowelt.ResponsibleEditors.AssignmentDialog.superclass.initComponent.call(this);
	},
	
	pnlAssignmentSaved: function (){
		//MsgBox with "Saved successfully"
		window.location.href = this.articleData.returnUrl;
	},
	
	onBtnSaveClicked: function( oSender, oEvent ) {
		this.pnlAssignment.save();
	},
	
	onBtnCancelClicked: function( oSender, oEvent ) {
		window.location.href = this.articleData.returnUrl;
	}
});

mw.loader.using( 'ext.bluespice.responsibleEditors', function() {
	CurrentAssignmentDialog = new biz.hallowelt.ResponsibleEditors.AssignmentDialog({
	articleData: bsResponsibleEditorsData,
		renderTo: 'bs-responsibleeditors-assignmentdialog'
	});

	CurrentAssignmentDialog.show();
});