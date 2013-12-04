Scalr.regPage('Scalr.ui.farms.builder', function (loadParams, moduleParams) {
    if (!Ext.ClassManager.isCreated('Scalr.CachedRequest')) {
        Scalr.message.Error('Some libraries need to be reloaded. Please refresh the page.');
        return Ext.create('Ext.container.Container', {});
    }
    
	var reconfigurePage = function(params) {
        var record;
        if (params['roleId']) {
            record = farmRolesStore.findRecord('role_id', params['roleId']);
            if (record) {
                farmbuilder.down('#farmroles').select(record);
            }
        }
	};

    var farmRolesSorters = [{
			property: 'launch_index',
			direction: 'ASC'
		}],
        farmRolesStore = Ext.create('store.store', {
            model: 'Scalr.FarmRoleModel',
            proxy: 'object',
            data: moduleParams.farm ? moduleParams.farm.roles : [],
            sortOnLoad: true,
            sortOnFilter: true,
            sorters: farmRolesSorters,
            listeners: {
                remove: function() {
                    this.resetLaunchIndexes();
                }
            },
            getNextLaunchIndex: function() {
                var index = -1;
                (this.snapshot || this.data).each(function() {
                    var i = this.get('launch_index');
                    index = i > index ? i : index;
                });
                return ++index;
            },
            resetLaunchIndexes: function() {
                var data = this.queryBy(function(){return true;}),//me.store.snapshot || me.store.data
                    index = 0;
                data.sort(farmRolesSorters);
                data.each(function() {
                    this.set('launch_index', index++);
                });
                this.sort(farmRolesSorters);
            },

            updateLaunchIndex: function(record, launchIndex) {
                var currentLaunchIndex = record.get('launch_index');
                (this.snapshot || this.data).each(function() {
                    var recLaunchIndex = this.get('launch_index');
                    if (recLaunchIndex >= launchIndex) {
                        this.set('launch_index', recLaunchIndex + 1);
                    }
                });
                record.set('launch_index', launchIndex);

                (this.snapshot || this.data).each(function() {
                    var recLaunchIndex = this.get('launch_index');
                    if (recLaunchIndex > currentLaunchIndex) {
                        this.set('launch_index', recLaunchIndex - 1);
                    }
                });
                this.sort(farmRolesSorters);
            }

        });

    var addRoleHandler = function (role) {
        var behaviors = role.get('behaviors');
        if (
            farmRolesStore.queryBy(function(record) {
                if (
                    record.get('platform') == role.get('platform') &&
                    record.get('role_id') == role.get('role_id') &&
                    (record.get('cloud_location') == role.get('cloud_location') || record.get('platform') === 'gce')
                )
                    return true;
            }).length > 0
        ) {
            Scalr.message.Error('Role "' + role.get('name') + '" already added');
            return false;
        }

        // check before adding
        if ((Ext.Array.contains(behaviors, 'mysql') || Ext.Array.contains(behaviors, 'mysql2') || Ext.Array.contains(behaviors, 'percona')) && !Ext.Array.contains(behaviors, 'mysqlproxy')) {
            if (
                farmRolesStore.queryBy(function(record) {
                    if ((record.get('behaviors').match('mysql') || record.get('behaviors').match('mysql2') || record.get('behaviors').match('percona')) && !record.get('behaviors').match('mysqlproxy'))
                        return true;
                }).length > 0
            ) {
                Scalr.message.Error('Only one MySQL / Percona role can be added to farm');
                return false;
            }
        }

        if (Ext.Array.contains(behaviors, 'postgresql')) {
            if (
                farmRolesStore.queryBy(function(record) {
                    if (record.get('behaviors').match('postgresql'))
                        return true;
                }).length > 0
            ) {
                Scalr.message.Error('Only one PostgreSQL role can be added to farm');
                return false;
            }
        }

        if (Ext.Array.contains(behaviors, 'redis')) {
            if (
                farmRolesStore.queryBy(function(record) {
                    if (record.get('behaviors').match('redis'))
                        return true;
                }).length > 0
            ) {
                Scalr.message.Error('Only one Redis role can be added to farm');
                return false;
            }
        }
        
        role.set({
            'farm_role_id': 'virtual_' + (new Date()).getTime(),
            'new': true,
            'launch_index': farmRolesStore.getNextLaunchIndex(),
            'behaviors': behaviors.join(',')
        });
        
        farmbuilder.down('#edit').addRoleDefaultValues(role);
        if (moduleParams.farm && moduleParams.farm.roleDefaultSettings !== undefined) {
            Ext.apply(role.get('settings', true), moduleParams.farm.roleDefaultSettings);
        }
        
        farmRolesStore.add(role);
        Scalr.message.Success('Role "' + role.get('name') + '" added');

    };
                            
    var saveHandler = function (farm) {
        var p = {};
        farm = farm || {};

        farmbuilder.down('#farmroles').deselectAll();
        farmbuilder.down('#farmroles').clearFilter();

        farm['farmId'] = moduleParams['farmId'];
        
        p['name'] = farmbuilder.down('#farmName').getValue();
        p['description'] = farmbuilder.down('#farmDescription').getValue();
        p['timezone'] = farmbuilder.down('#timezone').getValue();
        p['rolesLaunchOrder'] = farmbuilder.down('#launchorder').getValue().mode;
        p['variables'] = farmbuilder.down('#variables').getValue();
        
        //vpc
        var vpc = farmbuilder.down('#farm').getVpcSettings();
        if (vpc !== false) {
            p['vpc_region'] = vpc.region;
            p['vpc_id'] = vpc.id;
        }
        
        farm['farm'] = Ext.encode(p);

        p = [];
        (farmRolesStore.snapshot || farmRolesStore.data).each(function (rec) {
            var settings = rec.get('settings'), sets = {};

            sets = {
                role_id: rec.get('role_id'),
                farm_role_id: rec.get('farm_role_id'),
                launch_index: rec.get('launch_index'),
                platform: rec.get('platform'),
                cloud_location: rec.get('cloud_location'),
                settings: rec.get('settings'),
                scaling: rec.get('scaling'),
                scripting: rec.get('scripting'),
                scripting_params: rec.get('scripting_params'),
                config_presets: rec.get('config_presets'),
                storages: rec.get('storages'),
                variables: rec.get('variables')
            };

            if (Ext.isObject(rec.get('params'))) {
                sets['params'] = rec.get('params');
            }

            p[p.length] = sets;
        });

        farm['roles'] = Ext.encode(p);
        farm['v2'] = 1;
        farm['changed'] = moduleParams['farm'] ? moduleParams['farm']['changed'] : '';
        Scalr.Request({
            processBox: {
                msg: 'Saving farm ...'
            },
            url: '/farms/builder/xBuild',
            params: farm,
            success: function(data) {
                Scalr.event.fireEvent('redirect', '#/farms/' + data.farmId + '/view');
            },
            failure: function(data) {
                var card = farmbuilder.down('#fbcard').layout;
                if (card.getActiveItem().itemId === 'blank') {
                    card.setActiveItem('farm');
                }
                
                if (data['changedFailure']) {
                    Scalr.utils.Window({
                        title: 'Warning',
                        layout: 'fit',
                        width: 500,
                        items: [{
                            xtype: 'displayfield',
                            fieldCls: 'x-form-field-warning',
                            value: data['changedFailure'],
                            margin: '0 0 10 0'
                        }],
                        dockedItems: [{
                            xtype: 'container',
                            dock: 'bottom',
                            layout: {
                                type: 'hbox',
                                pack: 'center'
                            },
                            items: [{
                                xtype: 'button',
                                text: 'Override',
                                handler: function() {
                                    this.up('#box').close();
                                    moduleParams['farm']['changed'] = ''; // TODO: do better via flag
                                    saveHandler();
                                }
                            }, {
                                xtype: 'button',
                                text: 'Refresh page',
                                margin: '0 0 0 10',
                                handler: function() {
                                    this.up('#box').close();
                                    Scalr.event.fireEvent('refresh');
                                }
                            }, {
                                xtype: 'button',
                                text: 'Continue edit',
                                margin: '0 0 0 10',
                                handler: function() {
                                    this.up('#box').close();
                                }
                            }]
                        }]
                    });
                }
            }
        });
    }

    var farmbuilder = Ext.create('Ext.container.Container', {
        baseTitle: 'Farms &raquo; ' + (moduleParams.farm ? moduleParams.farm.farm.name : 'Builder'),
        updateTitle: function(text) {
            this.up('panel').setTitle(this.baseTitle + (text ? ' &raquo; ' + text : ''));
        },
		layout: {
			type: 'hbox',
			align : 'stretch',
			pack  : 'start'
		},
        itemId: 'farmbuilder',
        cache: Ext.create('Scalr.CachedRequest'),
        items: [{
                xtype: 'farmselroles',
                itemId: 'farmroles',
                store:  farmRolesStore,
                listeners: {
                    farmsettings: function(state) {
                        this.deselectAll();
                        if (state) {
                            farmbuilder.down('#fbcard').layout.setActiveItem('farm');
                        } else {
                            farmbuilder.down('#fbcard').layout.setActiveItem('blank');
                        }
                    },
                    selectionchange: function(c, selections) {
                        var c = farmbuilder.down('#fbcard');
                        if (selections[0]) {
                            c.layout.setActiveItem('blank');
                            farmbuilder.down('#edit').setCurrentRole(selections[0]);
                            c.layout.setActiveItem('edit');
                        } else {
                            c.layout.setActiveItem('blank');
                        }
                    },
                    addrole: function () {
                        this.deselectAll();
                        var card = farmbuilder.down('#fbcard');
                        if (!card.getComponent('add')) {
                            card.add({
                                xtype: 'roleslibrary',
                                moduleParams: moduleParams,
                                hidden: true,
                                autoRender: false,
                                itemId: 'add',
                                listeners: {
                                    activate: function() {
                                        farmbuilder.updateTitle('Add new role');
                                    },
                                    addrole: addRoleHandler
                                }
                            });
                        }
                        card.layout.setActiveItem('add');
                    }
                }
            }, {
                xtype: 'container',
                itemId: 'fbcard',
                layout: 'card',
                flex: 1,
                activeItem: 'farm',
                items: [{
                    itemId: 'farm',
                    xtype: 'container',
                    cls: 'x-panel-body-frame-dark',
                    autoScroll: true,
                    layout: 'anchor',
                    getVpcSettings: function() {
                        var result = false,
                            vpcRegion = this.down('[name="vpc_region"]').getValue(),
                            vpcId = this.down('[name="vpc_id"]').getValue();
                        if (moduleParams['farmVpcEc2Enabled'] && this.down('[name="vpc_enabled"]').getValue() && vpcRegion && vpcId){
                            result = {
                                region: vpcRegion,
                                id: vpcId
                            }
                        }
                        return result;
                    },
                    defaults: {
                        anchor: '100%'
                    },
                    items: [{
                        xtype: 'container',
                        cls: 'x-panel-body-frame x-panel-body-plain',
                        style: 'border-radius: 4px',
                        margin: '0 0 12 0',
                        layout: {
                            type: 'hbox',
                            align: 'stretch'
                        },
                        defaults: {
                            xtype: 'container',
                            layout: 'anchor',
                            padding: '16 32',
                            defaults: {
                                anchor: '100%',
                                labelWidth: 80
                            }
                        },
                        items: [{
                            cls: 'x-delimiter-vertical',
                            flex: 1,
                            maxWidth: 700,
                            items: [{
                                xtype: 'label',
                                text: 'General info',
                                cls: 'x-fieldset-subheader'
                            },{
                                xtype: 'textfield',
                                name: 'name',
                                itemId: 'farmName',
                                fieldLabel: 'Name'
                            }, {
                                xtype: 'textarea',
                                name: 'description',
                                itemId: 'farmDescription',
                                fieldLabel: 'Description',
                                grow: true,
                                growMin: 70
                            }, {
                                xtype: 'combo',
                                name: 'timezone',
                                itemId: 'timezone',
                                store: moduleParams['timezones_list'],
                                fieldLabel: 'Timezone',
                                allowBlank: false,
                                anchor: '100%',
                                forceSelection: true,
                                editable: true,
                                queryMode: 'local',
                                anyMatch: true
                            }]
                        },{
                            width: 440,
                            items: [{
                                xtype: 'label',
                                text: 'Settings',
                                cls: 'x-fieldset-subheader'
                            },{
                                xtype: 'radiogroup',
                                name: 'rolesLaunchOrder',
                                itemId: 'launchorder',
                                columns: 1,
                                listeners: {
                                    change: function(comp, value) {
                                        value = value.mode;
                                        farmbuilder.down('#launchorderinfo')[value == 1?'show':'hide']();
                                        farmbuilder.down('#farmroles').toggleLaunchOrder(value == 1);
                                        if (value == 1) {
                                            farmRolesStore.resetLaunchIndexes();
                                        }
                                    }
                                },
                                items: [{
                                    boxLabel: 'Launch roles simultaneously ',
                                    name: 'mode',
                                    inputValue: '0'
                                }, {
                                    boxLabel: 'Launch roles one-by-one in the order I set (slower) ',
                                    name: 'mode',
                                    inputValue: '1'
                                }]
                            },{
                                xtype: 'displayfield',
                                itemId: 'launchorderinfo',
                                fieldCls: 'x-form-field-info',
                                hidden: true,
                                value: 'Use drag and drop to adjust roles launch order. &nbsp;'
                            }]
                        }]
                    },{
                        xtype: 'displayfield',
                        itemId: 'vpcinfo',
                        fieldCls: 'x-form-field-info',
                        hidden: true,
                        value: 'VPC settings can be changed on TERMINATED farm only.'
                    },{
                        xtype: 'fieldset',
                        itemId: 'vpc',
                        title: 'Launch this farm inside VPC',
                        toggleOnTitleClick: true,
                        checkboxToggle: true,
                        collapsed: true,
                        collapsible: true,
                        hidden: !moduleParams['farmVpcEc2Enabled'],
                        checkboxName: 'vpc_enabled',
                        layout: 'hbox',
                        items: [{
                            xtype: 'combo',
                            width: 300,
                            name: 'vpc_region',
                            emptyText: 'Please, select VPC region',
                            editable: false,
                            store: {
                                fields: [ 'id', 'name' ],
                                data: moduleParams['farmVpcEc2Locations'] || [],
                                proxy: 'object'
                            },
                            queryMode: 'local',
                            valueField: 'id',
                            displayField: 'name',
                            listeners: {
                                change: function(field, value) {
                                    var f = field.next();
                                    f.reset();
                                    f.getPlugin('comboaddnew').postUrl = '?cloudLocation=' + value;
                                }
                            }
                        }, {
                            xtype: 'combo',
                            width: 300,
                            name: 'vpc_id',
                            emptyText: 'Please, select VPC ID',
                            margin: '0 0 12 12',
                            editable: false,
                            store: {
                                fields: [ 'id', 'name' ],
                                proxy: 'object'
                            },
                            queryMode: 'local',
                            valueField: 'id',
                            displayField: 'name',
                            plugins: [{
                                ptype: 'comboaddnew',
                                pluginId: 'comboaddnew',
                                url: '/tools/aws/vpc/create'
                            }],
                            listeners: {
                                addnew: function(item) {
                                    this.up('#farmbuilder').cache.setExpired({
                                        url: '/platforms/ec2/xGetVpcList', 
                                        params: {
                                            cloudLocation: this.prev().getValue()
                                        }
                                    });
                                },
                                expand: function() {
                                    var me = this,
                                        cloudLocation = this.prev().getValue();
                                    if (cloudLocation && cloudLocation !== me.loadedCloudLocation) {
                                        me.collapse();
                                        me.store.removeAll();
                                        this.up('#farmbuilder').cache.load(
                                            {
                                                url: '/platforms/ec2/xGetVpcList',
                                                params: {cloudLocation: cloudLocation}
                                            },
                                            function(data, status){
                                                if (!status) return;
                                                me.store.loadData(data['vpc'] || []);
                                                me.loadedCloudLocation = cloudLocation;
                                                me.expand();
                                            },
                                            this,
                                            0
                                        );
                                    }
                                    if (!cloudLocation) {
                                        me.collapse();
                                        Scalr.message.Warning('Select VPC region first!');
                                    }
                                }
                            }
                        }]
                    }, {
                        xtype: 'fieldset',
                        title: 'Global variables',
                        hidden: false,
                        items: [{
                            xtype: 'variablefield',
                            name: 'variables',
                            itemId: 'variables',
                            maxWidth: 1200,
                            currentScope: 'farm'
                        }]
                    }],
                    listeners: {
                        boxready: function() {
                            var form = farmbuilder.down('#farm'),
                                farm = moduleParams.farm ? Ext.clone(moduleParams.farm.farm) : {};
                            Ext.apply(farm, {
                                timezone: farm.timezone || moduleParams['timezone_default'],
                                rolesLaunchOrder: {mode: farm.rolesLaunchOrder || '0'},
                                variables: farm.variables || moduleParams.farmVariables,
                                status: farm.status || 0
                            });
                            
                            if (moduleParams['farmVpcEc2Enabled']) {
                                if (farm.vpc && farm.vpc.id) {
                                    farm.vpc_enabled = true;
                                    farm.vpc_region = farm.vpc.region;
                                    farm.vpc_id = farm.vpc.id;
                                }
                                if (farm.status != 0) {
                                    form.down('#vpc').mask();
                                    form.down('#vpcinfo').show();
                                }
                            }
                            form.setFieldValues(farm);
                            reconfigurePage(loadParams);
                        },
                        activate: function() {
                            farmbuilder.down('#farmroles').toggleFarmButton(true);
                            farmbuilder.updateTitle();
                        },
                        deactivate: function() {
                            farmbuilder.down('#farmroles').toggleFarmButton(false);
                        }
                    }
                }, {
                    xtype: 'container',
                    itemId: 'blank',
                    cls: 'x-panel-body-frame-dark',
                    listeners: {
                        activate: function() {
                            farmbuilder.updateTitle();
                        }
                    }
                }, {
                    xtype: 'farmroleedit',
                    itemId: 'edit',
                    farmRolesStore: farmRolesStore,
                    farmId: moduleParams['farmId'] || 0,
                    bodyStyle: 'padding-left:0',
                    listeners: {
                        activate: function() {
                            farmbuilder.updateTitle(this.currentRole.get('name'));
                        },
                        tabactivate: function(tab) {
                            if (tab.itemId === 'scripting') {
                                farmbuilder.down('#farmroles').down('dataview').addCls('scalr-ui-show-color-corners');
                            }
                        },
                        tabdeactivate: function(tab) {
                            if (tab.itemId === 'scripting') {
                                farmbuilder.down('#farmroles').down('dataview').removeCls('scalr-ui-show-color-corners')
                            }
                        }
                    }
                }]
            }]
    });

    if (moduleParams['farm'] && moduleParams['farm']['lock'])
        Scalr.message.Warning(moduleParams['farm']['lock'] + ' You won\'t be able to save any changes.');

    Ext.apply(moduleParams['tabParams'], {
        farmRolesStore: farmRolesStore,
        behaviors: moduleParams['behaviors'] || {},
        platforms: moduleParams['platforms'] || {},
        metrics: moduleParams['metrics'] || {},
        farm: moduleParams['farm'] ? moduleParams['farm'].farm : {}
    });

    farmbuilder.down('#edit').createTabs(moduleParams);

    return Ext.create('Ext.panel.Panel', {
        cls: 'scalr-ui-farmbuilder-panel',
        scalrOptions: {
            'maximize': 'all',
            'title': 'Farms &raquo; ' + (moduleParams.farm ? moduleParams.farm.farm.name : 'Builder') 
        },
		layout: 'fit',
        tools: [{
            xtype: 'favoritetool',
            hidden: !!moduleParams.farm,
            favorite: {
                text: 'Create new farm',
                href: '#/farms/build'
            }
        }],
        items: farmbuilder,
        dockedItems: [{
            xtype: 'container',
            dock: 'bottom',
            cls: 'x-docked-bottom-frame',
            layout: {
                type: 'hbox',
                pack: 'center'
            },
            items: [{
                xtype: 'button',
                text: 'Save',
                disabled: moduleParams['farm'] ? !!moduleParams['farm']['lock'] : false,
                handler: function() {
                    saveHandler();
                }
            }, {
                xtype: 'button',
                margin: '0 0 0 5',
                text: 'Cancel',
                handler: function() {
                    Scalr.event.fireEvent('close');
                }
            }]
        }]
    });
});


