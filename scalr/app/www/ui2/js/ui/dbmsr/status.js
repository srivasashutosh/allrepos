Scalr.regPage('Scalr.ui.dbmsr.status', function (loadParams, moduleParams) {
	
	var storageSize = function () {
		var total = this.size['total'], used = this.size['used'];
		var color = 'green';
		var percentUsed = Math.ceil(100/total*used);
		
		if (total != -1) {
			if (percentUsed > 90)
				color = 'red';
			else if (percentUsed > 70)
				color = 'yellow';
		}

		this.setValue("<span style='color:green;'>" + ((used == -1) ? "Unknown" : used) + "</span> of "+ ((total == -1) ? "Unknown" : total) + " GB");

		this.inputEl.applyStyles("padding-bottom: 3px; padding-left: 5px");
		this.el.applyStyles("background: -webkit-gradient(linear, left top, left bottom, from(#C8D6E5), to(#DAE5F4));");
		this.el.applyStyles("background: -moz-linear-gradient(top, #C8D6E5, #DAE5F4);");

		if (color == 'red') {
			this.bodyEl.applyStyles("background: -webkit-gradient(linear, left top, left bottom, from(#F4CDCC), to(#E78B84))");
			this.bodyEl.applyStyles("background: -moz-linear-gradient(top, #F4CDCC, #E78B84)");
		} else if (color == 'yellow') {
			this.bodyEl.applyStyles("background: -webkit-gradient(linear, left top, left bottom, from(#FCFACB), to(#F3C472))");
			this.bodyEl.applyStyles("background: -moz-linear-gradient(top, #FCFACB, #F3C472)");
		} else {
			this.bodyEl.applyStyles("background: -webkit-gradient(linear, left top, left bottom, from(#C5E1D9), to(#96CFAF))");
			this.bodyEl.applyStyles("background: -moz-linear-gradient(top, #C5E1D9, #96CFAF)");
		}
		if (total != -1) {
			this.bodyEl.applyStyles("background-size: " + Math.ceil(used * 100 / total) + "% 100%; background-repeat: no-repeat");
		}
	};
	
	var generalItems = [{
		xtype: 'displayfield',
		name: 'email',
		fieldLabel: 'Database type',
		readOnly: true,
		value: moduleParams['name']
	}];
	
	for (k in moduleParams['additionalInfo'])
	{
		generalItems[generalItems.length] = {
			xtype: 'displayfield',
			name: k,
			fieldLabel: k,
			readOnly: true,
			value: moduleParams['additionalInfo'][k]
		};
	}
	
	if (moduleParams['dbType'] != 'mysql') {
		generalItems[generalItems.length] = {
			xtype: 'button',
			itemId: 'manageConfiguration',
			text: 'Manage configuration',
			flex: 1,
			handler: function(){
				Scalr.event.fireEvent('redirect', '#/services/configurations/manage?farmRoleId=' + moduleParams['farmRoleId'] + '&behavior=' + moduleParams['dbType']);
			}
		};
	}
	
	if (moduleParams['bundleOperationId']) {
		if (moduleParams['dtLastBundle'] != 'Never')
			var dataBundleStatus = '<a href="#/operations/' + moduleParams['bundleOperationId'] + '/details">' + ((moduleParams['isBundleRunning'] == 1) ? 'In progress...' : moduleParams['dtLastBundle']) + "</a>";
		else
			var dataBundleStatus = 'Never';
	} else {
		var dataBundleStatus = (moduleParams['isBundleRunning'] == 1) ? 'In progress...' : moduleParams['dtLastBundle'];
	}
	
	if (moduleParams['backupOperationId']) {
		if (moduleParams['dtLastBackup'] != 'Never')
			var backupStatus = '<a href="#/operations/' + moduleParams['backupOperationId'] + '/details">' + ((moduleParams['isBackupRunning'] == 1) ? 'In progress...' : moduleParams['dtLastBackup']) + "</a>";
		else
			var backupStatus = 'Never';
	} else {
		var backupStatus = (moduleParams['isBackupRunning'] == 1) ? 'In progress...' : moduleParams['dtLastBackup'];
	}
	
	if ((moduleParams['dbType'] == 'percona' || moduleParams['dbType'] == 'mysql2') && (moduleParams['storage'] && moduleParams['storage']['engine'] == 'lvm')) {
		var confirmationDataBundleOptions = {
			xtype: 'fieldset',
			title: 'Data bundle settings',
			items: [{
				xtype: 'combo',
				fieldLabel: 'Type',
				store: [['incremental', 'Incremental'], ['full', 'Full']],
				valueField: 'id',
				displayField: 'name',
				editable: false,
				queryMode: 'local',
				value: 'incremental',
				name: 'bundleType',
				labelWidth: 80,
				width: 500
			}, {
				xtype: 'combo',
				fieldLabel: 'Compression',
				store: [['', 'No compression (Recommended on small instances)'], ['gzip', 'gzip (Recommended on large instances)']],
				valueField: 'id',
				displayField: 'name',
				editable: false,
				queryMode: 'local',
				value: 'gzip',
				name: 'compressor',
				labelWidth: 80,
				width: 500
			}, {
				xtype: 'checkbox',
				hideLabel: true,
				name: 'useSlave',
				boxLabel: 'Use SLAVE server for data bundle'
			}]
		};
	} else {
		
		if (moduleParams['dbType'] == 'percona' || moduleParams['dbType'] == 'mysql2') {
			var confirmationDataBundleOptions = {
				xtype: 'fieldset',
				title: 'Data bundle settings',
				items: [{
					xtype: 'checkbox',
					hideLabel: true,
					name: 'useSlave',
					boxLabel: 'Use SLAVE server for data bundle'
				}]
			};
		} else {
			var confirmationDataBundleOptions = {};
		}
	}
	
	var panel = Ext.create('Ext.form.Panel', {
		width: 900,
		title: 'Database status',
		bodyCls: 'x-panel-body-frame',
		fieldDefaults: {
			anchor: '100%',
			labelWidth: 150
		},
		items: [{
			xtype: 'container',
			layout: {
				type: 'hbox',
				aligin: 'stretchmax'
			},
			cls: 'x-container-form-item',
			hideLabel: true,
			items: [{
				xtype: 'fieldset',
				flex: 1,
				defaults: {
					labelWidth: 130
				},
				title: 'General information',
				items: generalItems
			}, {
				xtype: 'fieldset',
				title: 'Master storage',
				hidden: !moduleParams['storage'],
				flex: 1,
				defaults: {
					labelWidth: 110
				},
				margin: '0 0 0 10',
				items: [{
					xtype: 'displayfield',
					fieldLabel: 'Engine',
					value: (moduleParams['storage']) ? moduleParams['storage']['engineName'] : ""
				}, {
					xtype: 'displayfield',
					fieldLabel: 'ID',
					hidden: !moduleParams['storage'] || !moduleParams['storage']['id'],
					value: (moduleParams['storage']) ? moduleParams['storage']['id'] : ""
				}, {
					xtype: 'displayfield',
					fieldLabel: 'File system',
					hidden: !moduleParams['storage'] || !!moduleParams['storage']['fs'],
					value: (moduleParams['storage']) ? moduleParams['storage']['fs'] : ""
				}, {
					xtype: 'fieldcontainer',
					fieldLabel: 'Usage',
					layout: 'hbox',
					items: [{
						xtype: 'displayfield',
						width: 180,
						size: (moduleParams['storage']) ? moduleParams['storage']['size'] : {},
						listeners: {
							boxready: storageSize
						}
					}]
				}, {
					xtype: 'button',
					itemId: 'increaseStorageSize',
					text: 'Increase storage size',
					hidden: !(moduleParams['storage'] && (moduleParams['storage']['engine'] == 'ebs' || moduleParams['storage']['engine'] == 'raid.ebs') && (moduleParams['dbType'] == 'percona' || moduleParams['dbType'] == 'mysql2')),
					flex: 1
				}]
			}]
		}, {
			xtype: 'fieldset',
			title: 'Connection endpoints',
			collapsible: true,
			collapsed: true,
			hidden: !moduleParams['staticDnsSupported'],
			items: [{
				xtype: 'displayfield',
				fieldCls: 'x-form-field-info',
				value: 'Public - To connect to the service from the Internet<br / >Private - To connect to the service from another instance'
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Writes endpoint (Public)',
				value: 'ext.master.' + moduleParams['dbType'] + '.' + moduleParams['farmHash'] + '.scalr-dns.net'
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Reads endpoint (Public)',
				value: 'ext.slave.' + moduleParams['dbType'] + '.' + moduleParams['farmHash'] + '.scalr-dns.net'
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Writes endpoint (Private)',
				value: 'int.master.' + moduleParams['dbType'] + '.' + moduleParams['farmHash'] + '.scalr-dns.net'
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Reads endpoint (Private)',
				value: 'int.slave.' + moduleParams['dbType'] + '.' + moduleParams['farmHash'] + '.scalr-dns.net'
			}]
		}, {
			xtype: 'fieldset',
			title: 'PHPMyAdmin access',
			hidden: (moduleParams['dbType'] != 'mysql' && moduleParams['dbType'] != 'mysql2' && moduleParams['dbType'] != 'percona'),
			items: [{
				xtype: 'button',
				name: 'setupPMA',
				hidden: (moduleParams['pmaAccessConfigured'] || moduleParams['pmaAccessSetupInProgress']),
				text: 'Setup PHPMyAdmin access',
				handler: function(){
					Scalr.Request({
						processBox: {
							type: 'action'
						},
						url: '/dbmsr/xSetupPmaAccess/',
						params: {farmId: loadParams['farmId'], farmRoleId: moduleParams['farmRoleId']},
						success: function(){
							panel.down('[name="setupPMA"]').hide();
							panel.down('[name="PMAinProgress"]').show();
						}
					});
				}
			}, {
				xtype: 'button',
				name: 'launchPMA',
				margin: '0 0 0 5',
				hidden: (!moduleParams['pmaAccessConfigured']),
				text: 'Launch PHPMyAdmin',
				handler: function() {
					Scalr.event.fireEvent('redirect', '#/services/mysql/pma?farmId=' + loadParams['farmId']);
				}
			}, {
				xtype: 'button',
				name: 'resetPMA',
				margin: '0 0 0 5',
				hidden:(!moduleParams['pmaAccessConfigured']),
				text: 'Reset PHPMyAdmin credentials',
				handler: function(){
					Scalr.Request({
						confirmBox: {
							type: 'action',
							msg: 'Are you sure want to reset PMA access?'
						},
						processBox: {
							type: 'action'
						},
						url: '/dbmsr/xSetupPmaAccess/',
						params: {farmId: loadParams['farmId'], farmRoleId: moduleParams['farmRoleId']},
						success: function(){
							panel.down('[name="setupPMA"]').hide();
							panel.down('[name="launchPMA"]').hide();
							panel.down('[name="resetPMA"]').hide();
							panel.down('[name="PMAinProgress"]').show();
						}
					});
				}
			},{
				xtype: 'displayfield',
				width: 500,
				hidden: (!moduleParams['pmaAccessSetupInProgress']),
				name: 'PMAinProgress',
				value: 'MySQL access details for PMA requested. Please refresh this page in a couple minutes...'
			}]
		}, {
			xtype: 'fieldset',
			title: 'Backups &amp; Data Bundles',
			items: [{
				xtype: 'fieldcontainer',
				fieldLabel: 'Last backup',
				hidden: moduleParams['backupsNotSupported'],
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					width: 150,
					name: 'backupStatus',
					value: backupStatus
				} , {
					xtype: 'button',
					margin: '0 0 0 5',
					text: 'Create backup',
					handler: function(){
						Scalr.Request({
							confirmBox: {
								type: 'action',
								msg: 'Are you sure want to create backup?'
							},
							processBox: {
								type: 'action',
								msg: 'Sending backup request ...'
							},
							url: '/dbmsr/xCreateBackup/',
							params: {farmId: loadParams['farmId'], farmRoleId: moduleParams['farmRoleId']},
							success: function(){
								Scalr.event.fireEvent('refresh');
							}
						});
					}
				}, {
					xtype: 'button',
					margin: '0 0 0 5',
					text: 'Cancel backup',
					style: 'color:red;',
					hidden: !(moduleParams['isBackupRunning']),
					handler: function(){
						Scalr.Request({
							confirmBox: {
								type: 'action',
								msg: 'Are you sure want to cancel running backup?',
							},
							processBox: {
								type: 'action',
								msg: 'Sending backup cancel request ...'
							},
							url: '/dbmsr/xCancelBackup/',
							params: {farmId: loadParams['farmId'], farmRoleId: moduleParams['farmRoleId']},
							success: function(){
								Scalr.event.fireEvent('refresh');
							}
						});
					}
				}, {
					xtype: 'button',
					margin: '0 0 0 5',
					text: 'Manage backups',
					listeners: {
						click:function(){
							Scalr.event.fireEvent('redirect', '#/db/backups?farmId='+loadParams['farmId']);
						}
					}
				}]
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: 'Last data bundle',
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					name: 'dataBundleStatus',
					width: 150,
					value: dataBundleStatus
				}, {
					xtype: 'button',
					margin: '0 0 0 5',
					text: 'Create data bundle',
					disabled: (moduleParams['isBundleRunning'] == 1),
					handler: function(){
						Scalr.Request({
							confirmBox: {
								type: 'action',
								msg: 'Create data bundle?',
								formWidth: 600,
								form: confirmationDataBundleOptions
							},
							processBox: {
								type: 'action',
								msg: 'Sending data bundle request ...'
							},
							url: '/dbmsr/xCreateDataBundle/',
							params: {farmId: loadParams['farmId'], farmRoleId: moduleParams['farmRoleId']},
							success: function(){
								Scalr.event.fireEvent('refresh');
							}
						});
					}
				}, {
					xtype: 'button',
					margin: '0 0 0 5',
					text: 'Cancel data bundle',
					style: 'color:red;',
					hidden: !(moduleParams['storage'] && moduleParams['storage']['engine'] == 'lvm' && moduleParams['isBundleRunning'] == 1),
					handler: function(){
						Scalr.Request({
							confirmBox: {
								type: 'action',
								msg: 'Are you sure want to cancel data bundle?',
							},
							processBox: {
								type: 'action',
								msg: 'Sending data bundle cancel request ...'
							},
							url: '/dbmsr/xCancelDataBundle/',
							params: {farmId: loadParams['farmId'], farmRoleId: moduleParams['farmRoleId']},
							success: function(){
								Scalr.event.fireEvent('refresh');
							}
						});
					}
				}]
			}, {
				hidden: !moduleParams['noDataBundleForSlaves'],
				xtype: 'displayfield',
				fieldCls: 'x-form-field-warning',
				value: 'Data bundle was created on OLD master and cannot be used to launch new slaves.<br />Please create new data bundle to be able to launch slaves.'
			}]
		}],
		tools: [{
			type: 'refresh',
			handler: function () {
				Scalr.event.fireEvent('refresh');
			}
		}, {
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent('close');
			}
		}]
	});

	Ext.each(moduleParams['replicationStatus'], function(item){
		var items = [{
				xtype: 'displayfield',
				fieldLabel: 'Remote IP',
				labelWidth: 200,
				value: item['remoteIp']
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Local IP',
				labelWidth: 200,
				value: item['localIp']
			}];

		if (item['error']) {
			items[items.length] = {
				xtype: 'displayfield',
				hideLabel: true,
				fieldStyle: {
					color: 'red'
				},
				value: item['error']
			};
		}
		else {
			if (moduleParams['dbType'] == 'mysql' || moduleParams['dbType'] == 'mysql2' || moduleParams['dbType'] == 'percona') {
				if (item['replicationRole'] == 'Master') {
					items.push({
						xtype: 'displayfield',
						fieldLabel: 'Binary log position',
						labelWidth: 200,
						fieldStyle: {
							color: 'green'
						},
						value: "<img style='float:left; margin-right: 5px;' src='/ui2/images/icons/true.png'> " + item['masterPosition']
					});
				}
				else {
					if (! item['data'])
						item['data'] = {};

					items.push({
						xtype: 'displayfield',
						fieldLabel: 'Slave_IO_Running',
						labelWidth: 200,
						fieldStyle: {
							color: (item['data']['Slave_IO_Running'] == 'Yes' ? 'green' : 'red')
						},
						value: "<img style='float:left; margin-right: 5px;' src='/ui2/images/icons/" + (item['data']['Slave_IO_Running'] == 'Yes' ? 'true.png' : 'delete_icon_16x16.png') + "'> " + item['data']['Slave_IO_Running']
					});

					items.push({
						xtype: 'displayfield',
						fieldLabel: 'Slave_SQL_Running',
						labelWidth: 200,
						fieldStyle: {
							color: (item['data']['Slave_IO_Running'] == 'Yes' ? 'green' : 'red')
						},
						value: "<img style='float:left; margin-right: 5px;' src='/ui2/images/icons/" + (item['data']['Slave_SQL_Running'] == 'Yes' ? 'true.png' : 'delete_icon_16x16.png') + "'> " + item['data']['Slave_SQL_Running']
					});

					items.push({
						xtype: 'displayfield',
						fieldLabel: 'Seconds_Behind_Master',
						labelWidth: 200,
						fieldStyle: {
							color: (item['data']['Slave_IO_Running'] == 'Yes' ? 'green' : 'red')
						},
						value: "<img style='float:left; margin-right: 5px;' src='/ui2/images/icons/" + (item['data']['Seconds_Behind_Master'] == 0 ? 'true.png' : 'delete_icon_16x16.png') + "'> " + item['data']['Seconds_Behind_Master']
					});
				}
			}

			if (item['data'] && item['data'].length > 0) {
				for (key in item['data']) {

					if (key == 'Position' || key == 'Slave_IO_Running' || key == 'Slave_SQL_Running' || key == 'Seconds_Behind_Master')
						continue;

					if (item['data'][key] == '')
						continue;

					items[items.length] = {
						xtype: 'displayfield',
						fieldLabel: key,
						labelWidth: 200,
						value: item['data'][key]
					};
				}
			}
		}

		panel.add({
			xtype: 'fieldset',
			title: item['replicationRole']+": <a href='#/servers/"+item['serverId']+"/extendedInfo'>"+item['serverId']+"</a>",
			items: items
		});
	});

	panel.down('#increaseStorageSize').on('click', function(){
		Scalr.Request({
			confirmBox: {
				type: 'action',
				formWidth: 700,
				msg: 'Are you sure want to increase storage size?',
				form: [{
					xtype: 'displayfield',
					fieldCls: 'x-form-field-warning',
					value: '<span style="color:red;">Attention! This operation will cause downtime on master server.</span><br />During creation and replacement of new storage, master server won\'t be able to accept any connections.'
				}, {
					xtype: 'fieldcontainer',
					hideLabel: true,
					layout: 'hbox',
					items: [{
						xtype: 'displayfield',
						value: 'New size (GB):',
						hideLabel: true
					}, {
						xtype: 'textfield',
						width: 50,
						hideLabel: true,
						margin: '0 0 0 5',
						name: 'newSize',
						value: (parseInt(moduleParams['storage']['size']['total'])+1)*2
					}, {
						xtype: 'displayfield',
						margin: '0 0 0 5',
						value: ' GB',
						hideLabel: true
					}]
				}]
			},
			processBox: {
				type: 'action',
				msg: 'Updating scalarizr ...'
			},
			url: '/dbmsr/xGrowStorage/',
			params: {farmRoleId: moduleParams['farmRoleId']},
			success: function(data){
				Scalr.message.Success("Storage grow successfully initiated");
				Scalr.event.fireEvent('redirect', '#/operations/' + data['operationId'] + '/details');
			}
		});
	});

	return panel;
});
