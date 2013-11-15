(function() {
	// This does not work with old editors. Maybe the version could be asked before
	tinymce.PluginManager.requireLangPack('hwlingulab');
	tinymce.create('tinymce.plugins.hwlingulab', {
	init : function(ed, url) {
		var t = this;
		var Event = tinymce.dom.Event;

		t.editor = ed;
		// Register buttons
				ed.addButton('hwlingulab', {
								title : 'hwlingulab.title',
								cmd : 'mceLingulab',
								image : wgScriptPath+'/extensions/BlueSpiceExtensions/VisualEditor/resources/tiny_mce_plugins/hwlingulab/images/icon.png'
						});
				ed.addCommand('mceLingulab', function(){
								var text = tinymce.activeEditor.selection.getContent({format : 'text'});
								//alert(tinymce.activeEditor.selection.getContent());
								$.ajax({
										url: wgScriptPath+"/extensions/BlueSpiceExtensions/VisualEditor/resources/tiny_mce_plugins/hwlingulab/src/sample_posteddata.php",
										type: "POST",
										data: "text=" + escape(text),
										async : false,
										success: function (response) {
												if (response!='') {
													var resp = $.parseJSON(response);
													var conf = confirm(resp.text);
													if (conf == true){
															window.open(resp.link);
														}
												} else{
														alert('Fehler beim Abschicken des Formulares.');
												}
										}
								});
				});
		}
	})
	tinymce.PluginManager.add('hwlingulab', tinymce.plugins.hwlingulab);
})();
