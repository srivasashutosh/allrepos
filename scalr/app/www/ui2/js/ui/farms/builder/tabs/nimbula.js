Scalr.regPage('Scalr.ui.farms.builder.tabs.nimbula', function () {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Nimbula settings',
        itemId: 'numbula',
		tabData: null,

		isEnabled: function (record) {
			return record.get('platform') == 'nimbula';
		},

		getDefaultValues: function (record) {
			return {
				'nimbula.shape': 'small'
			};
		},

		beforeShowTab: function (record, handler) {
            this.up('#farmbuilder').cache.load(
                {
                    url: '/platforms/nimbula/xGetShapes/',
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

			this.down('[name="nimbula.shape"]').store.load({ data: this.tabData });
			this.down('[name="nimbula.shape"]').setValue(settings['nimbula.shape'] || 'small');
		},

		hideTab: function (record) {
			var settings = record.get('settings');
			settings['nimbula.shape'] = this.down('[name="nimbula.shape"]').getValue();
			record.set('settings', settings);
		},

		items: [{
			xtype: 'fieldset',
			defaults: {
				labelWidth: 50,
				width: 300
			},
			items: [{
				xtype: 'combo',
				store: {
					fields: [ 'id', 'name' ],
					proxy: 'object'
				},
				valueField: 'id',
				displayField: 'name',
				fieldLabel: 'Shape',
				editable: false,
				queryMode: 'local',
				name: 'nimbula.shape'
			}]
		}]
	});
});
