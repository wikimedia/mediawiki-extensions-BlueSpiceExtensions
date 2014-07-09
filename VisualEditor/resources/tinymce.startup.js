// Use strict breaks IE8
// "use strict";

VisualEditor = {

		/**
		 * @type VisualEditor
		 * @private
		 */
		_instance: this,
		_instancesGui: {},
		_editorMode: 'wiki',
		_started: false,
		_bookmark: false,
		_settings: {},
		/**
		 * @type Object
		 * @private
		 */
		_config: {
			_default: mw.config.get('BsVisualEditorConfigDefault')
		},

		/**
		 * Singleton for system wide possiblity to change the configuration
		 * @returns {VisualEditor}
		 */
		getInstance: function() {
			if (!this._instance) {
				this._instance = _createInstance();
			}
			return this._instance;
		},
		/**
		 * Starts all configured TinyMCE instances
		 */
		startEditors: function() {
			// set the appropriate base url for the TinyMCE installation
			window.tinyMCE.baseURL = mw.config.get('wgScriptPath')
				+ '/extensions/BlueSpiceExtensions/VisualEditor/resources/tinymce';

			// start all the configured TinyMCE instances
			// IE8 fix, does not know Object.keys
			if ( Object.keys && Object.keys(this._config).length > 1 ) {
				for (var key in this._config) {
					if (key === '_default') {
						continue;
					}
					window.tinyMCE.init($.extend({}, this._config['_default'], this._config[key]));
				}
			} else {
				// Fix for IE8/9 where we don't get a extra config by default
				this._config['_default'] =  mw.config.get('BsVisualEditorConfigDefault');
				window.tinyMCE.init(this._config['_default']);
			}
			this._editorMode = 'tiny';
			this._started = true;
		},
		/**
		 * Sets the default config object.
		 * @param {Object} config
		 */
		setDefaultConfig: function(config) {
			this._config['_default'] = config;
		},
		setConfig: function(key, config) {
			this._config[key] = config;
		},
		toggleGui: function() {
			var id = tinymce.activeEditor.id,
				loadConfig;
			if (typeof(this._instancesGui[id]) === 'undefined') {
				this._instancesGui[id] = 'BsVisualEditorConfigDefault';
			}

			if (this._instancesGui[id] === 'BsVisualEditorConfigDefault') {
				loadConfig = 'BsVisualEditorConfigAlternative';
				this._instancesGui[id] = 'BsVisualEditorConfigDefault';
			} else {
				loadConfig = 'BsVisualEditorConfigDefault';
				this._instancesGui[id] = 'BsVisualEditorConfigAlternative';
			}

			tinymce.remove('#' + id);
			tinymce.init($.extend(
				mw.config.get(loadConfig),
				{selector: '#' + id}
			));
		},
		toggleEditor: function(id) {

			if (this._started === false) {
				$(document).trigger('VisualEditor::instanceShow', [id]);
				this.startEditors();
				return;
			}

			if (id === undefined) {
				id = window.tinyMCE.activeEditor.id;
				this._settings[id] = window.tinyMCE.activeEditor.settings;
			}

			if (this._editorMode === 'wiki') {
				window.tinyMCE.createEditor(id, this._settings[id]).init();
				this._editorMode = 'tiny';
				$(document).trigger('VisualEditor::instanceShow', [id]);
			} else {
				//This is basically copied from tinymce.js:27051
				//We cannot call tinymce.destroy directly because tinymce.save 
				//called from tinymce.remove relies on the selection object 
				//which would be set to null in tinymce.destroy
				window.tinyMCE.DOM.unbind(
					window.tinyMCE.activeEditor.formElement,
					'submit reset',
					window.tinyMCE.activeEditor.formEventDelegate
				);
				window.tinyMCE.activeEditor.remove();
				this._editorMode = 'wiki';
				$(document).trigger('VisualEditor::instanceHide', [id]);
			}
		},
		startEditMode: function(bookmark) {
			if (this._editorMode === 'tiny' && !tinymce.activeEditor.isHidden()) {
				this._bookmark = bookmark;
			} else {
				this._bookmark = bs.util.selection.save();
			}
		},
		endEditMode: function() {
			this._bookmark = false;
			bs.util.selection.reset();
		},
		insertContent: function(wikitext) {
			//console.log(wikitext);
			if (_editorMode === 'tiny' && !tinymce.activeEditor.isHidden()) {
				tinyMCE.activeEditor.selection.moveToBookmark(this._bookmark);
				tinyMCE.execCommand('mceInsertRawHTML', true, wikitext);
				tinyMCE.activeEditor.selection.moveToBookmark(this._bookmark);
				tinyMCE.activeEditor.selection.collapse(true);
				//console.log('tiny');
			} else {
				bs.util.selection.restore(wikitext);
				//console.log('wiki');
			}
		},


	_createInstance: function() {
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
					this._config[key] = config;
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
				return this._config[key] || {};
			}
		};
	}
};