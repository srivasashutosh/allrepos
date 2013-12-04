Scalr.regPage('Scalr.ui.scripts.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			{ name: 'id', type: 'int' }, { name: 'accountId', type: 'int' },
			'name', 'description', 'dtUpdated', 'version'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/scripts/xListScripts/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Scripts &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: {scriptId: '' },
		store: store,
		stateId: 'grid-scripts-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Scripts',
				href: '#/scripts/view'
			}
		}],

		viewConfig: {
			emptyText: 'No scripts defined',
			loadingText: 'Loading scripts ...'
		},

		columns: [
			{ header: 'ID', width: 50, dataIndex: 'id', sortable: true },
			{ header: 'Name', flex: 1, dataIndex: 'name', sortable: true },
			{ header: 'Description', flex: 2, dataIndex: 'description', sortable: true },
			{ header: 'Latest version', width: 100, dataIndex: 'version', sortable: false, align:'center' },
			{ header: 'Updated on', width: 160, dataIndex: 'dtUpdated', sortable: true },
			{ header: 'Origin', width: 80, dataIndex: 'origin', sortable: false, align:'center', xtype: 'templatecolumn', tpl:
				'<tpl if="accountId == &quot;0&quot;"><img src="/ui2/images/ui/scripts/default.png" height="16" title="Contributed by Scalr"></tpl>' +
				'<tpl if="accountId != &quot;0&quot;"><img src="/ui2/images/ui/scripts/custom.png" height="16" title="Custom"></tpl>'
			}, {
				xtype: 'optionscolumn',
				getOptionVisibility: function (item, record) {
					if (item.itemId == 'option.view' || item.itemId == 'option.fork') {
						return true;
					} else {
						if (item.itemId == 'option.execute' || item.itemId == 'option.execSep') {
							return Scalr.user.type != 'ScalrAdmin';
						}
						return (Scalr.user.type == 'ScalrAdmin' || record.get('accountId'));
					}
				},

				optionsMenu: [{
					itemId: 'option.view',
					iconCls: 'x-menu-icon-view',
					text: 'View',
					href: '#/scripts/{id}/view'
				}, {
					itemId: 'option.execute',
					iconCls: 'x-menu-icon-execute',
					text: 'Execute',
					href: '#/scripts/{id}/execute'
				}, {
					xtype: 'menuseparator',
					itemId: 'option.execSep'
				}, {
					itemId: 'option.fork',
					text: 'Fork',
					iconCls: 'x-menu-icon-fork',
					menuHandler: function(item) {
						Scalr.Request({
							confirmBox: {
								formValidate: true,
								form: [{
									xtype: 'textfield',
									name: 'newName',
									labelWidth: 110,
									fieldLabel: 'New script name',
									value: 'Custom ' + item.record.get('name'),
									allowBlank: false
								}],
								type: 'action',
								msg: 'Are you sure want to fork script "' + item.record.get('name') + '" ?'
							},
							processBox: {
								type: 'action'
							},
							url: '/scripts/xFork',
							params: {
								scriptId: item.record.get('id')
							},
								success: function () {
								store.load();
							}
						});
					}
				}, {
					itemId: 'option.edit',
					iconCls: 'x-menu-icon-edit',
					text: 'Edit',
					href: '#/scripts/{id}/edit'
				}]
			}
		],

		multiSelect: true,
		selModel: {
			selType: 'selectedmodel',
			getVisibility: function(record) {
				return (Scalr.user.type == 'ScalrAdmin') || !!record.get('accountId');
			}
		},

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
			beforeItems: [{
				ui: 'paging',
				iconCls: 'x-tbar-add',
				handler: function() {
					Scalr.event.fireEvent('redirect', '#/scripts/create');
				}
			}],
			afterItems: [{
				ui: 'paging',
				itemId: 'delete',
				iconCls: 'x-tbar-delete',
				tooltip: 'Select one or more scripts to delete them',
				disabled: true,
				handler: function() {
					var request = {
						confirmBox: {
							msg: 'Remove selected script(s): %s ?',
							type: 'delete'
						},
						processBox: {
							msg: 'Removing selected scripts(s) ...',
							type: 'delete'
						},
						url: '/scripts/xRemove',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), data = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						data.push(records[i].get('id'));
						request.confirmBox.objects.push(records[i].get('name'));
					}
					request.params = { scripts: Ext.encode(data) };
					Scalr.Request(request);
				}
			}],
			items: [{
				xtype: 'filterfield',
				store: store
			}, ' ', {
				xtype: 'buttongroupfield',
				fieldLabel: 'Owner',
				labelWidth: 45,
				hidden: (Scalr.user.type == 'ScalrAdmin'),
				value: '',
				items: [{
					xtype: 'button',
					text: 'All',
					value: '',
					width: 60
				}, {
					xtype: 'button',
					text: 'Scalr',
					width: 60,
					value: 'Shared'
				}, {
					xtype: 'button',
					text: 'Private',
					width: 60,
					value: 'Custom'
				}],
				listeners: {
					change: function (field, value) {
						store.proxy.extraParams.origin = value;
						store.loadPage(1);
					}
				}
			}]
		}]
	});
});
