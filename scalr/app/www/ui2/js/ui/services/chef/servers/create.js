Scalr.regPage('Scalr.ui.services.chef.servers.create', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		title: loadParams['servId'] ? 'Edit Chef server' : 'Create new Chef server',
		width: 780,
		bodyCls: 'x-panel-body-frame',
		scalrOptions: {
			modal: true
		},
		items: [{
			xtype: 'textfield',
			name: 'url',
			fieldLabel: 'URL',
			labelWidth: 100,
			anchor: '100%',
			allowBlank: false
		},{
			xtype: 'fieldset',
			title: 'Client auth info',
			defaults: {
				labelWidth: 70,
				anchor: '100%',
				allowBlank: false
			},
			items: [{
				xtype: 'textfield',
				name: 'userName',
				fieldLabel: 'Username'
			},{
				xtype: 'textarea',
				height: 200,
				name: 'authKey',
				fieldLabel: 'Key'

			}]
		},{
			xtype: 'fieldset',
			title: 'Client validator auth info',
			defaults: {
				labelWidth: 70,
				anchor: '100%',
				allowBlank: false
			},
			items: [{
				xtype: 'textfield',
				name: 'userVName',
				fieldLabel: 'Username'
			},{
				xtype: 'textarea',
				height: 200,
				name: 'authVKey',
				fieldLabel: 'Key'

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
				text: loadParams['servId'] ? 'Save' : 'Add',
				formBind: true,
				handler: function() {
					Scalr.Request({
						processBox: {
							type: 'action'
						},
						scope: this,
						form: form.getForm(),
						url: '/services/chef/servers/xSaveServer',
						params: {servId: loadParams['servId'] ? loadParams['servId'] : 0},
						success: function (data) {
							Scalr.event.fireEvent('close');
						}
					});
				}
			},{
				xtype: 'button',
				margin: '0 0 0 5',
				text: 'Cancel',
				handler: function() {
					Scalr.event.fireEvent('close');
				}
			}]
		}]
	});
	if(loadParams['servId'])
		form.getForm().setValues(moduleParams['servParams']);
	return form;
});