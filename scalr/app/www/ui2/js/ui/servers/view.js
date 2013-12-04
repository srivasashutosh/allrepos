Scalr.regPage('Scalr.ui.servers.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'cloud_location', 'flavor', 'cloud_server_id', 'excluded_from_dns', 'server_id', 'remote_ip',
			'local_ip', 'status', 'platform', 'farm_name', 'role_name', 'index', 'role_id', 'farm_id', 'farm_roleid',
			'uptime', 'ismaster', 'os_family', 'has_eip', 'is_szr', 'cluster_position', 'agent_version', 'agent_update_needed', 'agent_update_manual',
			'initDetailsSupported', 'isInitFailed', 'la_server', 'launch_error', 'alerts', 'cluster_role'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/servers/xListServers/'
		},
		remoteSort: true
	});
	store.proxy.extraParams.hideTerminated = true;

	var laStore = Ext.create('store.store', {
		fields: [ 'server_id', 'la', 'time' ],
		proxy: 'object'
	});

	var confirmationTerminateOptions = {
		xtype: 'fieldset',
		title: 'Termination parameters',
		items: [{
			xtype: 'checkbox',
			boxLabel: 'Decrease \'Minimum servers\' setting',
			inputValue: 1,
			name: 'descreaseMinInstancesSetting'
		}, {
			xtype: 'checkbox',
			boxLabel: 'Forcefully terminate selected server(s)',
			inputValue: 1,
			name: 'forceTerminate'
		}]
	};

	var updateHandler = {
		id: 0,
		timeout: 15000,
		running: false,
		schedule: function (run) {
			this.running = Ext.isDefined(run) ? run : this.running;

			clearTimeout(this.id);
			if (this.running)
				this.id = Ext.Function.defer(this.start, this.timeout, this);
		},
		start: function () {
			var list = [];

			if (! this.running)
				return;

			store.each(function (r) {
				if (r.get('status') != 'Terminated')
					list.push(r.get('server_id'));
			});

			if (! list.length) {
				this.schedule();
				return;
			}

			Scalr.Request({
				url: '/servers/xListServersUpdate/',
				params: {
					servers: Ext.encode(list)
				},
				success: function (data) {
					for (var serverId in data.servers) {
						var r = store.findRecord('server_id', serverId);
						if (r) {
							r.set(data.servers[serverId]);
							r.commit();
						}
					}
					this.schedule();
				},
				failure: function () {
					this.schedule();
				},
				scope :this
			});
		}
	};
	store.on('load', function() {
		// reset to start
		this.schedule(true);
	}, updateHandler);

	var laHandler = {
		id: 0,
		timeout: 60000,
		running: false,
		cache: {},
		updateEvent: function () {
			laStore.removeAll();
			this.schedule(this.running, true);
		},
		schedule: function (run, start) {
			this.running = Ext.isDefined(run) ? run : this.running;
			start = start == true ? 0 : this.timeout;

			clearTimeout(this.id);
			if (this.running)
				this.id = Ext.Function.defer(this.start, start, this);
		},
		start: function () {
			var dt = new Date();

			if (! this.running)
				return;

			for (var i = 0; i < store.getCount(); i++) {
				var r = store.getAt(i);

				if (r.get('status') == 'Running') {
					var la = laStore.findRecord('server_id', r.get('server_id'));

					if (!la || (la.get('time') > dt)) {
						r.set('la_server', '<img src="/ui2/images/icons/anim/snake_16x16.gif">');
						r.commit();
						if (! la)
							la = laStore.add({ server_id: r.get('server_id') })[0];

						Scalr.Request({
							url: '/servers/xServerGetLa/',
							params: { serverId: r.get('server_id') },
							success: function (data) {
								r.set({
									'la_server': data.la
								});
								r.commit();
								la.set({
									'time': Ext.Date.add(new Date(), Date.MINUTE, 3),
									'la': data.la
								});
								la.commit();
								Ext.Function.defer(this.start, 50, this);
							},
							failure: function (data) {
								r.set({
									'la_server': '<img src="/ui2/images/icons/warning_icon_16x16.png" title="' + (data && data.errorMessage || 'Cannot proceed request') + '">'
								});
								r.commit();
								la.set({
									'time': Ext.Date.add(new Date(), Date.MINUTE, 3),
									'la': data && data.la
								});
								la.commit();
								Ext.Function.defer(this.start, 50, this);
							},
							scope: this
						});
						return false;
					} else {
						r.set('la_server', la.get('la'));
						r.commit();
					}
				} else {
					r.set('la_server', '-');
					r.commit();
				}
			}
			this.schedule();
		}
	};
	store.on('load', laHandler.updateEvent, laHandler);

	return Ext.create('Ext.grid.Panel', {
		title: 'Servers &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { farmId: '', roleId: '', farmRoleId: '', serverId: '' },
		store: store,
		stateId: 'grid-servers-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Servers',
				href: '#/servers/view'
			}
		}],

		viewConfig: {
			emptyText: 'No servers found',
			loadingText: 'Loading servers ...',
			getRowClass: function (record, rowIndex, rowParams) {
				//TODO: replace == 9999 with > 0 when ready
				return (!record.get('is_szr') && record.get('status') == 'Running') || (record.get('alerts') > 0) ? 'x-grid-row-red' : '';
			},
			listeners: {
				itemclick: function (view, record, item, index, e) {
					if (e.getTarget('a.updateAgent')) {
						Scalr.Request({
							processBox: {
								type: 'action'
							},
							url: e.getTarget('a.updateAgent').href
						})
						e.preventDefault();
					}
				}
			}
		},

		columns: [
			{ header: "Cloud", width: 80, dataIndex: 'platform', sortable: true },
			{ header: "Farm & Role", flex: 2, dataIndex: 'farm_name', sortable: true, xtype: 'templatecolumn',
			    doSort: function (state) {
			        var ds = this.up('tablepanel').store;
			        ds.sort([{
			            property: 'farm_name',
			            direction: state
			        }, {
			            property: 'role_name',
			            direction: state
			        }, {
			            property: 'index',
			            direction: state
			        }]);
			    }, tpl:
				'<tpl if="farm_id">' +
					'<a href="#/farms/{farm_id}/view" title="Farm {farm_name}">{farm_name}</a>' +
					'<tpl if="role_name">' +
						'&nbsp;&rarr;&nbsp;<a href="#/farms/{farm_id}/roles/{farm_roleid}/view" title="Role {role_name}">{role_name}</a> ' +
					'</tpl>' +
					'<tpl if="!role_name">' +
						'&nbsp;&rarr;&nbsp;*removed role*&nbsp;' +
					'</tpl>' +
					'#<a href="#/servers/{server_id}/view">{index}</a>'+
				'</tpl>' +
				'<tpl if="cluster_role"> ({cluster_role})</tpl>' +
				'<tpl if="cluster_position"> ({cluster_position})</tpl>' +
				'<tpl if="! farm_id"><img src="/ui2/images/icons/false.png" /></tpl>'
			},
			{ header: "Server ID", flex: 1, dataIndex: 'server_id', sortable: true, xtype: 'templatecolumn', tpl: new Ext.XTemplate(
				'<tpl if="!is_szr && status == &quot;Running&quot;"><div><a href="http://blog.scalr.net/announcements/ami-scripts/" target="_blank"><img src="/ui2/images/icons/message/error_16x16.png" width="12" title="This server using old (deprecated) scalr agent. Please click here for more informating about how to upgrade it."></a>&nbsp;</tpl>' +
				'<a href="#/servers/{server_id}/extendedInfo">{[this.serverId(values.server_id)]}</a>' +
				'<tpl if="!is_szr && status == &quot;Running&quot;"></div></tpl>', {
					serverId: function(id) {
						var values = id.split('-');
						return values[0] + '-...-' + values[values.length - 1];
					}
				})
			},
			{ header: "Cloud Server ID", width: 100, dataIndex: 'cloud_server_id', sortable: false, hidden: true, xtype:'templatecolumn',  tpl:
				'<tpl if="cloud_server_id">{cloud_server_id}</tpl>' +
				'<tpl if="!cloud_server_id"><img src="/ui2/images/icons/false.png" /></tpl>'
			},
			{ header: "Cloud Location", width: 100, dataIndex: 'cloud_location', sortable: false, hidden: true },
			{ header: "Status", width: 150, dataIndex: 'status', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="alerts &gt; 0"><a href="#/alerts?serverId={server_id}"><img src="/ui2/images/icons/message/error_16x16.png" width="13" title="{alerts} alert(s). Click for more information."></a> </tpl>' +
				'<tpl if="initDetailsSupported">' +
					'<tpl if="isInitFailed"><span style="color: red">Failed</span> (<a href="#/operations/details?serverId={server_id}&operation=Initialization">Why?</a>)</tpl>' +
					'<tpl if="!isInitFailed">' +
						'<tpl if="status == &quot;Pending&quot; || status == &quot;Initializing&quot;"><img src="/ui2/images/icons/running.gif" style="vertical-align:middle; margin-top:-2px;" /> <a href="#/operations/details?serverId={server_id}&operation=Initialization">{status}</a></tpl>' +
						'<tpl if="launch_error == 1">{status} (<a href="#/operations/details?serverId={server_id}&operation=Initialization">Why?</a>)</tpl>' +
						'<tpl if="status != &quot;Pending&quot; && status != &quot;Initializing&quot; && launch_error != 1">{status}</tpl>' +
					'</tpl>' +
				'</tpl>' +
				'<tpl if="!initDetailsSupported"><tpl if="status == &quot;Pending&quot; || status == &quot;Initializing&quot;"><img src="/ui2/images/icons/running.gif" style="vertical-align:middle; margin-top:-2px;" /> </tpl>{status}</tpl>'
			},
			{ header: 'Type', width: 100, dataIndex: 'flavor', sortable: false, hidden: true },
			{ header: "Remote IP", width: 120, dataIndex: 'remote_ip', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="remote_ip">' +
				'<tpl if="has_eip"><span style="color:green;">{remote_ip} <img title="Elastic IP" src="/ui2/images/icons/elastic_ip.png" /></span></tpl><tpl if="!has_eip">{remote_ip}</tpl>' +
				'</tpl>'
			},
			{ header: "Local IP", width: 120, dataIndex: 'local_ip', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="local_ip">{local_ip}</tpl>'
			},
			{ header: "Uptime", width: 200, dataIndex: 'uptime', sortable: false },
			{ header: "DNS", width: 38, dataIndex: 'excluded_from_dns', sortable: false, xtype: 'templatecolumn', align: 'center', tpl:
				'<tpl if="excluded_from_dns"><img src="/ui2/images/icons/false.png" /></tpl><tpl if="!excluded_from_dns"><img src="/ui2/images/icons/true.png" /></tpl>'
			},
			{ header: "LA", width: 50, dataIndex: 'la_server', itemId: 'la', sortable: false, hidden: true, align: 'center',
				listeners: {
					hide: function () {
						laHandler.schedule(false);
					},
					show: function () {
						laHandler.schedule(true, true);
					}
				}
			},
			{ header: "Agent", width: 80, dataIndex: 'agent_version', sortable: false, xtype: 'templatecolumn',  align: 'center', tpl:
				'<tpl if="(status == &quot;Running&quot; || status == &quot;Initializing&quot;)">' +
				'<tpl if="agent_update_needed"><a class="updateAgent" href="/servers/{server_id}/xUpdateAgent"><img src="/ui2/images/icons/message/warning_16x16.png" width="12" title="Your scalr agent version is too old. Please click here to update it to the latest version."></a> {agent_version}</tpl>'+
				'<tpl if="agent_update_manual"><a href="http://blog.scalr.net/announcements/ami-scripts/" target="_blank"><img src="/ui2/images/icons/message/error_16x16.png" width="12" title="This server using old (deprecated) scalr agent. Please click here for more informating about how to upgrade it."></a> {agent_version}</tpl>'+
				'<tpl if="!agent_update_needed && !agent_update_manual">{agent_version}</tpl>' +
				'</tpl><tpl if="!(status == &quot;Running&quot; || status == &quot;Initializing&quot;)"><img src="/ui2/images/icons/false.png"></tpl>'
			},
			{ header: "Actions", width: 80, minWidth: 80, fixed: true, dataIndex: 'id', sortable: false, hideable: false, align: 'center', xtype: 'templatecolumn', tpl: new Ext.XTemplate(
				'<tpl if="(status == &quot;Running&quot; || status == &quot;Initializing&quot;) && index != &quot;0&quot;">' +
					'<tpl if="os_family != \'windows\'">' +
						'<a href="#/servers/{server_id}/sshConsole" style="float:left;margin-right:2px;margin-left:4px;text-decoration: none;" class="scalr-ui-servers-actions-console"></a></tpl>' +
					'<a href="#/monitoring/view?farmId={farm_id}&role={farm_roleid}&server_index={index}" style="float:left;margin-right:2px;text-decoration: none;" class="scalr-ui-servers-actions-statsusage"></a>' +
					'<a href="#/scripts/execute?serverId={server_id}" style="float:left;text-decoration: none;" class="scalr-ui-servers-actions-execute"></a>' +
				'</tpl>' +
				'<tpl if="! ((status == &quot;Running&quot; || status == &quot;Initializing&quot;) && index != &quot;0&quot;)">' +
					'<img src="/ui2/images/icons/false.png">' +
				'</tpl>', {
					getServerId: function (serverId) {
						return serverId.replace(/-/g, '');
					}
				})
			}, {
				xtype: 'optionscolumn',
				getOptionVisibility: function (item, record) {
					var data = record.data;
					
					if (data.status == 'Troubleshooting') {
						if (item.itemId != 'option.info' && item.itemId != 'option.term')
						  return false;
						else
						  return true;
					}
					
                    if (item.itemId == 'option.logs' || item.itemId == 'option.scripting_logs') {
                        return true;
                    }
					if (item.itemId == 'option.dnsEx' && data['excluded_from_dns'])
						return false;

					if (item.itemId == 'option.dnsIn' && !data['excluded_from_dns'])
						return false;

					if (item.itemId == 'option.console')
						return ((data.platform == 'ec2' || data.platform == 'gce') && data.status != 'Terminated' && data.status != 'Pending launch');
						
					if (item.itemId == 'option.cloudWatch')
						return (data.platform == 'ec2' && data.status == 'Running');

					if (data.status == 'Importing' || data.status == 'Pending launch' || data.status == 'Temporary')  {
						if (item.itemId == 'option.cancel' || item.itemId == 'option.messaging')
							return true;
						else
							return false;
					} else {
						if (item.itemId == 'option.cancel')
							return false;

						if (item.itemId == 'option.info')
							return true;
						
						if (data.status == 'Terminated')
							return false;
							
						if (data.platform == 'gce' && item.itemId == 'option.reboot')
							return false;
							
						else
							return true;
					}
				},

				getVisibility: function (record) {
					return true;
				},

				optionsMenu: [{
					itemId: 'option.cancel',
					iconCls: 'x-menu-icon-cancel',
					text: 'Cancel',
					request: {
						processBox: {
							type: 'action'
						},
						url: '/servers/xServerCancelOperation/',
						dataHandler: function (record) {
							return { serverId: record.get('server_id') };
						},
						success: function () {
							store.load();
						}
					}
				}, {
					itemId: 'option.info',
					iconCls: 'x-menu-icon-info',
					text: 'Extended instance information',
					href: '#/servers/{server_id}/extendedInfo'
				}, {
					itemId: 'option.loadStats',
					iconCls: 'x-menu-icon-statsload',
					text: 'Load statistics',
					href: '#/monitoring/view?farmId={id}&role={farm_roleid}&server_index={index}'
				},{
					itemId: 'option.cloudWatch',
					iconCls: 'x-menu-icon-statsload',
					text: 'CloudWatch statistics',
					menuHandler: function (item) {
						var location = item.record.get('cloud_location').substring(0, (item.record.get('cloud_location').length-2));
						document.location.href = '#/tools/aws/ec2/cloudwatch/view?objectId=' + item.record.get('cloud_server_id') + '&object=InstanceId&namespace=AWS/EC2&region=' + location;
					}
					//href: '#/tools/aws/ec2/cloudwatch/view?objectId={cloud_server_id}&object=InstanceId&namespace=AWS/EC2&region={cloud_location}'
				}, {
					xtype: 'menuseparator',
					itemId: 'option.infoSep'
				}, {
					itemId: 'option.sync',
					text: 'Create server snapshot',
					iconCls: 'x-menu-icon-createserversnapshot',
					href: '#/servers/{server_id}/createSnapshot'
				}, {
					xtype: 'menuseparator',
					itemId: 'option.syncSep'
				}, {
					itemId: 'option.editRole',
					iconCls: 'x-menu-icon-configure',
					text: 'Configure role in farm',
					href: '#/farms/{farm_id}/edit?roleId={role_id}'
				}, {
					xtype: 'menuseparator',
					itemId: 'option.procSep'
				}, {
					itemId: 'option.dnsEx',
					text: 'Exclude from DNS zone',
					iconCls: 'x-menu-icon-excludedns',
					request: {
						processBox: {
							type: 'action'
						},
						url: '/servers/xServerExcludeFromDns/',
						dataHandler: function (record) {
							return { serverId: record.get('server_id') };
						},
						success: function () {
							store.load();
						}
					}
				}, {
					itemId: 'option.dnsIn',
					text: 'Include in DNS zone',
					iconCls: 'x-menu-icon-includedns',
					request: {
						processBox: {
							type: 'action'
						},
						url: '/servers/xServerIncludeInDns/',
						dataHandler: function (record) {
							return { serverId: record.get('server_id') };
						},
						success: function (data) {
							store.load();
						}
					}
				}, {
					xtype: 'menuseparator',
					itemId: 'option.editRoleSep'
				}, {
					itemId: 'option.console',
					text: 'View console output',
					iconCls: 'x-menu-icon-console',
					href: '#/servers/{server_id}/consoleoutput'
				},
				/*{ itemId: "option.process", text: 'View process list', href: '#/servers/{server_id}/processlist' },*/
				{
					itemId: 'option.messaging',
					text: 'Scalr internal messaging',
					iconCls: 'x-menu-icon-internalmessage',
					href: '#/servers/{server_id}/messages'
				},
				/*
				new Ext.menu.Separator({itemId: "option.mysqlSep"}),
				{itemId: "option.mysql",		text: 'Backup/bundle MySQL data', href: "#/dbmsr/status?farmid={farm_id}&type=mysql"},
				*/
				{
					xtype: 'menuseparator',
					itemId: 'option.execSep'
				}, {
					itemId: 'option.exec',
					iconCls: 'x-menu-icon-execute',
					text: 'Execute script',
					href: '#/scripts/execute?serverId={server_id}'
				}, {
					xtype: 'menuseparator',
					itemId: 'option.menuSep'
				}, {
					itemId: 'option.reboot',
					text: 'Reboot',
					iconCls: 'x-menu-icon-reboot',
					request: {
						confirmBox: {
							type: 'reboot',
							msg: 'Reboot server "{server_id}" ?'
						},
						processBox: {
							type: 'reboot',
							msg: 'Sending reboot command ...'
						},
						url: '/servers/xServerRebootServers/',
						dataHandler: function (record) {
							return { servers: Ext.encode([ record.get('server_id') ]) };
						},
						success: function () {
							store.load();
						}
					}
				}, {
					itemId: 'option.term',
					iconCls: 'x-menu-icon-terminate',
					text: 'Terminate',
					request: {
						confirmBox: {
							type: 'terminate',
							msg: 'Terminate server "{server_id}" ?',
							formWidth: 450,
							form: confirmationTerminateOptions
						},
						processBox: {
							type: 'terminate',
							msg: 'Terminating server(s) ...'
						},
						url: '/servers/xServerTerminateServers/',
						dataHandler: function (record) {
							return { servers: Ext.encode([ record.get('server_id') ]) };
						},
						success: function () {
							store.load();
						}
					}
				}, {
					xtype: 'menuseparator',
					itemId: 'option.logsSep'
				}, {
					itemId: 'option.logs',
					iconCls: 'x-menu-icon-logs',
					text: 'System logs',
					href: '#/logs/system?serverId={server_id}'
				}, {
					itemId: 'option.scripting_logs',
					iconCls: 'x-menu-icon-logs',
					text: 'Scripting logs',
					href: '#/logs/scripting?serverId={server_id}'
				}]
			}
		],

		multiSelect: true,
		selModel: {
			selType: 'selectedmodel',
			getVisibility: function(record) {
				return (record.get('status') == 'Running' || record.get('status') == 'Initializing');
			}
		},

		listeners: {
			activate: function () {
				updateHandler.schedule(true);
				laHandler.schedule(! this.headerCt.down('#la').isHidden(), true);
			},
			deactivate: function () {
				updateHandler.schedule(false);
				laHandler.schedule(false);
			},
			selectionchange: function(selModel, selections) {
				this.down('scalrpagingtoolbar').down('#reboot').setDisabled(!selections.length);
				this.down('scalrpagingtoolbar').down('#terminate').setDisabled(!selections.length);
			}
		},

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			afterItems: [{
				ui: 'paging',
				itemId: 'reboot',
				iconCls: 'x-tbar-reboot',
				tooltip: 'Select one or more servers to reboot them',
				disabled: true,
				handler: function() {
					var request = {
						confirmBox: {
							type: 'reboot',
							msg: 'Reboot selected server(s): %s ?'
						},
						processBox: {
							type: 'reboot',
							msg: 'Sending reboot command ...'
						},
						url: '/servers/xServerRebootServers/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), servers = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						servers.push(records[i].get('server_id'));
						request.confirmBox.objects.push(records[i].get('server_id'));
					}
					request.params = { servers: Ext.encode(servers) };
					Scalr.Request(request);
				}
			}, {
				ui: 'paging',
				itemId: 'terminate',
				iconCls: 'x-tbar-terminate',
				tooltip: 'Select one or more servers to terminate them',
				disabled: true,
				handler: function() {
					var request = {
						confirmBox: {
							type: 'terminate',
							msg: 'Terminate selected server(s): %s ?',
							form: confirmationTerminateOptions
						},
						processBox: {
							type: 'terminate',
							msg: 'Terminating servers(s) ...'
						},
						url: '/servers/xServerTerminateServers/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), servers = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						servers.push(records[i].get('server_id'));
						request.confirmBox.objects.push(records[i].get('server_id'));
					}
					request.params = { servers: Ext.encode(servers) };
					Scalr.Request(request);
				}
			}],
			items: [{
				xtype: 'filterfield',
				width: 300,
				form: {
					items: [{
						xtype: 'textfield',
						fieldLabel: 'Cloud server id',
						labelAlign: 'top',
						name: 'cloudServerId'
					}, {
						xtype: 'textfield',
						fieldLabel: 'Cloud server location',
						labelAlign: 'top',
						name: 'cloudServerLocation'
					}]
				},
				store: store
			}, ' ', {
				xtype: 'button',
				enableToggle: true,
				width: 190,
				text: 'Show terminated servers',
				toggleHandler: function (field, checked) {
					store.proxy.extraParams.hideTerminated = checked ? 'false' : 'true';
					store.loadPage(1);
				}
			}]
		}]
	});
});
