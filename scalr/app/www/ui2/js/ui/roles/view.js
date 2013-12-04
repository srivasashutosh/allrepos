Scalr.regPage('Scalr.ui.roles.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			{name: 'id', type: 'int'},
			{name: 'client_id', type: 'int'},
			'name', 'tags', 'origin', 'client_name', 'behaviors', 'os', 'platforms','generation','used_servers','status','behaviors_name'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/roles/xListRoles/'
		},
		remoteSort: true
	});

	var confirmationRemovalOptions = {
		xtype: 'fieldset',
		title: 'Removal parameters',
		hidden: moduleParams['isScalrAdmin'],
		items: [{
			xtype: 'checkbox',
			boxLabel: 'Remove image from cloud',
			inputValue: 1,
			checked: false,
			name: 'removeFromCloud'
		}]
	};
	
	var cloneOptions = {
		xtype: 'textfield',
		fieldLabel: 'New role name',
		editable: false,
		queryMode: 'local',
		value: '',
		name: 'newRoleName',
		labelWidth: 150,
		width: 500
	};

	return Ext.create('Ext.grid.Panel', {
		title: 'Roles &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { roleId: '', client_id: '' },
		store: store,
		stateId: 'grid-roles-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Roles',
				href: '#/roles/view'
			}
		}],

		viewConfig: {
			emptyText: "No roles found"
		},

		columns: [
			{ header: "Role name", flex: 2, dataIndex: 'name', sortable: true },
			{ header: "OS", width: 150, dataIndex: 'os', sortable: true },
			{ header: "Owner", flex: 1, dataIndex: 'client_name', sortable: false},
			{ header: "Behaviors", flex: 1, dataIndex: 'behaviors_name', sortable: false },
			{ header: "Available on", flex: 1, dataIndex: 'platforms', sortable: false },
			{ header: "Tags", width: 130, dataIndex: 'tags', sortable: false },
			{ header: "Status", width: 100, dataIndex: 'status', sortable: false, xtype: 'templatecolumn', tpl:
				'{status} ({used_servers})'
			},
			{ header: "Scalr agent", width: 100, dataIndex: 'generation', sortable: false },
			{
				xtype: 'optionscolumn',
				optionsMenu: [
					{ itemId: "option.view", iconCls: 'x-menu-icon-info', text:'View details', href: "#/roles/{id}/info" },
					{ itemId: "option.clone", iconCls: 'x-menu-icon-fork', text: 'Clone', request: {
						confirmBox: {
							type: 'action',
							form: cloneOptions,
							msg: 'Clone "{name}" role" ?'
						},
						processBox: {
							type: 'action',
							msg: 'Cloning role. Please wait ...'
						},
						url: '/roles/xClone/',
						dataHandler: function (record) {
							return { roleId: record.get('id') };
						},
						success: function () {
							Scalr.message.Success("Role successfully cloned");
							store.load();
						}
					}},
					{
						itemId: 'option.migrate',
						iconCls: 'x-menu-icon-fork',
						text: 'Copy to another EC2 region',
						request: {
							processBox: {
								type:'action'
							},
							url: '/roles/xGetMigrateDetails/',
							dataHandler: function (record) {
								return { roleId: record.get('id') };
							},
							success: function (data) {
								Scalr.Request({
									confirmBox: {
										type: 'action',
										msg: 'Copying images allows you to use roles in additional regions',
										formWidth: 700,
										form: [{
											xtype: 'fieldset',
											title: 'Region copy',
											items: [{
												xtype: 'displayfield',
												labelWidth: 120,
												width: 500,
												fieldLabel: 'Role name',
												value: data['roleName']	
											},{
												xtype: 'combo',
												fieldLabel: 'Source region',
												store: {
													fields: [ 'cloudLocation', 'name' ],
													proxy: 'object',
													data: data['availableSources']
												},
												autoSetValue: true,
												valueField: 'cloudLocation',
												displayField: 'name',
												editable: false,
												queryMode: 'local',
												name: 'sourceRegion',
												labelWidth: 120,
												width: 500
											}, {
												xtype: 'combo',
												fieldLabel: 'Destination region',
												store: {
													fields: [ 'cloudLocation', 'name' ],
													proxy: 'object',
													data: data['availableDestinations']
												},
												autoSetValue: true,
												valueField: 'cloudLocation',
												displayField: 'name',
												editable: false,
												queryMode: 'local',
												name: 'destinationRegion',
												labelWidth: 120,
												width: 500
											}]
										}]
									},
									processBox: {
										type: 'action'
									},
									url: '/roles/xMigrate',
									params: {roleId: data.roleId},
									success: function () {
										store.load();
									}
								});
							}
						}
					},
					{ itemId: "option.edit", iconCls: 'x-menu-icon-edit', text:'Edit', href: "#/roles/{id}/edit" }
				],

				getOptionVisibility: function (item, record) {
					if (item.itemId == 'option.view' || item.itemId == 'option.clone')
						return true;

					if (item.itemId == 'option.migrate')
						return (record.get('platforms').indexOf('EC2') != -1 && record.get('origin') == 'CUSTOM');

					if (record.get('origin') == 'CUSTOM') {
						if (item.itemId == 'option.edit') {
							if (! moduleParams.isScalrAdmin)
								return true;
							else
								return false;
						}
						return true;
					}
					else {
						return moduleParams.isScalrAdmin;
					}
				},

				getVisibility: function (record) {
					return (record.get('status').indexOf('Deleting') == -1);
				}
			}
		],

		multiSelect: true,
		selType: 'selectedmodel',

		listeners: {
			selectionchange: function(selModel, selections) {
				this.down('scalrpagingtoolbar').down('#delete').setDisabled(!selections.length);
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
					Scalr.event.fireEvent('redirect', '#/roles/builder');
				}
			}],
			afterItems: [{
				ui: 'paging',
				itemId: 'delete',
				iconCls: 'x-tbar-delete',
				tooltip: 'Select one or more roles to delete them',
				disabled: true,
				handler: function() {
					var request = {
						confirmBox: {
							msg: 'Remove selected role(s): %s ?',
								type: 'delete',
								form: confirmationRemovalOptions
						},
						processBox: {
							msg: 'Removing selected role(s) ...',
								type: 'delete'
						},
						url: '/roles/xRemove',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), roles = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						roles.push(records[i].get('id'));
						request.confirmBox.objects.push(records[i].get('name'));
					}
					request.params = { roles: Ext.encode(roles) };
					Scalr.Request(request);
				}
			}],
			items: [{
				xtype: 'filterfield',
				store: store,
				width: 300,
				form: {
					items: [{
						xtype: 'radiogroup',
						name: 'status',
						fieldLabel: 'Status',
						labelAlign: 'top',
						items: [{
							boxLabel: 'All',
							name: 'status',
							inputValue: ''
						}, {
							boxLabel: 'Used',
							name: 'status',
							inputValue: 'Used'
						}, {
							boxLabel: 'Not used',
							name: 'status',
							inputValue: 'Unused'
						}]
					}]
				}
			}, ' ', 'Location:', {
				xtype: 'combo',
				matchFieldWidth: false,
				width: 200,
				editable: false,
				store: {
					fields: [ 'id', 'name' ],
					data: moduleParams.locations,
					proxy: 'object'
				},
				displayField: 'name',
				valueField: 'id',
				value: '',
				queryMode: 'local',
				listeners: {
					change: function() {
						store.proxy.extraParams.cloudLocation = this.getValue();
						store.loadPage(1);
					}
				},
				iconCls: 'no-icon'
			}, ' ', 'Owner:', {
				xtype: 'buttongroupfield',
				value: '',
				items: [{
					xtype: 'button',
					text: 'All',
					value: '',
					width: 60
				}, {
					xtype: 'button',
					text: 'Scalr',
					width: 60,
					value: 'Shared'
				}, {
					xtype: 'button',
					text: 'Private',
					width: 60,
					value: 'Custom'
				}],
				listeners: {
					change: function (field, value) {
						store.proxy.extraParams.origin = value;
						store.loadPage(1);
					}
				}
			}]
		}]
	});
});
