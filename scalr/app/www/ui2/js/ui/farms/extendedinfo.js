Scalr.regPage('Scalr.ui.farms.extendedinfo', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		title: 'Farm "' + moduleParams['name'] + '" extended information',
		scalrOptions: {
			'modal': true
		},
		width: 800,
		bodyCls: 'x-panel-body-frame',
		fieldDefaults: {
			labelWidth: 160
		},
		items: moduleParams['info'],
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
		}]
	});
	
	if (form.down('#repo')) {
		form.down('#repo').store.load({
			data: [{name:'latest', description:'Latest'}, {name:'stable', description:'Stable'}]
		});
	}
	
	if (form.down('#updSettingsSave')) {
		form.down('#updSettingsSave').on('click', function(){
			
			var params = form.getForm().getValues();
			params['farmId'] = loadParams['farmId'];
			
			Scalr.Request({
				processBox: {
					type: 'action',
					//msg: 'Saving auto-update configuration. This operation can take a few minutes, please wait...'
					msg:  'Saving configuration ...'
				},
				url: '/farms/xSaveSzrUpdSettings/',
				params: params,
				success: function(){
					//Scalr.event.fireEvent('refresh');
				}
			});
		});
	}
	
	return form;
});
