Scalr.regPage('Scalr.ui.farms.builder.tabs.cloudstack', function (moduleParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Cloudstack settings',
        itemId: 'cloudstack',
        
        tabData: null,
        
        getTitle: function(record){
            return moduleParams.platforms[record.get('platform')].name + ' settings';
        },
        
		isEnabled: function (record) {
			return  Ext.Array.contains(['cloudstack', 'idcf', 'ucloud'], record.get('platform'));
		},

		getDefaultValues: function (record) {
			return {
				'cloudstack.network_id': '',
				'cloudstack.disk_offering_id': ''
			};
		},

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
                    this.tabData = data;
                    status ? handler() : this.deactivateTab();
                },
                this
            );
        },

		showTab: function (record) {
            var me = this,
                settings = record.get('settings'),
                field, defaultValue;
        
            Ext.Object.each({
                'cloudstack.network_id': 'networks',
                'cloudstack.disk_offering_id': 'diskOfferings', 
                'cloudstack.shared_ip.id': 'ipAddresses'
            }, function(fieldName, dataFieldName){
                defaultValue = null
                field = me.down('[name="' + fieldName + '"]');
                field.store.load({ data: me.tabData[dataFieldName] || [] });
                if (field.store.getCount() == 0) {
                    field.hide();
                } else {
                    defaultValue = field.store.getAt(0).get('id');
                    field.show();
                }
                field.setValue(!Ext.isEmpty(settings[fieldName]) ? settings[fieldName] : defaultValue);
            });
        },

		hideTab: function (record) {
			var settings = record.get('settings');
			settings['cloudstack.disk_offering_id'] = this.down('[name="cloudstack.disk_offering_id"]').getValue();
			settings['cloudstack.network_id'] = this.down('[name="cloudstack.network_id"]').getValue();
			
			settings['cloudstack.shared_ip.id'] = this.down('[name="cloudstack.shared_ip.id"]').getValue();
			if (settings['cloudstack.shared_ip.id']) {
				var r = this.down('[name="cloudstack.shared_ip.id"]').findRecordByValue(settings['cloudstack.shared_ip.id']);
				settings['cloudstack.shared_ip.address'] = r.get('name');
			} else {
				settings['cloudstack.shared_ip.address'] = "";
			}
			
			record.set('settings', settings);
		},

		items: [{
			xtype: 'fieldset',
            layout: 'anchor',
			defaults: {
				labelWidth: 150,
				maxWidth: 650,
                anchor: '100%'
			},
			items: [{
			}, {
				xtype: 'combo',
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
				fieldLabel: 'Disk offering',
				editable: false,
				queryMode: 'local',
				name: 'cloudstack.disk_offering_id'
			}, {
				xtype: 'combo',
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
				fieldLabel: 'Network',
				editable: false,
				queryMode: 'local',
				name: 'cloudstack.network_id'
			}, {
				xtype: 'combo',
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
				fieldLabel: 'Shared IP',
				editable: false,
				queryMode: 'local',
				name: 'cloudstack.shared_ip.id'
			}]
		}]
	});
});
