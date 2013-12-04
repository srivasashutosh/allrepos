Scalr.regPage('Scalr.ui.farms.builder.tabs.ec2', function (moduleParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'EC2',
        itemId: 'ec2',
        
        layout: 'anchor',
        minWidth: 700,
        
        tabData: null,
        
		isEnabled: function (record) {
			return record.get('platform') == 'ec2';
		},
        
		getDefaultValues: function (record) {
			return {
                //elastic IPs
				'aws.use_elastic_ips': 0,
				'aws.elastic_ips.map': '',
                
                //perks
				'aws.additional_security_groups': '',
				'aws.aki_id' : '',
				'aws.ari_id' : '',
				'aws.cluster_pg': '',
				'aws.instance_name_format': '',
                'aws.enable_cw_monitoring': 0
			};
		},
		
        onRoleUpdate: function(record, name, value, oldValue) {
            if (this.isVisible()) {
                var fullname = name.join('.');
                if (fullname === 'settings.aws.instance_type') {
                    var settings = record.get('settings', true),
                        field,
                        ebsOptimizedVisible;
                    ebsOptimizedVisible = record.isEc2EbsOptimizedFlagVisible(value);
                    settings['aws.ebs_optimized'] = ebsOptimizedVisible && settings['aws.ebs_optimized'] == 1 ? 1 : 0;
                    field = this.down('[name="aws.ebs_optimized"]');
                    field.setVisible(ebsOptimizedVisible);
                    field.setValue(settings['aws.ebs_optimized'] == 1);
                }
            }
        },
        
		beforeShowTab: function (record, handler) {
            this.vpc = this.up('#fbcard').down('#farm').getVpcSettings();
            this.up('#farmbuilder').cache.load(
                {
                    url: '/platforms/ec2/xGetPlatformData',
                    params: {
                        cloudLocation: record.get('cloud_location'),
                        farmRoleId: record.get('new') ? '' : record.get('farm_role_id'),
						vpcId: this.vpc ? this.vpc.id : null
                    }
                },
                function(data, status){
                    if (!status) {
                        this.deactivateTab();
                    } else {
                        this.tabData = data;
                        if (status === 'success') {
                            if (this.tabData['eips'] && this.tabData['eips']['ips']) {
                                this.tabData['eips']['ips'].unshift({ipAddress: '0.0.0.0'});
                            }
                        }
                        handler();
                    }
                },
                this,
                0
            );
		},

		showTab: function (record) {
			var settings = record.get('settings', true),
                eipsData = this.tabData['eips'],
                field, expand, eipsFieldset;
            this.suspendLayouts();
            
            this.down('#vpcoptions').hide();
            this.down('#no_elasticip_warning').hide();
            
            eipsFieldset = this.down('[name="aws.use_elastic_ips"]');
            if (this.vpc !== false) {
                var subnetIdField = this.down('[name="aws.vpc_subnet_id"]'),
                    internetAccessField = this.down('[name="aws.vpc_internet_access"]'),
                    availZoneField = this.down('[name="aws.vpc_avail_zone"]');
                    
                subnetIdField.reset();
                availZoneField.reset();
                subnetIdField.store.load({data: this.tabData['subnets']});
                
                this.down('[name="vpcSubnetType"]').setValue(settings['aws.vpc_subnet_id'] ? 'existing' : 'new');

                if (settings['aws.vpc_subnet_id']) {
                    subnetIdField.setValue(settings['aws.vpc_subnet_id']);
                } else if (settings['aws.vpc_avail_zone']){
                    availZoneField.setValue(settings['aws.vpc_avail_zone']);
                }
                this.down('[name="aws.vpc_routing_table_id"]').setValue(settings['aws.vpc_routing_table_id'] || null);
                internetAccessField.setValue(settings['aws.vpc_internet_access'] || 'outbound-only');                
                
                //this.down('#vpcSubnetTypeSubnet').setDisabled(subnetIdField.store.getCount() === 0);
                
                this.down('#vpcoptions').show();
            } else {
                this.down('#vpcoptions').hide();
                eipsFieldset.checkboxCmp.show()
                eipsFieldset.show();
            }
            
            //elastic IPs
			if (eipsData.map.length > settings['scaling.max_instances']) {
                //igor's request
                var removeIndex = settings['scaling.max_instances'];
				for (var i=eipsData.map.length-1; i>=0; i--) {
                    removeIndex = i + 1;
                    if (eipsData.map[i]['serverId'] || removeIndex == settings['scaling.max_instances']) {
                        break;
                    }
                }
                eipsData.map.splice(removeIndex, eipsData.map.length - removeIndex);
			} else if (eipsData.map.length < settings['scaling.max_instances']) {
				for (var i = eipsData.map.length; i < settings['scaling.max_instances']; i++)
					eipsData.map.push({ serverIndex: i + 1 });
			}

            field = this.down('[name="aws.elastic_ips.map"]');
			field.store.load({ data: eipsData.map });
			field['ipAddressEditorIps'] = eipsData['ips'];
			this.down('[name="aws.elastic_ips.warning"]').hide();

            eipsFieldset[settings['aws.use_elastic_ips'] == 1 ? 'expand' : 'collapse']();
            
            //perks
			this.down('[name="aws.additional_security_groups"]').setValue(settings['aws.additional_security_groups']);
			this.down('[name="aws.aki_id"]').setValue(settings['aws.aki_id']);
			this.down('[name="aws.ari_id"]').setValue(settings['aws.ari_id']);
			this.down('[name="aws.cluster_pg"]').setValue(settings['aws.cluster_pg']);
            this.down('[name="aws.instance_name_format"]').setValue(settings['aws.instance_name_format']);
            
            var ebsOptimizedVisible = record.isEc2EbsOptimizedFlagVisible();
            field = this.down('[name="aws.ebs_optimized"]');
            field.setVisible(ebsOptimizedVisible);
            field.setValue(ebsOptimizedVisible && settings['aws.ebs_optimized'] == 1);

            this.down('[name="aws.enable_cw_monitoring"]').setDisabled(record.get('behaviors', true).match("cf_")).setValue(settings['aws.enable_cw_monitoring'] == 1);
            
            expand = !Ext.isEmpty(settings['aws.additional_security_groups']) || !Ext.isEmpty(settings['aws.aki_id']) ||
                     !Ext.isEmpty(settings['aws.ari_id']) || !Ext.isEmpty(settings['aws.cluster_pg']) || 
                     (!Ext.isEmpty(settings['aws.instance_name_format']) && settings['aws.instance_name_format'] != this.getDefaultValues()['aws.instance_name_format']) || 
                     settings['aws.enable_cw_monitoring'] == 1 ||
                     (ebsOptimizedVisible && settings['aws.ebs_optimized'] == 1);

            this.down('#perks')[expand ? 'expand' : 'collapse']();
            
            //additional tags
            this.down('[name="aws.additional_tags"]').setValue(settings['aws.additional_tags'] || '');
            this.down('#additionaltags')[!Ext.isEmpty(settings['aws.additional_tags']) ? 'expand' : 'collapse']();
            
            this.resumeLayouts(true);
            
		},

		hideTab: function (record) {
			var me = this,
                settings = record.get('settings'),
                eipsFieldset = me.down('[name="aws.use_elastic_ips"]');;
            
            //vpc
            if (me.vpc !== false) {
                settings['aws.vpc_subnet_id'] = null;
                settings['aws.vpc_avail_zone'] = null;
                settings['aws.vpc_internet_access'] = null;
                settings['aws.vpc_routing_table_id'] = null;
                
                if (me.down('[name="vpcSubnetType"]').getValue() === 'new') {
                    settings['aws.vpc_internet_access'] = me.down('[name="aws.vpc_internet_access"]').getValue();
                    settings['aws.vpc_routing_table_id'] = me.down('[name="aws.vpc_routing_table_id"]').getValue();
                    settings['aws.vpc_avail_zone'] = me.down('[name="aws.vpc_avail_zone"]').getValue();
                } else {
                    settings['aws.vpc_subnet_id'] = me.down('[name="aws.vpc_subnet_id"]').getValue();
                }
            }
            
            //elastic IPs
			Ext.each(me.down('[name="aws.elastic_ips.map"]').store.getModifiedRecords(), function(record) {
				me.tabData['eips'].map[record.index] = record.data;
			});

			if (!eipsFieldset.collapsed && eipsFieldset.isVisible()) {
				settings['aws.use_elastic_ips'] = 1;
				settings['aws.elastic_ips.map'] = '';
				me.down('[name="aws.elastic_ips.map"]').store.each(function(record) {
					settings['aws.elastic_ips.map'] += record.get('serverIndex') + '=' + record.get('elasticIp') + ';';
				});
			} else {
				settings['aws.use_elastic_ips'] = 0;
			}
            
            //perks
			settings['aws.additional_security_groups'] = me.down('[name="aws.additional_security_groups"]').getValue();
			settings['aws.aki_id'] = me.down('[name="aws.aki_id"]').getValue();
			settings['aws.ari_id'] = me.down('[name="aws.ari_id"]').getValue();
			settings['aws.cluster_pg'] = me.down('[name="aws.cluster_pg"]').getValue();
            settings['aws.ebs_optimized'] = me.down('[name="aws.ebs_optimized"]').getValue() ? 1 : 0;
            settings['aws.enable_cw_monitoring'] = me.down('[name="aws.enable_cw_monitoring"]').getValue() ? 1 : 0; 
			settings['aws.instance_name_format'] = me.down('[name="aws.instance_name_format"]').getValue();       
            
            //additional tags
            settings['aws.additional_tags'] = me.down('[name="aws.additional_tags"]').getValue();
			
			record.set('settings', settings);
		},
        defaults: {
            anchor: '100%',
			defaults: {
                maxWidth: 820,
				labelWidth: 230,
                anchor: '100%'
			}
        },
        
        toggleInternetAccess: function() {
            var internetAccess,
                eipsFieldset = this.down('[name="aws.use_elastic_ips"]'),
                internetAccessField = this.down('[name="aws.vpc_internet_access"]'),
                internetAccessFieldRO = this.down('#vpc_internet_access'),
                subnetIdField = this.down('[name="aws.vpc_subnet_id"]');
            
            if (this.down('[name="vpcSubnetType"]').getValue() === 'new') {
                internetAccessFieldRO.hide();
                internetAccessField.show();
                internetAccess = internetAccessField.getValue();
            } else {
                internetAccessField.hide();
                var rec = subnetIdField.findRecordByValue(subnetIdField.getValue());
                if (rec) {
                    internetAccess = rec.get('internet') || 'unknown';
                    internetAccessFieldRO.show().setValue(Ext.String.capitalize(internetAccess));
                } else {
                    internetAccessFieldRO.hide();
                }
            }
            
            this.down('#no_elasticip_warning').setVisible(internetAccess === 'outbound-only');
            eipsFieldset.setVisible(internetAccess === 'full' || internetAccess === 'unknown');
            if (internetAccess === 'full') {
                eipsFieldset.expand();
            }
            eipsFieldset.checkboxCmp.setVisible(internetAccess === 'unknown');
        },
		items: [{
			xtype: 'fieldset',
            title: 'VPC-related options',
            itemId: 'vpcoptions',
            hidden: true,
			items: [{
                xtype: 'buttongroupfield',
                name: 'vpcSubnetType',
                fieldLabel: 'Placement (subnet settings)',
                labelWidth: 200,
                submitValue: false,
                defaults: {
                    width: 123
                },
                items: [{
                    text: 'New subnet',
                    value: 'new'
                },{
                    text: 'Existing subnet',
                    value: 'existing'
                }],
                listeners: {
                    change: function(comp, value) {
                        var tab = comp.up('#ec2'),
                            next = comp.next();
                        next.suspendLayouts();
                        next.down('[name="aws.vpc_subnet_id"]').setVisible(value !== 'new');
                        next.down('[name="aws.vpc_avail_zone"]').setVisible(value === 'new');
                        next.down('[name="aws.vpc_routing_table_id"]').setVisible(value === 'new');
                        next.resumeLayouts(true);
                        tab.toggleInternetAccess();
                    }
                }
            },{
                xtype: 'fieldset',
                defaults: {
                    labelWidth: 166,
                    width: 420
                },
                items: [{
                    xtype: 'combo',
                    name: 'aws.vpc_avail_zone',
                    fieldLabel: 'Availability zone',
                    editable: false,
                    valueField: 'id',
                    displayField: 'name',
                    queryMode: 'local',
                    store: {
                        fields: ['id' , 'name'],
                        proxy: 'object'
                    },
                    listeners: {
                        expand: function() {
                            var me = this,
                                tab = this.up('#ec2');
                            if (me.loadedCloudLocation != tab.vpc.region) {
                                me.collapse();
                                me.store.removeAll();
                                Scalr.cachedRequest.load(
                                    {
                                        url: '/platforms/ec2/xGetAvailZones',
                                        params: {cloudLocation: tab.vpc.region}
                                    },
                                    function(data, status){
                                        if (status) {
                                            me.store.loadData(data || []);
                                            me.loadedCloudLocation = tab.vpc.region;
                                            me.expand();
                                        }
                                    },
                                    this
                                );
                            }
                        }
                    }
                },{
                    xtype: 'combo',
                    name: 'aws.vpc_routing_table_id',
                    fieldLabel: 'Routing table',
                    hidden: true,
                    editable: false,
                    valueField: 'id',
                    displayField: 'name',
                    emptyText: 'Scalr default',
                    queryMode: 'local',
                    store: {
                        fields: ['id' , 'name'],
                        proxy: 'object'
                    },
                    listeners: {
                        expand: function() {
                            var me = this,
                                tab = this.up('#ec2');
                            if (me.loadedCloudLocation != tab.vpc.region) {
                                me.collapse();
                                me.store.removeAll();
                                tab.up('#farmbuilder').cache.load(
                                    {
                                        url: '/platforms/ec2/xGetRoutingTableList',
                                        params: {cloudLocation: tab.vpc.region, vpcId: tab.vpc.id}
                                    },
                                    function(data, status){
                                        me.store.loadData([{id: '', name: 'Scalr default'}]);
                                        if (status) {
                                            me.store.loadData(data['tables'] || [], true);
                                            me.loadedCloudLocation = tab.vpc.region;
                                        }
                                        me.expand();
                                    },
                                    this,
                                    0
                                );
                            }
                        }
                    }
                },{
                    xtype: 'buttongroupfield',
                    name: 'aws.vpc_internet_access',
                    fieldLabel: 'Internet access',
                    defaults:{
                        width: 122
                    },
                    items: [{
                        text: 'Full',
                        value: 'full'
                    },{
                        text: 'Outbound-only',
                        value: 'outbound-only'
                    }],
                    listeners: {
                        change: function(comp, value) {
                            comp.up('#ec2').toggleInternetAccess();
                        }
                    }
                },{
                    xtype: 'combo',
                    name: 'aws.vpc_subnet_id',
                    fieldLabel: 'Subnet',
                    editable: false,
                    valueField: 'id',
                    displayField: 'description',
                    queryMode: 'local',
                    store: {
                        fields: ['id' , 'description', 'availability_zone', 'internet', 'ips_left', 'sidr'],
                        proxy: 'object'
                    },
                    listConfig: {
                        cls: 'x-boundlist-alt',
                        tpl:
                            '<tpl for="."><div class="x-boundlist-item" style="height: auto; width: auto;line-height:20px">' +
                                '<div><span style="font-weight: bold">{id}</span> <span style="font-style: italic;font-size:90%">(Internet access: <b>{[values.internet || \'unknown\']}</b>)</span></div>' +
                                '<div>{sidr} in {availability_zone} [IPs left: {ips_left}]</div>' +
                            '</div></tpl>'
                    },
                    listeners: {
                        change: function(comp, value) {
                            comp.up('#ec2').toggleInternetAccess();
                        }
                    }
                },{
                    xtype: 'displayfield',
                    itemId: 'vpc_internet_access',
                    fieldLabel: 'Internet access',
                    hidden: true
                }]
            },{
				xtype: 'displayfield',
                itemId: 'no_elasticip_warning',
                hidden: true,
				fieldCls: 'x-form-field-info',
				value:   'ElasticIPs are not available with outbound-only internet access.'
            }]
		}, {
			xtype: 'fieldset',
			title: 'Assign one ElasticIP per instance',
			name: 'aws.use_elastic_ips',
			checkboxToggle: true,
            toggleOnTitleClick: true,
            collapsible: true,
			collapsed: true,
            listeners: {
                beforecollapse: function() {
                    var tab = this.up('#ec2'),
                        internetAccess = 'outbound-only',
                        subnetIdField, rec;

                    if (tab.vpc !== false) {
                        if (tab.down('[name="aws.vpc_subnet_id"]').disabled) {
                            internetAccess = tab.down('[name="aws.vpc_internet_access"]').getValue();
                        } else {
                            subnetIdField = tab.down('[name="aws.vpc_subnet_id"]');
                            rec = subnetIdField.findRecordByValue(subnetIdField.getValue());
                            if (rec) {
                                internetAccess = rec.get('internet');
                            }
                        }

                        if (internetAccess === 'full') {
                            this.checkboxCmp.setValue(true);
                            return false;
                        }
                    }
                }
            },
			items: [{
				xtype: 'displayfield',
				fieldCls: 'x-form-field-info',
				value:   'Enable to have Scalr automatically assign an ElasticIP to each instance of this role ' +
					'(this requires a few minutes during which the instance is unreachable from the public internet) ' +
					'after HostInit but before HostUp. If out of allocated IPs, Scalr will request more, but never remove any.'
			}, {
				xtype: 'displayfield',
				fieldCls: 'x-form-field-warning',
				anchor: '100%',
				name: 'aws.elastic_ips.warning',
				hidden: true,
				value: ''
			}, {
				xtype: 'grid',
                cls: 'x-grid-shadow',
				name: 'aws.elastic_ips.map',
				plugins: [{
					ptype: 'cellediting',
					clicksToEdit: 1,
					listeners: {
						beforeedit: function(comp, e) {
							var editor = this.getEditor(e.record, e.column);
							for (var i = 0, len = e.grid['ipAddressEditorIps'].length; i < len; i++) {
								e.grid['ipAddressEditorIps'][i]['fieldInstanceId'] = e.record.get('instanceId') && (e.grid['ipAddressEditorIps'][i]['instanceId'] == e.record.get('instanceId'));
							}
							editor.field.store.load({ data: e.grid['ipAddressEditorIps'] });
						},
						edit: function(comp, e) {
							if (e.value == null) {
								e.record.set('elasticIp', '');
							}

							if (e.record.get('elasticIp')) {
								var editor = this.getEditor(e.record, e.column);
								var r = editor.field.store.findRecord('ipAddress', e.record.get('elasticIp'));
								if (r && r.get('instanceId') && r.get('instanceId') != e.record.get('instanceId') && r.get('ipAddress') != e.record.get('remoteIp'))
									e.grid.up('[tab="tab"]').down('[name="aws.elastic_ips.warning"]').setValue(
										'IP address \'' + e.record.get('elasticIp') + '\' is already in use, and will be re-associated with selected server. IP address on old server will revert to dynamic IP.'
									).show();
								else
									e.grid.up('[tab="tab"]').down('[name="aws.elastic_ips.warning"]').hide();
							}
						}
					}
				}],
				viewConfig: {
					disableSelection: true
				},
				store: {
					proxy: 'object',
					fields: [ 'elasticIp', 'instanceId', 'serverId', 'serverIndex', 'remoteIp', 'warningInstanceIdDoesntMatch' ]
				},
				columns: [
					{ header: 'Server Index', width: 120, sortable: true, dataIndex: 'serverIndex' },
					{ header: 'Server ID', flex: 1, sortable: true, dataIndex: 'serverId', xtype: 'templatecolumn', tpl:
						'<tpl if="serverId"><a href="#/servers/{serverId}/extendedInfo">{serverId}</a> <tpl if="instanceId">({instanceId})</tpl><tpl else>Not running</tpl>'
					}, {
						header: 'Elastic IP', width: 250, sortable: true, dataIndex: 'elasticIp', editable: true, tdCls: 'x-grid-cell-editable',
						renderer: function(value, metadata, record) {
							metadata.tdAttr = 'title="Click here to change"';
							metadata.style = 'line-height: 16px; padding-top: 4px; padding-bottom: 2px';

							if (value == '0.0.0.0')
								value = 'Allocate new';
							else if (!value)
								value = 'Not allocated yet';

							value = '<span style="float: left">' + value + '</span>';

							if (record.get('warningInstanceIdDoesntMatch'))
								value += '<div style="margin-left: 5px; float: left; height: 15px; width: 16px; background-image: url(/ui2/images/icons/warning_icon_16x16.png)" title="This IP address is out of sync and associated with another instance on EC2">&nbsp;</div>'

							return value;
						},
						editor: {
							xtype: 'combobox',
							forceSelection: true,
							editable: false,
							displayField: 'ipAddress',
							valueField: 'ipAddress',
							matchFieldWidth: false,
							store: {
								proxy: 'object',
								fields: ['ipAddress', 'instanceId', 'farmName' , 'roleName', 'serverIndex', 'fieldInstanceId']
							},
							displayTpl: '<tpl for="."><tpl if="values.ipAddress == \'0.0.0.0\'">Allocate new<tpl else>{[values.ipAddress]}</tpl></tpl>',
							listConfig: {
								minWidth: 250,
								cls: 'x-boundlist-alt',
								tpl: '<tpl for="."><div class="x-boundlist-item" style="font: bold 13px arial; height: auto; padding: 5px;">' +
										'<tpl if="ipAddress == \'0.0.0.0\'"><span>Allocate new</span>' +
										'<tpl elseif="ipAddress != \'\'">' +
											'<tpl if="!fieldInstanceId">' +
												'<tpl if="farmName"><span style="color: #F90000">{ipAddress}</span>' +
												'<tpl else><span style="color: #138913">{ipAddress}</span> (free)</tpl>' +
											'<tpl else><span>{ipAddress}</span></tpl>' +
										'<tpl else>Not allocated yet</tpl>' +
										'<tpl if="ipAddress && farmName"><br /><span style="font-weight: normal">used by: {farmName} &rarr; {roleName} # {serverIndex}</span></tpl>' +
									'</div></tpl>'
							}
						}
					}
				]
			}]
        },{
			xtype: 'fieldset',
            itemId: 'perks',
            title: 'Perks',
            collapsible: true,
            collapsed: true,
            toggleOnTitleClick: true,
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Security groups (comma separated)',
				name: 'aws.additional_security_groups'
			}, {
				xtype: 'textfield',
				fieldLabel: 'AKI id',
				name: 'aws.aki_id'
			}, {
				xtype: 'textfield',
				fieldLabel: 'ARI id',
				name: 'aws.ari_id'
			}, {
				xtype: 'textfield',
				fieldLabel: 'Cluster placement group',
				name: 'aws.cluster_pg'
			}, {
                xtype: 'fieldcontainer',
                fieldLabel: 'Instance name',
                layout: 'hbox',
                items: [{
                    xtype: 'textfield',
                    name: 'aws.instance_name_format',
                    emptyText: '%farm_name% -> %role_name% #%instance_index%',
                    flex: 1
                },{
                    xtype: 'displayinfofield',
                    margin: '0 0 0 5',
					value: '<b>You can use the following variables:</b> %image_id%, %external_ip%, %internal_ip%, %role_name%, %isdbmaster%, %instance_index%, ' +
							'%server_id%, %farm_id%, %farm_name%, %env_id%, %env_name%, %cloud_location%, %instance_id%, %avail_zone%<br />' +
							'<b>For example:</b> %farm_name% -> %role_name% #%instance_index%'
                }]
			}, {
                xtype: 'checkbox',
                name: 'aws.ebs_optimized',
                boxLabel: 'Launch instances as an <a target="_blank" href="http://aws.typepad.com/aws/2012/08/fast-forward-provisioned-iops-ebs.html">EBS-Optimized</a>'
			},{
				xtype: 'checkbox',
				name: 'aws.enable_cw_monitoring',
				boxLabel: 'Enable Detailed <a href="http://aws.amazon.com/cloudwatch/" target="_blank">CloudWatch</a> monitoring for instances of this role (1 min interval)'
            }]
		}, {
			xtype: 'fieldset',
            itemId: 'additionaltags',
            title: 'Additional tags',
            collapsible: true,
            collapsed: true,
            toggleOnTitleClick: true,
			items: [{
                xtype: 'container',
                layout: {
                    type: 'hbox',
                    align: 'middle'
                },
                items: [{
                    xtype: 'displayfield',
                    value: 'One per line: name=value'
                }, {
                    xtype: 'displayinfofield',
                    margin: '0 0 0 5',
					value: '<b>You can use the following variables:</b> %image_id%, %external_ip%, %internal_ip%, %role_name%, %isdbmaster%, %instance_index%, ' +
						'%server_id%, %farm_id%, %farm_name%, %env_id%, %env_name%, %cloud_location%, %instance_id%, %avail_zone%<br />' +
						'<b>For example:</b> tag1=%instance_index%.%farm_id%.example'
                }]
            },{
				xtype: 'textarea',
				name: 'aws.additional_tags'
			}]
		}]
	});
});
