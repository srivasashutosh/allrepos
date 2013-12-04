Scalr.regPage('Scalr.ui.farms.builder.addrole.dbmsr', function () {
    var iopsMin = 100, 
        iopsMax = 4000, 
        integerRe = new RegExp('[0123456789]', 'i'), 
        maxEbsStorageSize = 1000;
    
    return {
        xtype: 'container',
        itemId: 'dbmsr',
        isExtraSettings: true,
        hidden: true,
        suspendUpdateEvent: 0,
        
        cls: 'x-delimiter-top',
        padding: '18 0',
        
        layout: 'anchor',
        defaults: {
            anchor: '100%',
            padding: '0 24'
        },

        isVisibleForRole: function(record) {
            return record.isDbMsr();
        },

        onSettingsUpdate: function(record, name, value) {
            this.suspendUpdateEvent++;
            if (name === 'aws.instance_type' || name === 'gce.machine-type') {
                this.refreshStorageEngine(record);
                this.refreshStorageDisks(record);
                this.refreshLvmCheckboxes(record);
            }
            this.suspendUpdateEvent--;
        },
        
        refreshStorageEngine: function(record) {
            var field = this.down('[name="db.msr.data_storage.engine"]'),
                currentValue = field.getValue();
			field.store.load({
				data: record.getAvailableStorages()
			});
            
            field.setValue(field.findRecordByValue(currentValue) ? currentValue : record.getDefaultStorageEngine());
            
        },
        
        refreshStorageDisks: function(record) {
            var field =  this.down('[name="db.msr.data_storage.eph.disk"]');
            field.store.load({data: record.getAvailableStorageDisks()});
            field.setValue(field.store.getAt(field.store.getCount() > 1 ? 1 : 0).get('device'));
        },
        
        refreshLvmCheckboxes: function(record) {
            var ephemeralDevicesMap = record.getEphemeralDevicesMap();
            if (ephemeralDevicesMap === undefined) return;
            
            var platform = record.get('platform'),
                settings = record.get('settings'),
                instanceType;
                
			if (platform === 'gce') {
				instanceType = settings['gce.machine-type'];
			} else if (platform === 'ec2') {
				instanceType = settings['aws.instance_type'];
			} 
			var cont = this.down('#lvm_settings');
            cont.suspendLayouts();
            cont.removeAll();
			if (Ext.isDefined(ephemeralDevicesMap[instanceType])) {
				var devices = ephemeralDevicesMap[instanceType], 
                    size = 0;

				for (var d in devices) {
					cont.add({
						xtype: 'checkbox',
						name: d,
						boxLabel: d + ' (' + devices[d]['size'] + 'Gb)',
						ephSize: devices[d]['size'],
						checked: false,
						handler: function() {
							/*var s = 0;
							Ext.each(cont.query('checkbox'), function() {
								if (this.getValue())
									s += parseInt(this.ephSize);
							});

							cont.down('displayfield').setValue(s + 'Gb');*/
						}
					});
					size += parseInt(devices[d]['size']);
				}

				/*cont.add({
					xtype: 'displayfield',
					fieldLabel: 'Total size',
					labelWidth: 80,
					value: size + 'Gb'
				});*/
			} else {
                cont.add({
					xtype: 'displayfield',
					value: 'LVM device'
                });
            }
            cont.resumeLayouts(true);
        },
        
        onSelectImage: function(record) {
            this.suspendUpdateEvent++;
            if (this.isVisibleForRole(record)) {
                this.setRole(record);
                this.show();
            } else {
                this.hide();
            }
            this.suspendUpdateEvent--;
        },

        setRole: function(record) {
            var moduleTabParams = this.up('roleslibrary').moduleParams['tabParams'],
                field;
                
			//storage engine
            this.refreshStorageEngine(record);
            
            //fs
            field = this.down('[name="db.msr.data_storage.fstype"]');
            field.suspendLayouts();
            field.removeAll();
            field.add(record.getAvailableStorageFs(moduleTabParams['featureMFS']));
			field.setValue('ext3');
            field.resumeLayouts(true);
            
            //disks
            this.refreshStorageDisks(record);
            
            //raid
			field = this.down('[name="db.msr.data_storage.raid.level"]');
			field.store.load({
				data: record.getAvailableStorageRaids()
			});
			field.setValue('10');
            
            //lvm
            this.refreshLvmCheckboxes(record);
            
			if (Ext.Array.contains(record.get('behaviors', true), 'redis')) {
				this.down('[name="db.msr.redis.persistence_type"]').setValue('snapshotting');
				this.down('[name="db.msr.redis.use_password"]').setValue('1');
				this.down('[name="db.msr.redis.num_processes"]').setValue(1);
				
				this.down('#redis_settings').show();
			} else {
				this.down('#redis_settings').hide();
			}
            
            this.down('[name="db.msr.redis.num_processes"]').setValue(1);
            this.down('[name="db.msr.data_storage.ebs.iops"]').setValue(100);
            this.down('[name="db.msr.data_storage.ebs.size"]').setValue(10);
            this.down('[name="db.msr.data_storage.cinder.size"]').setValue(100);
            this.down('[name="db.msr.data_storage.gced.size"]').setValue(100);
        },

        isValid: function() {
            var storageEngine = this.down('[name="db.msr.data_storage.engine"]').getValue(),
                res = true;
            if (Ext.Array.contains(['ebs', 'csvol'], storageEngine)) {
                res = this.down('[name="db.msr.data_storage.ebs.size"]').validate();
                if (res && this.down('[name="db.msr.data_storage.ebs.type"]').getValue() === 'io1') {
                    res = this.down('[name="db.msr.data_storage.ebs.iops"]').validate();
                }
            } else if (storageEngine === 'cinder') {
                res = this.down('[name="db.msr.data_storage.cinder.size"]').validate();
            } else if (Ext.Array.contains(['raid.ebs', 'raid.gce_persistent'], storageEngine)) {
                res = this.down('[name="db.msr.data_storage.raid.volume_size"]').validate();
                if (res && storageEngine === 'raid.ebs' && this.down('[name="db.msr.data_storage.raid.ebs.type"]').getValue() === 'io1') {
                    res = this.down('[name="db.msr.data_storage.raid.ebs.iops"]').validate();
                }
            }
            return res;
        },

        getSettings: function(record) {
            var settings = {
				'db.msr.data_storage.engine': this.down('[name="db.msr.data_storage.engine"]').getValue(),
				'db.msr.data_storage.fstype': this.down('[name="db.msr.data_storage.fstype"]').getValue()
            }
            
			if (settings['db.msr.data_storage.engine'] === 'eph') {
				settings['db.msr.data_storage.eph.disk'] = this.down('[name="db.msr.data_storage.eph.disk"]').getValue();
            } else if (settings['db.msr.data_storage.engine'] === 'lvm') {
                var volumes = {};
                Ext.each(this.down('#lvm_settings').query('checkbox'), function() {
                    if (this.getValue()) {
                        volumes[this.getName()] = this.ephSize;
                    }
                });
                
                settings['db.msr.storage.lvm.volumes'] = Ext.encode(volumes);
            } else if (Ext.Array.contains(['raid.ebs', 'raid.gce_persistent'], settings['db.msr.data_storage.engine'])) {
				settings['db.msr.data_storage.raid.level'] = this.down('[name="db.msr.data_storage.raid.level"]').getValue();
				settings['db.msr.data_storage.raid.volume_size'] = this.down('[name="db.msr.data_storage.raid.volume_size"]').getValue();
				settings['db.msr.data_storage.raid.volumes_count'] = this.down('[name="db.msr.data_storage.raid.volumes_count"]').getValue();
				
                if (settings['db.msr.data_storage.engine'] === 'raid.ebs') {
                    settings['db.msr.data_storage.raid.ebs.type'] = this.down('[name="db.msr.data_storage.raid.ebs.type"]').getValue();
                    settings['db.msr.data_storage.raid.ebs.iops'] = this.down('[name="db.msr.data_storage.raid.ebs.iops"]').getValue();
                }
			} else if (settings['db.msr.data_storage.engine'] == 'cinder') {
				settings['db.msr.data_storage.cinder.size'] = this.down('[name="db.msr.data_storage.cinder.size"]').getValue();
			} else if (settings['db.msr.data_storage.engine'] == 'gce_persistent') {
				settings['db.msr.data_storage.gced.size'] = this.down('[name="db.msr.data_storage.gced.size"]').getValue();
			} else if (Ext.Array.contains(['ebs', 'csvol'], settings['db.msr.data_storage.engine'])) {
                settings['db.msr.data_storage.ebs.size'] = this.down('[name="db.msr.data_storage.ebs.size"]').getValue();
                settings['db.msr.data_storage.ebs.type'] = this.down('[name="db.msr.data_storage.ebs.type"]').getValue();
                settings['db.msr.data_storage.ebs.iops'] = this.down('[name="db.msr.data_storage.ebs.iops"]').getValue();
            }
            
			if (Ext.Array.contains(record.get('behaviors', true), 'redis')) {
				settings['db.msr.redis.persistence_type'] = this.down('[name="db.msr.redis.persistence_type"]').getValue();
				settings['db.msr.redis.use_password'] = this.down('[name="db.msr.redis.use_password"]').getValue();
				settings['db.msr.redis.num_processes'] = this.down('[name="db.msr.redis.num_processes"]').getValue();
			}
            
            
            return settings;
        },

        items: [{
            xtype: 'container',
            itemId: 'redis_settings',
            cls: 'x-delimiter-bottom',
            hidden: true,
            margin: '0 0 18 0',
            layout: 'anchor',
            items: [{ 
                xtype: 'label',
                cls: 'x-fieldset-subheader',
                text: 'Redis settings'
            },{
                xtype: 'container',
                layout: 'hbox',
                defaults: {
                    maxWidth: 340
                },
                items: [{
                    xtype: 'combo',
                    name: 'db.msr.redis.persistence_type',
                    fieldLabel: 'Persistence type',
                    editable: false,
                    store: {
                        fields: [ 'name', 'description' ],
                        proxy: 'object',
                        data: [
                            {name:'aof', description:'Append Only File'},
                            {name:'snapshotting', description:'Snapshotting'}
                        ]
                    },
                    value: 'snapshotting',
                    valueField: 'name',
                    displayField: 'description',
                    flex: 1,
                    labelWidth: 130,
                    queryMode: 'local',
                    margin: '0 36 0 0'
                }, {
                    xtype: 'buttongroupfield',
                    name: 'db.msr.redis.use_password',
                    fieldLabel: 'Password auth',
                    labelWidth: 95,
                    flex: 1,
                    defaults: {
                        width: 50
                    },
                    items: [{
                        text: 'On',
                        value: '1'
                    },{
                        text: 'Off',
                        value: '0'
                    }]
                }]
            }, {
                xtype: 'sliderfield',
                name: 'db.msr.redis.num_processes',
                fieldLabel: 'Number of processes',
                minValue: 1,
                maxValue: 16,
                increment: 1,
                labelWidth: 130,
                anchor: '50%',
                maxWidth: 340,
                margin: '12 16 30 0',
                useTips: false,
                showValue: true
            }]
        },{
            xtype: 'label',
            cls: 'x-fieldset-subheader',
            text: 'Data storage settings'
        },{
            xtype: 'container',
            layout: 'hbox',
            maxWidth: 760,
            padding: '8 24',
            items: [{
                xtype: 'combo',
                name: 'db.msr.data_storage.engine',
                fieldLabel: 'Storage engine',
                allowChangeable: false,
                editable: false,
                store: {
                    fields: [ 'description', 'name' ],
                    proxy: 'object'
                },
                valueField: 'name',
                displayField: 'description',
                flex: 1,
                labelWidth: 100,
                margin: '0 36 0 0',
                queryMode: 'local',
                listeners:{
                    change: function(comp, value){
                        var tab = this.up('#dbmsr'),
                            moduleTabParams = tab.up('roleslibrary').moduleParams['tabParams'],
                            isRaid = Ext.Array.contains(['raid.ebs', 'raid.gce_persistent'], value);
                        tab.suspendLayouts();
                        tab.down('#eph_settings').setVisible(value === 'eph');
                        tab.down('#lvm_settings').setVisible(value === 'lvm');
                        
                        tab.down('#ebs_settings').setVisible(value === 'ebs' || value === 'csvol');
                        tab.down('#ebs_settings_type').setVisible(value === 'ebs');
                        
                        tab.down('#cinder_settings').setVisible(value === 'cinder');
                        tab.down('#gced_settings').setVisible(value === 'gce_persistent');
                        tab.down('#raid_settings_not_available').setVisible(isRaid && !moduleTabParams['featureRAID']);
                        tab.down('#raid_settings').setVisible(isRaid && moduleTabParams['featureRAID']);
                        if (isRaid) {
                            var raidEbsTypeField = tab.down('[name="db.msr.data_storage.raid.ebs.type"]');
                            raidEbsTypeField.setVisible(value === 'raid.ebs');
                            tab.down('[name="db.msr.data_storage.raid.ebs.iops"]').setVisible(value === 'raid.ebs' && raidEbsTypeField.getValue() === 'io');
                        }
                        tab.resumeLayouts(true);
                        if (value && tab.suspendUpdateEvent === 0) {
                            tab.up('form').updateRecordSettings(comp.name, value);
                        }
                    }
                }
            },{ 
                xtype: 'buttongroupfield',
                name: 'db.msr.data_storage.fstype',
                fieldLabel: 'Filesystem',
                labelWidth: 80,
                minWidth: 240,
                flex: 1,
                defaults: {
                    width: 50,
                    tooltipType: 'title'
                }
            }]
        },{
            xtype: 'container',
            maxWidth: 760,
            layout: 'anchor',
            defaults: {
               anchor: '100%'
            },
            items: [{
                xtype: 'container',
                itemId: 'eph_settings',
                cls: 'inner-container',
                layout: 'anchor',
                hidden: true,
                items: [{ 
                    xtype: 'combo',
                    name: 'db.msr.data_storage.eph.disk',
                    allowChangeable: false,
                    fieldLabel: 'Disk device',
                    editable: false,
                    store: {
                        fields: [ 'device', 'description' ],
                        proxy: 'object'
                    },
                    valueField: 'device',
                    displayField: 'description',
                    anchor: '100%',
                    labelWidth: 100,
                    queryMode: 'local'
                }]
            },{
                xtype: 'container',
                itemId: 'lvm_settings',
                cls: 'inner-container',
                hidden: true,
                items: [{
                    xtype: 'displayfield',
                    hideLabel: true,
                    value: 'LVM device',
                    width: 400
                }]
            },{
                xtype: 'container',
                itemId: 'ebs_settings',
                cls: 'inner-container',
                hidden: true,
                layout: {
                    type: 'hbox'
                },
                items: [{
                    xtype: 'container',
                    itemId: 'ebs_settings_type',
                    layout: 'hbox',
                    flex: 1,
                    margin: '0 32 12 0',
                    items: [{
                        xtype: 'combo',
                        store: [['standard', 'Standard'],['io1', 'Provisioned IOPS (' + iopsMin + ' - ' + iopsMax + '): ']],
                        allowChangeable: false,
                        fieldLabel: 'EBS type',
                        labelWidth: 80,
                        valueField: 'id',
                        displayField: 'name',
                        editable: false,
                        queryMode: 'local',
                        value: 'standard',
                        name: 'db.msr.data_storage.ebs.type',
                        flex: 1,
                        maxWidth: 320,
                        listeners: {
                            change: function (comp, value) {
                                var iopsField = comp.next();
                                iopsField.setVisible(value === 'io1');
                                if (value === 'io1') {
                                    iopsField.reset();
                                    iopsField.setValue(100);
                                } else {
                                    comp.up('container').next().isValid();
                                }
                            }
                        }
                    },{
                        xtype: 'textfield',
                        itemId: 'db.msr.data_storage.ebs.iops',
                        name: 'db.msr.data_storage.ebs.iops',
                        allowChangeable: false,
                        maskRe: integerRe,
                        validator: function(value){
                            if (value*1 > iopsMax) {
                                return 'Maximum value is ' + iopsMax + '.';
                            } else if (value*1 < iopsMin) {
                                return 'Minimum value is ' + iopsMin + '.';
                            }
                            return true;
                        },
                        hideLabel: true,
                        hidden: true,
                        margin: '0 0 0 2',
                        width: 50,
                        listeners: {
                            change: function(comp, value){
                                var sizeField = comp.up('container').next();
                                if (comp.isValid() && comp.prev().getValue() === 'io1') {
                                    var minSize = Math.ceil(value*1/10);
                                    if (sizeField.getValue()*1 < minSize) {
                                        sizeField.setValue(minSize);
                                    }
                                }
                            }
                        }
                    }]
                },{
                    xtype: 'textfield',
                    name: 'db.msr.data_storage.ebs.size',
                    allowChangeable: false,
                    fieldLabel: 'Storage size',
                    width: 140,
                    maskRe: integerRe,
                    validator: function(value){
                        var minValue = 1,
                            container = this.up('container');
                        if (container.down('[name="db.msr.data_storage.ebs.type"]').getValue() === 'io1') {
                            minValue = Math.ceil(container.down('[name="db.msr.data_storage.ebs.iops"]').getValue()*1/10);
                        }
                        if (value*1 > maxEbsStorageSize) {
                            return 'Maximum value is ' + maxEbsStorageSize + '.';
                        } else if (value*1 < minValue) {
                            return 'Minimum value is ' + minValue + '.';
                        }
                        return true;
                    },
                    labelWidth: 80,
                    allowBlank: false,
                    margin: '0 6 12 0'
                },{
                    xtype: 'label',
                    text: 'GB',
                    margin: '4 0 0 0'
                }]
            },{
                xtype: 'container',
                itemId: 'cinder_settings',
                cls: 'inner-container',
                hidden: true,
                layout: 'anchor',
                items: [{
                    xtype: 'textfield',
                    name: 'db.msr.data_storage.cinder.size',
                    allowChangeable: false,
                    fieldLabel: 'Disk size',
                    maskRe: integerRe,
                    allowBlank: false,
                    labelWidth: 100,
                    anchor: '100%',
                    maxWidth: 200,
                    value: 100
                }]
            },{
                xtype: 'container',
                itemId: 'gced_settings',
                cls: 'inner-container',
                hidden: true,
                layout: 'anchor',
                items: [{
                    xtype: 'textfield',
                    name: 'db.msr.data_storage.gced.size',
                    allowChangeable: false,
                    fieldLabel: 'Disk size',
                    maskRe: integerRe,
                    allowBlank: false,
                    labelWidth: 100,
                    anchor: '100%',
                    maxWidth: 200,
                    value: 100
                }]
            },{
                xtype: 'displayfield',
                itemId: 'raid_settings_not_available',
                fieldCls: 'x-form-field-warning',
                margin: '18 0 0 0',
                hidden: true,
                value: 'RAID arrays are not available for your pricing plan. <a href="#/billing">Please upgrade your account to be able to use this feature.</a>'
            },{
                xtype: 'container',
                itemId: 'raid_settings',
                cls: 'inner-container',
                hidden: true,
                layout: 'anchor',
                items: [{
                    xtype: 'container',
                    layout: 'hbox',
                    items: [{
                        xtype: 'combo',
                        name: 'db.msr.data_storage.raid.level',
                        allowChangeable: false,
                        fieldLabel: 'RAID level',
                        editable: false,
                        store: {
                            fields: [ 'name', 'description' ],
                            proxy: 'object'
                        },
                        valueField: 'name',
                        displayField: 'description',
                        flex: 1,
                        margin: '0 32 0 0',
                        value: '',
                        labelWidth: 80,
                        queryMode: 'local',
                        listeners:{
                            change:function(comp, value) {
                                var data = [];
                                if (value == '0') {
                                    data = {'2':'2', '3':'3', '4':'4', '5':'5', '6':'6', '7':'7', '8':'8'};
                                } else if (value == '1') {
                                    data = {'2':'2'};
                                } else if (value == '5') {
                                    data = {'3':'3', '4':'4', '5':'5', '6':'6', '7':'7', '8':'8'};
                                } else if (value == '10') {
                                    data = {'4':'4', '6':'6', '8':'8'};
                                }

                                var field = comp.next(), 
                                    val;
                                field.store.load({data: data});
                                if (field.store.getCount()) {
                                    val = field.store.getAt(0).get('id')
                                }
                                field.setValue(val);
                            }
                        }
                    },{
                        xtype: 'combo',
                        name: 'db.msr.data_storage.raid.volumes_count',
                        allowChangeable: false,
                        fieldLabel: 'Number of volumes',
                        editable: false,
                        store: {
                            fields: [ 'id', 'name'],
                            proxy: 'object'
                        },
                        valueField: 'id',
                        displayField: 'name',
                        flex: 1,
                        labelWidth: 120,
                        maxWidth: 200,
                        queryMode: 'local'
                    }]
                },{
                    xtype: 'container',
                    layout: 'hbox',
                    margin: '12 0',
                    items: [{
                        xtype: 'container',
                        flex: 1,
                        layout: 'hbox',
                        items: [{
                            xtype: 'combo',
                            store: [['standard', 'Standard'],['io1', 'Provisioned IOPS (' + iopsMin + ' - ' + iopsMax + '): ']],
                            allowChangeable: false,
                            fieldLabel: 'EBS type',
                            labelWidth: 80,
                            valueField: 'id',
                            displayField: 'name',
                            editable: false,
                            queryMode: 'local',
                            name: 'db.msr.data_storage.raid.ebs.type',
                            value: 'standard',
                            flex: 1,
                            listeners: {
                                change: function (comp, value) {
                                    var iopsField = comp.next();
                                    iopsField.setVisible(value === 'io1');
                                    if (value === 'io1') {
                                        iopsField.reset();
                                        iopsField.setValue(100);
                                    } else {
                                        comp.up('container').next().isValid();
                                    }
                                }
                            }
                        }, {
                            xtype: 'textfield',
                            itemId: 'db.msr.data_storage.raid.ebs.iops',
                            allowChangeable: false,
                            name: 'db.msr.data_storage.raid.ebs.iops',
                            maskRe: integerRe,
                            validator: function(value){
                                if (value*1 > iopsMax) {
                                    return 'Maximum value is ' + iopsMax + '.';
                                } else if (value*1 < iopsMin) {
                                    return 'Minimum value is ' + iopsMin + '.';
                                }
                                return true;
                            },
                            allowBlank: false,
                            hidden: true,
                            margin: '0 0 0 2',
                            width: 50,
                            listeners: {
                                change: function(comp, value){
                                    var sizeField = comp.up('container').next();
                                    if (comp.isValid() && comp.prev().getValue() === 'io1') {
                                        var minSize = Math.ceil(value*1/10);
                                        if (sizeField.getValue()*1 < minSize) {
                                            sizeField.setValue(minSize);
                                        }
                                    }
                                }
                            }
                            
                        }]
                    },{
                        xtype: 'textfield',
                        name: 'db.msr.data_storage.raid.volume_size',
                        allowChangeable: false,
                        fieldLabel: 'Each volume size',
                        allowBlank: false,
                        labelWidth: 120,
                        flex: 1,
                        maxWidth: 176,
                        margin: '0 6 0 32',
                        maskRe: integerRe,
                        value: 10,
                        validator: function(value){
                            var minValue = 1,
                                container = this.up('container'),
                                ebsTypeField = container.down('[name="db.msr.data_storage.raid.ebs.type"]');
                            if (ebsTypeField.isVisible()) {
                                if (ebsTypeField.getValue() === 'io1') {
                                    minValue = Math.ceil(container.down('[name="db.msr.data_storage.raid.ebs.iops"]').getValue()*1/10);
                                }
                                if (value*1 > maxEbsStorageSize) {
                                    return 'Maximum value is ' + maxEbsStorageSize + '.';
                                } else if (value*1 < minValue) {
                                    return 'Minimum value is ' + minValue + '.';
                                }
                            }
                            return true;
                        }
                    },{
                        xtype: 'label',
                        text: 'GB',
                        margin: '4 0 0 0'
                    }]
                }]
             }]
        }]
    }
});
