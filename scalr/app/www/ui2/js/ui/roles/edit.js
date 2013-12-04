Scalr.regPage('Scalr.ui.roles.edit', function (loadParams, moduleParams) {
	var removeImages = [];

	var imagesStore = Ext.create('store.store', {
		data: moduleParams.role.images,
		fields: [ 'platform', 'location', 'platform_name', 'location_name', 'image_id', 'architecture'],
		proxy: 'object'
	});

	var optionsStore = Ext.create('store.store', {
		data: moduleParams.role.parameters,
		fields: [ 'name', 'type', 'required', 'defval' ],
		proxy: 'object'
	});

	var platformsStore = Ext.create('store.store', {
		data: moduleParams.platforms,
		fields: [ 'id', 'name', 'locations' ],
		proxy: 'object'
	});

	var locationsStore = Ext.create('store.store', {
		fields: [ 'id', 'name' ],
		proxy: 'object'
	});

	var panel = Ext.create('Ext.tab.Panel', {
		scalrOptions: {
			'maximize': 'all'
		},
		title: moduleParams.role.id ? 'Roles &raquo; Edit &raquo; ' + moduleParams.role.name : 'Roles &raquo; Create new role',
		layout: 'fit',

		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			cls: 'x-docked-bottom-frame',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			itemId: 'toolbar',
			items: [{
				xtype: 'button',
				text: 'Save',
				itemId: 'save',
				handler: function() {
					var params = {};
					try {
						panel.items.each (function () {
							this.scalrPrivateGetData(params);
						});
					} catch (e) {
						return;
					}

					Scalr.Request({
						processBox: {
							type: 'save'
						},
						url: '/roles/xSaveRole/',
						params: params,
						success: function () {
							Scalr.event.fireEvent('close');
						}
					});
				}
			}, {
				xtype: 'button',
				margin: '0 0 0 5',
				text: 'Cancel',
				itemId: 'cancel',
				handler: function() {
					Scalr.event.fireEvent('close');
				}
			}]
		}]
	});

	var checkboxBehaviorListener = function() {
		var value = '';
		tabInfo.down('#behaviors').items.each(function() {
			if (this.checked && value != '')
				value = 'Mixed images'
			else if (this.checked) {
				if (this.inputValue == 'app')
					value = 'Application servers'
				else if (this.inputValue == 'base')
					value = 'Base images'
				else if (this.inputValue == 'mysql')
					value = 'Database servers'
				else if (this.inputValue == 'mysql2')
					value = 'Database servers'
				else if (this.inputValue == 'percona')
					value = 'Database servers'
				else if (this.inputValue == 'www')
					value = 'Load balancers'
				else if (this.inputValue == 'haproxy')
					value = 'Load balancers'
				else if (this.inputValue == 'memcached')
					value = 'Caching servers'
				else if (this.inputValue == 'cassandra')
					value = 'Database servers';
				else if (this.inputValue == 'postgresql')
					value = 'Database servers';
				else if (this.inputValue == 'mongodb')
					value = 'Database servers';
			    else if (this.inputValue == 'mariadb')
                    value = 'Database servers';
				else if (this.inputValue == 'redis')
					value = 'Database servers';
				else if (this.inputValue.indexOf('cf_') == 0)
					value = 'Cloud Foundry';
			}
		});

		tabInfo.down('[name="group"]').setValue(value);
	};

	var tabInfo = panel.add({
		xtype: 'form',
		title: 'Role Information',
		autoScroll: true,
		bodyCls: 'x-panel-body-frame',
		scalrPrivateGetData: function (params) {
			Ext.apply(params, this.getForm().getValues());
		},
		items: [{
			xtype: 'fieldset',
			items: [{
				xtype: 'textfield',
				fieldLabel: 'Name',
				name: 'name',
				width: 400,
				readOnly: moduleParams.role.id != 0 ? true : false,
				value: moduleParams.role.name
			}, {
				xtype: 'combo',
				fieldLabel: 'Scalr agent',
				name: 'agent',
				width: 400,
				readOnly: moduleParams.role.id != 0 ? true : false,
				store: [ [ '1', 'ami-scripts' ], [ '2', 'scalarizr' ] ],
				value: moduleParams.role.agent,
				queryMode: 'local',
				allowBlank: false,
				editable: false
			}, {
				xtype: 'textfield',
				fieldLabel: 'OS',
				width: 400,
				name: 'os',
				readOnly: moduleParams.role.id != 0 ? true : false,
				value: moduleParams.role.os
			}, {
				xtype: 'textarea',
				fieldLabel: 'Description',
				anchor: '100%',
				height: 100,
				name: 'description',
				value: moduleParams.role.description
			}, {
				xtype: 'textarea',
				fieldLabel: 'Software',
				anchor: '100%',
				height: 100,
				name: 'software',
				hidden: moduleParams.role.id != 0 ? true : false,
				value: ''
			}, {
				xtype: 'hidden',
				name: 'roleId',
				value: moduleParams.role.id
			}]
		}, {
			xtype: 'fieldset',
			title: 'Behaviors',
			items: [{
				xtype: 'checkboxgroup',
				columns: 4,
				fieldLabel: 'Behaviors',
				itemId: 'behaviors',
				listeners: {
					'afterrender': function () {
						var beh = moduleParams.role.behaviors.join(' ');
						this.items.each(function() {
							if (beh.match(this.inputValue))
								this.setValue(true);
						});
					}
				},
				items: [{
					boxLabel: 'Base',
					inputValue: 'base',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'MySQL',
					inputValue: 'mysql',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
                    boxLabel: 'MariaDB 5',
                    inputValue: 'mariadb',
                    name: 'behaviors[]',
                    readOnly: moduleParams.role.id != 0 ? true : false,
                    handler: checkboxBehaviorListener
                }, {
					boxLabel: 'MySQL 5.5',
					inputValue: 'mysql2',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'Percona Server 5.5',
					inputValue: 'percona',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'PostgreSQL',
					inputValue: 'postgresql',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'Apache',
					inputValue: 'app',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'Nginx',
					inputValue: 'www',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'HAProxy',
					inputValue: 'haproxy',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'Memcached',
					inputValue: 'memcached',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				},/* {
					boxLabel: 'Cassandra',
					inputValue: 'cassandra',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				},*/ {
					boxLabel: 'Redis',
					inputValue: 'redis',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'RabbitMQ',
					inputValue: 'rabbitmq',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'MongoDB',
					inputValue: 'mongodb',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'CF Router',
					inputValue: 'cf_router',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'CF Cloud Controller',
					inputValue: 'cf_cloud_controller',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'CF Health Manager',
					inputValue: 'cf_health_manager',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'CF DEA',
					inputValue: 'cf_dea',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'CF Service',
					inputValue: 'cf_service',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}, {
					boxLabel: 'Chef',
					inputValue: 'chef',
					name: 'behaviors[]',
					readOnly: moduleParams.role.id != 0 ? true : false,
					handler: checkboxBehaviorListener
				}]
			}, {
				xtype: 'displayfield',
				readOnly: true,
				fieldLabel: 'Group',
				name: 'group',
				width: 300
			}]
		}, {
			xtype: 'fieldset',
			items: [{
				xtype: 'checkboxgroup',
				columns: 4,
				fieldLabel: 'Tags',
				itemId: 'tags',
				items: [{
					boxLabel: 'ec2.ebs',
					inputValue: 'ec2.ebs',
					checked: moduleParams.tags['ec2.ebs'] != undefined || false,
					readOnly: moduleParams.role.id != 0 ? true : false,
					name: 'tags[]'
				}, {
					boxLabel: 'ec2.hvm',
					inputValue: 'ec2.hvm',
					checked: moduleParams.tags['ec2.hvm'] != undefined || false,
					readOnly: moduleParams.role.id != 0 ? true : false,
					name: 'tags[]'
				}]
			}]
		}]
	});
	
	var tabImages = panel.add({
		title: 'Images',
		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		scalrPrivateGetData: function (params) {
			var data = [], records = imagesStore.getRange();
			for (var i = 0; i < records.length; i++)
				data[data.length] = { 
					image_id: records[i].get('image_id'), 
					platform: records[i].get('platform'), 
					location: records[i].get('location'),
					architecture: records[i].get('architecture')
				};

			params['images'] = Ext.encode(data);
			params['remove_images'] = Ext.encode(removeImages);
		},

		imageAddReset: function() {
			this.down('[name="image_platform"]').reset();
			this.down('[name="image_platform"]').setReadOnly(false);
			this.down('[name="image_location"]').setReadOnly(false);
			this.down('[name="image_id"]').reset();
			this.down('[name="architecture"]').reset();

			this.down('#image_add').show();
			this.down('#image_save').hide();
			this.down('#image_delete').hide();
			this.down('#image_cancel').hide();
		},

		items: [{
			bodyCls: 'x-panel-body-frame',
			width: 400,
			items: [{
				xtype: 'fieldset',
				title: 'Role details',
				defaults: {
					anchor: '100%',
					labelWidth: 100
				},
				items: [{
					xtype: 'combo',
					fieldLabel: 'Platform',
					store: platformsStore,
					valueField: 'id',
					displayField: 'name',
					allowBlank: false,
					editable: false,
					name: 'image_platform',
					queryMode: 'local',
					listeners: {
						change: function () {
							tabImages.down('[name="image_location"]').reset();
							
							if (this.getValue() == 'gce') {
								tabImages.down('[name="image_location"]').hide();
								tabImages.down('[name="image_location"]').setValue('');
							} else {
								if (this.getValue()) {
									tabImages.down('[name="image_location"]').show();
									locationsStore.load({ data: this.store.findRecord('id', this.getValue()).get('locations') });
								} else
									tabImages.down('[name="image_location"]').hide();
							}
						}
					}
				}, {
					xtype: 'combo',
					fieldLabel: 'Location',
					store: locationsStore,
					valueField: 'id',
					displayField: 'name',
					hidden: true,
					allowBlank: false,
					editable: false,
					name: 'image_location',
					queryMode: 'local',
					matchFieldWidth: false
				}, {
					xtype: 'combo',
					fieldLabel: 'Architecture',
					store: ['i386', 'x86_64'],
					allowBlank: false,
					editable: false,
					value: 'x86_64',
					name: 'architecture',
					queryMode: 'local'
				}, {
					xtype: 'textfield',
					fieldLabel: 'Image ID',
					allowBlank: false,
					name: 'image_id'
				}, {
					layout: 'column',
					items: [{
						text: 'Add',
						itemId: 'image_add',
						xtype: 'button',
						width: 70,
						handler: function () {
							var invalid = false;
							
							var platform = tabImages.down('[name="image_platform"]').getValue();
							
							invalid = !tabImages.down('[name="image_platform"]').isValid() || invalid;
							
							if (platform != 'gce')
								invalid = !tabImages.down('[name="image_location"]').isValid() || invalid;
								
							invalid = !tabImages.down('[name="image_id"]').isValid() || invalid;
							invalid = !tabImages.down('[name="architecture"]').isValid() || invalid;

							if (! invalid) {
								var platform = tabImages.down('[name="image_platform"]').getValue(),
									location = tabImages.down('[name="image_location"]').getValue(),
									image_id = tabImages.down('[name="image_id"]').getValue(),
									arch = tabImages.down('[name="architecture"]').getValue();

								if (platform == 'gce')
									location = '';

								Scalr.message.Flush();

								if (imagesStore.findBy(function (record) {
									if (record.get('platform') == platform && record.get('location') == location) {
										Scalr.message.Error('Image on this platform/location already exist');
										return true;
									}

									if (record.get('image_id') == image_id && record.get('location') == location) {
										Scalr.message.Error('Image ID ' + image_id + ' already used');
										return true;
									}
								}) == -1) {
									var location_name = '';
									if (location != '')
										location_name = locationsStore.getById(location).get('name');

									
									imagesStore.add({
										platform: platform,
										platform_name: platformsStore.getById(platform).get('name'),
										location: location,
										location_name: location_name,
										image_id: image_id,
										architecture: arch
									});
									tabImages.imageAddReset();
								}
							}
						}
					}, {
						text: 'Save',
						itemId: 'image_save',
						xtype: 'button',
						hidden: true,
						width: 70,
						handler: function () {
							var records = tabImages.down('#images_view').getSelectionModel().getSelection();

							if (records[0]) {
								records[0].set('architecture', tabImages.down('[name="architecture"]').getValue());
								
								var location = tabImages.down('[name="image_location"]').getValue();
								if (records[0].get('platform') == 'gce')
									location = '';
								
								records[0].set('location', location);
								if (location != '')
									records[0].set('location_name', locationsStore.getById(location).get('name'));
								else
									records[0].set('location_name', '');
								
								
								records[0].set('image_id', tabImages.down('[name="image_id"]').getValue());
								tabImages.imageAddReset();
								tabImages.down('#images_view').getSelectionModel().deselectAll();
							}
						}
					}, {
						text: 'Cancel',
						margin: '0 0 0 5',
						itemId: 'image_cancel',
						xtype: 'button',
						hidden: true,
						width: 70,
						handler: function () {
							tabImages.down('#images_view').getSelectionModel().deselectAll();
							tabImages.imageAddReset();
						}
					}, {
						text: 'Delete',
						margin:'0 0 0 5',
						itemId: 'image_delete',
						xtype: 'button',
						hidden: true,
						width: 70,
						handler: function () {
							var view = tabImages.down('#images_view'), records = view.getSelectionModel().getSelection();
							if (records[0]) {
								view.getSelectionModel().deselectAll();
								view.store.remove(records[0]);
								removeImages[removeImages.length] = records[0].get('image_id');
								tabImages.imageAddReset();
							}
						}
					}]
				}]
			}]
		}, {
			xtype: 'grid',
			flex: 1,
			itemId: 'images_view',
			border: false,
			bodyStyle: 'border-left-width: 1px !important',

			store: imagesStore,
			singleSelect: true,

			plugins: {
				ptype: 'gridstore'
			},

			viewConfig: {
				emptyText: 'No images found'
			},

			columns: [
			    { header: "Image ID", width: 300, dataIndex: 'image_id', sortable: true },
				{ header: "Platform", width: 200, dataIndex: 'platform_name', sortable: true },
				{ header: "Location", flex: 1, dataIndex: 'location_name', sortable: true },
				{ header: "Architecture", width: 120, dataIndex: 'architecture', sortable: true }
			],

			listeners: {
				afterrender: function () {
					this.headerCt.el.applyStyles('border-left-width: 1px !important');
				},
				selectionchange: function(c, selections) {
					if (selections.length) {
						var rec = selections[0];
						tabImages.down('[name="image_platform"]').setValue(rec.get('platform')).setReadOnly(true);
						tabImages.down('[name="image_location"]').setValue(rec.get('location')).setReadOnly(true);
						tabImages.down('[name="image_id"]').setValue(rec.get('image_id'));
						
						tabImages.down('[name="architecture"]').setValue(rec.get('architecture'));

						tabImages.down('#image_add').hide();
						tabImages.down('#image_save').show();
						tabImages.down('#image_delete').show();
						tabImages.down('#image_cancel').show();
					} else
						tabImages.imageAddReset();
				}
			}
		}]
	});

	var tabProperties = panel.add({
		title: 'Properties',
		bodyCls: 'x-panel-body-frame',
		scalrPrivateGetData: function (params) {
			params['properties'] = Ext.encode({
				'system.ssh-port': this.down('[name="default_ssh_port"]').getValue()
			});
		},

		items: [{
			xtype: 'fieldset',
			items: {
				xtype: 'fieldcontainer',
				fieldLabel: 'SSH port',
				layout: 'hbox',
				labelWidth: 60,
				items: [{
					xtype: 'textfield',
					name: 'default_ssh_port',
					width: 60,
					value: moduleParams.role['properties']['system.ssh-port']
				}, {
					xtype: 'displayinfofield',
					margin: '0 0 0 5',
					info: 'This setting WON\'T change default SSH port on the servers. This port should be opened in the security groups.'
				}]
			}
		}]
	});

	var tabParameters = panel.add({
		title: 'Parameters',
		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		scalrPrivateGetData: function (params) {
			var parameters = [], records = optionsStore.getRange();
			for (var i = 0; i < records.length; i++)
				parameters[parameters.length] = records[i].data;

			params['parameters'] = Ext.encode(parameters);
		},

		paramAddReset: function() {
			this.down('[name="fieldname"]').reset();
			this.down('[name="fieldrequired"]').reset();
			this.down('[name="fielddefval_textarea"]').reset();
			this.down('[name="fielddefval_textfield"]').reset();

			this.down('#param_add').show();
			this.down('#param_save').hide();
			this.down('#param_delete').hide();
			this.down('#param_cancel').hide();
		},

		paramGetValue: function () {
			var data = {}, valid = true;

			data['name'] = tabParameters.down('[name="fieldname"]').getValue();
			data['type'] = tabParameters.down('[name="fieldtype"]').getValue();
			data['required'] = tabParameters.down('[name="fieldrequired"]').getValue() ? 1 : 0;

			valid = tabParameters.down('[name="fieldname"]').isValid() && valid;
			valid = tabParameters.down('[name="fieldtype"]').isValid() && valid;

			if (! valid)
				return;

			if (data['type'] == 'text')
				data['defval'] = tabParameters.down('[name="fielddefval_textfield"]').getValue();

			if (data['type'] == 'textarea')
				data['defval'] = tabParameters.down('[name="fielddefval_textarea"]').getValue();

			return data;
		},

		items: [{
			border: false,
			width: 450,
			bodyCls: 'x-panel-body-plain-frame',
			items: [{
				xtype: 'fieldset',
				title: 'Parameter details',
				defaults: {
					anchor: '100%',
					labelWidth: 80
				},
				items: [{
					xtype: 'combo',
					fieldLabel: 'Type',
					store: [['text', 'Text'], ['textarea', 'Textarea'], ['checkbox', 'Boolean']],
					allowBlank: false,
					editable: false,
					name: 'fieldtype',
					queryMode: 'local',
					value: 'text',
					listeners: {
						change: function () {
							tabParameters.down('[name="fielddefval_textfield"]').hide();
							tabParameters.down('[name="fielddefval_textarea"]').hide();

							if (this.getValue() == 'text')
								tabParameters.down('[name="fielddefval_textfield"]').show();

							if (this.getValue() == 'textarea')
								tabParameters.down('[name="fielddefval_textarea"]').show();
						}
					}
				}, {
					xtype: 'textfield',
					name: 'fieldname',
					fieldLabel: 'Name',
					allowBlank: false
				}, {
					xtype: 'checkbox',
					boxLabel: 'Required?',
					name: 'fieldrequired'
				}, {
					xtype: 'textfield',
					fieldLabel: 'Default value',
					name: 'fielddefval_textfield'
				}, {
					xtype: 'textarea',
					fieldLabel: 'Default value',
					name: 'fielddefval_textarea',
					hidden: true,
				}, {
					layout: 'column',
					border: false,
					margin: '0 0 5 0',
					items: [{
						text: 'Add',
						itemId: 'param_add',
						xtype: 'button',
						width: 70,
						handler: function () {
							var data = tabParameters.paramGetValue();

							if (Ext.isObject(data)) {
								if (optionsStore.findExact('name', data['name']) == -1) {
									optionsStore.add(data);
									tabParameters.paramAddReset();
								} else
									tabParameters.down('[name="fieldname"]').markInvalid('Such param name already exist');
							}
						}
					}, {
						text: 'Save',
						itemId: 'param_save',
						xtype: 'button',
						hidden: true,
						width: 70,
						handler: function () {
							var records = tabParameters.down('#options_view').getSelectionModel().getSelection(), data = tabParameters.paramGetValue();

							if (Ext.isObject(data) && records[0]) {
								for (i in data)
									records[0].set(i, data[i]);

								tabParameters.paramAddReset();
								tabParameters.down('#options_view').getSelectionModel().deselectAll();
							}
						}
					}, {
						text: 'Cancel',
						margin: '0 0 0 5',
						itemId: 'param_cancel',
						xtype: 'button',
						hidden: true,
						width: 70,
						handler: function () {
							tabParameters.down('#options_view').getSelectionModel().deselectAll();
							tabParameters.paramAddReset();
						}
					}, {
						text: 'Delete',
						margin: '0 0 0 5',
						itemId: 'param_delete',
						xtype: 'button',
						hidden: true,
						width: 70,
						handler: function () {
							var view = tabParameters.down('#options_view'), records = view.getSelectionModel().getSelection();
							if (records[0]) {
								view.getSelectionModel().deselectAll();
								view.store.remove(records[0]);
								tabParameters.paramAddReset();
							}
						}
					}]
				}]
			}]
		}, {
			xtype: 'grid',
			flex: 1,
			itemId: 'options_view',
			border: false,
			bodyStyle: 'border-left-width: 1px !important',

			store: optionsStore,
			singleSelect: true,

			plugins: {
				ptype: 'gridstore'
			},

			viewConfig: {
				emptyText: 'No parameters found'
			},

			columns: [
				{ header: 'Name', flex: 100, dataIndex: 'name', sortable: true },
				{ header: 'Type', flex: 100, dataIndex: 'type', sortable: true },
				{ header: 'Required', flex: 20, dataIndex: 'required', sortable: true, xtype: 'templatecolumn', tpl:
					'<tpl if="required == 1"><img src="/ui2/images/icons/true.png"></tpl>' +
					'<tpl if="required != 1"><img src="/ui2/images/icons/false.png"></tpl>'
				}
			],

			listeners: {
				afterrender: function () {
					this.headerCt.el.applyStyles('border-left-width: 1px !important');
				},
				selectionchange: function(c, selections) {
					if (selections.length) {
						var rec = selections[0];

						tabParameters.down('[name="fieldtype"]').setValue(rec.get('type'));
						tabParameters.down('[name="fieldname"]').setValue(rec.get('name'));
						tabParameters.down('[name="fieldrequired"]').setValue(rec.get('required') == 1 ? true : false);

						if (rec.get('type') == 'text')
							tabParameters.down('[name="fielddefval_textfield"]').setValue(rec.get('defval'));

						if (rec.get('type') == 'textarea')
							tabParameters.down('[name="fielddefval_textarea"]').setValue(rec.get('defval'));

						tabParameters.down('#param_add').hide();
						tabParameters.down('#param_save').show();
						tabParameters.down('#param_cancel').show();
						tabParameters.down('#param_delete').show();
					} else
						tabParameters.paramAddReset();
				}
			}
		}]
	});
	
	if (moduleParams.role.id) {
		var tabSecurityRules = panel.add({
			title: 'Security rules',
			scalrPrivateGetData: function (params) {
				var data = [];
				Ext.each (tabSecurityRules.store.getRange(), function (item) {
					data.push(item.data);
				});
				
				params['security_rules'] = Ext.encode(data);
			},
	
			xtype: 'grid',
			itemId: 'view',
			border: false,
			store: {
				proxy: 'object',
				fields: ['id', 'ipProtocol', 'fromPort', 'toPort' , 'cidrIp', 'comment']
			},
			plugins: {
				ptype: 'gridstore'
			},

			viewConfig: {
				emptyText: 'No security rules defined',
				deferEmptyText: false
			},

			columns: [
				{ header: 'Protocol', flex: 120, sortable: true, dataIndex: 'ipProtocol' },
				{ header: 'From port', flex: 120, sortable: true, dataIndex: 'fromPort' },
				{ header: 'To port', flex: 120, sortable: true, dataIndex: 'toPort' },
				{ header: 'CIDR IP', flex: 200, sortable: true, dataIndex: 'cidrIp' },
				{ header: 'Comment', flex: 300, sortable: true, dataIndex: 'comment' },
				{ header: '&nbsp;', width: 30, sortable: false, dataIndex: 'id', align:'left', xtype: 'templatecolumn',
					tpl: '<img class="delete" src="/ui2/images/icons/delete_icon_16x16.png">'
				}
			],

			listeners: {
				itemclick: function (view, record, item, index, e) {
					if (e.getTarget('img.delete'))
						view.store.remove(record);
				}
			},

			dockedItems: [{
				xtype: 'toolbar',
				dock: 'top',
				layout: {
					type: 'hbox',
					align: 'left',
					pack: 'start'
				},
				items: [{
					ui: 'paging',
					iconCls: 'x-tbar-add',
					handler: function() {
						Scalr.Confirm({
							form: [{
								xtype: 'combo',
								name: 'ipProtocol',
								fieldLabel: 'Protocol',
								labelWidth: 120,
								editable: false,
								store: [ 'tcp', 'udp', 'icmp' ],
								value: 'tcp',
								queryMode: 'local',
								allowBlank: false
							}, {
								xtype: 'textfield',
								name: 'fromPort',
								fieldLabel: 'From port',
								labelWidth: 120,
								allowBlank: false,
								validator: function (value) {
									if (value < -1 || value > 65535) {
										return 'Valid ports are - 1 through 65535';
									}
									return true;
								}
							}, {
								xtype: 'textfield',
								name: 'toPort',
								fieldLabel: 'To port',
								labelWidth: 120,
								allowBlank: false,
								validator: function (value) {
									if (value < -1 || value > 65535) {
										return 'Valid ports are - 1 through 65535';
									}
									return true;
								}
							}, {
								xtype: 'textfield',
								name: 'cidrIp',
								fieldLabel: 'CIDR IP',
								value: '0.0.0.0/0',
								labelWidth: 120,
								allowBlank: false
							}, {
								xtype: 'textfield',
								name: 'comment',
								fieldLabel: 'Comment',
								value: '',
								labelWidth: 120,
								allowBlank: true
							}],
							ok: 'Add',
							title: 'Add security rule',
							formValidate: true,
							closeOnSuccess: true,
							scope: this,
							success: function (formValues) {
								var view = this.up('#view'), store = view.store;

								if (store.findBy(function (record) {
									if (
										record.get('ipProtocol') == formValues.ipProtocol &&
											record.get('fromPort') == formValues.fromPort &&
											record.get('toPort') == formValues.toPort &&
											record.get('cidrIp') == formValues.cidrIp
										) {
										Scalr.message.Error('Such rule exists');
										return true;
									}
								}) == -1) {
									store.add(formValues);
									return true;
								} else {
									return false;
								}
							}
						});
					}
				}]
			}]
		});

		tabSecurityRules.store.load({ data: moduleParams.role.security_rules });
	}

	var tabScripts = panel.add({
		title: 'Scripts',

		scalrPrivateGetData: function (params) {
			this.down('scripteventgrid').getSelectionModel().deselectAll();
			if (this.down('scripteventgrid').getSelectionModel().hasSelection()) {
				panel.setActiveTab(tabScripts);
				return;
			}

			var data = [];
			Ext.each(tabScripts.down('scripteventgrid').store.getRange(), function (item) {
				data.push(item.data);
			});

			params['scripts'] = Ext.encode(data);
		},

		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		items: [{
			xtype: 'scriptfield',
			width: 400,
			bodyCls: 'x-panel-body-plain-frame',
			scalrModuleData: moduleParams['scriptData'],
			scalrModuleStepStart: 5
		}, {
			xtype: 'scripteventgrid',
			flex: 1,
			border: false
		}]
	});

	tabScripts.down('scripteventgrid').store.load({ data: moduleParams.role.scripts });

	if (moduleParams.role.id && Ext.isDefined(moduleParams.role.variables)) {
		panel.add({
			title: 'Global variables',
			scalrPrivateGetData: function(params) {
				params['variables'] = this.down('[name="variables"]').getValue();
			},
			layout: 'fit',
			bodyCls: 'x-panel-body-frame',
			items: [{
				xtype: 'fieldset',
				autoScroll: true,
				items: [{
					xtype: 'variablefield',
					name: 'variables',
					currentScope: 'role',
					maxWidth: 1200,
					value: moduleParams.role.variables
				}]
			}]
		});
	}

	panel.setActiveTab(0);

	return panel;
});
