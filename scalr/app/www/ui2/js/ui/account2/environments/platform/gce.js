Scalr.regPage('Scalr.ui.account2.environments.platform.gce', function (loadParams, moduleParams) {
	var params = moduleParams['params'];
	
	var sendForm = function(disablePlatform) {
		var frm = form.getForm(),
			r = {
				processBox: {
					type: 'save'
				},
				form: frm,
				url: '/account/environments/' + moduleParams.env.id + '/platform/xSaveGce',
				params: {beta: loadParams['beta']},
				success: function (data) {

					Scalr.event.fireEvent('unlock');

					if (data.demoFarm) {
						Scalr.event.fireEvent('redirect', '#/farms/view', true);
					} else {
						var flag = Scalr.flags.needEnvConfig && data.enabled;
						Scalr.event.fireEvent('update', '/account/environments/edit', moduleParams.env.id, 'gce', data.enabled);

						if (! flag)
							Scalr.event.fireEvent('close');
					}
				}
			};
		if (disablePlatform) {
			frm.findField('gce.is_enabled').setValue(null);
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
			frm.findField('gce.is_enabled').setValue('on');
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
		title: 'Environments &raquo; ' + moduleParams.env.name + '&raquo; Google Compute Engine',
		fieldDefaults: {
			anchor: '100%',
			labelWidth: 120
		},

		items: [{
			xtype: 'hidden',
			name: 'gce.is_enabled',
			value: 'on'
		}, {
			xtype: 'textfield',
			fieldLabel: 'Client ID',
			width: 320,
			name: 'gce.client_id',
			value: params['gce.client_id']
		}, {
			xtype: 'textfield',
			fieldLabel: 'Email (Service account name)',
			width: 320,
			name: 'gce.service_account_name',
			value: params['gce.service_account_name']
		}, {
			xtype: 'textfield',
			fieldLabel: 'Project ID',
			width: 320,
			name: 'gce.project_id',
			value: params['gce.project_id']
		}, {
			xtype: 'filefield',
			fieldLabel: 'Private key',
			name: 'gce.key',
			value: params['gce.key']
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
				hidden: !params['gce.is_enabled'] || Scalr.flags.needEnvConfig,
				text: 'Deleting',
				handler: function() {
					sendForm(true);
				}
			}, {
				xtype: 'button',
				hidden: !Scalr.flags.needEnvConfig,
				margin: '0 0 0 5',
				text: "I'm not using GCE, let me configure another cloud",
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
