Scalr.regPage('Scalr.ui.schedulertasks.create', function (loadParams, moduleParams) {
	var task = {};
	var scriptOptionsValue = {};
	var executionOptions = {};
	if (moduleParams['task']) {
		task = moduleParams['task'];
		executionOptions = moduleParams['task']['config'];
	}
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 1100,
		title: 'Scheduler tasks &raquo; ' + (moduleParams['task'] ? ('Edit &raquo; ' + moduleParams['task']['name']) : 'Create'),
		fieldDefaults: {
			anchor: '100%',
			labelWidth: 100
		},

		items: [{
			xtype: 'fieldset',
			title: 'Task',
			items: [{
				xtype: 'hidden',
				name: 'id'
			}, {
				xtype: 'textfield',
				fieldLabel: 'Name',
				name: 'name',
				allowBlank: false
			}, {
				xtype: 'combo',
				fieldLabel: 'Type',
				store: [ ['script_exec', 'Execute script'], ['terminate_farm', 'Terminate farm'], ['launch_farm', 'Launch farm']],
				editable: false,
				name: 'type',
				allowBlank: false,
				listeners: {
					change: function (field, newValue, oldValue) {
						var farmRoles = form.down('#farmRoles');
						if (newValue == 'script_exec') {
							farmRoles.optionChange('remove', 'disabledFarmRole');

							form.down('#executionOptions').show();
							form.down('#terminationOptions').hide();
							farmRoles.show();
						} else if (newValue == 'terminate_farm') {
							farmRoles.optionChange('add', 'disabledFarmRole');

							form.down('#executionOptions').hide();
							form.down('#scriptOptions').hide();
							form.down('#terminationOptions').show();
							farmRoles.show();

						} else if (newValue == 'launch_farm') {
							farmRoles.optionChange('add', 'disabledFarmRole');

							form.down('#executionOptions').hide();
							form.down('#scriptOptions').hide();
							form.down('#terminationOptions').hide();
							farmRoles.show();
						}
					}
				}
			}, {
                xtype: 'textarea',
                fieldLabel: 'Description',
                name: 'comments',
                grow: true,
                growMin: 22
            }]
		}, {
			xtype: 'farmroles',
			title: 'Target',
			itemId: 'farmRoles',
			hidden: true,
			params: moduleParams['farmWidget']
		}, {
			xtype: 'fieldset',
			title: 'Task settings',
			items: [{
				xtype: 'fieldcontainer',
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					value: 'Start from',
					width: 102
				}, {
					xtype: 'combo',
					store: [ 'Now', 'Specified time' ],
					editable: false,
					queryMode: 'local',
					margin: '0 0 0 3',
					width: 122,
					value: 'Now',
					name: 'startTimeType',
					listeners: {
						change: function (field, value) {
							if (value == 'Now')
								this.next().hide().disable().next().hide().disable();
							else
								this.next().show().enable().next().show().enable();
						}
					}
				}, {
					xtype: 'datefield',
					name: 'startTimeDate',
					hidden: true,
					disabled: true,
					allowBlank: false,
					format: 'Y-m-d',
					margin: '0 0 0 3',
					width: 110
				}, {
					xtype: 'timefield',
					name: 'startTimeTime',
					hidden: true,
					disabled: true,
					format: 'H:i',
					margin: '0 0 0 3',
					value: '00:00',
					width: 72
				}, {
					xtype: 'displayfield',
					margin: '0 0 0 3',
					value: 'and perform task every'
				}, {
					xtype: 'textfield',
					margin: '0 0 0 3',
					value: '30',
					name: 'restartEvery',
					allowBlank: false,
					width: 38
				}, {
					xtype: 'combo',
					store: [ 'minutes', 'hours', 'days' ],
					editable: false,
					queryMode: 'local',
					margin: '0 0 0 3',
					width: 85,
					value: 'minutes',
					name: 'restartEveryMeasure'
				}, {
					xtype: 'displayfield',
					margin: '0 0 0 3',
					value: 'till'
				}, {
					xtype: 'combo',
					editable: false,
					queryMode: 'local',
					margin: '0 0 0 3',
					width: 122,
					store: [ 'Forever', 'Specified time' ],
					value: 'Forever',
					name: 'endTimeType',
					listeners: {
						change: function (field, value) {
							if (value == 'Forever')
								this.next().hide().disable().next().hide().disable();
							else
								this.next().show().enable().next().show().enable();
						}
					}
				}, {
					xtype: 'datefield',
					name: 'endTimeDate',
					hidden: true,
					disabled: true,
					allowBlank: false,
					format: 'Y-m-d',
					margin: '0 0 0 3',
					width: 110
				}, {
					xtype: 'timefield',
					name: 'endTimeTime',
					hidden: true,
					disabled: true,
					allowBlank: false,
					format: 'H:i',
					margin: '0 0 0 3',
					value: '00:00',
					width: 72
				}]
			}, {
				xtype: 'fieldcontainer',
				layout: 'hbox',
				fieldLabel: 'Priority',
				items: [{
					xtype: 'textfield',
					name: 'orderIndex',
					value: 0,
					width: 60
				}, {
					xtype: 'displayinfofield',
					margin: '0 0 0 5',
					info: '0 - the highest priority'
				}]
			}, {
				xtype: 'combo',
				store: moduleParams['timezones'],
				fieldLabel: 'Timezone',
				queryMode: 'local',
				allowBlank: false,
				name: 'timezone',
				forceSelection: true,
				value: moduleParams['defaultTimezone'] || ''
			}]
		}, {
			xtype: 'fieldset',
			title: 'Execution options',
			itemId: 'executionOptions',
			hidden: true,
			items: [{
				xtype: 'combo',
				fieldLabel: 'Script',
				name: 'scriptId',
				store: {
					fields: [ 'id', 'name', 'description', 'issync', 'timeout', 'revisions' ],
					data: moduleParams['scripts'],
					proxy: 'object'
				},
				valueField: 'id',
				displayField: 'name',
				emptyText: 'Select a script',
				editable: true,
				forceSelection: true,
				queryMode: 'local',
				listeners: {
					change: function (field, value) {
						var cont = field.up(), r = field.findRecord('id', value), fR = cont.down('[name="scriptVersion"]');

						if (!r)
							return;

						fR.setValue();
						fR.store.loadData(r.get('revisions'));
						fR.store.sort('revision', 'DESC');
						fR.store.insert(0, { revision: -1, revisionName: 'Latest', fields: fR.store.first().get('fields') });
						fR.setValue(fR.store.first().get('revision'));

						cont.down('[name="scriptTimeout"]').setValue(r.get('timeout'));
						cont.down('[name="scriptIsSync"]').setValue(r.get('issync'));
					}
				}
			}, {
				xtype: 'combo',
				store: [ ['1', 'Synchronous'], ['0', 'Asynchronous']],
				editable: false,
				queryMode: 'local',
				name: 'scriptIsSync',
				fieldLabel: 'Execution mode'
			}, {
				xtype: 'textfield',
				fieldLabel: 'Timeout',
				name: 'scriptTimeout'
			},{
				xtype: 'combo',
				store: {
					fields: [{ name: 'revision', type: 'int' }, 'revisionName', 'fields' ],
					proxy: 'object'
				},
				valueField: 'revision',
				displayField: 'revisionName',
				editable: false,
				queryMode: 'local',
				name: 'scriptVersion',
				fieldLabel: 'Version',
				listeners: {
					change: function (field, value) {
						var r = this.findRecordByValue(value), fieldset = form.down('#scriptOptions');

						if (r) {
							var fields = r.get('fields');

							Ext.each(fieldset.items.getRange(), function(item) {
								scriptOptionsValue[item.name] = item.getValue();
							});
							fieldset.removeAll();
							if (Ext.isObject(fields)) {
								for (var i in fields) {
									fieldset.add({
										xtype: 'textfield',
										fieldLabel: fields[i],
										name: 'scriptOptions[' + i + ']',
										value: scriptOptionsValue['scriptOptions[' + i + ']'] ? scriptOptionsValue['scriptOptions[' + i + ']'] : '',
										width: 300
									});
								}
								fieldset.show();
							} else {
								fieldset.hide();
							}
						} else {
							fieldset.hide();
						}
					}
				}
			}]
		}, {
			xtype: 'fieldset',
			title: 'Script options',
			itemId: 'scriptOptions',
			labelWidth: 100,
			hidden: true,
			fieldDefaults: {
				width: 150
			}
		}, {
			xtype: 'fieldset',
			title: 'Termination options',
			itemId: 'terminationOptions',
			hidden: true,
			items: [{
				xtype: 'checkbox',
				boxLabel: 'Delete DNS zone from nameservers. It will be recreated when the farm is launched.',
				inputValue: 1,
				name: 'deleteDNSZones',
				checked: task['config'] ? (task['config']['deleteDNSZones'] ? task['config']['deleteDNSZones'] : false) : false
			}, {
				xtype: 'checkbox',
				boxLabel: 'Delete cloud objects (EBS, Elastic IPs, etc)',
				inputValue: 1,
				name: 'deleteCloudObjects',
				checked: task['config'] ? (task['config']['deleteCloudObjects'] ? task['config']['deleteCloudObjects'] : false): false
			}]
		}],

		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			cls: 'x-docked-bottom-frame',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				xtype: 'button',
				text: task ? 'Save' : 'Create',
				handler: function() {
					var task = {}, values = form.getForm().getValues();

					if (form.getForm().isValid()) {
						if (values['startTimeType'] == 'Now')
							task['startTime'] = '';
						else {
							task['startTime'] = values['startTimeDate'] + ' ' + values['startTimeTime'];
						}
							
						if (values['endTimeType'] == 'Forever')
							task['endTime'] = '';
						else {
							task['endTime'] = values['endTimeDate'] + ' ' + values['endTimeTime'];
						}

						if (values['restartEveryMeasure'] == 'days')
							task['restartEveryReal'] = values['restartEvery'] * 60 * 24;
						else if (values['restartEveryMeasure'] == 'hours')
							task['restartEveryReal'] = values['restartEvery'] * 60;

						Scalr.Request({
							processBox: {
								type: 'save'
							},
							form: form.getForm(),
							url: '/schedulertasks/xSave/',
							params: task,
							success: function () {
								Scalr.event.fireEvent('close');
							}
						});
					}
				}
			}, {
				xtype: 'button',
				margin: '0 0 0 5',
				text: 'Cancel',
				handler: function() {
					Scalr.event.fireEvent('close');
				}
			}]
		}]
	});

	if (task) {
		form.getForm().setValues(task);
		if (task['startTime']) {
			form.getForm().setValues({
				startTimeType: 'Specified time',
				startTimeDate: task['startTime'].split(' ')[0],
				startTimeTime: task['startTime'].split(' ')[1]
			});
		}
		if (task['endTime']) {
			form.getForm().setValues({
				endTimeType: 'Specified time',
				endTimeDate: task['endTime'].split(' ')[0],
				endTimeTime: task['endTime'].split(' ')[1]
			});
		}
		if (task['restartEvery']) {
			task['restartEveryMeasure'] = 'minutes';

			if (! (task['restartEvery'] % 60)) {
				task['restartEvery'] = task['restartEvery'] / 60;
				task['restartEveryMeasure'] = 'hours';
			}

			if (! (task['restartEvery'] % 24)) {
				task['restartEvery'] = task['restartEvery'] / 24;
				task['restartEveryMeasure'] = 'days';
			}

			form.getForm().setValues({
				restartEvery: task['restartEvery'],
				restartEveryMeasure: task['restartEveryMeasure']
			});
		}

		if (executionOptions['scriptId']) {
			executionOptions['scriptVersion'] = parseInt(executionOptions['scriptVersion']);
			for (var i in executionOptions['scriptOptions']) {
				scriptOptionsValue['scriptOptions[' + i + ']'] = executionOptions['scriptOptions'][i];
			}
			form.getForm().setValues(executionOptions);
		}
	}

	return form;
});
