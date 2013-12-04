Scalr.regPage('Scalr.ui.farms.builder.tabs.haproxy', function (moduleTabParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'HAProxy settings',
		itemId: 'haproxy',

		isEnabled: function (record) {
			return record.get('behaviors').match("haproxy");
		},

		getDefaultValues: function (record) {
			return {
				'haproxy.port': 80,
				'haproxy.healthcheck.interval': 30,
				'haproxy.healthcheck.fallthreshold': 5,
                'haproxy.healthcheck.risethreshold': 3
			};
		},

		showTab: function (record) {
			var me = this,
                settings = record.get('settings'),
                hp = me.down('haproxysettings');

            hp.roles = [];
			moduleTabParams.farmRolesStore.each(function(r){
				if (r != record) {
					hp.roles.push({id: r.get('farm_role_id'), name: r.get('name')});
                }
			});

            hp.setValue({
                'haproxy.port': settings['haproxy.port'] || 80,
                'haproxy.healthcheck.interval': settings['haproxy.healthcheck.interval'] || 5,
                'haproxy.healthcheck.fallthreshold': settings['haproxy.healthcheck.fallthreshold'] || 5,
                'haproxy.healthcheck.risethreshold': settings['haproxy.healthcheck.risethreshold'] || 3,
                backends: Ext.decode(settings['haproxy.backends']) || []
            });
		},

		hideTab: function (record) {
			var settings = record.get('settings');

            Ext.apply(settings, this.down('haproxysettings').getValue());
            settings['haproxy.backends'] = Ext.encode(settings['haproxy.backends']);
			record.set('settings', settings);
		},

		items: [{
            xtype: 'fieldset',
            items: {
                xtype: 'haproxysettings',
                defaults: {
                    maxWidth: 620
                },
                margin: '0 0 18 0'
            }
		}]
	});
});

