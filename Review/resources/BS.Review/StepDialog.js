Ext.define( 'BS.Review.StepDialog', {
	extend: 'BS.Window',
	singleton: true,
	modal: true,

	afterInitComponent: function() {
		this.setTitle( mw.message('bs-review-titleaddreviewer' ).plain() );
		this.cbUsers = Ext.create( 'BS.form.UserCombo', {
			anyMatch: true
		} );
		this.cbUsers.getStore().sort('display_name', 'asc');

		this.tfComment = Ext.create( 'Ext.form.TextField', {
			labelAlign: 'right',
			fieldLabel: mw.message('bs-review-labelcomment').plain()
		});

		this.items = [
			this.cbUsers,
			this.tfComment
		];

		this.callParent();
	},

	setData: function( obj ) {
		this.callParent( [obj.data] );
		if( typeof this.currentData.user_id == 'undefined' ){
			//TODO: Use reset() in BS.Window
			this.cbUsers.setValue( '' );
			this.tfComment.setValue( '' );
			return;
		}
		
		this.cbUsers.setValueByUserId( this.currentData.user_id );
		this.tfComment.setValue( this.currentData.comment );
	},

	getData: function() {
		var data = this.callParent( arguments );
		var user = this.cbUsers.getUserModel();
		data.user_id = user.get('user_id');
		data.user_name = user.get('user_name');
		data.user_display_name = user.get('display_name');
		data.comment = this.tfComment.getValue();
		return data;
	}
});