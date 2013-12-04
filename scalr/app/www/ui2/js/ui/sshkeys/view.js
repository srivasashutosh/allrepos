Scalr.regPage('Scalr.ui.sshkeys.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'id','type','fingerprint','cloud_location','farm_id','cloud_key_name' ],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/sshkeys/xListSshKeys/'
		},
		remoteSort: true
	});


	return Ext.create('Ext.grid.Panel', {
		title: 'Tools &raquo; SSH Keys manager',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { sshKeyId: '' },
		store: store,
		stateId: 'grid-sshkeys-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'SSH keys',
				href: '#/sshkeys/view'
			}
		}],

		viewConfig: {
			emptyText: 'No SSH keys found',
			disableSelection: true,
			loadingText: 'Loading SSH keys ...'
		},

		columns: [
			{ text: 'Key ID', width: 100, dataIndex: 'id', sortable: true },
			{ text: 'Name', flex: 1, dataIndex: 'cloud_key_name', sortable: false },
			{ header: 'Type', width: 200, dataIndex: 'type', sortable: true },
			{ header: "Cloud location", width: 150, dataIndex: 'cloud_location', sortable: true, xtype: 'templatecolumn', tpl: 
			'<tpl if="cloud_location">{cloud_location}<tpl else><img src="/ui2/images/icons/false.png" /></tpl>'
			},
			{ header: 'Farm ID', width: 80, dataIndex: 'farm_id', sortable: false },
			{
				xtype: 'optionscolumn',
				optionsMenu: [{
					text: 'Download Private key',
					iconCls: 'x-menu-icon-downloadprivatekey',
					menuHandler: function (item) {
 						Scalr.utils.UserLoadFile('/sshkeys/' + item.record.get('id') + '/downloadPrivate');
 					}
 				}, {
	 				text: 'Download SSH public key',
					iconCls: 'x-menu-icon-downloadpublickey',
	 				menuHandler: function (item) {
 						Scalr.utils.UserLoadFile('/sshkeys/' + item.record.get('id') + '/downloadPublic');
 					}
 				}, {
					itemId: 'option.delete',
					text: 'Remove',
					iconCls: 'x-menu-icon-delete',
					request: {
						confirmBox: {
							type: 'delete',
							msg: 'Remove SSH keypair "{cloud_key_name}"?'
						},
						processBox: {
							type: 'delete',
							msg: 'Removing SSH keypair ...'
						},
						url: '/sshkeys/delete/',
						dataHandler: function (record) {
							return { sshKeyId: record.get('id') };
						},
						success: function () {
							store.load();
						}
					}
				}]
			/*
			new Ext.menu.Separator({itemId: "option.download_sep"}),
			{ itemId: "option.regenerate", text:'Regenerate', handler: function(item) {

				Ext.Msg.wait('Please wait while generating keys');
				Ext.Ajax.request({
					url: '/sshkeys/regenerate',
					params:{id:item.currentRecordData.id},
					success: function(response, options) {
						Ext.MessageBox.hide();

						var result = Ext.decode(response.responseText);
						if (result.success == true) {
							Scalr.Viewers.SuccessMessage('Key successfully regenerated');
						} else {
							Scalr.Viewers.ErrorMessage(result.error);
						}
					}
				});
			}}
			*/
			}
		],

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			items: [{
				xtype: 'filterfield',
				store: store
			}, ' ', {
				xtype: 'combo',
				fieldLabel: 'Farm',
				labelWidth: 34,
				width: 250,
				matchFieldWidth: false,
				listConfig: {
					minWidth: 150
				},
				store: {
					fields: [ 'id', 'name' ],
					data: moduleParams['farms'],
					proxy: 'object'
				},
				editable: false,
				queryMode: 'local',
				value: 0,
				valueField: 'id',
				displayField: 'name',
				listeners: {
					change: function () {
						this.up('panel').store.proxy.extraParams['farmId'] = this.getValue();
						this.up('panel').store.loadPage(1);
					}
				}
			}]
		}]
	});
});
