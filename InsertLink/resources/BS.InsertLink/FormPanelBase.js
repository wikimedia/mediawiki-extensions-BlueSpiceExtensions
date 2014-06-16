/**
 * InsertLink Panel
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage InsertLink
 * @copyright  Copyright (C) 2013 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

Ext.define( 'BS.InsertLink.FormPanelBase', {
	extend: 'Ext.form.Panel',
	pnlMainConf: {
		items: null
	},
	fieldDefaults: {
		labelAlign: 'right'
	},
	layout: {
		type: 'vbox',
		align: 'stretch'
	},
	padding: '5px',
	border: false,
	
	constructor: function() {
		this.items = [];
		this.callParent(arguments);
	},
	
	initComponent: function() {

		this.beforeInitComponent();

		this.tfDesc = Ext.create( 'Ext.form.TextField', {
			fieldLabel : mw.message('bs-insertlink-label-description').plain(),
			name : 'inputDesc',
			value: ''
		});

		this.pnlMainConf.items.push(this.tfDesc);

		this.items = this.pnlMainConf.items;

		this.callParent(arguments);
	},

	beforeInitComponent: function() {},
	resetData: function() {
		this.tfDesc.reset();
	},
	setData: function( obj ) {
		//if( !obj.desc || this.tfDesc.getValue() != '' ) return;
		if( !obj.desc ) return;
		this.tfDesc.setValue(obj.desc);
	},
	getData: function() {
		return this.getDescription();
	},
	setDescription: function( desc ) {
		this.tfDesc.setValue( desc );
	},
	getDescription: function() {
		return this.tfDesc.getValue();
	}
});