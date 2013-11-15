Ext.define( 'BS.InterWikiLinks.InsertLink.FormPanelInterWiki', {
	extend: 'BS.InsertLink.FormPanelBase',
	linktype: 'internal_link',
	beforeInitComponent: function() {
		this.setTitle( mw.message('bs-interwikilinks-insertlink-tabtitle').plain() );

		this.cbInterWiki = Ext.create( 'Ext.form.field.ComboBox', {
			name: 'insertInterWiki',
			fieldLabel: mw.message('bs-interwikilinks-insertlink-labelprefix').plain(),
			store: this.storeIW,
			displayField:'name',
			typeAhead: true,
			mode: 'local',
			triggerAction: 'all',
			emptyText: mw.message('bs-insertlink-select_a_page').plain(),
			width: 600
		});

		this.tfPageTitle = Ext.create( 'Ext.form.field.Text', {
			name: 'inputTargetUrl',
			fieldLabel: mw.message('bs-insertlink-label_page').plain(),
			width: 600
		});

		this.pnlMainConf.items = [];
		this.pnlMainConf.items.push(this.cbInterWiki);
		this.pnlMainConf.items.push(this.tfPageTitle);

		this.callParent(arguments);
	},
	resetData: function() {
		this.cbInterWiki.reset();
		this.tfPageTitle.reset();

		this.callParent();
	},
	setData: function( obj ) {
		var bAcitve = false;
		var desc = false;

		//overwrites FormPanelWikiPage tab
		if( obj.type && obj.type == this.linktype ) {
			var link = String(obj.href);
			link = link.replace(wgServer+"/", "");
			link = unescape(link);

			if ( link.match( ':' ) ) {
				var parts = link.split( ':' );
				if( parts.length == 3 ) parts.shift();

				var interwiki = $.inArray(parts.shift(), mw.config.get('BSInterWikiPrefixes', []));
				if( interwiki > -1) {
					this.cbInterWiki.setValue( mw.config.get('BSInterWikiPrefixes', [])[interwiki] )
					this.tfPageTitle.setValue(parts.join( ':' ))

					if( obj.content.match( '|' ) ) {
						var content = obj.content.split( '|' );
						if(content.length > 1 ) {
							desc = content[1];
							desc = desc.replace( ']]', '' );
						} else if(content[0] != obj.href) {
							desc = content[0];
						}
					}
					bAcitve = true;
				}
			}
		} else if( obj.code !== false ) {
			if( obj.code.match(/\[\[[^\]]*\]\]/) ) {
				var link = new bs.wikiText.Link(obj.code);

				if( $.inArray(link.getNsText(), mw.config.get('BSInterWikiPrefixes', [])) > -1) {
					this.tfPageTitle.setValue( link.getTitle() );
					this.cbInterWiki.setValue( link.getNsText() );
					if( link.getTitle() != link.getDisplayText() ) {
						desc = link.getDisplayText();
					}
					bAcitve = true;
				}
			} else {
				desc = obj.code;
			}
		} else if( obj.content && obj.content != '' ) desc = obj.content;

		this.callParent( [{desc: desc}] );
		return bAcitve;
	},
	getData: function() {
		var title = this.callParent();

		var desc = '';
		if( title != '' ) {
			desc = '|'+title;
		}

		var prefix = '';
		if( this.cbInterWiki.getValue() ) {
			prefix = this.cbInterWiki.getValue() + ':';
		}

		var page = '';
		if( this.tfPageTitle.getValue() ) {
			page = this.tfPageTitle.getValue();
		}

		return { 
			title: title,
			href: prefix+page,
			type: this.linktype,
			code: '[[' + prefix + page + desc + ']]'
			//'class': ''
		};
	},
	setDescription: function( desc ) {
		this.callParent(arguments);
	},
	getDescription: function() {
		return this.callParent();
	}
});