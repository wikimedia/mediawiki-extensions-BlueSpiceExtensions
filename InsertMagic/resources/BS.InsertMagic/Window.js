
Ext.define( 'BS.InsertMagic.Window', {
	extend: 'BS.Window',
	requires:[
		'Ext.Button'
	],
	id: 'bs-InsertMagic-dlg-window',
	modal: true,
	width: 600,
	height: 400,
	layout: 'border',
	singleton: true,

	afterInitComponent: function() {
		this.setTitle( mw.message('bs-insertmagic-dlg-title').plain() );

		var typesArray = [
			[ 'tag', mw.message('bs-insertmagic-type-tags').plain() ],
			[ 'switch', mw.message('bs-insertmagic-type-switches').plain() ],
			[ 'variable', mw.message('bs-insertmagic-type-variables').plain() ]
		];
		//TODO: Make hook?

		//HINT: http://stackoverflow.com/questions/4834285/extjs-combobox-acting-like-regular-select
		this.cmbType = Ext.create( 'Ext.form.ComboBox', {
			id: 'bs-InsertMagic-cmb-type',
			mode: 'local',
			triggerAction: 'all',
			editable: false,
			readonly: true,
			allowBlank: false,
			forceSelection: true,
			value:'tag', //default selection
			store: typesArray
		});
		this.cmbType.on( 'select', this.onTypeSelected, this );

		this.tagsStore = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl('InsertMagic::ajaxGetData'),
				reader: {
					type: 'json',
					root: 'result',
					idProperty: 'id'
				}
			},
			autoLoad: true,
			fields: ['id', 'type', 'name', 'desc', 'code' ],
			sortInfo: {
				field: 'name',
				direction: 'ASC'
			}
		});
		this.tagsStore.on( 'load',this.onStoreLoad, this );

		this.tagsGrid = Ext.create('Ext.grid.Panel', {
			title: '',
			id: 'bs-InsertMagic-grid-tag',
			sm: Ext.create( 'Ext.selection.RowModel', { singleSelect: true }),
			store: this.tagsStore,layout: 'fit',
			loadMask: true,
			columns: [
				{
					id: 'name',
					sortable: true,
					dataIndex: 'name'
				}],
			forceFit: true, //HINT: http://stackoverflow.com/questions/6545719/extjs-grid-how-to-make-column-width-100
			border: true,
			columnLines: false,
			enableHdMenu: false,
			stripeRows: true,
			hideHeaders: true,
			flex: 1,
			style: 'padding-top: 5px'
		});
		this.tagsGrid.on( 'select', this.onRowSelect, this );

		this.syntaxTextArea = Ext.create( 'Ext.form.TextArea', {
			id: 'bs-InsertMagic-textarea-syntax',
			hideLabel: true,
			name: 'syntaxTextArea',
			flex: 1,
			bodyPadding: 5
		});

		this.previewPanel = Ext.create('Ext.Panel', {
			id: 'bs-InsertMagic-panel-preview',
			border: true,
			flex: 1,
			bodyStyle: 'padding:5px;',
			autoScroll: true
		});

		this.descPanel = Ext.create('Ext.Panel', {
			id: 'bs-InsertMagic-panel-desc',
			border: true,
			flex: 1,
			autoScroll: true,
			bodyPadding: 5
		});

		this.pnlWest = Ext.create('Ext.Container', {
			region: 'west',
			width: 250,
			padding: 5,
			layout: {
				//HINT: http://dev.sencha.com/deploy/ext-3.3.1/examples/form/vbox-form.js
				type: 'vbox',
				align: 'stretch' // Child items are stretched to full width
			},
			items: [
				Ext.create( 'Ext.form.Label', { text: mw.message('bs-insertmagic-label-first').plain() }),
				this.cmbType,
				this.tagsGrid,
				Ext.create( 'Ext.form.Label', { text: mw.message('bs-insertmagic-label-desc').plain(), style: 'padding-top: 10px' } ),
				this.descPanel
			]
		});

		this.pnlCenter = Ext.create('Ext.Container', {
			region: 'center',
			border: false,
			padding: 5,
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			items:[
				Ext.create( 'Ext.form.Label', { text: mw.message('bs-insertmagic-label-second').plain() }),
				this.syntaxTextArea
			]
		});

		this.items = [
			this.pnlWest,
			this.pnlCenter
		];

		this.callParent(arguments);
	},

	getData: function() {
		this.currentData.code = this.syntaxTextArea.getValue();
		return this.currentData;
	},

	setData: function( obj ) {
		this.syntaxTextArea.setValue( obj.code );
		this.callParent( arguments );
	},

	onStoreLoad: function( store, records, options ) {
		this.tagsStore.sort( 'name', 'ASC' );
		this.tagsStore.filter( 'type', 'tag'); //just initial
	},

	onTypeSelected: function( combo, record, index ){
		this.tagsStore.removeFilter();
		//record[0] because of single select
		//field1 is because of ArrayStore. Could be optimized.
		this.tagsStore.filter( 'type', record[0].get( 'field1' ) );
	},

	onRowSelect: function( grid, record, index, eOpts ) {
		var data = {
			desc : record.get( 'desc' ),
			type : record.get( 'type' )
		};
		this.currentData.type = data.type;
		this.currentData.name = record.get( 'name' );

		this.setCommonFields( record.get( 'code' ), data );
	},

	setCommonFields: function( text, data ) {
		this.descPanel.update(data.desc);
		this.syntaxTextArea.setValue( text );
		this.syntaxTextArea.focus();

		var start = text.indexOf('"') + 1;
		var end = text.indexOf('"', start );
		if( data.type != 'tag' ) {
			start = start - 1;
			end = end + 1;
		}
		this.syntaxTextArea.selectText(start, end);
	}
});