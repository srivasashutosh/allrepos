Ext.define('Scalr.ui.FarmBuilderRoleEdit', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.farmroleedit',

	layout: {
		type: 'vbox',
        align: 'stretch'
	},
	currentRole: null,
    cls: 'scalr-ui-farmbuilder-roleedit',
    bodyCls: 'x-panel-body-frame-dark',
    items: [{
        xtype: 'panel',
        flex: 1,
        layout: 'card',
        itemId: 'tabspanel',
        margin: '0 0 12 0',
        dockedItems: [{
            dock: 'left',
            width: 200,
            border: false,
            cls: 'scalr-ui-farmbuilder-tabs',
            autoScroll: true,
            itemId: 'tabs'
        }],

        onAdd: function (cmp) {
            cmp.tabButton = this.getDockedComponent('tabs').add({
                xtype: 'btn',
                text: cmp.tabTitle + (cmp.deprecated ? '<span class="deprecated">deprecated</span>' : ''),
                toggleGroup: 'tabs',
                allowDepress: false,
                tabCmp: cmp,
                cls: cmp.deprecated ? 'scalr-ui-farmbuilder-button-deprecated' : '',
                handler: function (b) {
                    this.layout.setActiveItem(b.tabCmp);
                },
				disableMouseDownPressed: true,
                scope: this
            });
        }
    }],
    
    onTabActivate: function(tab) {
        this.fireEvent('tabactivate', tab);
    },
    
    onTabDeactivate: function(tab) {
        this.fireEvent('tabdeactivate', tab);
    },

    addRoleDefaultValues: function (record) {
		var settings = record.get('settings');

		this.down('#tabspanel').items.each(function(item) {
			if (item.isActive(record)) {
				Ext.applyIf(settings, item.getDefaultValues(record));
            }
		});
        Ext.applyIf(settings, this.down('#maintab').getDefaultValues(record));
        
		record.set('settings', settings);
	},

	setCurrentRole: function (record) {
		this.currentRole = record;
	},

    createTabs: function(moduleParams) {
        var tabsPanel = this.down('#tabspanel');
        this.tabs = [];
        this.tabs.push(this.insert(0, Scalr.cache['Scalr.ui.farms.builder.tabs.main'](moduleParams['tabParams'])));
        for (var i = 0; i < moduleParams.tabs.length; i++) {
            this.tabs.push(tabsPanel.add(Scalr.cache['Scalr.ui.farms.builder.tabs.' + moduleParams.tabs[i]](moduleParams['tabParams'])));
        }
    },
    
    setActiveTab: function(id) {
        var ct = this.getComponent('tabspanel'), 
            tab = ct.getComponent(id);
        if (tab) {
            ct.getDockedComponent('tabs').items.each(function(item) {
                if (item.tabCmp === tab) {
                    item.toggle(true);
                    ct.layout.setActiveItem(tab);
                    return false;
                }
            });
        }
    },
    
    initComponent: function() {
        this.callParent(arguments);
        this.on({
            beforeactivate: function () {
                var me = this,
                    record = me.currentRole;
                this.suspendLayouts();
                if (record.get('is_bundle_running') == true) {
                    Scalr.message.Error('This role is locked by server snapshot creation process. Please wait till snapshot will be created.');
                    return false;
                } else {
                    var activate = true,
                        firstTab, lastTab;
                    for (var i=0, len=this.tabs.length; i<len; i++) {
                        activate = this.tabs[i].setCurrentRole(record, activate);
                        if (this.tabs[i].tabButton !== undefined) {
                            if (this.tabs[i].tabButton.isVisible()) {
                                if (firstTab === undefined) {
                                    firstTab = this.tabs[i].tabButton;
                                }
                                lastTab = this.tabs[i].tabButton;
                                this.tabs[i].tabButton.removeCls('scalr-ui-farmbuilder-button-first scalr-ui-farmbuilder-button-last');
                            }
                        }
                    }
                    if (firstTab !== undefined) {
                        firstTab.addCls('scalr-ui-farmbuilder-button-first');
                    }
                    if (lastTab !== undefined) {
                        lastTab.addCls('scalr-ui-farmbuilder-button-last');
                    }
                }
                this.resumeLayouts(true);
            },

            deactivate: function () {
                var tabsPanel = this.down('#tabspanel');
                if (tabsPanel.layout.activeItem) {
                    tabsPanel.layout.activeItem.hide();
                    tabsPanel.layout.activeItem.fireEvent('deactivate', tabsPanel.layout.activeItem);
                    tabsPanel.layout.activeItem = null;
                }
            }
        });
	}
});

