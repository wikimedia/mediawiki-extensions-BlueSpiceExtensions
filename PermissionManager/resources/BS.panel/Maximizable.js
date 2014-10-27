Ext.define('BS.panel.Maximizable', {
	extend: 'Ext.Window',
	draggable: false,
	shadow: false,
	closable: false,
	border: false,
	oldX: 0,
	oldY: 0,
	initComponent: function() {
		this.height = Ext.get(this.renderTo).getHeight();
		this.width = Ext.get(this.renderTo).getWidth();
		this.maximizeTool = new Ext.panel.Tool({
			type: 'maximize'
		});
		this.maximizeTool.on('click', this.onMaximizeToolClick, this);

		this.minimizeTool = new Ext.panel.Tool({
			type: 'minimize'
		});
		this.minimizeTool.on('click', this.onMinimizeToolClick, this);
		this.minimizeTool.setVisible(false);

		this.tools = [
			this.maximizeTool,
			this.minimizeTool
		];

		this.on('afterrender', this.onAfterRender, this);

		this.callParent(arguments);
	},
	onAfterRender: function(sender, eOpts) {
		this.showAt(sender.container.getXY());
	},
	onMaximizeToolClick: function() {
		Ext.get(this.renderTo).addCls('fullscreen');
		this.updateSize();
		this.maximizeTool.setVisible(false);
		this.minimizeTool.setVisible(true);
		this.showAt(Ext.get(this.renderTo).getXY());
	},
	onMinimizeToolClick: function() {
		Ext.get(this.renderTo).removeCls('fullscreen');

		this.updateSize();
		this.maximizeTool.setVisible(true);
		this.minimizeTool.setVisible(false);
		this.showAt(Ext.get(this.renderTo).getXY());
	},
	firstCall: true,
	showAt: function(x, y) {
		if (this.firstCall) {
			this.oldX = x;
			this.oldY = y;
			this.firstCall = false;
		}
		this.callParent(arguments);
	},
	updateSize: function() {
		this.setHeight(Ext.get(this.renderTo).getHeight());
		this.setWidth(Ext.get(this.renderTo).getWidth());
	}
});