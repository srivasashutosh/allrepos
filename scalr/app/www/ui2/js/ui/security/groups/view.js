Scalr.regPage('Scalr.ui.security.groups.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'name', 'description', 'id',
			'farm_name', 'farm_id', 'role_name', 'farm_roleid'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/security/groups/xListGroups/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Security &raquo; Groups &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: {},
		store: store,
		stateId: 'grid-security-groups-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}],

		viewConfig: {
			emptyText: "No security groups found",
			loadingText: 'Loading security groups ...'
		},

		columns: [
		    { header: "ID", width: 180, dataIndex: 'id', sortable: true },
		    { header: 'Used by', flex: 1, dataIndex: 'farm_id', sortable: false, xtype: 'templatecolumn', tpl:
				'<tpl if="farm_id">' +
					'<a href="#/farms/{farm_id}/view" title="Farm {farm_name}">{farm_name}</a>' +
					'<tpl if="farm_roleid">' +
						'&nbsp;&rarr;&nbsp;<a href="#/farms/{farm_id}/roles/{farm_roleid}/view" title="Role {role_name}">{role_name}</a> ' +
					'</tpl>' +
				'<tpl else><img src="/ui2/images/icons/false.png"></tpl>'
			},
			{ header: "Name", flex: 1, dataIndex: 'name', sortable: true },
			{ header: "Description", flex: 2, dataIndex: 'description', sortable: true },
			{
				xtype: 'optionscolumn',
				optionsMenu: [
					{ itemId: "option.edit", iconCls: 'x-menu-icon-edit', text:'Edit', menuHandler:function(item) {
						Scalr.event.fireEvent('redirect', '#/security/groups/' + item.record.get('id') + '/edit?platform=' + loadParams['platform'] + '&cloudLocation=' + store.proxy.extraParams.cloudLocation);
					} }
				],

				getOptionVisibility: function (item, record) {
					return true;
				},

				getVisibility: function (record) {
					return true;
				}
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
						msg: 'Remove selected security group(s): %s?',
						type: 'delete'
					},
					processBox: {
						msg: 'Removing security group(s) ...',
						type: 'delete'
					},
					url: '/security/groups/xRemove',
					dataHandler: function (records) {
						var groups = [];
						this.confirmBox.objects = [];
						for (var i = 0, len = records.length; i < len; i++) {
							groups.push(records[i].get('id'));
							this.confirmBox.objects.push(records[i].get('name'));
						}

						return { groups: Ext.encode(groups), platform:loadParams['platform'], cloudLocation: store.proxy.extraParams.cloudLocation};
					}
				}
			}]
		},

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			items: [{
				xtype: 'filterfield',
				store: store
			}, ' ', {
				xtype: 'fieldcloudlocation',
				itemId: 'cloudLocation',
				store: {
					fields: [ 'id', 'name' ],
					data: moduleParams.locations,
					proxy: 'object'
				},
				gridStore: store,
				cloudLocation: loadParams['cloudLocation'] || ''
			}, ' ', {
				xtype: 'button',
				enableToggle: true,
				width: 190,
				text: 'Show all security groups',
				toggleHandler: function (field, checked) {
					store.proxy.extraParams.showAll = checked ? 'true' : 'false';
					store.loadPage(1);
				}
			}]
		}]
	});
});