Scalr.regPage('Scalr.ui.farms.builder.tabs.main', function (tabParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		itemId: 'maintab',
        
        layout: {
            type: 'hbox',
            align: 'stretch'
        },
        margin: '0 0 12 12',
        cls: 'scalr-ui-farmbuilder-roleedit-tab scalr-ui-farmbuilder-roleedit-maintab',
        bodyCls: 'x-panel-body-frame x-panel-body-plain',
        isLoading: false,
        
        minified: false,
        stateful: true,
        stateId: 'scalr-ui-farmbuilder-roleedit-maintab',
        stateEvents: ['minify', 'maximize'],
        autoScroll: false,
        cache: null,
        
        listeners: {
            boxready: function() {
                var me = this;
                me.add({
                    xtype: 'btn',
                    cls: 'scalr-ui-btn-collapse',
                    baseCls: 'x-btn-base',
                    enableToggle: true,
                    disableMouseDownPressed: true,
                    margin: '17 12 0 0',
                    pressed: this.minified,
                    toggleHandler: function(btn) {
                        me.toggleMinified();
                    }
                });
            },
            staterestore: function() {
                this.setMinified(this.minified);
            }
        },
        
        getState: function() {
            return {
                minified: this.minified
            }
        },
        
        toggleMinified: function() {
            this.minified ? this.maximize() : this.minify();
        },
        
        minify: function(){
            this.setMinified(true);
            this.fireEvent('minify');
        },
        
        maximize: function() {
            this.setMinified(false);
            this.fireEvent('maximize');
        },
        
        setMinified: function(minified) {
            this.suspendLayouts();
            Ext.Array.each(this.query('[hideOnMinify]'), function(item){
                item.setVisible(!minified && !item.forceHidden);
            });
            this.resumeLayouts(true);
            this.setHeight(minified ? 54 : null);
            this.minified = minified;
        },
        
        setCurrentRole: function (record, activate) {
            this.currentRole = record;
            this.fireEvent('activate', this, this);
            return true;
        },
        
		getDefaultValues: function (record) {
			switch (record.get('platform')) {
                case 'ec2':
                    return {
                        'aws.availability_zone': '',
                        'aws.instance_type': record.get('arch') == 'i386' ? 'm1.small' : 'm1.large'
                    };
                break;
                case 'rackspace':
                    return {
                        'rs.flavor-id': 1
                    };
                break;
                case 'cloudstack':
                case 'idcf':
                case 'ucloud':
                    return {
                        'cloudstack.service_offering_id': ''
                    };
                break;
                case 'gce':
                    return {
                        'gce.machine-type': ''
                    };
                break;
                default:
                    return {};
                break;
            }
		},

		beforeShowTab: function (record, handler) {
            this.down('#mainroletitle').setText('<div style="float:left;margin:2px 10px 0 0" class="scalr-ui-icon-platform-small scalr-ui-icon-platform-small-' + record.get('platform') + '"></div>' + record.get('name'), false);
            if (this.callPlatformHandler('beforeShowTab', arguments) === false) {
                handler();
            }
		},
		
        onRoleUpdate: function(record, name, value, oldValue) {
            if (this.suspendOnRoleUpdate > 0) {
                return;
            }
            
            var fullname = name.join('.'), 
                comp;
            if (fullname === 'settings.scaling.min_instances') {
                comp = this.down('[name="min_instances"]');
            } else if (fullname === 'settings.scaling.max_instances') {
                comp = this.down('[name="max_instances"]');
            } else if (fullname === 'settings.scaling.enabled') {
                this.down('#mainscaling').setScalingDisabled(value != 1);
            } else if (fullname === 'scaling') {
                this.down('#mainscalinggrid').loadMetrics(value);
            } else if (fullname === 'settings.db.msr.data_storage.engine') {
                if (record.get('platform') === 'gce') {
                    this.gce.refreshMachineType.call(this, record, null, value);
                }
            }
            
            if (comp) {
                comp.suspendEvents(false);
                comp.setValue(value);
                comp.resumeEvents();
            }
        },

		showTab: function (record) {
            var me = this,
                settings = record.get('settings', true),
                platform = record.get('platform'),
                arch = record.get('arch'),
                roleBehaviors = record.get('behaviors').split(','),
                behaviors = [];
            me.isLoading = true;
            
            Ext.Array.each(roleBehaviors, function(b) {
               behaviors.push(tabParams['behaviors'][b] || b); 
            });
            
            behaviors = behaviors.join(', ');
            if (behaviors.length > 26){
                behaviors = '<span title="' + Ext.htmlEncode(behaviors)+ '">' + behaviors.substr(0, 26) + '...</span>';
            }
            me.setFieldValues({
                os: record.get('os') + (!Ext.isEmpty(arch) ? ' (' + (arch == 'i386' ? '32' : '64') + 'bit)' : ''),//'<div style="float:left" class="scalr-ui-icon-osfamily-small scalr-ui-icon-osfamily-small-' + record.get('os_family') + '"></div>&nbsp;' + 
                behaviors: behaviors,
                min_instances: settings['scaling.min_instances'] || 1,
                max_instances: settings['scaling.max_instances'] || 2,
                running_servers: record.get('running_servers') || 0
            });
            me.down('#cloud_location').selectLocation(platform, record.get('cloud_location'));
            
            me.suspendLayouts();
            Ext.Array.each(['#column1', '#column2'], function(itemId){
                me.down(itemId).items.each(function(comp) {
                    comp.setVisible(Ext.Array.contains(comp.platform, platform));
                });
            });
            me.resumeLayouts(true);
            
            var scalingTab = me.up('farmroleedit').down('#scaling'),
                topScalingTab = me.down('#mainscaling'),
                isVpcRouter = Ext.Array.contains(roleBehaviors, 'router');
            topScalingTab.down('#mainscalinggrid').loadMetrics(record.get('scaling'));
            if (scalingTab.isActive(record) && settings['scaling.enabled'] == 1 || isVpcRouter) {
                topScalingTab.setScalingDisabled(false);
                if (isVpcRouter) {
                    topScalingTab.down('[name="max_instances"]').setReadOnly(true);
                    topScalingTab.down('[name="min_instances"]').setReadOnly(true);
                } else {
                    var readonly = scalingTab.isTabReadonly(record),
                        isCfRole = (Ext.Array.contains(roleBehaviors, 'cf_cloud_controller') || Ext.Array.contains(roleBehaviors, 'cf_health_manager'));
                    topScalingTab.down('[name="max_instances"]').setReadOnly(readonly);
                    topScalingTab.down('[name="min_instances"]').setReadOnly(readonly && (isCfRole || !record.get('new')));
                }
            } else {
                topScalingTab.setScalingDisabled(true);
            }

            me.callPlatformHandler('showTab', arguments);
            
            me.isLoading = false;
		},
        
		onParamChange: function (name, value) {
            var record = this.currentRole;
            if (record && !this.isLoading) {
                this.suspendOnRoleUpdate++;
                switch (name) {
                    case 'min_instances':
                    case 'max_instances':
                        var settings = record.get('settings');
                        settings['scaling.' + name] = value;
                        record.set('settings', settings);
                    break;
                    default:
                        this.callPlatformHandler('saveParam', arguments);
                    break;
                }
                this.suspendOnRoleUpdate--;
            }
		},
        
        callPlatformHandler: function(method, args) {
            var handler = this.currentRole.get('platform');
            if (Ext.Array.contains(['rackspacengus', 'rackspacenguk'], handler)) {
                handler = 'openstack';
            } else if (Ext.Array.contains(['idcf', 'ucloud'], handler)) {
                handler = 'cloudstack';
            }
            if (this[handler] && this[handler][method]) {
                this[handler][method].apply(this, args);
            } else {
                return false;
            }
        },
		
        ec2: {
            beforeShowTab: function(record, handler) {
                this.cache = null;
                if (this.up('#fbcard').down('#farm').getVpcSettings() !== false || Ext.Array.contains(record.get('behaviors').split(','), 'router')) {
                    this.down('[name="aws.availability_zone"]').hide();
                    this.down('[name="aws.cloud_location"]').setValue(record.get('cloud_location')).show();
                    handler();
                } else {
                    Scalr.cachedRequest.load(
                        {
                            url: '/platforms/ec2/xGetAvailZones',
                            params: {cloudLocation: record.get('cloud_location')}
                        },
                        function(data, status, cacheId){
                            this.cache = data;
                            this.down('[name="aws.availability_zone"]').show().setDisabled(!status);
                            this.down('[name="aws.cloud_location"]').hide();
                            handler();
                        },
                        this
                    );
                }
            },
            showTab: function(record) {
                var settings = record.get('settings', true),
                    instType = record.getEc2InstanceType(),
                    field;

                //instance type
                field = this.down('[name="aws.instance_type"]');
                field.store.load({data: instType.list});
                field.setValue(instType.value);

                //availability zone
                field = this.down('[name="aws.availability_zone"]');
                var zones = Ext.Array.map(this.cache || [], function(item){ item.disabled = item.state != 'available'; return item;}),
                    data = [{ 
                        id: 'x-scalr-diff', 
                        name: 'Distribute equally' 
                    },{ 
                        id: '', 
                        name: 'AWS-chosen' 
                    },{ 
                        id: 'x-scalr-custom', 
                        name: 'Selected by me',
                        items: zones
                    }],
                    zone = settings['aws.availability_zone'] || '',
                    disableAvailZone =  record.get('behaviors').match('mysql') && settings['mysql.data_storage_engine'] == 'ebs' &&
                                        settings['mysql.master_ebs_volume_id'] != '' && settings['mysql.master_ebs_volume_id'] != undefined &&
                                        record.get('generation') != 2 && this.down('[name="aws.availability_zone"]').getValue() != '' &&
                                        this.down('[name="aws.availability_zone"]').getValue() != 'x-scalr-diff';
                    
                field.store.loadData(data);
                if (zone.match(/x-scalr-custom/)) {
                    zone = {id: 'x-scalr-custom', items: zone.replace('x-scalr-custom=', '').split(':')};
                } else if (!Ext.isEmpty(zone) && zone !== 'x-scalr-diff' && zone != 'x-scalr-custom') {
                    zone = {id: 'x-scalr-custom', items: [zone]};
                }

                field.setValue(zone);
                if (!field.disabled) {
                    field.setDisabled(disableAvailZone);
                }
                this.down('#aws_availability_zone_warn').setVisible(disableAvailZone && field.isVisible());
            },
            saveParam: function(name, value) {
                var record = this.currentRole,
                    settings = record.get('settings');
                switch (name) {
                    case 'aws.availability_zone':
                        if (Ext.isObject(value)) {
                            if (value.items) {
                                if (value.items.length === 1) {
                                    settings[name] = value.items[0];
                                } else if (value.items.length > 1) {
                                    settings[name] = value.id + '=' + value.items.join(':');
                                }
                            }
                        } else {
                            settings[name] = value;
                        }
                    break;
                    case 'aws.instance_type':
                        settings['aws.instance_type'] = value;
                    break;
                }
                record.set('settings', settings);
            
            }
        },
        
        rackspace: {
            beforeShowTab: function(record, handler) {
                Scalr.cachedRequest.load(
                    {
                        url: '/platforms/rackspace/xGetFlavors',
                        params: {cloudLocation: record.get('cloud_location')}
                    },
                    function(data, status){
                        var field = this.down('[name="rs.flavor-id"]');
                        field.setDisabled(!status);
                        field.store.load({ data: data || [] });
                        handler();
                    },
                    this
                );
            },
            showTab: function(record) {
                var settings = record.get('settings', true);
                this.down('[name="rs.flavor-id"]').setValue(settings['rs.flavor-id']*1 || 1);
                this.down('[name="rs.cloud_location"]').setValue(record.get('cloud_location'));
            },
            saveParam: function(name, value) {
                var record = this.currentRole,
                    settings = record.get('settings');
                settings[name] = value;
                record.set('settings', settings);
            }
        },
        
        openstack: {
            beforeShowTab: function(record, handler) {
                Scalr.cachedRequest.load(
                    {
                        url: '/platforms/openstack/xGetOpenstackResources',
                        params: {
                            cloudLocation: record.get('cloud_location'), 
                            platform: record.get('platform')
                        }
                    },
                    function(data, status){
                        var field = this.down('[name="openstack.flavor-id"]');
                        field.setDisabled(!status);
                        field.store.load({ data:  data ? data['flavors'] : []});
                        handler();
                    },
                    this
                );
            },
            showTab: function(record) {
                var settings = record.get('settings', true),
                    field = this.down('[name="openstack.flavor-id"]'),
                    defaultValue;
                
                if (field.store.getCount() > 0) {
                    defaultValue = field.store.getAt(0).get('id');
                }
                field.setValue(!Ext.isEmpty(settings['openstack.flavor-id']) ? settings['openstack.flavor-id']*1 : defaultValue);
                this.down('[name="openstack.cloud_location"]').setValue(record.get('cloud_location'));
            },
            saveParam: function(name, value) {
                var record = this.currentRole,
                    settings = record.get('settings');
                settings[name] = value;
                record.set('settings', settings);
            }
        },

        cloudstack: {
            beforeShowTab: function(record, handler) {
                Scalr.cachedRequest.load(
                    {
                        url: '/platforms/cloudstack/xGetOfferingsList/',
                        params: {
                            cloudLocation: record.get('cloud_location'),
                            platform: record.get('platform')
                        }
                    },
                    function(data, status){
                        var field = this.down('[name="cloudstack.service_offering_id"]');
                        field.setDisabled(!status);
                        field.store.load({ data: data ? data['serviceOfferings'] : []});
                        handler();
                    },
                    this
                );
            },
            showTab: function(record) {
                var settings = record.get('settings', true),
                    field = this.down('[name="cloudstack.service_offering_id"]'),
                    defaultValue;
                if (field.store.getCount() > 0) {
                    defaultValue = field.store.getAt(0).get('id');
                }
                field.setValue(!Ext.isEmpty(settings['cloudstack.service_offering_id']) ? settings['cloudstack.service_offering_id'] : defaultValue);
                this.down('[name="cloudstack.cloud_location"]').setValue(record.get('cloud_location'));
            },
            saveParam: function(name, value) {
                var record = this.currentRole,
                    settings = record.get('settings');
                settings[name] = value;
                record.set('settings', settings);
            }
        },
        
        gce: {
            beforeShowTab: function(record, handler) {
                this.cache = null;
                Scalr.cachedRequest.load(
                    {
                        url: '/platforms/gce/xGetOptions',
                        params: {}
                    },
                    function(data, status){
                        this.cache = data;
                        this.down('[name="gce.cloud-location"]').setDisabled(!status);
                        this.down('[name="gce.machine-type"]').setDisabled(!status);
                        handler();
                    },
                    this
                );
           },
            showTab: function(record) {
                var settings = record.get('settings', true),
                    data = this.cache || {},
                    field, value;
            
                //cloud location
                field = this.down('[name="gce.cloud-location"]');
                field.store.loadData(data['zones'] || []);
                value = settings['gce.cloud-location'];
                if (Ext.isEmpty(value)) {
                    value = field.store.getAt(3);
                    value = value ? value.get('name') : '';
                } else if (value.match(/x-scalr-custom/)) {
                    value = value.replace('x-scalr-custom=', '').split(':');
                }
                field.reset();
                field.setValue(value);
                
                this.gce.refreshMachineType.call(this, record, settings['gce.machine-type'], settings['db.msr.data_storage.engine']);
            },
            refreshMachineType: function(record, machineType, storageEngine){
                var field = this.down('[name="gce.machine-type"]'),
                    data = this.cache || {},
                    value = machineType || field.getValue();
				
                if (record.isDbMsr() && (storageEngine == 'lvm' || storageEngine == 'eph' || !storageEngine)) {
                    field.store.load({ data: data['dbTypes'] || [] });
                    if (value && !field.findRecordByValue(value)) {
                        value = null;
                    }
                    field.setValue(value || 'n1-standard-1-d');
                } else {
                    field.store.load({ data: data['types'] || [] });
                    if (value && !field.findRecordByValue(value)) {
                        value = null;
                    }
                    field.setValue(value || 'n1-standard-1');
                }
            },
            saveParam: function(name, value) {
                var record = this.currentRole,
                    settings = record.get('settings');
                switch (name) {
                    case 'gce.cloud-location':
                        if (value.length === 1) {
                            settings[name] = value[0];
                        } else if (value.length > 1) {
                            settings[name] = 'x-scalr-custom=' + value.join(':');
                        } else {
                            settings[name] = '';
                        }
                    break;
                    case 'gce.machine-type':
                        settings[name] = value;
                    break;
                }
                
                record.set('settings', settings);
            }
        },

        items: [{
            xtype: 'container',
            flex: 1,
            minWidth: 320,
            maxWidth: 360,
            padding: '16 16 16 20',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            cls: 'x-delimiter-vertical',
            defaults: {
                labelWidth: 90,
                fieldBodyCls: 'align-top'
            },
            items: [{
                xtype: 'label',
                html: '&nbsp;',
                itemId: 'mainroletitle',
                cls: 'x-fieldset-subheader',
                hideOnMinify: true,
                style: 'white-space:nowrap;overflow:hidden;text-overflow:ellipsis',
                margin: '0 0 16 0'
            },{
                xtype: 'displayfield',
                name: 'os',
                fieldLabel: 'OS',
                style: 'white-space:nowrap',
                height: 34,
                hideOnMinify: true
            },{
                xtype: 'displayfield',
                name: 'behaviors',
                fieldLabel: 'Behaviors',
                height:38,
                hideOnMinify: true
            },{
                xtype: 'container',
                itemId: 'column1',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                defaults: {
                    hidden: true
                },
                items: [{
                    xtype: 'container',
                    platform: ['ec2'],
                    hidden: false,
                    layout: 'anchor',
                    defaults: {
                        anchor: '100%'
                    },
                    items: [{
                        xtype: 'combo',
                        margin: 0,
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
                            change: function(comp, value) {
                                comp.up('#maintab').onParamChange(comp.name, value);
                            }
                        }
                    }]
                },{
                    xtype: 'combo',
                    platform: ['rackspace'],
                    store: {
                        fields: [ 'id', 'name' ],
                        proxy: 'object'
                    },
                    valueField: 'id',
                    displayField: 'name',
                    fieldLabel: 'Flavor',
                    labelWidth: 50,
                    editable: false,
                    queryMode: 'local',
                    name: 'rs.flavor-id',
                    listeners: {
                        change: function(comp, value) {
                            comp.up('#maintab').onParamChange(comp.name, value);
                        }
                    }
                },{
                    xtype: 'combo',
                    platform: ['openstack', 'rackspacengus', 'rackspacenguk'],
                    store: {
                        fields: [ 'id', 'name' ],
                        proxy: 'object'
                    },
                    valueField: 'id',
                    displayField: 'name',
                    fieldLabel: 'Flavor',
                    labelWidth: 50,
                    editable: false,
                    queryMode: 'local',
                    name: 'openstack.flavor-id',
                    listeners: {
                        change: function(comp, value) {
                            comp.up('#maintab').onParamChange(comp.name, value);
                        }
                    }
                },{
                    xtype: 'combo',
                    platform: ['cloudstack', 'idcf', 'ucloud'],
                    store: {
                        fields: [ 'id', 'name' ],
                        proxy: 'object'
                    },
                    matchFieldWidth: false,
                    listConfig: {
                        width: 'auto',
                        minWidth: 350
                    },
                    valueField: 'id',
                    displayField: 'name',
                    fieldLabel: 'Service offering',
                    labelWidth: 100,
                    editable: false,
                    labelStyle: 'white-space:nowrap',
                    queryMode: 'local',
                    name: 'cloudstack.service_offering_id',
                    listeners: {
                        change: function(comp, value) {
                            comp.up('#maintab').onParamChange(comp.name, value);
                        }
                    }
                },{
                    xtype: 'combo',
                    platform: ['gce'],
                    store: {
                        fields: [ 'name', 'description' ],
                        proxy: 'object'
                    },
                    valueField: 'name',
                    displayField: 'name',
                    fieldLabel: 'Machine type',
                    labelWidth: 90,
                    editable: false,
                    queryMode: 'local',
                    name: 'gce.machine-type',
                    listConfig: {
                        width: 'auto',
                        minWidth: 180,
                        style: 'white-space:nowrap',
                        getInnerTpl: function(displayField) {
                            return '{description}';
                        }
                    },
                    listeners: {
                        change: function(comp, value) {
                            comp.up('#maintab').onParamChange(comp.name, value);
                        }
                    }
               }]
          }]
        },{
            xtype: 'container',
            flex: 1,
            maxWidth: 300,
            minWidth: 260,
            padding: 16,
            cls: 'x-delimiter-vertical',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [{
                xtype: 'cloudlocationmap',
                //platforms: tabParams['platforms'],
                mode: 'single',
                itemId: 'cloud_location',
                margin: '0 0 10 0',
                hideOnMinify: true,
                listeners: {
                    selectlocation: function(location, state){
                        var tab = this.up('#maintab'),
                            record = tab.currentRole;
                        if (record.get('platform') === 'gce') {
                            var field = tab.down('[name="gce.cloud-location"]'),
                                value;
                            if (field) {
                                value = Ext.clone(field.getValue());
                                if (state) {
                                    if (!Ext.Array.contains(value, location)) {
                                        value.push(location);
                                    }
                                } else {
                                    if (value.length === 1) {
                                        Scalr.message.Warning('At least one cloud location must be selected!');
                                    } else {
                                        Ext.Array.remove(value, location);
                                    }
                                }
                                field.setValue(value);
                            }
                        }
                    }
                }
            },{
                xtype: 'container',
                itemId: 'column2',
                layout: {
                    type: 'vbox',
                    align: 'stretch'
                },
                defaults: {
                    hidden: true
                },
                items: [{
                    xtype: 'container',
                    platform: ['ec2'],
                    hidden: false,
                    layout: {
                        type: 'hbox',
                        align: 'stretch'
                    },
                    items:[{
                        xtype: 'comboradio',
                        fieldLabel: 'Avail zone',
                        flex: 1,
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
                        margin: 0,
                        labelWidth: 70,
                        listeners: {
                            collapse: function() {
                                var value = this.getValue();
                                if (Ext.isObject(value) && value.items.length === 0) {
                                    this.setValue('');
                                }
                            },
                            change: function(comp, value) {
                                comp.up('#maintab').onParamChange(comp.name, value);
                            }
                        }
                    },{
                        xtype: 'displayinfofield',
                        itemId: 'aws_availability_zone_warn',
                        hidden: true,
                        margin: '0 0 0 10',
                        info: 'If you want to change placement, you need to remove Master EBS volume first.'
                    },{
                        xtype: 'displayfield',
                        fieldLabel: 'Cloud location',
                        labelWidth: 95,
                        name: 'aws.cloud_location',
                        hidden: true
                    }]
                },{
                    xtype: 'displayfield',
                    platform: ['rackspace'],
                    fieldLabel: 'Cloud location',
                    labelWidth: 95,
                    name: 'rs.cloud_location'
                },{
                    xtype: 'displayfield',
                    platform: ['openstack', 'rackspacengus', 'rackspacenguk'],
                    fieldLabel: 'Cloud location',
                    labelWidth: 95,
                    name: 'openstack.cloud_location'
                },{
                    xtype: 'displayfield',
                    platform: ['cloudstack', 'idcf', 'ucloud'],
                    fieldLabel: 'Cloud location',
                    labelWidth: 95,
                    name: 'cloudstack.cloud_location'
                },{
                    xtype: 'combobox',
                    fieldLabel: 'Cloud location',
                    platform: ['gce'],
                    flex: 1,
                    multiSelect: true,
                    name: 'gce.cloud-location',
                    valueField: 'name',
                    displayField: 'description',
                    listConfig: {
                        cls: 'x-boundlist-checkboxes',
                        tpl : '<tpl for=".">'+
                                '<tpl if="state != &quot;UP&quot;">'+
                                    '<div class="x-boundlist-item x-boundlist-item-disabled" title="Zone is offline for maintenance">{description}&nbsp;<span class="warning"></span></div>'+
                                '<tpl else>'+
                                    '<div class="x-boundlist-item">{description}</div>'+
                                '</tpl>'+
                              '</tpl>'
				    },
                    store: {
                        fields: [ 'name', 'description', 'state' ],
                        proxy: 'object'
                    },
					editable: false,
					queryMode: 'local',
                    margin: 0,
                    labelWidth: 90,
                    listeners: {
                        beforeselect: function(comp, record, index) {
                            return record.get('state') === 'UP';
                        },
                        beforedeselect: function(comp, record, index) {
                            if (comp.getValue().length < 2) {
                                Scalr.message.Warning('At least one cloud location must be selected!');
                                return false;
                            } else {
                                return true;
                            }
                        },
                        change: function(comp, value) {
                            var tab = comp.up('#maintab'), locations = [];
                            tab.onParamChange(comp.name, value);
                            tab.currentRole.set('cloud_location', value.length === 1 ? value[0] : 'x-scalr-custom');
                            comp.store.data.each(function(){locations.push(this.get('name'))});
                            tab.down('#cloud_location').selectLocation(tab.currentRole.get('platform'), value, locations);
                        }
                    }
                }]
           }]
        },{
            xtype: 'container',
            flex: 2,
            minWidth: 320,
            items: [{
                xtype: 'container',
                itemId: 'mainscaling',
                layout: 'anchor',
                padding: 16,
                items: [{
                    xtype: 'label',
                    text: 'Scaling',
                    itemId: 'mainscalingtitle',
                    cls: 'x-fieldset-subheader',
                    hideOnMinify: true,
                    margin: '0 0 16 0'
                },{
                    xtype: 'container',
                    anchor: '100%',
                    layout: 'hbox',
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Min&nbsp;instances',
                        labelWidth: 90,
                        fieldStyle: 'width: 36px',
                        margin: '0 18 0 0',
                        name: 'min_instances',
                        hideOnDisabled: true,
                        listeners: {
                            change: function(comp, value) {
                                comp.up('#maintab').onParamChange(comp.name, value);
                            }
                        }
                    },{
                        xtype: 'textfield',
                        fieldLabel: 'Max&nbsp;instances',
                        labelWidth: 90,
                        fieldStyle: 'width: 36px',
                        margin: '0 18 0 0',
                        name: 'max_instances',
                        hideOnDisabled: true,
                        listeners: {
                            change: function(comp, value) {
                                comp.up('#maintab').onParamChange(comp.name, value);
                            }
                        }
                    },{
                        xtype: 'displayfield',
                        fieldLabel: 'Running&nbsp;servers',
                        value: 0,
                        name: 'running_servers',
                        renderer: function(value) {
                            return value > 0 ? '<a href="#">' + value + '</a>' : value;
                        },
                        listeners: {
                            boxready: function() {
                                this.inputEl.on('click', function(e) {
                                    var link = document.location.href.split('#'),
                                        farmRoleId = this.up('#maintab').currentRole.get('farm_role_id');
                                    if (farmRoleId) {
                                        window.open(link[0] + '#/servers/view?farmId=' + tabParams['farmId'] + '&farmRoleId=' + farmRoleId);
                                    }
                                    e.preventDefault();
                                }, this)
                                
                            }
                        }
                    }]

                },{
                    xtype: 'grid',
                    itemId: 'mainscalinggrid',
                    cls: 'x-grid-shadow',
                    hideOnMinify: true,
                    hideOnDisabled: true,
                    margin: '13 0 -2 0',
                    enableColumnResize: false,
                    maxWidth: 410,
                    store: {
                        fields: ['id', 'name', 'max', 'min', 'last'],
                        proxy: 'object'
                    },
                    columns: [{
                        text: 'Metric',
                        sortable: false,
                        dataIndex: 'name',
                        flex: 1.6
                    },{
                        text: 'Scale out',
                        sortable: false,
                        dataIndex: 'max',
                        flex: 1
                    },{
                        text: 'Scale in',
                        sortable: false,
                        dataIndex: 'min',
                        flex: 1
                    },{
                        text: 'Last value',
                        sortable: false,
                        dataIndex: 'last',
                        flex: 1
                    }],
                    viewConfig: {
                        focusedItemCls: '',
                        selectedItemCls: '',
                        overItemCls: '',
                        emptyText: 'No scaling algorithm yet added.',
                        deferEmptyText: false
                    },
                    loadMetrics: function(data) {
                        var dataToLoad = [],
                            metrics = tabParams['metrics'];
                        if (data) {
                            Ext.Object.each(data, function(id, value){
                                dataToLoad.push({
                                    id:id, 
                                    name: metrics[id].name, 
                                    min: metrics[id].alias === 'ram' ? (value.max ? '> ' + value.max : '') : (value.min ? '< ' + value.min : ''), 
                                    max: metrics[id].alias === 'ram' ? (value.min ? '< ' + value.min : '') : (value.max ? '> ' + value.max : ''), 
                                    last: metrics[id].last || ''
                                });
                            })
                        }
                        this.store.loadData(dataToLoad);
                    }

                }],
                setScalingDisabled: function(disabled) {
                    this.suspendLayouts();
                    this.down('#mainscalingtitle').setText(disabled ? ' <span style="color:#777">Auto scaling disabled</span>' : 'Scaling', false);
                    Ext.Array.each(this.query('[hideOnDisabled]'), function(item){
                        item.setVisible(!disabled);
                        item.forceHidden = disabled;
                    });
                    this.resumeLayouts(true);
                }
            }]
        }]

	})
});

