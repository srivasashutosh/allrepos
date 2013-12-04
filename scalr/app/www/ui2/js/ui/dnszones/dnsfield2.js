Ext.define('Scalr.ui.DnsRecordsField',{
	extend: 'Ext.grid.Panel',	
	mixins: {
		//field: 'Ext.form.field.Field'
	},
	alias: 'widget.dnsrecords',
	stores: {own: null, system: null},
	zone: {
		domainName: ''
	},
	selType: 'selectedmodel',
	plugins: {
		ptype: 'dnsrowediting',
		pluginId: 'rowediting'
	},
	columns: [{
		text: 'Domain', 
		flex: 1, 
		dataIndex: 'name', 
		sortable: true, 
		editor: {
			xtype: 'textfield',
			emptyText: 'Domain'
		}
	},{
		text: 'TTL',
		width: 60,
		dataIndex: 'ttl', 
		sortable: true, 
		resizable: false,
		editor: {
			xtype: 'textfield',
			emptyText: 'TTL'
		}
	},{
		text: 'Type', 
		dataIndex: 'type', 
		sortable: true, 
		resizable: false,
		width: 90,
		editor: {
			xtype: 'combo',
			store: [ 'A', 'CNAME', 'MX', 'TXT', 'NS', 'SRV'],
			editable: false,
			flex: 1.3,
			emptyText: 'Type',
			listeners: {
				change: function () {
					var field = this.up('panel'), value = this.getValue();
					field.down('#port').hide();
					field.down('#weight').hide();
					field.down('#priority').hide();

					if (value == 'MX' || value == 'SRV') {
						field.down('#priority').show();
					}
					
					if (value == 'SRV') {
						field.down('#weight').show();
						field.down('#port').show();
					}
					//field.down('#value').focus(true, 200);
				}
			}
		}
	},{
		text: 'Value', 
		flex: 1,
		minWidth: 280, 
		dataIndex: 'value', 
		sortable: true, 
		resizable: false,
		xtype: 'templatecolumn', 
		tpl: '<tpl if="type == \'SRV\'">'+
				'{value} (priority: {priority}, weight: {weight}, port: {port})'+
			 '<tpl elseif="type == \'MX\'">'+
				'{value} (priority: {priority})'+
			 '<tpl else>'+
				'{value}'+
			 '</tpl>'
			,
		editor: {
			xtype: 'container',
			layout: 'hbox',
			getValue: function() {
				return null;
			},
			items: [{
				xtype: 'textfield',
				itemId: 'priority',
				name: 'priority',
				emptyText: 'priority',
				hidden: true,
				flex: 1,
				maxWidth: 60,
				margin: '0 0 0 5'
			}, {
				xtype: 'textfield' ,
				itemId: 'weight',
				name: 'weight',
				emptyText: 'weight',
				hidden: true,
				flex: 1,
				maxWidth: 60,
				margin: '0 0 0 5'
			}, {
				xtype: 'textfield',
				itemId: 'port',
				name: 'port',
				emptyText: 'port',
				hidden: true,
				flex: 1,
				maxWidth: 60,
				margin: '0 0 0 5'
			},{
				xtype: 'textfield',
				itemId: 'value',
				name: 'value',
				emptyText: 'value',
				flex: 3,
				margin: '0 0 0 5'
			}]
		}
	}],
	
	dockedItems: [{
		dock: 'top',
		layout: 'hbox',
		items: [{
			xtype: 'livesearch',
			itemId: 'livesearch',
			margin: 0,
			submitValue: false,
			fields: ['name', 'value']
		},{
			xtype: 'buttongroupfield',
			itemId: 'type',
			value: 'own',
			hidden: true,
			submitValue: false,
			margin: '0 0 0 24',
			defaults: {
				width: 90
			},
			items: [{
				text: 'Custom',
				value: 'own'
			},{
				text: 'System',
				value: 'system'
			}],
			listeners: {
				change: function(comp, value){
					this.up('grid').fireEvent('changetype', value);
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
			margin: '0 10 0 0',
			tooltip: 'Delete selected records',
			handler: function() {
				var grid = this.up('grid'),
					selection = grid.getSelectionModel().getSelection();
				//are we going to ask for a confirmation here?
				/*Scalr.Confirm({
					type: 'delete',
					msg: 'Delete selected ' + selection.length + ' DNS record(s)?',
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
			tooltip: 'Add DNS record',
			handler: function() {
				var grid = this.up('grid'),
					store = grid.getStore(),
					rowEditing = grid.getPlugin('rowediting');
				rowEditing.cancelEdit();
				store.insert(0, store.createModel({name: null, type: null, ttl: null, value: null, isnew: true}));
				rowEditing.startEdit(0, 0);
				rowEditing.getEditor().getForm().clearInvalid(0);
			}
		}]
	}],	
	viewConfig: {
		focusedItemCls: 'x-grid-row-over',
		overItemCls: '',
		plugins: {
			ptype: 'dynemptytext',
			emptyText: '<b style="line-height:20px">No DNS records were found to match your search.</b><br/> Try modifying your search criteria',
			emptyTextNoItems:	'<b style="line-height:20px">No DNS records.</b><br/>'+
								'Click "<b>+</b>" button to create one.'
		},
		loadingText: 'Loading users ...',
		deferEmptyText: false,
		cls: 'x-grid-view-dns-records'
	},
	setType: function(type) {
		this.down('#type').setValue(type);
	},
	listeners: {
		viewready: function() {
			this.down('#livesearch').store = this.getStore();
			this.down('#type')[this.stores.system && this.stores.system.data.length?'show':'hide']();
		},
		selectionchange: function(selModel, selected) {
			this.down('#delete').setDisabled(!selected.length);
		},
		changetype: function(type) {
			var selModel = this.getSelectionModel(),
				liveSearch = this.down('#livesearch'),
				rowEditing = this.getPlugin('rowediting');
			if (type == 'system') {
				rowEditing.cancelEdit();
				rowEditing.disable();
				this.down('#add').disable();
				selModel.deselectAll();
				selModel.setLocked(true);
			} else {
				rowEditing.enable();
				this.down('#add').enable();
				selModel.setLocked(false);
			}
			this.reconfigure(this.stores[type]);
			liveSearch.reset();
			liveSearch.store = this.stores[type];
			
		},
		closeeditor: function() {
			var rowEditing = this.getPlugin('rowediting');
			if (rowEditing.editing) {
				var dnsRecordForm = rowEditing.getEditor().getForm();
				if (dnsRecordForm.getRecord().get('isnew') && 
					Ext.isEmpty(dnsRecordForm.findField('name').getValue()) &&
					Ext.isEmpty(dnsRecordForm.findField('value').getValue()) ) {
					rowEditing.cancelEdit();
				} else {
					rowEditing.continuousAdd = false;
					rowEditing.completeEdit();
					rowEditing.continuousAdd = true;
					if (rowEditing.editing) {
						Scalr.message.Error('Please correct errors in DNS records first.');
						return false;
					}
				}
			}
			return true;
		}
	}
	
});

Ext.define('Scalr.ui.GridDnsRowEditing', {
	extend: 'Ext.grid.plugin.RowEditing',
	alias: 'plugin.dnsrowediting',
	clicksToMoveEditor: 1,
	clicksToEdit: 1,
	autoCancel: true,
	errorSummary: false,
	continuousAdd: true,

    init: function(grid) {
		this.mon(Ext.getDoc(), {
			mousewheel: this.onDocClick,
			mousedown: this.onDocClick,
			scope: this
		});
        this.callParent(arguments);
    },

	destroy: function() {
		var doc = doc = Ext.getDoc();
		doc.un('mousewheel', this.onDocClick, this);
		doc.un('mousedown', this.onDocClick, this);
		this.callParent(arguments);
	},

	listeners: {
		startedit: function(editor, o) {
			if (o.record.get('isnew')) {
				this.getEditor().getForm().findField('type').setValue('A');
				this.getEditor().getForm().findField('ttl').setValue(14400);
			} else {
				this.getEditor().getForm().findField('type').fireEvent('change');
			}
			this.grid.getSelectionModel().deselect(o.record);
		},
		canceledit: function(editor, o) {
			if (o.record.get('isnew')) {
				this.grid.getStore().remove(o.record);
			} else {
				var selModel = this.grid.getSelectionModel();
				selModel.deselect(o.record);
				selModel.refreshLastFocused();
			}
		},
		edit: function(editor, o) {
			var selModel = this.grid.getSelectionModel(),
				store = this.grid.getStore();
			selModel.deselect(o.record);
			o.record.set('ttl', o.record.get('ttl') || 0);
			if (o.record.get('isnew')) {
				o.record.set('isnew', false);
				if (this.continuousAdd) {
					store.insert(0, store.createModel({name: null, type: null, ttl: null, value: null, isnew: true}));
					this.startEdit(0, 0);
					this.getEditor().getForm().clearInvalid(0);
				}
			}
			selModel.refreshLastFocused();
		},
		validateedit: function(editor, o){
			var me = this,
				form = me.getEditor().getForm(),
				valid = true,
				name = o.newValues.name == '@' || o.newValues.name == '' ? me.grid.zone['domainName'] + '.' : o.newValues.name;

			o.store.data.each(function(){
				if (o.record !== this) {
					var rname = this.get('name');
					rname = rname == '@' || rname == '' ? me.grid.zone['domainName'] + '.' : rname;
					if ((o.newValues.type == 'CNAME' || this.get('type') == 'CNAME') && this.get('name') == name) {
						form.findField('name').markInvalid('Conflict name ' + name);
						valid = false;
						return false;
					}
				}
			});

			return valid;
		}

	},
	onDocClick: function(e) {
		if (!this.isDestroyed) {
			var cancelEdit = false;
			cancelEdit = !e.within(this.grid.view.el, false, true);
			if (cancelEdit) {
				this.getEditor().getForm().getFields().each(function(){
					if(this.picker) {
						cancelEdit = !e.within(this.picker.el, false, true);
					}
					return cancelEdit;
				});
			}
			if (cancelEdit) {
				this.cancelEditIf();
			}
		}
	},
	cancelEditIf: function() {
		if (this.editing) {
			this.completeEdit();
			if (this.editing) {
				if (!this.editor.getForm().getRecord().get('isnew')) {
					return false;
				} else {
					this.cancelEdit();
				}
			}
		}
		return true;
	},
	onCellClick: function(view, cell, colIdx, record, row, rowIdx, e) {
		/* Changed */
		if (!this.cancelEditIf()) {
			return;
		}
		if (Ext.fly(e.getTarget()).hasCls('x-grid-row-checker')) {//skip row select checkbox click
			return;
		}
		/* End */
		if(!view.expanderSelector || !e.getTarget(view.expanderSelector)) {
			this.startEdit(record, view.getHeaderAtIndex(colIdx));
		}
	},
	startEdit: function(record, columnHeader) {
		if (this.disabled) return;
		var me = this,
			editor = me.getEditor();

		/* Changed */
		if ((editor.beforeEdit() !== false) && (this.superclass.startEdit.apply(this, arguments) !== false)) {
			editor.startEdit(me.context.record, me.context.column);
			me.fireEvent('startedit', me, me.context)
			return true;
		}
		/* End */
		return false;
	},
	onEnterKey: function(e) {
		var me = this,
			grid = me.grid,
			selModel = grid.getSelectionModel(),
			record,
			pos,
			columnHeader = grid.headerCt.getHeaderAtIndex(0);

		// Calculate editing start position from SelectionModel
		// CellSelectionModel
		if (selModel.getCurrentPosition) {
			pos = selModel.getCurrentPosition();
			if (pos) {
				record = grid.store.getAt(pos.row);
				columnHeader = grid.headerCt.getHeaderAtIndex(pos.column);
			}
			/* Changed */
			else if (selModel.lastFocused) {
				record = selModel.lastFocused;
			}
			/* End */
		}
		// RowSelectionModel
		else {
			record = selModel.getLastSelected();
		}

		// If there was a selection to provide a starting context...
		if (record && columnHeader) {
			me.startEdit(record, columnHeader);
		}
	}
});