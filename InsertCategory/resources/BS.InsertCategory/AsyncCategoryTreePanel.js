Ext.define( 'BS.InsertCategory.AsyncCategoryTreePanel', {
	extend: 'Ext.tree.Panel',
	/*viewConfig: {
		plugins: {
			ptype: 'treeviewdragdrop'
		}
	},*/
	width: 300,
	title: mw.message( 'bs-insertcategory-panel-title' ).plain(),
	useArrows: true,
	rootVisible: false,
	displayField: 'text',

	initComponent: function() {
		this.store = Ext.create('Ext.data.TreeStore', {
			proxy: {
				type: 'ajax',
				url: bs.api.makeUrl( 'bs-category-treestore' )
			},
			defaultRootProperty: 'results',
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