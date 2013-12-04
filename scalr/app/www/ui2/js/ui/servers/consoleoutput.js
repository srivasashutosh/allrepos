Scalr.regPage('Scalr.ui.servers.consoleoutput', function (loadParams, moduleParams) {
	return Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		scalrOptions: {
			'maximize': 'all',
			'reload': true
		},
		title: 'Server "' + moduleParams['name'] + '" console output',
		html: moduleParams['content'],
		autoScroll: true,
		tools: [{
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent('close');
			}
		}]
	});
});
