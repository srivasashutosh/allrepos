Scalr.regPage('Scalr.ui.tools.aws.rds.pg.edit', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel',{
		bodyCls: 'x-panel-body-frame',
		title: 'Tools &raquo; Amazon Web Services &raquo; Amazon RDS &raquo; Parameter groups &raquo; ' + loadParams['name'] + ' &raquo; Edit',
		width: 900,
		items: [{
			xtype: 'fieldset',
			title: 'General',
			itemId: 'general',
			defaults: {
				labelWidth: 250,
				xtype: 'displayfield'
			},
			items: [{
				fieldLabel: 'Parameter Group Name',
				name: 'DBParameterGroupName',
				value: moduleParams.group['dBParameterGroupName']
			},
			{
				fieldLabel: 'Engine',
				name: 'Engine',
				value: moduleParams.group['dBParameterGroupFamily']
			},
			{
				fieldLabel: 'Description',
				name: 'Description',
				value: moduleParams.group['description']
			}]
		},{
			xtype: 'fieldset',
			title: 'System parameters',
			itemId: 'system',
			items: moduleParams.params['system']
		},{
			xtype: 'fieldset',
			title: 'Engine default parameters',
			itemId: 'engine-default',
			items: moduleParams.params['engine-default']
		},{
			xtype: 'fieldset',
			title: 'User parameters',
			itemId: 'user',
			items: moduleParams.params['user']
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
						form: form.getForm(),
						url: '/tools/aws/rds/pg/xSave',
						params: loadParams,
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
			},{
				xtype: 'button',
				text: 'Reset to defaults',
				margin: '0 0 0 15',
				handler: function() {
					Scalr.Request({
						confirmBox: {
							msg: 'Are you sure you want to reset all parameters?',
							type: 'action'
						},
						processBox: {
							type: 'action'
						},
						url: '/tools/aws/rds/pg/xReset',
						params: loadParams,
						success: function (data) {
							document.location.reload();
						}
					});
				}
			}]
		}]
	});
	return form;
});
