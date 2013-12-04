Scalr.regPage('Scalr.ui.farms.builder.addrole.ec2', function () {
    return {
        xtype: 'container',
        isExtraSettings: true,
        hidden: true,
        
        cls: 'x-delimiter-top',
        padding: '18 24',
        
        layout: {
            type: 'hbox',
            align: 'stretch'
        },
        defaults: {
            maxWidth: 340
        },
        
        isVisibleForRole: function(record) {
            return record.get('platform') === 'ec2';
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
            var formPanel = this.up('form'),
                role = formPanel.getCurrentRole(),
                instType,
                field,
                tags;

            if (formPanel.mode === 'shared') {
                tags = record.get('tags') || [];
                if (role.hvm == 1) {
                    Ext.Array.include(tags, 'ec2.hvm');
                }
                if (role.ebs == 1) {
                    Ext.Array.include(tags, 'ec2.ebs');
                }
                record.set('tags', tags);
            }

            instType = record.getEc2InstanceType();

            field = this.down('[name="aws.instance_type"]');
            field.reset();
            field.store.load({data: instType.list});
            field.setValue(instType.value);

            if (formPanel.up('roleslibrary').vpc === false) {
                Scalr.cachedRequest.load(
                    {
                        url: '/platforms/ec2/xGetAvailZones',
                        params: {cloudLocation: record.get('cloud_location')}
                    },
                    function(data, status){
                        var availZoneField = this.down('[name="aws.availability_zone"]'),
                            items = [{id: '', name: 'AWS-chosen'}];

                        if (status) {
                            items = [{ 
                                id: 'x-scalr-diff', 
                                name: 'Distribute equally' 
                            },{ 
                                id: '', 
                                name: 'AWS-chosen' 
                            },{ 
                                id: 'x-scalr-custom', 
                                name: 'Selected by me',
                                items: Ext.Array.map(data || [], function(item){ item.disabled = item.state != 'available'; return item;})
                            }];
                        }
                        availZoneField.store.loadData(items);
                        availZoneField.setValue('');
                        availZoneField.show().setDisabled(!status);
                    },
                    this
                );
            } else {
                this.down('[name="aws.availability_zone"]').hide();
            }

        },

        isValid: function() {
            return true;
        },

        getSettings: function() {
            var formPanel = this.up('form'),
                settings = {},
                value;

            if (formPanel.up('roleslibrary').vpc === false) {
                value = this.down('[name="aws.availability_zone"]').getValue();
                if (Ext.isObject(value)) {
                    if (value.items) {
                        if (value.items.length === 1) {
                            value = value.items[0];
                        } else if (value.items.length > 1) {
                            value = value.id + '=' + value.items.join(':');
                        }
                    }
                }
                settings['aws.availability_zone'] = value;
            }
            settings['aws.instance_type'] = this.down('[name="aws.instance_type"]').getValue();
            return settings;
        },

        items: [{
            xtype: 'comboradio',
            fieldLabel: 'Avail zone',
            flex: 1,
            submitValue: false,
            name: 'aws.availability_zone',
            valueField: 'id',
            displayField: 'name',
            listConfig: {
                cls: 'x-menu-light'
            },
            store: {
                fields: [ 'id', 'name', 'state', 'disabled', 'items' ],
                proxy: 'object'
            },
            margin: '0 36 0 0',
            labelWidth: 70,
            listeners: {
                collapse: function() {
                    var value = this.getValue();
                    if (Ext.isObject(value) && value.items.length === 0) {
                        this.setValue('');
                    }
                }
            }
        },{
            xtype: 'combo',
            flex: 1,
            submitValue: false,
            editable: false,
            labelWidth: 90,
            queryMode: 'local',
            name: 'aws.instance_type',
            fieldLabel: 'Instance type',
            store: {
                fields: [ 'id', 'name' ],
                proxy: 'object'
            },
            valueField: 'name',
            displayField: 'name',
            listeners: {
                change: function(comp, value){
                    if (value) {
                        this.up('form').updateRecordSettings(comp.name, value);
                    }
                }
            }
        }]
    }
});
