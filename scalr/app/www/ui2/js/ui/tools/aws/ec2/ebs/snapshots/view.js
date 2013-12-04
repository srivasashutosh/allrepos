Scalr.regPage('Scalr.ui.tools.aws.ec2.ebs.snapshots.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'snapshotId', 'volumeId', 'volumeSize', 'status', 'startTime', 'comment', 'progress', 'owner','volumeSize' ],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/tools/aws/ec2/ebs/snapshots/xListSnapshots/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Tools &raquo; Amazon Web Services &raquo; EC2 &raquo; EBS &raquo; Snapshots',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { volumeId: '', snapshotId: '' },
		store: store,
		stateId: 'grid-tools-aws-ec2-ebs-snapshots-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'EBS Snapshots',
				href: '#/tools/aws/ec2/ebs/snapshots'
			}
		}],

		viewConfig: {
			emptyText: "No snapshots found",
			loadingText: 'Loading snapshots ...'
		},

		columns: [
			{ header: "Snapshot ID", width: 150, dataIndex: 'snapshotId', sortable: true },
			{ header: "Owner", width: 150, dataIndex: 'owner', sortable: true },
			{ header: "Created on", width: 100, dataIndex: 'volumeId', sortable: true },
			{ header: "Size (GB)", width: 100, dataIndex: 'volumeSize', sortable: true },
			{ header: "Status", width: 120, dataIndex: 'status', sortable: true },
			{ header: "Local start time", width: 150, dataIndex: 'startTime', sortable: true },
			{ header: "Completed", width: 100, dataIndex: 'progress', sortable: false, align:'center', xtype: 'templatecolumn', tpl: '{progress}%' },
			{ header: "Comment", flex: 1, dataIndex: 'comment', sortable: true, xtype: 'templatecolumn', tpl: '<tpl if="comment">{comment}</tpl>' },
			{
				xtype: 'optionscolumn',
				optionsMenu: [{
					itemId: 'option.create',
					text: 'Create new volume based on this snapshot',
					iconCls: 'x-menu-icon-create',
					menuHandler: function(menuItem) {
						Scalr.event.fireEvent('redirect','#/tools/aws/ec2/ebs/volumes/create?' +
							Ext.Object.toQueryString({
								'snapshotId': menuItem.record.get('snapshotId'),
								'size': menuItem.record.get('volumeSize'),
								'cloudLocation': store.proxy.extraParams.cloudLocation
							})
						);
					}
				}, {
					itemId: 'option.migrate',
					iconCls: 'x-menu-icon-fork',
					text: 'Copy to another EC2 region',
					request: {
						processBox: {
							type:'action'
						},
						url: '/tools/aws/ec2/ebs/snapshots/xGetMigrateDetails/',
						dataHandler: function (record) {
							return { 
								'snapshotId': record.get('snapshotId'),
								'cloudLocation': store.proxy.extraParams.cloudLocation 
							};
						},
						success: function (data) {
							Scalr.Request({
								confirmBox: {
									type: 'action',
									msg: 'Copying snapshots allows you to use them in additional regions',
									formWidth: 700,
									form: [{
										xtype: 'fieldset',
										title: 'Region copy',
										items: [{
											xtype: 'displayfield',
											labelWidth: 120,
											width: 500,
											fieldLabel: 'Snpashot ID',
											value: data['snapshotId']	
										},{
											xtype: 'displayfield',
											labelWidth: 120,
											width: 500,
											fieldLabel: 'Source region',
											value: data['sourceRegion']	
										}, {
											xtype: 'combo',
											fieldLabel: 'Destination region',
											store: {
												fields: [ 'cloudLocation', 'name' ],
												proxy: 'object',
												data: data['availableDestinations']
											},
											autoSetValue: true,
											valueField: 'cloudLocation',
											displayField: 'name',
											editable: false,
											queryMode: 'local',
											name: 'destinationRegion',
											labelWidth: 120,
											width: 500
										}]
									}]
								},
								processBox: {
									type: 'action'
								},
								url: '/tools/aws/ec2/ebs/snapshots/xMigrate',
								params: {snapshotId: data.snapshotId, sourceRegion: data.sourceRegion},
								success: function (data) {
									document.location.href = '#/tools/aws/ec2/ebs/snapshots/' + data.data.snapshotId + '/view?cloudLocation=' + data.data.cloudLocation;
								}
							});
						}
					}
				}, {
					xtype: 'menuseparator',
					itemId: 'option.Sep'
				}, {
					itemId: 'option.delete',
					text: 'Delete',
					iconCls: 'x-menu-icon-delete',
					request: {
						confirmBox: {
							type: 'delete',
							msg: 'Are you sure want to delete EBS snapshot "{snapshotId}"?'
						},
						processBox: {
							type: 'delete',
							msg: 'Deleting EBS snapshot ...'
						},
						url: '/tools/aws/ec2/ebs/snapshots/xRemove/',
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
		selType: 'selectedmodel',
		listeners: {
			selectionchange: function(selModel, selections) {
				var toolbar = this.down('scalrpagingtoolbar');
				toolbar.down('#delete').setDisabled(!selections.length);
			}
		},

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			afterItems: [{
				ui: 'paging',
				itemId: 'delete',
				disabled: true,
				iconCls: 'x-tbar-delete',
				tooltip: 'Delete',
				handler: function() {
					var request = {
						confirmBox: {
							type: 'delete',
							msg: 'Delete selected EBS snapshot(s): %s ?'
						},
						processBox: {
							msg: 'Deleting EBS snapshot(s) ...',
							type: 'delete'
						},
						url: '/tools/aws/ec2/ebs/snapshots/xRemove/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), data = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						data.push(records[i].get('snapshotId'));
						request.confirmBox.objects.push(records[i].get('snapshotId'))
					}
					request.params = { snapshotId: Ext.encode(data), cloudLocation: store.proxy.extraParams.cloudLocation };
					Scalr.Request(request);
				}
			}],
			items: [{
				xtype: 'filterfield',
				store: store
			}, ' ',{
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
				width: 220,
				text: 'Show public (Shared) snapshots',
				toggleHandler: function (field, checked) {
					store.proxy.extraParams.showPublicSnapshots = checked ? '1' : '';
					store.loadPage(1);
				}
			}]
		}]
	});
});