Ext.define('Ext.form.field.ComboBoxRadio', {
    extend:'Ext.form.field.ComboBox',
    alias: 'widget.comboradio',
    mixins: {
        bindable: 'Ext.util.Bindable'    
    },
	editable: false,
	queryMode: 'local',
	autoSelect: false,
		
    defaultListConfig: {},

    createPicker: function() {
        var me = this,
            picker,
            pickerCfg = Ext.apply({
            xtype: 'comboradiolist',
            pickerField: me,
            floating: true,
            hidden: true,
            store: me.store,
            displayField: me.displayField,
            valueField: me.valueField
            }, me.listConfig, me.defaultListConfig);
        
        picker = me.picker = Ext.widget(pickerCfg);

        me.mon(picker, {
            selectionchange: me.onListSelectionChange,
            refresh: me.onListRefresh,
            scope: me
        });

        return picker;
    },
	
    onListSelectionChange: function(value) {
        var me = this;
        if (!me.ignoreSelection && me.isExpanded) {
	        me.setValue(value, false);
	        me.fireEvent('select', me, value);
	        me.inputEl.focus();
	    }
    },
	
    findRecordByValue: function(value) {
        return this.findRecord(this.valueField, Ext.isObject(value)  ? value[this.valueField] : value);
    },
	
    setValue: function(value, doSelect) {
        var me = this,
            valueNotFoundText = me.valueNotFoundText,
            inputEl = me.inputEl,
            i, len, record,
            dataObj,
            matchedRecords = [],
            displayTplData = [],
            processedValue = [];

        if (me.store.loading) {
            me.value = value;
            return me;
        }

        value = Ext.Array.from(value);

        for (i = 0, len = value.length; i < len; i++) {
            record = value[i];
            if (!record || !record.isModel) {
                record = me.findRecordByValue(record);
            }
            if (record) {
            	var processedValueTmp, tplData;
                matchedRecords.push(record);
                if (Ext.isObject(value[i])) {
		            processedValueTmp = {};
	                processedValueTmp[me.valueField] = record.get(me.valueField);
	                if (value[i].items) {
                		processedValueTmp['items'] = value[i].items;
                        tplData = value[i].items.join(', ');
                	}
                	//todo check items
                } else {
                	processedValueTmp = record.get(me.valueField);
                    tplData = record.get(me.displayField);
                }
                processedValue.push(processedValueTmp);
                displayTplData.push(tplData);
            } else if (Ext.isDefined(valueNotFoundText)) {
                displayTplData.push(valueNotFoundText);
            }
        }

        me.value = me.multiSelect ? processedValue : processedValue[0];
        me.value;
        if (!Ext.isDefined(me.value)) {
            me.value = null;
        }
        me.displayTplData = displayTplData;
        me.lastSelection = me.valueModels = matchedRecords;

        if (inputEl && me.emptyText && !Ext.isEmpty(value)) {
            inputEl.removeCls(me.emptyCls);
        }

        me.setRawValue(me.getDisplayValue());
        me.checkChange();

        if (doSelect !== false) {
            me.syncSelection();
        }
        me.applyEmptyText();

        return me;
    },
    	
    getValue: function() {
        var me = this,
            picker = me.picker,
            rawValue = me.getRawValue(),
            value = me.value;

        if (me.getDisplayValue() !== rawValue) {
            value = rawValue;
            me.value = me.displayTplData = me.valueModels = null;
            if (picker) {
                me.ignoreSelection++;
                picker.items.each(function(item){
                	item.setChecked(false);
                });
                me.ignoreSelection--;
            }
        }

        return value;
    },

    isEqual: function(v1, v2) {
        var fromArray = Ext.Array.from,
            i, j, len;

        v1 = fromArray(v1);
        v2 = fromArray(v2);
        len = v1.length;

        if (len !== v2.length) {
            return false;
        }

        for(i = 0; i < len; i++) {
        	if (Ext.isObject(v2[i])) {
        		if (v2[i][this.valueField] !== v1[i][this.valueField]) {
        			return false;
        		} else if (v2[i].items && v1[i].items && v2[i].items.length === v1[i].items.length){
        			for(j = 0; j < v2[i].items.length; j++) {
        				if (v2[i].items[j] != v2[i].items[j]) {
        					return false;
        				}
        			}
        		} else if (v2[i].items || v1[i].items){
        			return false;
        		}
        	} else if (v2[i] !== v1[i]) {
                return false;
            }
        }

        return true;
    },

   syncSelection: function() {
        var me = this,
            picker = me.picker;

        if (picker) {
        	var value, items, values = Ext.Array.from(me.value);
            me.ignoreSelection++;
            picker.items.each(function(item){
            	var checked = false;
            	for (var i=0, len=values.length; i<len; i++) {
            		value = null;
            		items = [];
            		if (Ext.isObject(values[i])) {
            			value = values[i][me.valueField];
            			items = values[i].items || [];
            		} else {
            			value = values[i];
            		}
	           		if (value == item.value) {
	           			checked = true;
            		} else if (items.length) {
            			for (var j=0, len1=items.length; j<len1; j++) {
		 	           		if (items[j] == item.value) {
			           			checked = true;
			           			break;
			           		}
		            	}
            		}
            		if (checked) {
            			break;
            		}
            	}
            	item.setChecked(checked);
            });
            me.ignoreSelection--;
        }
    }    	
});

