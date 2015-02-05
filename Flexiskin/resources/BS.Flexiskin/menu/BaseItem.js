Ext.define('BS.Flexiskin.menu.BaseItem', {
	extend: 'Ext.Panel',
	layout: 'form',
	currentData: {},
	initComponent: function() {
		this.callParent(arguments);
	},
	getData: function() {
		this.callParent(arguments);
	},
	setData: function(data) {
		this.callParents(arguments);
	},
	setColor: function(el, clr, textfield) {
		if( typeof clr == "undefined" || clr == null) return;

		var bFound = false;
		clr = clr.replace('#', "");
		Ext.Array.each(el.colors, function(val) {
			if (clr == val) {
				bFound = true;
			}
		});
		if (bFound == false){
			if (textfield)
				textfield.setValue(clr);
			el.clear();
		}
		else
			el.select(clr);
	}
});