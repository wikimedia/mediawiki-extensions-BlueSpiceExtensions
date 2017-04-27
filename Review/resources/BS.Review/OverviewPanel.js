Ext.define( 'BS.Review.OverviewPanel', {
	extend: 'Ext.grid.Panel',
	requires: [ 'Ext.PagingToolbar' ],
	features: [],
	viewConfig: {
		forceFit: true
	},

	initComponent: function() {

		this.store = Ext.create( 'Ext.data.JsonStore', {
			proxy: {
				type: 'ajax',
				url: bs.util.getAjaxDispatcherUrl( 'SpecialReview::ajaxGetOverview' ),
				reader: {
					type: 'json',
					root: 'payload',
					idProperty: 'rev_id'
				},
				extraParams: {
					userID: mw.config.get('bsSpecialReviewUserID', 0)
				}
			},
			fields: [ 'rev_id', 'page_title', 'owner_name', 'owner_real_name',
				'rev_status', 'rev_status_text', 'rev_sequential', 'rejected',
				'accepted', 'accepted_text', 'total', 'endtimetamp',
				'assessors', 'startdate', 'enddate' ],
			autoLoad: true
		});

		this.columns = {
			defaults: {
				flex: 1
			},
			items:[
				{
					header: mw.message('bs-review-header-page-title').plain(),
					dataIndex: 'page_title',
					renderer: this.renderPageTitle,
					sortable: false
				},
				{
					header: mw.message('bs-review-header-owner-name').plain(),
					dataIndex: 'owner_name',
					renderer: this.renderOwner,
					sortable: false
				},
				{
					header: mw.message('bs-review-header-assessors').plain(),
					dataIndex: 'assessors',
					renderer: this.renderAssessors,
					sortable: false
				},
				{
					header: mw.message('bs-review-header-accepted-text').plain(),
					dataIndex: 'accepted_text',
					sortable: false
				},
				{
					header: mw.message('bs-review-header-startdate').plain(),
					sortable: false,
					dataIndex: 'startdate'
				},
				{
					header: mw.message('bs-review-header-enddate').plain(),
					sortable: false,
					dataIndex: 'enddate'
				}
			]
		};

		this.filters = Ext.create('Ext.ux.grid.FiltersFeature', {
			encode: true,
			local: false,
			filters: [{
				type: 'string',
				dataIndex: 'page_title'
			},{
				type: 'string',
				dataIndex: 'owner_name'
			},{
				type: 'string',
				dataIndex: 'assessors'
			}]
		});

		this.pagingToolbar = Ext.create('Ext.PagingToolbar', {
			store: this.store,
			displayInfo: true
		});

		this.features = [this.filters];

		this.bbar = this.pagingToolbar;

		this.callParent(arguments);
	},

	renderOwner: function( value, metaData, record, rowIndex, colIndex, store ) {
		var ownerName = mw.config.get('bsSpecialReviewUserName', false);

		var title = new mw.Title( value, bs.ns.NS_USER );
		var style = '';
		if(ownerName && ownerName === value) {
			style = 'font-weight:bold';
		}

		var content = record.get('owner_real_name') || value;

		return mw.html.element(
			'a',
			{
				'href': title.getUrl(),
				'style': style,
				'data-bs-username': value,
				'data-bs-title': title.getPrefixedText()
			},
			content
		);
	},

	renderPageTitle: function( value, metaData, record, rowIndex, colIndex, store ) {
		return mw.html.element(
			"a",
			{
				'data-bs-title': value,
				'href': mw.util.wikiGetlink( value )
			},
			value
		);
	},

	renderAssessors: function( value, metaData, record, rowIndex, colIndex, store ) {
		var ownerName = mw.config.get('bsSpecialReviewUserName', false);
		var table = '<table cellpadding="5">';
		var row = '<tr><td>{0}</td><td>{1} ({2})</td></tr>';
		var findActive = true;
		var isSequential = record.get( 'rev_sequential' );

		for( var i = 0; i < value.length; i++ ) {
			var line = value[i];
			var status = '<div class="{0}"></div>';
			if( line.revs_status == 0 ) {
				status = status.format( 'rv_no' );
			}
			if( line.revs_status == 1 ) {
				status = status.format( 'rv_yes' );
			}
			if( line.revs_status == -1 ) {
				//We only want to highlight the line(s) where the current user
				//is required to take action
				if( isSequential && i > 0 ) {
					var lastLine = value[ i - 1 ];
					if( lastLine.revs_status != 1 ) {
						findActive = false;
					}
				}
				if( findActive && ownerName && ownerName === line.name) {
					status = status.format( 'rv_active' );
				}
				else {
					status = status.format( 'rv_unknown' );
				}
			}
			if( line.revs_status == -2 ) { //Was '1' before reject, see BsReviewProcess::reset()
				status = '<div class="rv_yes">'+status.format( 'rv_invalid' )+'</div>';
			}
			if( line.revs_status == -3 ) { //Was '0' before reject
				status = '<div class="rv_no">'+status.format( 'rv_invalid' )+'</div>';
			}

			var openTag = '';
			var closeTag = '';
			var content = line.real_name || line.name;
			var title = new mw.Title( line.name, bs.ns.NS_USER );
			var style = '';
			if(ownerName && ownerName === line.name) {
				style = 'font-weight:bold';
			}

			table += row.format(
				status,
				mw.html.element(
					'a',
					{
						'href': title.getUrl(),
						'style': style,
						'data-bs-username': line.name,
						'data-bs-title': title.getPrefixedText()
					},
					content
				),
				line.timestamp || ''
			);
		}
		table += '</table>';
		return table;
	}
});