Scalr.regPage('Scalr.ui.admin.accounts.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			{name: 'id', type: 'int'}, 
			'name', 'dtadded', 'status', 'servers', 'users', 'envs', 'farms', 'limitEnvs', 'limitFarms', 'limitUsers', 'limitServers', 'ownerEmail', 'dnsZones', 'isTrial'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/admin/accounts/xListAccounts'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Admin &raquo; Accounts &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { accountId: '' },
		store: store,
		stateId: 'grid-admin-accounts-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}],
		viewConfig: {
			emptyText: 'No accounts found',
			loadingText: 'Loading accounts ...'
		},

		columns: [
			{ header: "ID", width: 60, dataIndex: 'id', sortable: true },
			{ header: "Name", flex:1, dataIndex: 'name', sortable: true },
			{ header: "Owner email", flex: 1, dataIndex: 'ownerEmail', sortable: false },
			{ header: "Added", flex: 1, dataIndex: 'dtadded', sortable: true, xtype: 'templatecolumn',
				tpl: '{[values.dtadded ? values.dtadded : ""]}'
			},
			{ text: "Status", width: 100, dataIndex: 'status', sortable: true, xtype: 'templatecolumn', tpl:
				new Ext.XTemplate('<span style="color: {[this.getClass(values.status)]}">{status} ({isTrial})</span>', {
					getClass: function (value) {
						if (value == 'Active')
							return "green";
						else if (value != 'Inactive')
							return "#666633";
						else
							return "red";
					}
				})
			},
			{ header: "Environments", width:  100, align:'center', dataIndex: 'envs', sortable: false, xtype: 'templatecolumn',
				tpl: '{envs}/{limitEnvs}'
			},
			{ header: "Users", width: 100, dataIndex: 'users', align:'center', sortable: false, xtype: 'templatecolumn',
				tpl: '{users}/{limitUsers}'
			},
			{ header: "Servers", width: 100, dataIndex: 'groups', align:'center', sortable: false, xtype: 'templatecolumn',
				tpl: '{servers}/{limitServers}'
			},
			{ header: "Farms", width: 100, dataIndex: 'farms', align:'center', sortable: false, xtype: 'templatecolumn',
				tpl: '{farms}/{limitFarms}'
			},
			{ header: "DNS Zones", width:  100, align:'center', dataIndex: 'dnsZones', sortable: false, xtype: 'templatecolumn',
				tpl: '{dnsZones}'
			},
			{
				xtype: 'optionscolumn',
				getOptionVisibility: function (item, record) {
					var data = record.data;

					return true;
				},

				optionsMenu: [{
					itemId: 'option.edit',
					iconCls: 'x-menu-icon-edit',
					text: 'Edit',
					href: "#/admin/accounts/{id}/edit"
				}, {
					itemId: 'option.login',
					iconCls: 'x-menu-icon-login',
					text: 'Login as owner',
					menuHandler: function(item) {
						Scalr.Request({
							processBox: {
								type: 'action'
							},
							url: '/admin/accounts/xLoginAs',
							params: {
								accountId: item.record.get('id')
							},
							success: function() {
								Scalr.event.fireEvent('lock');
								Scalr.event.fireEvent('redirect', '#/dashboard', true);
								Scalr.event.fireEvent('unlock');
								Scalr.application.updateContext();
							}
						});
					}
				}, {
					itemId: 'option.terminateFarm',
					iconCls: 'x-menu-icon-login',
					text: 'Login as user',
					request: {
						processBox: {
							type: 'action'
						},
						url: '/admin/accounts/xGetUsers',
						dataHandler: function (record) {
							return { accountId: record.get('id') };
						},
						success: function (data) {
							Scalr.Request({
								confirmBox: {
									type: 'action',
									msg: 'Please select user. You can search by id, email, fullname, type.',
									form: [{
										xtype: 'combo',
										name: 'userId',
										store: {
											fields: [ 'id', 'email', 'fullname', 'type' ],
											data: data.users,
											proxy: 'object'
										},
										allowBlank: false,
										forceSelection: true,
										filterFn: function(queryString, item) {
											var value = new RegExp(queryString);
											return (
												value.test(item.get('id')) ||
													value.test(item.get('email')) ||
													value.test(item.get('fullname')) ||
													value.test(item.get('type'))
												) ? true : false;
										},
										valueField: 'id',
										displayField: 'email',
										queryMode: 'local',
										listConfig: {
											cls: 'x-boundlist-alt',
											tpl:
												'<tpl for="."><div class="x-boundlist-item" style="height: auto; width: auto">' +
													'[{id}] {email} [{type}]' +
													'</div></tpl>'
										}
									}]
								},
								processBox: {
									type: 'action'
								},
								url: '/admin/accounts/xLoginAs',
								success: function() {
									Scalr.event.fireEvent('lock');
									Scalr.event.fireEvent('redirect', '#/dashboard', true);
									Scalr.event.fireEvent('unlock');
									Scalr.application.updateContext();
								}
							});
						}
					}
				}]
			}
		],

		multiSelect: true,
		selType: 'selectedmodel',
		listeners: {
			selectionchange: function(selModel, selections) {
				var toolbar = this.down('scalrpagingtoolbar');
				toolbar.down('#delete').setDisabled(!selections.length);
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
					Scalr.event.fireEvent('redirect', '#/admin/accounts/create');
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
							msg: 'Remove selected accounts(s): %s ?'
						},
						processBox: {
							type: 'delete',
							msg: 'Removing account(s)...'
						},
						url: '/admin/accounts/xRemove',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), data = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						data.push(records[i].get('id'));
						request.confirmBox.objects.push(records[i].get('name'))
					}
					request.params = { accounts: Ext.encode(data) };
					Scalr.Request(request);
				}
			}],
			items: [{
				xtype: 'filterfield',
				width: 250,
				form: {
					items: [{
						xtype: 'textfield',
						fieldLabel: 'FarmId',
						labelAlign: 'top',
						name: 'farmId'
					}, {
						xtype: 'textfield',
						fieldLabel: 'Owner',
						labelAlign: 'top',
						name: 'owner'
					}, {
						xtype: 'textfield',
						fieldLabel: 'User',
						labelAlign: 'top',
						name: 'user'
					} ,{
						xtype: 'textfield',
						fieldLabel: 'EnvId',
						labelAlign: 'top',
						name: 'envId'
					}]
				},
				store: store
			}]
		}]
	});
});
