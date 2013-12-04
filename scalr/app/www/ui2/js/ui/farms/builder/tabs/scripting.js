Scalr.regPage('Scalr.ui.farms.builder.tabs.scripting', function (tabParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Scripting',

		itemId: 'scripting',
		layout: 'fit',
        bodyCls: 'x-panel-body-frame x-panel-body-plain',
		
        tabData: null,
        
		isEnabled: function (record) {
			return record.get('platform') != 'rds';
		},

		getDefaultValues: function (record) {
			record.set('scripting', []);
			return {};
		},

		beforeShowTab: function (record, handler) {
            this.up('#farmbuilder').cache.load(
                {
                    url: '/farms/builder/xGetScripts',
                    params: {
                        cloudLocation: record.get('cloud_location'),
                        roleId: record.get('role_id')
                    }
                },
                function(data, status){
                    this.tabData = data;
                    status ? handler() : this.deactivateTab();
                },
                this,
                0
            );
		},
		
		showTab: function (record) {
			var scripts = record.get('scripting'),
				roleScripts = this.tabData['roleScripts'] || {},
				roleParams = record.get('scripting_params'),
				params = {};
			
			if (Ext.isArray(roleParams)) {
				for (var i = 0; i < roleParams.length; i++) {
					params[roleParams[i]['hash']] = roleParams[i]['params'];
				}
			}

			for (var i in roleScripts) {
				scripts.push({
					role_script_id: roleScripts[i]['role_script_id'],
					event: roleScripts[i]['event_name'],
					issync: roleScripts[i]['issync'],
					order_index: roleScripts[i]['order_index'],
					params: params[roleScripts[i]['hash']] || roleScripts[i]['params'],
					script: roleScripts[i]['script_name'],
					script_id: roleScripts[i]['script_id'],
					target: roleScripts[i]['target'],
					timeout: roleScripts[i]['timeout'],
					version: roleScripts[i]['version']+'',
					system: true,
					hash: roleScripts[i]['hash']
				});
			}
			
			
			var rolescripting = this.down('#rolescripting');
			rolescripting.setCurrentRole(record);
			
			//load farm roles
			var farmRoles = [],
				farmRolesStore = record.store;
			(farmRolesStore.snapshot || farmRolesStore.data).each(function(item){
				farmRoles.push({
					farm_role_id: item.get('farm_role_id'),
					platform: item.get('platform'),
					cloud_location: item.get('cloud_location'),
					role_id: item.get('role_id'),
					name: item.get('name'),
					current: item === record
				});
			});
			rolescripting.loadRoles(farmRoles);
			
			//load scripst, events and behaviors
			rolescripting.loadScripts(this.tabData['scripts'] || []);
			rolescripting.loadEvents(this.tabData['events'] || {});
			rolescripting.loadBehaviors(tabParams['behaviors']);

			//load role scripts
			rolescripting.loadRoleScripts(scripts);
		},

		hideTab: function (record) {
			var scripts = this.down('#rolescripting').getRoleScripts(),
				scripting = [], 
				scripting_params = [];
			
			scripts.each(function(item) {
				if (item.get('system') != true) {
					scripting.push(item.data);
				} else {
					scripting_params.push({
						role_script_id: item.get('role_script_id'),
						params: item.get('params'),
						hash: item.get('hash')
					});
				}
			});

			record.set('scripting', scripting);
			record.set('scripting_params', scripting_params);
			
			this.down('#rolescripting').clearRoleScripts();
		},
		
		items: {
			xtype: 'scriptfield2',
			itemId: 'rolescripting',
			margin: 0
		}
	});
});
