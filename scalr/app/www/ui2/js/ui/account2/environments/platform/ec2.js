Scalr.regPage('Scalr.ui.account2.environments.platform.ec2', function (loadParams, moduleParams) {
	var params = moduleParams['params'];

	var sendForm  = function(disablePlatform) {
		var frm = form.getForm(),
			r = {
				processBox: {
					type: 'save'
				},
				form: frm,
				url: '/account/environments/' + moduleParams.env.id + '/platform/xSaveEc2',
				params: {beta: loadParams['beta']},
				success: function (data) {
					Scalr.event.fireEvent('unlock');
					if (data.demoFarm) {
						Scalr.event.fireEvent('redirect', '#/farms/view?demoFarm=1', true);
					} else {
						var flag = Scalr.flags.needEnvConfig && data.enabled;
						Scalr.event.fireEvent('update', '/account/environments/edit', moduleParams.env.id, 'ec2', data.enabled);
						if (! flag)
							Scalr.event.fireEvent('close');
					}
				}
			};
		if (disablePlatform) {
			frm.findField('ec2.is_enabled').setValue(null);
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
			frm.findField('ec2.is_enabled').setValue('on');
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
		title: 'Environments &raquo; ' + moduleParams.env.name + '&raquo; Amazon EC2',
		fieldDefaults: {
			anchor: '100%',
			labelWidth: 130
		},

		items: [{
			xtype: 'displayfield',
			fieldCls: 'x-form-field-info',
			hidden: !Scalr.flags.needEnvConfig,
			value: 'Thanks for signing up to Scalr!<br><br>' +
				'The next step after signing up is to share your EC2 keys with us, or keys from any other infrastructure cloud. We use these keys to make the API calls to the cloud, on your behalf. These keys are stored encrypted on a secured, firewalled server.<br><br>' +
				'You can <a href="http://wiki.scalr.net/Tutorials/Create_an_AWS_account" target="_blank" style="font-weight: bold">get these keys by following this video</a>'
		}, {
			xtype: 'displayfield',
			fieldCls: 'x-form-field-info',
			hidden: Scalr.flags.needEnvConfig,
			value: '<a href="http://wiki.scalr.net/Tutorials/Create_an_AWS_account" target="_blank" style="font-weight: bold">Tutorial: How to obtain all this information.</a>'
		}, {
			xtype: 'hidden',
			name: 'ec2.is_enabled',
			value: 'on'
		}, {
			xtype: 'textfield',
			fieldLabel: 'Account Number',
			width: 320,
			name: 'ec2.account_id',
			value: params['ec2.account_id'],
			listeners: {
				'blur': function () {
					this.setValue(this.getValue().replace(/-/g, ''));
				}
			}
		}, {
			xtype: 'textfield',
			fieldLabel: 'Access Key ID',
			width: 320,
			name: 'ec2.access_key',
			value: params['ec2.access_key']
		}, {
			xtype: 'textfield',
			fieldLabel: 'Secret Access Key',
			width: 320,
			name: 'ec2.secret_key',
			value: params['ec2.secret_key']
		}, {
			xtype: 'filefield',
			fieldLabel: 'X.509 Certificate file',
			name: 'ec2.certificate',
            hidden: Ext.isEmpty(params['ec2.certificate']),
			value: params['ec2.certificate']
		}, {
			xtype: 'filefield',
			fieldLabel: 'X.509 Private Key file',
			name: 'ec2.private_key',
            hidden: Ext.isEmpty(params['ec2.private_key']),
			value: params['ec2.private_key']
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
				hidden: !params['ec2.is_enabled'] || Scalr.flags.needEnvConfig,
				text: 'Delete',
				handler: function() {
					sendForm(true);
				}
			}, {
				xtype: 'button',
				hidden: !Scalr.flags.needEnvConfig,
				margin: '0 0 0 5',
				text: "I'm not using AWS EC2, let me configure another cloud",
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
