Scalr.regPage('Scalr.ui.farms.builder.tabs.advanced', function (moduleTabParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Advanced',
        itemId: 'advanced', 
        layout: 'anchor',
        
		getDefaultValues: function (record) {
			return {
                'base.keep_scripting_logs_time': 3600,
                
				'system.timeouts.reboot': 360,
				'system.timeouts.launch': 2400
                
			};
		},

        isEnabled: function (record) {
			return record.get('platform') != 'rds';
		},

		showTab: function (record) {
			var settings = record.get('settings', true);
            this.setFieldValues({
                'base.keep_scripting_logs_time': Math.round(settings['base.keep_scripting_logs_time']/3600) || 1,
                
                'system.timeouts.reboot': settings['system.timeouts.reboot'] || 360,
                'system.timeouts.launch': settings['system.timeouts.launch'] || 2400,
                
                'dns.exclude_role': settings['dns.exclude_role'] == 1,
                'dns.int_record_alias': settings['dns.int_record_alias'] || '',
                'dns.ext_record_alias': settings['dns.ext_record_alias'] || ''
            });
		},

		hideTab: function (record) {
			var settings = record.get('settings');

            settings['base.keep_scripting_logs_time'] = this.down('[name="base.keep_scripting_logs_time"]').getValue()*3600;
            
			settings['system.timeouts.reboot'] = this.down('[name="system.timeouts.reboot"]').getValue();
			settings['system.timeouts.launch'] = this.down('[name="system.timeouts.launch"]').getValue();

            settings['dns.exclude_role'] = this.down('[name="dns.exclude_role"]').getValue() ? 1 : 0;
			settings['dns.int_record_alias'] = this.down('[name="dns.int_record_alias"]').getValue();
			settings['dns.ext_record_alias'] = this.down('[name="dns.ext_record_alias"]').getValue();
            
			record.set('settings', settings);
		},
        defaults: {
            defaults: {
                maxWidth: 800,
                anchor: '100%'
            }
        },
		items: [{
			xtype: 'fieldset',
            title: 'General',
			items: [{
				xtype: 'container',
				layout: {
                    type: 'hbox',
                    align: 'middle'
                },
				items: [{
					xtype: 'label',
					text: "Rotate scripting logs every"
				}, {
					xtype: 'numberfield',
					name: 'base.keep_scripting_logs_time',
					margin: '0 5',
                    width: 60,
                    minValue: 1,
                    allowDecimals: false,
                    listeners: {
                        blur: function(){
                            if (!this.getValue()) {
                                this.setValue(1);
                            }
                        }
                    }
				}, {
					xtype: 'label',
					text: 'hour(s).'
				}]
            }]
		}, {
			xtype: 'fieldset',
            title: 'Timeouts',
			items: [{
				xtype: 'container',
				layout: {
                    type: 'hbox',
                    align: 'middle'
                },
				items: [{
					xtype: 'label',
					text: "Terminate instance if it will not send 'rebootFinish' event after reboot in"
				}, {
					xtype: 'textfield',
					name: 'system.timeouts.reboot',
					margin: '0 5',
                    width: 70
				}, {
					xtype: 'label',
					text: 'seconds.'
				}]
			}, {
				xtype: 'container',
				layout: {
                    type: 'hbox',
                    align: 'middle'
                },
				items: [{
					xtype: 'label',
					text: "Terminate instance if it will not send 'hostUp' or 'hostInit' event after launch in"
				}, {
					xtype: 'textfield',
					name: 'system.timeouts.launch',
					margin: '0 5',
                    width: 70
				}, {
					xtype: 'label',
					text: 'seconds.'
				}]
			}]
        },{
			xtype: 'fieldset',
            title: 'DNS',
			items: [{
				xtype: 'checkbox',
				name: 'dns.exclude_role',
				boxLabel: 'Exclude role from DNS zone'
            },{
                 xtype: 'component',
                 cls: 'x-fieldset-delimiter-large',
                 maxWidth: null
			},{
				xtype: 'displayfield',
				fieldCls: 'x-form-field-warning',
				value: 'Will affect only new records. Old ones WILL REMAIN the same.'
			}, {
				xtype: 'container',
				layout: {
                    type: 'hbox',
                    align: 'middle'
                },
				items: [{
					xtype: 'label',
					text: 'Create'
				}, {
					xtype: 'textfield',
					name: 'dns.int_record_alias',
                    flex: 1,
					margin: '0 5'
				}, {
					xtype: 'label',
                    width: 240,
					html: 'records instead of <b>int-%rolename%</b> ones'
				}]
			}, {
				xtype: 'container',
				layout: {
                    type: 'hbox',
                    align: 'middle'
                },
				items: [{
					xtype: 'label',
					text: 'Create'
				}, {
					xtype: 'textfield',
					name: 'dns.ext_record_alias',
                    flex: 1,
					margin: '0 5'
				}, {
					xtype: 'label',
                    width: 240,
					html: 'records instead of <b>ext-%rolename%</b> ones'
				}]
			}]
		}]
	});
});
