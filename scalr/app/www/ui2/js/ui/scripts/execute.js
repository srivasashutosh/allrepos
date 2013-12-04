Scalr.regPage('Scalr.ui.scripts.execute', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 900,
		title: 'Execute script',
		fieldDefaults: {
			anchor: '100%'
		},

		items: [{
			xtype: 'farmroles',
			title: 'Execution target',
			itemId: 'executionTarget',
			params: moduleParams['farmWidget']
		}, {
			xtype: 'fieldset',
			title: 'Execution options',
			labelWidth: 100,
			fieldDefaults: {
				width: 150
			},
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
					change: function () {
						var f = form.down('[name="scriptId"]'), r = f.store.findRecord('id', f.getValue(), 0, false, false, true), fR = form.down('[name="scriptVersion"]');

						fR.setValue('');

						if (r) {
							fR.store.loadData(r.get('revisions'));
							fR.store.sort('revision', 'DESC');
							fR.store.insert(0, { revision: -1, revisionName: 'Latest', fields: fR.store.first().get('fields') });

							if (!moduleParams['eventName']) {
								fR.setValue(fR.store.first().get('revision'));

								form.down('[name="scriptTimeout"]').setValue(r.get('timeout'));
								form.down('[name="scriptIsSync"]').setValue(r.get('issync'));
							}
						}
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
						var fieldset = form.down('#scriptOptions');
						if (! value) {
							fieldset.removeAll();
							fieldset.hide();
							return;
						}
						var r = field.store.findRecord('revision', value, 0, false, false, true);
						if (!r)
							return;

						var fields = r.get('fields');

						fieldset.removeAll();
						if (Ext.isObject(fields)) {
							for (var i in fields) {
								fieldset.add({
									xtype: 'textfield',
									fieldLabel: fields[i],
									name: 'scriptOptions[' + i + ']',
									value: moduleParams['scriptOptions'] ? moduleParams['scriptOptions'][i] : ''
								});
							}
							fieldset.show();
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
				anchor: '100%'
			}
		}, {
			xtype: 'fieldset',
			title: 'Additional settings',
			labelWidth: 100,
			items: [{
				xtype: 'checkbox',
				hideLabel: true,
				boxLabel: 'Add a shortcut in Options menu for roles. It will allow me to execute this script with the above parameters with a single click.',
				name: 'createMenuLink',
				inputValue: 1,
				checked: loadParams['isShortcut'],
				disabled: loadParams['isShortcut'] || loadParams['eventName']
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
				text: 'Save',
				hidden: !loadParams['isShortcut'],
				handler: function () {
                    if (form.getForm().isValid())
                        Scalr.Request({
                            processBox: {
                                type: 'action'
                            },
                            url: '/scripts/xExecute/',
                            form: form.getForm(),
                            success: function () {
                                Scalr.event.fireEvent('close');
                            }
                        });
				}
			}, {
				xtype: 'splitbutton',
				text: 'Execute',
				hidden: !!loadParams['isShortcut'],
				handler: function () {
					Scalr.message.Flush(true);
                    if (form.getForm().isValid())
                        Scalr.Request({
                            processBox: {
                                type: 'action'
                            },
                            url: '/scripts/xExecute/',
                            form: form.getForm(),
                            success: function () {
                                Scalr.event.fireEvent('close');
                            }
                        });
				},
				menu: [{
					text: 'Execute script and stay on this page',
					handler: function () {
                        if (form.getForm().isValid())
                            Scalr.Request({
                                processBox: {
                                    type: 'action'
                                },
                                url: '/scripts/xExecute/',
                                form: form.getForm(),
                                success: function () {

                                }
                            });
					}
				}],
				listeners: {
					menushow: function () {
						Scalr.message.Flush(true);
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

	if (moduleParams) {
		for (var i in moduleParams) {
			if (! moduleParams[i])
				delete moduleParams[i];
		}

		form.getForm().setValues(moduleParams);
	}

	return form;
});
