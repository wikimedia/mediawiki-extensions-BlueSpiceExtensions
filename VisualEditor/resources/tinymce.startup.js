VisualEditor = (function VisualEditor() {
	"use strict";

	var
		/**
		 * @type VisualEditor
		 * @private
		 */
		_instance = this,
		_instancesGui = {},
		_editorMode = 'wiki',
		_started = false,
		_bookmark = false,
		_settings = {},
		/**
		 * @type Object
		 * @private
		 */
		_config = {
		_default: mw.config.get('BsVisualEditorConfigDefault')
	};

	return {
		/**
		 * Singleton for system wide possiblity to change the configuration
		 * @returns {VisualEditor}
		 */
		getInstance: function() {
			if (!_instance) {
				_instance = _createInstance();
			}
			return _instance;
		},
		/**
		 * Starts all configured TinyMCE instances
		 */
		startEditors: function() {
			// set the appropriate base url for the TinyMCE installation
			window.tinyMCE.baseURL = mw.config.get('wgScriptPath')
				+ '/extensions/BlueSpiceExtensions/VisualEditor/resources/tinymce';

			// start all the configured TinyMCE instances
			for (var key in _config) {
				if (key === '_default') {
					continue;
				}
				window.tinyMCE.init($.extend({}, _config['_default'], _config[key]));
			}

			_editorMode = 'tiny';
			_started = true;
		},
		/**
		 * Sets the default config object.
		 * @param {Object} config
		 */
		setDefaultConfig: function(config) {
			_config['_default'] = config;
		},
		toggleGui: function() {
			var id = tinymce.activeEditor.id,
				loadConfig;
			if (typeof(_instancesGui[id]) === 'undefined') {
				_instancesGui[id] = 'BsVisualEditorConfigDefault';
			}

			if (_instancesGui[id] === 'BsVisualEditorConfigDefault') {
				loadConfig = 'BsVisualEditorConfigAlternative';
				_instancesGui[id] = 'BsVisualEditorConfigDefault';
			} else {
				loadConfig = 'BsVisualEditorConfigDefault';
				_instancesGui[id] = 'BsVisualEditorConfigAlternative';
			}

			tinymce.remove('#' + id);
			tinymce.init($.extend(
				mw.config.get(loadConfig),
				{selector: '#' + id}
			));
		},
		toggleEditor: function(id) {

			if (_started === false) {
				$(document).trigger('VisualEditor::instanceShow', [id]);
				this.startEditors();
				return;
			}

			if (id === undefined) {
				id = tinymce.activeEditor.id;
				_settings[id] = tinymce.activeEditor.settings;
			}

			if (_editorMode === 'wiki') {
				tinymce.createEditor(id, _settings[id]).init();
				_editorMode = 'tiny';
				$(document).trigger('VisualEditor::instanceShow', [id]);
			} else {
				tinymce.activeEditor.remove();
				_editorMode = 'wiki';
				$(document).trigger('VisualEditor::instanceHide', [id]);
			}
		},
		startEditMode: function(bookmark) {
			if (_editorMode === 'tiny' && !tinymce.activeEditor.isHidden()) {
				_bookmark = bookmark;
			} else {
				_bookmark = bs.util.selection.save();
			}
		},
		endEditMode: function() {
			_bookmark = false;
			bs.util.selection.reset();
		},
		insertContent: function(wikitext) {
			console.log(wikitext);
			if (_editorMode === 'tiny' && !tinymce.activeEditor.isHidden()) {
				tinyMCE.activeEditor.selection.moveToBookmark(_bookmark);
				tinyMCE.execCommand('mceInsertRawHTML', true, wikitext);
				tinyMCE.activeEditor.selection.moveToBookmark(_bookmark);
				tinyMCE.activeEditor.selection.collapse(true);
				console.log('tiny');
			} else {
				bs.util.selection.restore(wikitext);
				console.log('wiki');
			}
		}
	}

	function _createInstance() {
		return {
			/**
			 * Add or override a config object with the given key.
			 * 
			 * @param {String} key
			 * @param {Object} config
			 */
			setConfig: function(key, config) {
				if (key == "_default") {
					throw "Don`t overwrite the default config!";
				} else {
					_config[key] = config;
				}
			},
			/**
			 * Returns the config for the given key or an empty object, if the
			 * key doesn`t exist.
			 * 
			 * @param {String} key
			 * @return {Object}
			 */
			getConfig: function(key) {
				return _config[key] || {};
			}
		}
	}
}());