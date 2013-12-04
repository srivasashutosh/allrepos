Scalr.regPage('Scalr.ui.tools.cloudstack.snapshots.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'snapshotId', 'volumeId', 'state', 'createdAt', 'volumeType', 'intervalType', 'type'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/tools/cloudstack/snapshots/xListSnapshots/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Tools &raquo; Cloudstack &raquo; Snapshots',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { volumeId: '' },
		store: store,
		stateId: 'grid-tools-cloudstack-volumes-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Cloudstack Snapshots',
				href: '#/tools/cloudstack/snapshots'
			}
		}],

		viewConfig: {
			emptyText: 'No snapshots found',
			loadingText: 'Loading snapshots ...'
		},

		columns: [
			{ header: "ID", width: 80, dataIndex: 'snapshotId', sortable: true },
			{ header: "Type", width: 150, dataIndex: 'type', sortable: true},
			{ header: "Volume ID", width: 90, dataIndex: 'volumeId', sortable: true },
			{ header: "Volume type", width: 180, dataIndex: 'volumeType', sortable: true },
			{ header: "Status", width: 180, dataIndex: 'state', sortable: true },
			{ header: "Created at", width: 180, dataIndex: 'createdAt', sortable: true },
			{
				xtype: 'optionscolumn',
				getOptionVisibility: function (item, record) {
					return true;
				},

				optionsMenu: [{
					itemId: 'option.delete',
					text: 'Delete',
					iconCls: 'x-menu-icon-delete',
					request: {
						confirmBox: {
							type: 'delete',
							msg: 'Are you sure want to delete Snapshot "{snapshotId}"?'
						},
						processBox: {
							type: 'delete',
							msg: 'Deleting volume(s) ...'
						},
						url: '/tools/cloudstack/snapshots/xRemove/',
						dataHandler: function (record) {
							return { snapshotId: Ext.encode([record.get('snapshotId')]), cloudLocation: store.proxy.extraParams.cloudLocation };
						},
						success: function () {
							store.load();
						}
					}
				}]
			}
		],

		multiSelect: true,
		selModel: {
			selType: 'selectedmodel'
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
			afterItems: [{
				ui: 'paging',
				itemId: 'delete',
				iconCls: 'x-tbar-delete',
				tooltip: 'Select one or more events to delete them',
				disabled: true,
				handler: function() {
					var request = {
						confirmBox: {
							msg: 'Delete selected snapshot(s): %s ?',
							type: 'delete'
						},
						processBox: {
							msg: 'Deleting snapshot(s) ...',
							type: 'delete'
						},
						url: '/tools/cloudstack/snapshots/xRemove/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), data = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						data.push(records[i].get('snapshotId'));
						request.confirmBox.objects.push(records[i].get('snapshotId'));
					}
					request.params = { snapshotId: Ext.encode(data), cloudLocation: store.proxy.extraParams.cloudLocation };
					Scalr.Request(request);
				}
			}],
			items: [{
				xtype: 'fieldcloudlocation',
				itemId: 'cloudLocation',
				store: {
					fields: [ 'id', 'name' ],
					data: moduleParams.locations,
					proxy: 'object'
				},
				gridStore: store,
				cloudLocation: loadParams['cloudLocation'] || ''
			}]
		}]
	});
});
