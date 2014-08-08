Ext.define( 'BS.Review.ReviewPanel', {
	extend: 'BS.Panel',
	layout: 'form',

	afterInitComponent: function() {
		var today = new Date();
		var nextWeek = new Date(today.getTime() + 7 * 24 * 60 * 60 * 1000);

		this.dfStart = new Ext.create('Ext.form.field.Date',{
			fieldLabel: mw.message('bs-review-lblstartdate' ).plain(),
			margin: '0 5 0 0',
			value: new Date(),
			minValue: today,
			labelAlign: 'right'
		});

		this.dfEnd = new Ext.create('Ext.form.field.Date',{
			fieldLabel:mw.message('bs-review-lblenddate' ).plain(),
			value: nextWeek,
			labelAlign: 'right'
		});

		this.gdSteps = Ext.create('BS.Review.StepsGrid');

		var items = [
			{
				xtype: 'fieldcontainer',
				combineErrors: true,
				layout: 'hbox',
				defaults: {
					flex: 1
				},
				items: [
					this.dfStart,
					this.dfEnd
				]
			},
			this.gdSteps
		];

		$(document).trigger( 'bsspecialreviewbeforecreateform', [items] );
		$(document).trigger( 'BSReviewPanelAfterInitComponent', [this, items]);

		this.items = items;

		this.callParent();
	},

	setData: function( obj ) {
		this.callParent( arguments );

		if( this.currentData.startdate ) {
			this.dfStart.setValue( new Date( this.currentData.startdate * 1000 ) );
			this.dfEnd.setValue( new Date( this.currentData.enddate * 1000 ) );
		}

		if( this.currentData.userCanEdit == false ) {
			this.dfStart.disable();
			this.dfEnd.disable();
		}

		this.gdSteps.enableEditing( this.currentData.userCanEdit );
		if( this.currentData.steps ) {
			this.gdSteps.setData( this.currentData.steps );
		}
	},

	getData: function() {
		//TODO: refactor saveReview()!
		return {
			cmd: 'insert',
			pid: this.currentData.page_id,
			editable: true,
			sequential: true,
			abortable: true,
			startdate: this.dfStart.getValue(),
			enddate: this.dfEnd.getValue(),
			steps: this.gdSteps.getData()
		};
	},

	saveReview: function() {
		obj = this.getData();

		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl( 'Review::doEditReview'),
			params: {
				review: Ext.encode(obj),
				pid: obj.pid,
				cmd: 'insert'
			},
			success: function(response) {
				result = Ext.decode( response.responseText );
				if(result.messages.length) {
					var tmp = '';
					for(i in result.messages) {
						if(typeof(result.messages[i]) != 'string') {
							continue;
						}
						tmp = tmp + result.messages[i] + '<br />';
					}
					bs.util.alert(
						'bs-review-save-alert',
						{
							titleMsg: 'bs-review-colstatus',
							text: tmp
						},
						{
							ok: function(){
								window.location.reload();
							}
						}
					);
				}
			},
			failure: function(response) {
				result = Ext.decode( response.responseText );
				if(result.messages.length) {
					var tmp = '';
					for(i in result.messages) {
						if(typeof(result.messages[i]) != 'string') {
							continue;
						}
						tmp = tmp + result.messages[i] + '<br />';
					}

					bs.util.alert(
						'bs-review-save-alert',
						{
							titleMsg: 'bs-review-colstatus',
							text: tmp
						}
					);
				}
			},
			scope: this
		});
	},

	deleteReview: function() {
		bs.util.confirm(
			'bs-review-delete',
			{
				textMsg: 'bs-review-confirm-delete-review'
			},
			{
				ok: this.doDeleteReview,
				scope: this
			}
		);
	},

	doDeleteReview: function() {
		Ext.Ajax.request({
			url: bs.util.getAjaxDispatcherUrl( 'Review::doEditReview'),
			params: {
				pid: this.currentData.page_id,
				cmd: 'delete'
			},
			success: function(response) {
				result = Ext.decode( response.responseText );
				if(result.messages.length) {
					var tmp = '';
					for(i in result.messages) {
						if(typeof(result.messages[i]) != 'string') {
							continue;
						}
						tmp = tmp + result.messages[i] + '<br />';
					}
					bs.util.alert(
						'bs-review-delete-alert',
						{
							text: tmp
						},
						{
							ok: function(){
								window.location.reload();
							}
						}
					);
				}
			}
		});
	}
});