Scalr.regPage('Scalr.ui.monitoring.view', function (loadParams, moduleParams) {
	var panel = Ext.create('Ext.panel.Panel', {
		title: 'Farms &raquo; Monitoring',

		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		layout: {
			type: 'hbox',
			align: 'top'
		},
		autoScroll: true,

		compareMode: false,

		dockedItems: [{
			dock: 'left',
			xtype: 'treepanel',
			itemId: 'tree',
			width: 300,
			margin: '5 5 0 0',
			rootVisible: false,
			store: {
				fields: [ 'itemId', 'value', 'text' ],
				root: {
					text: 'Monitoring',
					expanded: true,
					children: moduleParams['children']
				}
			},
            listeners: {
    			itemclick: function( view, record, item, index, e, options ) {
    				if(!panel.compareMode){
    					panel.removeAll(true);
						panel.newStatistics(record);
    					var node = panel.down('#tree').getChecked();
    					if(node.length)
							node[0].set('checked', false);
						record.set('checked', true);
    				}
    			},
    			checkchange: function(node, check, opt) {
    				if(!panel.compareMode)
						node.set('checked', false);
					if(panel.compareMode && check)
						panel.newStatistics(node);
					else if(panel.compareMode && !check){
						if(panel.down('#tree').getChecked().length!=0)
							panel.remove((node.get('text') + node.get('id')));
						if(panel.down('#tree').getChecked().length==0)
							node.set('checked', true);
					}
    			},
    			afterrender: function(component, opt) {
    				var loadPath = '';
    				if( loadParams['server_index'] && loadParams['role'] && component.getRootNode().findChild('itemId', ('INSTANCE_' + loadParams['role'] + '_' + loadParams['server_index']), true) )
    					loadPath = 'INSTANCE_' + loadParams['role'] + '_' + loadParams['server_index'];
    				else {
    					if(loadParams['role'] && component.getRootNode().findChild('itemId', loadParams['role'], true) )
	    					loadPath = loadParams['role'];
    					else {
    						if(loadParams['farmId'] && component.getRootNode().findChild('itemId', loadParams['farmId'], true) )
		    					loadPath = loadParams['farmId'];
    					}
    				}
    				if(loadPath != '') {
    					component.selectPath(component.getRootNode().findChild('itemId', loadPath, true).getPath());
		    			component.getRootNode().findChild('itemId', loadPath, true).set('checked', true);
		    			panel.newStatistics(component.getRootNode().findChild('itemId', loadPath, true));
    				}
    			}
            }
		}, {
			xtype: 'toolbar',
			dock: 'top',
			layout: {
				type: 'hbox',
				pack: 'start'
			},
			items:[{
				xtype: 'filterfield',
				handler: function(field, value) {
					var treepanel = this.up('panel').down('treepanel');

					Ext.each(treepanel.getRootNode().childNodes, function(farmItem) {
						farmItem.cascadeBy(function(){
							var el = Ext.get(treepanel.getView().getNodeByRecord(this));
							el.setVisibilityMode(Ext.Element.DISPLAY);
							if(this.get('text').search(value) != -1 || value == '')
								el.setVisible(true);
							else
								el.setVisible(false);
						});
					});
				}
			}, ' ', {
				xtype: 'button',
				enableToggle: true,
				width: 120,
				itemId: 'compareMode',
				text: 'Compare Mode',
				toggleHandler: function (field, checked) {
					if (checked) {
						panel.compareMode = true;
						var node = panel.down('#tree').getChecked();
						if(node.length) {
							panel.remove((node[0].get('text') + node[0].get('id')));
							panel.newStatistics(node[0]);
						}
					} else{
						panel.compareMode = false;
						panel.removeAll(true);
						if(panel.down('#tree').getChecked().length) {
							var arr = panel.down('#tree').getChecked();
							node = arr[0];
							if(panel.down('#tree').getSelectionModel().getLastSelected().get('checked'))
								node = panel.down('#tree').getSelectionModel().getLastSelected();
							for(var i = 0; i < arr.length; i++) {
								arr[i].set('checked', false);
							}
							node.set('checked', true);
							panel.newStatistics(node);
						}
					}
				}
			}, ' ', {
				xtype: 'button',
				enableToggle: true,
				width: 120,
				text: 'Auto Refresh',
				pressed: true,
				toggleHandler: function (field, checked) {
					if(checked)
						Ext.TaskManager.start(taskManager);
					else
						Ext.TaskManager.stop(taskManager);
				}
			}]
		}],

		listeners: {
			resize: function() {
				// ???
				//panel.body.child('.x-box-inner').applyStyles({width: '100%', height: '100%', overflow: 'hidden'});
			}
		},

		refreshStat: function () {
			Ext.each (this.items.items, function (panelItem) {
				Ext.each(panelItem.items.items,function(windowItem) {
					windowItem.fillByStatistics();
				});
			});
		},

		newStatistics: function (record) {
			if(!panel.compareMode)
				panel.removeAll();
			panel.add({
				xtype: 'monitoring.statisticspanel',
				compareMode: panel.compareMode,
				params: this.paramsForStatistics(record),
				itemId: record.get('text') + record.get('id')
			});
		},

		paramsForStatistics: function (record) {
			var role;
			var farm;
			var panelTitle = '';
			if(record.get('parentId') == 'root') {
				role = 'FARM';
				farm = record.raw.itemId;
				panelTitle = record.get('value');
			}
			else {
				if(record.parentNode.get('parentId') == 'root') {
					farm = record.parentNode.get('itemId');
					panelTitle += record.parentNode.get('value') + '&nbsp;&rarr;&nbsp;';
				}
				else {
					farm = record.parentNode.parentNode.get('itemId');
					panelTitle += record.parentNode.parentNode.get('value') + '&nbsp;&rarr;&nbsp;' + record.parentNode.get('value');
				}
				role = record.raw.itemId;
				panelTitle += record.get('value');
			}
			return {
				role: role,
				farm: farm,
				panelTitle: panelTitle
			};
		}
	});
	var taskManager = {
		run: function() {
			panel.refreshStat();
		},
		interval: 60000
	};
	Ext.TaskManager.start(taskManager);
	return panel;
});