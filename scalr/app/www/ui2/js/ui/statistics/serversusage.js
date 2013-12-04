Scalr.regPage('Scalr.ui.statistics.serversusage', function (loadParams, moduleParams) {
	var thisYear = new Date().getFullYear();
	var columns = [{
		xtype: 'templatecolumn',
		text: "Cloud Location / Instance Type",
		flex: 2,
		dataIndex: 'cloudLocation',
		sortable: true,
		tpl: new Ext.XTemplate('<tpl>{cloudLocation} / {instanceType} ({[this.price(values.cloudLocation, values.instanceType)]})</tpl>', {
			price: function(location, insType) {
				if (panel.pricing[location] && panel.pricing[location][insType])
					return '$' + panel.pricing[location][insType] + ' / hour';
				else
					return 'unknown';
			}
		}),
		summaryRenderer: function() {
			return 'Total spent:';
		}
	}];

	Ext.each(Ext.Date.monthNames, function(month) {
		columns.push({
			xtype: 'templatecolumn',
			text: month,
			width: 120,
			dataIndex: month,
			sortable: false,
			align: 'center',
			tpl: '<tpl if="usage.' + month + '"><center>{usage.' + month + '}</center></tpl><tpl if="!usage.' + month + '"><center><img src="/ui2/images/icons/false.png" /></center></tpl>',
			summaryType: function(records, field) {
				var total = 0;
				for (var i = 0; i < records.length; i++) {
					if (
						panel.pricing[records[i].get('cloudLocation')] &&
							panel.pricing[records[i].get('cloudLocation')][records[i].get('instanceType')] &&
							records[i].get('usage')[field]
						)
						total += panel.pricing[records[i].get('cloudLocation')][records[i].get('instanceType')] * records[i].get('usage')[month];
				}

				return total;
			},
			summaryRenderer: function(value) {
				return Ext.String.format('${0}', Ext.util.Format.round(value, 2));

			}
		});
	});

	var panel = new Ext.create('Ext.grid.Panel', {
		title: 'Servers Usage Statistics (instance / hours)',
		scalrOptions: {
			maximize: 'all'
		},
		scalrReconfigureParams: { farmId: '' },

		pricing: moduleParams['price'] ? moduleParams['price'] : [],

		store: {
			fields: [ 'cloudLocation', 'instanceType', 'usage' ],
			proxy: {
				type: 'scalr.paging',
				extraParams: { year: thisYear, envId: Scalr.user.envId, farmId: loadParams.farmId || 0 },
				url: '/statistics/xListServersUsage'
			}
		},

		plugins: {
			ptype: 'gridstore'
		},

		features: [{
			ftype: 'summary'
		}],

		viewConfig: {
			emptyText: 'No statistics found',
			loadingText: 'Loading statistics ...'
		},

		tools: [{
			type: 'refresh',
			handler: function () {
				Scalr.event.fireEvent('refresh');
			}
		}],

		columns: columns,

		dockedItems: [{
			xtype: 'toolbar',
			dock: 'top',
			defaults: {
				xtype: 'combo',
				queryMode: 'local',
				displayField: 'name',
				editable: false
			},
			items: [ {
				fieldLabel: 'Year',
				labelWidth: 30,
				width: 120,
				itemId: 'years',
				valueField: 'name',
				value: thisYear.toString(),
				store: moduleParams.years,
				listeners: {
					change: function(field, value) {
						panel.store.proxy.extraParams.year = value;
						panel.store.load();
					}
				}
			}, ' ', 'Environment:', {
				xtype: Scalr.user.type == 'AccountOwner' ? 'combo' : 'displayfield',
				itemId: 'envId',
				valueField: 'id',
				value: Scalr.user.type == 'AccountOwner' ? Scalr.user.envId : moduleParams.env[Scalr.user.envId],
				store: {
					fields: ['id', 'name'],
					data: moduleParams.env,
					proxy: 'object'
				},
				width: 200,
				matchFieldWidth: false,
				listConfig: {
					width: 'auto',
					minWidth: 200
				},
				listeners: {
					change: function(field, value) {
						panel.store.proxy.extraParams.envId = panel.down('#farmId').store.proxy.extraParams.envId = value;
						var farmId = panel.down('#farmId').getValue();
						panel.down('#farmId').setValue('0');
						panel.down('#farmId').store.load();
						if (farmId == '0')
							panel.store.load();
					}
				}
			}, ' ', 'Farm:', {
				xtype: 'combo',
				valueField: 'id',
				itemId: 'farmId',
				width: 200,
				value: loadParams['farmId'] || '0',
				store: {
					fields: [ 'id', 'name' ],
					proxy: {
						type: 'ajax',
						reader: {
							type: 'json',
							root: 'data'
						},
						url: '/statistics/xListFarms',
						extraParams: { envId: Scalr.user.envId }
					}
				},
				matchFieldWidth: false,
				listConfig: {
					width: 'auto',
					minWidth: 200
				},
				listeners: {
					change: function(field, value) {
						panel.store.proxy.extraParams.farmId = value;
						panel.store.load();
					}
				}
			},'->', {
				xtype: 'button',
				width: 170,
				text: 'Download statistics',
				iconCls: 'scalr-ui-btn-icon-download',
				handler: function () {
					var params = Scalr.utils.CloneObject(panel.store.proxy.extraParams);
					params['action'] = 'download';
					Scalr.utils.UserLoadFile('/statistics/xListServersUsage?' + Ext.urlEncode(params));
				}
			}]
		}]
	});
	panel.down('#farmId').store.load();
	panel.store.load();

	return panel;
});