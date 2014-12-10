/**
 * SaferEdit extension
 *
 * Part of BlueSpice for MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.biz>
 * @package    Bluespice_Extensions
 * @subpackage SaferEdit
 * @copyright  Copyright (C) 2011 Hallo Welt! - Medienwerkstatt GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v2 or later
 * @filesource
 */

/**
 * Base class for all safer edit related methods and properties
 */
BsSaferEditEditMode = {
	/**
	 * Time between two intermediate saves
	 * @var integer time in seconds
	 */
	interval: bsSaferEditInterval * 1000,
	/**
	 * Pointer to javascript timeout callback, needed to cancel timeout when changes are saved
	 * @var pointer javascript timeout callback
	 */
	timeout: false,
	/**
	 * Store for older text before safer edit saving in order to compare it to current text to see if any changes were made
	 * @var string text at time of page call or last safer edit saving
	 */
	oldText: '',
	/**
	 * Store for initial text before saving in order to compare it to current text to see if any changes were made
	 * @var string text at time of page call or last saving
	 */
	origText: '',
	/**
	 * Indicates if text was changed client side since the last safer edit saving
	 * @var bool true if text was changed
	 */
	isDirty: false,
	/**
	 * Indicates if text was changed client side since the last user saving
	 * @var bool true if text was changed
	 */
	isUnsaved: false,
	/**
	 * Indicates if the editform is submitted
	 * @var bool
	 */
	isSubmit: false,
	/**
	 * Indicates whether page is in edit mode and saving of texts should be started
	 * @var bool true if page is in edit mode
	 */
	editMode: false,
	/**
	 * Window object that stores the SaferEdit dialogue
	 * @var Ext.Window Instance of a window object
	 */
	win: false,
	/**
	 * Time of saved version in restore dialogue
	 * @var string Rendered timestamp, currently age of stored release
	 */
	savedTime: '',
	/**
	 * Date of saved version in restore dialogue
	 * @var string Rendered timestamp, currently age of stored release
	 */
	savedDate: '',
	/**
	 * Rendered HTML of saved version that is displayed in restore dialogue
	 * @var string Rendered HTML
	 */
	savedHTML: '',
	/**
	 * Wiki code of saved version that is inserted on OK in restore dialogue
	 * @var string Wiki code
	 */
	savedWikiCode: '',
	/**
	 * URL of section edit of page that is used if page is called in edit mode, but a saved part of a section is present
	 * @var string Redirect url
	 */
	redirect: '',
	/**
	 * Boolean if a backup is created
	 * @var bool backup created
	 */
	bBackupCreated: false,
	/**
	 * Boolean if oldtext should be reseted
	 * @var bool backup oldtext should be reseted
	 */
	bResetOldText: true,
	/**
	 * Used to display the SaferEdit restore dialogue. Calls getLostTexts with some delay to fix a sttrange IE bug.
	 */
	toggleDialog: function() {
		/* this is to fix a strange IE bug */
		setTimeout( BsSaferEditEditMode.getLostTexts, 10 );
	},
	/**
	 * Renders SaferEdit restore dialogue. Data (HTML and WikiCode) is expected to be loaded already
	 */
	show: function() {
		BsSaferEditEditMode.win = Ext.create( 'Ext.Window', {
			id: 'winSaferEdit',
			width:600,
			title:'SaferEdit',
			closeAction: 'close',
			items: [{
				xtype: "container",
				items: [{
					xtype: 'toolbar',
					autoHeight: true,
					items: [
						{
							xtype: 'tbtext',
							text: mw.message('bs-saferedit-lastsavedversion', BsSaferEditEditMode.savedDate, BsSaferEditEditMode.savedTime).plain()
						}
					]
				}, {
					xtype: "container",
					html: BsSaferEditEditMode.savedHTML,
					height: 400,
					style: 'background-color: #FFFFFF;padding:5px;',
					autoScroll: true
				}]
			}],
			buttons: [{
				id: 'ok-btn',
				text: mw.message('bs-saferedit-restore').plain(),
				handler: function(e) {
					BsSaferEditEditMode.restore();
					BsSaferEditEditMode.hide();
				},
				scope: this
			},{
				text: mw.message('bs-extjs-cancel').plain(),
				handler: function(){
					BsSaferEditEditMode.cancelSaferEdit();
					BsSaferEditEditMode.canceledByUser = false;
					BsSaferEditEditMode.hide();
				},
				scope: this
			}],
			layout: 'fit'
		});
		BsSaferEditEditMode.win.show();
	},
	/**
	 * Hides the SaferEdit restore dialogue
	 */
	hide: function() {
		BsSaferEditEditMode.startSaving();
		BsSaferEditEditMode.win.close();
		// MRG (25.02.11 01:28): Dependency to StateBar
		if ( document.getElementById('sb-SaferEdit') ) {
			document.getElementById('sb-SaferEdit').style.display = 'none';
		}
	},
	/**
	 * Initiates saving of edited text in certain intervals
	 */
	startSaving: function() {
		BsSaferEditEditMode.oldText = BsSaferEditEditMode.getText();
		BsSaferEditEditMode.origText = BsSaferEditEditMode.oldText;
		BSPing.registerListener(
			'SaferEditSave',
			0,
			[{
				text: BsSaferEditEditMode.oldText,
				section: bsSaferEditEditSection
			}],
			BsSaferEditEditMode.saveTextListener
		);
	},

	saveTextListener: function(result, Listener) {
		var text = BsSaferEditEditMode.getText();

		if ( BsSaferEditEditMode.canceledByUser ) return;

		if ( BsSaferEditEditMode.oldText != text ) {
			BsSaferEditEditMode.isDirty = true;
			BsSaferEditEditMode.oldText = text;
			BsSaferEditEditMode.bBackupCreated = true;
		} else {
			if( BsSaferEditEditMode.bBackupCreated === true ) {
				text = '';
			}
		}
		BSPing.registerListener(
			'SaferEditSave',
			BsSaferEditEditMode.interval,
			[{
				text: text,
				section: bsSaferEditEditSection
			}],
			BsSaferEditEditMode.saveTextListener
		);
	},

	/**
	 * Renders the redirection dialogue if a whole page is edited but a saved text for a section is available
	 */
	showRedirect: function() {
		// Show a dialog using config options:
		bs.util.confirm(
			'bs-saferedit',
			{
				titleMsg: 'bs-saferedit-othersectiontitle',
				text: mw.message('bs-saferedit-othersectiontext1').plain() + '<br/>' +
					mw.message('bs-saferedit-othersectiontext2', BsSaferEditEditMode.savedDate, BsSaferEditEditMode.savedTime ).plain() + '<br />' +
					mw.message('bs-saferedit-othersectiontext3').plain()
			},
			{
				ok: function() {
					window.location.href = BsSaferEditEditMode.redirect;
				},
				cancel: function() {
					BsSaferEditEditMode.cancelSaferEdit();
					BsSaferEditEditMode.startSaving();
				}
			}
		);
	},

	getText: function( mode ) {
		var text = '';

		switch (mode) {
			case "VisualEditor":
				text = $('wpTextbox1').val();
				break;
			case "MW":
				text = tinyMCE.activeEditor.getContent({save:true});
				break;
			default: //detect
				if( typeof VisualEditorMode !== 'undefined' && VisualEditorMode ) {
					text = tinyMCE.activeEditor.getContent({save:true});
					break;
				}
				text = $('#wpTextbox1').val();
		}

		return text;
	},
	/**
	 * Retrieves a saved intermediate text if present
	 */
	getLostTexts: function() {
		if ( typeof( bsSaferEditUseSE ) != "undefined" ) {
			if ( bsSaferEditUseSE ) {
				var url = bs.util.getAjaxDispatcherUrl(
					'SaferEdit::getLostTexts',
					[ wgUserName, wgPageName, wgNamespaceNumber, bsSaferEditEditSection ]
				);

				$.get(
					url,
					null,
					function ( sResponseData ){
						var oResponse = JSON.parse(sResponseData);

						if ( oResponse.notexts == "1" ) return;
						if ( oResponse.savedOtherSection == "1" ) {
							BsSaferEditEditMode.savedTime = oResponse.time;
							BsSaferEditEditMode.savedDate = oResponse.date;
							BsSaferEditEditMode.redirect = oResponse.redirect;
							BsSaferEditEditMode.showRedirect();
							return;
						}
						BsSaferEditEditMode.savedTime = oResponse.time;
						BsSaferEditEditMode.savedDate = oResponse.date;
						BsSaferEditEditMode.savedHTML = unescape(oResponse.html);
						BsSaferEditEditMode.savedWikiCode = unescape(oResponse.wiki);

						setTimeout( BsSaferEditEditMode.show, 10 );
					}
				);
			}
		} else {
			this.startSaving();
			//BsSaferEditEditMode.timeout = setTimeout("BsSaferEditEditMode.saveText()", BsSaferEditEditMode.interval);
		}
	},
	/**
	 * Conducts neccessary preparations of edit form and starts intermediate saving
	 */
	init: function() {
		if ( wgAction == "edit" || wgAction == "submit" ) BsSaferEditEditMode.editMode = true;
		if ( wgCanonicalNamespace == "Special" ) BsSaferEditEditMode.editMode = false;

		if ( !BsSaferEditEditMode.editMode ) return;
		BsSaferEditEditMode.origText = BsSaferEditEditMode.getText();
		var links = document.getElementsByTagName( "a" );
		for ( i = 0; i < links.length; i++ ) {
			if ( links[i].innerHTML == mw.message('bs-extjs-cancel').plain() || links[i].innerHTML == "Cancel" ) {
				links[i].onclick = BsSaferEditEditMode.cancelSaferEdit;
			}
		}

		//some browsers do not support ajax calls after submit
		//document.getElementById('editform').onsubmit=BsSaferEditEditMode.cancelSaferEdit;
		//btnSave.onclick="alert('test');";

		if( mw.config.get('bsSaferEditHasTexts', false )) return;

		BsSaferEditEditMode.startSaving();
	},
	/** DEPRECATED
	 * Retrieves edited text from textfield or editor and sends it to server
	 */
	saveText: function() {
		var text = BsSaferEditEditMode.getText();

		if( BsSaferEditEditMode.canceledByUser ) return;

		if( BsSaferEditEditMode.oldText != text ) {
			BsSaferEditEditMode.isDirty = true;
			BsSaferEditEditMode.oldText = text;
			BsSaferEditEditMode.sendText(text);
			BsSaferEditEditMode.bBackupCreated = true;
		} else {
			if( BsSaferEditEditMode.bBackupCreated === true ) {
				BsSaferEditEditMode.sendText( false );
			} else {
				BsSaferEditEditMode.timeout = setTimeout( BsSaferEditEditMode.saveText, BsSaferEditEditMode.interval);
			}
		}
	},
	/** DEPRECATED
	 * Sends a changed text to the server.
	 * @param string text the text that sould be sent to server
	 */
	sendText: function(text) {
		var bPingOnly = false;
		if ( text === false ) bPingOnly = true;
		$.post(
			bs.util.getAjaxDispatcherUrl(
				'SaferEdit::saveText',
				[ encodeURIComponent( text ), wgUserName, wgPageName, wgNamespaceNumber, bsSaferEditEditSection, bPingOnly ]
			),
			function ( sResponseData ){
				if ( sResponseData == "OK" ) {
					BsSaferEditEditMode.timeout = setTimeout( BsSaferEditEditMode.saveText, BsSaferEditEditMode.interval); // TODO RBV (19.05.11 09:41): XHRResponse Abstraktion?
				}
			}
		);
	},
	/**
	 * Resets "dirty"-state if all changes are saved.
	 */
	clearDirty: function() {
		BsSaferEditEditMode.isDirty = false;
	},
	// flag gesetzt um zu verhindern, dass doSaferEdit nach submit ausgeführt wird
	canceledByUser: false,
	/**
	 * All saved texts for the current article are deleted.
	 */
	cancelSaferEdit: function() {
		// flag gesetzt um zu verhindern, dass doSaferEdit nach submit ausgeführt wird
		BsSaferEditEditMode.canceledByUser = true;
		BsSaferEditEditMode.clearDirty();

		$.post(
			bs.util.getAjaxDispatcherUrl(
				'SaferEdit::doCancelSaferEdit',
				[ escape(wgUserName), wgPageName, wgNamespaceNumber ]
			),
			function ( sResponseData ){
				//BsSaferEditEditMode.clearIcon();
				// TODO RBV (19.05.11 09:42): Implement
			}
		);
	},
	/**
	 * Writes text that is to be restored into the text field or editor
	 */
	restore: function() {
		var text = BsSaferEditEditMode.savedWikiCode;

		//text = text.replace(/^<textarea.*?>/i, '');
		//text = text.replace(/<\/textarea>$/i, '');
		if ( typeof bsVisualEditorUse !== 'undefined' ) {
			if ( bsVisualEditorUse ) {
				tinyMCE.execCommand('mceSetContent', false, text);
			}
		} else {
			$('#wpTextbox1').val(text);
		}

		$.post(
			bs.util.getAjaxDispatcherUrl(
				'SaferEdit::doCancelSaferEdit',
				[ wgUserName, wgPageName, wgNamespaceNumber ]
			),
			function ( sResponseData ){
				//BsSaferEditEditMode.clearIcon();
				// TODO RBV (19.05.11 09:42): Implement
			}
		);
	},

	hasUnsavedChanges: function(mode) {
		var text = BsSaferEditEditMode.getText( mode );

		if ( text.trim() != BsSaferEditEditMode.origText.trim() ) {
			BsSaferEditEditMode.isUnsaved = true;
			return true;
		} else {
			BsSaferEditEditMode.isUnsaved = false;
			return false;
		}
	},
	/**
	 * Called when edit page is left and there are unsaved changes.
	 */
	checkSaved: function() {
		if ( !BsSaferEditEditMode.isSubmit && BsSaferEditEditMode.hasUnsavedChanges("-") ) {
			if(/chrome/.test(navigator.userAgent.toLowerCase())) { //chrome compatibility
				return mw.message('bs-saferedit-unsavedchanges').plain();
			}
			if(window.event) {
				window.event.returnValue = mw.message('bs-saferedit-unsavedchanges').plain();
			} else {
				return mw.message('bs-saferedit-unsavedchanges').plain();
			}
		}
		// do not return anything, not even null. otherwise IE will display the dialogue
	},

	onToggleEditor: function(name, data) {
		if ( BsSaferEditEditMode.isUnsaved ) return;

		BsSaferEditEditMode.origText = BsSaferEditEditMode.getText( data );
	},

	onBeforeToggleEditor: function(name, data) {
		BsSaferEditEditMode.hasUnsavedChanges(data);
	},

	onSavedText: function(name) {
		BsSaferEditEditMode.origText = BsSaferEditEditMode.getText( 'VisualEditor' );
		BsSaferEditEditMode.isUnsaved = false;
	}
};

mw.loader.using( 'ext.bluespice', function() {
	BsSaferEditEditMode.init();
	if ( mw.config.get('bsSaferEditHasTexts', false ) ) {
		BsSaferEditEditMode.toggleDialog();
	}
	if ( bsSaferEditWarnOnLeave && (typeof(alreadyBound) == 'undefined' || alreadyBound == false) ) {
		$(window).on( 'beforeunload', function() {
			alreadyBound = true;
			// if a string is returned, a dialog is displayed.
			// if null is returned, nothing happenes and the page is left.
			return BsSaferEditEditMode.checkSaved();
		});
		$(document).on( 'submit', '#editform', function() {
			BsSaferEditEditMode.isSubmit = true;
		} );
	}
});

$(document).on( 'BSVisualEditorBeforeToggleEditor', BsSaferEditEditMode.onBeforeToggleEditor );
$(document).on( 'BSVisualEditorToggleEditor', BsSaferEditEditMode.onToggleEditor );
$(document).on( 'BSVisualEditorSavedText', BsSaferEditEditMode.onSavedText );