Ext.define('Ext.view.ComboRadioList', {
    extend: 'Ext.menu.Menu',
    alias: 'widget.comboradiolist',
    mixins: {
        bindable: 'Ext.util.Bindable'
    },
    baseCls: 'x-comboradiolist',
    initComponent: function() {
        var me = this;
	    	
        me.store = Ext.data.StoreManager.lookup(me.store || 'ext-empty-store');
        me.bindStore(me.store, true);
        
        me.callParent();
        me.onDataRefresh();
    },
    
    onShow: function(){
    	this.callParent();
    	this.fireEvent('refresh');
    },
    
    onItemClick: function(item) {
        var me = this,
            checked = item.checked;
    	if (checked || item.parentValue !== undefined) {
    		var params;
    		
	    	if (item.hasItems || item.parentValue !== undefined) {
	    		params = {};
	    		params[me.valueField] = item.record.get(me.valueField);
	    		params.items = [];
	    		var items = me.query('[parentValue='+params[me.valueField]+']');
	    		for (var i=0, len=items.length; i<len; i++) {
	    			if (items[i].checked) {
	    				params.items.push(items[i].value);
	    			}
	    		}
                if (item.parentValue !== undefined) {
                    me.down('[value="'+item.parentValue+'"]').setChecked(true);
                }
	    	} else {
	    		params = item.record.get(me.valueField);
	    	}
    		me.fireEvent('selectionchange', params);
    	}
    	
		if (checked && item.group !== undefined && !item.hasItems) {
			me.pickerField.collapse();
		}
    },
    
    bindStore : function(store, initial) {
        var me = this;
        me.mixins.bindable.bindStore.apply(me, arguments);
    },
    
    onDataRefresh: function() {
        var me = this,
            clickHandler = Ext.bind(me.onItemClick, me);
        me.removeAll();
        (me.store.snapshot || me.store.data).each(function(record) {
        	var value = record.get(me.valueField),
        		text = record.get(me.displayField),
        		items = record.get('items');
        	me.add({
                xtype: 'menucheckitem',
                group: me.getId(),
                hasItems: items ? true : false,
                text: text,
                value: value,
                hideOnClick: false,
                record: record,
                handler: clickHandler
            });
            if (items) {
            	for (var i=0, len=items.length; i<len; i++) {
		        	me.add({
                        xtype: 'menucheckitem',
                        cls: 'x-subitem' + (items[i].disabled ? ' x-menu-item-disabled-forced' : ''),
		                parentValue: value,
                        tooltip: items[i].disabled ? 'Zone is offline for maintenance' : null,
                        tooltipType: 'title',
		                text: items[i][me.displayField],
		                value: items[i][me.valueField],
		                disabled: items[i].disabled || false,
		                record: record,
                        handler: clickHandler
		            });
            	}
            }
        });
        me.fireEvent('refresh');
    },
    
    getStoreListeners: function() {
        var me = this;
        return {
            refresh: me.onDataRefresh,
            add: me.onDataRefresh,
            remove: me.onDataRefresh,
            update: me.onDataRefresh,
            clear: me.onDataRefresh
        };
    },

    onDestroy: function() {
        this.bindStore(null);
        this.callParent();
    }
});

