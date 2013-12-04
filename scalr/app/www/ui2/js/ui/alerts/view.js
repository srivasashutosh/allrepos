Scalr.regPage('Scalr.ui.alerts.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'id', 'server_id', 'farm_id', 'farm_name', 'farm_roleid', 
			'role_name', 'server_index', 'status', 'metric', 'details', 'dtoccured', 
			'dtsolved', 'dtlastcheck', 'server_exists'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/alerts/xListAlerts/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Alerts',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { metricId: '' },
		store: store,
		stateId: 'grid-scripts-alerts-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Custom events',
				href: '#/scripts/events/view'
			}
		}],

		viewConfig: {
			emptyText: "Alerts history is empty",
			loadingText: 'Loading alerts ...'
		},

		columns: [
			{ header: "Check", flex: 1, dataIndex: 'metric', sortable: true },
			{ header: 'Target', flex: 1, dataIndex: 'server_id', sortable: false, xtype: 'templatecolumn', tpl:
				'<a href="#/farms/{farm_id}/view" title="Farm {farm_name}">{farm_name}</a>' +
				'<tpl if="role_name">' +
					'&nbsp;&rarr;&nbsp;<a href="#/farms/{farm_id}/roles/{farm_roleid}/view" title="Role {role_name}">{role_name}</a> ' +
				'</tpl>' +
				'<tpl if="!role_name">' +
					'&nbsp;&rarr;&nbsp;*removed role*&nbsp;' +
				'</tpl><tpl if="!server_exists">' +
					'#{server_index} (Not running)' +
				'<tpl else>'+
					'#<a href="#/servers/{server_id}/view">{server_index}</a>'+
				'</tpl>'
			},
			{ header: "Status", width: 100, dataIndex: 'status', sortable: true, xtype: 'templatecolumn', tpl: 
			'<tpl if="status == \'failed\'"><span style="color:red;">Failed</span><tpl else><span style="color:green;">OK</span></tpl>'
			},
			{ header: "Occured", width: 160, dataIndex: 'dtoccured', sortable: true },
			{ header: "Last check", width: 160, dataIndex: 'dtlastcheck', sortable: true, xtype: 'templatecolumn', tpl: 
				'<tpl if="dtlastcheck">{dtlastcheck}<tpl else><img src="/ui2/images/icons/false.png" /></tpl>'
			},
			{ header: "Solved", width: 160, dataIndex: 'dtsolved', sortable: true, xtype: 'templatecolumn', tpl: 
			'<tpl if="dtsolved">{dtsolved}<tpl else><img src="/ui2/images/icons/false.png" /></tpl>'
			},
			{ header: "Details", flex: 1, dataIndex: 'details', sortable: true }
		],

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top'
		}]
	});
});
