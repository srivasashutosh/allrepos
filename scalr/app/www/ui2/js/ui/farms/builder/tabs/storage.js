Scalr.regPage('Scalr.ui.farms.builder.tabs.storage', function (moduleTabParams) {
    var iopsMin = 100, 
        iopsMax = 4000, 
        integerRe = new RegExp('[0123456789]', 'i'), 
        maxEbsStorageSize = 1000;
        
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Storage',
		itemId: 'storage',
        layout: {
            type: 'hbox',
            align: 'stretch'
        },

        bodyCls: 'x-panel-body-frame x-panel-body-plain',

        isEnabled: function (record) {
			return true;
		},
		
		showTab: function (record) {
			var settings = this.down('#settings'),
                platform = record.get('platform'),
                storages = record.get('storages', true),
                data = [];

			this.down('#configuration').store.loadData(storages['configs'] || []);
			this.down('#configuration').devices = storages['devices'] || [];

			this.down('[name="ebs.snapshot"]').cloudLocation = record.get('cloud_location');
			this.down('[name="ebs.snapshot"]').forceSelection = false;

			// Storage engine
			if (platform == 'ec2') {
				data = [{
					name: 'ebs', description: 'Single EBS volume'
				}, {
					name: 'raid.ebs', description: 'RAID array (on EBS)'
				}];
			} else if (Ext.Array.contains(['cloudstack', 'idcf', 'ucloud'], platform)) {
				data = [{
					name: 'csvol', description: 'Single CS volume'
				}, {
					name: 'raid.csvol', description: 'RAID array (on CS volumes)'
				}];
			} else if (Ext.Array.contains(['openstack', 'rackspacengus', 'rackspacenguk'], platform)) {
                data = [{
                    name: 'cinder', description: 'Persistent disk'
                }, {
                    name: 'raid.cinder', description: 'RAID array (on Persistent disks)'
                }];
            }
			settings.down('[name="type"]').store.loadData(data);

			// Storage filesystem
			var data = [{ fs: 'ext3', description: 'Ext3' }];

			if ((record.get('os_family') == 'centos' && record.get('arch') == 'x86_64') ||
				(record.get('os_family') == 'ubuntu' && Ext.Array.contains(['10.04', '12.04'], record.get('os_generation')))
				) {
				if (moduleTabParams['featureMFS']) {
					data.push({ fs: 'ext4', description: 'Ext4'});
					data.push({ fs: 'xfs', description: 'XFS'});
				} else {
					data.push({ fs: 'ext4', description: 'Ext4 (Not available for your pricing plan)'});
					data.push({ fs: 'xfs', description: 'XFS (Not available for your pricing plan)'});
				}
			}
			settings.down('[name="fs"]').store.loadData(data);
			this.down('#editor').hide();
		},
		
		hideTab: function (record) {
			var storages = [];

			this.down('#configuration').store.each(function(record) {
				storages.push(record.getData());
			});

			var c = record.get('storages') || {};
			c['configs'] = storages;
			record.set('storages', c);
		},

		
		items: [{
            xtype: 'container',
            maxWidth: 900,
            minWidth: 460,
            flex: 1,
            cls: 'x-panel-columned-leftcol',
            layout: {
                type: 'vbox',
                align: 'stretch'
            },
            items: [{
                xtype: 'grid',
                itemId: 'configuration',
                cls: 'x-grid-shadow x-grid-dark-focus',
                margin: '5 0 0 0',
                selType: 'selectedmodel',
                multiSelect: true,
                padding: 9,
                plugins: [{
                    ptype: 'rowpointer',
                    addCls: 'x-panel-columned-row-pointer-light'
                }],
                viewConfig: {
                    //focusedItemCls: '',
                    plugins: {
                        ptype: 'dynemptytext',
                        emptyText: '',
                        emptyTextNoItems: '<div style="margin-top:-7px">Click on the <a class="add-link" href="#"><img src="'+Ext.BLANK_IMAGE_URL+'" class="scalr-ui-action-icon scalr-ui-action-icon-add" style="position:relative;top:5px" /></a> button above to add storage configuration.</div>',
                        onAddItemClick: function() {
                            this.client.ownerCt.up('panel').down('#add').handler();
                        }
                    },
                    getRowClass: function(record) {
                        if (record.get('status') == 'Pending delete')
                            return 'x-grid-row-striped';
                    }
                },
                store: {
                    proxy: 'object',
                    fields: [ 'id', 'type', 'fs', 'settings', 'mount', 'mountPoint', 'reUse', 'status', 'rebuild' ]
                },
                columns: [
                    { header: 'Type', flex: 2, sortable: true, dataIndex: 'type', xtype: 'templatecolumn', tpl:
                        new Ext.XTemplate('{[this.name(values.type)]}', {
                            name: function(type) {
                                var l = {
                                    'ebs': 'Single EBS volume',
                                    'csvol': 'Single CS volume',
									'cinder': 'Single persistent disk',
                                    'raid.ebs': 'RAID array (on EBS)',
                                    'raid.csvol': 'RAID array (on CS volumes)',
									'raid.cinder': 'RAID array (on Persistent disks)'
                                };

                                return l[type] || type;
                            }
                        })
                    },
                    { header: 'FS', flex: 1, sortable: true, dataIndex: 'fs', xtype: 'templatecolumn', tpl:
                        new Ext.XTemplate('{[this.name(values.fs)]}', {
                            name: function(type) {
                                var l = {
                                    'ext3': 'Ext3'
                                };

                                return l[type] || type;
                            }
                        })
                    },
                    { header: 'Re-use', width: 60, xtype: 'templatecolumn', sortable: false, align: 'center', tpl:
                        '<tpl if="reUse"><img src="/ui2/images/icons/true.png"><tpl else><img src="/ui2/images/icons/false.png"></tpl>'
                    },
                    { header: 'Mount point', flex: 2, sortable: true, dataIndex: 'mountPoint', xtype: 'templatecolumn', tpl:
                        '<tpl if="mountPoint">{mountPoint}<tpl else><img src="/ui2/images/icons/false.png"></tpl>'
                    },
                    { header: 'Description', flex: 3, sortable: false, dataIndex: 'type', xtype: 'templatecolumn', tpl:
                        new Ext.XTemplate(
                            '{[this.getDescription(values)]}', {
                            getDescription: function(v) {
                                var result = [],
                                    s;
                                if (Ext.Array.contains(['raid.ebs', 'raid.csvol', 'raid.cinder'], v.type)) {
                                    result.push('RAID ' + v['settings']['raid.level'] + ' on ' + v['settings']['raid.volumes_count'] + ' x');
                                }

                                if (Ext.Array.contains(['raid.ebs', 'ebs'], v.type)) {
                                    s = v['settings']['ebs.size'] + 'GB EBS volume';
                                    if (v['settings']['ebs.type'] == 'io1') {
                                        s += ' (' + v['settings']['ebs.iops'] + ' iops)';
                                    }
                                } else if (Ext.Array.contains(['raid.csvol', 'csvol'], v.type)) {
                                    s = v['settings']['csvol.size'] + 'GB CS volume';
                                } else if (Ext.Array.contains(['raid.cinder', 'cinder'], v.type)) {
                                    s = v['settings']['cinder.size'] + 'GB Persistent disk';
                                }

                                result.push(s);
                                return result.join(' ');
                            }
                        })
                    }
                ],

                listeners: {
                    selectionchange: function(grid, selections) {
                        this.down('#delete').setDisabled(!selections.length);
                    },
                    viewready: function() {
                        var me = this;
                        me.getSelectionModel().on('focuschange', function(gridSelModel) {
                            var dev = me.next();
                            dev.hide();
                            dev.store.removeAll();
                            if (gridSelModel.lastFocused) {
                                var id = gridSelModel.lastFocused.get('id');
                                if (me.devices[id]) {
                                    for (var i in me.devices[id])
                                        dev.store.add(me.devices[id][i]);
                                    dev.show();
                                }
                            }
                        });
                    }
                },
                dockedItems: [{
                    cls: 'x-toolbar',
                    dock: 'top',
                    layout: 'hbox',
                    defaults: {
                        margin: '0 0 0 10'
                    },
                    items: [{
                        xtype: 'label',
                        cls: 'x-fieldset-subheader',
                        html: 'Storages',
                        margin: 0
                    },{
                        xtype: 'tbfill' 
                    },{
                        itemId: 'delete',
                        xtype: 'button',
                        ui: 'action-dark',
                        iconCls: 'x-btn-groupacton-delete',
                        disabled: true,
                        tooltip: 'Delete storage configuration',
                        handler: function() {
                            var grid = this.up('#configuration'), selections = grid.getSelectionModel().getSelection();

                            for (var i = 0; i < selections.length; i++) {
                                if (! selections[i].get('id'))
                                    grid.getStore().remove(selections[i]);
                                else
                                    selections[i].set('status', 'Pending delete');
                            }
                        }
                    },{
                        itemId: 'add',
                        xtype: 'button',
                        ui: 'action-dark',
                        iconCls: 'x-btn-groupacton-add',
                        tooltip: 'Add storage configuration',
                        handler: function() {
                            var conf = this.up('#configuration'), editor = this.up('#storage').down('#editor');
                            conf.getSelectionModel().setLastFocused(null);
                            editor.loadRecord(conf.store.createModel({ reUse: 1 }), this.up('#storage').currentRole);
                        }
                    }]
                }]
            },{
                xtype: 'grid',
                flex: 1,
                padding: 9,
                itemId: 'devices',
                cls: 'x-grid-shadow',
                margin: '36 0 0 0',
                hidden: true,
                viewConfig: {
                    disableSelection: true,
                    deferEmptyText: false,
                    emptyText: 'Selected storage is not in use.'
                },
                store: {
                    proxy: 'object',
                    fields: [ 'serverIndex', 'serverId', 'serverInstanceId', 'farmRoleId', 'storageId', 'storageConfigId', 'placement' ]
                },
                columns: [
                    { header: 'Server Index', width: 110, sortable: true, dataIndex: 'serverIndex' },
                    { header: 'Server Id', flex: 1, sortable: true, dataIndex: 'serverId', xtype: 'templatecolumn', tpl:
                        '<tpl if="serverId"><a href="#/servers/{serverId}/extendedInfo">{serverId}</a> <tpl if="serverInstanceId">({serverInstanceId})</tpl><tpl else>Not running</tpl>'
                    },
                    { header: 'Storage Id', width: 130, sortable: true, dataIndex: 'storageId' },
                    { header: 'Placement', width: 100, sortable: true, dataIndex: 'placement' },
                    { header: 'Config', width: 80, sortable: false, dataIndex: 'config', xtype: 'templatecolumn', tpl:
                        '<a href="#" class="view">View</a>'
                    }
                ],

                listeners: {
                    itemclick: function (view, record, item, index, e) {
                        if (e.getTarget('a.view')) {
                            Scalr.Request({
                                processBox: {
                                    type: 'action',
                                    msg: 'Loading config ...'
                                },
                                url: '/farms/builder/xGetStorageConfig',
                                params: {
                                    farmRoleId: record.get('farmRoleId'),
                                    configId: record.get('storageConfigId'),
                                    serverIndex: record.get('serverIndex')
                                },
                                success: function(data) {
                                    Scalr.utils.Window({
                                        xtype: 'form',
                                        title: 'Storage config',
                                        width: 800,
                                        layout: 'fit',
                                        items: [{
                                            xtype: 'codemirror',
                                            readOnly: true,
                                            value: JSON.stringify(data.config, null, "\t"),
                                            mode: 'application/json',
                                            margin: '0 0 12 0'
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
                                                text: 'Close',
                                                handler: function() {
                                                    this.up('#box').close();
                                                }
                                            }]
                                        }]
                                    });
                                }
                            });
                            e.preventDefault();
                        }
                    }
                },
                dockedItems: [{
                    cls: 'x-toolbar',
                    dock: 'top',
                    items: [{
                        xtype: 'label',
                        cls: 'x-fieldset-subheader',
                        html: 'Storage usage',
                        margin: 0
                    }]
                }]
                
            }]
        },{
            xtype: 'container',
            layout: 'fit',
            flex: .7,
            style: 'background: #F0F1F4;border-radius:0 4px 4px 0',
            margin: 0,
            items: {
                xtype: 'form',
                itemId: 'editor',
                margin: 0,
                overflowY: 'auto',
                suspendLiveUpdate: 0,
                listeners: {
                    boxready: function() {
                        var me = this,
                            grid = me.up('#storage').down('#configuration'),
                            form = me.getForm();
                        grid.getSelectionModel().on('focuschange', function(gridSelModel) {
                            if (gridSelModel.lastFocused && (gridSelModel.lastFocused.get('status') == 'Pending create' || gridSelModel.lastFocused.get('status') == '')) {
                                if (gridSelModel.lastFocused !== form.getRecord()) {
                                    me.loadRecord(gridSelModel.lastFocused);
                                }
                            } else {
                                me.deselectRecord();
                            }
                        });
                        form.getFields().each(function(){
                            this.on('change', me.onLiveUpdate, me)
                        });
                    },
                    beforeloadrecord: function() {
                        var form = this.getForm();
                        this.suspendLiveUpdate++;
                        form.reset(true);
                    },
                    loadrecord: function(record) {
                        var form = this.getForm();
                        form.setValues(record.get('settings'));
                        form.clearInvalid();
                        if (!this.isVisible()) {
                            this.setVisible(true);
                        }
                        this.suspendLiveUpdate--;
                    },
                    updaterecord: function(record) {
                        this.updateSettings();
                    }
                },
                deselectRecord: function() {
                    var form = this.getForm();
                    this.setVisible(false);
                    this.suspendLiveUpdate++;
                    form.reset();
                    if (form._record) {
                        delete form._record;//todo: replace with .getForm().reset(true) in latest extjs
                    }
                    this.suspendLiveUpdate--;

                },
                isSettingsField: function(name) {
                    return name.indexOf('ebs') === 0 || name.indexOf('raid') === 0 || name.indexOf('csvol') === 0 || name.indexOf('cinder') === 0;
                },
                updateSettings: function() {
                    var me = this,
                        form = me.getForm(),
                        settings = {};
                    Ext.Object.each(form.getFieldValues(), function(name, value){
                        if (me.isSettingsField(name)) {
                            settings[name] = value;
                        }
                    });
                    form.getRecord().set('settings', settings);
                },
                onLiveUpdate: function(field, value, oldValue) {
                    if (this.suspendLiveUpdate > 0) return;
                    var me = this,
                        form = me.getForm(),
                        record = form.getRecord();
                    
                    if (form.isValid()) {
                        form.updateRecord();
                        var conf = me.up('#storage').down('#configuration');
                        if (!record.store) {
                            conf.store.add(record);
                            conf.getView().focusRow(record);
                            conf.getSelectionModel().setLastFocused(record);
                        } else {
                            conf.getSelectionModel().lastFocused = null;//we lost row focus after record update for unknwo reason
                            conf.getSelectionModel().setLastFocused(record);
                        }
                    }
                },
                
                items: [{
                    xtype: 'fieldset',
                    title: 'Storage configuration',
                    itemId: 'settings',
                    defaults: {
                        labelWidth: 110,
                        anchor: '100%',
                        maxWidth: 480
                    },
                    items: [{
                        xtype: 'combo',
                        name: 'type',
                        fieldLabel: 'Storage engine',
                        editable: false,
                        store: {
                            fields: [ 'description', 'name' ],
                            proxy: 'object'
                        },
                        valueField: 'name',
                        displayField: 'description',
                        queryMode: 'local',
                        allowBlank: false,
                        emptyText: 'Please select storage engine',
                        listeners: {
                            change: function(field, value) {
                                var editor = this.up('#editor'),
                                    ebs = editor.down('#ebs_settings'),
                                    ebsSnapshots = editor.down('[name="ebs.snapshot"]'),
                                    raid = editor.down('#raid_settings'),
                                    csvol = editor.down('#csvol_settings'),
									cinder = editor.down('#cinder_settings');

                                ebs[ value == 'ebs' || value == 'raid.ebs' ? 'show' : 'hide' ]();
                                ebs[ value == 'ebs' || value == 'raid.ebs' ? 'enable' : 'disable' ]();
                                ebsSnapshots[ value == 'ebs' ? 'show' : 'hide' ]();
                                
								raid[ value == 'raid.ebs' || value == 'raid.csvol' || value == 'raid.cinder' ? 'show' : 'hide' ]();
                                raid[ value == 'raid.ebs' || value == 'raid.csvol' || value == 'raid.cinder' ? 'enable' : 'disable' ]();
                                
								csvol[ value == 'csvol' || value == 'raid.csvol' ? 'show' : 'hide' ]();
                                csvol[ value == 'csvol' || value == 'raid.csvol' ? 'enable' : 'disable' ]();
								
								cinder[ value == 'cinder' || value == 'raid.cinder' ? 'show' : 'hide' ]();
                                cinder[ value == 'cinder' || value == 'raid.cinder' ? 'enable' : 'disable' ]();

                                if (value == 'raid.ebs' || value == 'raid.csvol' || value == 'raid.cinder') {
                                    // set default values for raid configuration
                                    raid.down('[name="raid.level"]').setValue('10');
                                }
                            }
                        }
                    },{
                        xtype: 'combo',
                        name: 'fs',
                        fieldLabel: 'Filesystem',
                        editable: false,
                        store: {
                            fields: [ 'fs', 'description' ],
                            proxy: 'object'
                        },
                        valueField: 'fs',
                        displayField: 'description',
                        queryMode: 'local',
                        emptyText: 'Please select filesystem',
                        allowBlank: false
                    }, {
                        xtype: 'container',
                        layout: {
                            type: 'hbox',
                            align: 'middle'
                        },
                        items: [{
                            xtype: 'checkbox',
                            name: 'reUse',
                            boxLabel: 'Re-use'
                        }, {
                            xtype: 'displayinfofield',
                            value: "If re-use is checked, volume will be always re-attached to the replaced server. If it's not checked during server replacement new volume will be created according to the settings and old one will be removed.",
                            margin: '0 0 0 5'
                        }]
                    }, {
                        xtype: 'container',
                        layout: 'hbox',
                        items: [{
                            xtype: 'checkbox',
                            name: 'rebuild',
                            boxLabel: 'Regenerate storage if missing volumes'
                        }, {
                            xtype: 'displayinfofield',
                            value: "If this checkbox checked and scalr will find that volume(s) are missed, storage will be re-created from scratch based on configuration",
                            margin: '0 0 0 5'
                        }]
                    }, {
                        xtype: 'container',
                        layout: {
                            type: 'hbox',
                            align: 'middle'
                        },
                        items: [{
                            xtype: 'checkbox',
                            boxLabel: 'Automatically mount device to',
                            name: 'mount',
                            inputValue: 1,
                            handler: function (field, checked) {
                                if (checked)
                                    this.next('[name="mountPoint"]').enable();
                                else
                                    this.next('[name="mountPoint"]').setValue('').disable();
                            }
                        }, {
                            xtype: 'textfield',
                            margin: '0 0 0 5',
                            disabled: true,
                            validator: function(value) {
                                var valid = true;
                                if (this.prev().getValue() && Ext.isEmpty(value)) {
                                    valid = 'Field is required';
                                }
                                return valid;
                            },
                            flex: 1,
                            name: 'mountPoint'
                        }]
                    }]
                }, {
                    xtype:'fieldset',
                    itemId: 'raid_settings',
                    style: 'padding-top:0',
                    defaults: {
                        labelWidth: 110,
                        anchor: '100%',
                        maxWidth: 480
                    },
                    items: [{
                        xtype: 'component',
                        cls: 'x-fieldset-delimiter',
                        maxWidth: null
                    },{
                        xtype: 'label',
                        cls: 'x-fieldset-subheader',
                        text: 'RAID settings'
                    },{
                        xtype: 'container',
                        layout: {
                            type: 'hbox',
                            align: 'middle'
                        },
                        anchor: '100%',
                        items: [{
                            xtype: 'combo',
                            name: 'raid.level',
                            hideLabel: true,
                            editable: false,
                            store: {
                                fields: [ 'name', 'description' ],
                                proxy: 'object',
                                data: [
                                    { name: '0', description: 'RAID 0 (block-level striping without parity or mirroring)' },
                                    { name: '1', description: 'RAID 1 (mirroring without parity or striping)' },
                                    { name: '5', description: 'RAID 5 (block-level striping with distributed parity)' },
                                    { name: '10', description: 'RAID 10 (mirrored sets in a striped set)' }
                                ]
                            },
                            valueField: 'name',
                            displayField: 'description',
                            value: '0',
                            queryMode: 'local',
                            flex: 1,
                            allowBlank: false,
                            listeners: {
                                change: function() {
                                    var data = [], field = this.next('[name="raid.volumes_count"]');

                                    if (this.getValue() == '0') {
                                        data = [{ id: 2, name: 2 }, { id: 3, name: 3 }, { id: 4, name: 4 }, { id: 5, name: 5 },
                                            { id: 6, name: 6 }, { id: 7, name: 7 }, { id: 8, name: 8 }];
                                    } else if (this.getValue() == '1') {
                                        data = [{ id: 2, name: 2 }];
                                    } else if (this.getValue() == '5') {
                                        data = [{ id: 3, name: 3 }, { id: 4, name: 4 }, { id: 5, name: 5 },
                                            { id: 6, name: 6 }, { id: 7, name: 7 }, { id: 8, name: 8 }];
                                    } else if (this.getValue() == '10') {
                                        data = [{ id: 4, name: 4 }, { id: 6, name: 6 }, { id: 8, name: 8 }];
                                    } else {
                                        field.reset();
                                        field.disable();
                                        return;
                                    }

                                    field.store.loadData(data);
                                    field.enable();
                                    if (! field.getValue())
                                        field.setValue(field.store.first().get('id'));
                                }
                            }
                        }, {
                            xtype: 'label',
                            text: 'on',
                            margin: '0 0 0 5'
                        }, {
                            xtype: 'combo',
                            name: 'raid.volumes_count',
                            disabled: true,
                            editable: false,
                            width: 45,
                            store: {
                                fields: [ 'id', 'name'],
                                proxy: 'object'
                            },
                            valueField: 'id',
                            displayField: 'name',
                            queryMode: 'local',
                            margin: '0 0 0 5'
                        }, {
                            xtype: 'label',
                            text: 'volumes',
                            margin: '0 0 0 5'
                        }]
                    }]
                }, {
                    xtype:'fieldset',
                    itemId: 'ebs_settings',
                    defaults: {
                        labelWidth: 110,
                        anchor: '100%',
                        maxWidth: 480
                    },
                    style: 'padding-top:0',
                    items: [{
                        xtype: 'component',
                        cls: 'x-fieldset-delimiter',
                        maxWidth: null
                    },{
                        xtype: 'label',
                        cls: 'x-fieldset-subheader',
                        text: 'Volume settings'
                    }, {
                        xtype: 'textfield',
                        name: 'ebs.size',
                        fieldLabel: 'Size (GB)',
                        allowBlank: false,
                        maxWidth: 170,
                        validator: function(value) {
                            var form = this.up('#editor'),
                                field = form.down('[name="ebs.snapshot"]');
                            if (! field.isDisabled() && field.getValue()) {
                                var record = field.findRecord(field.valueField, field.getValue());
                                if (record && (parseInt(value) < record.get('size')))
                                    return 'Value must be bigger than snapshot size: ' + record.get('size') + 'GB';
                            }
                            
                            var minValue = 1;
                            if (form.down('[name="ebs.type"]').getValue() === 'io1') {
                                minValue = Math.ceil(form.down('[name="ebs.iops"]').getValue()*1/10);
                            }
                            if (value*1 > maxEbsStorageSize) {
                                return 'Maximum value is ' + maxEbsStorageSize + '.';
                            } else if (value*1 < minValue) {
                                return 'Minimum value is ' + minValue + '.';
                            }
                            
                            return true;
                        }
                    }, {
                        xtype: 'fieldcontainer',
                        layout: 'hbox',
                        fieldLabel: 'EBS type',
                        items: [{
                            xtype: 'combo',
                            store: [['standard', 'Standard'], ['io1', 'Provisioned IOPS (' + iopsMin + ' - ' + iopsMax + '): ']],
                            valueField: 'id',
                            displayField: 'name',
                            editable: false,
                            queryMode: 'local',
                            value: 'standard',
                            name: 'ebs.type',
                            flex: 1,
                            listeners: {
                                change: function (comp, value) {
                                    var form = comp.up('form'),
                                        iopsField = form.down('[name="ebs.iops"]');
                                    if (value == 'io1') {
                                        iopsField.show().enable().focus(false, 100);
                                        var value = iopsField.getValue();
                                        iopsField.setValue(value || 100);
                                    } else {
                                        iopsField.hide().disable();
                                        form.down('[name="ebs.size"]').isValid();
                                    }
                                }
                            }
                        }, {
                            xtype: 'textfield',
                            name: 'ebs.iops',
                            hidden: true,
                            disabled: true,
                            margin: '0 0 0 5',
                            maskRe: integerRe,
                            validator: function(value){
                                if (value*1 > iopsMax) {
                                    return 'Maximum value is ' + iopsMax + '.';
                                } else if (value*1 < iopsMin) {
                                    return 'Minimum value is ' + iopsMin + '.';
                                }
                                return true;
                            },
                            flex: 1,
                            maxWidth: 60
                        }]
                    }, {
                        xtype: 'combo',
                        fieldLabel: 'Snapshot',
                        name: 'ebs.snapshot',
                        emptyText: 'Create an empty volume',
                        valueField: 'snapshotId',
                        displayField: 'snapshotId',
                        queryMode: 'local',
                        anchor: '100%',
                        matchFieldWidth: true,
                        store: {
                            fields: [ 'snapshotId', 'createdDate', 'size', 'volumeId', 'description' ],
                            proxy: 'object'
                        },
                        listConfig: {
                            cls: 'x-boundlist-alt',
                            tpl:
                                '<tpl for="."><div class="x-boundlist-item" style="height: auto; width: auto">' +
                                    '<tpl if="snapshotId">' +
                                        '<div style="font-weight: bold">{snapshotId} ({size}GB)</div>' +
                                        '<div>created <span style="font-weight: bold">{createdDate}</span> on <span style="font-weight: bold">{volumeId}</span></div>' +
                                        '<div style="font-style: italic; font-size: 11px;">{description}</div>' +
                                    '<tpl else><div style="line-height: 26px;">Create an empty volume</div></tpl>' +
                                '</div></tpl>'
                        },
                        filterFn: function(queryString, item) {
                            var value = new RegExp(queryString);
                            return (
                                value.test(item.get('snapshotId')) ||
                                    value.test(item.get('volumeId')) ||
                                    value.test(item.get('description'))
                                ) ? true : false;
                        },
                        listeners: {
                            expand: function() {
                                var me = this;
                                if (me.cloudLocation != me.loadedCloudLocation) {
                                    me.collapse();
                                    me.store.removeAll();
                                    me.up('#farmbuilder').cache.load(
                                        {
                                            url: '/platforms/ec2/xGetSnapshots',
                                            params: {
                                                cloudLocation: me.cloudLocation
                                            }
                                        },
                                        function(data, status) {
                                            if (!status) return;
                                            me.loadedCloudLocation = me.cloudLocation;
                                            me.store.loadData([{instanceId: ''}]);
                                            me.store.loadData(data || [], true);
                                            me.forceSelection = true;
                                            me.expand();
                                        },
                                        this,
                                        0
                                    );
                                }
                            }
                        }
                    }]
                }, {
                    xtype:'fieldset',
                    itemId: 'csvol_settings',
                    defaults: {
                        labelWidth: 100,
                        width: 300
                    },
                    style: 'padding-top:0',
                    items: [{
                        xtype: 'component',
                        cls: 'x-fieldset-delimiter',
                        maxWidth: null
                    },{
                        xtype: 'label',
                        cls: 'x-fieldset-subheader',
                        text: 'Volume settings'
                    },{
                        xtype: 'container',
                        layout: {
                            type: 'hbox',
                            align: 'middle'
                        },
                        items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Size',
                            labelWidth: 50,
                            maxWidth: 110,
                            name: 'csvol.size',
                            allowBlank: false
                        },{
                            xtype: 'label',
                            text: 'GB',
                            margin: '0 0 0 6'
                        }]
                    }]
                }, {
                    xtype:'fieldset',
                    itemId: 'cinder_settings',
                    defaults: {
                        labelWidth: 100,
                        width: 300
                    },
                    style: 'padding-top:0',
                    items: [{
                        xtype: 'component',
                        cls: 'x-fieldset-delimiter',
                        maxWidth: null
                    },{
                        xtype: 'label',
                        cls: 'x-fieldset-subheader',
                        text: 'Volume settings'
                    },{
                        xtype: 'container',
                        layout: {
                            type: 'hbox',
                            align: 'middle'
                        },
                        items: [{
                            xtype: 'textfield',
                            fieldLabel: 'Size',
                            labelWidth: 50,
                            maxWidth: 110,
                            name: 'cinder.size',
                            allowBlank: false
                        },{
                            xtype: 'label',
                            text: 'GB',
                            margin: '0 0 0 6'
                        }]
                    }]
                }]
            }
        }]
	});
});