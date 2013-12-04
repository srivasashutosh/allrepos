Scalr.regPage('Scalr.ui.tools.rackspace.limits', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'unit', 'remaining', 'verb', 'regex', 'value', 'resetTime', 'URI'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/tools/rackspace/xListLimits/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Tools &raquo; Rackspace &raquo; Limits',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { volumeId: '' },
		store: store,
		stateId: 'grid-tools-rackspace-limits',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Tackspace Limits',
				href: '#/tools/rackspace/limits'
			}
		}],

		viewConfig: {
			emptyText: 'No limits found',
			loadingText: 'Loading limits ...'
		},

		columns: [
			{ header: "verb", flex: 1, dataIndex: 'verb', sortable: false },
			{ header: "Regex", flex: 2, dataIndex: 'regex', sortable: false },
			{ header: "Value", flex: 1, dataIndex: 'value', sortable: false },
			{ header: "Remaining", flex: 1, dataIndex: 'remaining', sortable: false },
			{ header: "unit", width: 150, dataIndex: 'unit', sortable: false },
			{ header: "URI", flex: 2, dataIndex: 'URI', sortable: false },
			{ header: "Reset Time", width: 300, dataIndex: 'resetTime', sortable: false }
		],

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			items: [{
				xtype: 'fieldcloudlocation',
				itemId: 'cloudLocation',
				store: {
					fields: [ 'id', 'name' ],
					data: moduleParams.locations,
					proxy: 'object'
				},
				gridStore: store,
				cloudLocation: loadParams['cloudLocation'] || ''
			}]
		}]
	});
});
