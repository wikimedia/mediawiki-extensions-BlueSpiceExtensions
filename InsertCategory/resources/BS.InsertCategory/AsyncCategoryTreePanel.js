Ext.define( 'BS.InsertCategory.AsyncCategoryTreePanel', {
	extend: 'Ext.tree.Panel',
	/*viewConfig: {
		plugins: {
			ptype: 'treeviewdragdrop'
		}
	},*/
	width: 250,
	title: 'Categories',
	useArrows: true,
	rootVisible: false,
	displayField: 'text',

	initComponent: function() {
		this.store = Ext.create('Ext.data.TreeStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getCAIUrl('getAsyncCategoryTreeStoreData')
			},
			root: {
				text: 'Categories',
				id: 'src',
				expanded: true
			},
			model: 'BS.model.Category'
		});
		
		this.callParent();
	}
});