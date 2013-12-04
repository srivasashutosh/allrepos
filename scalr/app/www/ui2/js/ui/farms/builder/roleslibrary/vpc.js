Scalr.regPage('Scalr.ui.farms.builder.addrole.vpc', function () {
    return {
        xtype: 'container',
        itemId: 'vpc',
        isExtraSettings: true,
        hidden: true,

        cls: 'x-delimiter-top',
        padding: '18 24',
        
        layout: 'anchor',
        defaults: {
            anchor: '100%',
            labelWidth: 110,
            maxWidth: 710
        },
        
        isVisibleForRole: function(record) {
            return record.get('platform') === 'ec2' && 
                   this.up('roleslibrary').vpc !== false &&
                   !Ext.Array.contains(record.get('behaviors'), 'router');
        },

        onSelectImage: function(record) {
            if (this.isVisibleForRole(record)) {
                this.setRole(record);
                this.show();
            } else {
                this.hide();
            }
        },

        setRole: function(record) {
            var field;

            field = this.down('[name="aws.vpc_internet_access"]');
            field.setValue(field.getValue() || 'outbound-only');
            
            this.down('[name="aws.vpc_routing_table_id"]').reset();
            this.down('[name="vpcSubnetType"]').setValue('new');
        },

        isValid: function() {
            var res = true,
                fields = this.query('[isFormField]');
                
            for (var i = 0, len = fields.length; i < len; i++) {
                res = fields[i].validate();
                if (res === false) break;
            }
            return res;
        },

        getSettings: function() {
            var settings = {
                    'aws.vpc_internet_access': null,
                    'aws.vpc_subnet_id': null,
                    'aws.vpc_avail_zone': null
                };

            if (this.down('[name="vpcSubnetType"]').getValue() === 'new') {
                settings['aws.vpc_avail_zone'] = this.down('[name="aws.vpc_avail_zone"]').getValue();
                settings['aws.vpc_internet_access'] = this.down('[name="aws.vpc_internet_access"]').getValue();
                settings['aws.vpc_routing_table_id'] = this.down('[name="aws.vpc_routing_table_id"]').getValue();
                if (settings['aws.vpc_internet_access'] === 'full') {
                    settings['aws.use_elastic_ips'] = 1;
                }
            } else {
                var subnetIdField = this.down('[name="aws.vpc_subnet_id"]'),
                    subnet = subnetIdField.findRecordByValue(subnetIdField.getValue());
                settings['aws.vpc_subnet_id'] = subnetIdField.getValue();
                if (subnet && subnet.get('internet') === 'full') {
                    settings['aws.use_elastic_ips'] = 1;
                }
            }
            return settings;
        },

        items: [{
            xtype: 'label',
            cls: 'x-fieldset-subheader',
            text: 'VPC-related settings'
        },{
            xtype: 'buttongroupfield',
            name: 'vpcSubnetType',
            fieldLabel: 'Placement',
            labelWidth: 90,
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
                    var c = comp.next(),
                        isNew = value === 'new',
                        subnetIdField =  c.down('[name="aws.vpc_subnet_id"]'),
                        subnet = subnetIdField.findRecordByValue(subnetIdField.getValue());
                        
                    c.suspendLayouts();
                    c.down('[name="aws.vpc_avail_zone"]').setVisible(isNew).setDisabled(!isNew);
                    c.down('[name="aws.vpc_internet_access"]').setVisible(isNew).setDisabled(!isNew);
                    c.down('[name="aws.vpc_routing_table_id"]').setVisible(isNew).setDisabled(!isNew);
                    
                    subnetIdField.setVisible(!isNew).setDisabled(isNew);
                    if (!isNew && subnet) {
                        c.down('#vpc_internet_access').setValue(Ext.String.capitalize(subnet.get('internet') || 'unknown')).show();
                    } else {
                        c.down('#vpc_internet_access').hide();
                    }
                    
                    c.resumeLayouts(true);
                }
            }
        },{
            xtype: 'container',
            cls: 'inner-container',
            items: [{
                xtype: 'combo',
                name: 'aws.vpc_avail_zone',
                fieldLabel: 'Avail zone',
                editable: false,
                submitValue: false,
                width: 325,
                valueField: 'id',
                displayField: 'name',
                queryMode: 'local',
                allowBlank: false,
                store: {
                    fields: ['id' , 'name'],
                    proxy: 'object'
                },
                listeners: {
                    expand: function() {
                        var me = this,
                            vpc = me.up('roleslibrary').vpc;
                        if (me.loadedCloudLocation != vpc.region) {
                            me.collapse();
                            me.store.removeAll();
                            Scalr.cachedRequest.load(
                                {
                                    url: '/platforms/ec2/xGetAvailZones',
                                    params: {cloudLocation: vpc.region}
                                },
                                function(data, status){
                                    if (!status) return;
                                    me.store.loadData(data || []);
                                    me.loadedCloudLocation = vpc.region;
                                    me.expand();
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
                width: 325,
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
                            vpc = me.up('roleslibrary').vpc;
                        if (me.loadedCloudLocation != vpc.region) {
                            me.collapse();
                            me.store.removeAll();
                            Scalr.cachedRequest.load(
                                {
                                    url: '/platforms/ec2/xGetRoutingTableList',
                                    params: {cloudLocation: vpc.region, vpcId: vpc.id}
                                },
                                function(data, status){
                                    me.store.loadData([{id: '', name: 'Scalr default'}]);
                                    if (status) {
                                        me.store.loadData(data['tables'] || [], true);
                                        me.loadedCloudLocation = vpc.region;
                                    }
                                    me.expand();
                                },
                                this
                            );
                        }
                    }
                }
            },{
                xtype: 'buttongroupfield',
                name: 'aws.vpc_internet_access',
                submitValue: false,
                fieldLabel: 'Internet access',
                defaults:{
                    width: 109
                },
                items: [{
                    text: 'Full',
                    value: 'full'
                },{
                    text: 'Outbound-only',
                    value: 'outbound-only'
                }]
            },{
                xtype: 'combo',
                name: 'aws.vpc_subnet_id',
                fieldLabel: 'Subnet',
                editable: false,
                submitValue: false,
                width: 325,
                valueField: 'id',
                displayField: 'description',
                queryMode: 'local',
                allowBlank: false,
                store: {
                    fields: ['id' , 'description', 'availability_zone', 'internet', 'ips_left', 'sidr'],
                    proxy: 'object'
                },
                listConfig: {
                    style: 'white-space:nowrap',
                    cls: 'x-boundlist-alt',
                    tpl:
                        '<tpl for="."><div class="x-boundlist-item" style="height: auto; width: auto;line-height:20px">' +
                            '<div><span style="font-weight: bold">{id}</span> <span style="font-style: italic;font-size:90%">(Internet access: <b>{[values.internet || \'unknown\']}</b>)</span></div>' +
                            '<div>{sidr} in {availability_zone} [IPs left: {ips_left}]</div>' +
                        '</div></tpl>'
                },
                listeners: {
                    change: function(comp, value) {
                        var tab = this.up('#vpc'),
                            rec = comp.findRecordByValue(value);
                        if (rec) {
                            tab.down('#vpc_internet_access').setValue(Ext.String.capitalize(rec.get('internet') || 'unknown')).show();
                        } else {
                            tab.down('#vpc_internet_access').hide();
                        }

                    },
                    expand: function() {
                        var me = this,
                            vpc = me.up('roleslibrary').vpc,
                            cache = me.up('#farmbuilder').cache,
                            loadParams = {
                                url: '/platforms/ec2/xGetSubnetsList',
                                params: {
                                    cloudLocation: vpc.region,
                                    vpcId: vpc.id
                                }
                            },
                            cacheId = cache.getCacheId(loadParams);

                        if (me.loadedCacheId != cacheId) {
                            me.collapse();
                            me.store.removeAll();
                            cache.load(
                                loadParams,
                                function(data, status, cacheId){
                                    if (!status) return;
                                    me.store.loadData(data || []);
                                    me.loadedCacheId = cacheId;
                                    me.expand();
                                },
                                this,
                                0
                            );
                        }

                    }
                }
            },{
                xtype: 'displayfield',
                itemId: 'vpc_internet_access',
                fieldLabel: 'Internet access',
                hidden: true
            }]
        }]
    }
});
