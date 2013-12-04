Scalr.regPage('Scalr.ui.farms.builder.addrole.cloudstack', function () {
    return {
        xtype: 'container',
        isExtraSettings: true,
        hidden: true,

        cls: 'x-delimiter-top',
        padding: '18 24',
        
        defaults: {
            maxWidth: 720
        },
        
        isVisibleForRole: function(record) {
            return Ext.Array.contains(['cloudstack', 'idcf', 'ucloud'], record.get('platform'));
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
            Scalr.cachedRequest.load(
                {
                    url: '/platforms/cloudstack/xGetOfferingsList/',
                    params: {
                        cloudLocation: record.get('cloud_location'), 
                        platform: record.get('platform')
                    }
                },
                function(data, status){
                    var me = this,
                        field,
                        defaultValue = null;
                    data = data || {};
                    field = this.down('[name="cloudstack.service_offering_id"]');
                    field.store.load({ data: data['serviceOfferings'] || []});
                    if (field.store.getCount() > 0) {
                        defaultValue = field.store.getAt(0).get('id');
                    }
                    field.setValue(defaultValue);
                    field.setDisabled(!status);


                    Ext.Object.each({
                        'cloudstack.network_id': 'networks',
                        'cloudstack.disk_offering_id': 'diskOfferings', 
                        'cloudstack.shared_ip.id': 'ipAddresses'
                    }, function(fieldName, dataFieldName){
                        defaultValue = null;
                        field = me.down('[name="' + fieldName + '"]');
                        field.store.load({ data: data[dataFieldName] || [] });
                        if (field.store.getCount() == 0) {
                            field.hide();
                        } else {
                            defaultValue = field.store.getAt(0).get('id');
                            field.show();
                        }
                        field.setValue(defaultValue);
                    });
                },
                this
            );
        },

        isValid: function() {
            return true;
        },

        getSettings: function() {
            var sharedIpIdField = this.down('[name="cloudstack.shared_ip.id"]'),
                settings = {
                'cloudstack.service_offering_id': this.down('[name="cloudstack.service_offering_id"]').getValue(),
                'cloudstack.network_id': this.down('[name="cloudstack.network_id"]').getValue(),
                'cloudstack.shared_ip.id': sharedIpIdField.getValue()
            };

            if (settings['cloudstack.shared_ip.id']) {
                var r = sharedIpIdField.findRecordByValue(settings['cloudstack.shared_ip.id']);
                settings['cloudstack.shared_ip.address'] = r ? r.get('name') : '';
            } else {
                settings['cloudstack.shared_ip.address'] = '';
            }

            return settings;
        },

        items: [{
            xtype: 'container',
            layout: 'hbox',
            margin: '0 0 12 0',
            items: [{
                xtype: 'combo',
                store: {
                    fields: [ 'id', 'name' ],
                    proxy: 'object'
                },
                flex: 1,
                matchFieldWidth: false,
                listConfig: {
                    style: 'white-space:nowrap'
                },
                valueField: 'id',
                displayField: 'name',
                fieldLabel: 'Service offering',
                labelWidth: 100,
                editable: false,
                labelStyle: 'white-space:nowrap',
                queryMode: 'local',
                name: 'cloudstack.service_offering_id'
            },{
                xtype: 'combo',
                store: {
                    fields: [ 'id', 'name' ],
                    proxy: 'object'
                },
                flex: 1,
                margin: '0 0 0 36',
                matchFieldWidth: false,
                listConfig: {
                    width: 'auto',
                    minWidth: 350
                },
                valueField: 'id',
                displayField: 'name',
                fieldLabel: 'Disk offering',
                editable: false,
                queryMode: 'local',
                name: 'cloudstack.disk_offering_id'
            }]
        },{
            xtype: 'container',
            layout: {
                type: 'hbox',
                align: 'stretch'
            },
            items: [{
                xtype: 'combo',
                store: {
                    fields: [ 'id', 'name' ],
                    proxy: 'object'
                },
                flex: 1,
                matchFieldWidth: false,
                listConfig: {
                    width: 'auto',
                    minWidth: 350
                },
                valueField: 'id',
                displayField: 'name',
                fieldLabel: 'Network',
                editable: false,
                queryMode: 'local',
                name: 'cloudstack.network_id'
            }, {
                xtype: 'combo',
                store: {
                    fields: [ 'id', 'name' ],
                    proxy: 'object'
                },
                flex: 1,
                margin: '0 0 0 36',
                matchFieldWidth: false,
                listConfig: {
                    width: 'auto',
                    minWidth: 350
                },
                valueField: 'id',
                displayField: 'name',
                fieldLabel: 'Shared IP',
                editable: false,
                queryMode: 'local',
                name: 'cloudstack.shared_ip.id'
            }]
        }]
    }
});