Ext.define('Scalr.ui.FarmsBuilderTab', {
	extend: 'Ext.panel.Panel',
	tabTitle: '',
	autoScroll: true,
    bodyCls: 'x-panel-body-frame',
    cls: 'scalr-ui-farmbuilder-roleedit-tab',
    layout: 'anchor',
    
	currentRole: null,
    
    tab: 'tab',
    
    initComponent: function() {
        this.callParent(arguments);
        this.on({
            activate: function () {
                var handler = Ext.bind(this.showTab, this, [this.currentRole]);
                this.beforeShowTab(this.currentRole, handler);
                this.up('farmroleedit').onTabActivate(this);
            },
            deactivate: function () {
                this.hideTab(this.currentRole);
                this.up('farmroleedit').onTabDeactivate(this);
            },
            added: {
                fn: function() {
                    if (this.onRoleUpdate !== undefined) {
                        this.up('farmroleedit').farmRolesStore.on('roleupdate', this.onRoleUpdate, this);
                    }
                },
                scope: this,
                single: true
            }
        });
        
    },
    
	setCurrentRole: function (record, activate) {
        var enabled = this.isActive(record);
		this.currentRole = record;
        this.tabButton.setVisible(enabled);
        if (enabled && this.getTitle !== undefined) {
            this.tabButton.setText(this.getTitle(record));
        }
        if (enabled && activate) {
            this.ownerCt.layout.setActiveItem(this);
            this.tabButton.toggle(true);
            activate = false;
        }
        return activate;
	},
    
	beforeShowTab: function (record, handler) {
		this.el.unmask();
		handler();
	},

	// show tab
	showTab: function (record) {},

	// hide tab
	hideTab: function (record) {},

    deactivateTab: function () {
		this.el.mask();
	},

	// tab can show or used for this role
	isEnabled: function (record) {
		return true;
	},

	isActive: function (record) {
        var behaviors = record.get('behaviors').split(','),
            isVpcRouter = Ext.Array.contains(behaviors, 'router');
        if (isVpcRouter) {
            return Ext.Array.contains(['vpcrouter', 'devel'], this.itemId) && this.isEnabled(record);
        } else {
            return this.itemId !== 'vpcrouter' && this.isEnabled(record);
        }
	},

	// default values for new role
	getDefaultValues: function (record) {
		return {};
	},
    
    suspendOnRoleUpdate: 0
    //difining this method in tab will allow to react to a role settings update
    //onRoleUpdate: function(record, name, value, oldValue) {},
    
});
	