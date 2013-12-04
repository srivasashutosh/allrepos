Scalr.regPage('Scalr.ui.farms.builder.tabs.rabbitmq', function () {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'RabbitMQ settings',
		itemId: 'rabbitmq',
		
		isEnabled: function(record){
			return record.get('behaviors').match('rabbitmq');
		},
		
		getDefaultValues: function(record){
			if (record.get('platform') == 'ec2') 
				var default_storage_engine = 'ebs';
			else if (record.get('platform') == 'rackspace') 
				var default_storage_engine = 'eph';
			else if (record.get('platform') == 'cloudstack' || record.get('platform') == 'idcf') 
				var default_storage_engine = 'csvol';
	        else if (record.get('platform') == 'gce') 
                var default_storage_engine = 'gce_persistent';
			
			
			return {
				'rabbitmq.data_storage.engine': default_storage_engine,
				'rabbitmq.data_storage.ebs.size': 2,
				'rabbitmq.nodes_ratio': '10%'
			};
		},
		
		showTab: function(record){
			var settings = record.get('settings');
			var storages = new Array();
			
			if (record.get('platform') == 'ec2') {
				storages[storages.length] = [{name:'ebs', description:'Single EBS Volume'}];
			}
			else if (record.get('platform') == 'rackspace' || record.get('platform') == 'rackspacengus' || record.get('platform') == 'rackspacenguk') {
				storages[storages.length] = [{name:'eph', description:'Ephemeral device'}];
			}
			else if (record.get('platform') == 'cloudstack' || record.get('platform') == 'idcf') {
				storages[storages.length] = [{name:'csvol', description:'CloudStack Block Volume'}];
			}
			else if (record.get('platform') == 'gce') {
				storages[storages.length] = [{name:'eph', description:'Ephemeral device'}];
                storages[storages.length] = {name:'gce_persistent', description:'GCE Persistent disk'};
            }
			
			this.down('[name="rabbitmq.data_storage.engine"]').store.load({
                data: storages
            });
			
			if (settings['rabbitmq.data_storage.engine'] != 'eph') {
			
				if (record.get('new')) 
					this.down('[name="rabbitmq.data_storage.ebs.size"]').setReadOnly(false);
				else 
					this.down('[name="rabbitmq.data_storage.ebs.size"]').setReadOnly(true);
				
				this.down('[name="rabbitmq.data_storage.ebs.size"]').setValue(settings['rabbitmq.data_storage.ebs.size']);
			}
			
			this.down('[name="rabbitmq.data_storage.engine"]').setValue(settings['rabbitmq.data_storage.engine']);
			
			this.down('[name="rabbitmq.nodes_ratio"]').setValue(settings['rabbitmq.nodes_ratio']);
		},
		
		hideTab: function(record){
			var settings = record.get('settings');
			
			settings['rabbitmq.data_storage.engine'] = this.down('[name="rabbitmq.data_storage.engine"]').getValue();
			
			settings['rabbitmq.nodes_ratio'] = this.down('[name="rabbitmq.nodes_ratio"]').getValue();
			
			if (settings['rabbitmq.data_storage.engine'] != 'eph') {
				if (record.get('new')) 
					settings['rabbitmq.data_storage.ebs.size'] = this.down('[name="rabbitmq.data_storage.ebs.size"]').getValue();
			}
			else {
				delete settings['rabbitmq.data_storage.ebs.size'];
			}
			
			record.set('settings', settings);
		},
		
		items: [{
			xtype: 'fieldset',
			title: 'RabbitMQ general settings',
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Disk nodes / RAM nodes ratio',
				name: 'rabbitmq.nodes_ratio',
				value: '10%',
				labelWidth: 180
			}]
		}, {
			xtype: 'fieldset',
			title: 'RabbitMQ data storage settings',
			items: [{
				xtype: 'combo',
				name: 'rabbitmq.data_storage.engine',
				fieldLabel: 'Storage engine',
				editable: false,
				store: {
                    fields: [ 'description', 'name' ],
                    proxy: 'object'
                },
                valueField: 'name',
                displayField: 'description',
				width: 400,
				labelWidth: 200,
				queryMode: 'local',
				listeners: {
					change: function(){
						this.up('#rabbitmq').down('[name="ebs_settings"]').hide();
						
						if (this.getValue() != 'eph') {
							this.up('#rabbitmq').down('[name="ebs_settings"]').show();
						}
					}
				}
			}]
		}, {
			xtype: 'fieldset',
			name: 'ebs_settings',
			title: 'Block Storage settings',
			hidden: true,
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Storage size (max. 1000 GB)',
				labelWidth: 160,
				width: 200,
				name: 'rabbitmq.data_storage.ebs.size'
			}]
		}]
	});
});
