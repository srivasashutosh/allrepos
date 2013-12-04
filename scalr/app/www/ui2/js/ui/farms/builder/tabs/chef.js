Scalr.regPage('Scalr.ui.farms.builder.tabs.chef', function () {
	var getListRecord = function(target, runList, type) {
		// TODO: fix
		if (type == 'update') {
			Ext.each(this.down('#runlist').store.getRange(), function(item){
				if(item.get('id') == runList.id)
					item.set(runList);
			});
		}
		if (type == 'create')
			this.down('#runlist').store.add(runList);
	};
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Chef settings',
		tab: 'chef',
		serversLoaded: false,

		getDefaultValues: function (record) {
			return {};
		},

		isEnabled: function (record) {
			return record.get('behaviors').match('chef');
		},

		beforeShowTab: function(record, handler) {
			if (this.serversLoaded) {
				handler();
			} else {
				this.down('[name="chefServer"]').store.on('load', handler, this, {
					single: true
				});

				this.down('[name="chefServer"]').prefetch();
			}
		},

		showTab: function (record) {
			var settings = record.get('settings');

			this.serversLoaded = true;
			Scalr.event.on('update', getListRecord, this);

			var chefId = settings['chef.runlist_id'];
			this.down('[name="chef.bootstrap"]').setValue(settings['chef.bootstrap']);
			if (chefId) {
				var runlists = this.down('#runlist');
				runlists.store.proxy.extraParams = {
					serverId: settings['chef.server_id'],
					chefEnvironment: settings['chef.environment']
				};
				runlists.chefId = chefId;
			}

			this.down('[name="chef.attributes"]').setValue(settings['chef.attributes']);
			this.down('[name="chef.daemonize"]').setValue(settings['chef.daemonize'] | 0);

			this.down('[name="chefEnvironment"]').setReadOnly(true);
			this.down('[name="chefEnvironment"]').environmentValue = settings['chef.environment'];
			this.down('[name="chefRole"]').setReadOnly(true);

			this.down('[name="chefServer"]').setValue(settings['chef.server_id']);
			this.down('[name="chefEnvironment"]').setValue(settings['chef.environment']);
			this.down('[name="chefRole"]').setValue(settings['chef.role_name']);
			this.down('[name="nodeName"]').setValue(settings['chef.node_name_tpl']);
		},

		hideTab: function (record) {
			var settings = record.get('settings');
			settings['chef.bootstrap'] = this.down('[name="chef.bootstrap"]').getValue() ? 1 : '';
			settings['chef.runlist_id'] = '';
			settings['chef.attributes'] = this.down('[name="chef.attributes"]').getValue();
			settings['chef.daemonize'] = this.down('[name="chef.daemonize"]').getValue();

			if (settings['chef.bootstrap']) {
				var r = this.down('#runlist').store.findRecord('current', true), data = {};
				if (r) {
					settings['chef.runlist_id'] = r.get('id');
				}

				settings['chef.server_id'] = this.down('[name="chefServer"]').getValue();
				settings['chef.environment'] = this.down('[name="chefEnvironment"]').getValue();
				settings['chef.role_name'] = this.down('[name="chefRole"]').getValue();
				settings['chef.node_name_tpl'] = this.down('[name="nodeName"]').getValue();
			}

			this.down('[name="chefServer"]').setValue();
			this.down('[name="chefEnvironment"]').setValue();
			this.down('[name="chefRole"]').setValue();
			this.down('[name="chef.attributes"]').setValue();

			Scalr.event.un('update', getListRecord, this);

			var runlist = this.down('#runlist');
			runlist.getSelectionModel().deselectAll();
			runlist.hide();

			this.down('[name="chef.bootstrap"]').setValue(false);

			record.set('settings', settings);
		},

		items: [{
			xtype: 'fieldset',
			title: 'Enable chef',
			checkboxToggle: true,
            toggleOnTitleClick: true,
            collapsible: true,
			collapsed: true,
			checkboxName: 'chef.bootstrap',
			layout: {
				type: 'hbox'
			},
			items: [{
				xtype: 'container',
                cls: 'x-panel-columned-leftcol',
				layout: 'anchor',
				width: 620,
				defaults: {
					anchor: '100%',
					labelWidth: 110
				},
				items: [{
					xtype: 'combo',
					name: 'chefServer',
					fieldLabel: 'Chef server',
					valueField: 'id',
					displayField: 'url',
					editable: false,
					value: '',
					store: {
						fields: [ 'url', 'id' ],
						proxy: {
							type: 'ajax',
							reader: {
								type: 'json',
								root: 'data'
							},
							url: '/services/chef/servers/xListServers/'
						}
					},
					listeners: {
						change: function(field, value) {
							if (value) {
								var f = this.next('[name="chefEnvironment"]');
								f.setReadOnly(false);
								f.queryReset = true;

								// hack, to preselect _default, when none being set
								if (! f.environmentValue)
									f.setValue('_default');

								f.store.proxy.extraParams['servId'] = value;
								
								var f1 = this.next('[name="chefRole"]');
								f1.setReadOnly(false);
								f1.queryReset = true;
								f1.store.proxy.extraParams['servId'] = value;
							}
						}
					}
				}, {
					xtype: 'combo',
					name: 'chefEnvironment',
					fieldLabel: 'Chef environment',
					store: {
						fields: [ 'name' ],
						proxy: {
							type: 'ajax',
							reader: {
								type: 'json',
								root: 'data'
							},
							url: '/services/chef/servers/xListEnvironments/'
						}
					},
					valueField: 'name',
					displayField: 'name',
					editable: false,
					value: '',
					listeners: {
						change: function(field, value) {
							var runlists = this.next('#runlist');
							if (value) {
								var f = this.next('[name="chefRole"]');
								f.setReadOnly(false);
								f.queryReset = true;
								f.store.proxy.extraParams['chefEnv'] = value;
								f.store.proxy.extraParams['servId'] = this.store.proxy.extraParams['servId'];

								runlists.store.proxy.extraParams = {
									serverId: f.store.proxy.extraParams['servId'],
									chefEnvironment: f.store.proxy.extraParams['chefEnv']
								};

								runlists.store.load(function() {
									if (runlists.chefId) {
										var r = this.findRecord('id', runlists.chefId);
										if (r) {
											r.set('current', true);
											runlists.getSelectionModel().select(r);
										}
									}
								});

								if (f.getValue()) {
									f.setValue('');
									f.prefetch();
								} else {
									runlists.show();
								}

							} else {
								runlists.hide();
							}
						}
					}
				}, {
					xtype: 'combo',
					name: 'chefRole',
					fieldLabel: 'Chef role',
					store: {
						fields: [ 'name', 'chef_type' ],
						proxy: {
							type: 'ajax',
							reader: {
								type: 'json',
								root: 'data'
							},
							url: '/services/chef/xListRoles'
						},
						remoteSort: true
					},
					valueField: 'name',
					displayField: 'name',
					editable: false,
					listeners: {
						change: function(field, value) {
							var runlists = this.next('#runlist');
							if (value)
								runlists.hide();
							else
								runlists.show();
						}
					}
				}, {
					xtype: 'fieldcontainer',
					fieldLabel: 'Node name',
					layout: 'hbox',
					items: [{
						xtype: 'textfield',
						name: 'nodeName',
						emptyText: 'Leave blank for scalr-generated name',
						submitEmptyText: false,
						flex: 1
					}, {
						xtype: 'displayinfofield',
						margin: '0 0 0 5',
						value: 'You can use the following variables: %image_id%, %external_ip%, %internal_ip%, %role_name%, %isdbmaster%, %instance_index%, ' +
							'%server_id%, %farm_id%, %farm_name%, %env_id%, %env_name%, %cloud_location%, %instance_id%, %avail_zone%<br />' +
							'For example: %instance_index%.%farm_id%.example.com'
					}]
				}, {
                    xtype: 'checkbox',
                    hideLabel: true,
                    name: 'chef.daemonize',
					hidden: !Scalr.flags['betaMode'],
                    boxLabel: 'Daemonize chef client'
                }, {
					xtype: 'grid',
                    margin: '30 0 0 0',
					itemId: 'runlist',
					hidden: true,
                    cls: 'x-grid-shadow',
					plugins: {
						ptype: 'gridstore'
					},
					viewConfig: {
						emptyText: 'No runLists found'
					},
					store: {
						fields: [ 'id', 'name', 'description', 'attributes', 'chefEnv', 'current' ],
						proxy: {
							type: 'ajax',
							reader: {
								type: 'json',
								root: 'data'
							},
							url: '/services/chef/xListRunList'
						}
					},
					columns: [{
						text: '',
						width: 35,
						dataIndex: 'current',
						sortable: false,
						xtype: 'radiocolumn'
					}, {
						text: 'Name',
						dataIndex: 'name',
						flex: 1
					}, {
						text: 'Description',
						flex: 2,
						dataIndex: 'description'
					}, {
						xtype: 'optionscolumn',
						optionsMenu: [{
							text:'Edit',
							iconCls: 'x-menu-icon-edit',
							menuHandler: function(item) {
								Scalr.event.fireEvent('redirect','#/services/chef/runlists/edit?runlistId=' + item.record.get('id'));
							}
						},{
							xtype: 'menuseparator',
							itemId: 'option.attachSep'
						},{
							text:'Source',
							iconCls: 'x-menu-icon-info',
							menuHandler: function(item) {
								Scalr.event.fireEvent('redirect','#/services/chef/runlists/source?runlistId=' + item.record.get('id'));
							}
						}],
						getVisibility: function (record) {
							return true;
						}
					}],
					dockedItems: [{
                        cls: 'x-toolbar',
                        dock: 'top',
                        layout: 'hbox',
                        defaults: {
                            margin: '0 0 0 10'
                        },
                        items: [{
                            xtype: 'label',
                            cls: 'x-fieldset-subheader',
                            html: 'Runlists',
                            margin: 0
                        },{
                            xtype: 'tbfill'
                        },{
                            xtype: 'button',
                            ui: 'action-dark',
                            iconCls: 'x-btn-groupacton-add',
							handler: function() {
								Scalr.event.fireEvent('redirect', '#/services/chef/runlists/create');
							}
						}]
					}]
				}]
			},{
				xtype: 'fieldset',
				title: 'Attributes',
				itemId: 'attrib',
				margin: '0 0 0 10',
				flex: 1,
				defaults: {
					anchor: '100%'
				},
				items: [{
					xtype: 'displayfield',
					fieldCls: 'x-form-field-warning',
					value: 'Attributes should be defined in json format'
				}, {
					xtype: 'textarea',
					hideLabel: true,
					name: 'chef.attributes',
					grow: true,
					growMax: 300,
					validateOnChange: false,
					validator: function(value) {
						return Ext.decode(value, true) == null ? 'Not valid json' : true;
					}
				}]
			}]
		}]
	});
});
