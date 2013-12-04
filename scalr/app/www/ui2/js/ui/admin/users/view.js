Scalr.regPage('Scalr.ui.admin.users.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'id', 'status', 'email', 'fullname', 'dtcreated', 'dtlastlogin', 'type', 'comments'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/admin/users/xListUsers'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Admin &raquo; Users &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: {},
		store: store,
		stateId: 'grid-admin-users-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}],
		viewConfig: {
			emptyText: 'No users found',
			loadingText: 'Loading users ...'
		},

		columns: [
			{ text: 'ID', width: 50, dataIndex: 'id', sortable: true },
			{ text: 'Email', flex: 1, dataIndex: 'email', sortable: true },
			{ text: 'Status', Width: 50, dataIndex: 'status', sortable: true, xtype: 'templatecolumn', tpl:
				'<span ' +
				'<tpl if="status == &quot;Active&quot;">style="color: green"</tpl>' +
				'<tpl if="status != &quot;Active&quot;">style="color: red"</tpl>' +
				'>{status}</span>'
			},
			{ text: 'Full name', flex: 1, dataIndex: 'fullname', sortable: true },
			{ text: 'Created date', width: 170, dataIndex: 'dtcreated', sortable: true },
			{ text: 'Last login', width: 170, dataIndex: 'dtlastlogin', sortable: true },
			{
				xtype: 'optionscolumn',
				getVisibility: function(record) {
					if (record.get('email') == 'admin') {
						return (Scalr.user.userName == 'admin');
					} else
						return true;
				},
				getOptionVisibility: function (item, record) {
					return !(item.itemId == 'option.delete' && record.get('email') == 'admin');
				},
				optionsMenu: [{
					text: 'Edit',
					iconCls: 'x-menu-icon-edit',
					href: '#/admin/users/{id}/edit'
				}, {
					text: 'Remove',
					itemId: 'option.delete',
					iconCls: 'x-menu-icon-delete',
					request: {
						confirmBox: {
							type: 'delete',
							msg: 'Are you sure want to remove user "{email}" ?'
						},
						processBox: {
							type: 'delete'
						},
						url: '/admin/users/xRemove',
						dataHandler: function (record) {
							return { userId: record.get('id') };
						},
						success: function () {
							store.load()
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
					Scalr.event.fireEvent('redirect', '#/admin/users/create');
				}
			}],
			items: [{
				xtype: 'filterfield',
				store: store
			}]
		}]
	});
});
