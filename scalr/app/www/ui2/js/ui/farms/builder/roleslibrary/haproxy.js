Scalr.regPage('Scalr.ui.farms.builder.addrole.haproxy', function () {
    return {
        xtype: 'container',
        isExtraSettings: true,
        hidden: true,

        cls: 'x-delimiter-top',
        padding: '18 24',
        
        layout: 'anchor',

        isVisibleForRole: function(record) {
            return Ext.Array.contains(record.get('behaviors', true), 'haproxy');
        },

        onSelectImage: function(record) {
            if (this.isVisibleForRole(record)) {
                this.setRole(record);
                this.show();
            } else {
                this.hide();
            }
        },
        
        setRole: function(record){
            var hp = this.down('haproxysettings');
            hp.roles = [];
			this.up('roleslibrary').moduleParams.tabParams.farmRolesStore.each(function(r){
    			hp.roles.push({id: r.get('farm_role_id'), name: r.get('name')});
			});
            
            hp.setValue({
                'haproxy.port': 80,
                'haproxy.healthcheck.interval': 5,
                'haproxy.healthcheck.fallthreshold': 5,
                'haproxy.healthcheck.risethreshold': 3,
                backends: []
            });
        },

        isValid: function() {
            return true;
        },

        getSettings: function() {
            var hp = this.down('haproxysettings'),
                settings = hp.getValue();
            settings['haproxy.backends'] = Ext.encode(settings['haproxy.backends']);
            return settings;
        },

        items: [{
            xtype: 'label',
            cls: 'x-fieldset-subheader',
            text: 'Proxy settings'
        },{
            xtype: 'haproxysettings',
            roles: []
        }]
    }
});
