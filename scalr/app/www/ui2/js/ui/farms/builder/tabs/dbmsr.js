Scalr.regPage('Scalr.ui.farms.builder.tabs.dbmsr', function (moduleTabParams) {
    var iopsMin = 100, 
        iopsMax = 4000, 
        integerRe = new RegExp('[0123456789]', 'i'), 
        maxEbsStorageSize = 1000;

    return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Database settings',
		itemId: 'dbmsr',

		isEnabled: function (record) {
			return record.isDbMsr();
		},
        
		getDefaultValues: function (record) {
			return {
				'db.msr.data_bundle.enabled': 1,
				'db.msr.data_bundle.every': 24,
				'db.msr.data_bundle.timeframe.start_hh': '05',
				'db.msr.data_bundle.timeframe.start_mm': '00',
				'db.msr.data_bundle.timeframe.end_hh': '09',
				'db.msr.data_bundle.timeframe.end_mm': '00',
				
				'db.msr.data_storage.engine': record.getDefaultStorageEngine(),
				'db.msr.data_storage.ebs.size': 10,
				'db.msr.data_storage.ebs.snaps.enable_rotation' : 1,
				'db.msr.data_storage.ebs.snaps.rotate' : 5,
				
				'db.msr.data_storage.cinder.size': 100,
				'db.msr.data_storage.gced.size': 1,
				
				'db.msr.data_backup.enabled': 1,
				'db.msr.data_backup.every' : 48,
				'db.msr.data_backup.timeframe.start_hh': '05',
				'db.msr.data_backup.timeframe.start_mm': '00',
				'db.msr.data_backup.timeframe.end_hh': '09',
				'db.msr.data_backup.timeframe.end_mm': '00'
			};
		},
		
       onRoleUpdate: function(record, name, value, oldValue) {
            if (!this.isActive(record)) return;
            
            var fullname = name.join('.'),
                platform = record.get('platform'),
                settings = record.get('settings');
            if (fullname === 'settings.aws.instance_type' || fullname === 'settings.gce.machine-type') {
                if (platform === 'ec2' || platform === 'gce')
                    var devices = record.getAvailableStorageDisks(),
                        fistDevice = '';

                    Ext.Array.each(devices, function(disk){
                        if (fistDevice === ''){
                            fistDevice = disk.device;
                        }
                        if (settings['db.msr.data_storage.eph.disk'] == disk.device) {
                            fistDevice = disk.device;
                        }
                    });
                    
                    if (this.isVisible()) {
                        var field = this.down('[name="db.msr.data_storage.eph.disk"]');
                        field.store.load({data: devices});
                        field.setValue(fistDevice);
                        
                        this.refreshLvmCheckboxes(record);
                    }
                    if (settings['db.msr.data_storage.engine'] === 'eph') {
                        settings['db.msr.data_storage.eph.disk'] = fistDevice;
                        record.set('settings', settings);
                    }
            }
            
        },
        
        refreshLvmCheckboxes: function(record) {
            var ephemeralDevicesMap = record.getEphemeralDevicesMap();
            if (ephemeralDevicesMap === undefined) return;
            
			var platform = record.get('platform'),
                settings = record.get('settings', true),
                iType = false;
			
			if (platform === 'gce') {
				iType = settings['gce.machine-type'];
			} else if (platform === 'ec2') {
				iType = settings['aws.instance_type'];
			} 
			
            var cont = this.down('[name="lvm_settings"]');
            cont.suspendLayouts();
            cont.removeAll();
			if (Ext.isDefined(ephemeralDevicesMap[iType])) {
				var devices = ephemeralDevicesMap[iType], size = 0,
					volumes = Ext.decode(settings['db.msr.storage.lvm.volumes']), def = Ext.Object.getSize(volumes) ? false : true;

				for (var d in devices) {
					cont.add({
						xtype: 'checkbox',
						name: d,
						boxLabel: d + ' (' + devices[d]['size'] + 'Gb)',
						ephSize: devices[d]['size'],
						checked: def || Ext.isDefined(volumes[d]),
						handler: function() {
							/*var c = this.up('fieldset'), s = 0;
							Ext.each(c.query('checkbox'), function() {
								if (this.getValue())
									s += parseInt(this.ephSize);
							});

							c.down('displayfield').setValue(s + 'Gb');*/
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
			}
            cont.resumeLayouts(true);
            cont.setDisabled(!record.get('new'))
        },
        
		showTab: function (record) {
			var settings = record.get('settings'),
                platform = record.get('platform'),
                notANewRecord = !record.get('new'),
                field, value;
            
			this.isLoading = true;
            
    		this.down('[name="db.msr.data_bundle.use_slave"]').setVisible(record.isMySql());
			
            if (Ext.Array.contains(['cloudstack', 'idcf', 'ucloud'], platform)) {
				this.down('[name="db.msr.data_backup.enabled"]').hide().collapse();
			} else {
                this.down('[name="db.msr.data_backup.enabled"]').show();
            }
            
			this.down('[name="db.msr.data_storage.engine"]').store.load({data: record.getAvailableStorages()});
			
			// File systems
            field = this.down('[name="db.msr.data_storage.fstype"]');
            
            field.suspendLayouts();
            field.removeAll();
            field.add(record.getAvailableStorageFs(moduleTabParams['featureMFS']));
			field.setValue(settings['db.msr.data_storage.fstype'] || 'ext3');
            field.setReadOnly(notANewRecord);
            field.resumeLayouts(true);
            
			// Ephemeral devices
            this.down('[name="lvm_settings"]').setVisible(settings['db.msr.data_storage.engine'] === 'lvm');
            this.down('[name="db.msr.data_bundle.compression"]').setVisible(settings['db.msr.data_storage.engine'] === 'lvm');
			this.refreshLvmCheckboxes(record);
			
			//redis
			if (record.get('behaviors').match('redis')) {
				this.down('[name="db.msr.redis.persistence_type"]').setValue(settings['db.msr.redis.persistence_type'] || 'snapshotting');
				this.down('[name="db.msr.redis.use_password"]').setValue(settings['db.msr.redis.use_password'] || 1);
				this.down('[name="db.msr.redis.num_processes"]').setValue(settings['db.msr.redis.num_processes'] || 1);
				
				this.down('[name="redis_settings"]').show();
			} else {
				this.down('[name="redis_settings"]').hide();
			}
			
            //eph
            field = this.down('[name="db.msr.data_storage.eph.disk"]');
			field.store.load({data: record.getAvailableStorageDisks()});
			field.setValue(settings['db.msr.data_storage.eph.disk'] || (field.store.getAt(field.store.getCount() > 1 ? 1 : 0).get('device')));
			
			//raid
            field = this.down('[name="db.msr.data_storage.raid.level"]');
			field.store.load({data: record.getAvailableStorageRaids()});
			field.setValue(settings['db.msr.data_storage.raid.level'] || '10');
			
			this.down('[name="db.msr.data_storage.raid.ebs.type"]').setValue(settings['db.msr.data_storage.raid.ebs.type'] || 'standard');
			this.down('[name="db.msr.data_storage.raid.ebs.iops"]').setValue(settings['db.msr.data_storage.raid.ebs.iops'] || 50);
			this.down('[name="db.msr.data_storage.raid.volumes_count"]').setValue(settings['db.msr.data_storage.raid.volumes_count'] || 4);
			this.down('[name="db.msr.data_storage.raid.volume_size"]').setValue(settings['db.msr.data_storage.raid.volume_size'] || 10);

			this.down('[name="db.msr.data_storage.cinder.size"]').setValue(settings['db.msr.data_storage.cinder.size'] || 1);
			this.down('[name="db.msr.data_storage.gced.size"]').setValue(settings['db.msr.data_storage.gced.size'] || 1);
            
            //data bundle
            this.down('[name="db.msr.data_bundle.enabled"]')[settings['db.msr.data_bundle.enabled'] == 1 ? 'expand' : 'collapse']();
			this.down('[name="db.msr.data_bundle.every"]').setValue(settings['db.msr.data_bundle.every']);
			this.down('[name="db.msr.data_bundle.use_slave"]').setValue(settings['db.msr.data_bundle.use_slave'] || 0);
			this.down('[name="db.msr.no_data_bundle_on_promote"]').setValue(settings['db.msr.no_data_bundle_on_promote'] || 0);
			this.down('[name="db.msr.data_bundle.compression"]').setValue(settings['db.msr.data_bundle.compression'] || '');
			this.down('[name="db.msr.data_bundle.timeframe.start_hh"]').setValue(settings['db.msr.data_bundle.timeframe.start_hh']);
			this.down('[name="db.msr.data_bundle.timeframe.start_mm"]').setValue(settings['db.msr.data_bundle.timeframe.start_mm']);
			this.down('[name="db.msr.data_bundle.timeframe.end_hh"]').setValue(settings['db.msr.data_bundle.timeframe.end_hh']);
			this.down('[name="db.msr.data_bundle.timeframe.end_mm"]').setValue(settings['db.msr.data_bundle.timeframe.end_mm']);

            //data backup
            this.down('[name="db.msr.data_backup.enabled"]')[settings['db.msr.data_backup.enabled'] == 1 ? 'expand' : 'collapse']();

			if (!Ext.Array.contains(['cloudstack', 'idcf', 'ucloud'], platform)) {
				this.down('[name="db.msr.data_backup.every"]').setValue(settings['db.msr.data_backup.every']);
				this.down('[name="db.msr.data_backup.timeframe.start_hh"]').setValue(settings['db.msr.data_backup.timeframe.start_hh']);
				this.down('[name="db.msr.data_backup.timeframe.start_mm"]').setValue(settings['db.msr.data_backup.timeframe.start_mm']);
				this.down('[name="db.msr.data_backup.timeframe.end_hh"]').setValue(settings['db.msr.data_backup.timeframe.end_hh']);
				this.down('[name="db.msr.data_backup.timeframe.end_mm"]').setValue(settings['db.msr.data_backup.timeframe.end_mm']);
			}

			if (Ext.Array.contains(['ebs', 'csvol'], settings['db.msr.data_storage.engine'])) {
                this.down('[name="db.msr.data_storage.ebs.snaps.enable_rotation"]').setValue(settings['db.msr.data_storage.ebs.snaps.enable_rotation'] == 1);
                
                field = this.down('[name="db.msr.data_storage.ebs.snaps.rotate"]');
                field.setDisabled(settings['db.msr.data_storage.ebs.snaps.enable_rotation'] != 1);
				field.setValue(settings['db.msr.data_storage.ebs.snaps.rotate']);
                
				this.down('[name="db.msr.data_storage.ebs.size"]').setValue(settings['db.msr.data_storage.ebs.size']);
				this.down('[name="db.msr.data_storage.ebs.type"]').setValue(settings['db.msr.data_storage.ebs.type'] || 'standard');
				this.down('[name="db.msr.data_storage.ebs.iops"]').setValue(settings['db.msr.data_storage.ebs.iops'] || 50);
				
				if (settings['db.msr.data_storage.engine'] == 'csvol') {
					this.down('[name="db.msr.data_storage.ebs.type"]').hide();
					this.down('[name="db.msr.data_storage.ebs.iops"]').hide();
				} else {
					this.down('[name="db.msr.data_storage.ebs.type"]').show();
					this.down('[name="db.msr.data_storage.ebs.iops"]').setVisible(this.down('[name="db.msr.data_storage.ebs.type"]').getValue() == 'io1');
				}
					
			}

			this.down('[name="db.msr.data_storage.engine"]').setValue(settings['db.msr.data_storage.engine']);
			
            //RAID Settings
            this.down('[name="db.msr.data_storage.raid.level"]').setDisabled(notANewRecord)
            this.down('[name="db.msr.data_storage.raid.volumes_count"]').setDisabled(notANewRecord);
            this.down('[name="db.msr.data_storage.raid.volume_size"]').setDisabled(notANewRecord);
            this.down('[name="db.msr.data_storage.raid.ebs.type"]').setDisabled(notANewRecord);
            this.down('[name="db.msr.data_storage.raid.ebs.iops"]').setDisabled(notANewRecord);

            // Engine & EBS Settings
            this.down('[name="db.msr.data_storage.engine"]').setDisabled(notANewRecord);
            this.down('[name="db.msr.data_storage.ebs.size"]').setDisabled(notANewRecord);
            this.down('[name="db.msr.data_storage.ebs.type"]').setDisabled(notANewRecord);
            this.down('[name="db.msr.data_storage.ebs.iops"]').setDisabled(notANewRecord);
            this.down('[name="db.msr.data_storage.fstype"]').setDisabled(notANewRecord);				

            // Cinder settings
            this.down('[name="db.msr.data_storage.cinder.size"]').setDisabled(notANewRecord);

            //GCE Disk settings
            this.down('[name="db.msr.data_storage.gced.size"]').setDisabled(notANewRecord);
            
            this.isLoading = false;
		},

		hideTab: function (record) {
			var settings = record.get('settings');

			if (record.get('behaviors').match('redis')) {
				settings['db.msr.redis.persistence_type'] = this.down('[name="db.msr.redis.persistence_type"]').getValue();
				settings['db.msr.redis.use_password'] = this.down('[name="db.msr.redis.use_password"]').getValue();
				settings['db.msr.redis.num_processes'] = this.down('[name="db.msr.redis.num_processes"]').getValue();
			}

			if (! this.down('[name="db.msr.data_bundle.enabled"]').collapsed) {
				settings['db.msr.data_bundle.enabled'] = 1;
				settings['db.msr.data_bundle.every'] = this.down('[name="db.msr.data_bundle.every"]').getValue();
				settings['db.msr.data_bundle.timeframe.start_hh'] = this.down('[name="db.msr.data_bundle.timeframe.start_hh"]').getValue();
				settings['db.msr.data_bundle.timeframe.start_mm'] = this.down('[name="db.msr.data_bundle.timeframe.start_mm"]').getValue();
				settings['db.msr.data_bundle.timeframe.end_hh'] = this.down('[name="db.msr.data_bundle.timeframe.end_hh"]').getValue();
				settings['db.msr.data_bundle.timeframe.end_mm'] = this.down('[name="db.msr.data_bundle.timeframe.end_mm"]').getValue();
				
				settings['db.msr.data_bundle.use_slave'] = this.down('[name="db.msr.data_bundle.use_slave"]').getValue();
				settings['db.msr.no_data_bundle_on_promote'] = this.down('[name="db.msr.no_data_bundle_on_promote"]').getValue();
				settings['db.msr.data_bundle.compression'] = this.down('[name="db.msr.data_bundle.compression"]').getValue();
			} else {
				settings['db.msr.data_bundle.enabled'] = 0;
				delete settings['db.msr.data_bundle.every'];
				delete settings['db.msr.data_bundle.timeframe.start_hh'];
				delete settings['db.msr.data_bundle.timeframe.start_mm'];
				delete settings['db.msr.data_bundle.timeframe.end_hh'];
				delete settings['db.msr.data_bundle.timeframe.end_mm'];
			}

			if (! this.down('[name="db.msr.data_backup.enabled"]').collapsed) {
				settings['db.msr.data_backup.enabled'] = 1;
				settings['db.msr.data_backup.every'] = this.down('[name="db.msr.data_backup.every"]').getValue();
				settings['db.msr.data_backup.timeframe.start_hh'] = this.down('[name="db.msr.data_backup.timeframe.start_hh"]').getValue();
				settings['db.msr.data_backup.timeframe.start_mm'] = this.down('[name="db.msr.data_backup.timeframe.start_mm"]').getValue();
				settings['db.msr.data_backup.timeframe.end_hh'] = this.down('[name="db.msr.data_backup.timeframe.end_hh"]').getValue();
				settings['db.msr.data_backup.timeframe.end_mm'] = this.down('[name="db.msr.data_backup.timeframe.end_mm"]').getValue();
			} else {
				settings['db.msr.data_backup.enabled'] = 0;
				delete settings['db.msr.data_backup.every'];
				delete settings['db.msr.data_backup.timeframe.start_hh'];
				delete settings['db.msr.data_backup.timeframe.start_mm'];
				delete settings['db.msr.data_backup.timeframe.end_hh'];
				delete settings['db.msr.data_backup.timeframe.end_mm'];
			}

			if (record.get('new')) {
				settings['db.msr.data_storage.engine'] = this.down('[name="db.msr.data_storage.engine"]').getValue();
				settings['db.msr.data_storage.fstype'] = this.down('[name="db.msr.data_storage.fstype"]').getValue();
			}
			
			if (settings['db.msr.data_storage.engine'] === 'ebs' || settings['db.msr.data_storage.engine'] === 'csvol') {
				if (record.get('new')) {
					settings['db.msr.data_storage.ebs.size'] = this.down('[name="db.msr.data_storage.ebs.size"]').getValue();
					settings['db.msr.data_storage.ebs.type'] = this.down('[name="db.msr.data_storage.ebs.type"]').getValue();
					settings['db.msr.data_storage.ebs.iops'] = this.down('[name="db.msr.data_storage.ebs.iops"]').getValue();
				}

				if (this.down('[name="db.msr.data_storage.ebs.snaps.enable_rotation"]').getValue()) {
					settings['db.msr.data_storage.ebs.snaps.enable_rotation'] = 1;
					settings['db.msr.data_storage.ebs.snaps.rotate'] = this.down('[name="db.msr.data_storage.ebs.snaps.rotate"]').getValue();
				} else {
					settings['db.msr.data_storage.ebs.snaps.enable_rotation'] = 0;
					delete settings['db.msr.data_storage.ebs.snaps.rotate'];
				}
			} else {
				delete settings['db.msr.data_storage.ebs.size'];
				delete settings['db.msr.data_storage.ebs.snaps.enable_rotation'];
				delete settings['db.msr.data_storage.ebs.snaps.rotate'];
			}

			if (settings['db.msr.data_storage.engine'] === 'eph') {
				settings['db.msr.data_storage.eph.disk'] = this.down('[name="db.msr.data_storage.eph.disk"]').getValue();
			}

			if (settings['db.msr.data_storage.engine'] === 'lvm') {
				//Remove this settings because if instance type was changed we need to update this setting.
				// Update it manually still not allowed, need consider to allow to change this setting.
				//if (record.get('new')) {
					var volumes = {};
					Ext.each(this.down('[name="lvm_settings"]').query('checkbox'), function() {
						if (this.getValue()) {
							volumes[this.getName()] = this.ephSize;
						}
					});
					settings['db.msr.storage.lvm.volumes'] = Ext.encode(volumes);
				//}
			}

			if (Ext.Array.contains(['raid.ebs', 'raid.gce_persistent'], settings['db.msr.data_storage.engine'])) {
				settings['db.msr.data_storage.raid.level'] = this.down('[name="db.msr.data_storage.raid.level"]').getValue();
				settings['db.msr.data_storage.raid.volume_size'] = this.down('[name="db.msr.data_storage.raid.volume_size"]').getValue();
				settings['db.msr.data_storage.raid.volumes_count'] = this.down('[name="db.msr.data_storage.raid.volumes_count"]').getValue();
				if (settings['db.msr.data_storage.engine'] === 'raid.ebs') {
                    settings['db.msr.data_storage.raid.ebs.type'] = this.down('[name="db.msr.data_storage.raid.ebs.type"]').getValue();
                    settings['db.msr.data_storage.raid.ebs.iops'] = this.down('[name="db.msr.data_storage.raid.ebs.iops"]').getValue();
                }
			}

			if (settings['db.msr.data_storage.engine'] === 'cinder') {
				settings['db.msr.data_storage.cinder.size'] = this.down('[name="db.msr.data_storage.cinder.size"]').getValue();
			}

			if (settings['db.msr.data_storage.engine'] === 'gce_persistent') {
				settings['db.msr.data_storage.gced.size'] = this.down('[name="db.msr.data_storage.gced.size"]').getValue();
			}
            
			record.set('settings', settings);
		},

		items: [{
				xtype: 'fieldset',
				name: 'redis_settings',
				hidden: true,
				title: 'Redis settings',
				items: [{ 
                    xtype: 'container',
                    layout: 'hbox',
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
                        valueField: 'name',
                        displayField: 'description',
                        width: 400,
                        margin: '0 32 0 0',
                        labelWidth: 160,
                        queryMode: 'local'
                    }, {
                        xtype: 'buttongroupfield',
                        name: 'db.msr.redis.use_password',
                        fieldLabel: 'Password auth',
                        labelWidth: 95,
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
                    labelWidth: 160,
                    width: 400,
                    margin: '0 0 24 0',
                    useTips: false,
                    showValue: true
				}]
			}, {
				xtype: 'fieldset',
				title: 'Storage settings',
                layout: 'hbox',
				items: [{ 
					xtype: 'combo',
					name: 'db.msr.data_storage.engine',
					fieldLabel: 'Storage engine',
					editable: false,
					store: {
						fields: [ 'description', 'name' ],
						proxy: 'object'
					},
					valueField: 'name',
					displayField: 'description',
					width: 400,
					labelWidth: 160,
                    margin: '0 32 12 0',
					queryMode: 'local',
					listeners:{
						change: function(comp, value){
							var tab = this.up('#dbmsr');
                            tab.suspendLayouts();
                            tab.down('[name="ebs_settings"]').setVisible(value === 'ebs' || value === 'csvol');
                            tab.down('[name="eph_settings"]').setVisible(value === 'eph');
                            tab.down('[name="lvm_settings"]').setVisible(value === 'lvm');
                            tab.down('[name="db.msr.data_bundle.compression"]').setVisible(value === 'lvm');
                            tab.down('[name="cinder_settings"]').setVisible(value === 'cinder');
                            tab.down('[name="gced_settings"]').setVisible(value === 'gce_persistent');
                            if (Ext.Array.contains(['raid.ebs', 'raid.gce_persistent'], value)) {
								if (moduleTabParams['featureRAID']) {
									tab.down('[name="raid_settings"]').show();
								} else {
									tab.down('[name="raid_settings_not_available"]').show();
								}
                                tab.down('#raid_ebs_type').setVisible(value === 'raid.ebs');
							} else {
                                tab.down('[name="raid_settings"]').hide();
                                tab.down('[name="raid_settings_not_available"]').hide();
                            }
                            if (!tab.isLoading) {
                                var record = tab.currentRole,
                                    settings = record.get('settings');
                                settings[comp.name] = value;
                                record.set('settings', settings);
                            }
                            tab.resumeLayouts(true);
						}
					}
				}, { 
					xtype: 'buttongroupfield',
					name: 'db.msr.data_storage.fstype',
					fieldLabel: 'Filesystem',
                    labelWidth: 95,
                    defaults: {
                        width: 50,
                        tooltipType: 'title'
                    }
				}]
			}, {
				xtype:'fieldset',
				name: 'eph_settings',
				title: 'Ephemeral Storage settings',
				hidden: true,
				items: [{ 
					xtype: 'combo',
					name: 'db.msr.data_storage.eph.disk',
					fieldLabel: 'Disk device',
					editable: false,
					store: {
						fields: [ 'device', 'description' ],
						proxy: 'object'
					},
					valueField: 'device',
					displayField: 'description',
					width: 500,
					labelWidth: 160,
					queryMode: 'local',
					listeners:{
						change:function(){
							//TODO:
						}
					}
				}]
			}, {
				xtype:'fieldset',
				name: 'lvm_settings',
				title: 'LVM Storage settings',
				hidden: true,
				items: [{
					xtype: 'displayfield',
					hideLabel:true,
					value: 'LVM device',
					width: 400
				}]
			}, {
				xtype:'fieldset',
				name: 'ebs_settings',
				title: 'Block Storage settings',
				hidden: true,
				items: [{
					xtype: 'container',
					layout: 'hbox',
					width: 600,
					items: [{
						xtype: 'combo',
						store: [['standard', 'Standard'],['io1', 'Provisioned IOPS (' + iopsMin + ' - ' + iopsMax + '): ']],
                        fieldLabel: 'EBS type',
                        labelWidth:160,
						valueField: 'id',
						displayField: 'name',
						editable: false,
						queryMode: 'local',
						value: 'standard',
						name: 'db.msr.data_storage.ebs.type',
						width: 390,
						listeners: {
							change: function (comp, value) {
                                var tab = comp.up('#dbmsr'),
                                    iopsField = tab.down('[name="db.msr.data_storage.ebs.iops"]');
                                iopsField.setVisible(value === 'io1');
                                if (tab.currentRole.get('new')) {
                                    if (value === 'io1') {
                                        iopsField.reset();
                                        iopsField.setValue(100);
                                    } else {
                                        tab.down('[name="db.msr.data_storage.ebs.size"]').isValid();
                                    }
                                }
							}
						}
					}, {
						xtype: 'textfield',
						itemId: 'db.msr.data_storage.ebs.iops',
						name: 'db.msr.data_storage.ebs.iops',
                        maskRe: integerRe,
                        validator: function(value){
                            if (value*1 > iopsMax) {
                                return 'Maximum value is ' + iopsMax + '.';
                            } else if (value*1 < iopsMin) {
                                return 'Minimum value is ' + iopsMin + '.';
                            }
                            return true;
                        },
						hidden: true,
						margin: '0 0 0 2',
						width: 50,
                        listeners: {
                            change: function(comp, value){
                                var tab = comp.up('#dbmsr'),
                                    sizeField = tab.down('[name="db.msr.data_storage.ebs.size"]');
                                if (tab.currentRole.get('new')) {
                                    if (comp.isValid() && comp.prev().getValue() === 'io1') {
                                        var minSize = Math.ceil(value*1/10);
                                        if (sizeField.getValue()*1 < minSize) {
                                            sizeField.setValue(minSize);
                                        }
                                    }
                                }
                            }
                        }
                        
					}]
				}, {
                    xtype: 'container',
                    layout: {
                        type: 'hbox',
                        align: 'middle'
                    },
                    items: [{
                        xtype: 'textfield',
                        name: 'db.msr.data_storage.ebs.size',
                        fieldLabel: 'Storage size',
                        labelWidth: 160,
                        width: 300,
                        value: '10',
                        maskRe: integerRe,
                        validator: function(value){
                            var minValue = 1,
                                container = this.up('#dbmsr');
                            if (container.down('[name="db.msr.data_storage.ebs.type"]').getValue() === 'io1') {
                                minValue = Math.ceil(container.down('[name="db.msr.data_storage.ebs.iops"]').getValue()*1/10);
                            }
                            if (value*1 > maxEbsStorageSize) {
                                return 'Maximum value is ' + maxEbsStorageSize + '.';
                            } else if (value*1 < minValue) {
                                return 'Minimum value is ' + minValue + '.';
                            }
                            return true;
                        }
                    },{
                        xtype: 'label',
                        text: 'GB',
                        margin: '0 0 0 6'
                    }]
				}, {
					xtype: 'fieldcontainer',
					layout: 'hbox',
					name: 'ebs_rotation_settings',
					items: [{
						xtype: 'checkbox',
						hideLabel: true,
						name: 'db.msr.data_storage.ebs.snaps.enable_rotation',
						boxLabel: 'Snapshots are rotated',
						handler: function (checkbox, checked) {
							if (checked)
								this.next('[name="db.msr.data_storage.ebs.snaps.rotate"]').enable();
							else
								this.next('[name="db.msr.data_storage.ebs.snaps.rotate"]').disable();
						}
					}, {
						xtype: 'textfield',
						hideLabel: true,
						name: 'db.msr.data_storage.ebs.snaps.rotate',
						width: 40,
						margin: '0 0 0 3'
					}, {
						xtype: 'displayfield',
						value: 'times before being removed.',
						margin: '0 0 0 3'
					}]
				}]
			}, {
				xtype:'fieldset',
				name: 'cinder_settings',
				title: 'Cinder Storage settings',
				hidden: true,
				items: [{
					xtype: 'textfield',
					fieldLabel: 'Disk size',
					labelWidth: 160,
					width: 300,
					name: 'db.msr.data_storage.cinder.size',
					value: 100
				}/*, {
					xtype: 'fieldcontainer',
					layout: 'hbox',
					name: 'ebs_rotation_settings',
					items: [{
						xtype: 'checkbox',
						hideLabel: true,
						name: 'db.msr.data_storage.ebs.snaps.enable_rotation',
						boxLabel: 'Snapshots are rotated',
						handler: function (checkbox, checked) {
							if (checked)
								this.next('[name="db.msr.data_storage.ebs.snaps.rotate"]').enable();
							else
								this.next('[name="db.msr.data_storage.ebs.snaps.rotate"]').disable();
						}
					}, {
						xtype: 'textfield',
						hideLabel: true,
						name: 'db.msr.data_storage.ebs.snaps.rotate',
						width: 40,
						margin: '0 0 0 3'
					}, {
						xtype: 'displayfield',
						value: 'times before being removed.',
						margin: '0 0 0 3'
					}]
				}*/]
			}, {
				xtype:'fieldset',
				name: 'gced_settings',
				title: 'GCE persistent disk settings',
				hidden: true,
				items: [{
					xtype: 'textfield',
					fieldLabel: 'Disk size',
					labelWidth: 160,
					width: 300,
					name: 'db.msr.data_storage.gced.size',
					value: 100
				}]
			},{
				xtype:'fieldset',
				name: 'raid_settings_not_available',
				title: 'RAID Storage settings',
				hidden: true,
				items: [{
					xtype: 'displayfield',
					fieldCls: 'x-form-field-warning',
					value: 'RAID arrays are not available for your pricing plan. <a href="#/billing">Please upgrade your account to be able to use this feature.</a>'
				}]
			}, {
				xtype:'fieldset',
				name: 'raid_settings',
				title: 'RAID storage settings',
				hidden: true,
				items: [{ 
					xtype: 'combo',
					name: 'db.msr.data_storage.raid.level',
					fieldLabel: 'RAID level',
					editable: false,
					store: {
						fields: [ 'name', 'description' ],
						proxy: 'object'
					},
					valueField: 'name',
					displayField: 'description',
					width: 500,
					value: '',
					labelWidth: 160,
					queryMode: 'local',
					listeners:{
						change:function() {
							try {
								var data = [];
								if (this.getValue() == '0') {
									data = {'2':'2', '3':'3', '4':'4', '5':'5', '6':'6', '7':'7', '8':'8'};
								} else if (this.getValue() == '1') {
									data = {'2':'2'};
								} else if (this.getValue() == '5') {
									data = {'3':'3', '4':'4', '5':'5', '6':'6', '7':'7', '8':'8'};
								} else if (this.getValue() == '10') {
									data = {'4':'4', '6':'6', '8':'8'};
								}
								
								var obj = this.up('#dbmsr').down('[name="db.msr.data_storage.raid.volumes_count"]');
								obj.store.load({data: data});
								var val = obj.store.getAt(0).get('id');
								obj.setValue(val);
							} catch (e) {}
						}
					}
				}, {
					xtype: 'combo',
					name: 'db.msr.data_storage.raid.volumes_count',
					fieldLabel: 'Number of volumes',
					editable: false,
					store: {
						fields: [ 'id', 'name'],
						proxy: 'object'
					},
					valueField: 'id',
					displayField: 'name',
					width: 500,
					labelWidth: 160,
					queryMode: 'local'
				}, {
					xtype: 'container',
					layout: 'hbox',
                    itemId: 'raid_ebs_type',
					width: 600,
					items: [{
						xtype: 'combo',
						store: [['standard', 'Standard'],['io1', 'Provisioned IOPS (' + iopsMin + ' - ' + iopsMax + '): ']],
                        fieldLabel: 'EBS type',
						valueField: 'id',
						displayField: 'name',
						editable: false,
						queryMode: 'local',
						name: 'db.msr.data_storage.raid.ebs.type',
						value: 'standard',
						width: 390,
                        labelWidth:160,
						listeners: {
							change: function (comp, value) {
                                var tab = comp.up('#dbmsr'),
                                    iopsField = tab.down('[name="db.msr.data_storage.raid.ebs.iops"]');
                                iopsField.setVisible(value === 'io1');
                                if (tab.currentRole.get('new')) {
                                    if (value === 'io1') {
                                        iopsField.reset();
                                        iopsField.setValue(100);
                                    } else {
                                        tab.down('[name="db.msr.data_storage.raid.volume_size"]').isValid();
                                    }
                                }
							}
						}
					}, {
						xtype: 'textfield',
						itemId: 'db.msr.data_storage.raid.ebs.iops',
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
						hidden: true,
						margin: '0 0 0 2',
						width: 80,
                        listeners: {
                            change: function(comp, value){
                                var tab = comp.up('#dbmsr'),
                                    sizeField = tab.down('[name="db.msr.data_storage.raid.volume_size"]');
                                if (tab.currentRole.get('new')) {
                                    if (comp.isValid() && comp.prev().getValue() === 'io1') {
                                        var minSize = Math.ceil(value*1/10);
                                        if (sizeField.getValue()*1 < minSize) {
                                            sizeField.setValue(minSize);
                                        }
                                    }
                                }
                            }
                        }
					}]
				}, {
                    xtype: 'container',
                    layout: {
                        type: 'hbox',
                        align: 'middle'
                    },
                    items: [{
                        xtype: 'textfield',
                        fieldLabel: 'Each volume size',
                        labelWidth: 160,
                        width: 240,
                        value: '10',
                        name: 'db.msr.data_storage.raid.volume_size',
                        maskRe: integerRe,
                        validator: function(value){
                            var minValue = 1,
                                container = this.up('#dbmsr');
                            if (container.down('[name="db.msr.data_storage.raid.ebs.type"]').getValue() === 'io1') {
                                minValue = Math.ceil(container.down('[name="db.msr.data_storage.raid.ebs.iops"]').getValue()*1/10);
                            }
                            if (value*1 > maxEbsStorageSize) {
                                return 'Maximum value is ' + maxEbsStorageSize + '.';
                            } else if (value*1 < minValue) {
                                return 'Minimum value is ' + minValue + '.';
                            }
                            return true;
                        }
                        
                    },{
                        xtype: 'label',
                        text: 'GB',
                        margin: '0 0 0 6'
                    }]
				}]
			}, {
			xtype: 'fieldset',
			checkboxToggle:  true,
			name: 'db.msr.data_bundle.enabled',
			title: 'Bundle and save data snapshot',
			defaults: {
				labelWidth: 150
			},
			items: [{
				xtype: 'fieldcontainer',
				layout: 'hbox',
				hideLabel: true,
				items: [{
					xtype: 'displayfield',
					value: 'Perform full data bundle every'
				}, {
					xtype: 'textfield',
					width: 40,
					margin: '0 0 0 3',
					name: 'db.msr.data_bundle.every'
				}, {
					xtype: 'displayfield',
					margin: '0 0 0 3',
					value: 'hours'
				}, {
					xtype: 'displayinfofield',
					margin: '0 0 0 5',
					info:   'DB snapshots contain a hotcopy of database data directory, file that holds binary log position and debian.cnf' +
							'<br>' +
							'When farm starts:<br>' +
							'1. Database master dowloads and extracts a snapshot from storage depends on cloud platfrom<br>' +
							'2. When data is loaded and master starts, slaves download and extract a snapshot as well<br>' +
							'3. Slaves are syncing with master for some time'
				}]
			}, {
				xtype: 'fieldcontainer',
				layout: 'hbox',
				fieldLabel: 'Preferred bundle window',
				items: [{
					xtype: 'textfield',
					name: 'db.msr.data_bundle.timeframe.start_hh',
					width: 40
				}, {
					xtype: 'displayfield',
					value: ':',
					margin: '0 0 0 3'
				}, {
					xtype: 'textfield',
					name: 'db.msr.data_bundle.timeframe.start_mm',
					width: 40,
					margin: '0 0 0 3'
				}, {
					xtype: 'displayfield',
					value: '-',
					margin: '0 0 0 3'
				}, {
					xtype: 'textfield',
					name: 'db.msr.data_bundle.timeframe.end_hh',
					width: 40,
					margin: '0 0 0 3'
				}, {
					xtype: 'displayfield',
					value: ':',
					margin: '0 0 0 3'
				},{
					xtype: 'textfield',
					name: 'db.msr.data_bundle.timeframe.end_mm',
					width: 40,
					margin: '0 0 0 3'
				}, {
					xtype: 'displayfield',
					value: 'Format: hh24:mi - hh24:mi',
					bodyStyle: 'font-style: italic',
					margin: '0 0 0 3'
				}]
			}, {
				xtype: 'combo',
				fieldLabel: 'Compression',
				store: [['', 'No compression (Recommended on small instances)'], ['gzip', 'gzip (Recommended on large instances)']],
				valueField: 'id',
				displayField: 'name',
				editable: false,
				queryMode: 'local',
				value: 'gzip',
				name: 'db.msr.data_bundle.compression',
				labelWidth: 80,
				width: 500
			}, {
				xtype: 'checkbox',
				hideLabel: true,
				name: 'db.msr.data_bundle.use_slave',
				boxLabel: 'Use SLAVE server for data bundle'
			}, {
				xtype: 'checkbox',
				hideLabel: true,
				name: 'db.msr.no_data_bundle_on_promote',
				boxLabel: 'Do not create data bundle during slave to master promotion process'
			}]
		}, {
			xtype: 'fieldset',
			checkboxToggle:  true,
			name: 'db.msr.data_backup.enabled',
			title: 'Backup data (gziped database dump)',
			defaults: {
				labelWidth: 150
			},
			items: [{
				xtype: 'fieldcontainer',
				layout: 'hbox',
				hideLabel: true,
				items: [{
					xtype: 'displayfield',
					value: 'Perform backup every'
				}, {
					xtype: 'textfield',
					width: 40,
					margin: '0 0 0 3',
					name: 'db.msr.data_backup.every'
				}, {
					xtype: 'displayfield',
					margin: '0 0 0 3',
					value: 'hours'
				}]
			}, {
				xtype: 'fieldcontainer',
				layout: 'hbox',
				fieldLabel: 'Preferred backup window',
				items: [{
					xtype: 'textfield',
					name: 'db.msr.data_backup.timeframe.start_hh',
					width: 40
				}, {
					xtype: 'displayfield',
					value: ':',
					margin: '0 0 0 3'
				}, {
					xtype: 'textfield',
					name: 'db.msr.data_backup.timeframe.start_mm',
					width: 40,
					margin:'0 0 0 3'
				}, {
					xtype: 'displayfield',
					value: '-',
					margin: '0 0 0 3'
				}, {
					xtype: 'textfield',
					name: 'db.msr.data_backup.timeframe.end_hh',
					width: 40,
					margin: '0 0 0 3'
				}, {
					xtype: 'displayfield',
					value: ':',
					margin: '0 0 0 3'
				},{
					xtype: 'textfield',
					name: 'db.msr.data_backup.timeframe.end_mm',
					width: 40,
					margin: '0 0 0 3'
				}, {
					xtype: 'displayfield',
					value: 'Format: hh24:mi - hh24:mi',
					bodyStyle: 'font-style: italic',
					margin: '0 0 0 3'
				}]
			}]
		}]
	});
});
