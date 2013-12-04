Scalr.regPage('Scalr.ui.tools.aws.rds.instances.details', function (loadParams, moduleParams) {
	return Ext.create('Ext.form.Panel', {
		title: 'Tools &raquo; Amazon Web Services &raquo; RDS &raquo; DB Instance Details',
		bodyCls: 'x-panel-body-frame',
		tools: [{
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent('close');
			}
		}],
		items: moduleParams
	});
});
