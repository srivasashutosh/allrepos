Scalr.regPage('Scalr.ui.guest.updatePassword', function (loadParams, moduleParams) {
	if (moduleParams.authenticated) {
		Scalr.event.fireEvent('unlock');
		Scalr.event.fireEvent('redirect', '#/dashboard', true);
		return null;
	}

	if (! moduleParams.valid) {
		Scalr.message.Error('Invalid confirmation link.');
		Scalr.event.fireEvent('redirect', '#/guest/login', true);
		return null;
	}

	return Ext.create('Ext.panel.Panel', {
		title: 'New password',
		scalrOptions: {
			modal: true
		},
		bodyCls: 'x-panel-body-frame',
		width: 400,
		layout: 'anchor',
		items: [{
			xtype: 'textfield',
			inputType: 'password',
			fieldLabel: 'New password',
			labelWidth: 110,
			anchor: '100%',
			name: 'password',
			allowBlank: false,
			validator: function(value) {
				if (value.length < 6)
					return "Password should be longer than 6 chars";

				return true;
			}
		}, {
			xtype: 'textfield',
			fieldLabel: 'Confirm',
			inputType: 'password',
			labelWidth: 110,
			anchor: '100%',
			name: 'password2',
			allowBlank: false,
			validator: function(value) {
				if (value != this.prev('[name="password"]').getValue())
					return "Passwords doesn't match";

				return true;
			}
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
				text: 'Update my password',
				handler: function () {
					if (this.up('panel').down('[name="password"]').validate() && this.up('panel').down('[name="password2"]').validate()) {
						Scalr.Request({
							processBox: {
								type: 'action'
							},
							scope: this.up('panel'),
							params: {
								password: this.up('panel').down('[name="password"]').getValue(),
								hash: loadParams.hash || ''
							},
							url: '/guest/xUpdatePassword',
							success: function (data) {
								Scalr.event.fireEvent('close', true);
							}
						});
					}
				}
			}, {
				xtype: 'button',
				text: 'Cancel',
				margin: '0 0 0 5',
				handler: function () {
					Scalr.event.fireEvent('close', true);
				}
			}]
		}],
		listeners: {
			activate: function () {
				Scalr.event.fireEvent('lock');
			},
			deactivate: function () {
				Scalr.event.fireEvent('unlock');
			}
		}
	});
});
