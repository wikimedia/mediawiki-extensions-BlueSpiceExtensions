/**
 * UserManager UserGroups Dialog
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2014 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.UserManager.dialog.UserGroups', {
	extend: 'BS.Window',
	currentData: {},
	selectedData: {},
	maxHeight: 620,
	title: mw.message('bs-usermanager-headergroups').plain(),
	afterInitComponent: function() {
		if( this.currentData.groups ) {
			this.cbGroups.setValue( this.getGroupsValue(this.currentData.groups) );
		}

		this.cbGroups = Ext.create( 'Ext.ux.form.MultiSelect', {
			fieldLabel: mw.message('bs-usermanager-headergroups').plain(),
			labelWidth: 130,
			labelAlign: 'right',
			store: this.strGroups,
			valueField: 'group',
			displayField: 'displayname',
			maxHeight: 350
		} );

		this.items = [
			this.cbGroups
		];

		this.callParent(arguments);
	},
	resetData: function() {
		this.cbGroups.reset();

		this.callParent(arguments);
	},
	setData: function( obj ) {
		this.currentData = obj;
		this.cbGroups.setValue( this.getGroupsValue(this.currentData.groups) );
	},
	getData: function() {
		this.selectedData.groups = this.cbGroups.getValue();

		return this.selectedData;
	},

	getGroupsValue: function( data ) {
		var groups = [];
		for( var i = 0; i < data.length; i++ ) {
			groups.push( data[i].group );
		}
		return groups;
	}
} );