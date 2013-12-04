Ext.getHead().createChild('<style type="text/css">' +
	'.scalr-ui-farms-view-td-lock .x-grid-cell-inner { padding: 0px 17px 2px; }' +
	'.scalr-ui-farms-view-lock { background: url("/ui2/images/ui/farms/view/lock.png") 0px 0px; width: 20px; height: 20px; cursor: pointer; }' +
	'.scalr-ui-farms-view-unlock { background: url("/ui2/images/ui/farms/view/lock.png") 0px -20px; width: 20px; height: 20px; cursor: pointer; }' +
	'</style>');

Scalr.regPage('Scalr.ui.farms.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			{name: 'id', type: 'int'},
			{name: 'clientid', type: 'int'},
			'name', 'status', 'dtadded', 'running_servers', 'non_running_servers', 'roles', 'zones','client_email',
			'havemysqlrole','shortcuts', 'havepgrole', 'haveredisrole', 'haverabbitmqrole', 'havemongodbrole', 'havemysql2role', 'havemariadbrole', 
			'haveperconarole', 'lock', 'lock_comment', 'created_by_id', 'created_by_email', 'alerts'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/farms/xListFarms'
		},
		remoteSort: true
	});
	
	if (loadParams['demoFarm'] == 1) {
		Scalr.message.Success("Your first environment successfully configured and linked to your AWS account. Please use 'Options -> Launch' menu to launch your first demo LAMP farm.");
	}

	var grid = Ext.create('Ext.grid.Panel', {
		title: 'Farms &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { farmId: '', clientId: '', status: '' },
		store: store,
		stateId: 'grid-farms-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Farms',
				href: '#/farms/view'
			}
		}],

		viewConfig: {
			emptyText: 'No farms found',
			loadingText: 'Loading farms ...',
			disableSelection: true
		},

		columns: [
			{ text: "Farm ID", width: 70, dataIndex: 'id', sortable: true },
			{ text: "Farm Name", flex: 1, dataIndex: 'name', sortable: true },
			{ text: "Added", flex: 1, dataIndex: 'dtadded', sortable: true },
			{ text: "Owner", flex: 1, dataIndex: 'created_by_email', sortable: true },
			{ text: "Servers", width: 100, dataIndex: 'servers', sortable: false, xtype: 'templatecolumn',
				tpl: '<span style="color:green;">{running_servers}</span>/<span style="color:gray;">{non_running_servers}</span> [<a href="#/servers/view?farmId={id}">View</a>]'
			},
			{ text: "Roles", width: 70, dataIndex: 'roles', sortable: false, align:'center', xtype: 'templatecolumn',
				tpl: '<a href="#/farms/{id}/roles">{roles}</a>'
			},
			{ text: "DNS zones", width: 100, dataIndex: 'zones', sortable: false, align:'center', xtype: 'templatecolumn',
				tpl: '<a href="#/dnszones/view?farmId={id}">{zones}</a>'
			},
			{ text: "Status", width: 100, dataIndex: 'status', sortable: true, xtype: 'templatecolumn', tpl:
				new Ext.XTemplate('<span style="color: {[this.getClass(values.status)]}">{[this.getName(values.status)]}</span>', {
					getClass: function (value) {
						if (value == 1)
							return "green";
						else if (value == 3)
							return "#666633";
						else
							return "red";
					},
					getName: function (value) {
						var titles = {
							1: "Running",
							0: "Terminated",
							2: "Terminating",
							3: "Synchronizing"
						};
						return titles[value] || value;
					}
				})
			}, { text: "Alerts", width: 90, dataIndex: 'alerts', align:'center', sortable: false, xtype: 'templatecolumn',
				tpl: '<tpl if="status == 1">'+
					'<tpl if="alerts &gt; 0"><span style="color:red;">{alerts}</span> [<a href="#/alerts/view?farmId={id}&status=failed">View</a>]<tpl else><span style="color:green;">0</span></tpl>'+
				'<tpl else><img src="/ui2/images/icons/false.png" /></tpl>'
			}, {
				text: 'Lock', width: 55, dataIndex: 'lock', fixed: true, resizable: false, sortable: false, tdCls: 'scalr-ui-farms-view-td-lock', xtype: 'templatecolumn', tpl:
					'<tpl if="lock"><div class="scalr-ui-farms-view-lock" title="{lock_comment}"></div><tpl else><div class="scalr-ui-farms-view-unlock" title="Lock farm"></div></tpl>'
			}, {
				xtype: 'optionscolumn',
				getOptionVisibility: function (item, record) {
					var data = record.data;
                    
					if (item.itemId == 'option.launchFarm')
						return (data.status == 0);

					if (item.itemId == 'option.terminateFarm')
						return (data.status == 1);

					if (item.itemId == 'option.controlSep')
						return (data.status == 1 || data.status == 0);

					if (item.itemId == 'option.scSep')
						return (data.shortcuts.length > 0);

					if (item.itemId == 'option.viewMap' ||
						item.itemId == 'option.viewMapSep' ||
						item.itemId == 'option.loadStats' ||
						item.itemId == 'option.mysqlSep' ||
						item.itemId == 'option.mysql' ||
						item.itemId == 'option.mysql2' ||
						item.itemId == 'option.postgresql' ||
						item.itemId == 'option.mariadb' ||
						item.itemId == 'option.percona' ||
						item.itemId == 'option.redis' ||
						item.itemId == 'option.rabbitmq' ||
						item.itemId == 'option.mongodb' ||
						item.itemId == 'option.script'
						) {

						if (data.status == 0)
							return false;
						else
						{
							if (item.itemId == 'option.postgresql')
								return data.havepgrole;
							else if (item.itemId == 'option.redis')
								return data.haveredisrole;
							else if (item.itemId == 'option.mysql')
								return data.havemysqlrole;
							else if (item.itemId == 'option.mysql2')
								return data.havemysql2role;
							else if (item.itemId == 'option.rabbitmq')
								return data.haverabbitmqrole;
							else if (item.itemId == 'option.mongodb')
								return data.havemongodbrole;
							else if (item.itemId == 'option.percona')
								return data.haveperconarole;
						    else if (item.itemId == 'option.mariadb')
                                return data.havemariadbrole;
							else
								return true;
						}
					}

					return true;
				},

				beforeShowOptions: function (record, menu) {
					menu.items.each(function (item) {
						if (item.isshortcut) {
							menu.remove(item);
						}
					});

					if (record.get('shortcuts').length) {
						menu.add({
							xtype: 'menuseparator',
							isshortcut: true
						});

						Ext.Array.each(record.get('shortcuts'), function (shortcut) {
							if (typeof(shortcut) != 'function') {
								menu.add({
									isshortcut: true,
									text: 'Execute ' + shortcut.name,
									href: '#/scripts/execute?eventName=' + shortcut.event_name
								});
							}
						});
					}
				},

				optionsMenu: [{
					itemId: 'option.addToDash',
					text: 'Add to Dashboard',
					iconCls: 'x-menu-icon-dashboard',
					request: {
						processBox: {
							type: 'action',
							msg: 'Adding new widget to dashboard ...'
						},
						url: '/dashboard/xUpdatePanel',
						dataHandler: function (record) {
							var data = Ext.encode({params: {farmId: record.get('id')}, name: 'dashboard.farm', url: '' });
							return {widget: data};
						},
						success: function (data, response, options) {
							Scalr.event.fireEvent('update', '/dashboard', data.panel);
							Scalr.storage.set('dashboard', Ext.Date.now());
						}
					}
				}, {
					xtype: 'menuseparator',
					itemId: 'option.dashSep'
				}, {
					itemId: 'option.launchFarm',
					text: 'Launch',
					iconCls: 'x-menu-icon-launch',
					request: {
						confirmBox: {
							type: 'launch',
							msg: 'Are you sure want to launch farm "{name}" ?'
						},
						processBox: {
							type: 'launch',
							msg: 'Launching farm ...'
						},
						url: '/farms/xLaunch/',
						dataHandler: function (record) {
							return { farmId: record.get('id') };
						},
						success: function () {
							store.load();
						}
					}
				}, {
					itemId: 'option.terminateFarm',
					iconCls: 'x-menu-icon-terminate',
					text: 'Terminate',
					request: {
						processBox: {
							type:'action'
						},
						url: '/farms/xGetTerminationDetails/',
						dataHandler: function (record) {
							return { farmId: record.get('id') };
						},
						success: function (data) {
							var items = [];
							for (var i = 0; i < data.roles.length; i++) {
								var t = {
									xtype: 'checkbox',
									cci: i,
									name: 'sync[]',
									fName: 'sync',
									inputValue: data.roles[i].id,
									boxLabel: '<b>' + data.roles[i].name + '</b> (Last synchronization: ' + data.roles[i].dtLastSync + ')',
									handler: function (checkbox, checked) {
										var c = this.ownerCt.query('[ci="' + checkbox.cci + '"]');
										for (var k = 0; k < c.length; k++) {
											if (checked)
												c[k].show();
											else
												c[k].hide();
										}

										var c = this.ownerCt.query('[fName="sync"]'), flag = false;
										for (var k = 0; k < c.length; k++) {
											if (c[k].checked) {
												flag = true;
												break;
											}
										}

										if (flag)
											this.up('fieldset').ownerCt.down('[name="unTermOnFail"]').show();
										else
											this.up('fieldset').ownerCt.down('[name="unTermOnFail"]').hide();
									}
								};

								if (data.roles[i].isBundleRunning) {
									t.disabled = true;
									items.push(t);
									items.push({
										xtype: 'displayfield',
										anchor: '100%',
										margin: '0 0 0 20',
										value: 'Synchronization for this role already running ...'
									});
								} else if (! Ext.isArray(data.roles[i].servers)) {
									t.disabled = true;
									items.push(t);
									items.push({
										xtype: 'displayfield',
										anchor: '100%',
										style: 'margin-left: 20px',
										value: 'No running servers found on this role'
									});
								} else {
									var s = [];
									for (var j = 0; j < data.roles[i].servers.length; j++) {
										s[s.length] = {
											boxLabel: data.roles[i].servers[j].remoteIp + ' (' + data.roles[i].servers[j].server_id + ')',
											name: 'syncInstances[' + data.roles[i].id + ']',
											checked: j == 0 ? true : false, // select first
											inputValue: data.roles[i].servers[j].server_id
										};
									}

									items.push(t);

									items.push({
										xtype: 'radiogroup',
										hideLabel: true,
										columns: 1,
										hidden: true,
										ci: i,
										anchor: '100%',
										margin: '0 0 0 10',
										items: s
									});
								}
							}

							Scalr.Request({
								confirmBox: {
									type: 'terminate',
									disabled: data.isMongoDbClusterRunning || false,
									msg: 'Hey mate! Have you made any modifications to your instances since you launched the farm <b>' + data['farmName'] + '</b>? \'Cause if you did, you might want to save your modifications lest you lose them! Save them by taking a snapshot, which creates a machine image.',
									multiline: true,
									formWidth: 700,
									form: [{
										hidden: !data.isMongoDbClusterRunning,
										xtype: 'displayfield',
										fieldCls: 'x-form-field-warning',
										value: 'You currently have some Mongo instances in this farm. <br> Terminating it will result in <b>TOTAL DATA LOSS</b> (yeah, we\'re serious).<br/> Please <a href=\'#/services/mongodb/status?farmId='+data.farmId+'\'>shut down the mongo cluster</a>, then wait, then you\'ll be able to terminate the farm or just use force termination option (that will remove all mongodb data).'
									}, {
										hidden: !data.isMysqlRunning,
										xtype: 'displayfield',
										fieldCls: 'x-form-field-warning',
										value: 'Server snapshot will not include database data. You can create db data bundle on database manager page.'
									}, {
										xtype: 'fieldset',
										title: 'Synchronization settings',
										hidden: items.length ? false : true,
										items: items
									}, {
										xtype: 'fieldset',
										title: 'Termination options',
										items: [{
											xtype: 'checkbox',
											boxLabel: 'Forcefully terminate mongodb cluster (<b>ALL YOUR MONGODB DATA ON THIS FARM WILL BE REMOVED</b>)',
											name: 'forceMongoTerminate',
											hidden: !data.isMongoDbClusterRunning,
											listeners: {
												change: function () {
													if (this.checked)
														this.up().up().up().down('#buttonOk').enable();
													else
														this.up().up().up().down('#buttonOk').disable();
												}
											}
										}, {
											xtype: 'checkbox',
											boxLabel: 'Do not terminate a farm if synchronization fail on any role',
											name: 'unTermOnFail',
											hidden: true
										}, {
											xtype: 'checkbox',
											boxLabel: 'Delete DNS zone from nameservers. It will be recreated when the farm is launched.',
											name: 'deleteDNSZones'
										}, {
											xtype: 'checkbox',
											boxLabel: 'Delete cloud objects (EBS, Elastic IPs, etc)',
											name: 'deleteCloudObjects'
										}, {
											xtype: 'checkbox',
											checked: true,
											boxLabel: 'Forcefully terminate farm (Do not process beforeHostTerminate event)',
											name: 'forceTerminate'
										}]
									}]
								},
								processBox: {
									type: 'terminate'
								},
								url: '/farms/xTerminate',
								params: {farmId: data.farmId},
								success: function () {
									store.load();
								}
							});
						}
					}
				}, {
					xtype: 'menuseparator',
					itemId: 'option.controlSep'
				}, {
					itemId: 'option.info',
					iconCls: 'x-menu-icon-info',
					text: 'Extended information',
					href: "#/farms/{id}/extendedInfo"
				}, {
					itemId: 'option.usageStats',
					text: 'Usage statistics',
					iconCls: 'x-menu-icon-statsusage',
					href: '#/statistics/serversusage?farmId={id}'
				}, {
					itemId: 'option.loadStats',
					iconCls: 'x-menu-icon-statsload',
					text: 'Load statistics',
					href: '#/monitoring/view?farmId={id}'
				}, {
					itemId: 'option.events',
					text: 'Events & Notifications',
					iconCls: 'x-menu-icon-events',
					href: '#/farms/{id}/events'
				}, {
					xtype: 'menuseparator',
					itemId: 'option.mysqlSep'
				}, {
					itemId: 'option.mysql',
					iconCls: 'x-menu-icon-mysql',
					text: 'MySQL status',
					href: "#/dbmsr/status?farmId={id}&type=mysql"
				}, {
					itemId: 'option.mysql2',
					iconCls: 'x-menu-icon-mysql',
					text: 'MySQL status',
					href: "#/db/manager/dashboard?farmId={id}&type=mysql2"
				}, {
					itemId: 'option.percona',
					iconCls: 'x-menu-icon-percona',
					text: 'Percona Server status',
					href: "#/db/manager/dashboard?farmId={id}&type=percona"
				}, {
					itemId: 'option.postgresql',
					iconCls: 'x-menu-icon-postgresql',
					text: 'PostgreSQL status',
					href: "#/db/manager/dashboard?farmId={id}&type=postgresql"
				}, {
					itemId: 'option.redis',
					iconCls: 'x-menu-icon-redis',
					text: 'Redis status',
					href: "#/db/manager/dashboard?farmId={id}&type=redis"
				}, {
                    itemId: 'option.mariadb',
                    iconCls: 'x-menu-icon-mariadb',
                    text: 'MariaDB status',
                    href: "#/db/manager/dashboard?farmId={id}&type=mariadb"
                }, {
					itemId: 'option.rabbitmq',
					iconCls: 'x-menu-icon-rabbitmq',
					text: 'RabbitMQ status',
					href: "#/services/rabbitmq/status?farmId={id}"
				}, {
					itemId: 'option.mongodb',
					iconCls: 'x-menu-icon-mongodb',
					text: 'MongoDB status',
					href: "#/services/mongodb/status?farmId={id}"
				}, {
					itemId: 'option.script',
					iconCls: 'x-menu-icon-execute',
					text: 'Execute script',
					href: '#/scripts/execute?farmId={id}'
				}, {
					xtype: 'menuseparator',
					itemId: 'option.logsSep'
				}, {
					itemId: 'option.logs',
					iconCls: 'x-menu-icon-logs',
					text: 'View log',
					href: "#/logs/system?farmId={id}"
				}, {
					itemId: 'option.logs',
					iconCls: 'x-menu-icon-logs',
					text: 'Alerts',
					href: "#/alerts/view?farmId={id}"
				},{
					xtype: 'menuseparator',
					itemId: 'option.editSep'
				}, {
					itemId: 'option.edit',
					iconCls: 'x-menu-icon-configure',
					text: 'Configure',
					href: '#/farms/{id}/edit'
				}, {
					itemId: 'option.clone',
					iconCls: 'x-menu-icon-clone',
					text: 'Clone',
					request: {
						confirmBox: {
							type: 'action',
							msg: 'Are you sure want to clone farm "{name}" ?'
						},
						processBox: {
							type: 'action',
							msg: 'Cloning farm ...'
						},
						url: '/farms/xClone/',
						dataHandler: function (record) {
							return { farmId: record.get('id') };
						},
						success: function () {
							store.load();
						}
					}
				}, {
					itemId: 'option.delete',
					iconCls: 'x-menu-icon-delete',
					text: 'Delete',
					request: {
						confirmBox: {
							type: 'delete',
							msg: 'Are you sure want to remove farm "{name}" ?'
						},
						processBox: {
							type: 'delete',
							msg: 'Removing farm ...'
						},
						url: '/farms/xRemove/',
						dataHandler: function (record) {
							return { farmId: record.get('id') };
						},
						success: function () {
							store.load();
						}
					}
				}]
			}
		],
		listeners: {
			itemclick: function(grid, record, item, index, e) {
				if (e.getTarget('div.scalr-ui-farms-view-lock')) {
					Scalr.Request({
						confirmBox: {
							type: 'action',
							msg: 'Are you sure want to unlock farm "' + record.get('name') + '" ?'
						},
						processBox: {
							type: 'action',
							msg: 'Unlocking farm ...'
						},
						url: '/farms/xUnlock/',
						params: {
							farmId: record.get('id')
						},
						success: function () {
							store.load();
						}
					});
				} else if (e.getTarget('div.scalr-ui-farms-view-unlock')) {
					var message = 'Only farm owner (' + record.get('created_by_email') + ') can unlock this farm';
					Scalr.Request({
						confirmBox: {
							type: 'action',
							msg: 'Are you sure want to lock farm "' + record.get('name') + '" and protect it from any changes to configuration and launch / terminate / remove actions ?',
							formWidth: 500,
							formValidate: true,
							form: [{
								xtype: 'fieldset',
								defaults: {
									anchor: '100%'
								},
								items: [{
									xtype: 'textarea',
									fieldLabel: 'Comment',
									labelWidth: 65,
									name: 'comment',
									allowBlank: false
								}, {
									xtype: 'checkbox',
									checked: true,
									hidden: !record.get('created_by_email'),
									boxLabel: message,
									name: 'restrict'
								}]
							}]
						},
						processBox: {
							type: 'action',
							msg: 'Locking farm ...'
						},
						params: {
							farmId: record.get('id')
						},
						url: '/farms/xLock/',
						success: function () {
							store.load();
						}
					});

				}
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
					Scalr.event.fireEvent('redirect', '#/farms/build');
				}
			}],
			items: [{
				xtype: 'filterfield',
				store: store
			}, ' ', {
				xtype: 'button',
				text: 'Show only my farms',
				enableToggle: true,
                pressed: Scalr.storage.get('grid-farms-view-show-only-my-farms'),
				width: 150,
				toggleHandler: function (field, checked) {
					store.proxy.extraParams.showOnlyMy = checked ? '1' : '';
                    Scalr.storage.set('grid-farms-view-show-only-my-farms', checked);
					store.loadPage(1);
				},
                listeners: {
                    added: function() {
                        store.proxy.extraParams.showOnlyMy = this.pressed ? '1' : '';
                    }
                }
			}]
		}]
	});

	/*grid.getView().on('viewready', function () {
		this.getView().on('refresh', function() {
			if (this.getStore().getAt(0)) {
				this.down('optionscolumn').showOptionsMenu(this.getView(), this.getStore().getAt(0));
			}
		}, grid, {
			single: true
		});
	}, grid);*/

	return grid;
});
