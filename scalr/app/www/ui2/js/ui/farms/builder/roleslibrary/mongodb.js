Scalr.regPage('Scalr.ui.farms.builder.addrole.mongodb', function () {
    return {
        xtype: 'fieldset',
        isExtraSettings: true,
        hidden: true,
        
        title: 'Mongo over SSL',
        name: 'mongodb.ssl.enabled',
        toggleOnTitleClick: true,
        checkboxToggle: true,
        collapsed: true,
        collapsible: true,
        layout: 'anchor',
        cls: 'x-delimiter-top',
        padding: '0 0 12 0',
        style: 'border-radius:0',
        
        isVisibleForRole: function(record) {
            return Ext.Array.contains(record.get('behaviors'), 'mongodb');
        },

        onSelectImage: function(record) {
            if (this.isVisibleForRole(record)) {
                this.show();
            } else {
                this.hide();
            }
        },

        isValid: function() {
            var res = true;
            if (!this.collapsed) {
                res = this.down('[name="mongodb.ssl.cert_id"]').validate();
            }
            return res;
        },

        getSettings: function() {
            var settings = {},
                sslCertId = this.down('[name="mongodb.ssl.cert_id"]').getValue();
            if (!this.collapsed && sslCertId) {
                settings['mongodb.ssl.enabled'] = 1;
                settings['mongodb.ssl.cert_id'] = sslCertId;
            } else {
                settings['mongodb.ssl.enabled'] = 0;
                settings['mongodb.ssl.cert_id'] = '';
            }
            return settings;
        },

        items: [{
            xtype: 'combo',
            name: 'mongodb.ssl.cert_id',
            fieldLabel: 'SSL certificate',
            maxWidth: 720,
            anchor: '100%',
            store: {
                fields: [ 'id', 'name' ],
                data: []
            },
            queryMode: 'local',
            emptyText: 'Choose certificate',
            valueField: 'id',
            displayField: 'name',
            forceSelection: true,
            allowBlank: false,
            plugins: [{
                ptype: 'comboaddnew',
                url: '/services/ssl/certificates/create'
            }],
            getRequestParams: function() {
                return {url: '/services/ssl/certificates/xListCertificates'};
            },
            listeners: {
                addnew: function(item) {
                    this.up('#farmbuilder').cache.setExpired(this.getRequestParams());
                },
                expand: function() {
                    var me = this,
                        cache = me.up('#farmbuilder').cache,
                        data = cache.get(me.getRequestParams());
                    if (!me.dataLoaded || data.expired === true) {
                        me.collapse();
                        me.store.removeAll();
                        this.up('#farmbuilder').cache.load(
                            me.getRequestParams(),
                            function(data, status){
                                if (!status) return;
                                me.store.loadData(data || []);
                                me.dataLoaded = true
                                me.expand();
                            },
                            this,
                            0
                        );
                    }
                }
            }
        }]
    }
});
