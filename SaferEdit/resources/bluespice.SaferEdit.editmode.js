/**
 * SaferEdit extension
 *
 * Part of BlueSpice MediaWiki
 *
 * @author     Markus Glaser <glaser@hallowelt.com>
 * @package    Bluespice_Extensions
 * @subpackage SaferEdit
 * @copyright  Copyright (C) 2016 Hallo Welt! GmbH, All rights reserved.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU Public License v3
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
	interval: mw.config.get( 'bsSaferEditInterval' ) * 1000,
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
	 * Initiates saving of edited text in certain intervals
	 */
	startSaving: function () {
		BSPing.registerListener(
			'SaferEditSave',
			0,
			[{
						section: mw.config.get( 'bsSaferEditEditSection' ),
						bUnsavedChanges: BsSaferEditEditMode.hasUnsavedChanges( )
			}],
				BsSaferEditEditMode.startSaving
		);
	},
	/**
	 * Conducts neccessary preparations of edit form and starts intermediate saving
	 */
	init: function() {
		if ( mw.config.get( "wgAction" ) == "edit" || mw.config.get( "wgAction" ) == "submit" ) {
			BsSaferEditEditMode.editMode = true;
		}
		if ( mw.config.get( "wgCanonicalNamespace" ) == "Special" ) {
			BsSaferEditEditMode.editMode = false;
		}

		if ( !BsSaferEditEditMode.editMode ) {
			return;
		}
		BsSaferEditEditMode.origText = BsSaferEditEditMode.getText();
		BsSaferEditEditMode.startSaving();
	},
	checkSaved: function () {
		if ( !BsSaferEditEditMode.isSubmit && BsSaferEditEditMode.hasUnsavedChanges( ) ) {
			if ( /chrome/.test( navigator.userAgent.toLowerCase() ) ) { //chrome compatibility
				return mw.message( 'bs-saferedit-unsavedchanges' ).plain();
			}
			if ( window.event ) {
				window.event.returnValue = mw.message( 'bs-saferedit-unsavedchanges' ).plain();
			} else {
				return mw.message( 'bs-saferedit-unsavedchanges' ).plain();
			}
		}
		return null;
	},
	hasUnsavedChanges: function ( mode ) {
		if ( typeof ( VisualEditor ) !== "undefined" && VisualEditor._editorMode === "tiny" ) {
			if (!tinyMCE.activeEditor ) {
				return null;
			}
			BsSaferEditEditMode.isUnsaved = tinyMCE.activeEditor.isDirty();
			return BsSaferEditEditMode.isUnsaved;
		}
		var text = BsSaferEditEditMode.getText( );
		if ( text.trim() != BsSaferEditEditMode.origText.trim() ) {
			BsSaferEditEditMode.isUnsaved = true;
			return true;
		} else {
			BsSaferEditEditMode.isUnsaved = false;
			return false;
		}
	},
	onSavedText: function ( name ) {
		BsSaferEditEditMode.origText = BsSaferEditEditMode.getText( 'VisualEditor' );
		BsSaferEditEditMode.isUnsaved = false;
	},

	onToggleEditor: function ( name, data ) {
		if ( BsSaferEditEditMode.isUnsaved )
			return;

		BsSaferEditEditMode.origText = BsSaferEditEditMode.getText( data );
	},
	onVisualEditorInstanceShow: function () {
		BsSaferEditEditMode.origText = BsSaferEditEditMode.getText( "MW" );
	},
	onBeforeToggleEditor: function ( name, data ) {
		BsSaferEditEditMode.hasUnsavedChanges( data );
	},
	getText: function ( mode ) {
		var text = '';

		switch ( mode ) {
			case "MW":
				text = $( '#wpTextbox1' ).val();
				break;
			case "VisualEditor":
				text = tinyMCE.activeEditor.getContent( { save: true } );
				break;
			default: //detect
				if ( typeof VisualEditorMode !== 'undefined' && VisualEditorMode ) {
					text = tinyMCE.activeEditor.getContent( { save: true } );
					break;
				}
				text = $( '#wpTextbox1' ).val();
		}

		return text || '';
	}
};

mw.loader.using( 'ext.bluespice', function() {
	BsSaferEditEditMode.init();
	if ( mw.config.get( 'bsSaferEditWarnOnLeave' ) ) {
		window.onbeforeunload = function ( e ) {
			var e = e || window.event;
			var bReturn = BsSaferEditEditMode.checkSaved();
			if ( bReturn === null ) {
				return;
			}
			if ( e ) {
				e.returnValue = bReturn;
			}
			return bReturn;
		};
		$( document ).on( 'submit', '#editform', function () {
			BsSaferEditEditMode.isSubmit = true;
		} );
	}
} );

$( document ).on( 'BSVisualEditorBeforeToggleEditor', BsSaferEditEditMode.onBeforeToggleEditor );
$( document ).on( 'BSVisualEditorSavedText', BsSaferEditEditMode.onSavedText );
$( document ).on( 'BSVisualEditorToggleEditor', BsSaferEditEditMode.onToggleEditor );