Ext.define('Scalr.ui.ScriptEventView', {
	alias: 'widget.scripteventgrid',
	extend: 'Ext.grid.Panel',

	scalrModuleData: {},
	store: {
		fields: [ 'role_script_id', 'script_id', 'script_name', 'event_name', 'target', 'issync', 'timeout', 'version', 'params', { name: 'order_index', type: 'int' }],
		groupField: 'event_name',
		sorters: [ 'order_index' ],
		proxy: 'object'
	},
	plugins: {
		ptype: 'gridstore'
	},
	viewConfig: {
		emptyText: 'No scripts assigned to events',
		deferEmptyText: false
	},
	singleSelect: true,

	columns: [
		{ width: 30, sortable: false, xtype: 'templatecolumn', tpl:
			'<img src="' +
				'<tpl if="target != &quot;&quot;">/ui2/images/ui/farms/scripting/execution.png</tpl>' +
				'<tpl if="target == &quot;&quot;">/ui2/images/ui/farms/scripting/execution_disable.png</tpl>' +
			'">'
		},
		{ header: 'Script', flex: 1, sortable: true, dataIndex: 'script_name' },
		{ header: 'Event', flex: 1, sortable: true, hidden: true, dataIndex: 'event_name' },
		{ header: 'Order', width: 100, sortable: true, dataIndex: 'order_index' }
	],

	features: [
		Ext.create('Ext.grid.feature.Grouping', {
			groupHeaderTpl: 'Scripts on {name}'
		})
	],

	initComponent: function () {
		this.callParent(arguments);

		this.on('beforedeselect', function (view, record) {
			return this.up().down('scriptfield').scalrSaveRecord(record);
		});

		this.on('select', function (view, record) {
			this.up().down('scriptfield').scalrLoadRecord(record);
		});
	}
});

