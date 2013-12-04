Scalr.regPage('Scalr.ui.services.configurations.presets.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'id','env_id','client_id','name','role_behavior','dtadded','dtlastmodified' ],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/services/configurations/presets/xListPresets/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Services &raquo; Configurations &raquo; Presets',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { presetId: '' },
		store: store,
		stateId: 'grid-services-configurations-presets-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Server config presets',
				href: '#/services/configurations/presets/view'
			}
		}],

		viewConfig: {
			emptyText: "No presets found",
			loadingText: 'Loading presets ...'
		},

		columns:[
			{ header: "ID", width: 50, dataIndex: 'id', sortable:true },
			{ header: "Name", flex: 1, dataIndex: 'name', sortable:true },
			{ header: "Role behavior", flex: 1, dataIndex: 'role_behavior', sortable: true },
			{ header: "Added at", flex: 1, dataIndex: 'dtadded', sortable: false },
			{ header: "Last time modified", flex: 1, dataIndex: 'dtlastmodified', sortable: false },
			{
				xtype: 'optionscolumn',
				optionsMenu: [{
					text: 'Edit',
					iconCls: 'x-menu-icon-edit',
					href: "#/services/configurations/presets/{id}/edit"
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
						msg: 'Remove selected configuration preset(s): %s ?'
					},
					processBox: {
						type: 'delete',
						msg: 'Removing configuration preset(s) ...'
					},
					url: '/services/configurations/presets/xRemove/',
					dataHandler: function(records) {
						var presets = [];
						this.confirmBox.objects = [];
						for (var i = 0, len = records.length; i < len; i++) {
							presets.push(records[i].get('id'));
							this.confirmBox.objects.push(records[i].get('name'));
						}
						return { presets: Ext.encode(presets) };
					}
				}
			}]
		},

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			/*
			afterItems: [{
				ui: 'paging',
				iconCls: 'x-tbar-add',
				handler: function() {
					Scalr.event.fireEvent('redirect', '#/services/configurations/presets/build');
				}
			}],
			*/
			items: [{
				xtype: 'filterfield',
				store: store
			}]
		}]
	});
});
