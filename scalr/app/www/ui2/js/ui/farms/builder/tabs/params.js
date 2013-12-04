Scalr.regPage('Scalr.ui.farms.builder.tabs.params', function (moduleTabParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Parameters',
        deprecated: true,
		tabData: null,
        layout: 'anchor',
        
		isEnabled: function (record) {
			return true;
		},

		beforeShowTab: function (record, handler) {
            this.up('#farmbuilder').cache.load(
                {
                    url: '/roles/xGetRoleParams',
                    params: {
                        roleId: record.get('role_id'),
                        farmId: moduleTabParams.farmId,
                        cloudLocation: record.get('cloud_location')
                    }
                },
                function(data, status) {
                    this.tabData = data;
                    status ? handler() : this.deactivateTab();
                },
                this,
                0
            );
		},

		showTab: function (record) {
			var pars = this.tabData || [], 
                params = record.get('params'), 
                comp = this.down('#params'), obj;
			comp.removeAll();

			// set loaded values
			if (! Ext.isObject(params)) {
				params = {};
				for (var i = 0; i < pars.length; i++)
					params[pars[i]['hash']] = pars[i]['value'];

				record.set('params', params);
			}

			if (pars.length) {
				for (var i = 0; i < pars.length; i++) {
					obj = {};
					obj['name'] = pars[i]['hash'];
					obj['fieldLabel'] = pars[i]['name'];
					obj['allowBlank'] = pars[i]['isrequired'] == 1 ? false : true;
					obj['value'] = params[pars[i]['hash']];

					if (pars[i]['type'] == 'text') {
						obj['xtype'] = 'textfield';
						obj['width'] = 200;
					}

					if (pars[i]['type'] == 'textarea') {
						obj['xtype'] = 'textarea';
						obj['width'] = 600;
						//obj['height'] = 300;
					}

					if (pars[i]['type'] == 'boolean' || pars[i]['type'] == 'checkbox') {
						obj['xtype'] = 'checkbox';
						obj['checked'] = params[pars[i]['hash']] == 1 ? true : false;
					}

					comp.add(obj);
				}

			} else {
				comp.add({
					xtype: 'displayfield',
					hideLabel: true,
					value: 'No parameters for this role'
				});
			}
		},

		hideTab: function (record) {
			var params = record.get('params'), comp = this.down('#params');

			comp.items.each(function (item) {
				if (item.xtype == 'textfield' | item.xtype == 'textarea')
					params[item.name] = item.getValue()
				else if (item.xtype == 'checkbox')
					params[item.name] = item.getValue() ? 1 : 0;
			});

			record.set('params', params);
		},

		items: [{
			xtype: 'displayfield',
            anchor: '100%',
			fieldCls: 'x-form-field-warning',
			value: 'This Parameters manager is deprecated. Please use <a href="#">GLOBAL VARIABLES</a> instead.',
			listeners: {
				afterrender: function() {
					this.el.down('a').on('click', function(e) {
                        this.up('farmroleedit').setActiveTab('variables');
						e.preventDefault();
					}, this);
				}
			}
		}, {
			xtype: 'fieldset',
			itemId: 'params'
		}]
	});
});