Ext.define('Scalr.ui.ScriptField', {
	alias: 'widget.scriptfield',
	extend: 'Ext.form.Panel',

	bodyCls: 'x-panel-body-frame',
	border: false,
	fieldDefaults: {
		labelWidth: 120,
		anchor: '100%'
	},
	autoScroll: true,
	scalrModuleStepStart: 0,
	scalrInternalRecord: '',

	initComponent: function () {
		this.callParent(arguments);

		this.down('#scriptAdd').down('[name="event_name"]').store.load({ data: this.scalrModuleData['events'] });
		this.down('#scriptAdd').down('[name="script_id"]').store.load({ data: this.scalrModuleData['scripts'] });

		Ext.each(this.down('#scriptEdit').query('[isFormField]'), function (field) {
			field.on('change', function (f, value) {
				if (this.up('scriptfield').scalrInternalRecord)
					this.up('scriptfield').scalrInternalRecord.set(f.name, value);
			});
		});
	},

	scalrLoadRecord: function (record) {
		this.scalrShow('edit');

		var swhen = this.down('#scriptAdd').down('[name="event_name"]').findRecord('id', record.get('event_name')),
			sdo = this.down('#scriptAdd').down('[name="script_id"]').findRecord('id', record.get('script_id'));

		this.down('#when').setValue(swhen ? swhen.get('name') : 'none');
		this.down('#do').setValue(sdo ? sdo.get('description') : 'none');

		var data = [ [ '', '-- DO NOT EXECUTE SCRIPT --' ], [ 'farm', 'All instances in the farm' ] ];
		if (record.get('event_name') != 'DNSZoneUpdated')
			data.push(['role', 'All instances of this role']);

		if (record.get('event_name') != 'HostDown' && record.get('event_name') != 'DNSZoneUpdated')
			data.push(['instance', 'That instance only']);

		var revisions = Scalr.utils.CloneObject(sdo.get('revisions'));
		for (var i in revisions)
			revisions[i]['revisionName'] = revisions[i]['revision'];

		var latestRev = Ext.Array.max(Object.keys(revisions), function (a, b) {
			return parseInt(a) > parseInt(b) ? 1 : -1;
		});

		revisions[0] = { revision: -1, revisionName: 'Latest', fields: revisions[latestRev]['fields'] };

		this.down('[name="target"]').store.load({ data: data});
		this.down('[name="version"]').store.load({ data: revisions });
		//this.down('[name="version"]').store.sort('revision', 'DESC');
		//this.down('[name="version"]').reset();

		if (record.get('version') == 'latest')
			record.set('version', -1);

		this.down('[name="version"]')['scalrScriptParams'] = record.get('params');

		this.getForm().loadRecord(record);
		this.scalrInternalRecord = record;
	},

	scalrSaveRecord: function (record) {
		if (this.getForm().isValid()) {
			var params = {};

			this.down('#scriptOptions').items.each(function (item) {
				params[item.paramName] = item.getValue();
			});
			this.down('#scriptOptions').removeAll();
			this.down('#scriptOptions').hide();
			record.set('params', params);

			this.getForm().updateRecord(record);
			this.scalrInternalRecord = '';

			this.scalrShow('create');
		} else {
			return false;
		}
	},

	scalrShow: function (p) {
		if (p == 'create') {
			this.down('#scriptEdit').hide().disable();
			this.down('#scriptEditButtons').hide();
			this.down('#scriptAdd').show().enable();
			this.getForm().setValues({
				script_id: '',
				event_name: ''
			});
			this.down('#scriptOptions').removeAll();
			this.down('#scriptOptions').hide();
			this.getForm().reset();
		} else {
			this.down('#scriptAdd').hide().disable();
			this.down('#scriptEdit').show().enable();
			this.down('#scriptEditButtons').show();
		}
	},

	dockedItems: [{
		xtype: 'toolbar',
		dock: 'top',
		items: [{
			ui: 'paging',
			iconCls: 'x-tbar-add',
			handler: function() {
				var sm = this.up('scriptfield').up().down('scripteventgrid').getSelectionModel();
				if (sm.hasSelection())
					sm.deselectAll();
				else
					this.up('scriptfield').down('#scriptAdd').show();
			}
		}]
	}],

	items: [{
		xtype: 'fieldset',
		itemId: 'scriptAdd',
		title: 'Add script',
		hidden: true,
		items: [{
			xtype: 'combo',
			labelWidth: 60,
			fieldLabel: 'Event',
			store: {
				fields: [ 'id', 'name' ],
				proxy: 'object'
			},
			emptyText: 'Select event',
			valueField: 'id',
			displayField: 'id',
			queryMode: 'local',
			editable: false,
			allowBlank: false,
			name: 'event_name',
			listConfig: {
				width: 'auto'
			}
		}, {
			xtype: 'combo',
			labelWidth: 60,
			fieldLabel: 'Script',
			emptyText: 'Select script',
			store: {
				fields: [ 'id', 'name', 'description', 'issync', 'timeout', 'revisions' ],
				proxy: 'object'
			},
			valueField: 'id',
			displayField: 'name',
			queryMode: 'local',
			editable: false,
			allowBlank: false,
			name: 'script_id',
			listConfig: {
				width: 'auto'
			}
		}, {
			xtype: 'button',
			text: 'Add',
			itemId: 'scriptAddButton',
			width: 80,
			handler: function () {
				if (this.up('scriptfield').getForm().isValid()) {
					var values = this.up('scriptfield').getForm().getValues(),
						data = this.up('scriptfield').down('[name="script_id"]').findRecordByValue(values.script_id).data,
						store = this.up('scriptfield').up().down('scripteventgrid').store,
						index = this.up('scriptfield').scalrModuleStepStart;

					store.each(function (rec) {
						var order = parseInt(rec.get('order_index'));
						if (rec.get('event_name') == values.event_name && order >= index)
							index = order + 10;
					});

					var newRec = {
						script_name: data['name'],
						event_name: values.event_name,
						target: '',
						script_id: values.script_id,
						timeout: data['timeout'],
						issync: data['issync'],
						version: -1,
						order_index: index
					};

					this.up('scriptfield').getForm().reset();
					var r = store.add(newRec)
					//this.up('scriptfield').up().down('scripteventgrid').getSelectionModel().select(store.add(newRec));
				}
			}
		}]
	}, {
		xtype: 'fieldset',
		hidden: true,
		disabled: true,
		itemId: 'scriptEdit',
		title: 'Script execution settings',
		items: [{
			xtype: 'displayfield',
			fieldLabel: 'When',
			itemId: 'when'
		}, {
			xtype: 'displayfield',
			fieldLabel: 'Do',
			itemId: 'do'
		}, {
			xtype: 'combo',
			store: [ [ '', '-- DO NOT EXECUTE SCRIPT --' ], ['instance', 'That instance only'], ['role', 'All instances of this role'], [ 'farm', 'All instances in the farm' ]],
			queryMode: 'local',
			editable: false,
			name: 'target',
			fieldLabel: 'Where'
		}, {
			xtype: 'combo',
			fieldLabel: 'Execution mode',
			store: [ ['1', 'Synchronous'], ['0', 'Asynchronous']],
			queryMode: 'local',
			editable: false,
			name: 'issync'
		}, {
			xtype: 'textfield',
			fieldLabel: 'Timeout',
			name: 'timeout',
			allowBlank: false,
			regex: /^[0-9]+$/
		}, {
			xtype: 'textfield',
			fieldLabel: 'Execution order',
			name: 'order_index',
			allowBlank: false,
			regex: /^[0-9]+$/
		}, {
			xtype: 'combo',
			store: {
				fields: [{ name: 'revision', type: 'int' }, 'revisionName', 'fields' ],
				proxy: 'object'
			},
			valueField: 'revision',
			displayField: 'revisionName',
			queryMode: 'local',
			editable: false,
			name: 'version',
			fieldLabel: 'Version',
			listeners: {
				change: function () {
					if (this.getValue()) {
						var record = this.store.findRecord('revision', this.getValue()),
							fields = record ? record.get('fields') || '' : '',
							fieldset = this.up().next();

						if (Ext.isObject(fields)) {
							var values = {};
							fieldset.items.each(function (item) {
								values[item.paramName] = item.getValue();
							});
							fieldset.show();
							fieldset.removeAll();

							for (var i in fields) {
								fieldset.add({
									xtype: 'textfield',
									fieldLabel: fields[i],
									paramName: i,
									value: values[i] || this['scalrScriptParams'][i] || ''
								});
							}
						} else {
							fieldset.removeAll();
							fieldset.hide();
						}
					}
				}
			}
		}]
	}, {
		xtype: 'fieldset',
		title: 'Script options',
		itemId: 'scriptOptions',
		hidden: true
	}, {
		xtype: 'fieldset',
		itemId: 'scriptEditButtons',
		hidden: true,
		layout: {
			type: 'hbox',
			pack: 'center'
		},
		items: [{
			xtype: 'button',
			text: '<span style="color:red">Remove</span>',
			width: 80,
			handler: function () {
				this.up('scriptfield').up().down('scripteventgrid').getStore().remove(this.up('scriptfield').scalrInternalRecord);
				this.up('scriptfield').scalrShow('create');
			}
		}]
	}]
});
