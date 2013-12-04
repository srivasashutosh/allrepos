Scalr.regPage('Scalr.ui.security.groups.edit', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		//bodyCls: 'x-panel-body-frame',
		width: 900,
		layout: {
			type: 'vbox',
			align: 'stretch'
		},
		scalrOptions: {
			modal:true
		},
		title: 'Security &raquo; Groups &raquo; '+((moduleParams['securityGroupId']) ? moduleParams['securityGroupId']+' &raquo; Edit' : 'Create'),
		fieldDefaults: {
			anchor: '100%'
		},

		items: [{
				flex: 1,
				xtype: 'grid',
				itemId: 'view',
				border: false,
				store: {
					proxy: 'object',
					fields: ['id', 'ipProtocol', 'fromPort', 'toPort' , 'cidrIp', 'comment']
				},
				plugins: {
					ptype: 'gridstore'
				},

				viewConfig: {
					emptyText: 'No IP based security rules defined',
					deferEmptyText: false
				},

				columns: [
					{ header: 'Protocol', width: 100, sortable: true, dataIndex: 'ipProtocol' },
					{ header: 'From port', width: 100, sortable: true, dataIndex: 'fromPort' },
					{ header: 'To port', width: 100, sortable: true, dataIndex: 'toPort' },
					{ header: 'CIDR IP', width: 270, sortable: true, dataIndex: 'cidrIp' },
					{ header: 'Comment', flex: 1, sortable: true, dataIndex: 'comment' },
					{ header: '&nbsp;', width: 36, sortable: false, dataIndex: 'id', align: 'center', xtype: 'templatecolumn',
						tpl: '<img class="delete" src="/ui2/images/icons/delete_icon_16x16.png">',
						listeners: {
							click: function(view, cell, recordIndex, cellIndex, e, record) {
								if (e.getTarget('img.delete'))
									view.store.remove(record);
							}
						}
					}
				],

				dockedItems: [{
					xtype: 'toolbar',
					dock: 'top',
					layout: {
						type: 'hbox',
						align: 'left',
						pack: 'start'
					},
					items: [{
						ui: 'paging',
						iconCls: 'x-tbar-add',
						handler: function() {
							Scalr.Confirm({
								form: [{
									xtype: 'combo',
									name: 'ipProtocol',
									fieldLabel: 'Protocol',
									labelWidth: 120,
									editable: false,
									store: [ 'tcp', 'udp', 'icmp' ],
									value: 'tcp',
									queryMode: 'local',
									allowBlank: false
								}, {
									xtype: 'textfield',
									name: 'fromPort',
									fieldLabel: 'From port',
									labelWidth: 120,
									allowBlank: false,
									validator: function (value) {
										if (value < -1 || value > 65535) {
											return 'Valid ports are - 1 through 65535';
										}
										return true;
									}
								}, {
									xtype: 'textfield',
									name: 'toPort',
									fieldLabel: 'To port',
									labelWidth: 120,
									allowBlank: false,
									validator: function (value) {
										if (value < -1 || value > 65535) {
											return 'Valid ports are - 1 through 65535';
										}
										return true;
									}
								}, {
									xtype: 'textfield',
									name: 'cidrIp',
									fieldLabel: 'CIDR IP',
									value: '0.0.0.0/0',
									labelWidth: 120,
									allowBlank: false
								}, {
									xtype: 'textfield',
									name: 'comment',
									fieldLabel: 'Comment',
									value: '',
									labelWidth: 120,
									allowBlank: true
								}],
								ok: 'Add',
								title: 'Add security rule',
								formValidate: true,
								closeOnSuccess: true,
								scope: this,
								success: function (formValues) {
									var view = this.up('#view'), store = view.store;

									if (store.findBy(function (record) {
										if (
											record.get('ipProtocol') == formValues.ipProtocol &&
												record.get('fromPort') == formValues.fromPort &&
												record.get('toPort') == formValues.toPort &&
												record.get('cidrIp') == formValues.cidrIp
											) {
											Scalr.message.Error('Such rule exists');
											return true;
										}
									}) == -1) {
										store.add(formValues);
										return true;
									} else {
										return false;
									}
								}
							});
						}
					}]
				}]
			}, {
				xtype:'component',
				html:' ',
				height: 20,
				style: {
					background: 'white'
				}
			}, {
				flex: 1,
				xtype: 'grid',
				itemId: 'viewSg',
				border: false,
				store: {
					proxy: 'object',
					fields: ['id', 'ipProtocol', 'fromPort', 'toPort' , 'sg', 'comment']
				},
				plugins: {
					ptype: 'gridstore'
				},

				viewConfig: {
					emptyText: 'No SG based security rules defined',
					deferEmptyText: false
				},

				columns: [
					{ header: 'Protocol', width: 100, sortable: true, dataIndex: 'ipProtocol' },
					{ header: 'From port', width: 100, sortable: true, dataIndex: 'fromPort' },
					{ header: 'To port', width: 100, sortable: true, dataIndex: 'toPort' },
					{ header: 'Security Group', width: 270, sortable: true, dataIndex: 'sg' },
					{ header: 'Comment', flex: 1, sortable: true, dataIndex: 'comment' },
					{ header: '&nbsp;', width: 36, sortable: false, dataIndex: 'id', align: 'center', xtype: 'templatecolumn',
						tpl: '<img class="delete" src="/ui2/images/icons/delete_icon_16x16.png">',
						listeners: {
							click: function(view, cell, recordIndex, cellIndex, e, record) {
								if (e.getTarget('img.delete'))
									view.store.remove(record);
							}
						}
					}
				],

				listeners: {
					itemclick: function (view, record, item, index, e) {
						if (e.getTarget('img.delete'))
							view.store.remove(record);
					}
				},
				
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'top',
					layout: {
						type: 'hbox',
						align: 'left',
						pack: 'start'
					},
					items: [{
						ui: 'paging',
						iconCls: 'x-tbar-add',
						handler: function() {
							Scalr.Confirm({
								form: [{
									xtype: 'combo',
									name: 'ipProtocol',
									fieldLabel: 'Protocol',
									labelWidth: 120,
									editable: false,
									store: [ 'tcp', 'udp', 'icmp' ],
									value: 'tcp',
									queryMode: 'local',
									allowBlank: false
								}, {
									xtype: 'textfield',
									name: 'fromPort',
									fieldLabel: 'From port',
									labelWidth: 120,
									value: '1',
									allowBlank: false,
									validator: function (value) {
										if (value < -1 || value > 65535) {
											return 'Valid ports are - 1 through 65535';
										}
										return true;
									}
								}, {
									xtype: 'textfield',
									name: 'toPort',
									fieldLabel: 'To port',
									labelWidth: 120,
									value: '65535',
									allowBlank: false,
									validator: function (value) {
										if (value < -1 || value > 65535) {
											return 'Valid ports are - 1 through 65535';
										}
										return true;
									}
								}, {
									xtype: 'textfield',
									name: 'sg',
									fieldLabel: 'Security group',
									value: moduleParams['accountId']+"/default",
									labelWidth: 120,
									allowBlank: false
								}, {
									xtype: 'textfield',
									name: 'comment',
									fieldLabel: 'Comment',
									value: '',
									labelWidth: 120,
									allowBlank: true
								}],
								ok: 'Add',
								title: 'Add security rule',
								formValidate: true,
								closeOnSuccess: true,
								scope: this,
								success: function (formValues) {
									var view = this.up('#viewSg'), store = view.store;

									if (store.findBy(function (record) {
										if (
											record.get('ipProtocol') == formValues.ipProtocol &&
												record.get('fromPort') == formValues.fromPort &&
												record.get('toPort') == formValues.toPort &&
												record.get('sg') == formValues.sg
											) {
											Scalr.message.Error('Such rule exists');
											return true;
										}
									}) == -1) {
										store.add(formValues);
										return true;
									} else {
										return false;
									}
								}
							});
						}
					}]
				}]
			}],

		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			cls: 'x-docked-bottom-frame',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				xtype: 'button',
				text: 'Save',
				handler: function() {
					var data = [];
					Ext.each (form.down('#view').store.getRange(), function (item) {
						data.push(item.data);
					});
					
					var sgData = [];
					Ext.each (form.down('#viewSg').store.getRange(), function (item) {
						sgData.push(item.data);
					});
					
					Scalr.Request({
						processBox: {
							type: 'save'
						},
						url: '/security/groups/xSave/',
						params: Ext.applyIf(loadParams, {'rules': Ext.encode(data), 'sgRules': Ext.encode(sgData)}),
						success: function(){
							Scalr.event.fireEvent('refresh');
						}
					});
				}
			}, {
				xtype: 'button',
				margin: '0 0 0 5',
				text: 'Cancel',
				handler: function() {
					Scalr.event.fireEvent('close');
				}
			}]
		}]
	});

	form.down('#view').store.load({ data: moduleParams.rules });
	form.down('#viewSg').store.load({ data: moduleParams.sgRules });

	return form;
});
