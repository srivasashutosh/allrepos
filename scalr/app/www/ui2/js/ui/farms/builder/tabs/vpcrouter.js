Scalr.regPage('Scalr.ui.farms.builder.tabs.vpcrouter', function (moduleTabParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'VPC router settings',
        itemId: 'vpcrouter',
        layout: 'anchor',
        
		isEnabled: function (record) {
			return record.get('platform') === 'ec2';
		},

		getDefaultValues: function (record) {
			return {
			};
		},

		beforeShowTab: function (record, handler) {
            handler();
		},

		showTab: function (record) {
			var settings = record.get('settings');
			
			this.down('[name="router.vpc.networkInterfaceId"]').setValue(settings['router.vpc.networkInterfaceId'] || '-');
			this.down('[name="router.vpc.ip"]').setValue(settings['router.vpc.ip'] || '-');
			this.down('[name="router.vpc.ipAllocationId"]').setValue(settings['router.vpc.ipAllocationId'] || '-');
		},

		hideTab: function (record) {
			//var settings = record.get('settings');

			//record.set('settings', settings);
		},

		items: [{
            xtype: 'displayfield',
			name: 'router.vpc.networkInterfaceId',
            fieldLabel: 'Network Interface ID',
            value: '',
            width: 500,
			labelWidth: 150
        }, {
            xtype: 'displayfield',
			name: 'router.vpc.ip',
            fieldLabel: 'Proxy IP address',
            value: '',
            width: 500,
			labelWidth: 150
        }, {
            xtype: 'displayfield',
			name: 'router.vpc.ipAllocationId',
            fieldLabel: 'IP Allocation ID',
            value: '',
            width: 500,
			labelWidth: 150
        }]
	});
});
