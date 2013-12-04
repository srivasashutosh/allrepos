Scalr.regPage('Scalr.ui.farms.roles.extendedinfo', function (loadParams, moduleParams) {
	return Ext.create('Ext.form.Panel', {
		title: 'Farms &raquo; ' + moduleParams['farmName'] + ' &raquo; ' + moduleParams['roleName'] + ' &raquo; Extended information',
		scalrOptions: {
			'modal': true
		},
		width: 900,
		bodyCls: 'x-panel-body-frame',
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
		items: moduleParams['form']
	});
});
