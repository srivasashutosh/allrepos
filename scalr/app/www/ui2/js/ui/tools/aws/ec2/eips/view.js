Scalr.regPage('Scalr.ui.tools.aws.ec2.eips.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'ipaddress','instance_id', 'domain', 'allocation_id', 'farm_id', 'farm_name', 'role_name', 'indb', 'farm_roleid', 'server_id', 'server_index' ],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/tools/aws/ec2/eips/xListEips/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Tools &raquo; Amazon Web Services &raquo; EC2 &raquo; Elastic IPs',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: {},
		store: store,
		stateId: 'grid-tools-aws-ec2-eips-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Elastic IPs',
				href: '#/tools/aws/ec2/eips'
			}
		}],

		viewConfig: {
			emptyText: "No elastic IPs found",
			disableSelection: true,
			loadingText: 'Loading elastic IPs ...'
		},

		columns: [
			{ header: "Used By", flex: 1, dataIndex: 'farm_name', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="farm_id"><a href="#/farms/{farm_id}/view" title="Farm {farm_name}">{farm_name}</a>' +
					'<tpl if="role_name">&nbsp;&rarr;&nbsp;<a href="#/farms/{farm_id}/roles/{farm_roleid}/view"' +
						'title="Role {role_name}">{role_name}</a> #{server_index}' +
					'</tpl>' +
				'</tpl>' +
				'<tpl if="! farm_id"><img src="/ui2/images/icons/false.png" /></tpl>'
			},
			{ header: "IP address", width: 200, dataIndex: 'ipaddress', sortable: false },
			{ header: "Type", width: 80, dataIndex: 'domain', sortable: false },
			{ header: "Allocation ID", width: 200, dataIndex: 'allocation_id', sortable: false },
			{ header: "Auto-assigned", width: 150, dataIndex: 'role_name', sortable: true, xtype: 'templatecolumn', align:'center', tpl:
				'<tpl if="indb"><img src="/ui2/images/icons/true.png"></tpl>' +
				'<tpl if="!indb"><img src="/ui2/images/icons/false.png"></tpl>'
			},
			{ header: "Server", flex: 1, dataIndex: 'server_id', sortable: true, xtype: 'templatecolumn', tpl:
				'<tpl if="server_id"><a href="#/servers/{server_id}/view">{server_id}</a></tpl>' +
				'<tpl if="!server_id">{instance_id}</tpl>'
			}, {
				xtype: 'optionscolumn',
				getVisibility: function (record) {
					return !(record.get('server_id'));
				},
				optionsMenu: [
					/*
					{ itemId: "option.associate", text:'Associate',
						menuHandler: function (item) {
							document.location.href = "#/tools/aws/ec2/eips/{ipaddress}/associate?cloudLocation="+store.baseParams.cloudLocation;
						}
					}, */
				{
					itemId: 'option.delete',
					text: 'Delete',
					iconCls: 'x-menu-icon-delete',
					request: {
						confirmBox: {
							type: 'delete',
							msg: 'Are you sure want to delete elastic ip "{ipaddress}"?'
						},
						processBox: {
							type: 'delete',
							msg: 'Deleting elastic IP address ...'
						},
						url: '/tools/aws/ec2/eips/xDelete/',
						dataHandler: function (record) {
							return { elasticIp: record.get('ipaddress'), cloudLocation: store.proxy.extraParams.cloudLocation };
						},
						success: function () {
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
