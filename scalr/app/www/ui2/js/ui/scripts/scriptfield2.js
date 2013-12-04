Ext.define('Scalr.ui.RoleScriptingPanel', {
	extend: 'Ext.container.Container',
	alias: 'widget.scriptfield2',
	
	layout: {
		type: 'hbox',
		align: 'stretch'
	},
	items: [{
		xtype: 'grid',
		hideHeaders: true,
		cls: 'x-panel-columned-leftcol x-grid-shadow x-grid-role-scripting x-grid-dark-focus',
		padding: 9,
		maxWidth: 500,
        flex: .6,
		multiSelect: true,
		selModel: {
			selType: 'selectedmodel',
			getVisibility: function(record) {
				return !record.get('system');
			}
		},
		features: [{
			ftype: 'rowbody',
			getAdditionalData: function(data, rowIndex, record, orig) {
				var name = '',
					target = record.get('target');
				switch (target) {
					case '':
						name = '<span>No target (no execution)</span>';
					break;
					case 'farm':
						name = 'on <span style="color:#2BAF23">all instances in the farm</span>';
					break;
					case 'instance':
						name = 'on <span style="color:#1582EE">triggering instance only</span>';
					break;
					case 'role':
					case 'roles':
						var roleIds = target == 'role' ? [this.grid.up('scriptfield2').farmRoleId] : (record.get('target_roles') || []),
							roles = [],
							rolesStore = this.grid.form.getForm().findField('target_roles').getStore();
							
						for (var i=0, len=roleIds.length; i<len; i++) {
							var res = rolesStore.query('farm_role_id', roleIds[i]);
							if (res.length){
								var rec = res.first();
								roles.push('<span style="color:#' + Scalr.utils.getColorById(rec.get('farm_role_id'))+'">' + rec.get('name') + '</span>');
							}
						}
						name = roles.length ? 'on <span><i>' + roles.join(', ') + '</i></span>' : '&nbsp;';
					break;
					case 'behaviors':
						var bahaviorIds = record.get('target_behaviors') || [],
							behaviors = [],
							behaviorsStore = this.grid.form.getForm().findField('target_behaviors').getStore();
							
						for (var i=0, len=bahaviorIds.length; i<len; i++) {
							var res = behaviorsStore.query('id', bahaviorIds[i]);
							if (res.length){
								var rec = res.first();
								behaviors.push(rec.get('name'));
							}
						}
						name = behaviors.length ? 'on all <span><i>' + behaviors.join(', ') + '</i></span> roles' : '&nbsp;';
					break;
				}
					
				return {
					rowBody: '<span style="float:left;width:44px;margin-right:7px;text-align:center;font-size:90%;font-weight:bold;">(' + (record.get('issync') == 1 ? 'sync' : 'async') + ')</span><div style="margin:0 51px 5px">'+name+'</div>',
					rowBodyColspan: this.view.headerCt.getColumnCount()
				};
			}
		},{
			ftype: 'rowwrap'
		},{
			id:'grouping',
			ftype:'grouping',
			groupHeaderTpl: [
				'<span style="font-weight:normal">On <span style="font-weight:bold">{name:this.formatName}</span> perform:</span>',
				{
					formatName: function(value) {
						return value == "*" ? "All events" : value;
					}
				}
			]
		}],
		store: {
			fields: [ 'script_id', 'script', 'event', 'target', 'target_roles', 'target_behaviors', 'issync', 'timeout', 'version', 'params', {name: 'order_index', type: 'int'}, 'system', 'role_script_id', 'hash' ],
			filterOnLoad: true,
			sortOnLoad: true,
			sorters: ['order_index'],
			proxy: 'object',
			groupField: 'event'
		},
		plugins: [{
			ptype: 'rowpointer',
			addOffset: 8,
			addCls: 'x-panel-columned-row-pointer-light'
		}],
		listeners: {
			viewready: function() {
				var me = this;
                me.form = me.up('scriptfield2').down('form');
				me.down('#scriptsLiveSearch').store = me.store;
				me.getSelectionModel().on('focuschange', function(gridSelModel){
					if (!me.disableOnFocusChange) {
						if (gridSelModel.lastFocused) {
							if (gridSelModel.lastFocused != me.form.getRecord()) {
								me.form.loadRecord(gridSelModel.lastFocused);
							}
						} else {
							me.form.deselectRecord();
						}
					}
				});
			},
			selectionchange: function(selModel, selected) {
				this.down('#delete').setDisabled(!selected.length);
			}
		},
		viewConfig: {
			plugins: {
				ptype: 'dynemptytext',
				emptyText: '<div class="title">No rules were found to match your search.</div> Try modifying your search criteria or <a class="add-link" href="#">creating a new orchestration&nbsp;rule</a>.',
				emptyTextNoItems: 'Click on the <a class="add-link" href="#"><img src="'+Ext.BLANK_IMAGE_URL+'" class="scalr-ui-action-icon scalr-ui-action-icon-add" /></a> button above to create your first orchestration&nbsp;rule',
				onAddItemClick: function() {
					this.client.ownerCt.down('#add').handler();
				}
			},
			loadingText: 'Loading scripts ...',
			deferEmptyText: false,
			overflowY: 'auto',
			overflowX: 'hidden',
			getRowClass: function(record){
				var cls = [];
				if (record.get('system')) {
					cls.push('x-grid-row-system');
				}
				return cls.join(' ');
			}
		},

		columns: [{
			flex: 1, 
			dataIndex: 'order_index',
			renderer: function(val, meta, record, rowIndex, colIndex, store) {
				if (record.dirty) {
					meta.tdCls += ' x-grid-dirty-cell';
				}
				return '<span style="float:left;width:40px;font-size:90%;">#'+record.get('order_index')+'</span> <b>'+record.get('script')+'</b>';
			}
		}],
		dockedItems: [{
			cls: 'x-toolbar',
			dock: 'top',
			layout: 'hbox',
			defaults: {
				margin: '0 0 0 10'
			},
			items: [{
				xtype: 'livesearch',
				itemId: 'scriptsLiveSearch',
				margin: 0,
				fields: ['script'],
				listeners: {
					afterfilter: function(){
						//workaround of the extjs grouped store/grid bug
						var grid = this.up('grid'),
							grouping = grid.getView().getFeature('grouping');
						grid.disableOnFocusChange = true;
						grid.suspendLayouts();
						grouping.disable();
						grouping.enable();
						grid.resumeLayouts(true);
						grid.disableOnFocusChange = false;
					}
				}
			},{
				xtype: 'tbfill' 
			},{
				itemId: 'delete',
				xtype: 'button',
				iconCls: 'x-btn-groupacton-delete',
				ui: 'action-dark',
				disabled: true,
				tooltip: 'Delete selected scripts',
				handler: function() {
					var grid = this.up('grid'),
						selection = grid.getSelectionModel().getSelection();
					//are we going to ask for a confirmation here?
					/*Scalr.Confirm({
						type: 'delete',
						msg: 'Delete selected ' + selection.length + ' script(s)?',
						success: function (data) {*/
							grid.suspendLayouts();
							grid.getStore().remove(selection);
							grid.resumeLayouts(true);
						/*}
					});*/
				}
			},{
				itemId: 'add',
				xtype: 'button',
				iconCls: 'x-btn-groupacton-add',
				ui: 'action-dark',
				tooltip: 'Add script',
				handler: function() {
					var grid = this.up('grid');
					grid.getSelectionModel().setLastFocused(null);
					grid.form.loadRecord(grid.getStore().createModel({issync: '1', order_index: 10}));
				}
			}]
		}]
	}, {
        xtype: 'container',
        layout: 'fit',
        flex: 1,
        style: 'background: #F0F1F4;border-radius:0 4px 4px 0',
        margin: 0,
        items: {
            xtype: 'form',

            margin: 0,

            hidden: true,
            overflowY: 'auto',
            items: [{
                xtype: 'fieldset',
                margin: 0,
                defaults: {
                    anchor: '100%',
                    maxWidth: 700,
                    labelWidth: 120
                },
                items: [{
                    xtype: 'container',
                    cls: 'x-fieldset-subheader',
                    layout: {
                        type: 'hbox',
                        align: 'left'
                    },
                    items: [{
                        xtype: 'label',
                        html: 'Trigger'
                    }, {
                        xtype: 'displayinfofield',
                        margin: '0 0 0 10',
                        info: 'Trigger description.'
                    }]
                },{
                    xtype: 'combo',
                    store: {
                        fields: [
                            'id', 
                            {
                                name: 'title', 
                                convert: function(v, record){
                                    return record.get('id')=='*' ? 'All Events' : record.get('id');
                                }
                            },
                            'name'
                        ],
                        proxy: 'object'
                    },
                    valueField: 'id',
                    displayField: 'title',
                    queryMode: 'local',
                    allowBlank: false,
                    validateOnChange: false,
                    editable: false,
                    itemId: 'event',
                    name: 'event',
                    emptyText: 'Please select trigger',
                    listConfig: {
                        cls: 'x-boundlist-role-scripting-events',
                        style: 'white-space:nowrap',
                        getInnerTpl: function(displayField) {
                            return '<tpl if=\'id == \"*\"\'>All Events<tpl else>{id} <span style="color:#999">({name})</span></tpl>';
                        }
                    },
                    listeners: {
                        change: function(comp, value) {
                            var formPanel = this.up('form'),
                                form = formPanel.getForm(),
                                record = formPanel.getRecord(),
                                scriptRecord = comp.findRecordByValue(value),
                                disableFields = function(fields) {
                                    for (var i=0, len=fields.length; i<len; i++) {
                                        var field = formPanel.down(fields[i]);
                                        if (field.getValue()) {
                                            formPanel.down('#targetDoNotExecute').setValue(true);
                                        }
                                        field.disable();
                                    }
                                };
                            formPanel.suspendLayouts();
                            if (value) {
                                var c = formPanel.query('component[hideOn~=x-empty-trigger-hide]');
                                for (var i=0, len=c.length; i<len; i++) {
                                    c[i].setVisible(true);
                                }
                                if (record.store === undefined) {
                                    form.findField('order_index').setValue(comp.up('scriptfield2').getNextOrderIndexForEvent(value));
                                }
                            }
                            formPanel.savedScrollTop = formPanel.body.getScroll().top;
                            formPanel.updateRecordSuspended++;
                            switch (value) {
                                case 'HostDown':
                                    disableFields(['#targetInstance']);
                                break;
                                default:
                                    formPanel.down('#targetRoles').enable();
                                    formPanel.down('#targetInstance').enable();
                                break;
                            }
                            comp.next().update(scriptRecord ? scriptRecord.get('name') : '&nbsp;');
                            formPanel.body.scrollTo('top', formPanel.savedScrollTop);

                            formPanel.updateRecordSuspended--;
                            formPanel.updateRecord(['event', 'target', 'target_roles']);
                            formPanel.resumeLayouts(true);
                        }
                    }
                }, {
                    xtype: 'container',
                    itemId: 'eventDescription',
                    style: 'color:#666',
                    html: '&nbsp;'
                }, {
                    xtype: 'component',
                    cls: 'x-fieldset-delimiter-large',
                    maxWidth: null
                },{
                    xtype: 'container',
                    cls: 'x-fieldset-subheader',
                    hideOn: 'x-empty-trigger-hide',
                    layout: {
                        type: 'hbox',
                        align: 'left'
                    },
                    items: [{
                        xtype: 'label',
                        html: 'Action'
                    }, {
                        xtype: 'displayinfofield',
                        margin: '0 0 0 10',
                        info: 'Action description.'
                    }]
                },{
                    xtype: 'container',
                    hideOn: 'x-empty-trigger-hide',
                    layout: {
                        type: 'hbox',
                        align: 'stretch'
                    },
                    items: [{
                        xtype: 'combo',
                        fieldLabel: 'Script',
                        store: {
                            fields: [ 'id', 'name', 'description', 'issync', 'timeout', 'revisions' ],
                            proxy: 'object'
                        },
                        valueField: 'id',
                        displayField: 'name',
                        queryMode: 'local',
                        editable: true,
                        allowBlank: false,
                        validateOnChange: false,
                        forceSelection: true,
                        itemId: 'script',
                        name: 'script_id',
                        flex: 1,
                        labelWidth: 110,
                        emptyText: 'Please select script',
                        anyMatch: true,
                        listeners: {
                            change: function(comp, value) {
                                var scriptRecord = comp.findRecordByValue(value),
                                    formPanel = comp.up('form'),
                                    form = formPanel.getForm(),
                                    versionField = form.findField('version');
                                formPanel.updateRecordSuspended++;
                                formPanel.suspendLayouts();
                                versionField.getStore().removeAll();
                                if (value) {
                                    var c = formPanel.query('component[hideOn~=x-empty-action-hide]');
                                    for (var i=0, len=c.length; i<len; i++) {
                                        c[i].setVisible(true);
                                    }
                                }

                                if (scriptRecord) {
                                    form.findField('script').setValue(scriptRecord.get('name'));
                                    var	revisions = Scalr.utils.CloneObject(scriptRecord.get('revisions'));

                                    //load script revisions
                                    for (var i in revisions) {
                                        revisions[i]['revisionName'] = revisions[i]['revision'];
                                    }
                                    var latestRev = Ext.Array.max(Object.keys(revisions), function (a, b) {
                                        return parseInt(a) > parseInt(b) ? 1 : -1;
                                    });

                                    revisions.splice(0, 0, { revision: -1, revisionName: 'Latest', fields: revisions[latestRev]['fields'] });
                                    versionField.getStore().load({data: revisions});

                                    versionField.reset();
                                    versionField.setValue('-1');

                                    form.findField('target').setValue(scriptRecord.get('target'));
                                    form.findField('issync').setValue(scriptRecord.get('issync') || '0');
                                    form.findField('timeout').setValue(scriptRecord.get('timeout'));

                                    var order_index = form.findField('order_index').getValue();
                                    form.findField('order_index').setValue(order_index > 0 ? order_index : comp.up('scriptfield2').getNextOrderIndexForEvent(form.findField('event').getValue()));
                                }
                                formPanel.updateRecordSuspended--;
                                formPanel.updateRecord(['script_id', 'script', 'version', 'target', 'issync', 'timeout', 'order_index']);
                                formPanel.resumeLayouts(true);
                            }
                        }
                    }, {
                        xtype: 'hiddenfield',
                        name: 'script'
                    }, {
                        xtype: 'combo',
                        fieldLabel: 'Version',
                        store: {
                            fields: [{ name: 'revision', type: 'string' }, 'revisionName', 'fields' ],
                            proxy: 'object'
                        },
                        valueField: 'revision',
                        displayField: 'revisionName',
                        queryMode: 'local',
                        editable: false,
                        name: 'version',
                        width: 140,
                        labelWidth: 50,
                        margin: '0 0 0 20',
                        listeners: {
                            change: function (comp, value) {
                                var formPanel = this.up('form'),
                                    scriptParams = formPanel.down('#scripting_edit_parameters'),
                                    getParamValues = function(){
                                        var res = {};
                                        scriptParams.items.each(function(){
                                            res[this.paramName] = this.getValue();
                                        })
                                        return res;
                                    };

                                formPanel.updateRecordSuspended++;
                                formPanel.savedScrollTop = formPanel.body.getScroll().top;
                                formPanel.down('#scriptParamsDelimiter').hide();
                                formPanel.down('#scriptParamsWrapper').hide();
                                formPanel.suspendLayouts();
                                if (value) {
                                    var revisionRecord = this.findRecord('revision', value),
                                        fields = revisionRecord ? revisionRecord.get('fields') : null;
                                    if (Ext.isObject(fields)) {
                                        var record = formPanel.getForm().getRecord(),
                                            values = formPanel.isLoading && record ? record.get('params') : getParamValues();

                                        scriptParams.removeAll();
                                        formPanel.removeScriptParams();
                                        for (var i in fields) {
                                            formPanel.updateScriptParam(i, values[i] || '');
                                            scriptParams.add({
                                                xtype: 'textfield',
                                                fieldLabel: fields[i],
                                                isScriptParamField: true,
                                                paramName: i,
                                                value: values[i] || '',
                                                submitValue: false,
                                                listeners: {
                                                    change: function(comp, value) {
                                                        formPanel.updateScriptParam(comp.paramName, value);
                                                    }
                                                }
                                            });
                                        }
                                        formPanel.down('#scriptParamsDelimiter').show();
                                        formPanel.down('#scriptParamsWrapper').show();
                                    } else {
                                        scriptParams.removeAll();
                                        formPanel.removeScriptParams();
                                    }
                                } else {
                                    formPanel.removeScriptParams();
                                    scriptParams.removeAll();
                                }
                                formPanel.resumeLayouts(true);
                                formPanel.body.scrollTo('top', formPanel.savedScrollTop);
                                formPanel.updateRecordSuspended--;
                                formPanel.updateRecord(['version']);

                            }
                        }
                    }]
                },{
                    xtype: 'container',
                    hideOn: 'x-empty-trigger-hide',
                    margin: '8 0 12 0',
                    layout: {
                        type: 'hbox',
                        align: 'middle'
                    },
                    items: [{
                        xtype: 'buttongroupfield',
                        fieldLabel: 'Execution mode',
                        editable: false,
                        name: 'issync',
                        labelWidth: 110,
                        items: [{
                            text: 'Synchronous',
                            value: '1'
                        },{
                            text: 'Asynchronous',
                            value: '0'
                        }],
                        listeners: {
                            change: function (comp, value) {
                                var formPanel = comp.up('form');
                                formPanel.updateRecord(['issync']);
                            }
                        }
                    },{xtype: 'tbfill'},{
                        xtype: 'textfield',
                        fieldLabel: 'Timeout',
                        name: 'timeout',
                        allowBlank: false,
                        validateOnChange: false,
                        regex: /^[0-9]+$/,
                        width: 115,
                        labelWidth: 50,
                        margin: '0 5 0 10',
                        listeners: {
                            change: function (comp, value) {
                                var formPanel = comp.up('form');
                                formPanel.updateRecord(['timeout']);
                            }
                        }
                    },{
                        xtype: 'label',
                        html: 'sec'
                    }]
                }, {
                    xtype: 'textfield',
                    hideOn: 'x-empty-trigger-hide',
                    fieldLabel: 'Order',
                    name: 'order_index',
                    allowBlank: false,
                    validateOnChange: false,
                    regex: /^[0-9]+$/,
                    maxWidth: 200,
                    labelWidth: 110,
                    listeners: {
                        change: function (comp, value) {
                            var formPanel = comp.up('form');
                            formPanel.updateRecord(['order_index']);
                        }
                    }
                }, {
                    xtype: 'component',
                    hideOn: 'x-empty-trigger-hide',
                    cls: 'x-fieldset-delimiter-large',
                    maxWidth: null
                }, {
                    xtype: 'container',
                    cls: 'x-fieldset-subheader',
                    hideOn: 'x-empty-action-hide',
                    layout: {
                        type: 'hbox',
                        align: 'left'
                    },
                    items: [{
                        xtype: 'label',
                        html: 'Target'
                    }, {
                        xtype: 'displayinfofield',
                        margin: '0 0 0 10',
                        info: 'Target description.'
                    }]
                },{
                    xtype: 'fieldcontainer',
                    hideOn: 'x-empty-action-hide',
                    defaults: {
                        listeners: {
                            change: function(comp, checked) {
                                if (checked) {
                                    var formPanel = comp.up('form');
                                    formPanel.down('#targetRolesList').setDisabled(true);
                                    formPanel.down('#targetBehaviorsList').setDisabled(true);
                                    formPanel.updateRecord([{name: 'target', value: comp.inputValue}, 'target_roles']);
                                }
                            }
                        }
                    },
                    items: [{
                        xtype: 'radio',
                        name: 'target',
                        itemId: 'targetDoNotExecute',
                        inputValue: '',
                        boxLabel: 'No target (no execution)'
                    },{
                        xtype: 'radio',
                        margin: '6 0 10 0',
                        name: 'target',
                        itemId: 'targetInstance',
                        inputValue: 'instance',
                        boxLabel: 'Triggering instance only'
                    },{
                        xtype: 'container',
                        margin: '6 0 0',
                        layout: {
                            type: 'column'
                        },
                        items: [{
                            xtype: 'radio',
                            name: 'target',
                            itemId: 'targetRoles',
                            inputValue: 'roles',
                            boxLabel: 'Selected roles:',
                            fieldBodyCls: 'x-form-cb-wrap-top',
                            width: 160,
                            listeners: {
                                change: function(comp, checked) {
                                    if (checked) {
                                        var formPanel = comp.up('form');
                                        formPanel.down('#targetRolesList').setDisabled(false);
                                        formPanel.down('#targetBehaviorsList').setDisabled(true);
                                        formPanel.updateRecord([{name: 'target', value: comp.inputValue}, 'target_roles']);
                                    }
                                }
                            }

                        },{
                            xtype: 'comboboxselect',
                            itemId: 'targetRolesList',
                            name: 'target_roles',
                            displayField: 'title',
                            valueField: 'farm_role_id',
                            //allowBlank: false,
                            //validateOnChange: false,
                            columnWidth: 1,
                            queryMode: 'local',
                            store: {
                                fields: ['farm_role_id', 'platform', 'cloud_location', 'role_id',  'name', {name: 'title', convert: function(v, rec){return '<span style=\'color:#' + Scalr.utils.getColorById(rec.data.farm_role_id)+'\'>' + rec.data.name + '</span> (' + rec.data.cloud_location +')'}}],
                                proxy: 'object'
                            },
                            flex: 1,
                            listeners: {
                                change: function(){//prevent form scroll top reset after change comboboxselect value
                                    var formPanel = this.up('form');
                                    formPanel.savedScrollTop = formPanel.body.getScroll().top;
                                    formPanel.on('afterlayout', function(){
                                        if (this.savedScrollTop) {
                                            this.body.scrollTo('top', this.savedScrollTop);
                                        }
                                    }, formPanel, {single: true});

                                    formPanel.updateRecord(['target', 'target_roles']);

                                }
                            }

                        }]
                    },{
                        xtype: 'container',
                        margin: '6 0 0',
                        layout: {
                            type: 'column'
                        },
                        items: [{
                            xtype: 'radio',
                            name: 'target',
                            itemId: 'targetBehaviors',
                            inputValue: 'behaviors',
                            boxLabel: 'Selected behaviors:',
                            fieldBodyCls: 'x-form-cb-wrap-top',
                            width: 160,
                            listeners: {
                                change: function(comp, checked) {
                                    if (checked) {
                                        var formPanel = comp.up('form');
                                        formPanel.down('#targetRolesList').setDisabled(true);
                                        formPanel.down('#targetBehaviorsList').setDisabled(false);
                                        formPanel.updateRecord([{name: 'target', value: comp.inputValue}, 'target_behaviors']);
                                    }
                                }
                            }

                        },{
                            xtype: 'comboboxselect',
                            itemId: 'targetBehaviorsList',
                            name: 'target_behaviors',
                            displayField: 'name',
                            valueField: 'id',
                            //allowBlank: false,
                            //validateOnChange: false,
                            columnWidth: 1,
                            queryMode: 'local',
                            store: {
                                fields: ['id', 'name'],
                                proxy: 'object'
                            },
                            flex: 1,
                            listeners: {
                                change: function(){//prevent form scroll top reset after change comboboxselect value
                                    var formPanel = this.up('form');
                                    formPanel.savedScrollTop = formPanel.body.getScroll().top;
                                    formPanel.on('afterlayout', function(){
                                        if (this.savedScrollTop) {
                                            this.body.scrollTo('top', this.savedScrollTop);
                                        }
                                    }, formPanel, {single: true});

                                    formPanel.updateRecord(['target', 'target_behaviors']);

                                }
                            }

                        }]
                    },{
                        xtype: 'radio',
                        name: 'target',
                        inputValue: 'farm',
                        itemId: 'targetFarm',
                        boxLabel: 'All instances in the farm',
                        margin: '6 0 0'
                    }]
                },{
                    xtype: 'component',
                    cls: 'x-fieldset-delimiter-large',
                    itemId: 'scriptParamsDelimiter',
                    maxWidth: null
                },{
                    xtype: 'fieldcontainer',
                    itemId: 'scriptParamsWrapper',
                    items: [{
                        xtype: 'container',
                        cls: 'x-fieldset-subheader',
                        maxWidth: 700,
                        layout: {
                            type: 'hbox',
                            align: 'left'
                        },
                        items: [{
                            xtype: 'label',
                            html: 'Script parameters'
                        }, {
                            xtype: 'displayinfofield',
                            margin: '0 0 0 10',
                            info: 'Script parameters description.'
                        }]
                    },{
                        maxWidth: 700,
                        xtype: 'container',
                        itemId: 'scripting_edit_parameters',
                        layout: 'anchor',
                        defaults: {
                            labelWidth: 120,
                            anchor: '100%'
                        }
                    }],
                    hidden: true
                }]
            }],
            listeners: {
                boxready: function() {
                    this.grid = this.up('scriptfield2').down('grid');
                },
                beforeloadrecord: function(record) {
                    var form = this.getForm();
                    this.isLoading = true;
                    form.reset(true);
                    if (record.get('version') == 'latest') {
                        record.set('version', '-1');
                    }

                    if (record.get('target') == 'role') {
                        record.set('target', 'roles');
                        record.set('target_roles', [this.up('scriptfield2').farmRoleId]);
                    }

                },
                loadrecord: function(record) {
                    var isNewRecord = !record.store,
                        form = this.getForm();

                    form.getFields().each(function(){
                        if (!this.isScriptParamField) {
                            this.setReadOnly(record.get('system'));
                        }
                    });
                    if (isNewRecord || record.get('target') != 'roles') {
                        form.findField('target_roles').setValue([this.up('scriptfield2').farmRoleId]);
                    }
                    form.clearInvalid();
                    if (!this.isVisible()) {
                        this.setVisible(true);
                        this.ownerCt.updateLayout();//this is required in extjs 4.1 to recalculate form dimensions after container size was changed, while form was hidden
                    }

                    var c = this.query('component[hideOn~=x-empty-trigger-hide], component[hideOn~=x-empty-action-hide]');
                    for (var i=0, len=c.length; i<len; i++) {
                        c[i].setVisible(!isNewRecord);
                    }

                    this.isLoading = false;
                }
            },

            updateRecordSuspended: 0,

            deselectRecord: function() {
                var form = this.getForm();
                this.setVisible(false);
                this.isLoading = true;
                form.reset();
                if (form._record) {
                    delete form._record;//todo: replace with .getForm().reset(true) in latest extjs
                }
                this.isLoading = false;

            },

            removeScriptParams: function(){
                var record = this.getRecord();
                if (!this.isLoading && record) {
                    record.set('params', {});
                }
            },

            updateScriptParam: function(name, value) {
                var form = this.getForm(),
                    record = this.getRecord();
                if (this.isLoading) {// || this.updateRecordSuspended
                    return;
                }
                if (record) {
                    var versionField = form.findField('version'),
                        revisionRecord = versionField.findRecord('revision', versionField.getValue()),
                        fields = revisionRecord ? revisionRecord.get('fields') : null;
                    if (fields && fields[name]) {
                        var params = record.get('params');
                        params = Ext.isEmpty(params) ? {} : params;
                        if (!Ext.isEmpty(fields[name])) {
                            params[name] = value;
                        } else if (params[name]){
                            delete params[name];
                        }
                        record.set('params', params);
                    }

                }
            },

            updateRecord: function(fields) {
                var form = this.getForm(),
                    record = this.getRecord(),
                    values = {};

                if (this.isLoading || this.updateRecordSuspended || (record && record.get('system'))) {
                    return;
                }
                if (record && record.store === undefined) {
                    if (!form.hasInvalidField()) {
                        values = form.getValues();
                    }
                } else {
                    for (var i=0, len=fields.length; i<len; i++) {
                        if (Ext.typeOf(fields[i]) == 'object') {
                            values[fields[i].name] = fields[i].value;
                        } else {
                            var field = form.findField(fields[i]);
                            if (field.isValid()) {
                                values[fields[i]] = field[fields[i]=='target' ? 'getGroupValue' : 'getValue']();
                            }
                        }
                    }
                }

                if (values.target && values.target_roles) {
                    if (values.target == 'roles' && values.target_roles.length == 0) {
                        values.target = '';
                    }
                }

                if (Ext.Object.getSize(values) && record) {
                    var scrollTop = this.grid.getView().el.getScroll().top;
                    this.grid.disableOnFocusChange = true;
                    this.grid.suspendLayouts();
                    this.grid.getView().getFeature('grouping').disable();
                    record.set(values);
                    if (record.store === undefined) {
                        this.grid.getStore().add(record);
                        this.grid.getSelectionModel().setLastFocused(record);
                    }
                    this.grid.getView().getFeature('grouping').enable();
                    this.grid.disableOnFocusChange = false;
                    this.grid.resumeLayouts(true);
                    this.grid.getView().el.scrollTo('top', scrollTop);
                    this.grid.getView().focusRow(record);
                }

            }
        }
	}],

	setCurrentRole: function(role) {
		this.farmRoleId = role.get('farm_role_id');
		this.farmRoleId = Ext.isEmpty(this.farmRoleId) ? '*self*' : this.farmRoleId;
	},
			
	loadRoleScripts: function(data) {
		this.down('grid').getStore().load({data: data});
	},

	clearRoleScripts: function(data) {
		this.down('grid').getStore().removeAll();
		this.down('#scriptsLiveSearch').reset();
		this.down('form').deselectRecord();
	},

	getRoleScripts: function(data) {
		var store = this.down('grid').getStore();
		return store.snapshot || store.data;
	},

	loadScripts: function(data) {
		this.down('#script').getStore().load({data: data});
	},

	loadEvents: function(data) {
		var events = {'*': 'All events'};
		Ext.apply(events, data);
		this.down('#event').getStore().load({data: events});
	},
	
	loadBehaviors: function(data) {
		this.down('#targetBehaviorsList').getStore().load({data: data});
	},

	loadRoles: function(data) {
		var roles = [];
		for (var i=0, len=data.length; i<len; i++) {
			if (!Ext.isEmpty(data[i].farm_role_id) || data[i].current) {
				data[i].farm_role_id = Ext.isEmpty(data[i].farm_role_id) ? '*self*' : data[i].farm_role_id;
				roles.push(data[i]);
			}
		}

		this.down('#targetRolesList').getStore().load({data: roles});
	},
	
	getNextOrderIndexForEvent: function(eventName) {
		var index = 10;
		this.getRoleScripts().each(function(){
			var curIndex = this.get('order_index');
			if (this.get('event') == eventName && curIndex >= index) {
				index = Math.floor(curIndex/10)*10 + 10;
			}
		});
		return index;
	}
	

});
