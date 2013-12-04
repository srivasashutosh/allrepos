Scalr.regPage('Scalr.ui.logs.scriptingmessage', function (loadParams, moduleParams) {
	// TODO: check if needed
	return Ext.create('Ext.panel.Panel', {
		title: 'Logs &raquo; Scripting &raquo; Message',
		scalrOptions: {
			'modal': true
		},
		bodyCls: 'x-panel-body-frame',
		width: 800,
		tools: [{
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent('close');
			}
		}],
		layout: 'anchor',
		defaults: {
			anchor: '100%'
		},
		items: moduleParams
	});
});
