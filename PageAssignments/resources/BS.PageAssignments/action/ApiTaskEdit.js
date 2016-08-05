Ext.define('BS.PageAssignments.action.ApiTaskEdit', {
	extend: 'BS.action.Base',

	//Custom Settings
	pageId: -1,
	pageAssignments: [],

	execute: function() {
		var dfd = $.Deferred();
		this.actionStatus = BS.action.Base.STATUS_RUNNING;
		var data = {
			pageId: this.pageId,
			pageAssignments: this.pageAssignments
		};

		this.doApiEditPageAssignment( dfd, data );

		return dfd.promise();
	},

	doApiEditPageAssignment: function( dfd, data ) {
		var me = this;

		var api = new mw.Api();
		api.postWithToken( 'edit', {
			'action': 'bs-pageassignment-tasks',
			'task': 'edit',
			'taskData': Ext.encode( data )
		})
		.fail(function( code, errResp ){
			me.actionStatus = BS.action.Base.STATUS_ERROR;
			dfd.reject( me, data, errResp );
		})
		.done(function( resp, jqXHR ){
			if( !resp.success ) {
				me.actionStatus = BS.action.Base.STATUS_ERROR;
				dfd.reject( me, data, resp );
				return;
			}

			me.actionStatus = BS.action.Base.STATUS_DONE;
			dfd.resolve( me );
		});
	},

	getDescription: function() {
		return mw.message('bs-pageassignments-action-apiedit-description', this.pageTitle).parse();
	}
});