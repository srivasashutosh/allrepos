Scalr.regPage('Scalr.ui.tools.aws.ec2.elb.details', function( loadParams, moduleParams ) {
	var elb = moduleParams['elb'];
	var healthCheck = elb['healthCheck'];
	var listenerDescription = elb['listenerDescriptions'];
	var instanceString = '';
	var availableZones = '';
	var policyFlag = true;

	if (elb['availabilityZones']) {
		availableZones = elb['availabilityZones'].join(', ');
	} else {
		availableZones = "There are no availability zones registered on this load balancer";
	}

	if (elb['instances']) {
		Ext.each(elb['instances'], function(item){
			instanceString += " <a href='#/tools/aws/ec2/elb/"
			               + elb['loadBalancerName'] + "/instanceHealth?awsInstanceId=" + item['instanceId']
			               + "&cloudLocation="+ loadParams[ 'cloudLocation' ] +"' "
			               + "style = 'cursor: pointer; text-decoration: none;'>" + item['instanceId'] + "</a>";
		});
	} else {
	    instanceString = "There are no instances registered on this load balancer";
	}

	var policyStore = Ext.create('Ext.data.JsonStore', {
		fields: [
			{ name: 'policyType' },
			{ name: 'policyName' },
			{ name: 'cookieSettings' }
		],
		data: elb['policies']
	});

	var comboStore = Ext.create('Ext.data.JsonStore', {
		fields: [ 'policyName', 'description' ],
		data: [ { policyName: '', description : 'Do not use session stickness on this ELB port' } ]
	});
	Ext.each( policyStore.getRange(), function(item){
		comboStore.add( { policyName: item.get('policyName'), description: item.get('policyName') } );
	});

	var listenerStore = Ext.create('Ext.data.JsonStore', {
		fields: [
			{ name: 'protocol' },
			{ name: 'loadBalancerPort' },
			{ name: 'instancePort' },
			{ name: 'policyNames' }
		]
	});
	Ext.each(listenerDescription, function( item ){
		item[ 'listener' ].policyNames = item.policyNames;
		listenerStore.add( item['listener'] );
		if (item['listener'].protocol == 'HTTP' || item['listener'].protocol == 'HTTPS')
			policyFlag = false;
	});

	var panel = Ext.create('Ext.Panel', {
		bodyCls: 'x-panel-body-frame',
		title: 'Details',
		items: [{
			xtype: 'fieldset',
			title: 'General',
			defaults: {
				labelWidth: 150,
				xtype: 'displayfield'
			},
			items: [{
				fieldLabel: 'Name',
				value: elb['loadBalancerName']
			}, {
				fieldLabel: 'DNS name',
				value: elb['dnsName']
			}, {
				fieldLabel: 'Created At',
				value: elb['createdTime'].date
			},{
				fieldLabel: 'Availability Zones',
				value: availableZones
			},{
				fieldLabel: 'Instances',
				value: instanceString
			}]
		},{
			xtype: 'fieldset',
			title: 'HealthCheck settings',
			defaults: {
				labelWidth: 150,
				xtype: 'displayfield'
			},
			items: [{
				fieldLabel: 'Interval',
				value: healthCheck['interval']
			},{
				fieldLabel: 'Target',
				value: healthCheck['target']
			},{
				fieldLabel: 'Healthy Threshold',
				value: healthCheck['healthyThreshold']
			},{
				fieldLabel: 'Timeout',
				value: healthCheck['timeout'] + ' seconds'
			},{
				fieldLabel: 'UnHealthy Threshold',
				value: healthCheck['unhealthyThreshold']
			}]
		},{
			xtype: 'panel',
			border: false,
			height: 400,
			itemId: 'panel',
			bodyCls: 'x-panel-body-frame',
			layout: {
				type: 'hbox',
				align: 'stretch'
			},
			items: [{
				xtype: 'gridpanel',
				store: listenerStore,
				plugins: {
					ptype: 'gridstore'
				},
				viewConfig: {
					deferEmptyText: false,
					emptyText: "No Listeners found"
				},
				title: 'Listeners',
				itemId: 'listenerGrid',
				flex: 1,
				columns: [{
					text: 'Protocol',
					dataIndex: 'protocol'
				},{
					flex: 1,
					text: 'LoadBalancer Port',
					sortable: false,
					dataIndex: 'loadBalancerPort'
				},{
					flex: 1,
					text: 'Instance Port',
					sortable: false,
					dataIndex: 'instancePort'
				},{
					text: 'Stickiness Policy',
					sortable: false,
					dataIndex: 'policyNames'
				},{
					xtype: 'optionscolumn',
					optionsMenu: [{
						itemId: "option.edit", iconCls: 'x-menu-icon-edit', text:'Settings',
						menuHandler: function (item) {
							Scalr.Request({
								confirmBox: {
									title: 'Create new parameter group',
									form: [{
										xtype: 'combo',
										name: 'policyName',
										store: comboStore,
										editable: false,
										fieldLabel: 'Location',
										queryMode: 'local',
										valueField: 'policyName',
										displayField: 'description',
										value: panel.down('#listenerGrid').getSelectionModel().getLastSelected().get('policyNames') || ''
									}]
								},
								processBox: {
									type: 'save'
								},
								scope: this,
								url: '/tools/aws/ec2/elb/'+ elb['loadBalancerName'] +'/xAssociateSp/',
								params: {
									cloudLocation: loadParams['cloudLocation'],
									elbPort: item.record.get('loadBalancerPort')
								},
								success: function (data, response, options){
									var rowIndex = listenerStore.find('loadBalancerPort', options.params.elbPort);
									listenerStore.getAt(rowIndex).set('policyNames', options.params.policyName || '');
								}
							});
						}
					},{
						itemId: "option.delete", iconCls: 'x-menu-icon-delete', text:'Delete',
						request: {
							confirmBox: {
								msg: 'Remove Listener?',
								type: 'delete'
							},
							processBox: {
								type: 'delete',
								msg: 'Removing Listener ...'
							},
							dataHandler: function (record) {
								this.currentRecord = record;
								var data = {
									lbPort: record.get('loadBalancerPort'),
									cloudLocation: loadParams['cloudLocation']
								};
								this.url = '/tools/aws/ec2/elb/'+ elb['loadBalancerName'] +'/xDeleteListeners/';
								return data;
							},
							success: function (data, response, options) {
								listenerStore.remove(options.currentRecord);
								if (! policyFlag) {
									var flag = true;
									for(i = 0; i < listenerStore.data.length; i++){
										if(listenerStore.getAt(i).get('protocol') == "HTTP" || listenerStore.getAt(i).get('protocol') == "HTTPS") {
											flag = false;
											break;
										}
									}
									if (flag) {
										policyFlag = true;
										panel.down('#policyGrid').hide().disable();
									}
								}
							}
						}
					}],
					getOptionVisibility: function (item, record) {
						if (item.itemId == 'option.delete')
							return true;
						if (item.itemId == 'option.edit') {
							if(record.get('protocol') == 'TCP' || record.get('protocol') == 'SSL'){
								return false;
							}
							else return true;
						}
					}
				}],
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'top',
					layout: {
						type: 'hbox',
						pack: 'start'
					},
					items: [{
						ui: 'paging',
						iconCls: 'x-tbar-add',
						handler: function() {
							Scalr.Request({
								confirmBox: {
									title: 'Add new Listener',
									form: [{
										xtype: 'hiddenfield',
										name: 'cloudLocation',
										value: loadParams['cloudLocation']
									},{
										xtype: 'hiddenfield',
										name: 'elbName',
										value: elb['loadBalancerName']
									},{
										xtype: 'combo',
										itemId: 'test',
										name: 'protocol',
										fieldLabel: 'protocol',
										labelWidth: 120,
										editable: false,
										store: [ 'TCP', 'HTTP', 'SSL', 'HTTPS' ],
										queryMode: 'local',
										allowBlank: false,
										listeners: {
											change: function (field, value) {
												if (value == 'SSL' || value == 'HTTPS')
													this.next('[name="certificateId"]').show().enable();
												else
													this.next('[name="certificateId"]').hide().disable();
											}
										}
									},{
										xtype: 'textfield',
										name: 'lbPort',
										fieldLabel: 'Load balancer port',
										labelWidth: 120,
										allowBlank: false,
										validator: function (value) {
											if (value < 1024 || value > 65535) {
												if (value != 80 && value != 443)
													return 'Valid LoadBalancer ports are - 80, 443 and 1024 through 65535';
											}
											return true;
										}
									},{
										xtype: 'textfield',
										name: 'instancePort',
										fieldLabel: 'Instance port',
										labelWidth: 120,
										allowBlank: false,
										validator: function (value) {
											if (value < 1 || value > 65535)
												return 'Valid instance ports are one (1) through 65535';
											else
												return true;
										}
									},{
										xtype: 'combo',
										name: 'certificateId',
										fieldLabel: 'SSL Certificate',
										labelWidth: 120,
										hidden: true,
										disabled: true,
										editable: false,
										allowBlank: false,
										store: {
											fields: [ 'name','path','arn','id','upload_date' ],
											proxy: {
												type: 'ajax',
												reader: {
													type: 'json',
													root: 'data'
												},
												url: '/tools/aws/iam/servercertificates/xListCertificates/'
											}
										},
										valueField: 'arn',
										displayField: 'name'
									}],
									ok: 'Add'
								},
								processBox: {
									msg: 'Adding new Listener ...',
									type: 'save'
								},
								url: '/tools/aws/ec2/elb/'+ elb['loadBalancerName'] +'/xCreateListeners/',
								scope: this,
								success: function (data, response, options){
									listenerStore.add({
									    protocol: options.params.protocol,
									    loadBalancerPort: options.params.lbPort,
									    instancePort: options.params.instancePort
								    });
									if (policyFlag) {
										if(options.params.protocol == "HTTP" || options.params.protocol == "HTTPS"){
											policyFlag = false;
											this.up('#panel').down('#policyGrid').show().enable();
										}
									}
								}
							});
						}
					}]
				}]
			},{
				xtype: 'gridpanel',
				itemId: 'policyGrid',
				plugins: {
					ptype: 'gridstore'
				},
				viewConfig: {
					deferEmptyText: false,
					emptyText: 'No Stickiness Policies found'
				},
				margin: '0 0 0 3',
				title: 'Stickiness Policies',
				flex: 1,
				columns: [{
					text: 'Type',
					dataIndex: 'policyType'
				},{
					flex: 2,
					text: 'Name',
					dataIndex: 'policyName'
				},{
					flex: 2,
					text: 'Cookie name / Exp. period',
					sortable: false,
					dataIndex: 'cookieSettings'
				},{
					xtype: 'optionscolumn',
					optionsMenu: [{
						itemId: "option.delete", iconCls: 'x-menu-icon-delete', text:'Delete',
						request: {
							confirmBox: {
								msg: 'Remove Stickiness Policy?',
								type: 'delete'
							},
							processBox: {
								type: 'delete',
								msg: 'Removing Stickiness Policy ...'
							},
							dataHandler: function (record) {
								this.currentRecord = record;
								var data = {
									policyName: record.get('policyName'),
									cloudLocation: loadParams['cloudLocation'],
									elbName: elb['loadBalancerName']
								};
								this.url = '/tools/aws/ec2/elb/'+ elb['loadBalancerName'] +'/xDeleteSp/';
								return data;
							},
							success: function (data, response, options ) {
								policyStore.remove(options.currentRecord);
								comboStore.remove(comboStore.getAt(comboStore.find('policyName', options.currentRecord.get('policyName'))));
							}
						}
					}]
				}],
				disabled: policyFlag,
				store: policyStore,
				dockedItems: [{
					xtype: 'toolbar',
					dock: 'top',
					layout: {
						type: 'hbox',
						pack: 'start'
					},
					items: [{
						ui: 'paging',
						iconCls: 'x-tbar-add',
						handler: function() {
							Scalr.Request({
								confirmBox: {
									title: 'Create Stickiness Policies',
									form: [{
										xtype: 'hiddenfield',
										name: 'cloudLocation',
										value: loadParams['cloudLocation']
									},{
										xtype: 'hiddenfield',
										name: 'elbName',
										value: elb['loadBalancerName']
									},{
										xtype: 'combo',
										itemId: 'polis',
										name: 'policyType',
										editable: false,
										fieldLabel: 'Cookie Type',
										queryMode: 'local',
										store: [ ['AppCookie','App cookie'], ['LbCookie','Lb cookie'] ],
										value: 'AppCookie',
										listeners: {
											change: function (field, value){
												var nextContainer = this.next('container');
												if(value == "LbCookie"){
													nextContainer.down('[name="cookieSettings"]').labelEl.update("Exp. period:");
													nextContainer.down('[name="Sec"]').show();
												}
												else{
													nextContainer.down('[name="cookieSettings"]').labelEl.update("Cookie Name:");
													nextContainer.down('[name="Sec"]').hide();
												}
											}
										}
									},{
										xtype: 'textfield',
										name: 'policyName',
										fieldLabel: 'Name',
										allowBlank: false
									},{
										xtype: 'container',
										layout: {
											type: 'hbox'
										},
										items:[{
											xtype: 'textfield',
											name: 'cookieSettings',
											fieldLabel: 'Cookie Name',
											allowBlank: false,
											labelWidth: 100,
											width: 365
										},{
											margin: '0 0 0 2',
											xtype: 'displayfield',
											name: 'Sec',
											value: 'sec',
											hidden: true
										}]
									}],
									formValidate: true
								},
								scope: this,
								processBox: {
									type: 'save'
								},
								url: '/tools/aws/ec2/elb/'+ elb['loadBalancerName'] +'/xCreateSp/',
								success: function (data, response, options) {
									policyStore.add({
									    policyType: options.params.policyType,
									    policyName: options.params.policyName,
									    cookieSettings: options.params.cookieSettings
								    });
									comboStore.add({
									    policyName: options.params.policyName,
									    description: options.params.policyName
								    });
								}
							});
						}
					}]
				}]
			}]
		}]
	});
	return panel;
});