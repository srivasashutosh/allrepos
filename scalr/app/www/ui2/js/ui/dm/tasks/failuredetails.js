Scalr.regPage('Scalr.ui.dm.tasks.failuredetails', function (loadParams, moduleParams) {
	var form = new Ext.form.FormPanel({
		fieldDefaults: {
			anchor: '100%'
		},
		bodyCls: 'x-panel-body-frame',
		width:700,
		title: 'Deploy task information',
		items: [{
			xtype: 'fieldset',
			title: 'General information',
			labelWidth: 130,
			items: [{
				xtype: 'displayfield',
				name: 'email',
				fieldLabel: 'Failure reason',
				readOnly:true,
				anchor:"-20",
				value: '<span style="color:red;">' + moduleParams['last_error'] + '</span>'
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
				text: 'Close',
				handler: function() {
					Scalr.event.fireEvent('close');
				}
			}]
		}]
	});

	return form;
});
