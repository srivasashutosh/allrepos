Scalr.regPage('Scalr.ui.dm.sources.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'id', 'url', 'type', 'auth_type'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/dm/sources/xListSources'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Deployments &raquo; Sources',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { sourceId: ''},
		store: store,
		stateId: 'grid-dm-sources-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Deployment Sources',
				href: '#/dm/sources/view'
			}
		}],

		viewConfig: {
			emptyText: 'No sources found',
			loadingText: 'Loading sources ...'
		},

		columns: [
			{ header: "ID", width: 80, dataIndex: 'id', sortable: true },
			{ header: "URL", flex: 1, dataIndex: 'url', sortable: true },
			{ header: "Type", width: 120, dataIndex: 'type', sortable: true },
			{ header: "Auth type", width: 120, dataIndex: 'auth_type', sortable: false },
			{
				xtype: 'optionscolumn',
				optionsMenu: [{
					text: 'Edit',
					iconCls: 'x-menu-icon-edit',
					href: '#/dm/sources/{id}/edit'
				}, {
					xtype: 'menuseparator'
				}, {
					text: 'Delete',
					iconCls: 'x-menu-icon-delete',
					request: {
						confirmBox: {
							msg: 'Are you sure want to remove demployment source "{url}"?',
							type: 'delete'
						},
						processBox: {
							type: 'delete',
							msg: 'Removing demployment source ...'
						},
						url: '/dm/sources/xRemoveSources',
						dataHandler: function (record) {
							return {
								sourceId: record.get('id')
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
					Scalr.event.fireEvent('redirect', '#/dm/sources/create');
				}
			}]
		}]
	});
});