Ext.define('Scalr.ui.HaproxySettingsField', {
	extend: 'Ext.container.Container',
	alias: 'widget.haproxysettings',
	layout: 'anchor',
    cls: 'x-grid-shadow',
    
    setValue: function(value){
        var me = this,
            ct = me.down('#backends');
        value = value || {};
        if (me.rendered) {
            ct.suspendLayouts();
            ct.removeAll();
            me.setFieldValues(value);
            if (Ext.isArray(value.backends)){
                Ext.Array.each(value.backends, function(item){
                    me.addBackend(item);
                });
            }
            if (!value.backends || value.backends.length === 0) {
                me.addBackend();
            }
            ct.resumeLayouts(true);
        }
    },
    
    getValue: function(){
        var value = {
            'haproxy.port': this.down('[name="haproxy.port"]').getValue(),
            'haproxy.healthcheck.interval': this.down('[name="haproxy.healthcheck.interval"]').getValue(),
            'haproxy.healthcheck.fallthreshold': this.down('[name="haproxy.healthcheck.fallthreshold"]').getValue(),
            'haproxy.healthcheck.risethreshold': this.down('[name="haproxy.healthcheck.risethreshold"]').getValue(),
            'haproxy.backends': []
        };
        this.down('#backends').items.each(function(item){
            var data = {},
                type = item.down('[name="haproxy.type"]').getValue();
            data[type] = item.down('[name="haproxy.' + type + '"]').getValue();
            if (data[type]) {
                data['port'] = item.down('[name="haproxy.port"]').getValue();
                data['backup'] = item.down('[name="haproxy.backup"]').getValue();
                data['down'] = item.down('[name="haproxy.down"]').getValue();
                value['haproxy.backends'].push(data);
            }
        });
        
        return value;
    },
    
    addBackend: function(data) {
        var ct = this.down('#backends'),
            item;
        data = data || {};
        item = ct.add({
            xtype: 'container',
            layout: 'hbox',
            cls: 'item',
            items:[{
                xtype: 'buttongroupfield',
                name: 'haproxy.type',
                defaults: {
                    width: 50
                },
                items: [{
                    value: 'ip',
                    text: 'IP'
                },{
                    value: 'farm_role_id',
                    text: 'Role'
                }],
                margin: '0 10 0 3',
                listeners: {
                    change: function(comp, value) {
                        var ct = comp.up('container');
                        ct.down('[name="haproxy.ip"]').setVisible(value === 'ip');
                        ct.down('[name="haproxy.farm_role_id"]').setVisible(value === 'farm_role_id');
                    }
                }
            },{
                xtype: 'combo',
                name: 'haproxy.farm_role_id',
                emptyText: 'Select role',
                store: {
                    fields: [ 'id', 'name' ],
                    proxy: 'object',
                    data: this.roles
                },
                valueField: 'id',
                displayField: 'name',
                editable: false,
                allowBlank: false,
                queryMode: 'local',
                flex: 1,
                hidden: true
            },{
                xtype: 'textfield',
                name: 'haproxy.ip',
                emptyText: 'IP address',
                allowBlank: false,
                maskRe: new RegExp('[0123456789.]', 'i'),
                flex: 1
            },{
                xtype: 'textfield',
                name: 'haproxy.port',
                emptyText: 'port',
                maskRe: new RegExp('[0123456789]', 'i'),
                allowBlank: false,
                fieldLabel: ':',
                labelSeparator: '',
                labelWidth: 4,
                margin: '0 4',
                width: 80
            },{
                xtype: 'btnfield',
                cls: 'scalr-ui-proxy-rule-backup',
                baseCls: 'x-button-icon',
                tooltip: 'Backup',
                margin: '0 0 0 18',
                name: 'haproxy.backup',
                inputValue: 1,
                enableToggle: true,
                submitValue: false
            },{
                xtype: 'btnfield',
                cls: 'scalr-ui-proxy-rule-down',
                baseCls: 'x-button-icon',
                tooltip: 'Down',
                margin: '0 0 0 6',
                name: 'haproxy.down',
                inputValue: 1,
                enableToggle: true,
                submitValue: false
            },{
                xtype: 'btn',
                itemId: 'delete',
                margin: '0 0 0 18',
                cls: 'scalr-ui-btn-delete',
                baseCls: 'x-button',
                handler: function() {
                    var item = this.up('container');
                    item.ownerCt.remove(item);
                }
            }]
        });
        
        item.setFieldValues({
            'haproxy.type': data.farm_role_id !== undefined ? 'farm_role_id' : 'ip',
            'haproxy.farm_role_id': data.farm_role_id,
            'haproxy.ip': data.ip,
            'haproxy.port': data.port || 80,
            'haproxy.backup': data.backup,
            'haproxy.down': data.down
        });
    },
    
    items: [{
        xtype: 'textfield',
        name: 'haproxy.port',
        fieldLabel: 'Port',
        allowBlank: false,
        labelWidth: 40,
        width: 120
    },{
        xtype: 'container',
        layout: 'hbox',
        cls: 'x-grid-header-ct',
        margin: '18 0 0',
        style: 'border-radius: 3px 3px 0 0',
        items: [{
            xtype: 'component',
            html: '<div class="x-column-header-inner"><span class="x-column-header-text">Backends</span></div>',
            cls: 'x-column-header x-column-header-first',
            flex: 1
        },{
            xtype: 'component',
            html: '<div class="x-column-header-inner"><span class="x-column-header-text">Settings</span></div>',
            width: 76,
            cls: 'x-column-header'
        },{
            xtype: 'component',
            cls: 'x-column-header x-column-header-last',
            width: 36
        }]
    },{
        xtype: 'container',
        cls: 'scalr-ui-striped-list',
        itemId: 'backends',
        layout: 'anchor',
        defaults: {
            anchor: '100%'
        },
        updateItemsCls: function(){
            this.items.each(function(item, index){
                item[index % 2 ? 'addCls' : 'removeCls']('item-alt');
            });
        },
        listeners: {
            add: function() {
                this.updateItemsCls();
            },
            remove: function() {
                this.updateItemsCls();
            }
        }
        
    },{
        xtype: 'btn',
        anchor: '100%',
        baseCls: 'x-button-icon',
        cls: 'x-button-add-item-big',
        margin: '0 0 12 0',
        handler: function() {
            this.up('haproxysettings').addBackend();
        }
    },{
        xtype: 'component',
        cls: 'x-fieldset-delimiter-large'
    },{
        xtype: 'label',
        cls: 'x-fieldset-subheader',
        text: 'Health check'
    },{
        xtype: 'container',
        layout: 'hbox',
        items: [{
            xtype: 'textfield',
            name: 'haproxy.healthcheck.interval',
            allowBlank: false,
            fieldLabel: 'Interval ',
            flex: 1,
            labelWidth: 60,
            minWidth: 100,
            maxWidth: 120
        },{
            xtype: 'displayfield',
            margin: '0 0 0 3',
            value: 'sec'
        },{
            xtype: 'displayinfofield',
            margin: '0 40 0 5',
            info:   'The approximate interval (in seconds) between health checks of an individual instance.<br />The default is 30 seconds and a valid interval must be between 5 seconds and 600 seconds.' +
                    'Also, the interval value must be greater than the Timeout value'
        },{
            xtype: 'textfield',
            name: 'haproxy.healthcheck.fallthreshold',
            allowBlank: false,
            fieldLabel: 'Fall threshold',
            flex: 1,
            labelWidth: 90,
            minWidth: 110,
            maxWidth: 150
        },{
            xtype: 'displayinfofield',
            margin: '0 40 0 5',
            info:   'The number of consecutive health probe failures that move the instance to the unhealthy state.<br />The default is 5 and a valid value lies between 2 and 10.'
        },{
            xtype: 'textfield',
            name: 'haproxy.healthcheck.risethreshold',
            allowBlank: false,
            fieldLabel: 'Rise threshold',
            flex: 1,
            labelWidth: 90,
            minWidth: 110,
            maxWidth: 150
        },{
            xtype: 'displayinfofield',
            margin: '0 0 0 5',
            info:   'The number of consecutive health probe successes required before moving the instance to the Healthy state.<br />The default is 3 and a valid value lies between 2 and 10.'
        }]
    }]
});