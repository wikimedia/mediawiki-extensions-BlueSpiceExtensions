Ext.define( 'BS.PageAssignments.model.Assignable', {
	extend: 'Ext.data.Model',
	 fields: [
		{name: 'type',  type: 'string'},
		{name: 'id',   type: 'string' },
		{name: 'label', type: 'string'},
		{name: 'anchor', type: 'string' }
	]
});