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
	textField: null,
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
	 * Reference to the caracter counter
	 * @var jQuery
	 */
	characterCounter: null,
	/**
	 * Reference to the jQuery Tab
	 * @var jQuery
	 */
	shoutboxTab: null,
	/**
	 * Sup Element for the ShoutboxTab
	 * @var jQuery
	 */
	shoutboxTabCounter: null,
	/**
	 * Load and display a current list of shouts from server
	 * @param sblimit int Maximum number of shouts before more link is displayed
	 */
	updateShoutbox: function( sblimit ) {
		if ( typeof sblimit == 'undefined' )
			sblimit = 0;
		$( document ).trigger( "onBsShoutboxBeforeUpdated", [ BsShoutBox ] );
		BsShoutBox.ajaxLoader.fadeIn();

		this.msgList.load(
				bs.util.getAjaxDispatcherUrl( 'ShoutBox::getShouts', [ wgArticleId, sblimit ] ),
				function( data ) {
					BsShoutBox.msgList.slideDown();
					BsShoutBox.btnSend.blur().removeAttr( 'disabled' ); //reactivate the send button
					BsShoutBox.textField.val( BsShoutBox.defaultMessage );
					BsShoutBox.textField.blur().removeAttr( 'disabled' );
					BsShoutBox.ajaxLoader.fadeOut();
					BsShoutBox.characterCounter.text( mw.message( 'bs-shoutbox-charactersleft', BsShoutBox.textField.attr( 'maxlength' ) ).text() );
					BsShoutBox.shoutboxTabCounter.text( $( "#bs-sb-count-all" ).text() );
					//statebar element
					if ($('#sb-Shoutbox-text a').length !== 0) {
						var nshoutsmsg = mw.message( 'bs-shoutbox-n-shouts', $( "#bs-sb-count-all" ).text() ).text();
						$('#sb-Shoutbox-text a').text( nshoutsmsg );
						$('#sb-Shoutbox-text a').attr( 'title', nshoutsmsg );
					}
					$( document ).trigger( "onBsShoutboxAfterUpdated", [ BsShoutBox ] );
				}
		);
	},
	archiveEntry: function( iShoutID ) {
		$( "#bs-sb-error" ).empty();
		$( document ).trigger( "onBsShoutboxBeforeArchived", [ BsShoutBox ] );
		BsShoutBox.ajaxLoader.fadeIn();
		$.post(
				bs.util.getAjaxDispatcherUrl( 'ShoutBox::archiveShout', [ iShoutID, wgArticleId ] ),
				function( data ) {
					BsShoutBox.updateShoutbox();
					$( "#bs-sb-error" ).html( data ).fadeIn().delay( "1500" ).fadeOut();
					$( document ).trigger( "onBsShoutboxAfterArchived", [ BsShoutBox ] );
				}
		);
	}
};

mw.loader.using( 'ext.bluespice', function() {
	$( "#bs-sb-content" ).before( $( "<div id='bs-sb-error'></div>" ) );
	BsShoutBox.textField = $( "#bs-sb-message" );
	BsShoutBox.btnSend = $( "#bs-sb-send" );
	BsShoutBox.msgList = $( "#bs-sb-content" );
	BsShoutBox.ajaxLoader = $( "#bs-sb-loading" );
	BsShoutBox.defaultMessage = BsShoutBox.textField.val();
	BsShoutBox.characterCounter = $( '#bs-sb-charactercounter' );
	BsShoutBox.shoutboxTab = $( "#bs-data-after-content-tabs a[href='#bs-shoutbox']" );
	BsShoutBox.shoutboxTabCounter = $( "<sup class='bs-sb-tab-counter'>" );
	if ( typeof ( BsShoutBox.shoutboxTab ) !== "undefined" )
		BsShoutBox.shoutboxTab.after( BsShoutBox.shoutboxTabCounter );
	BsShoutBox.updateShoutbox();

	//HTML5 like placeholder effect.

	BsShoutBox.textField
			.focus( function() {
				if ( $( this ).val() == BsShoutBox.defaultMessage )
					$( this ).val( '' );
			}
			).blur( function() {
		if ( $( this ).val() == '' ) {
			$( this ).val( BsShoutBox.defaultMessage );
		}
	} );

	BsShoutBox.textField.bind( "input propertychange", function( e ) {
		var currCharLen = $( this ).attr( 'maxlength' ) - $( this ).val().length;

		BsShoutBox.characterCounter.text( mw.message( 'bs-shoutbox-charactersleft', currCharLen ).text() );
	} );

	$( "#bs-sb-form" ).submit( function() {
		var sMessage = BsShoutBox.textField.val();
		if ( sMessage == '' || sMessage == BsShoutBox.defaultMessage ) {
			bs.util.alert(
					'bs-shoutbox-alert',
					{
						textMsg: 'bs-shoutbox-entermessage'
					}
			);
			return false;
		}

		//we deactivate submit button while sending
		BsShoutBox.btnSend.blur().attr( 'disabled', 'disabled' );
		BsShoutBox.textField.blur().attr( 'disabled', 'disabled' );

		$.post(
				bs.util.getAjaxDispatcherUrl( 'ShoutBox::insertShout', [ wgArticleId, sMessage ] ),
				function( data ) {
					var responseObj = $.parseJSON( data );
					if ( responseObj.success === false ) {
						bs.util.alert(
								'bs-shoutbox-alert',
								{
									textMsg: responseObj.msg
								}
						);
					}
					BsShoutBox.updateShoutbox();
				}
		);

		//we prevent the refresh of the page after submitting the form
		return false;
	} );

	$( document ).on( 'click', '.bs-sb-archive', function() {
		var iShoutID = $( this ).parent().attr( 'id' );
		bs.util.confirm(
				'bs-shoutbox-confirm',
				{
					titleMsg: 'bs-shoutbox-confirm-title',
					textMsg: 'bs-shoutbox-confirm-text'
				},
		{
			ok: function() {
				BsShoutBox.archiveEntry( iShoutID.replace( /bs-sb-/, "" ) );
			}
		} );
	} );

	$('#sb-Shoutbox-link').click( function() {
		$("#bs-data-after-content").tabs('select','#bs-shoutbox');
	});
} );