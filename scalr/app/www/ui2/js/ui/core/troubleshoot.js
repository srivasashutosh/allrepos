Scalr.regPage('Scalr.ui.core.troubleshoot', function (loadParams, moduleParams) {
	return Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 700,
		title: 'Troubleshoot page',

		tools: [{
			type: 'refresh',
			handler: function () {
				Scalr.event.fireEvent('refresh');
			}
		}, {
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent('close');
			}
		}],

		items: [/*{
			xtype: 'fieldset',
			title: 'UI settings',
			items: [{
				xtype: 'button',
				text: 'Reset UI settings to defaults',
				// move action to link
				handler: function () {
					localStorage.clear();
					Scalr.message.Success('Settings successfully reset');
				}
			}]
		}, */{
			xtype: 'component',
			html: "If you experience problems, you could clear browser cache, reset UI settings to defaults (a href)"
			// post report about your system
		}]
	});
});
