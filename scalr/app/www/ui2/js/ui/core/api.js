Scalr.regPage('Scalr.ui.core.api', function (loadParams, moduleParams) {
	var params = moduleParams;
	
	return Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 700,
		title: 'API access details & settings',
		fieldDefaults: {
			labelWidth: 110
		},
		items: [{
			xtype: 'fieldset',
			title: 'Enable API for current environment',
			checkboxToggle:  true,
			collapsed: !params['api.enabled'],
			checkboxName: 'api.enabled',
			inputValue: 1,
			items: [{
				xtype: 'fieldcontainer',
				layout: 'hbox',
				fieldLabel: 'API Key ID',
				items: [{
					xtype: 'textfield',
					flex: 1,
					name: 'api.access_key',
					readOnly: true,
					value: params['api.access_key']
				}, {
					xtype: 'button',
					margin: '0 0 0 5',
					text: 'Regenerate',
					hidden: !params['api.enabled'],
					handler: function () {
						Scalr.Request({
							confirmBox: {
								type: 'action',
								msg: 'Are you sure want to regenerate API keys ? This action will immediately replace your current keys.'
							},
							processBox: {
								type: 'action'
							},
							url: '/core/xRegenerateApiKeys',
							scope: this,
							success: function (data) {
								this.up('form').getForm().setValues({
									'api.access_key': data.keys.id,
									'api.secret_key': data.keys.key
								});
							}
						});
					}
				}]
			}, {
				xtype: 'textarea',
				name: 'api.secret_key',
				fieldLabel: 'API Access Key',
				readOnly: true,
				height: 100,
				anchor: '100%',
				value: params['api.secret_key']
			}, {
				xtype:'textarea',
				fieldLabel: 'API access whitelist (by IP address). Example: 67.45.3.7, 67.46.*.*, 91.*.*.*',
				labelAlign: 'top',
				name:'api.ip.whitelist',
				grow: true,
				growMax: 200,
				anchor: '100%',
				value: params['api.ip.whitelist']
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
					Scalr.Request({
						processBox: {
							type: 'save'
						},
						url: '/core/xSaveApiSettings/',
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
});
