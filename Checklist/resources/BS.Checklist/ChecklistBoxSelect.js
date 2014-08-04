Ext.define( 'BS.Checklist.ChecklistBoxSelect', {
	extend:'Ext.ux.form.field.BoxSelect',
	requires: [ 'BS.model.Checklist' ],
	displayField: 'text',
	valueField: 'text',
	anchor: '95%',
	growMin: 150,
	pinList: true,
	triggerOnClick: true,
	triggerAction: 'all',
	filterPickList: true,
	stacked: true,
	forceSelection: false,
	createNewOnEnter: true,
	queryMode: 'local',
	delimiter: ',',
	deferredSetValueConf: false,
	initComponent: function() {
		this.store = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'Checklist::getAvailableOptions' ),
				reader: {
					type: 'json',
					root: 'categories',
					idProperty: 'cat_id'
				}
			},
			model: 'BS.model.Checklist'
		});
		this.store.load();

		this.callParent(arguments);
	}
});