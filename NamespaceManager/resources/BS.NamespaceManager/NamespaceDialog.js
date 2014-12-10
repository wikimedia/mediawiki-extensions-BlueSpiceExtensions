/**
 * NamespaceManager NamespaceDialog
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Stephan Muggli <muggli@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage NamespaceManager
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.NamespaceManager.NamespaceDialog', {
	extend: 'BS.Window',
	currentData: {},
	selectedData: {},
	afterInitComponent: function() {
		this.tfNamespaceName = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-namespacemanager-labelnsname' ).plain(),
			labelWidth: 130,
			labelAlign: 'right',
			name: 'namespacename',
			allowBlank: false
		});
		
		this.items = [
			this.tfNamespaceName
		];
		this.checkboxControls = {};
		
		//TODO: this is not nice since it introduces an dependency
		var fieldDefs = mw.config.get('bsNamespaceManagerMetaFields');
		
		for( var i = 0; i < fieldDefs.length; i++ ) {
			var fieldDef = fieldDefs[i];
			if( fieldDef.type !== 'boolean' || fieldDef.name === 'editable' ) {
				continue;
			}
			var cbControl =  Ext.create( 'Ext.form.field.Checkbox', {
				boxLabel: fieldDef.label,
				name: 'cb-'+fieldDef.name
			});
			this.checkboxControls[fieldDef.name] = cbControl;
			this.items.push( cbControl );
		}

		this.callParent(arguments);
	},
	resetData: function() {
		this.tfNamespaceName.reset();
		for( var name in this.checkboxControls ) {
			this.checkboxControls[name].reset();
		}

		this.callParent();
	},
	setData: function( obj ) {
		this.currentData = obj;
		
		if(this.currentData.editable) {
			this.tfNamespaceName.enable();
		}
		else {
			this.tfNamespaceName.disable();
		}

		this.tfNamespaceName.setValue( this.currentData.name );
		for( var name in this.checkboxControls ) {
			this.checkboxControls[name].setValue( this.currentData[name] );
		}
	},
	getData: function() {
		this.selectedData.id = this.currentData.id;
		this.selectedData.name = this.tfNamespaceName.getValue();
		for( var name in this.checkboxControls ) {
			this.selectedData[name] = this.checkboxControls[name].getValue();
		}

		return this.selectedData;
	}
} );