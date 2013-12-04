Scalr.regPage('Scalr.ui.scripts.events.create', function (loadParams, moduleParams) {
	var action = (!loadParams['eventId']) ? 'Create' : 'Edit';

	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 700,
		title: 'Scripts &raquo; Events &raquo; ' + action,
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
				name: 'description',
				fieldLabel: 'Description',
				value: moduleParams['description']
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
						url: '/scripts/events/xSave',
						form: form.getForm(),
						params: { eventId: loadParams['eventId'] },
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
