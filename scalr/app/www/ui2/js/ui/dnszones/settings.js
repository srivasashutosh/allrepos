Scalr.regPage('Scalr.ui.dnszones.settings', function (loadParams, moduleParams) {
	return Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		title: 'DNS Zones &raquo; Settings',
		width: 900,

		items: [{
			xtype: 'fieldset',
			title: 'AXFR white list',
			items: [{
				xtype: 'textarea',
				name: 'axfrAllowedHosts',
				fieldLabel: 'IP address(es) that are allowed to transfer (copy) the zone information sepparated by ; (eg. 5.6.7.8;9.1.2.3)',
				labelAlign: 'top',
				anchor: '100%',
				grow: true,
				growMax: 400,
				value: moduleParams['axfrAllowedHosts']
			}]
		}, {
			xtype: 'fieldset',
			title: 'Scalr accounts authorized to create subdomains for this zone',
			items: [{
				xtype: 'textarea',
				name: 'allowedAccounts',
				fieldLabel: 'Email addresses sepparated by ; (eg. mysecondaccount@company.net;dev@company.net)',
				labelAlign: 'top',
				anchor: '100%',
				grow: true,
				growMax: 400,
				value: moduleParams['allowedAccounts']
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
				handler: function () {
					Scalr.Request({
						processBox: {
							type: 'save'
						},
						url: '/dnszones/xSaveSettings/',
						form: this.up('form').getForm(),
						params: loadParams,
						success: function () {
							Scalr.event.fireEvent('close');
						}
					})
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
