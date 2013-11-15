/**
 * StateBar extension
 *
 *
 * @author     Robert Vogel <vogel@hallowelt.biz>
 * @author     Patric Wirth <wirth@hallowelt.biz>
 * @version    1.0.0 stable

 * @package    Bluespice_Extensions
 * @subpackage StateBar
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

BsStateBar = {
	oViewToggler: null,
	oStateBarView: null,
	bAjaxCallComplete: false,
	sStateBarBodyLoadView: '<div id="sStateBarBodyLoadView"><center><img src="' + wgScriptPath + '/extensions/BlueSpiceFoundation/resources/bluespice/images/bs-ajax-loader-bar-blue.gif" /></center></div>',
	aRegisteredToggleClickElements: new Array(),
	imagePathActive: wgScriptPath+'/skins/BlueSpiceSkin/resources/images/desktop/statusbar-btn_less.png',
	imagePathInactive: wgScriptPath+'/skins/BlueSpiceSkin/resources/images/desktop/statusbar-btn_more.png',

	getStateBarBody: function(){
		if ( BsStateBar.bAjaxCallComplete === true ) return;

		this.oStateBarView.html(this.sStateBarBodyLoadView);
		$.each( this.aRegisteredToggleClickElements, function( key, value ) {
			value.unbind('click');
		} );

		$.getJSON(
			wgScriptPath + '/index.php',
			{
				action:'ajax',
				rs:'StateBar::ajaxCollectBodyViews',
				articleID: wgArticleId
			},
			function( result ) {
				if ( result['success'] === true ) {
					$('#sStateBarBodyLoadView').slideToggle('fast');

					if ( result['views'].length < 1 ) {
						//console.log(result['message']);
						var messageItem = $('<div class="bs-statebar-body-item style="display:none"><p>' + result['message'] + '</p></div>').filter('DIV.bs-statebar-body-item');
						BsStateBar.oStateBarView.append(messageItem.slideToggle('fast'));
						$.each( BsStateBar.aRegisteredToggleClickElements, function( key, value ) {
							BsStateBar.viewTogglerClick(value);
						});
						return;
					}

					$.each(result['views'], function( key, value ) {
						var bodyItem = $(value).filter('DIV.bs-statebar-body-item');
						bodyItem.hide();
						BsStateBar.oStateBarView.append(bodyItem.slideToggle('fast'));
					});

					$.each(BsStateBar.aRegisteredToggleClickElements, function( key, value ) {
						BsStateBar.viewTogglerClick(value);
					});
					$(document).trigger( 'BsStateBarBodyLoadComplete', [result['views']] );
				} else {
					//console.log(result);
				}
			}
		);
		this.bAjaxCallComplete = true;
	},

	bindViewTogglerClick: function(){
		//TODO: use class="bs-statebar-viewtoggler" instead of trigger
		this.aRegisteredToggleClickElements.push(this.oViewToggler);
		$(document).trigger( 'BsStateBarRegisterToggleClickElements', [this.aRegisteredToggleClickElements] );

		$.each(this.aRegisteredToggleClickElements, function( key, value ) {
			BsStateBar.viewTogglerClick(value);
		});
	},

	viewTogglerClick: function( inputObject ) {
		inputObject.click(function(){
			BsStateBar.oStateBarView.slideToggle( 'fast' );
			var sCurImg = $('#bs-statebar-viewtoggler-image').attr( 'src' );
			sCurImg.match('_more')
				? $('#bs-statebar-viewtoggler-image').attr( 'src', BsStateBar.imagePathActive )
				: $('#bs-statebar-viewtoggler-image').attr( 'src', BsStateBar.imagePathInactive );

			BsStateBar.getStateBarBody();
		});
	},

	init: function() {
		this.oViewToggler = $('#bs-statebar-viewtoggler, .bs-statebar-viewtoggler');
		this.oStateBarView = $('#bs-statebar-view');

		this.bindViewTogglerClick();
	}
};

$( document ).ready( function() {
	BsStateBar.init();
} );