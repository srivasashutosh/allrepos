Scalr.regPage('Scalr.ui.core.variables', function (loadParams, moduleParams) {
	return Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 1000,
		title: 'Global variables',
		fieldDefaults: {
			labelWidth: 110
		},
		items: [{
			xtype: 'fieldset',
			items: [{
				xtype: 'variablefield',
				name: 'variables',
				currentScope: 'env',
				value: moduleParams.variables
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
					if (this.up('form').getForm().isValid())
						Scalr.Request({
							processBox: {
								type: 'save'
							},
							url: '/core/xSaveVariables/',
							form: this.up('form').getForm(),
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
});
