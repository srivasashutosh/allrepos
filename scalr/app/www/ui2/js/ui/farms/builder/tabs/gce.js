Scalr.regPage('Scalr.ui.farms.builder.tabs.gce', function (moduleTabParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'GCE settings',
        itemId: 'gce',
        layout: 'anchor',
        
		isEnabled: function (record) {
			return record.get('platform') == 'gce';
		},

		getDefaultValues: function (record) {
			return {
				'gce.network': 'default'
			};
		},

		beforeShowTab: function (record, handler) {
            Scalr.cachedRequest.load(
                {
                    url: '/platforms/gce/xGetOptions',
                    params: {}
                },
                function(data, status){
                    this.down('[name="gce.network"]').store.load({data: status ? data['networks'] : []});
                    status ? handler() : this.deactivateTab();
                },
                this
            );
		},

		showTab: function (record) {
			var settings = record.get('settings', true);
			this.down('[name="gce.network"]').setValue(settings['gce.network'] || 'default');
		},

		hideTab: function (record) {
			var settings = record.get('settings');

			settings['gce.network'] = this.down('[name="gce.network"]').getValue();
			
			record.set('settings', settings);
		},

		items: [{
			xtype: 'fieldset',
            layout: 'anchor',
            defaults: {
                anchor: '100%',
                maxWidth: 600
            },
			items: [{
				xtype: 'combo',
				store: {
					fields: [ 'name', 'description' ],
					proxy: 'object'
				},
				valueField: 'name',
				displayField: 'description',
				fieldLabel: 'Network',
				editable: false,
				queryMode: 'local',
				name: 'gce.network'
			}]
		}]
	});
});
