Scalr.regPage('Scalr.ui.account2.environments.platform.nimbula', function (loadParams, moduleParams) {
	var params = moduleParams['params'];

	var sendForm = function(disablePlatform) {
		var frm = form.getForm(),
			r = {
				processBox: {
					type: 'save'
				},
				form: frm,
				url: '/account/environments/' + moduleParams.env.id + '/platform/xSaveNimbula',
				success: function (data) {
					var flag = Scalr.flags.needEnvConfig && data.enabled;
					Scalr.event.fireEvent('update', '/account/environments/edit', moduleParams.env.id, 'nimbula', data.enabled);
					if (! flag)
						Scalr.event.fireEvent('close');
				}
			};
		if (disablePlatform) {
			frm.findField('nimbula.is_enabled').setValue(null);
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
			frm.findField('nimbula.is_enabled').setValue('on');
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
		title: 'Environments &raquo; ' + moduleParams.env.name + '&raquo; Nimbula',
		fieldDefaults: {
			anchor: '100%',
			labelWidth: 120
		},

		items: [{
			xtype: 'hidden',
			name: 'nimbula.is_enabled',
			value: 'on'
		}, {
			xtype: 'textfield',
			fieldLabel: 'Username',
			name: 'nimbula.username',
			value: params['nimbula.username']
		}, {
			xtype: 'textfield',
			fieldLabel: 'Password',
			name: 'nimbula.password',
			value: params['nimbula.password']
		}, {
			xtype: 'textfield',
			fieldLabel: 'API URL',
			name: 'nimbula.api_url',
			value: params['nimbula.api_url']
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
				hidden: !params['nimbula.is_enabled'] || Scalr.flags.needEnvConfig,
				text: 'Delete',
				handler: function() {
					sendForm(true);
				}
			}, {
				xtype: 'button',
				hidden: !Scalr.flags.needEnvConfig,
				margin: '0 0 0 5',
				text: "I'm not using Nimbula, let me configure another cloud",
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

	return form;
});
