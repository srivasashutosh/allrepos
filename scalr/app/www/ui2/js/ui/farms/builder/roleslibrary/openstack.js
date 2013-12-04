Scalr.regPage('Scalr.ui.farms.builder.addrole.openstack', function () {
    return {
        xtype: 'container',
        isExtraSettings: true,
        hidden: true,

        cls: 'x-delimiter-top',
        padding: '18 24',
        
        layout: 'hbox',

        isVisibleForRole: function(record) {
            return Ext.Array.contains(['openstack', 'rackspacengus', 'rackspacenguk'], record.get('platform'));
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
                    url: '/platforms/openstack/xGetOpenstackResources',
                    params: {
                        cloudLocation: record.get('cloud_location'), 
                        platform: record.get('platform')
                    }
                },
                function(data, status){
                    var flavorIdField = this.down('[name="openstack.flavor-id"]'),
                        value = '';
                    if (status) {
                        flavorIdField.store.load({ data:  data['flavors'] || []});
                        if (flavorIdField.store.getCount() > 0) {
                            value = flavorIdField.store.getAt(0).get('id');
                        }
                    }
                    flavorIdField.setValue(value);
                    flavorIdField.setDisabled(!status);
                },
                this
            );
        },

        isValid: function() {
            return true;
        },

        getSettings: function() {
            return {
                'openstack.flavor-id': this.down('[name="openstack.flavor-id"]').getValue()
            };
        },

        items: [{
            xtype: 'combo',
            store: {
                fields: [ 'id', 'name' ],
                proxy: 'object'
            },
            maxWidth: 710,
            flex: 1,
            valueField: 'id',
            displayField: 'name',
            fieldLabel: 'Flavor',
            labelWidth: 50,
            editable: false,
            queryMode: 'local',
            name: 'openstack.flavor-id'
        }]
    }
});
