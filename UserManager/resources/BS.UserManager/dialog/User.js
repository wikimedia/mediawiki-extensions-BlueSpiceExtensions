/**
 * UserManager UserDialog
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage UserManager
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.UserManager.dialog.User', {
	extend: 'BS.Window',
	currentData: {},
	selectedData: {},
	maxHeight: 620,
	afterInitComponent: function() {
		if( this.currentData.groups ) {
			this.cbGroups.setValue( this.getGroupsValue(this.currentData.groups) );
		}
		this.tfUserName = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message('bs-usermanager-headerusername').plain(),
			labelWidth: 130,
			labelAlign: 'right',
			name: 'username',
			allowBlank: false
		});
		this.tfPassword = Ext.create( 'Ext.form.TextField', {
			inputType: 'password',
			fieldLabel: mw.message('bs-usermanager-labelnewpassword').plain(),
			labelWidth: 130,
			labelAlign: 'right',
			name: 'pass',
			allowBlank: false
		});
		this.tfRePassword = Ext.create( 'Ext.form.TextField', {
			inputType: 'password',
			fieldLabel: mw.message('bs-usermanager-labelpasswordcheck').plain(),
			labelWidth: 130,
			labelAlign: 'right',
			name: 'repass',
			allowBlank: false
		});
		this.tfEmail = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message('bs-usermanager-headeremail').plain(),
			labelWidth: 130,
			labelAlign: 'right',
			name: 'email'
		});
		this.tfRealName = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message('bs-usermanager-headerrealname').plain(),
			labelWidth: 130,
			labelAlign: 'right',
			name: 'realname'
		});

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
			this.tfUserName,
			this.tfPassword,
			this.tfRePassword,
			this.tfEmail,
			this.tfRealName,
			this.cbGroups
		];

		this.callParent(arguments);
	},
	resetData: function() {
		this.tfUserName.reset();
		this.tfPassword.reset();
		this.tfRePassword.reset();
		this.tfEmail.reset();
		this.tfRealName.reset();
		this.cbGroups.reset();

		this.callParent(arguments);
	},
	setData: function( obj ) {
		this.currentData = obj;

		this.tfUserName.setValue( this.currentData.user_name );
		this.tfEmail.setValue( this.currentData.user_email );
		this.tfRealName.setValue( this.currentData.user_real_name );
		this.cbGroups.setValue( this.getGroupsValue(this.currentData.groups) );
	},
	getData: function() {
		this.selectedData.user_name = this.tfUserName.getValue();
		this.selectedData.user_password = this.tfPassword.getValue();
		this.selectedData.user_repassword = this.tfRePassword.getValue();
		this.selectedData.user_email = this.tfEmail.getValue();
		this.selectedData.user_real_name = this.tfRealName.getValue();
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