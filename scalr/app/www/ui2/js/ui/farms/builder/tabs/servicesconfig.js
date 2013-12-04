Scalr.regPage('Scalr.ui.farms.builder.tabs.servicesconfig', function () {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Services config',
        deprecated: true,
		tabData: {},
        layout: 'anchor',
        
		isEnabled: function (record) {
			return record.get('platform') != 'rds';
		},

		beforeShowTab: function (record, handler) {
			var me = this, 
                beh = [];

			Ext.Array.each(record.get('behaviors').split(','), function (behavior) {
                if (me.tabData[behavior] === undefined) {
                    beh.push(behavior);
                }
			});

			if (! beh.length) {
				handler();
            } else {
				Scalr.Request({
					processBox: {
						type: 'action'
					},
					params: {
						behaviors: Ext.encode(beh)
					},
					url: '/services/configurations/presets/xGetList',
					scope: this,
					success: function (response) {
						for (var i in response.data) {
							response.data[i].unshift({ id: 0, name: 'Service defaults' });
                            me.tabData[i] = response.data[i];
						}

						handler();
					},
					failure: function () {
						this.deactivateTab();
					}
				});
            }
		},

		showTab: function (record) {
			var me = this,
                behaviors = record.get('behaviors').split(','), 
                config_presets = record.get('config_presets') || {}, 
                fieldset = me.down('#servicesconfig');

			Ext.Array.each(behaviors, function (behavior) {
				fieldset.add({
					xtype: 'combo',
					store: {
						fields: [ 'id', 'name' ],
						proxy: 'object',
						data: me.tabData[behavior]
					},
					fieldLabel: behavior,
					valueField: 'id',
					displayField: 'name',
					//disabled: !config_presets[behavior],
					editable: false,
					queryMode: 'local',
					behavior: behavior,
					value: config_presets[behavior] || 0
				});
			});
		},

		hideTab: function (record) {
			var config_presets = {}, fieldset = this.down('#servicesconfig');

			fieldset.items.each(function (item) {
				var value = item.getValue();
				if (value != '0')
					config_presets[item.behavior] = value;
			});

			fieldset.removeAll();
			record.set('config_presets', config_presets);
		},

		items: [{
			xtype: 'displayfield',
			fieldCls: 'x-form-field-warning',
            anchor: '100%',
			value: 'Services config is deprecated.'
		}, {
			xtype: 'fieldset',
			itemId: 'servicesconfig',
			defaults: {
				labelWidth: 100,
				width: 400
			}
		}]
	});
});
