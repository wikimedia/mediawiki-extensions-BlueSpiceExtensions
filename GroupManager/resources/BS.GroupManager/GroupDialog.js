/**
 * UserManager UserDialog
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.com>
 * @author     Stephan Muggli <muggli@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.GroupManager.GroupDialog', {
	extend: 'BS.Window',
	currentData: {},
	selectedData: {},
	afterInitComponent: function() {
		this.tfGroupName = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-groupmanager-lablename' ).plain(),
			labelWidth: 85,
			labelAlign: 'right',
			name: 'groupname',
			allowBlank: false
		});

		this.items = [
			this.tfGroupName
		];

		this.callParent(arguments);
	},
	resetData: function() {
		this.tfGroupName.reset();

		this.callParent();
	},
	setData: function( obj ) {
		this.currentData = obj;
		this.tfGroupName.setValue( this.currentData.group_name );
	},
	getData: function() {
		this.selectedData.group_name = this.tfGroupName.getValue();
		this.selectedData.group_name_old = this.currentData.group_name;

		return this.selectedData;
	}
} );