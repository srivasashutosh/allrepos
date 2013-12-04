Scalr.regPage('Scalr.ui.core.security', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		width: 700,
		bodyCls: 'x-panel-body-frame',
		title: 'Security',
		fieldDefaults: {
			anchor: '100%',
			labelWidth: 130
		},
		items: [{
			xtype: 'fieldset',
            hidden: Scalr.flags['authMode'] == 'ldap',
			items: [{
				xtype: 'textfield',
				inputType:'password',
				name: 'password',
				allowBlank: false,
				fieldLabel: 'Password',
				value: '******'
			},{
				xtype: 'textfield',
				inputType:'password',
				name: 'cpassword',
				allowBlank: false,
				fieldLabel: 'Confirm password',
				value: '******'
			}]
		}, {
			xtype: 'fieldset',
			hidden: !moduleParams['security_2fa'],
			title: 'Two-factor authentication based on <a href="http://code.google.com/p/google-authenticator/" target="_blank">google authenticator</a>',
			items: [{
				xtype: 'buttongroupfield',
				name: 'security_2fa_ggl',
				listeners: {
					beforetoggle: function(field, value) {
						if (value == '1') {
							var b32 = ('234567QWERTYUIOPASDFGHJKLZXCVBNM').split(''), barcode = '', qrcode = '';

							for (var i = 0; i < 16; i++)
								barcode = barcode + b32[Math.floor(Math.random() * (b32.length))];

							qrcode = '<img src="http://chart.apis.google.com/chart?cht=qr&chs=200x200&chld=H|0&chl=otpauth://totp/scalr:' + moduleParams['email']  + '?secret=' + barcode + '">';

							Scalr.utils.Window({
								xtype: 'form',
								title: 'Enable two-factor authentication',
								width: 400,
								items: [{
									xtype: 'fieldset',
									defaults: {
										labelWidth: 50,
										anchor: '100%'
									},
									items: [{
										xtype: 'textfield',
										readOnly: true,
										name: 'qr',
										value: barcode,
										fieldLabel: 'Key'
									}, {
										xtype: 'displayfield',
										hideLabel: true,
										padding: '0 0 0 55',
										name: 'qr',
										value: qrcode,
										height: 203
									}, {
										xtype: 'textfield',
										name: 'code',
										fieldLabel: 'Code',
										allowBlank: false
									}]
								}],
								dockedItems: [{
									xtype: 'container',
									dock: 'bottom',
									layout: {
										type: 'hbox',
										pack: 'center'
									},
									items: [{
										xtype: 'button',
										text: 'Enable',
										handler: function() {
											var fm = this.up('#box').getForm();

											if (fm.isValid())
												Scalr.Request({
													processBox: {
														type: 'action'
													},
													form: fm,
													url: '/core/xSettingsEnable2FaGgl/',
													success: function () {
														form.down('[name="security_2fa_ggl"]').setValue('1');
														this.up('#box').close();

													},
													scope: this
												});
										}
									}, {
										xtype: 'button',
										margin: '0 0 0 12',
										text: 'Cancel',
										handler: function() {
											this.up('#box').close();
										}
									}]
								}]
							});
						} else {
							Scalr.Request({
								confirmBox: {
									type: 'action',
									msg: 'Disable two-factor authentication',
									ok: 'Disable'
								},
								processBox: {
									type: 'action'
								},
								url: '/core/xSettingsDisable2FaGgl/',
								success: function () {
									form.down('[name="security_2fa_ggl"]').setValue('');
								},
								scope: this
							});
						}

						return false;
					}
				},
				items: [{
					xtype: 'button',
					text: 'Disabled',
					value: ''
				}, {
					text: 'Enabled',
					xtype: 'button',
					value: '1'
				}]
			}]
		}, {
			xtype: 'fieldset',
			title: 'IP access whitelist',
			items: [{
				xtype: 'displayfield',
				value: 'Example: 67.45.3.7, 67.46.*, 91.*'
			}, {
				xtype:'textarea',
				hideLabel: true,
				name: 'security_ip_whitelist',
				grow: true,
				growMax: 200,
				emptyText: 'Leave blank to disable',
				anchor: '100%'
			}]
		}],

		dockedItems: [{
			xtype: 'container',
			cls: 'x-docked-bottom-frame',
			dock: 'bottom',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				xtype: 'button',
				text: 'Save',
				handler: function() {
					Scalr.Request({
						processBox: {
							type: 'save'
						},
						url: '/core/xSecuritySave/',
						form: this.up('form').getForm(),
						success: function () {
							Scalr.event.fireEvent('close');
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

	form.getForm().setValues(moduleParams);
	return form;
});
