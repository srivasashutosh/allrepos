Scalr.regPage('Scalr.ui.farms.builder.addrole.proxy', function () {
    return {
        xtype: 'container',
        isExtraSettings: true,
        hidden: true,

        cls: 'x-delimiter-top',
        padding: '18 0',
        
        defaults: {
            padding: '0 24'
        },
        
        isVisibleForRole: function(record) {
            return Ext.Array.contains(record.get('behaviors', true), 'www');
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
        },

        isValid: function() {
            return true;
        },

        getSettings: function() {
            return {};
        },

        items: [{
            xtype: 'label',
            cls: 'x-fieldset-subheader',
            text: 'Proxy settings'
        },{
            xtype: 'proxysettings'
        }]
    }
});


Ext.define('Scalr.ui.ProxySettingsField', {
	extend: 'Ext.container.Container',
	mixins: {
		field: 'Ext.form.field.Field'
	},
	alias: 'widget.proxysettings',
	layout: 'anchor',
    padding: '0 16 10',
    
	initComponent : function() {
		var me = this;
		me.callParent();
		me.initField();
        
        /*me.down('#ct').on({
            remove: function() {
                if (this.items.length === 0) {
                    this.add({xtype: 'proxysettingsitem'});
                }
            }
        });*/
	},

	getValue: function() {
	},

	setValue: function(value) {
		var ct = this.down('#ct');

		ct.suspendLayouts();
		this.removeItems();
        this.addItem();
		ct.resumeLayouts(true);
	},
    
    defaults: {
        anchor: '100%'
    },
    
    removeItems: function() {
        this.down('#ct').removeAll();
    },
    
    addItem: function(config) {
        var me = this,
            ct = this.down('#ct');
        ct.add(Ext.apply({
            xtype: 'proxysettingsitem', 
            toggleGroup: me.getId(),
            listeners: {
                configure: function(item, state) {
                    me.toggleEditItem(item, state);
                }
            }
        }, config));
    },
    
    toggleEditItem: function(item, edit) {
        var form = this.down('#edit'),
            rightcol = this.up('#rightcol'),
            scrollTop = rightcol.el.getScroll().top;
        form.currentItem = edit ? item : null;
        form.setVisible(edit);
        rightcol.el.scrollTo('top', scrollTop);
    },
    
	items: [{
        xtype: 'container',
        itemId: 'ct',
        layout: 'anchor',
        listeners: {
            beforeremove: function(comp, item) {
                var c = comp.up('proxysettings');
                if (c.down('#edit').currentItem === item) {
                    c.toggleEditItem(item, false);
                }
            }
        }
    },{
        xtype: 'btn',
        baseCls: 'x-button-icon',
        cls: 'x-button-ghost x-button-ghost-add',
        handler: function() {
            this.up('proxysettings').addItem();
        }
    },{
        xtype: 'container',
        itemId: 'edit',
        cls: 'scalr-ui-inlineform',
        margin: '20 -24 -10',
        hidden: true,
        padding: 20,
        layout: 'anchor',
        defaults: {
            anchor: '100%'
        },
        addItem: function(config) {
            var ct = this.down('#ct1');
            ct.add(Ext.apply({
                xtype: 'proxysettingsitemrule'
            }, config));
        },
        listeners: {
            show: function(){
                if (this.down('#ct1').items.length === 0) {
                    this.addItem();
                }
            }
        },
        items: [{
            xtype: 'container',
            layout: 'hbox',
            cls: 'x-grid-header-ct',
            style: 'border-radius: 3px 3px 0 0',
            items: [{
                xtype: 'component',
                html: '<div class="x-column-header-inner"><span class="x-column-header-text">URI</span></div>',
                cls: 'x-column-header x-column-header-first',
                flex: .6
            },{
                xtype: 'component',
                html: '<div class="x-column-header-inner"><span class="x-column-header-text">Destination</span></div>',
                cls: 'x-column-header',
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
            itemId: 'ct1',
            layout: 'anchor',
            cls: 'scalr-ui-proxy-settings-container',
            updateItemsBg: function(){
                this.items.each(function(item, index){
                    item[index % 2 ? 'addCls' : 'removeCls']('scalr-ui-rule-alt');
                });
            },
            listeners: {
                add: function() {
                    this.updateItemsBg();
                },
                remove: function() {
                    this.updateItemsBg();
                }
            }
        },{
            xtype: 'btn',
            baseCls: 'x-button-icon',
            cls: 'x-button-add',
            handler: function() {
                this.up('container').addItem();
            }
        },{
            xtype: 'container',
            layout: 'hbox',
            margin: '20 0 0',
            cls: 'scalr-ui-proxy-settings',
            defaults: {
                margin: '0 20 0 0'
            },
            items: [{
                xtype: 'btnfield',
                name: 'iphash',
                cls: 'x-button-text-dark',
                text: 'IP hash',
                enableToggle: true,
                submitValue: false
            },{
                xtype: 'checkbox',
                name: 'ssl',
                boxLabel: 'SSL',
                boxLabelCls: 'scalr-ui-label-large',
                submitValue: false,
                listeners: {
                    change: function(comp, checked) {
                        comp.next().setVisible(checked);
                    }
                }
            },{
                xtype: 'container',
                itemId: 'ssloptions',
                layout: 'hbox',
                hidden: true,
                margin:0,
                flex: 1,
                defaults: {
                    margin: '0 13 0 0'
                },
                items: [{
                    xtype: 'combo',
                    name: 'sslCertId',
                    submitValue: false,
                    flex: 1,
                    store: {
                        fields: [ 'id', 'name' ],
                        data: []
                    },
                    emptyText: 'Choose certificate',
                    valueField: 'id',
                    displayField: 'name',
                    forceSelection: true,
                    allowBlank: false,
                    //disabled: !moduleParams['vhost']['isSslEnabled'],
                    plugins: [{
                        ptype: 'comboaddnew',
                        url: '/services/ssl/certificates/create'
                    }]
                },{
                    xtype: 'textfield',
                    name: 'sslPort',
                    submitValue: false,
                    fieldLabel: 'HTTPS port',
                    labelWidth: 80,
                    width: 135,
                    value: 443
                },{
                    xtype: 'btnfield',
                    name: 'sslHttpHttps',
                    submitValue: false,
                    cls: 'x-button-text-dark',
                    text: 'HTTP &rarr; HTTPS',
                    enableToggle: true,
                    submitValue: false,
                    margin: 0
                }]
            }]
        }]
    }]
});

Ext.define('Scalr.ui.ProxySettingsFieldItem', {
	extend: 'Ext.container.Container',
    alias: 'widget.proxysettingsitem',
    margin: '0 0 10 0',
    layout: {
        type: 'hbox',
        align: 'middle'
    },
    
    rules: [],
    
	initComponent : function() {
		var me = this;
        me.callParent();
        me.add([{
            xtype: 'textfield',
            name: 'hostname',
            submitValue: false,
            emptyText: 'hostname',
            flex: 1
        },{
            xtype: 'label',
            text: ' : ',
            padding: '0 6'
        },{
            xtype: 'textfield',
            name: 'post',
            submitValue: false,
            emptyText: 'port',
            value: 80,
            width: 55
        },{
            xtype: 'btn',
            itemId: 'configure',
            cls: 'x-button-text-dark',
            enableToggle: true,
            text: 'Configure',
            toggleGroup: me.toggleGroup,
            toggleHandler: function(comp, state) {
                var item = comp.up('proxysettingsitem');
                if (state === true || !Ext.ButtonToggleManager.getPressed(comp.toggleGroup)) {
                    item.fireEvent('configure', item, state);
                }
            },
            disableMouseDownPressed: true,
            width: 110,
            margin: '0 0 0 13'
        },{
            xtype: 'btn',
            itemId: 'delete',
            margin: '0 0 0 8',
            cls: 'scalr-ui-btn-delete',
            baseCls: 'x-button',
            handler: function() {
                var item = this.up('proxysettingsitem');
                item.ownerCt.remove(item);
            }
        }]);
		
	}
});

Ext.define('Scalr.ui.ProxySettingsFieldItemRule', {
	extend: 'Ext.container.Container',
    alias: 'widget.proxysettingsitemrule',
    layout: 'hbox',
    cls: 'scalr-ui-rule',
    rules: [],
    
	initComponent : function() {
		var me = this;
        me.callParent();
        me.add([{
            xtype: 'textfield',
            name: 'uri',
            fieldLabel: '/',
            labelSeparator: '',
            labelWidth: 6,
            submitValue: false,
            flex: .44,
            padding: '0 8 0 0'
        },{
            xtype: 'container',
            itemId: 'destinations',
            layout: 'anchor',
            defaults: {
                anchor: '100%'
            },
            flex: 1,
            padding: '0 0 0 8',
            updateItems: function(){
                var me = this;
                me.items.each(function(item, index){
                    item.down('#add').setVisible(index === 0);
                    item.down('#delete').setVisible(index !== 0);
                });
            },
            listeners: {
                add: function() {
                    this.updateItems();
                },
                remove: function() {
                    this.updateItems();
                }
            }
        },{
            xtype: 'btn',
            itemId: 'delete',
            margin: '0 0 0 18',
            cls: 'scalr-ui-btn-delete',
            baseCls: 'x-button',
            handler: function() {
                var item = this.up('proxysettingsitemrule');
                item.ownerCt.remove(item);
            }
        }]);
        
        me.addDestination(true);
	},
    
    addDestination: function(first) {
        var ct = this.down('#destinations'),
            item;
        item = ct.add({
            xtype: 'container',
            layout: 'hbox',
            margin: '0 0 5 0',
            items:[{
                xtype: 'combo',
                name: 'type',
                value: 'ip',
                store: [
                    ['ip', 'IP'],
                    ['role', 'Role']
                ],
                width: 65,
                margin: '0 5 0 0',
                listeners: {
                    change: function(comp, value) {
                        var ct = comp.up('container');
                        ct.down('[name="ip"]').setVisible(value === 'ip');
                        ct.down('[name="role"]').setVisible(value === 'role');
                    }
                }
            },{
                xtype: 'combo',
                name: 'role',
                emptyText: 'Select role',
                store: [],
                hidden: true,
                flex: 1 
            },{
                xtype: 'textfield',
                name: 'ip',
                emptyText: 'IP address',
                flex: 1
            },{
                xtype: 'textfield',
                name: 'port',
                emptyText: 'port',
                fieldLabel: ':',
                labelSeparator: '',
                labelWidth: 4,
                margin: '0 4',
                width: 50,
                value: 80
            },{
                xtype: 'btn',
                itemId: 'add',
                cls: 'scalr-ui-btn-plus',
                baseCls: 'x-button-dummy',
                margin: '0 12 0 0',
                handler: function() {
                    this.up('proxysettingsitemrule').addDestination();
                }
            },{
                xtype: 'btn',
                itemId: 'delete',
                cls: 'scalr-ui-btn-minus',
                baseCls: 'x-button-dummy',
                margin: '0 12 0 0',
                handler: function() {
                    var item = this.up('container');
                    item.ownerCt.remove(item);
                }
            }]
        });
        if (!first) {
            item.add([{
                xtype: 'btnfield',
                cls: 'scalr-ui-proxy-rule-backup',
                baseCls: 'x-button-icon',
                tooltip: 'Backup',
                margin: '0 0 0 18',
                name: 'backup',
                inputValue: 1,
                enableToggle: true,
                submitValue: false
            },{
                xtype: 'btnfield',
                cls: 'scalr-ui-proxy-rule-down',
                baseCls: 'x-button-icon',
                tooltip: 'Down',
                margin: '0 0 0 6',
                name: 'backup',
                inputValue: 1,
                enableToggle: true,
                submitValue: false
            }])
        } else {
            item.add({
                xtype: 'component',
                width: 72
            });
        }
    }
});