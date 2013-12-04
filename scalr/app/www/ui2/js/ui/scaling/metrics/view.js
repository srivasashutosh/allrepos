Scalr.regPage('Scalr.ui.scaling.metrics.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'id','env_id','client_id','name','file_path','retrieve_method','calc_function' ],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/scaling/metrics/xListMetrics/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Scaling &raquo; Metrics &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		scalrReconfigureParams: { metricId: '' },
		store: store,
		stateId: 'grid-scaling-metrics-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},

		tools: [{
			xtype: 'gridcolumnstool'
		}, {
			xtype: 'favoritetool',
			favorite: {
				text: 'Custom scaling metrics',
				href: '#/scaling/metrics/view'
			}
		}],

		viewConfig: {
			emptyText: "No presets defined",
			loadingText: 'Loading presets ...'
		},

		columns: [
			{ header: "ID", width: 40, dataIndex: 'id', sortable: true },
			{ header: "Name", flex: 1, dataIndex: 'name', sortable:true },
			{ header: "File path", flex: 1, dataIndex: 'file_path', sortable: true },
			{ header: "Retrieve method", flex: 1, dataIndex: 'retrieve_method', sortable: false, xtype: 'templatecolumn', tpl:
				'<tpl if="retrieve_method == \'read\'">File-Read</tpl>' +
				'<tpl if="retrieve_method == \'execute\'">File-Execute</tpl>'
			},
			{ header: "Calculation function", flex: 1, dataIndex: 'calc_function', sortable: false, xtype: 'templatecolumn', tpl:
				'<tpl if="calc_function == \'avg\'">Average</tpl>' +
				'<tpl if="calc_function == \'sum\'">Sum</tpl>'
			}, {
				xtype: 'optionscolumn',
				optionsMenu: [{
					text: 'Edit',
					href: "#/scaling/metrics/{id}/edit"
				}],
				getVisibility: function (record) {
					return (record.get('env_id') != 0);
				}
			}
		],


		multiSelect: true,
		selModel: {
			selType: 'selectedmodel',
			getVisibility: function (record) {
				return (record.get('env_id') != 0);
			}
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
			beforeItems: [{
				ui: 'paging',
				iconCls: 'x-tbar-add',
				handler: function() {
					Scalr.event.fireEvent('redirect', '#/scaling/metrics/create');
				}
			}],
			afterItems: [{
				ui: 'paging',
				itemId: 'delete',
				iconCls: 'x-tbar-delete',
				tooltip: 'Select one or more metrics to delete them',
				disabled: true,
				handler: function() {
					var request = {
						confirmBox: {
							msg: 'Remove selected metric(s): %s ?',
							type: 'delete'
						},
						processBox: {
							msg: 'Removing selected metric(s) ...',
							type: 'delete'
						},
						url: '/scaling/metrics/xRemove/',
						success: function() {
							store.load();
						}
					}, records = this.up('grid').getSelectionModel().getSelection(), metrics = [];

					request.confirmBox.objects = [];
					for (var i = 0, len = records.length; i < len; i++) {
						metrics.push(records[i].get('id'));
						request.confirmBox.objects.push(records[i].get('name'));
					}
					request.params = { metrics: Ext.encode(metrics) };
					Scalr.Request(request);
				}
			}]
		}]
	});
});