Ext.define('Scalr.FarmRoleModel', {
    extend: 'Ext.data.Model',
    fields: [
        'id',
        { name: 'new', type: 'boolean' },
        'role_id',
        'platform',
        'generation',
        'os',
        'os_family',
        'os_generation',
        'os_version',
        'farm_role_id',
        'cloud_location',
        'arch',
        'image_id',
        'name',
        'group',
        'cat_id',
        'behaviors',
        {name: 'launch_index', type: 'int'},
        'is_bundle_running',
        'settings',
        'scaling',
        'scripting',
        'scripting_params',
        'storages',
        'config_presets',
        'tags',
        'variables',
        'running_servers'
    ],
    
    constructor: function() {
        var me = this;
        me.callParent(arguments);
    },
    
    get: function(field, raw) {
        var value = this.callParent([field]);
        return raw === true || !value || Ext.isPrimitive(value) ? value : Ext.clone(value);
    },
    
    watchList: {
        launch_index: true,
        settings: ['scaling.enabled', 'scaling.min_instances', 'scaling.max_instances', 'aws.instance_type', 'db.msr.data_storage.engine', 'gce.machine-type'],
        scaling: true
    },
    
    set: function (fieldName, newValue) {
        var me = this,
            data = me[me.persistenceProperty],
            single = (typeof fieldName == 'string'),
            name, values, currentValue, value,
            events = [];
        
        if (me.store) {
            if (single) {
                values = me._singleProp;
                values[fieldName] = newValue;
            } else {
                values = fieldName;
            }

            for (name in values) {
                if (values.hasOwnProperty(name)) {
                    value = values[name];
                    currentValue = data[name];
                    if (me.isEqual(currentValue, value)) {
                        continue;
                    }
                    if (me.watchList[name]) {
                        if (me.watchList[name] === true) {
                            events.push({name: [name], value: value, oldValue: currentValue});
                        } else {
                            for (var i=0, len=me.watchList[name].length; i<len; i++) {
                                var name1 = me.watchList[name][i],
                                    currentValue1 = currentValue && currentValue[name1] ? currentValue[name1] : undefined,
                                    value1 = value && value[name1] ? value[name1] : undefined;
                                if (currentValue1 != value1) {
                                    events.push({name: [name, name1], value: value1, oldValue: currentValue1});
                                }
                            }
                        }
                    }
                }
            }

            if (single) {
                delete values[fieldName];
            }
        }
        
        me.callParent(arguments);
        
        Ext.Array.each(events, function(event){
            me.store.fireEvent('roleupdate', me, event.name, event.value, event.oldValue);
        });
    },
    
    getEc2InstanceType: function(what) {
        var me = this,
            settings = me.get('settings', true),
            tagsString = (me.get('tags', true) || []).join(' '),
            behaviors = me.get('behaviors', true),
            result = {
                list: [],
                value: ''
            };
        what = what || 'both';
        
        behaviors = Ext.isArray(behaviors) ? behaviors : behaviors.split(',');
        
        if (me.get('arch') == 'i386') {
            if ((tagsString.indexOf('ec2.ebs') != -1 || settings['aws.instance_type'] == 't1.micro') && !Ext.Array.contains(behaviors, 'cf_cloud_controller')) {
                result.list = ['t1.micro', 'm1.small', 'c1.medium'];
            } else {
                result.list = ['m1.small', 'c1.medium'];
            }
            result.value = (settings['aws.instance_type'] || 'm1.small');
        } else {
            result.value = (settings['aws.instance_type'] || 'm1.small');

            if (tagsString.indexOf('ec2.ebs') != -1 || settings['aws.instance_type'] == 't1.micro') {
                if (tagsString.indexOf('ec2.hvm') != -1 && me.get('os') != '2008Server' && me.get('os') != '2008ServerR2' && me.get('os_family') != 'windows') {
                    result.list = ['cc1.4xlarge', 'cc2.8xlarge', 'cg1.4xlarge', 'hi1.4xlarge', 'cr1.8xlarge'];
                    if (settings['aws.instance_type'] != 'm1.large') {
                        result.value = (settings['aws.instance_type'] || 'cc1.4xlarge');
                    } else {
                        result.value = 'cc1.4xlarge';
                    }
                } else {
					
                    //if (me.get('behaviors', true).match('cf_cloud_controller')) {
                    //    result.list = ['m1.small', 'c1.medium', 'm1.medium', 'm1.large', 'm1.xlarge', 'c1.xlarge', 'm2.xlarge', 'm2.2xlarge', 'm2.4xlarge', 'm3.xlarge', 'm3.2xlarge', 'hi1.4xlarge', 'hs1.8xlarge', 'cr1.8xlarge'];
                    //} else {
                        result.list = ['t1.micro', 'm1.small', 'c1.medium', 'm1.medium', 'm1.large', 'm1.xlarge', 'c1.xlarge', 'm2.xlarge', 'm2.2xlarge', 'm2.4xlarge', 'm3.xlarge', 'm3.2xlarge', 'hi1.4xlarge', 'hs1.8xlarge', 'cr1.8xlarge'];
                    //}
                    result.value = (settings['aws.instance_type'] || 'm1.small');
                }
            } else {
                result.list = ['m1.large', 'm1.xlarge', 'c1.xlarge', 'm2.xlarge', 'm2.2xlarge', 'm2.4xlarge'];
                result.value = (settings['aws.instance_type'] || 'm1.large');
            }
        }
        return what ==='both' ? result : result[what];
    },
    
    isEc2EbsOptimizedFlagVisible: function(instType) {
        var me = this,
            result = false,
            tagsString = (me.get('tags', true) || []).join(' ');
        if (instType === undefined) {
            instType = me.getEc2InstanceType('value');
        }
        if (tagsString.indexOf('ec2.ebs') !== -1) {
            result = Ext.Array.contains(['m1.large', 'm1.xlarge', 'm2.4xlarge', 'm3.xlarge','m3.2xlarge'], instType);
        }
        return result;
    },
    
    getDefaultStorageEngine: function() {
        var default_storage_engine = '',
            platform = this.get('platform', true);
        
        if (platform === 'ec2') {
            default_storage_engine = 'ebs';
        } else if (platform === 'rackspace') {
            default_storage_engine = 'eph';
        } else if (Ext.Array.contains(['openstack', 'rackspacengus', 'rackspacenguk', 'gce'], platform)) {
            default_storage_engine = this.isMySql() ? 'lvm' : 'eph';
        } else if (platform == 'cloudstack' || platform == 'idcf' || platform == 'ucloud') {
            default_storage_engine = 'csvol';
        }        
        return default_storage_engine;
    },
    
    isDbMsr: function(includeDeprecated) {
        var behaviors = this.get('behaviors', true),
            db = ['mysql2', 'percona', 'redis', 'postgresql', 'mariadb'];
        
        if (includeDeprecated === true) {
            db.push('mysql');
        }
        behaviors = Ext.isArray(behaviors) ? behaviors : behaviors.split(',');
        return Ext.Array.some(behaviors, function(rb){
            return Ext.Array.contains(db, rb);
        });
    },
    
    isMySql: function() {
        var behaviors = this.get('behaviors', true),
            db = ['mysql2', 'percona', 'mariadb'];
        
        behaviors = Ext.isArray(behaviors) ? behaviors : behaviors.split(',');
        return Ext.Array.some(behaviors, function(rb){
            return Ext.Array.contains(db, rb);
        });
    },
    
    ephemeralDevicesMap: {
        ec2: {
            'm1.small': {'ephemeral0':{'size': 150}},
            'm1.medium': {'ephemeral0':{'size': 400}},
            'm1.large': {'ephemeral0':{'size': 420}, 'ephemeral1':{'size': 420}},
            'm1.xlarge': {'ephemeral0':{'size': 420}, 'ephemeral1':{'size': 420}, 'ephemeral2':{'size': 420}, 'ephemeral3':{'size': 420}},
            'c1.medium': {'ephemeral0':{'size': 340}},
            'c1.xlarge': {'ephemeral0':{'size': 420}, 'ephemeral1':{'size': 420}, 'ephemeral2':{'size': 420}, 'ephemeral3':{'size': 420}},
            'm2.xlarge': {'ephemeral0':{'size': 410}},
            'm2.2xlarge': {'ephemeral0':{'size': 840}},
            'm2.4xlarge': {'ephemeral0':{'size': 840}, 'ephemeral1':{'size': 840}},
            'hi1.4xlarge': {'ephemeral0':{'size': 1000}, 'ephemeral1':{'size': 1000}},
            'cc1.4xlarge': {'ephemeral0':{'size': 840}, 'ephemeral1':{'size': 840}},
            'cr1.8xlarge': {'ephemeral0':{'size': 120}, 'ephemeral1':{'size': 120}},
            'cc2.8xlarge': {'ephemeral0':{'size': 840}, 'ephemeral1':{'size': 840}, 'ephemeral2':{'size': 840}, 'ephemeral3':{'size': 840}},
            'cg1.4xlarge': {'ephemeral0':{'size': 840}, 'ephemeral1':{'size': 840}},
            'hs1.8xlarge': {'ephemeral0':{'size': 12000}, 'ephemeral1':{'size': 12000}, 'ephemeral2':{'size': 12000}, 'ephemeral3':{'size': 12000}}
        },
        gce: {
            'n1-highcpu-2-d': {'google-ephemeral-disk-0':{'size': 870}},
            'n1-highcpu-4-d': {'google-ephemeral-disk-0':{'size': 1770}},
            'n1-highcpu-8-d': {'google-ephemeral-disk-0':{'size': 1770}, 'google-ephemeral-disk-1':{'size': 1770}},
            'n1-highmem-2-d': {'google-ephemeral-disk-0':{'size': 870}},
            'n1-highmem-4-d': {'google-ephemeral-disk-0':{'size': 1770}},
            'n1-highmem-8-d': {'google-ephemeral-disk-0':{'size': 1770}, 'google-ephemeral-disk-1':{'size': 1770}},
            'n1-standard-1-d': {'google-ephemeral-disk-0':{'size': 420}},
            'n1-standard-2-d': {'google-ephemeral-disk-0':{'size': 870}},
            'n1-standard-4-d': {'google-ephemeral-disk-0':{'size': 1770}},
            'n1-standard-8-d': {'google-ephemeral-disk-0':{'size': 1770}, 'google-ephemeral-disk-1':{'size': 1770}}
        }
    },
    
    getEphemeralDevicesMap: function() {
        return this.ephemeralDevicesMap[this.get('platform')];
        
    },
    
    getAvailableStorages: function() {
        var platform = this.get('platform'),
            ephemeralDevicesMap = this.getEphemeralDevicesMap(),
            settings = this.get('settings', true),
            storages = [];
        
        if (platform === 'ec2') {
            storages.push({name:'ebs', description:'Single EBS Volume'});
            storages.push({name:'raid.ebs', description:'RAID array on EBS volumes'});
            
            if (this.isMySql()) {
                if (Ext.isDefined(ephemeralDevicesMap[settings['aws.instance_type']])) {
                    storages.push({name:'lvm', description:'LVM on ephemeral devices'});
                }
                if (settings['db.msr.data_storage.engine'] == 'eph' || Scalr.flags['betaMode']) {
                    storages.push({name:'eph', description:'Single ephemeral device'});
                }
            } else {
                storages.push({name:'eph', description:'Single ephemeral device'});
            }
        } else if (platform === 'rackspace') {
            storages.push({name:'eph', description:'Ephemeral device'});
        } else if (platform === 'gce') {
            if (this.isMySql()) {
                storages = [{name:'lvm', description:'LVM on ephemeral devices'}];
            } else {
                storages.push({name:'eph', description:'Ephemeral device'});
            }

            storages.push({name:'gce_persistent', description:'GCE Persistent disk'});
            if (Scalr.flags['betaMode']) {
                storages.push({name:'raid.gce_persistent', description:'RAID array on GCE Persistent disk'});
            }
        } else if (Ext.Array.contains(['rackspacengus', 'rackspacenguk', 'openstack'], platform)) {
            storages.push({name:'cinder', description:'Cinder volume'});

            if (this.isMySql()) {
                storages.push({name:'lvm', description:'LVM on loop device (75% from /)'});
            } else {
                storages.push({name:'eph', description:'Ephemeral device'});
            }


        } else if (Ext.Array.contains(['cloudstack', 'idcf', 'ucloud'], platform)) {
            storages.push({name:'csvol', description:'CloudStack Block Volume'});
        }
        return storages;
    },
    
    getAvailableStorageFs: function(featureMFS) {
        var list,
            osFamily = this.get('os_family'),
            arch = this.get('arch'),
            osVersion = this.get('os_generation'),
            extraFs = (osFamily === 'centos' && arch === 'x86_64') ||
                      (osFamily === 'ubuntu' && (osVersion == '10.04' || osVersion == '12.04')),
            disabledText = extraFs && !featureMFS ? 'Not available for your pricing plan' : '';
        list = [
            {value: 'ext3', text: 'Ext3'},
            {value: 'ext4', text: 'Ext4', disabled: !extraFs || !featureMFS, tooltip: disabledText},
            {value: 'xfs', text: 'XFS', disabled: !extraFs || !featureMFS, tooltip: disabledText}
        ];      
        return list;
    },
    
    storageDisks: {
        ec2: {
            '/dev/sda2': {'m1.small':1, 'c1.medium':1},
            '/dev/sdb': {'m1.medium':1, 'm1.large':1, 'm1.xlarge':1, 'c1.xlarge':1, 'cc1.4xlarge':1, 'cc2.8xlarge':1, 'cr1.8xlarge':1, 'm2.xlarge':1, 'm2.2xlarge':1, 'm2.4xlarge':1},
            '/dev/sdc': {               'm1.large':1, 'm1.xlarge':1, 'c1.xlarge':1, 'cc1.4xlarge':1, 'cc2.8xlarge':1, 'cr1.8xlarge':1},
            '/dev/sdd': {						 	  'm1.xlarge':1, 'c1.xlarge':1, 			   	 'cc2.8xlarge':1 },
            '/dev/sde': {						 	  'm1.xlarge':1, 'c1.xlarge':1, 			     'cc2.8xlarge':1 },

            '/dev/sdf': {'hi1.4xlarge':1 },
            '/dev/sdg': {'hi1.4xlarge':1 }
        }
    },
    getAvailableStorageDisks: function() {
        var platform = this.get('platform'),
            settings = this.get('settings', true),
            disks = [];
        
        disks.push({'device':'', 'description':''});
        if (platform === 'ec2') {
            Ext.Object.each(this.storageDisks['ec2'], function(key, value){
                if (value[settings['aws.instance_type']] === 1) {
                    disks.push({'device': key, 'description':'LVM on ' + key + ' (80% available for data)'});
                }
            });
        } else if (Ext.Array.contains(['rackspacengus', 'rackspacenguk', 'openstack', 'rackspace'], platform)) {
            disks.push({'device':'/dev/loop0', 'description':'Loop device (75% from /)'});
        } else if (platform === 'gce') {
            disks.push({'device':'ephemeral-disk-0', 'description':'Loop device (80% of ephemeral-disk-0)'});
        }
        return disks;
    },
    
    getAvailableStorageRaids: function() {
        return [
            {name:'0', description:'RAID 0 (block-level striping without parity or mirroring)'},
            {name:'1', description:'RAID 1 (mirroring without parity or striping)'},
            {name:'5', description:'RAID 5 (block-level striping with distributed parity)'},
            {name:'10', description:'RAID 10 (mirrored sets in a striped set)'}
        ];
    }
});