Scalr.regPage('Scalr.ui.account2.environments.platform.rackspace', function (loadParams, moduleParams) {
	var params = moduleParams['params'];
	
	var sendForm = function(disablePlatform) {
		var frm = form.getForm(),
			r = {
				processBox: {
					type: 'save'
				},
				form: frm,
				url: '/account/environments/' + moduleParams.env.id + '/platform/xSaveRackspace',
				success: function (data) {
					var flag = Scalr.flags.needEnvConfig && data.enabled;
					Scalr.event.fireEvent('update', '/account/environments/edit', moduleParams.env.id, 'rackspace', data.enabled);
					if (! flag)
						Scalr.event.fireEvent('close');
				}
			};
		if (disablePlatform) {
			frm.findField('rackspace.is_enabled.rs-ORD1').setValue(null);
			frm.findField('rackspace.is_enabled.rs-LONx').setValue(null);
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
			var locations = ['rs-ORD1', 'rs-LONx'];
			for (var i=0, len=locations.length; i<len; i++) {
				var locationDisabled = Ext.isEmpty(Ext.String.trim(frm.findField('rackspace.username.'+locations[i]).getValue()))
					&& Ext.isEmpty(Ext.String.trim(frm.findField('rackspace.api_key.'+locations[i]).getValue()));
					frm.findField('rackspace.is_enabled.'+locations[i]).setValue(locationDisabled?null:'on');
					frm.findField('rackspace.username.'+locations[i]).allowBlank = locationDisabled?true:false;
					frm.findField('rackspace.api_key.'+locations[i]).allowBlank = locationDisabled?true:false;
			}
			if (!frm.isValid()) return;
		}
		
		Scalr.Request(r);
	};
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		scalrOptions: {
			'modal': true
		},
		width: 600,
		title: 'Environments &raquo; ' + moduleParams.env.name + '&raquo; Rackspace',
		fieldDefaults: {
			anchor: '100%'
		},

		items: [{
			xtype: 'fieldset',
			title: 'Rackspace US cloud location',
			items: [{
				xtype: 'hidden',
				name: 'rackspace.is_enabled.rs-ORD1',
				value: params['rs-ORD1']
			}, {
				xtype: 'textfield',
				fieldLabel: 'Username',
				name: 'rackspace.username.rs-ORD1',
				value: (params['rs-ORD1']) ? params['rs-ORD1']['rackspace.username'] : ''
			}, {
				xtype: 'textfield',
				fieldLabel: 'API Key',
				name: 'rackspace.api_key.rs-ORD1',
				value: (params['rs-ORD1']) ? params['rs-ORD1']['rackspace.api_key'] : ''
			}, {
				xtype: 'checkbox',
				name: 'rackspace.is_managed.rs-ORD1',
				checked: (params['rs-ORD1'] && params['rs-ORD1']['rackspace.is_managed']) ? true : false,
				hideLabel: true,
				boxLabel: 'Check this checkbox if your account is managed'
			}]
		}, {
			xtype: 'fieldset',
			title: 'Rackspace UK cloud location',
			items: [{
				xtype: 'hidden',
				name: 'rackspace.is_enabled.rs-LONx',
				value: params['rs-LONx']
			}, {
				xtype: 'textfield',
				fieldLabel: 'Username',
				name: 'rackspace.username.rs-LONx',
				value: (params['rs-LONx']) ? params['rs-LONx']['rackspace.username'] : ''
			}, {
				xtype: 'textfield',
				fieldLabel: 'API Key',
				name: 'rackspace.api_key.rs-LONx',
				value: (params['rs-LONx']) ? params['rs-LONx']['rackspace.api_key'] : ''
			}, {
				xtype: 'checkbox',
				name: 'rackspace.is_managed.rs-LONx',
				checked: (params['rs-LONx'] && params['rs-LONx']['rackspace.is_managed']) ? true : false,
				hideLabel: true,
				boxLabel: 'Check this checkbox if your account is managed'
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
				hidden: !params['rs-LONx'] && !params['rs-ORD1'] || Scalr.flags.needEnvConfig,
				text: 'Delete',
				handler: function() {
					sendForm(true);
				}
			}, {
				xtype: 'button',
				hidden: !Scalr.flags.needEnvConfig,
				margin: '0 0 0 5',
				text: "I'm not using Rackspace, let me configure another cloud",
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
