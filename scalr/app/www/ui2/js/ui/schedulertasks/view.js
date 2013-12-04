Scalr.regPage('Scalr.ui.schedulertasks.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'id', 'name', 'type', 'comments', 'targetName', 'targetType', 'startTime', 'config',
			'endTime', 'lastStartTime', 'timezone', 'restartEvery','orderIndex', 'status', 'targetFarmId', 'targetFarmName', 'targetRoleId', 'targetRoleName', 'targetId'
		],
		proxy: {
			type: 'scalr.paging',
			url: '/schedulertasks/xListTasks/'
		},
		remoteSort: true
	});
	return Ext.create('Ext.grid.Panel', {
		title: 'Scheduler tasks &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: {},
		store: store,
		stateId: 'grid-schedulertasks-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Tasks scheduler',
				href: '#/schedulertasks/view'
			}
		}],

		viewConfig: {
			emptyText: 'No tasks found',
			loadingText: 'Loading tasks ...'
		},

		columns: [
			{ text: 'ID', width: 50, dataIndex: 'id', sortable: true },
			{ text: 'Name', flex: 1, dataIndex: 'name', sortable: true },
            { text: 'Description', flex: 2, dataIndex: 'comments', sortable: false, hidden: true },
			{ text: 'Type', flex: 2, dataIndex: 'type', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="type == &quot;Execute script&quot;">Execute script: <a href="#/scripts/{config.scriptId}/view?version={config.scriptVersion}">{config.scriptName}</a> (<tpl if="config.scriptVersion == -1">latest<tpl else>{config.scriptVersion}</tpl>)' +
				'<tpl else>{type}</tpl>'
			},
			{ text: 'Target name', flex: 3, dataIndex: 'target', sortable: false, xtype: 'templatecolumn', tpl:
				'<tpl if="targetType == &quot;farm&quot;">Farm: <a href="#/farms/{targetId}/view" title="Farm {targetName}">{targetName}</a></tpl>' +
				'<tpl if="targetType == &quot;role&quot;">Farm: <a href="#/farms/{targetFarmId}/view" title="Farm {targetFarmName}">{targetFarmName}</a>' +
					'&nbsp;&rarr;&nbsp;Role: <a href="#/farms/{targetFarmId}/roles/{targetId}/view" title="Role {targetName}">{targetName}</a>' +
				'</tpl>' +
				'<tpl if="targetType == &quot;instance&quot;">Farm: <a href="#/farms/{targetFarmId}/view" title="Farm {targetFarmName}">{targetFarmName}</a>' +
					'&nbsp;&rarr;&nbsp;Role: <a href="#/farms/{targetFarmId}/roles/{targetRoleId}/view" title="Role {targetRoleName}">{targetRoleName}</a>' +
					'&nbsp;&rarr;&nbsp;Server: <a href="#/servers/view?farmId={targetFarmId}" title="Server {targetName}">{targetName}</a>' +
				'</tpl>'
			},
			{ text: 'Start date', width: 150, dataIndex: 'startTime', sortable: true },
			{ text: 'Restart every', width: 120, dataIndex: 'restartEvery', sortable: false, xtype: 'templatecolumn', tpl: new Ext.XTemplate(
				'<tpl if="restartEvery == 0">Never</tpl>' +
				'<tpl if="restartEvery != 0">{[this.convertTime(values.restartEvery)]}</tpl>', {
					convertTime: function (time) {
						if (time > 60) {
							var d1 = Math.ceil(time/60), d2 = Math.floor(time/60);
							if (d1 == d2) {
								time = time/60;
								if (time > 24) {
									d1 = Math.ceil(time/24), d2 = Math.floor(time/24);
									if (d1 == d2) {
										time = time/24;
										return time + " days";
									}
								} else {
									return time + " hours";
								}
								time = time * 60;
							}
						}
						return time + " minutes";
					}
				})
			},
			{ text: 'End date', width: 150, dataIndex: 'endTime', sortable: true },
			{ text: 'Last time executed', width: 150, dataIndex: 'lastStartTime', sortable: true },
			{ text: 'Timezone', width: 120, dataIndex: 'timezone', sortable: true },
			{ text: 'Priority', width: 60, dataIndex: 'order_index', sortable: true, hidden: true },
			{ text: 'Status', width: 100, dataIndex: 'status', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="status == &quot;Active&quot;"><span style="color: green;">{status}</span></tpl>' +
				'<tpl if="status == &quot;Suspended&quot;"><span style="color: blue;">{status}</span></tpl>' +
				'<tpl if="status == &quot;Finished&quot;"><span style="color: gray;">{status}</span></tpl>'
			}, {
				xtype: 'optionscolumn',
				getVisibility: function (record) {
					var reg =/Finished/i;
					return !reg.test(record.get('status'));
				},
				getOptionVisibility: function (item, record) {
					if (item.itemId == "option.activate" || item.itemId == "option.suspend" || item.itemId == "option.editSep") {
						var reg =/Finished/i
						if(reg.test(record.data.status))
							return false;
					}
					var reg =/Active/i
					if (item.itemId == "option.activate" && reg.test(record.get('status')))
						return false;

					var reg =/Suspended/i
					if (item.itemId == "option.suspend"  && reg.test(record.get('status')))
						return false;

					return true;
				},
				optionsMenu: [{
					itemId: 'option.activate',
					text: 'Activate',
					iconCls: 'x-menu-icon-activate',
					request: {
						processBox: {
							type: 'action'
						},
						url: '/schedulertasks/xActivate',
						dataHandler: function (record) {
							return { tasks: Ext.encode([record.get('id')]) };
						},
						success: function(data) {
							store.load();
						}
					}
				}, {
					itemId: 'option.suspend',
					text: 'Suspend',
					iconCls: 'x-menu-icon-suspend',
					request: {
						processBox: {
							type: 'action'
						},
						url: '/schedulertasks/xSuspend',
						dataHandler: function (record) {
							return { tasks: Ext.encode([record.get('id')]) };
						},
						success: function(data) {
							store.load();
						}
					}
				}, {
					itemId: 'option.execute',
					text: 'Execute',
					iconCls: 'x-menu-icon-launch',
					request: {
						processBox: {
							type: 'action'
						},
						url: '/schedulertasks/xExecute',
						dataHandler: function (record) {
							return { tasks: Ext.encode([record.get('id')]) };
						},
						success: function(data) {
							store.load();
						}
					}
				}, {
					xtype: 'menuseparator',
					itemId: 'option.editSep'
				}, {
					itemId: 'option.edit',
					iconCls: 'x-menu-icon-edit',
					text: 'Edit',
					href: '#/schedulertasks/{id}/edit'
				}]
			}
		],

		multiSelect: true,
		selType: 'selectedmodel',

		listeners: {
			selectionchange: function(selModel, selections) {
				var toolbar = this.down('scalrpagingtoolbar');
				toolbar.down('#delete').setDisabled(!selections.length);
				toolbar.down('#activate').setDisabled(!selections.length);
				toolbar.down('#suspend').setDisabled(!selections.length);
				toolbar.down('#execute').setDisabled(!selections.length);
			}
		},

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			beforeItems: [{
				ui: 'paging',
				iconCls: 'x-tbar-add',
				handler: function() {
					Scalr.event.fireEvent('redirect', '#/schedulertasks/create');
				}
			}],
			afterItems: [{
				ui: 'paging',
				itemId: 'delete',
				disabled: true,
				iconCls: 'x-tbar-delete',
				tooltip: 'Delete',
				handler: function() {
					var request = {
						confirmBox: {
							type: 'delete',
							msg: 'Delete selected task(s): %s ?'
						},
						processBox: {
							type: 'delete'
						},
						url: '/schedulertasks/xDelete/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), tasks = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						tasks.push(records[i].get('id'));
						request.confirmBox.objects.push(records[i].get('name'))
					}
					request.params = { tasks: Ext.encode(tasks) };
					Scalr.Request(request);
				}
			}, {
				ui: 'paging',
				itemId: 'activate',
				disabled: true,
				iconCls: 'x-tbar-activate',
				tooltip: 'Activate',
				handler: function() {
					var request = {
						confirmBox: {
							type: 'action',
							msg: 'Activate selected task(s)?',
							ok: 'Activate'
						},
						processBox: {
							type: 'action'
						},
						url: '/schedulertasks/xActivate/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), tasks = [];

					for (var i = 0, len = records.length; i < len; i++) {
						tasks.push(records[i].get('id'));
					}

					request.params = { tasks: Ext.encode(tasks) };
					Scalr.Request(request);
				}
			}, {
				ui: 'paging',
				itemId: 'suspend',
				disabled: true,
				iconCls: 'x-tbar-suspend',
				tooltip: 'Suspend',
				handler: function() {
					var request = {
						confirmBox: {
							type: 'action',
							msg: 'Suspend selected task(s)?',
							ok: 'Suspend'
						},
						processBox: {
							type: 'action'
						},
						url: '/schedulertasks/xSuspend/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), tasks = [];

					for (var i = 0, len = records.length; i < len; i++) {
						tasks.push(records[i].get('id'));
					}

					request.params = { tasks: Ext.encode(tasks) };
					Scalr.Request(request);
				}
			}, {
				ui: 'paging',
				itemId: 'execute',
				disabled: true,
				iconCls: 'x-tbar-launch',
				tooltip: 'Execute',
				handler: function() {
					var request = {
						confirmBox: {
							type: 'action',
							msg: 'Execute selected task(s)?',
							ok: 'Execute'
						},
						processBox: {
							type: 'action'
						},
						url: '/schedulertasks/xExecute/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), tasks = [];

					for (var i = 0, len = records.length; i < len; i++) {
						tasks.push(records[i].get('id'));
					}

					request.params = { tasks: Ext.encode(tasks) };
					Scalr.Request(request);
				}
			}],
			items: [{
				xtype: 'filterfield',
				store: store
			}]
		}]
	});
});
