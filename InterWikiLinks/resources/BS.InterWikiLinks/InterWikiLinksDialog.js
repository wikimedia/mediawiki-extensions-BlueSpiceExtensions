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

Ext.define( 'BS.InterWikiLinks.InterWikiLinksDialog', {
	extend: 'BS.Window',
	currentData: {},
	selectedData: {},
	afterInitComponent: function() {
		this.tfIWLPrefix = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-interwikilinks-labelprefix' ).plain(),
			labelWidth: 85,
			labelAlign: 'right',
			name: 'iwl_prefix',
			allowBlank: false
		});
		this.tfIWLUrl = Ext.create( 'Ext.form.TextField', {
			fieldLabel: mw.message( 'bs-interwikilinks-labelurl' ).plain(),
			labelWidth: 85,
			labelAlign: 'right',
			name: 'iwl_url',
			allowBlank: false
		});

		this.items = [
			this.tfIWLPrefix,
			this.tfIWLUrl
		];

		this.callParent( arguments );
	},
	resetData: function() {
		this.tfIWLPrefix.reset();
		this.tfIWLUrl.reset();

		this.callParent();
	},
	setData: function( obj ) {
		this.currentData = obj;
		this.tfIWLPrefix.setValue( this.currentData.iwl_prefix );
		this.tfIWLUrl.setValue( this.currentData.iwl_url );
	},
	getData: function() {
		this.selectedData.iwl_prefix = this.tfIWLPrefix.getValue();
		this.selectedData.iwl_url = this.tfIWLUrl.getValue();
		this.selectedData.iwl_prefix_old = this.currentData.iwl_prefix;

		return this.selectedData;
	}
} );