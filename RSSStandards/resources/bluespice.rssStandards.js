var combo;

Ext.onReady( function() {
	Ext.QuickTips.init();

	var link = '';

	// TODO SW: make generic
	var buttons = {
		rc: Ext.get('btnFeedRc'),
		own: Ext.get('btnFeedOwn'),
		page: Ext.get('btnFeedPage'),
		ns: Ext.get('btnFeedNs'),
		cat: Ext.get('btnFeedCat'),
		watch: Ext.get('btnFeedWatch')
	};

	buttons.rc.addListener('click', function() {
		location.href = this.dom.value;
	});

	buttons.own.addListener('click', function() {
		location.href = this.dom.value;
	});
	buttons.page.addListener('click', function() {
		if(link) {
			location.href = link;
		}
	});
	buttons.ns.addListener('click', function() {
		location.href = Ext.get('selFeedNs').dom.value;
	});
	buttons.cat.addListener('click', function() {
		location.href = Ext.get('selFeedCat').dom.value;
	});
	buttons.watch.addListener('click', function() {
		location.href = Ext.get('selFeedWatch').dom.value;
	});

	var pagestore = Ext.create( 'BS.store.BSApi', {
		apiAction: 'bs-rss-standards-pages-store',
		fields: ['page', 'url']
	});

	Ext.create( 'Ext.form.ComboBox', {
		renderTo: 'divFeedPage',
		displayField: 'page',
		minChars: 1,
		store: pagestore,
		mode: 'local',
		typeAhead: true,
		triggerAction: 'all',
		allowBlank: false,
		width: 400,
		style: {
			padding: '1px'
		},
		listeners: {
			'select': {
				fn: function(box, record, idx) {
					link = record[0].get('url');
				},
				scope: this
			}
		}
	});

	pagestore.load();
});