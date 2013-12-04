Scalr.regPage('Scalr.ui.servers.import_step1_2', function (loadParams, moduleParams) {
	function isValidIPAddress(ipaddr) {
		var re = /^\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}$/;
		if (re.test(ipaddr)) {
			var parts = ipaddr.split(".");
			if (parseInt(parseFloat(parts[0])) == 0) { return false; }
			for (var i=0; i<parts.length; i++) {
				if (parseInt(parseFloat(parts[i])) > 255) { return false; }
			}
			return true;
		} else {
			return false;
		}
	}

	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 700,
		title: 'Import server - Step 1 (Server details)',
		fieldDefaults: {
			anchor: '100%'
		},

		items: [{
			xtype: 'fieldset',
			title: 'Server information',
			labelWidth: 130,
			items: [{
				xtype: 'combo',
				fieldLabel: 'Platform',
				name: 'platform',
				store: moduleParams['platforms'],
				allowBlank: false,
				editable: false,
				value: '',
				itemId: 'platform_combo',
				queryMode: 'local',
				listeners: {
					'change': function() {
						var value = this.getValue();
						if (value != 'ec2' && value != 'gce') {
							var lstore = moduleParams['locations'][value];

							form.down('#loc_combo').store.load({ data: lstore });
							form.down('#loc_combo').setValue(form.down('#loc_combo').store.getAt(0).get('id'));
							form.down('#loc_combo').show().enable();
						} else {
							form.down('#loc_combo').hide().disable();
						}
						
						if (value != 'ec2' && value != 'gce' && value != 'rackspacengus' && value != 'rackspacenguk') {
							form.down('#ipAddress').show().enable();
						} else {
							form.down('#ipAddress').hide().disable();
						}
					}
				}
			}, {
				xtype: 'combo',
				fieldLabel: 'Cloud location',
				name: 'cloudLocation',
				store: {
					fields: [ 'id', 'name' ],
					proxy: 'object'
				},
				allowBlank: false,
				valueField: 'id',
				displayField: 'name',
				itemId: 'loc_combo',
				editable: false,
				value: '',
				queryMode: 'local',
				hidden: true
			}, {
				xtype: 'textfield',
				name: 'roleName',
				fieldLabel: 'Role name',
				value: ''
			}, {
                xtype: 'textfield',
				itemId: 'ipAddress',
                name: 'ipAddress',
                fieldLabel: 'IP address',
				emptyText: 'Leave blank for auto-detection',
                value: ''
            }]
		}],

		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			cls: 'x-docked-bottom-frame',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				xtype: 'button',
				text: 'Continue',
				handler: function() {
					if (form.getForm().isValid())
						Scalr.Request({
							processBox: {
								type: 'action',
								msg: 'Initializing import ...'
							},
							form: form.getForm(),
							url: '/servers/xImportStart/',
							success: function (data) {
								Scalr.event.fireEvent('redirect', '#/servers/' + data.serverId + '/importCheck');
							}
						});
				}
			}, {
				xtype: 'button',
				margin: '0 0 0 5',
				text: 'Cancel',
				handler: function() {
					Scalr.event.fireEvent('close');
				}
			}]
		}]
	});

	return form;
});
