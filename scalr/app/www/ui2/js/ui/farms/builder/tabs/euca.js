Scalr.regPage('Scalr.ui.farms.builder.tabs.euca', function () {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Placement and type',
        itemId: 'eucalyptus',
		tabData: {},

		isEnabled: function (record) {
			return record.get('platform') == 'eucalyptus';
		},

		beforeShowTab: function (record, handler) {
            this.up('#farmbuilder').cache.load(
                {
                    url: '/platforms/eucalyptus/xGetAvailZones',
                    params: {
                        cloudLocation: record.get('cloud_location')
                    }
                },
                function(data, status) {
                    this.tabData = data;
                    status ? handler() : this.deactivateTab();
                },
                this,
                0
            );
		},

		showTab: function (record) {
			var settings = record.get('settings');

			if (record.get('arch') == 'i386') {
				this.down('[name="euca.instance_type"]').store.load({ data: ['m1.small', 'c1.medium'] });
				this.down('[name="euca.instance_type"]').setValue(settings['euca.instance_type'] || 'm1.small');
			} else {
				this.down('[name="euca.instance_type"]').store.load({ data: ['m1.large', 'm1.xlarge', 'c1.xlarge'] });
				this.down('[name="euca.instance_type"]').setValue(settings['euca.instance_type'] || 'm1.large');
			}

            var field = this.down('[name="euca.availability_zone"]');
            field.store.load({ data: [{ id: '', name: 'Default' }] });
			field.store.load({ data: this.tabData || [], addRecords: true });
			field.setValue(settings['euca.availability_zone'] || '');
		},

		hideTab: function (record) {
			var settings = record.get('settings');

			settings['euca.instance_type'] = this.down('[name="euca.instance_type"]').getValue();
			settings['euca.availability_zone'] = this.down('[name="euca.availability_zone"]').getValue();

			record.set('settings', settings);
		},

		items: [{
			xtype: 'fieldset',
			defaults: {
				fieldLabel: 200,
				width: 400
			},
			items: [{
				xtype: 'combo',
				store: {
					fields: [ 'id', 'name' ],
					proxy: 'object'
				},
				fieldLabel: 'Placement',
				valueField: 'id',
				displayField: 'name',
				editable: false,
				queryMode: 'local',
				name: 'euca.availability_zone'
			},{
				xtype: 'combo',
				store: {
					fields: [ 'id', 'name' ],
					proxy: 'object'
				},
				valueField: 'name',
				displayField: 'name',
				fieldLabel: 'Instances type',
				editable: false,
				queryMode: 'local',
				name: 'euca.instance_type'
			}]
		}]
	});
});
