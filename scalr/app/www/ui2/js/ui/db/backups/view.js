Scalr.regPage('Scalr.ui.db.backups.view', function (loadParams, moduleParams) {
	//TODO back-end part
	var panel = Ext.create('Ext.panel.Panel', {
		title: 'DB Backups',
		scalrOptions: {
			maximize: 'all'
			//reload: false
		},
		dataStore: {},
		scalrReconfigureParams: {},
		autoScroll: true,
		items: [{
			xtype: 'db.backup.monthcalendar',
			itemId: 'dbbackupsScalrMonthcalendar'
		}, {
			xtype: 'db.backup.daycalendar',
			itemId: 'dbbackupsScalrDaycalendar',
			hidden: true
		}],

		tools: [{
			xtype: 'favoritetool',
			favorite: {
				text: 'DB backups',
				href: '#/db/backups'
			}
		}],

		dockedItems: [{
			xtype: 'toolbar',
			dock: 'top',
			items: [{
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
				itemId: 'farmId',
				value: loadParams['farmId'] || 0,
				valueField: 'id',
				displayField: 'name',
				listeners: {
					change: function() {
						panel.getListbyDateAndSet({
							farmId: this.getValue()
						});
					}
				}
			}, ' ', {
				iconCls: 'x-tbar-page-prev',
				ui: 'paging',
				xtype: 'button',
				handler: function() {
					var dt = new Date(this.next().getValue());
					dt = Ext.Date.add(dt, Ext.Date.MONTH, -1);
					this.next().setValue(dt);
					panel.down('#dbbackupsScalrMonthcalendar').setCurrentDate(dt);
					panel.getListbyDateAndSet();
				}
			}, {
				xtype: 'monthfield',
				format: 'F Y',
				value: new Date(),
				itemId: 'dateSelector',
				width: 178,
				listeners: {
					select: function (picker, value) {
						panel.down('#dbbackupsScalrMonthcalendar').setCurrentDate(new Date(value));
						panel.getListbyDateAndSet();
					}
				}
			}, {
				iconCls: 'x-tbar-page-next',
				ui: 'paging',
				xtype: 'button',
				handler: function() {
					var dt = new Date(this.prev().getValue());
					dt = Ext.Date.add(dt, Ext.Date.MONTH, 1);
					this.prev().setValue(dt);
					panel.down('#dbbackupsScalrMonthcalendar').setCurrentDate(dt);
					panel.getListbyDateAndSet();
				}
			}, '-', {
                iconCls: 'x-tbar-loading',
                ui: 'paging',
                xtype: 'button',
                handler: function() {
                    panel.getListbyDateAndSet();
                }
            },' ', {
				hidden: true,
				xtype: 'button',
				text: 'Back to the month view',
				width: 160,
				itemId: 'monthView',
				handler: function () {
					this.up('panel').down('#dbbackupsScalrMonthcalendar').show();
					this.up('panel').down('#dbbackupsScalrDaycalendar').hide();
					this.hide();
				}
			}]
		}],
		getListbyDateAndSet: function () {
			Scalr.Request({
				url: '/db/backups/xGetListBackups',
				processBox: {
					type: 'action'
				},
				params: {
					time: panel.convertDateFromPicker(),
					farmId: panel.down('#farmId').getValue()
				},
				success: function (data, response, options) {
					if(data && data['backups']) {
						panel.down('#dbbackupsScalrMonthcalendar').setStoreData(data['backups'][panel.getCurrentMonthAndYear()]);
                        panel.down('#dbbackupsScalrDaycalendar').setStoreData(
                            panel.down('#dbbackupsScalrMonthcalendar').getStoreData(panel.down('#dbbackupsScalrDaycalendar').getCurrentDate())
                        );
						panel.dataStore = data['backups'];
					}
				}
			});
		},
		convertDateFromPicker: function () {
			if(this.down('#dateSelector')) {
				var value = this.down('#dateSelector').getValue();
				return new Date(value);
			} else return '';
		},
		convertDateForDayView: function (day) {
			if (this.down('#dateSelector')) {
				var value = this.down('#dateSelector').getValue();
				return new Date((value.getMonth()+1) + '/' + day + '/' + value.getFullYear());
			} else return '';
		},
		getCurrentMonthAndYear: function () {
			return Ext.Date.format(panel.down('#dbbackupsScalrMonthcalendar').getCurrentDate(),'n Y');
		},
		onBoxReady: function () {
			if(!moduleParams['backups'])
				panel.getListbyDateAndSet();
			else {
				panel.dataStore = moduleParams['backups'];
				panel.down( '#dbbackupsScalrMonthcalendar' ).setStoreData(moduleParams['backups'][panel.getCurrentMonthAndYear()]);
			}
			/*panel.on('resize', function () {
				console.log('e');
				//this.down('#dbbackupsScalrMonthcalendar').resizeCells(this.getHeight());
			});*/
			panel.body.on('click', function ( e, el, obj ) {
				if (e.getTarget('div.scalr-ui-dbbackups-cell-content')) {
					Scalr.event.fireEvent('redirect', '#/db/backups/details?backupId=' + e.getTarget('div.scalr-ui-dbbackups-cell-content').getAttribute('backupId'));
				}
				if (e.getTarget('div.scalr-ui-dbbackups-cell-title-right') && !e.getTarget('div.scalr-ui-dbbackups-cell-content')) {
					var currentMonthYear = panel.down('#dateSelector').getValue();
					panel.down('#dbbackupsScalrDaycalendar').setCurrentDate(
						panel.convertDateForDayView(
							e.getTarget('div.scalr-ui-dbbackups-cell-title-right').getElementsByTagName('div')[0].getAttribute('day')
						)
					);
					panel.down('#dbbackupsScalrDaycalendar').setStoreData(
						panel.down('#dbbackupsScalrMonthcalendar').getStoreData(panel.down('#dbbackupsScalrDaycalendar').getCurrentDate())
					);
					panel.down('#dbbackupsScalrMonthcalendar').hide();
					panel.down('#dbbackupsScalrDaycalendar').show();

					if (panel.down('#monthView')) {
						panel.down('#monthView').show();
					}
				}
			});
		}
	});
	return panel;
});