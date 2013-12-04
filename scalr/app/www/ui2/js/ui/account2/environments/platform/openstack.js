Scalr.regPage('Scalr.ui.account2.environments.platform.openstack', function (loadParams, moduleParams) {
	var params = moduleParams['params'];

	var isEnabledProp = moduleParams['platform'] + '.is_enabled';
	
	var sendForm = function(disablePlatform) {
		var frm = form.getForm(),
			r = {
				processBox: {
					type: 'save'
				},
				form: frm,
				params: { platform: moduleParams['platform']},
				url: '/account/environments/' + moduleParams.env.id + '/platform/xSaveOpenstack',
				success: function (data) {
					var flag = Scalr.flags.needEnvConfig && data.enabled;
					Scalr.event.fireEvent('update', '/account/environments/edit', moduleParams.env.id, moduleParams['platform'], data.enabled);
					if (! flag)
						Scalr.event.fireEvent('close');
				}
			};
		if (disablePlatform) {
			frm.findField(isEnabledProp).setValue(null);
			Ext.apply(r, {
				confirmBox: {
					msg: 'Delete this cloud?',
					type: 'delete',
					ok: 'Delete'
				},
				processBox: {
					msg: 'Deleting...'
				}
			});
		} else {
			frm.findField(isEnabledProp).setValue('on');
			if (!frm.isValid()) return;
		}
		
		Scalr.Request(r);
		
	}
	
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		scalrOptions: {
			'modal': true
		},
		width: 600,
		title: 'Environments &raquo; ' + moduleParams.env.name + '&raquo; ' + moduleParams['platformName'],
		fieldDefaults: {
			anchor: '100%',
			labelWidth: 120
		},

		items: [{
			xtype: 'hidden',
			name: isEnabledProp,
			value: 'on'
		}, {
			xtype: 'textfield',
			fieldLabel: 'Keystone URL',
			name: 'keystone_url',
			value: params['keystone_url'],
			hidden: false
		}, {
			xtype: 'textfield',
			fieldLabel: 'Username',
			name: 'username',
			value: params['username'],
			hidden: false
		}, {
			xtype: 'textfield',
			fieldLabel: 'Password',
			name: 'password',
			value: params['password'],
			hidden: true
		}, {
			xtype: 'textfield',
			fieldLabel: 'API key',
			name: 'api_key',
			value: params['api_key'],
			hidden: true
		}, {
			xtype: 'textfield',
			fieldLabel: 'Tenant name',
			name: 'tenant_name',
			value: params['tenant_name'],
			hidden: true
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
				text: 'Save',
				handler: function() {
					sendForm();
				}
			}, {
				xtype: 'button',
				margin: '0 0 0 5',
				hidden: Scalr.flags.needEnvConfig,
				text: 'Cancel',
				handler: function() {
					Scalr.event.fireEvent('close');
				}
			},{
				xtype: 'button',
				margin: '0 0 0 10',
				cls: 'x-btn-default-small-red',
				hidden: !params[isEnabledProp] || Scalr.flags.needEnvConfig,
				text: 'Delete',
				handler: function() {
					sendForm(true);
				}
			}, {
				xtype: 'button',
				hidden: !Scalr.flags.needEnvConfig,
				margin: '0 0 0 5',
				text: "I'm not using "+moduleParams['platformName']+", let me configure another cloud",
				handler: function () {
					Scalr.event.fireEvent('redirect', '#/account/environments/?envId=' + moduleParams.env.id, true);
				}
			}, {
				xtype: 'button',
				hidden: !Scalr.flags.needEnvConfig,
				margin: '0 0 0 5',
				text: 'Do this later',
				handler: function () {
					sessionStorage.setItem('needEnvConfigLater', true);
					Scalr.event.fireEvent('unlock');
					Scalr.event.fireEvent('redirect', '#/dashboard');
				}
			}]
		}]
	});

	if (moduleParams['platform'] == 'rackspacengus') {
		var apiUrl = form.down('[name="keystone_url"]')
		apiUrl.setValue('https://identity.api.rackspacecloud.com/v2.0');
		apiUrl.setReadOnly(true);
		
		form.down('[name="api_key"]').show();
		
		form.down('[name="password"]').hide();
		form.down('[name="tenant_name"]').hide();
	}
	else if (moduleParams['platform'] == 'rackspacenguk') {
		var apiUrl = form.down('[name="keystone_url"]')
		apiUrl.setValue('https://lon.identity.api.rackspacecloud.com/v2.0');
		apiUrl.setReadOnly(true);
		
		form.down('[name="api_key"]').show();
		
		form.down('[name="password"]').hide();
		form.down('[name="tenant_name"]').hide();
	} else {
		form.down('[name="api_key"]').hide();
		
		form.down('[name="password"]').show();
		form.down('[name="tenant_name"]').show();
	}

	return form;
});
