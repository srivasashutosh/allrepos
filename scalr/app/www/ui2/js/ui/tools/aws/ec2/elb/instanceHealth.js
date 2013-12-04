Scalr.regPage('Scalr.ui.tools.aws.ec2.elb.instanceHealth', function(loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		tools: [{
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent('close');
			}
		}],
		bodyCls: 'x-panel-body-frame',
		width: 650,
		title: 'Instance Health',
		items: [{
			labelWidth: 63,
			xtype: 'displayfield',
			fieldLabel: 'State',
			value: moduleParams['state']
		},{
			data: moduleParams,
			xtype: 'displayfield',
			value: moduleParams['description'],
			tpl: new Ext.XTemplate('Description: <tpl if="this.State==\'OutOfService\'"><font color = "red">{Description}</tpl><tpl if="this.State==\'InService\'"><font color = "black">{Description}</tpl>',
			{State: moduleParams.state})
		}],
		dockedItems:[{
			xtype: 'container',
			dock: 'bottom',
			cls: 'x-docked-bottom-frame',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				xtype: 'button',
				text: 'Derigister instance from the load balancer',
				handler: function(){
					Scalr.Request({
						processBox: {
							type: 'delete'
						},
						url: '/tools/aws/ec2/elb/'+ loadParams['elbName'] +'/xDeregisterInstance',
						params: loadParams,
						success: function () {
							Scalr.event.fireEvent('close');
						}
					});
				}
			}]

		}]
	});
	return form;
});