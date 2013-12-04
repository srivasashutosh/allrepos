Scalr.regPage('Scalr.ui.services.apache.vhosts.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'id','env_id','client_id','name','role_behavior','is_ssl_enabled','last_modified','farm_name','role_name', 'farm_id', 'farm_roleid'],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/services/apache/vhosts/xListVhosts/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Services &raquo; Apache &raquo; Virtualhosts',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { presetId: '' },
		store: store,
		stateId: 'grid-services-apache-vhosts-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Apache Virtual Hosts',
				href: '#/services/apache/vhosts/view'
			}
		}],

		viewConfig: {
			emptyText: "No apache virtualhosts found",
			loadingText: 'Loading virtualhosts ...'
		},

		columns:[
			{ header: "ID", width: 50, dataIndex: 'id', sortable:true },
			{ header: "Name", flex: 5, dataIndex: 'name', sortable:true },
			{ header: "Farm & Role", flex: 5, dataIndex: 'farm_id', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="farm_name && role_name">'+
					'<a href="#/farms/{farm_id}/view" title="Farm {farm_name}">{farm_name}</a>' +
					'&nbsp;&rarr;&nbsp;<a href="#/farms/{farm_id}/roles/{farm_roleid}/view" title="Role {role_name}">{role_name}</a> ' +
				'<tpl else><img src="/ui2/images/icons/false.png" /></tpl>'
			},
			{ header: "Last time modified", width: 150, dataIndex: 'last_modified', sortable: true },
			{ header: "SSL", width: 60, dataIndex: 'is_ssl_enabled', sortable: true, align: 'center', xtype: 'templatecolumn', tpl:
				'<tpl if="is_ssl_enabled == 1"><img src="/ui2/images/icons/true.png" /></tpl>' +
				'<tpl if="is_ssl_enabled == 0"><img src="/ui2/images/icons/false.png" /></tpl>'
			},
			{
				xtype: 'optionscolumn',
				optionsMenu: [{
					text: 'Edit',
					href: "#/services/apache/vhosts/{id}/edit"
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
				tooltip: 'Select one or more virtual hosts to delete them',
				disabled: true,
				handler: function() {
					var request = {
						confirmBox: {
							type: 'delete',
							msg: 'Delete selected virtual host(s): %s ?'
						},
						processBox: {
							type: 'delete',
							msg: 'Deleting selected virtual host(s) ...'
						},
						url: '/services/apache/vhosts/xRemove/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), ids = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						ids.push(records[i].get('id'));
						request.confirmBox.objects.push(records[i].get('name'));
					}
					request.params = { vhosts: Ext.encode(ids) };
					Scalr.Request(request);
				}
			}],
			beforeItems: [{
				ui: 'paging',
				iconCls: 'x-tbar-add',
				handler: function() {
					Scalr.event.fireEvent('redirect', '#/services/apache/vhosts/create');
				}
			}],
			items: [{
				xtype: 'filterfield',
				store: store
			}]
		}]
	});
});
