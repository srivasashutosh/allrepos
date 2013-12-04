Scalr.regPage('Scalr.ui.bundletasks.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			{name: 'id', type: 'int'},{name: 'clientid', type: 'int'},
			'server_id','prototype_role_id','replace_type','status','platform','rolename','failure_reason','bundle_type','dtadded',
			'dtstarted','dtfinished','snapshot_id','platform_status','server_exists'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/bundletasks/xListTasks/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Bundle tasks &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { bundleTaskId: ''},
		store: store,
		stateId: 'grid-bundletasks-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Bundle tasks',
				href: '#/bundletasks/view'
			}
		}],

		viewConfig: {
			emptyText: 'No bundle tasks found',
			loadingText: 'Loading bundle tasks ...'
		},

		columns: [
			{ header: "ID", width: 70, dataIndex: 'id', sortable: true },
			{ header: "Server ID", flex: 1, dataIndex: 'server_id', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="server_exists"><a href="#/servers/{server_id}/extendedInfo">{server_id}</a></tpl>' +
				'<tpl if="!server_exists">{server_id}</tpl>'
			},
			{ header: "Role name", flex: 1, dataIndex: 'rolename', sortable: true },
			{ header: "Status", width: 140, dataIndex: 'status', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="status == &quot;failed&quot;">{status} (<a href="#/bundletasks/{id}/failureDetails">Why?</a>)</tpl>' +
				'<tpl if="status != &quot;failed&quot;">{status}</tpl>'
			},
			{ header: "Type", width: 135, dataIndex: 'platform', sortable: false, xtype: 'templatecolumn', tpl: '{platform}/{bundle_type}' },
			{ header: "Added", width: 165, dataIndex: 'dtadded', sortable: true, hidden: true },
			{ header: "Started", width: 165, dataIndex: 'dtstarted', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="dtstarted">{dtstarted}</tpl>'
			},
			{ header: "Finished", width: 165, dataIndex: 'dtfinished', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="dtfinished">{dtfinished}</tpl>'
			}, {
				xtype: 'optionscolumn',
				optionsMenu: [{
					text:'View log',
					iconCls: 'x-menu-icon-logs',
					href: '#/bundletasks/{id}/logs'
				}, {
					itemId: 'option.cancel',
					iconCls: 'x-menu-icon-cancel',
					text: 'Cancel',
					request: {
						confirmBox: {
							msg: 'Cancel selected bundle task?',
							type: 'action'
						},
						processBox: {
							type: 'action',
							msg: 'Canceling...'
						},
						url: '/bundletasks/xCancel/',
						dataHandler: function (record) {
							return { bundleTaskId: record.get('id') };
						},
						success: function(data) {
							store.load();
						}
					}
				}],
				getOptionVisibility: function (item, record) {
					if (item.itemId == 'option.cancel') {
						if (record.get('status') != 'success' && record.get('status') != 'failed')
							return true;
						else
							return false;
					}

					return true;
				}
			}
		],

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top'
		}]
	});
});
