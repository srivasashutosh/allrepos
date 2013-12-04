Scalr.regPage('Scalr.ui.dm.applications.deploy', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 700,
		title: 'Deployments &raquo; Applications &raquo; Deploy',
		fieldDefaults: {
			anchor: '100%'
		},

		items: [{
			xtype: 'farmroles',
			title: 'Deploy target',
			params: moduleParams['farmWidget']
		}, {
			xtype: 'fieldset',
			title: 'Options',
			itemId: 'options',
			labelWidth: 150,
			items: [{
				xtype:'textfield',
                allowBlank: false,
				itemId: 'remotePath',
				fieldLabel: 'Remote path',
				name: 'remotePath'
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
				text: 'Deploy',
				handler: function () {
					Scalr.Request({
						processBox: {
							type: 'execute',
							msg: 'Deploying ...'
						},
						url: '/dm/applications/xDeploy/',
						params: loadParams,
						form: form.getForm(),
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

	return form;
});
