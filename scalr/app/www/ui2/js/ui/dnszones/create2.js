Scalr.regPage('Scalr.ui.dnszones.create2', function (loadParams, moduleParams) {
	var zone = moduleParams['zone'], 
		ownRecords = [],
		systemRecords = [];
		
	Ext.each(moduleParams['records'] || [], function(item) {
			if (item.issystem == '1') {
				systemRecords.push(item);
			} else {
				ownRecords.push(item);
			}
	});

	var storeOwnRecords = Ext.create('store.store', {
		filterOnLoad: true,
		sortOnLoad: true,
		fields: [
			'issystem', 'name', 'port', 'priority', 'server_id', {name: 'ttl', type: 'string'}, 'type', 'value', 'weight', 'zone_id', 'isnew'
		],
		data: ownRecords,
		sorters: [{
			property: 'name',
			transform: function(value){
				return value.toLowerCase();
			}
		}]
	});
	
	var storeSystemRecords = Ext.create('store.store', {
		filterOnLoad: true,
		sortOnLoad: true,
		fields: [
			'issystem', 'name', 'port', 'priority', 'server_id', {name: 'ttl', type: 'string'}, 'type', 'value', 'weight', 'zone_id', 'isnew'
		],
		data: systemRecords,
		sorters: [{
			property: 'name',
			transform: function(value){
				return value.toLowerCase();
			}
		}]
	});

	
	
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame scalr-ui-dnszone-form',
		title: (zone['domainId'] || 0) ? 'DNS Zones &raquo; Edit' : 'DNS Zones &raquo; Create',
		scalrOptions: {
			'reload': true,
			'maximize': 'all'
		},
		//width: 1250,
		layout: {
			type: 'hbox',
			pack: 'start',
			align: 'stretch'
		},
		fieldDefaults: {
			anchor: '100%'
		},
		items: [{
			layout: {
				type: 'vbox',
				pack: 'start',
				align: 'stretch'
			},
			minHeight: 480,
			width: 380,
			items: [{
				xtype: 'fieldset',
				title: 'Domain name',
				items: [{
					xtype: 'fieldcontainer',
					layout: 'hbox',
					items: [{
						xtype: 'buttongroupfield',
						value: zone['domainType'],
						name: 'domainType',
						hidden: moduleParams['action'] == 'create' ? false : true,
						width: 290,
						items: [{
							value: 'scalr',
							text: 'Auto',
							width:80,
							margin: '0 0 0 3'
						},{
							value: 'own',
							text: 'Custom',
							width:80
						}],
						listeners: {
							change: function () {
								var field = form.query('textfield[name="domainName"]')[0];
								if (this.getValue() == 'own') {
									field.enable();
									field.setValue('');
									field.focus(false, 200);
								} else {
									field.disable();
									field.setValue(zone['domainName']);
								}
							}
						}
					}, {
						xtype: 'displayinfofield',
						hidden: moduleParams['action'] == 'create' ? false : true,
						value: 'Choose <b>&quot;Auto&quot;</b> to use domain name automatically generated and provided by Scalr.<br/> Choose <b>&quot;Custom&quot;</b> to use your own domain name.',
						width: 300,
						margin: '0 0 0 5'
					}]
				}, {
					xtype: 'displayfield',
					width: 10,
					hidden: moduleParams['action'] == 'create' ? false : true
				},{

					xtype: 'textfield',
					name: 'domainName',
					disabled: zone['domainType'] == 'scalr' ? true : false,
					value: zone['domainName'],
					hidden: moduleParams['action'] == 'create' ? false : true
				}, {
					xtype: 'displayfield',
					cls: 'x-form-check-wrap',
					value: zone['domainName'],
					hidden: moduleParams['action'] == 'edit' ? false : true
				}]
			}, {
				xtype: 'fieldset',
				title: 'Automatically create A records for',
				items: [{
					xtype: 'fieldcontainer',
					fieldLabel: 'Farm',
					labelWidth: 80,
					layout: 'hbox',
					items: [{
						xtype: 'combo',
						store: {
							fields: [ 'id', 'name' ],
							data: moduleParams['farms'],
							proxy: 'object'
						},
						queryMode: 'local',
						editable: false,
						name: 'domainFarm',
						value: zone['domainFarm'] != '0' ? zone['domainFarm'] : '',
						valueField: 'id',
						displayField: 'name',
						width: 210,
						listeners: {
							change: function (field, value) {
								if (value) {
									Scalr.Request({
										processBox: {
											type: 'load',
											msg: 'Loading farm roles ...'
										},
										url: '/dnszones/xGetFarmRoles/',
										params: { farmId: value },
										success: function (data) {
											form.down('[name="domainFarmRole"]').setValue('');
											form.down('[name="domainFarmRole"]').store.load({ data: data.farmRoles });
										}
									});
								} else {
									form.query('combobox[name="domainFarmRole"]')[0].store.loadData([]);
									form.query('combobox[name="domainFarmRole"]')[0].setValue('');
								}
							}
						}
					}, {
						xtype: 'displayinfofield',
						margin: '0 0 0 5',
						value: 'Each server in this farm will add int-rolename ext-rolename records. Leave blank if you don`t need such records.'
					}]
				}, {
					xtype: 'fieldcontainer',
					fieldLabel: 'Role',
					labelWidth: 80,
					layout: 'hbox',
					items: [{
						xtype: 'combo',
						store: {
							fields: [ 'id', 'name', 'platform', 'role_id' ],
							data: moduleParams['farmRoles'],
							proxy: 'object'
						},
						queryMode: 'local',
						editable: false,
						name: 'domainFarmRole',
						value: zone['domainFarmRole'] != '0' ? zone['domainFarmRole'] : '',
						valueField: 'id',
						displayField: 'name',
						width: 210
					}, {
						xtype: 'displayinfofield',
						value: 'Servers of this role will create root records. Leave blank to add root records manually.',
						margin: '0 0 0 5'
					}]
				}]
			}, {
				xtype: 'fieldset',
				flex: 1,
				title: 'SOA settings',
				items: [{
					xtype: 'fieldcontainer',
					fieldLabel: 'SOA Owner',
					labelWidth: 80,
					layout: 'hbox',
					items: [{
						xtype: 'textfield',
						name: 'soaOwner',
						width: 210,
						value: zone['soaOwner']
					}, {
						xtype: 'displayinfofield',
						margin: '0 0 0 5',
						info: 'Email address of the person responsible for this zone and to which email may be sent to report errors or problems. In the jargon this is called the RNAME field which is why we called it email-addr. The email address of a suitable DNS admin but more commonly the technical contact for the domain. By convention (in RFC 2142) it is suggested that the reserved mailbox hostmaster be used for this purpose but any sensible and stable email address will work. NOTE: Format is mailbox-name.domain.com, for example, hostmaster.example.com (uses a dot not the more normal @ sign, since @ has other uses in the zone file) but mail is sent to hostmaster@example.com. Most commonly ending with a &quot;.&quot; (dot) but if the email address lies within this domain you can just use hostmaster (see also example below).'
					}]
				}, {
					xtype: 'fieldcontainer',
					fieldLabel: 'SOA Retry',
					labelWidth: 80,
					layout: 'hbox',
					items: [{
						xtype: 'combo',
						store: [['300', '5 minutes'], ['900', '15 minutes'], [ '1800', '30 minutes' ], [ '3600', '1 hour' ], [ '7200', '2 hours' ], [ '14400', '4 hours' ], [ '28800', '8 hours' ], [ '86400', '1 day' ]],
						editable: false,
						name: 'soaRetry',
						width: 210,
						value: zone['soaRetry']
					}, {
						xtype: 'displayinfofield',
						margin: '0 0 0 5',
						info: 'Signed 32 bit value in seconds. Defines the time between retries if the slave (secondary) fails to contact the master when refresh (above) has expired. Typical values would be 180 (3 minutes) to 900 (15 minutes) or higher.'
					}]
				}, {
					xtype: 'fieldcontainer',
					fieldLabel: 'SOA refresh',
					labelWidth: 80,
					layout: 'hbox',
					items: [{
						xtype: 'combo',
						store: [[ '3600', '1 hour' ], [ '7200', '2 hours' ], [ '14400', '4 hours' ], [ '28800', '8 hours' ], [ '86400', '1 day' ]],
						editable: false,
						name: 'soaRefresh',
						width: 210,
						value: zone['soaRefresh']
					}, {
						xtype: 'displayinfofield',
						margin: '0 0 0 5',
						info: 'Signed 32 bit value in seconds. Indicates when the zone data is no longer authoritative. Used by Slave or (Secondary) servers only. BIND9 slaves stop responding to queries for the zone when this time has expired and no contact has been made with the master. Thus every time the refresh values expires the slave will attempt to read the SOA record from the zone master - and request a zone transfer AXFR/IXFR if sn is HIGHER. If contact is made the expiry and refresh values are reset and the cycle starts again. If the slave fails to contact the master it will retry every retry period but continue to supply authoritative data for the zone until the expiry value is reached at which point it will stop answering queries for the domain. RFC 1912 recommends 1209600 to 2419200 seconds (2-4 weeks) to allow for major outages of the zone master.'
					}]
				}, {
					xtype: 'fieldcontainer',
					fieldLabel: 'SOA expire',
					labelWidth: 80,
					layout: 'hbox',
					items: [{
						xtype: 'combo',
						store: [[ '86400', '1 day' ], [ '259200', '3 days' ], [ '432000', '5 days' ], [ '604800', '1 week' ], [ '3024000', '5 weeks' ], [ '6048000', '10 weeks' ] ],
						editable: false,
						name: 'soaExpire',
						width: 210,
						value: zone['soaExpire']
					}, {
						xtype: 'displayinfofield',
						margin: '0 0 0 5',
						info: 'Signed 32 bit time value in seconds. Indicates the time when the slave will try to refresh the zone from the master (by reading the master DNS SOA RR). RFC 1912 recommends 1200 to 43200 seconds, low (1200) if the data is volatile or 43200 (12 hours) if it`s not. If you are using NOTIFY you can set for much higher values, for instance, 1 or more days (> 86400 seconds).'
					}]
				}]
			}]
		},{
			xtype: 'dnsrecords',
			cls: 'x-grid-shadow x-fieldset-panel',
			itemId: 'dnsrecords',
			store: storeOwnRecords,
			multiSelect: true,
			title: 'DNS Records <img data-qtip="Click on DNS record to edit." class="tipHelp" src="/ui2/images/icons/info_icon_16x16.png" style="cursor: help; height: 16px;">',
			flex: 1,
			layout: 'fit',
			margin: '0 0 12 12',
			padding: '8 32',
			zone: zone,
			stores: {own: storeOwnRecords, system: storeSystemRecords}
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
					if (form.getForm().isValid()) {
						form.down('#dnsrecords').setType('own');
						if (!form.down('#dnsrecords').fireEvent('closeeditor')) {
							return;
						}
						var results = {};
						(storeOwnRecords.snapshot || storeOwnRecords.data).each(function(item, index){
							results['record-'+index] = item.getData();
						});

						Scalr.Request({
							processBox: {
								type: 'save'
							},
							form: form.getForm(),
							url: '/dnszones/xSave/',
							scope: this,
							params: {
								domainId: zone['domainId'] || 0,
								records: Ext.encode(results)
							},
							success: function () {
								Scalr.event.fireEvent('close');
							},
							failure: function(data) {
								Ext.Object.each(data.errors, function(index, item){
									(storeOwnRecords.snapshot || storeOwnRecords.data).each(function(record, recIndex){
										if (index.replace('record-', '') == recIndex) {
											form.down('#dnsrecords').getPlugin('rowediting').startEdit(record, 0);
										}
									});
									Scalr.message.Error(item);
									return false;
								})
							}
						});
					}
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
	return form;
});
