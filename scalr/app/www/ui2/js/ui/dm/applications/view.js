Scalr.regPage('Scalr.ui.dm.applications.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'id', 'name', 'source_id', 'source_url', 'used_on'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/dm/applications/xListApplications/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Deployments &raquo; Applications &raquo; Manage',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { applicationId: '' },
		store: store,
		stateId: 'grid-dm-applications-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Applications',
				href: '#/dm/applications/view'
			}
		}],

		viewConfig: {
			emptyText: 'No applications found',
			loadingText: 'Loading applications ...'
		},

		columns: [
			{ header: 'ID', width: 80, dataIndex: 'id', sortable: true },
			{ header: 'Name', flex: 1, dataIndex: 'name', sortable: true },
			{ header: 'Source', flex: 1, dataIndex: 'source_url', sortable: false, xtype: 'templatecolumn',
				tpl: '<a href="#/dm/sources/{source_id}/view">{source_url}</a>'
			},
			{ header: 'Status', width: 120, dataIndex: 'status', sortable: false, xtype: 'templatecolumn',
				tpl: '<tpl if="used_on != 0"><span style="color:green;">In use</span></tpl><tpl if="used_on == 0"><span style="color:gray;">Not used</span></tpl>'
			}, {
				xtype: 'optionscolumn',
				optionsMenu: [{
					text: 'Deploy',
					iconCls: 'x-menu-icon-launch',
					href: '#/dm/applications/{id}/deploy'
				}, {
					xtype: 'menuseparator'
				}, {
					text: 'Edit',
					iconCls: 'x-menu-icon-edit',
					href: '#/dm/applications/{id}/edit'
				}, {
					xtype: 'menuseparator'
				}, {
					text: 'Delete',
					iconCls: 'x-menu-icon-delete',
					request: {
						confirmBox: {
							msg: 'Are you sure want to remove demployment "{name}"?',
							type: 'delete'
						},
						processBox: {
							type: 'delete',
							msg: 'Removing demployment ...'
						},
						url: '/dm/applications/xRemoveApplications',
						dataHandler: function (record) {
							return {
								applicationId: record.get('id')
							};
						},
						success: function(data) {
							store.load();
						}
					}
				}]
			}
		],

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			afterItems: [{
				ui: 'paging',
				iconCls: 'x-tbar-add',
				handler: function() {
					Scalr.event.fireEvent('redirect','#/dm/applications/create');
				}
			}]
		}]
	});
});
