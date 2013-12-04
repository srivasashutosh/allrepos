Scalr.regPage('Scalr.ui.scripts.shortcuts.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			{name: 'id', type: 'int'},
			'farmid', 'farmname', 'farm_roleid', 'rolename', 'scriptname', 'event_name'
		],
		proxy: {
			type: 'scalr.paging',
			url: '/scripts/shortcuts/xListShortcuts/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Scripts &raquo; Shortcuts &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { scriptId: '', eventName:'' },
		store: store,
		stateId: 'grid-scripts-shortcuts-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}],

		viewConfig: {
			emptyText: "No shortcuts defined",
			loadingText: 'Loading shortcuts ...'
		},

		columns: [
			{ header: "Target", flex: 1, dataIndex: 'id', sortable: false, xtype: 'templatecolumn', tpl:
				'<a href="#/farms/{farmid}/view">{farmname}</a>' +
				'<tpl if="farm_roleid &gt; 0">&rarr;<a href="#/farms/{farmid}/roles/{farm_roleid}/view">{rolename}</a></tpl>' +
				'&nbsp;&nbsp;&nbsp;'
			},
			{ header: "Script", flex: 2, dataIndex: 'scriptname', sortable: true }, {
				xtype: 'optionscolumn',
				optionsMenu: [{
					text: 'Edit',
					href: "#/scripts/execute?eventName={event_name}&isShortcut=1"
				}]
			}
		],

		multiSelect: true,
		selModel: {
			selType: 'selectedmodel',
			selectedMenu: [{
				text: 'Delete',
				iconCls: 'x-menu-icon-delete',
				request: {
					confirmBox: {
						type: 'delete',
						msg: 'Delete selected shortcuts(s): %s ?'
					},
					processBox: {
						type: 'delete',
						msg: 'Removing selected shortcut(s) ...'
					},
					url: '/scripts/shortcuts/xRemove/',
					dataHandler: function(records) {
						var shortcuts = [];
						this.confirmBox.objects = [];
						for (var i = 0, len = records.length; i < len; i++) {
							shortcuts.push(records[i].get('id'));
							this.confirmBox.objects.push(records[i].get('scriptname'));
						}

						return { shortcuts: Ext.encode(shortcuts) };
					}
				}
			}]
		},

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top'
		}]
	});
});
