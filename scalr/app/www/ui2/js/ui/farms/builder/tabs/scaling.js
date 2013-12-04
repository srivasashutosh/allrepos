Scalr.regPage('Scalr.ui.farms.builder.tabs.scaling', function (moduleTabParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Scaling',
		itemId: 'scaling',

        layout: {
            type: 'hbox',
            align: 'stretch'
        },

        bodyCls: 'x-panel-body-frame x-panel-body-plain',

        isEnabled: function (record) {
			return  record.get('platform') != 'rds' && !record.get('behaviors').match('mongodb');
		},

		getDefaultValues: function (record) {
			return {
				'scaling.min_instances': 1,
				'scaling.max_instances': 2,
				'scaling.polling_interval': 1,
				'scaling.keep_oldest': 0,
				'scaling.ignore_full_hour' : 0,
				'scaling.safe_shutdown': 0,
				'scaling.exclude_dbmsr_master' : 0,
				'scaling.one_by_one' : 0,
				'scaling.enabled' : 1
			};
		},

        onRoleUpdate: function(record, name, value, oldValue) {
            if (this.suspendOnRoleUpdate > 0 || !this.isVisible()) {
                return;
            }
            
            var fullname = name.join('.'), 
                comp;
            if (fullname === 'settings.scaling.min_instances') {
                comp = this.down('[name="scaling.min_instances"]');
            } else if (fullname === 'settings.scaling.max_instances') {
                comp = this.down('[name="scaling.max_instances"]');
            }
            
            if (comp) {
                comp.suspendEvents(false);
                comp.setValue(value);
                comp.resumeEvents();
            }
        },

        isTabReadonly: function(record) {
            var behaviors = record.get('behaviors').split(','),
                readonly = false,
                isCfRole = Ext.Array.contains(behaviors, 'cf_cloud_controller') || Ext.Array.contains(behaviors, 'cf_health_manager');
            
            if (isCfRole || Ext.Array.contains(behaviors, 'rabbitmq')) {
                readonly = true;
            }
            return readonly;
        },
		showTab: function (record) {
			var settings = record.get('settings'), 
                scaling = record.get('scaling'),
                metrics = moduleTabParams['metrics'],
                readonly = this.isTabReadonly(record);
            this.suspendLayouts();
            
            this.down('[name="scaling.enabled"]').setValue(settings['scaling.enabled'] == 1 ? '1' : '0').setReadOnly(readonly);
            this.down('#scalinggrid').setReadOnly(readonly);
            //this.down('#scalingsettings').setVisible(!readonly);
            
            var isCfRole = (record.get('behaviors').match("cf_cloud_controller") || record.get('behaviors').match("cf_health_manager"));
            Ext.each(this.query('field'), function(item){
                item.setDisabled(readonly && (item.name != 'scaling.min_instances' || isCfRole || !record.get('new')));
            });

            this.down('#scaling_safe_shutdown_compositefield').setVisible(true);
            this.down('[name="scaling.ignore_full_hour"]').setVisible(record.get('platform') === 'ec2');
            
            //set values
            this.setFieldValues({
                'scaling.min_instances': settings['scaling.min_instances'] || 1,
                'scaling.max_instances': settings['scaling.max_instances'] || 2,
                'scaling.polling_interval': settings['scaling.polling_interval'] || 1,
                'scaling.keep_oldest': settings['scaling.keep_oldest'] == 1,
                'scaling.ignore_full_hour': settings['scaling.ignore_full_hour'] == 1,
                'scaling.safe_shutdown': settings['scaling.safe_shutdown'] == 1,
                'scaling.exclude_dbmsr_master': settings['scaling.exclude_dbmsr_master'],
                'scaling.one_by_one': settings['scaling.one_by_one'] == 1,
                'scaling.upscale.timeout_enabled': settings['scaling.upscale.timeout_enabled'] == 1,
                'scaling.upscale.timeout': settings['scaling.upscale.timeout'] || 10,
                'scaling.downscale.timeout_enabled': settings['scaling.downscale.timeout_enabled'] == 1,
                'scaling.downscale.timeout': settings['scaling.downscale.timeout'] || 10
            });
            this.down('[name="scaling.upscale.timeout"]').setDisabled(settings['scaling.upscale.timeout_enabled'] != 1);
            this.down('[name="scaling.downscale.timeout"]').setDisabled(settings['scaling.downscale.timeout_enabled'] != 1);
            
            this.down('[name="scaling.exclude_dbmsr_master"]').setVisible(record.isDbMsr(true));
            
			this.down('[name="scaling_algo"]').store.load({ data: metrics });

            var dataToLoad = [];
            Ext.Object.each(scaling, function(id, settings){
                dataToLoad.push({
                    id: id,
                    settings: settings,
                    name: metrics[id].name,
                    alias: metrics[id].alias
                });
            })
            this.down('grid').store.loadData(dataToLoad);
            
			this.down('#timezone').setText('Time zone: <span style="color:#666">' + this.up('#fbcard').down('#farm').down('#timezone').getValue() +
					'</span> <a href="#">Change</a>', false);
            
            //workaround of restore collapsed state problem
            var scalingSettings = this.down('#scalingsettings');
            if (scalingSettings.collapsed) {
                scalingSettings.expand();
                scalingSettings.collapse();
            }
            this.resumeLayouts(true);
		},
        
        onScalingUpdate: function() {
            var record = this.currentRole,
                store = this.down('grid').getStore(),
                scaling = {};
            (store.snapshot || store.data).each(function(item){
                scaling[item.get('id')] = item.get('settings');
            });
            this.suspendOnRoleUpdate++;
            record.set('scaling', scaling);   
            this.suspendOnRoleUpdate--;
        },
        
		hideTab: function (record) {
			var settings = record.get('settings');
			var scaling = {},
                store = this.down('grid').getStore();
            (store.snapshot || store.data).each(function(item){
                scaling[item.get('id')] = item.get('settings');
            });
            
			settings['scaling.enabled'] = this.down('[name="scaling.enabled"]').getValue();

			settings['scaling.min_instances'] = this.down('[name="scaling.min_instances"]').getValue();
			settings['scaling.max_instances'] = this.down('[name="scaling.max_instances"]').getValue();
			settings['scaling.polling_interval'] = this.down('[name="scaling.polling_interval"]').getValue();
			settings['scaling.keep_oldest'] = this.down('[name="scaling.keep_oldest"]').getValue() == true ? 1 : 0;
			settings['scaling.ignore_full_hour'] = this.down('[name="scaling.ignore_full_hour"]').getValue() == true ? 1 : 0;
			settings['scaling.safe_shutdown'] = this.down('[name="scaling.safe_shutdown"]').getValue() == true ? 1 : 0;
			settings['scaling.exclude_dbmsr_master'] = this.down('[name="scaling.exclude_dbmsr_master"]').getValue() == true ? 1 : 0;
			settings['scaling.one_by_one'] = this.down('[name="scaling.one_by_one"]').getValue() == true ? 1 : 0;

			if (this.down('[name="scaling.upscale.timeout_enabled"]').getValue()) {
				settings['scaling.upscale.timeout_enabled'] = 1;
				settings['scaling.upscale.timeout'] = this.down('[name="scaling.upscale.timeout"]').getValue();
			} else {
				settings['scaling.upscale.timeout_enabled'] = 0;
				delete settings['scaling.upscale.timeout'];
			}

			if (this.down('[name="scaling.downscale.timeout_enabled"]').getValue()) {
				settings['scaling.downscale.timeout_enabled'] = 1;
				settings['scaling.downscale.timeout'] = this.down('[name="scaling.downscale.timeout"]').getValue();
			} else {
				settings['scaling.downscale.timeout_enabled'] = 0;
				delete settings['scaling.downscale.timeout'];
			}
            this.down('[name="scaling.enabled"]').reset();
			record.set('settings', settings);
			record.set('scaling', scaling);
		},

		items: [{
            xtype: 'container',
            maxWidth: 600,
            minWidth: 460,
            //padding: 9,
            flex: .7,
            cls: 'x-panel-columned-leftcol',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [{
                xtype: 'buttongroupfield',
                name: 'scaling.enabled',
                margin: '12 0 0 12',
                defaults: {
                    width: 80
                },
                items: [{
                    text: 'Manual',
                    value: '0'
                },{
                    text: 'Automatic',
                    value: '1'
                }],
                listeners: {
                    change: function(comp, value) {
                        var tab = comp.up('#scaling'),
                            record = tab.currentRole,
                            settings = record.get('settings');
                        
                        Ext.Array.each(['scalingdelim', 'scalinggrid', 'scalingsettings', 'scalingform'], function(id) {
                            tab.down('#'+id).setVisible(value === '1');
                        });
                        
                        if (settings[comp.name] != value) {
                            settings[comp.name] = value;
                            tab.suspendOnRoleUpdate++;
                            record.set('settings', settings);
                            tab.suspendOnRoleUpdate--;
                        }
                        
                    } 
                }
            },{
                xtype: 'component',
                itemId: 'scalingdelim',
                cls: 'x-fieldset-delimiter-large',
                margin: '12 0 4'
            },{
                xtype: 'grid',
                itemId: 'scalinggrid',
                padding: 9,
                selType: 'selectedmodel',
                cls: 'x-grid-shadow x-grid-role-scaling-rules x-grid-dark-focus',
                multiSelect: true,
                enableColumnResize: false,
                plugins: [{
                    ptype: 'rowpointer',
                    addOffset: 5,
                    addCls: 'x-panel-columned-row-pointer-light'
                }],
                store: {
                    fields: ['id', 'name', 'alias', 'min', 'max', 'settings'],
                    proxy: 'object'
                },
                columns: [{
                    text: 'Scale based on',
                    sortable: false,
                    dataIndex: 'name',
                    flex: 1.6
                },{
                    text: 'Scale out',
                    sortable: false,
                    dataIndex: 'max',
                    flex: 1,
                    xtype: 'templatecolumn', 
                    tpl: '<tpl if="alias===\'ram\'">'+
                         '{[values.settings.min ? \'< \' + values.settings.min : \'\']}'+
                         '<tpl else>'+
                         '{[values.settings.max ? \'> \' + values.settings.max : \'\']}'+
                         '</tpl>'
                },{
                    text: 'Scale in',
                    sortable: false,
                    dataIndex: 'min',
                    flex: 1,
                    xtype: 'templatecolumn', 
                    tpl: '<tpl if="alias===\'ram\'">'+
                         '{[values.settings.max ? \'> \' + values.settings.max : \'\']}'+
                         '<tpl else>'+
                         '{[values.settings.min ? \'< \' + values.settings.min : \'\']}'+
                         '</tpl>'
                }],
                viewConfig: {
                    plugins: {
                        ptype: 'dynemptytext',
                        emptyText: '',
                        emptyTextNoItems: 'Click on the <a href="#" class="add-link"><img src="'+Ext.BLANK_IMAGE_URL+'" class="scalr-ui-action-icon scalr-ui-action-icon-add" /></a> button above to add a scaling algorithm.',
                        onAddItemClick: function() {
                            var btn = this.client.ownerCt.up('panel').down('#add');
                            if (!btn.disabled && btn.isVisible()) btn.handler();
                        }
                    },
                    loadingText: 'Loading scaling algorithms ...',
                    deferEmptyText: false,
                    overItemCls: '',
                    overflowY: 'auto',
                    overflowX: 'hidden'
                },
                dockedItems: [{
                    cls: 'x-toolbar',
                    dock: 'top',
                    layout: 'hbox',
                    defaults: {
                        margin: '0 0 0 10'
                    },
                    items: [{
						xtype: 'textfield',
                        fieldLabel: 'Minimum instances',
                        labelStyle: 'color:#000',
                        fieldStyle: 'box-shadow: 0 1px 2px #93A0B3 inset;background:#fff',
                        labelWidth: 120,
						name: 'scaling.min_instances',
						width: 160,
                        margin: 0,
                        listeners: {
                            change: function(comp, value) {
                                var tab = comp.up('#scaling'),
                                    record = tab.currentRole,
                                    settings = record.get('settings');
                                settings[comp.name] = value;
                                tab.suspendOnRoleUpdate++;
                                record.set('settings', settings);
                                tab.suspendOnRoleUpdate--;
                            }
                        }
                    },{
						xtype: 'textfield',
                        fieldLabel: 'Maximum instances',
                        labelStyle: 'color:#000',
                        fieldStyle: 'box-shadow: 0 1px 2px #93A0B3 inset;background:#fff',
                        labelWidth: 125,
						name: 'scaling.max_instances',
						width: 165,
                        margin: '0 0 0 30',
                        listeners: {
                            change: function(comp, value) {
                                var tab = comp.up('#scaling'),
                                    record = tab.currentRole,
                                    settings = record.get('settings');
                                settings[comp.name] = value;
                                tab.suspendOnRoleUpdate++;
                                record.set('settings', settings);
                                tab.suspendOnRoleUpdate--;
                            }
                        }
					}, {
                        xtype: 'tbfill' 
                    },{
                        itemId: 'delete',
                        xtype: 'button',
                        iconCls: 'x-btn-groupacton-delete',
                        ui: 'action-dark',
                        disabled: true,
                        tooltip: 'Delete scaling algorithm',
                        handler: function() {
                            var grid = this.up('grid');
                            grid.getStore().remove(grid.getSelectionModel().getSelection());
                        }
                    },{
                        itemId: 'add',
                        xtype: 'button',
                        iconCls: 'x-btn-groupacton-add',
                        ui: 'action-dark',
                        tooltip: 'Add scaling algorithm',
                        handler: function() {
                            var grid = this.up('grid');
                            grid.getSelectionModel().setLastFocused(null);
                            grid.form.loadRecord(grid.getStore().createModel({}));
                        }
                    }]
                }],
                listeners: {
                    viewready: function() {
                        var me = this,
                            tab = me.up('#scaling');
                        me.form = me.up('panel').up('container').down('form');
                        me.getSelectionModel().on('focuschange', function(gridSelModel){
                            if (!me.disableOnFocusChange) {
                                if (gridSelModel.lastFocused) {
                                    if (gridSelModel.lastFocused != me.form.getRecord()) {
                                        me.form.loadRecord(gridSelModel.lastFocused);
                                    }
                                } else {
                                    me.form.deselectRecord();
                                }
                            }
                        });
                        me.store.on({
                            add: {fn: tab.onScalingUpdate, scope: tab},
                            update: {fn: tab.onScalingUpdate, scope: tab},
                            remove: {fn: tab.onScalingUpdate, scope: tab}
                        });
                    },
                    selectionchange: function(selModel, selected) {
                        this.down('#delete').setDisabled(!selected.length);
                    }
                },
                setReadOnly: function(readonly) {
                    //this.headerCt.el.setVisible(!readonly);
                    //this.body.setVisible(!readonly);
                    this.down('#add').setVisible(!readonly);
                    this.down('#delete').setVisible(!readonly);
                }
            }, {
               xtype: 'fieldset',
               itemId: 'scalingsettings',
               margin: '3 12 12 12',
               overflowY: 'auto',
               overflowX: 'hidden',
               title: 'Algorithm refinements',
               collapsible: true,
               stateId: 'farms-builder-scaling-options',
               stateful: true,
               toggleOnTitleClick: true,
               flex: 1,
               items: [{
                   xtype: 'container',
                   layout: {
                       type: 'hbox',
                       align: 'middle'
                   },
                   items: [{
                       xtype: 'label',
                       text: 'Polling interval (every)'
                   }, {
                       xtype: 'textfield',
                       name: 'scaling.polling_interval',
                       margin: '0 5',
                       width: 40
                   }, {
                       xtype: 'label',
                       text: 'minute(s)'
                   }]
               },{
                   xtype: 'checkbox',
                   name: 'scaling.one_by_one',
                   boxLabel: 'Do not up-scale role if there is at least one pending instance'
               },{
                   xtype: 'checkbox',
                   name: 'scaling.exclude_dbmsr_master',
                   boxLabel: 'Exclude database master from scaling metrics calculations'
               },{
                   xtype: 'checkbox',
                   name: 'scaling.keep_oldest',
                   boxLabel: 'Keep oldest instance running after scale down'
               },{
                   xtype: 'checkbox',
                   name: 'scaling.ignore_full_hour',
                   boxLabel: 'Do not wait for full hour during downscaling'
               },{
                   xtype: 'container',
                   layout: 'hbox',
                   itemId: 'scaling_safe_shutdown_compositefield',
                   items: [{
                       xtype: 'checkbox',
                       name: 'scaling.safe_shutdown',
                       width: 260,
                       boxLabel: 'Enable safe shutdown during downscaling'
                   }, {
                       xtype: 'displayinfofield',
                       margin: '0 0 0 5',
                       info:   'Scalr will terminate instance ONLY if script &#39;/usr/local/scalarizr/hooks/auth-shutdown&#39; return 1. ' +
                               'If script not found or return any other value Scalr WON&#39;T terminate this server.'
                   }]
               },{
                    xtype: 'component',
                    cls: 'x-fieldset-delimiter-large'
               },{
                    xtype: 'label',
                    cls: 'x-fieldset-subheader',
                    html: 'Delays'
               },{
                   xtype: 'container',
                   layout: {
                       type: 'hbox',
                       align: 'middle'
                   },
                   items: [{
                       xtype: 'checkbox',
                       boxLabel: 'Wait',
                       name: 'scaling.upscale.timeout_enabled',
                       handler: function (checkbox, checked) {
                           if (checked)
                               this.next('[name="scaling.upscale.timeout"]').enable();
                           else
                               this.next('[name="scaling.upscale.timeout"]').disable();
                       }
                   }, {
                       xtype: 'textfield',
                       name: 'scaling.upscale.timeout',
                       margin: '0 5',
                       width: 40
                   }, {
                       xtype: 'label',
                       flex: 1,
                       text: 'minute(s) after a new instance have been started before the next up-scale'
                   }]
               }, {
                   xtype: 'container',
                   layout: {
                       type: 'hbox',
                       align: 'middle'
                   },
                   items: [{
                       xtype: 'checkbox',
                       boxLabel: 'Wait',
                       name: 'scaling.downscale.timeout_enabled',
                       handler: function (checkbox, checked) {
                           if (checked)
                               this.next('[name="scaling.downscale.timeout"]').enable();
                           else
                               this.next('[name="scaling.downscale.timeout"]').disable();
                       }
                   }, {
                       xtype: 'textfield',
                       name: 'scaling.downscale.timeout',
                       margin: '0 5',
                       width: 40
                   }, {
                       xtype: 'label',
                       flex: 1,
                       text: 'minute(s) after a shutdown before shutting down another instance'
                   }]
               }]
           }]
        }, {
            xtype: 'container',
            itemId: 'scalingform',
            layout: 'fit',
            flex: 1,
            style: 'background: #F0F1F4;border-radius:0 4px 4px 0',
            margin: 0,
            items: {
                xtype: 'form',
                margin: 0,
                hidden: true,
                overflowY: 'auto',
                items: [{
                    xtype: 'fieldset',
                    margin: 0,
                    defaults: {
                        anchor: '100%',
                        labelWidth: 120
                    },
                    items: [{
                        xtype: 'container',
                        cls: 'x-fieldset-subheader',
                        layout: {
                            type: 'hbox',
                            align: 'left'
                        },
                        items: [{
                            xtype: 'label',
                            html: 'Scaling algorithm'
                        }, {
                            xtype: 'displayinfofield',
                            margin: '0 0 0 10',
                            info: 'Scaling algorithm description.'
                        }]
                    },{
                        xtype: 'combo',
                        store: {
                            fields: [ 'id', 'name', 'alias' ],
                            proxy: 'object'
                        },
                        maxWidth: 600,
                        valueField: 'id',
                        displayField: 'name',
                        editable: false,
                        queryMode: 'local',
                        name: 'scaling_algo',
                        emptyText: 'Please select scaling algorithm',
                        listeners: {
                            change: function(comp, value, oldValue) {
                                var formPanel = this.up('form'),
                                    record = formPanel.getForm().getRecord(),
                                    algos = formPanel.down('#algos');
                                if (value) {
                                    var alias = comp.findRecordByValue(value).get('alias');
                                    if (!formPanel.isLoading && formPanel.grid) {
                                        var forbidChange = false;
                                        if (formPanel.grid.store.find('id', value) !== -1) {
                                            Scalr.message.Error('This scaling algorithm already added.');
                                            forbidChange = true;
                                        } else if (
                                            !record.store && (formPanel.grid.store.find('alias', 'time') !== -1 || alias === 'time' && formPanel.grid.store.getCount() > 0) || 
                                            record.store && alias === 'time' && formPanel.grid.store.getCount() > 1
                                        ){
                                            Scalr.message.Error('DateAndTime algoritm cannot be used with others');
                                            forbidChange = true;
                                        }
                                        if (forbidChange) {
                                            this.suspendEvents(false);
                                            this.setValue(oldValue);
                                            this.resumeEvents(false);
                                            return;
                                        }
                                    }
                                    formPanel.updateRecordSuspended++;
                                    algos.layout.setActiveItem(alias);
                                    formPanel.showStat(alias);
                                    formPanel.updateRecordSuspended--;
                                    formPanel.updateRecord();
                                } else if (algos.layout.activeItem) {
                                    algos.layout.setActiveItem('blank');
                                    formPanel.hideStat();
                                }
                            }
                        }
                    /*}, {
                        xtype: 'container',
                        itemId: 'scalingAlgoDescription',
                        style: 'color:#666',
                        html: '&nbsp;'*/
                    }, {
                        xtype: 'component',
                        cls: 'x-fieldset-delimiter-large',
                        maxWidth: null
                    },{
                        xtype: 'container',
                        layout: 'card',
                        itemId: 'algos',
                        activeItem: 'blank',
                        defaults: {
                            listeners: {
                                beforeactivate: function() {
                                    var me = this;
                                    //default field values
                                    if (me.defaultValues) {
                                        Ext.Object.each(me.defaultValues, function(name, value){
                                            var field = me.down('[name="' + name + '"]'),
                                                fieldValue = field.getValue();
                                            if (Ext.isEmpty(fieldValue) || !fieldValue) {
                                                field.setValue(value);
                                            }
                                        });
                                    }
                                },
                                boxready: function() {
                                    var me = this,
                                        formPanel = me.up('form'),
                                        onFieldChange = function(comp, value){
                                            formPanel.updateRecord(comp.name, value);
                                        };
                                    if (me.defaultValues) {
                                        Ext.Object.each(me.defaultValues, function(name, value){
                                            var field = me.down('[name="' + name + '"]');
                                            field.on('change', onFieldChange, field);
                                        });
                                    }
                                    
                                }
                            }
                        },
                        items: [{
                            xtype: 'component',
                            itemId: 'blank'
                        },{
                            xtype: 'container',
                            itemId: 'la',
                            defaultValues: {
                                period: '15',
                                min: '2',
                                max: '5'
                            },
                            defaults: {
                                maxWidth: 340,
                                margin: '0 0 10 0'
                            },
                            items: [{
                                xtype: 'label',
                                cls: 'x-fieldset-subheader',
                                html: 'Downscaling and upscaling thresholds'
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    text: 'Use'
                                }, {
                                    xtype: 'combo',
                                    hideLabel: true,
                                    store: ['1','5','15'],
                                    allowBlank: false,
                                    editable: false,
                                    name: 'period',
                                    queryMode: 'local',
                                    margin: '0 5',
                                    width: 60
                                }, {
                                    xtype: 'label',
                                    text: 'minute(s) load averages for scaling'
                                }]
                            }, {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale in (release instances) when LA goes under'
                                }, {
                                    xtype: 'textfield',
                                    name: 'min',
                                    margin: '0 0 0 5',
                                    width: 40
                                }]
                            }, {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale out (add more instances) when LA goes over'
                                }, {
                                    xtype: 'textfield',
                                    name: 'max',
                                    margin: '0 0 0 5',
                                    width: 40
                                }]
                            }]
                        },{
                            xtype: 'container',
                            itemId: 'ram',
                            defaultValues: {
                                use_cached: false,
                                min: '',
                                max: ''
                            },
                            maxWidth: 400,
                            defaults: {
                                margin: '0 0 10 0'
                            },
                            items: [{
                                xtype: 'label',
                                cls: 'x-fieldset-subheader',
                                html: 'Downscaling and upscaling thresholds'
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale out (add more instances) when free RAM goes under'
                                }, {
                                    xtype: 'textfield',
                                    name: 'min',
                                    margin: '0 5',
                                    width: 40
                                }, {
                                    xtype: 'label',
                                    text: 'MB'
                                }]
                            }, {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale in (release instances) when free RAM goes over'
                                }, {
                                    xtype: 'textfield',
                                    name: 'max',
                                    margin: '0 5',
                                    width: 40
                                }, {
                                    xtype: 'label',
                                    text: 'MB'
                                }]
                            }, {
                                xtype: 'checkbox',
                                boxLabel: 'Use free+cached ram as scaling metric',
                                name: 'use_cached',
                                inputValue: '1'
                            }]
                        },{
                            xtype: 'container',
                            itemId: 'bw',
                            defaultValues: {
                                type: 'outbound',
                                min: '10',
                                max: '40'
                            },
                            maxWidth: 560,
                            defaults: {
                                margin: '0 0 10 0'
                            },
                            items: [{
                                xtype: 'label',
                                cls: 'x-fieldset-subheader',
                                html: 'Downscaling and upscaling thresholds'
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    text: 'Use'
                                }, {
                                    xtype: 'combo',
                                    hideLabel: true,
                                    store: [ 'inbound', 'outbound' ],
                                    allowBlank: false,
                                    editable: false,
                                    name: 'type',
                                    queryMode: 'local',
                                    margin: '0 5',
                                    width: 100
                                }, {
                                    xtype: 'label',
                                    text: ' bandwidth usage value for scaling'
                                }]
                            }, {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale in (release instances) when average bandwidth usage on role is less than'
                                }, {
                                    xtype: 'textfield',
                                    name: 'min',
                                    margin: '0 5',
                                    width: 40
                                }, {
                                    xtype: 'label',
                                    text: 'Mbit/s'
                                }]
                            }, {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale out (add more instances) when average bandwidth usage on role is more than'
                                }, {
                                    xtype: 'textfield',
                                    name: 'max',
                                    margin: '0 5',
                                    width: 40
                                }, {
                                    xtype: 'label',
                                    text: 'Mbit/s'
                                }]
                            }]
                        },{
                            xtype: 'container',
                            itemId: 'sqs',
                            maxWidth: 410,
                            defaultValues: {
                                queue_name: '',
                                min: '',
                                max: ''
                            },
                            defaults: {
                                margin: '0 0 10 0'
                            },
                            items: [{
                                xtype: 'label',
                                cls: 'x-fieldset-subheader',
                                html: 'Downscaling and upscaling thresholds'
                            },{
                                fieldLabel: 'Queue name',
                                xtype: 'textfield',
                                name: 'queue_name',
                                labelWidth: 80,
                                width: 300
                            }, {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale out (add more instances) when queue size goes over'
                                }, {
                                    xtype: 'textfield',
                                    name: 'max',
                                    margin:'0 5',
                                    width: 40
                                }, {
                                    xtype: 'label',
                                    text: 'items'
                                }]
                            }, {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale in (release instances) when queue size goes under'
                                }, {
                                    xtype: 'textfield',
                                    name: 'min',
                                    margin: '0 5',
                                    width: 40
                                }, {
                                    xtype: 'label',
                                    text: 'items'
                                }]
                            }]
                        },{
                            xtype: 'container',
                            itemId: 'http',
                            defaultValues: {
                                url: '',
                                min: '1',
                                max: '5'
                            },
                            maxWidth: 480,
                            defaults: {
                                margin: '0 0 10 0'
                            },
                            items: [{
                                xtype: 'label',
                                cls: 'x-fieldset-subheader',
                                html: 'Downscaling and upscaling thresholds'
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale out (add more instances) when URL response time more than'
                                }, {
                                    xtype: 'textfield',
                                    name: 'max',
                                    margin: '0 5',
                                    width: 40
                                }, {
                                    xtype: 'label',
                                    text: 'seconds'
                                }]
                            }, {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale in (release instances) when URL response time less than'
                                }, {
                                    xtype: 'textfield',
                                    name: 'min',
                                    margin: '0 5',
                                    width: 40
                                }, {
                                    xtype: 'label',
                                    text: 'seconds'
                                }]
                            }, {
                                xtype: 'textfield',
                                fieldLabel: 'URL (with http(s)://)',
                                name: 'url',
                                labelWidth: 80,
                                labelStyle: 'white-space:nowrap',
                                width: '100%'
                            }]
                        },{
                            xtype: 'container',
                            itemId: 'custom',
                            defaultValues: {
                                min: '',
                                max: ''
                            },
                            maxWidth: 390,
                            defaults: {
                                margin: '0 0 10 0'
                            },
                            items: [{
                                xtype: 'label',
                                cls: 'x-fieldset-subheader',
                                html: 'Downscaling and upscaling thresholds'
                            },{
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale out (add more instances) when metric value goes over'
                                }, {
                                    xtype: 'textfield',
                                    name: 'max',
                                    margin: '0 0 0 5',
                                    width: 40
                                }]
                            }, {
                                xtype: 'container',
                                layout: {
                                    type: 'hbox',
                                    align: 'middle'
                                },
                                items: [{
                                    xtype: 'label',
                                    flex: 1,
                                    text: 'Scale in (release instances) when metric value goes under'
                                }, {
                                    xtype: 'textfield',
                                    name: 'min',
                                    margin: '0 0 0 5',
                                    width: 40
                                }]
                            }]
                        },{
                            xtype: 'grid',
                            title: 'Schedule rules',
                            itemId: 'time',
                            hideHeaders: true,
                            maxWidth: 600,
                            store: {
                                fields: [ 'start_time', 'end_time', 'week_days', 'instances_count', 'id' ],
                                proxy: 'object'
                            },
                            selType: 'selectedmodel',
                            cls: 'x-grid-shadow x-panel-columned-leftcol x-grid-scaling-schedule-rules',
                            multiSelect: true,
                            viewConfig: {
                                emptyText: 'No schedule rules defined',
                                deferEmptyText: false,
                                focusedItemCls: '',
                                overItemCls: '',
                                getRowClass: function(record){
                                    return 'x-grid-row-scaling-grid';
                                }
                            },
                            columns: [{
                                xtype: 'templatecolumn', 
                                flex:1,
                                tpl: '<b>{instances_count}</b> instance(s), between <b>{start_time}</b> and <b>{end_time}</b> on <b>{week_days}</b>'
                            }],
                            onDataChange: function() {
                                var me = this,
                                form = me.up('form'),
                                data = [], records = me.store.getRange();
                                for (var i = 0; i < records.length; i++)
                                    data[data.length] = records[i].data;

                                form.updateRecord('settings', data);
                            },
                            listeners: {
                                viewready: function() {
                                    var me = this;
                                    me.store.on({
                                        add: {fn: me.onDataChange, scope: me},
                                        update: {fn: me.onDataChange, scope: me},
                                        remove: {fn: me.onDataChange, scope: me}
                                    });
                                },
                                selectionchange: function(selModel, selected) {
                                    this.down('#delete').setDisabled(!selected.length);
                                }
                            },
                            dockedItems: [{
                                cls: 'x-toolbar',
                                dock: 'top',
                                layout: {
                                    type: 'hbox',
                                    align: 'stretch'
                                },
                                defaults: {
                                    margin: '0 0 0 10'
                                },
                                items: [{
                                    xtype: 'label',
                                    itemId: 'timezone',
                                    flex: 1,
                                    margin:0,
                                    style: 'height:22px;overflow:hidden;line-height:24px',
                                    listeners: {
                                        render: function() {
                                            var me = this;
                                            me.el.on('click', function(e){
                                                var el = me.el.query('a');
                                                if (el.length && e.within(el[0])) {
                                                    var builder = me.up('#fbcard');
                                                    builder.prev().deselectAll();
                                                    builder.layout.setActiveItem('farm');
                                                    
                                                    e.preventDefault();
                                                }
                                            });
                                        }
                                    }
                                },{
                                    itemId: 'delete',
                                    xtype: 'button',
                                    iconCls: 'x-btn-groupacton-delete',
                                    ui: 'action-dark',
                                    disabled: true,
                                    tooltip: 'Delete period',
                                    handler: function() {
                                        var grid = this.up('grid');
                                        grid.getStore().remove(grid.getSelectionModel().getSelection());
                                    }
                                },{
                                    itemId: 'add',
                                    xtype: 'button',
                                    iconCls: 'x-btn-groupacton-add',
                                    ui: 'action-dark',
                                    tooltip: 'Add period',
                                    handler: function() {
                                        Scalr.Confirm({
                                            form: [{
                                                xtype: 'timefield',
                                                fieldLabel: 'Start time',
                                                name: 'ts_s_time',
                                                anchor: '100%',
                                                minValue: '0:00am',
                                                maxValue: '23:55pm',
                                                allowBlank: false
                                            }, {
                                                xtype: 'timefield',
                                                fieldLabel: 'End time',
                                                name: 'ts_e_time',
                                                anchor: '100%',
                                                minValue: '0:00am',
                                                maxValue: '23:55pm',
                                                allowBlank: false
                                            }, {
                                                xtype: 'checkboxgroup',
                                                fieldLabel: 'Days of week',
                                                columns: 3,
                                                items: [
                                                    { boxLabel: 'Sun', name: 'ts_dw_Sun', width: 50 },
                                                    { boxLabel: 'Mon', name: 'ts_dw_Mon' },
                                                    { boxLabel: 'Tue', name: 'ts_dw_Tue' },
                                                    { boxLabel: 'Wed', name: 'ts_dw_Wed' },
                                                    { boxLabel: 'Thu', name: 'ts_dw_Thu' },
                                                    { boxLabel: 'Fri', name: 'ts_dw_Fri' },
                                                    { boxLabel: 'Sat', name: 'ts_dw_Sat' }
                                                ]
                                            }, {
                                                xtype: 'numberfield',
                                                fieldLabel: 'Instances count',
                                                name: 'ts_instances_count',
                                                anchor: '100%',
                                                allowDecimals: false,
                                                minValue: 0,
                                                allowBlank: false
                                            }],
                                            ok: 'Add',
                                            title: 'Add new time scaling period',
                                            formValidate: true,
                                            closeOnSuccess: true,
                                            scope: this,
                                            success: function (formValues) {
                                                var store = this.up('grid').store,
                                                    week_days_list = '',
                                                    i = 0, k;

                                                for (k in formValues) {
                                                    if (k.indexOf('ts_dw_') != -1 && formValues[k] == 'on') {
                                                        week_days_list += k.replace('ts_dw_','')+', ';
                                                        i++;
                                                    }
                                                }

                                                if (i == 0) {
                                                    Scalr.message.Error('You should select at least one week day');
                                                    return false;
                                                }
                                                else
                                                    week_days_list = week_days_list.substr(0, week_days_list.length-2);

                                                var int_s_time = parseInt(formValues.ts_s_time.replace(/\D/g,''));
                                                var int_e_time = parseInt(formValues.ts_e_time.replace(/\D/g,''));

                                                if (formValues.ts_s_time.indexOf('AM') && int_s_time >= 1200)
                                                    int_s_time = int_s_time-1200;

                                                if (formValues.ts_e_time.indexOf('AM') && int_e_time >= 1200)
                                                    int_e_time = int_e_time-1200;

                                                if (formValues.ts_s_time.indexOf('PM') != -1)
                                                    int_s_time = int_s_time+1200;

                                                if (formValues.ts_e_time.indexOf('PM') != -1)
                                                    int_e_time = int_e_time+1200;

                                                if (int_e_time <= int_s_time) {
                                                    Scalr.message.Error('End time value must be greater than Start time value');
                                                    return false;
                                                }

                                                var record_id = int_s_time+':'+int_e_time+':'+week_days_list+':'+formValues.ts_instances_count;

                                                var recordData = {
                                                    start_time: formValues.ts_s_time,
                                                    end_time: formValues.ts_e_time,
                                                    instances_count: formValues.ts_instances_count,
                                                    week_days: week_days_list,
                                                    id: record_id
                                                };

                                                var list_exists = false;
                                                var list_exists_overlap = false;
                                                var week_days_list_array = week_days_list.split(", ");

                                                store.each(function (item, index, length) {
                                                    if (item.data.id == recordData.id) {
                                                        Scalr.message.Error('Same record already exists');
                                                        list_exists = true;
                                                        return false;
                                                    }

                                                    var chunks = item.data.id.split(':');
                                                    var s_time = chunks[0];
                                                    var e_time = chunks[1];
                                                    if (
                                                        (int_s_time >= s_time && int_s_time <= e_time) ||
                                                            (int_e_time >= s_time && int_e_time <= e_time)
                                                        )
                                                    {
                                                        var week_days_list_array_item = (chunks[2]).split(", ");
                                                        for (var ii = 0; ii < week_days_list_array_item.length; ii++)
                                                        {
                                                            for (var kk = 0; kk < week_days_list_array.length; kk++)
                                                            {
                                                                if (week_days_list_array[kk] == week_days_list_array_item[ii] && week_days_list_array[kk] != '')
                                                                {
                                                                    list_exists_overlap = "Period "+week_days_list+" "+formValues.ts_s_time+" - "+formValues.ts_e_time+" overlaps with period "+chunks[2]+" "+item.data.start_time+" - "+item.data.end_time;
                                                                    return true;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }, this);

                                                if (!list_exists && !list_exists_overlap) {
                                                    store.add(recordData);
                                                    return true;
                                                } else {
                                                    Scalr.message.Error((!list_exists_overlap) ? 'Same record already exists' : list_exists_overlap);
                                                    return false;
                                                }
                                            }
                                        });
                                    }
                                }]
                            }]
                        }]
                    }, {
                        xtype: 'container',
                        itemId: 'statpanel',
                        hidden: true,
                        items: [{
                            xtype: 'component',
                            cls: 'x-fieldset-delimiter-large'
                        },{
                            xtype: 'label',
                            cls: 'x-fieldset-subheader',
                            html: 'Statistics'
                        },{
                            xtype: 'label',
                            itemId: 'statstatus',
                            style: 'color:#666'
                        },{
                            xtype: 'image',
                            itemId: 'stat',
                            farm: moduleTabParams['farmId'],
                            style: 'max-width:537px;cursor:pointer',
                            width: '100%',
                            listeners: {
                                boxready: function(){
                                    var me = this;
                                    me.on('click', 
                                        function() {
                                            this.up('form').showStatPopup(this, this.farm, this.role, this.watcher)
                                        },
                                        me,
                                        {element: 'el'}
                                    );
                                }
                            }
                        }]
                    }]
                }],
                listeners: {
                    boxready: function() {
                        this.grid = this.up('panel').down('grid');
                    },
                    beforeloadrecord: function(record) {
                        var form = this.getForm();
                        this.isLoading = true;
                        form.reset(true);
                        this.down('#algos').down('#time').store.loadData({});
                    },

                    loadrecord: function(record) {
                        var isNewRecord = !record.store,
                            form = this.getForm(),
                            alias = record.get('alias');

                        form.clearInvalid();
                        form.findField('scaling_algo').setValue(record.get('id'));
                        
                        if (alias) {
                            if (alias === 'time') {
                                this.down('#algos').down('#'+record.get('alias')).store.loadData(record.get('settings') || {});
                            } else {
                                this.down('#algos').down('#'+record.get('alias')).setFieldValues(record.get('settings') || {});
                            }
                        }
                        if (!this.isVisible()) {
                            this.setVisible(true);
                            this.ownerCt.updateLayout();//this is required in extjs 4.1 to recalculate form dimensions after container size was changed, while form was hidden
                        }

                        this.isLoading = false;
                    }
                },
                
                updateRecordSuspended: 0,
                
                updateRecord: function(fieldName, fieldValue) {
                    var record = this.getRecord();
                        
                    if (this.isLoading || this.updateRecordSuspended || !record) {
                        return;
                    }
                    
                    var form = this.getForm(),
                        data = {
                            settings: record.get('settings') || {}
                        },
                        algoId = form.findField('scaling_algo').getValue(),
                        fieldsContainer = this.down('#algos').layout.getActiveItem();

                    if (fieldName) {
                        if (fieldName == 'settings') {
                            data['settings'] = fieldValue;
                        } else {
                            data['settings'][fieldName] = fieldValue;
                        }
                    } else {
                        var algoData = moduleTabParams['metrics'][algoId];
                        data['id'] = algoId;
                        data['name'] = algoData.name;
                        data['alias'] = algoData.alias;
                        data['settings'] = fieldsContainer.getFieldValues();
                    }
                    if (fieldName !== 'settings') {
                        data.min = data['settings'].min || undefined;
                        data.max = data['settings'].max || undefined;
                    }
                    
                    this.grid.suspendLayouts();
                    record.set(data);
                    if (record.store === undefined) {
                        this.grid.getStore().add(record);
                        this.grid.getSelectionModel().setLastFocused(record);
                    } else {
                        this.grid.getSelectionModel().lastFocused = null;//we lost row focus after record update for unknwo reason
                        this.grid.getSelectionModel().setLastFocused(record);
                    }
                    this.grid.resumeLayouts(true);

                },
                
                deselectRecord: function() {
                    var form = this.getForm();
                    this.setVisible(false);
                    this.isLoading = true;
                    form.reset();
                    if (form._record) {
                        delete form._record;//todo: replace with .getForm().reset(true) in latest extjs
                    }
                    this.isLoading = false;

                },
                
                hideStat: function() {
                    this.down('#statpanel').hide();
                },
                
                showStat: function(alias) {
                    var statPanel = this.down('#statpanel'),
                        stat = statPanel.down('#stat'),
                        statStatus = statPanel.down('#statstatus'),
                        record = this.up('#scaling').currentRole,
                        watchers = {la: 'LASNMP', ram: 'MEMSNMP', bw: 'NETSNMP'},
                        role = record.get('farm_role_id');
                    if (!record.get('new') && watchers[alias]) {
                        statStatus.setText('Loading...');
                        stat.hide();
                        statPanel.show();
                        stat.role = role;
                        stat.watcher = watchers[alias];
                        Scalr.Request({
                            scope: this,
                            //url: '/server/statistics.php?version=2&task=get_stats_image_url&farmid=13997&watchername=LASNMP&graph_type=daily&role=48557&_dc=1366717697420',
                            url: '/server/statistics_proxy.php?version=2&task=get_stats_image_url&farmid=' + stat.farm + '&watchername=' + stat.watcher + '&graph_type=daily&role=' + stat.role,
                            success: function (data, response, options) {
                                if (stat.rendered && !stat.destroyed && role == stat.role) {
                                    statStatus.setText('');
                                    stat.setSrc(data.msg);
                                    stat.show();
                                }
                            },
                            failure: function(data, response, options) {
                                if (stat.rendered && !stat.destroyed && role == stat.role) {
                                    statStatus.setText(data.msg);
                                }
                            }
                        });
                    } else {
                        statPanel.hide();
                    }
                },
                
                showStatPopup: function(comp, farm, role, watcher) {
                    var record = this.up('#scaling').currentRole,
                        statistics = {
                            MEMSNMP: {
                                height: 400,
                                title: ' / Memory Usage'
                            },
                            CPUSNMP: {
                                height: 352,
                                title: ' / CPU Utilization'
                            },
                            LASNMP: {
                                height: 319,
                                title: ' / Load Averages'
                            },
                            NETSNMP: {
                                height: 264,
                                title: ' / Network Usage'
                            }
                        };
                    Scalr.utils.Window({
                        animationTarget: comp,
                        xtype: 'monitoring.statisticswindow',
                        title: record.get('name') + statistics[watcher].title,

                        toolMenu: false,
                        typeMenu: true,
                        removeDockedItem: false,

                        watchername: watcher,
                        farm: farm,
                        role: role,

                        width: 537,
                        height: statistics[watcher].height,
                        bodyPadding: 0,
                        padding: 0,
                        autoScroll: false,

                        closable: true,
                        cls: null,
                        titleAlign: 'left',
                        tools: [{
                            type: 'refresh',
                            handler: function () {
                                this.up('panel').fillByStatistics();
                            }
                        }]
                    });

                }
                
            }
		}]
	});
});
