Scalr.regPage('Scalr.ui.scripts.events.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'id', 'name','description' ],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/scripts/events/xListCustomEvents/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Scripts &raquo; Events &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { metricId: '' },
		store: store,
		stateId: 'grid-scripts-events-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Custom events',
				href: '#/scripts/events/view'
			}
		}],

		viewConfig: {
			emptyText: "No custom events",
			loadingText: 'Loading custom events ...'
		},

		columns: [
			{ header: "ID", width: 40, dataIndex: 'id', sortable: true },
			{ header: "Name", flex: 1, dataIndex: 'name', sortable:true },
			{ header: "Description", flex: 10, dataIndex: 'description', sortable: false },
			{
				xtype: 'optionscolumn',
				optionsMenu: [{
					text: 'Edit',
					href: "#/scripts/events/{id}/edit"
				}],
				getVisibility: function (record) {
					return (record.get('env_id') != 0);
				}
			}
		],

		multiSelect: true,
		selModel: {
			selType: 'selectedmodel',
			getVisibility: function (record) {
				return (record.get('env_id') != 0);
			}
		},

		listeners: {
			selectionchange: function(selModel, selections) {
				this.down('scalrpagingtoolbar').down('#delete').setDisabled(!selections.length);
			}
		},

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			beforeItems: [{
				ui: 'paging',
				iconCls: 'x-tbar-add',
				handler: function() {
					Scalr.event.fireEvent('redirect', '#/scripts/events/create');
				}
			}],
			afterItems: [{
				ui: 'paging',
				itemId: 'delete',
				iconCls: 'x-tbar-delete',
				tooltip: 'Select one or more events to delete them',
				disabled: true,
				handler: function() {
					var request = {
						confirmBox: {
							msg: 'Remove selected event(s): %s ?',
							type: 'delete'
						},
						processBox: {
							msg: 'Removing selected event(s) ...',
							type: 'delete'
						},
						url: '/scripts/events/xRemove/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), data = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						data.push(records[i].get('id'));
						request.confirmBox.objects.push(records[i].get('name'));
					}
					request.params = { events: Ext.encode(data) };
					Scalr.Request(request);
				}
			}]
		}]
	});
});
