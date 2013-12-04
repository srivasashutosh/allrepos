Scalr.regPage('Scalr.ui.farms.builder.tabs.ebs', function (moduleTabParams) {
	
	var pageParameters = Ext.urlDecode(window.location.search.substring(1));
	
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'EBS',
        deprecated: true,
        layout: 'anchor',
        
        tabData: null,
        
		isEnabled: function (record) {
			return record.get('platform') == 'ec2';
		},

		getDefaultValues: function (record) {
			return {
				'aws.use_ebs': 0
			};
		},

		beforeShowTab: function (record, handler) {
            this.up('#farmbuilder').cache.load(
                {
                    url: '/platforms/ec2/xGetSnapshots',
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

			this.down('[name="aws.ebs_snapid"]').reset();
			this.down('[name="aws.ebs_snapid"]').store.load({ data: this.tabData || [] });

			this.down('[name="aws.ebs_size"]').setValue(settings['aws.ebs_size'] || '5');
			this.down('[name="aws.ebs_snapid"]').setValue(settings['aws.ebs_snapid'] || '');
			this.down('[name="aws.ebs_mountpoint"]').setValue(settings['aws.ebs_mountpoint'] || '/mnt/storage');

			if (settings['aws.use_ebs'] == 1) {
				this.down('[name="aws.use_ebs"]').expand();
			} else {
				this.down('[name="aws.use_ebs"]').collapse();
				this.down('[name="aws.ebs_mountpoint"]').disable();
			}

			if (settings['aws.ebs_mount'] == 1) {
				this.down('[name="aws.ebs_mount"]').setValue(true);
				this.down('[name="aws.ebs_mountpoint"]').enable();
			} else {
				this.down('[name="aws.ebs_mount"]').setValue(false);
				this.down('[name="aws.ebs_mountpoint"]').disable();
			}
			
			this.down('[name="aws.ebs_type"]').setValue(settings['aws.ebs_type'] || 'standard');
			this.down('[name="aws.ebs_iops"]').setValue(settings['aws.ebs_iops'] || 100);
		},

		hideTab: function (record) {
			var settings = record.get('settings');

			if (! this.down('[name="aws.use_ebs"]').collapsed) {
				settings['aws.use_ebs'] = 1;
				settings['aws.ebs_size'] = this.down('[name="aws.ebs_size"]').getValue();
				settings['aws.ebs_snapid'] = this.down('[name="aws.ebs_snapid"]').getValue();
				settings['aws.ebs_type'] = this.down('[name="aws.ebs_type"]').getValue();
				settings['aws.ebs_iops'] = this.down('[name="aws.ebs_iops"]').getValue();

				if (this.down('[name="aws.ebs_mount"]').getValue()) {
					settings['aws.ebs_mount'] = 1;
					settings['aws.ebs_mountpoint'] = this.down('[name="aws.ebs_mountpoint"]').getValue();
				} else {
					settings['aws.ebs_mount'] = 0;
					delete settings['aws.ebs_mountpoint'];
				}
			} else {
				settings['aws.use_ebs'] = 0;
				delete settings['aws.ebs_mountpoint'];
				delete settings['aws.ebs_size'];
				delete settings['aws.ebs_snapid'];
				delete settings['aws.ebs_mount'];
				delete settings['aws.ebs_mountpoint'];
			}

			record.set('settings', settings);
		},

		items: [{
			xtype: 'displayfield',
			fieldCls: 'x-form-field-warning',
            anchor: '100%',
			value: 'This EBS manager is deprecated. Please use <a href="#">NEW STORAGE MANAGER</a> instead.',
			listeners: {
				afterrender: function() {
					this.el.down('a').on('click', function(e) {
						this.up('farmroleedit').setActiveTab('storage');
						e.preventDefault();
					}, this);
				}
			}
		}, {
			xtype: 'fieldset',
			name: 'aws.use_ebs',
			checkboxToggle: true,
			collapsed: true,
			title: 'Automatically attach EBS volume with the following options:',
			defaults: {
				labelWidth: 70
			},
			items: [{
				xtype: 'fieldcontainer',
				layout: 'hbox',
				fieldLabel: 'Size',
				items: [{
					xtype: 'textfield',
					name: 'aws.ebs_size',
					width: 40
				}, {
					xtype: 'displayfield',
					margin: '0 0 0 5',
					value: 'GB'
				}]
			}, {
				xtype: 'fieldcontainer',
				layout: 'hbox',
				name: 'aws.ebs_settings',
				fieldLabel: 'EBS type',
				width: 600,
				items: [{
					xtype: 'combo',
					store: [['standard', 'Standard'],['io1', 'Provisioned IOPS (1-1000): ']],
					valueField: 'id',
					displayField: 'name',
					editable: false,
					queryMode: 'local',
					name: 'aws.ebs_type',
					width: 200,
					listeners: {
						change: function (field, value) {
							var c = this.up().down('[name="aws.ebs_iops"]');
							if (value == 'io1')
								c.show();
							else
								c.hide();
						}
					}
				}, {
					xtype: 'textfield',
					itemId: 'aws.ebs_iops',
					name: 'aws.ebs_iops',
					hideLabel: true,
					hidden: true,
					margin: '0 0 0 5',
					width: 40,
					value: '500'
				}]
			}, {
				xtype: 'combo',
				name: 'aws.ebs_snapid',
				fieldLabel: 'Snapshot',
				editable: true,
				forceSelection: false,
				width: 500,
				typeAhead: true,
				allowBlank: true,
				selectOnFocus: true,
				valueField: 'snapid',
				displayField: 'snapid',
				queryMode: 'local',
				displayTpl: '<tpl for="."><tpl if="snapid">{snapid} (Created: {createdat}, Size: {size}GB)</tpl></tpl>',
				listConfig: {
					getInnerTpl: function() {
                    	return '<tpl for="."><tpl if="snapid">{snapid} (Created: {createdat}, Size: {size}GB)</tpl></tpl>';
                    }
				},
				store: {
					fields: [ 'snapid', 'createdat', 'size' ],
					proxy: 'object'
				}
			}, {
				xtype: 'fieldcontainer',
				layout: 'hbox',
				hideLabel: true,
				items: [{
					xtype: 'checkbox',
					boxLabel: 'Automatically mount device to',
					name: 'aws.ebs_mount',
					handler: function (field, checked) {
						if (checked)
							this.next('[name="aws.ebs_mountpoint"]').enable();
						else
							this.next('[name="aws.ebs_mountpoint"]').disable();
					}
				}, {
					xtype: 'textfield',
					margin: '0 0 0 5',
					name: 'aws.ebs_mountpoint'
				}, {
					xtype: 'displayfield',
					margin: '0 0 0 5',
					value: 'mount point.'
				}]
			}]
		}]
	});
});
