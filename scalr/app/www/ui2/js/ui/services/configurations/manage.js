Scalr.regPage('Scalr.ui.services.configurations.manage', function (loadParams, moduleParams) {
	
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 900,
		title: 'Services &raquo; Configurations &raquo; Manage',

		items: [{
			xtype: 'fieldset',
			title: 'General information',
			labelWidth: 200,
			items:[{
				xtype: 'displayfield',
				name: 'presetName',
				fieldLabel: 'Farm & Role',
				width: 600,
				value: moduleParams['farmName']+" &raquo; "+moduleParams['roleName']
			}, {
				xtype: 'displayfield',
				fieldLabel: 'Behavior',
				width: 600,
				value: moduleParams['behaviorName']
			}, {
				xtype: 'displayfield',
				name: 'masterServer',
				hidden: !moduleParams['masterServerId'],
				fieldLabel: 'Master server',
				width: 600,
				value: moduleParams['masterServerId'] ? "<a href='#/servers/"+moduleParams['masterServerId']+"/extendedInfo'>"+moduleParams['masterServer']['remoteIp']+" ("+moduleParams['masterServerId']+")</a>" : ""
			}, {
				xtype: 'combo',
				name: 'masterServer2',
				fieldLabel: 'Master server',
				width: 600,
				queryMode: 'local',
				editable: false,
				valueField: 'serverId',
				displayField: 'remoteIp',
				hidden: !!moduleParams['masterServerId'],
				emptyText: 'Please select master server...',
				store: moduleParams['servers'],
				listeners: {
					'select': function() {
						/*
						Scalr.Request({
							processBox: {
								type: 'load'
							},
							url: '/services/configurations/presets/xGetPresetOptions',
							params: {
								'compat4': 1,
								'presetId': moduleParams['presetId'],
								'presetName': form.down('[name="presetName"]').getValue(),
								'roleBehavior': form.down('[name="roleBehavior"]').getValue()
							},
							success: function (data) {
								var field = form.down('#optionsSet');

								field.removeAll();
								field.add(data.presetOptions);
								field.show();
							}
						});
						*/
					}
				}
			}]
		}, {
			xtype: 'fieldset',
			title: 'Configuration options',
			itemId: 'optionsSet',
			items: []
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
					
					var results = [];
					var configFields = form.child('#optionsSet').query('configfield');
					for (var i = 0; i < configFields.length; i++) {
						item = configFields[i];
						
						if (!item.getValue())
							form.child('#optionsSet').remove(item);
						else{
							results[results.length] = item.getValue();
							item.clearStatus();
						}
					}
					
					Scalr.Request({
						processBox: {
							type: 'save'
						},
						form: form.getForm(),
						url: '/services/configurations/xSave/',
						params: {
							'masterServerId': moduleParams['masterServerId'], 
							'farmRoleId': moduleParams['farmRoleId'], 
							'behavior': moduleParams['behavior'], 
							'config': Ext.encode(results) 
						},
						success: function () {
							Scalr.event.fireEvent('refresh');
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
	
	var optionsSet = form.down("#optionsSet");
	
	for (name in moduleParams['config']) {
		
		var itemId = name.replace(/[^a-zA-Z0-9]+/gi, '');
		
		optionsSet.add({
			'xtype': 'fieldset',
			'itemId' : itemId,
			'flex': 1,
			'title': name,
			'items': []
		});
		
		for (settingName in moduleParams['config'][name]) {
			form.down('#'+itemId).add({
				showRemoveButton: true,
				notEditable: true,
				configFile: name,
				xtype: 'configfield',
				value: {key: settingName, value: moduleParams['config'][name][settingName]}
			});
		}
		
		form.down('#'+itemId).add({
			xtype: 'configfield',
			configFile: name
		});
	}
	
	//form.down("#optionsSet").

	return form;
});