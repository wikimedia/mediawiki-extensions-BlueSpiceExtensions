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
			fieldLabel: mw.message( 'bs-namespacemanager-labelNamespaceName' ).plain(),
			labelWidth: 130,
			labelAlign: 'right',
			name: 'namespacename',
			allowBlank: false
		});
		this.fCbSubpages = Ext.create( 'Ext.form.field.Checkbox', {
			boxLabel: mw.message( 'bs-namespacemanager-headerIsSubpagesNamespace' ).plain(),
			name: 'cb-subpages',
			checked: true,
			allowBlank: false
		});
		this.fCbSearchable = Ext.create( 'Ext.form.field.Checkbox', {
			boxLabel: mw.message( 'bs-namespacemanager-headerIsSearchableNamespace' ).plain(),
			name: 'cb-searchable',
			checked: true,
			allowBlank: false
		});
		this.fCbEvaluable = Ext.create( 'Ext.form.field.Checkbox', {
			boxLabel: mw.message( 'bs-namespacemanager-headerIsContentNamespace' ).plain(),
			name: 'cb-evaluable',
			checked: true,
			allowBlank: false
		});

		this.items = [
			this.tfNamespaceName,
			this.fCbSubpages,
			this.fCbSearchable,
			this.fCbEvaluable
		];

		this.callParent(arguments);
	},
	resetData: function() {
		this.tfNamespaceName.reset();
		this.fCbSubpages.reset();
		this.fCbSearchable.reset();
		this.fCbEvaluable.reset();

		this.callParent();
	},
	setData: function( obj ) {
		this.currentData = obj;

		this.tfNamespaceName.setValue( this.currentData.name );
		this.fCbSubpages.setValue( this.currentData.subpages );
		this.fCbSearchable.setValue( this.currentData.searchable );
		this.fCbEvaluable.setValue( this.currentData.evaluable );
	},
	getData: function() {
		this.selectedData.id = this.currentData.id;
		this.selectedData.name = this.tfNamespaceName.getValue();
		this.selectedData.subpages = this.fCbSubpages.getValue();
		this.selectedData.searchable = this.fCbSearchable.getValue();
		this.selectedData.evaluable = this.fCbEvaluable.getValue();

		return this.selectedData;
	}
} );