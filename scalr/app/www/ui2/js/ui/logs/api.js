Scalr.regPage('Scalr.ui.logs.api', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'id','transaction_id','dtadded','action','ipaddress','request' ],
		proxy: {
			type: 'scalr.paging',
			url: '/logs/xListApiLogs/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Logs &raquo; API',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		store: store,
		stateId: 'grid-logs-api-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'API Log',
				href: '#/logs/api'
			}
		}],

		viewConfig: {
			emptyText: 'No logs found',
			loadingText: 'Loading logs ...'
		},

		columns: [
			{ header: 'Transaction ID', flex: 1, dataIndex: 'transaction_id', sortable: false },
			{ header: 'Time', flex: 1, dataIndex: 'dtadded', sortable: true },
			{ header: 'Action', flex: 1, dataIndex: 'action', sortable: true },
			{ header: 'IP address', flex: 1, dataIndex: 'ipaddress', sortable: true },
			{
				xtype: 'optionscolumn',
				optionsMenu: [
					{ text:'Details', href: "#/logs/apiLogEntryDetails?transactionId={transaction_id}" }
				]
			}
		],

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			items: [{
				xtype: 'filterfield',
				store: store
			}]
		}]
	});
});
