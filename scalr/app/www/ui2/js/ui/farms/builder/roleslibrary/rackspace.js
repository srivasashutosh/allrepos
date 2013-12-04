Scalr.regPage('Scalr.ui.farms.builder.addrole.rackspace', function () {
    return {
        xtype: 'container',
        isExtraSettings: true,
        hidden: true,

        cls: 'x-delimiter-top',
        padding: '18 24',
        
        layout: 'hbox',

        isVisibleForRole: function(record) {
            return record.get('platform') === 'rackspace';
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
                    url: '/platforms/rackspace/xGetFlavors',
                    params: {cloudLocation: record.get('cloud_location')}
                },
                function(data, status){
                    var flavorIdField = this.down('[name="rs.flavor-id"]');
                    if (status) {
                        flavorIdField.store.load({ data: data || []});
                    }
                    flavorIdField.setValue(1);
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
                'rs.flavor-id': this.down('[name="rs.flavor-id"]').getValue()
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
            submitValue: false,
            valueField: 'id',
            displayField: 'name',
            fieldLabel: 'Flavor',
            labelWidth: 50,
            editable: false,
            queryMode: 'local',
            name: 'rs.flavor-id'
        }]
    }
});
