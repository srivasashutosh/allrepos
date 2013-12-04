Scalr.regPage('Scalr.ui.dm.tasks.logs', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'dtadded', 'message'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/dm/tasks/xListLogs/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Deployments &raquo; Tasks &raquo; Log',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { deploymentTaskId: ''},
		store: store,
		stateId: 'grid-dm-tasks-logs-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		viewConfig: {
			emptyText: 'Log is empty for selected deployment task',
			loadingText: 'Loading logs ...'
		},

		columns: [
			{ header: "Date", width: 160, dataIndex: 'dtadded', sortable: true },
			{ header: "Message", flex: 1, dataIndex: 'message', sortable: true }
		],

		tools: [{
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent('close');
			}
		}],

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top'
		}]
	});
});
