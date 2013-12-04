Scalr.regPage('Scalr.ui.scaling.metrics.create', function (loadParams, moduleParams) {
	var action = (!loadParams['metricId']) ? 'Create' : 'Edit';

	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 700,
		title: 'Scaling &raquo; Metrics &raquo; ' + action,
		fieldDefaults: {
			anchor: '100%'
		},

		items: [{
			xtype: 'fieldset',
			title: 'General information',
			labelWidth: 120,
			items:[{
				xtype: 'textfield',
				name: 'name',
				fieldLabel: 'Name',
				value: moduleParams['name']
			}, {
				xtype: 'textfield',
				name: 'filePath',
				fieldLabel: 'File path',
				value: moduleParams['filePath']
			}, {
				xtype: 'combo',
				name: 'retrieveMethod',
				fieldLabel: 'Retrieve method',
				editable:false,
				value: (moduleParams['retrieveMethod'] || 'read'),
				queryMode: 'local',
				store: [['read','File-Read'], ['execute','File-Execute']]
			}, {
				xtype: 'combo',
				name: 'calcFunction',
				fieldLabel: 'Calculation function',
				editable:false,
				value: (moduleParams['calcFunction'] || 'avg'),
				queryMode: 'local',
				store: [['avg','Average'], ['sum','Sum']]
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
						url: '/scaling/metrics/xSave',
						form: form.getForm(),
						params: { metricId: loadParams['metricId'] },
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
