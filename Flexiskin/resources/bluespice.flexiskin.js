mw.loader.using('ext.bluespice.flexiskin', function() {
    Ext.Loader.setPath('BS.Flexiskin', bs.em.paths.get('Flexiskin') + '/resources/BS.Flexiskin');

    Ext.create('BS.Flexiskin.Panel', {
	renderTo: 'bs-flexiskin-container'
    });
});