/**
 * ShoutBox Extension
 *
 * Inspiration by
 * Adrian "yEnS" Mato Gondelle & Ivan Guardado Castro
 * www.yensdesign.com
 * yensamg@gmail.com
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @author     Karl Waldmanstetter
 * @version    1.1.0
 * @version    $Id: ShoutBox.js 9418 2013-05-16 14:01:58Z rvogel $
 * @package    Bluespice_Extensions
 * @subpackage ShoutBox
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/* Changelog
 * v1.1.0
 * - Reworked code
 * v0.1
 * - initial commit
 */

//Last Code review RBV (30.06.2011)

/**
 * Base class for all ShoutBox related methods and properties
 */
BsShoutBox = {
	/**
	 * Reference to input field where current message is stored
	 * @var jQuery text input field
	 */
	textField : null,
	/**
	 * Default value of message field, taken from message input box
	 * @var string 
	 */
	defaultMessage: '',
	/**
	 * Reference to the message list div
	 * @var jQuery
	 */
	msgList: null,
	/**
	 * Reference to the send button
	 * @var jQuery 
	 */
	btnSend: null,
	/**
	 * Reference to the ajax loader gif
	 * @var jQuery 
	 */
	ajaxLoader: null,

	/**
	 * Load and display a current list of shouts from server
	 * @param sblimit int Maximum number of shouts before more link is displayed
	 */
	updateShoutbox: function(sblimit) {
		if ( typeof(sblimit) == 'undefined' ) sblimit = 0;
		BsShoutBox.ajaxLoader.fadeIn();

		this.msgList.load( 
			BlueSpice.buildRemoteString( "ShoutBox", "getShouts" ),
			{
				"articleid" : wgArticleId,
				"sblimit"   : sblimit
			},
			function( data ) {
				BsShoutBox.msgList.slideDown();
				BsShoutBox.btnSend.blur().removeAttr( 'disabled' ); //reactivate the send button
				BsShoutBox.textField.val( BsShoutBox.defaultMessage );
				BsShoutBox.textField.blur().removeAttr( 'disabled' );
				BsShoutBox.ajaxLoader.fadeOut();
			}
		);
	},

	archiveEntry: function(iShoutID){
		$("#bs-sb-error").empty();
		BsShoutBox.ajaxLoader.fadeIn();
		$.post( 
			BlueSpice.buildRemoteString( "ShoutBox", "archiveShout"),
			{
				"shoutID" : iShoutID
			},
			function( data ) {
				BsShoutBox.updateShoutbox();
				$("#bs-sb-error").html(data).fadeIn().delay("1500").fadeOut();
			}
		);
	}
};

$(document).ready(function(){
	$("#bs-sb-content").before($("<div id='bs-sb-error'></div>"));
	BsShoutBox.textField = $( "#bs-sb-message" );
	BsShoutBox.btnSend   = $( "#bs-sb-send" );
	BsShoutBox.msgList   = $( "#bs-sb-content" );
	BsShoutBox.ajaxLoader = $( "#bs-sb-loading" );
	BsShoutBox.defaultMessage =  BsShoutBox.textField.val();
	BsShoutBox.updateShoutbox();

	//HTML5 like placeholder effect.

	BsShoutBox.textField
		.focus( function(){
			if ($(this).val() == BsShoutBox.defaultMessage)
				$(this).val('');
			}
		).blur( function(){ 
			if( $(this).val() == '' ) {
				$(this).val( BsShoutBox.defaultMessage );
			}
		});

	$( "#bs-sb-form" ).submit( function() {
		var sMessage = BsShoutBox.textField.val();
		if( sMessage == '' || sMessage == BsShoutBox.defaultMessage ) {
			BlueSpice.alert( mw.msg('bs-shoutbox-enterMessage') );
			return false;
	}

	//we deactivate submit button while sending
	BsShoutBox.btnSend.blur().attr( 'disabled', 'disabled' );
	BsShoutBox.textField.blur().attr( 'disabled', 'disabled' );

	$.post( 
		BlueSpice.buildRemoteString( "ShoutBox", "insertShout"),
		{
			"articleid" : wgArticleId,
			"message" : sMessage
		},
		function( data ) {
			BsShoutBox.updateShoutbox();
		}
	);

	//we prevent the refresh of the page after submitting the form
	return false;
	});

	$(".bs-sb-archive").live("click", function(){
		var iShoutID = $(this).parent().attr('id');
		Ext.Msg.confirm(
			mw.msg('bs-shoutbox-confirm_title'),
			mw.msg('bs-shoutbox-confirm_text'),
			function(btn, text){
				if(btn == 'yes') {
					BsShoutBox.archiveEntry(iShoutID.replace(/bs-sb-/, ""));
				}
				return;
			}
		);
	});
});