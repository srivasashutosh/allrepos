Scalr.regPage('Scalr.ui.farms.monitoring.view', function (loadParams, moduleParams) {
	function refreshStat(){
		Ext.each (panel.items.items, function (panelItem) {
			Ext.each(panelItem.items.items,function(windowItem){
				fillStatistic(
					panelItem.farm, 
					windowItem.down('#viewMode').type, 
					windowItem.down('#viewMode').text, 
					panelItem.role
				);
			});
		});
	}
	function fillStatistic(farm, watchername, type, role){
		if(panel.down('#' + watchername + farm + role).body) panel.down('#' + watchername + farm + role).body.update('<div style="position: relative; top: 48%; text-align: center; width: 100%; height: 50%;"><img src = "/ui2/images/icons/anim/loading_16x16.gif">&nbsp;Loading...</div>');
		panel.down('#' + watchername + farm + role).html = '<div style="position: relative; top: 48%; text-align: center; width: 100%; height: 50%;"><img src = "/ui2/images/icons/anim/loading_16x16.gif">&nbsp;Loading...</div>';
		Scalr.Request({
			scope: this,
			url: '/server/statistics.php?version=2&task=get_stats_image_url&farmid=' + farm + '&watchername=' + watchername + '&graph_type=' + type + '&role=' + role,
			success: function (data, response, options) {
				panel.down('#' + watchername + farm + role).body.update('<img src = "' + data.msg + '">');
				
			},
			failure: function(data, response, options){
				panel.down('#' + watchername + farm + role).removeDocked(panel.down('#' + watchername + farm + role).down('#viewMode'), true);
				panel.down('#' + watchername + farm + role).addDocked({
						xtype: 'toolbar',
						dock: 'top',
						items: [{
							text: type,
							type: watchername,
							itemId: 'viewMode',
							menu: [{
								text: 'Daily',
								itemId: 'daily' + id,
								checked: type == 'daily'? true: '',
								listeners: {
									click: function(item, e, opt){
										fillStatistic(farm, watchername, 'daily', role);
									}
								}
							},{
								text: 'Weekly',
								itemId: 'weekly' + id,
								checked: type == 'weekly'? true: '',
								listeners: {
									click: function(item, e, opt){
										fillStatistic(farm, watchername, 'weekly', role);
									}
								}
							},{
								text: 'Monthly',
								itemId: 'monthly' + id,
								checked: type == 'monthly'? true: '',
								listeners: {
									click: function(item, e, opt){
										fillStatistic(farm, watchername, 'monthly', role);
									}
								}
							},{
								text: 'Yearly',
								itemId: 'yearly' + id,
								checked: type == 'yearly'? true: '',
								listeners: {
									click: function(item, e, opt){
										fillStatistic(farm, watchername, 'yearly', role);
									}
								}
							}]
						}]
					
				});
				panel.down('#' + watchername + farm + role).body.update('<div style="position: relative; top: 48%; text-align: center; width: 100%; height: 50%;"><font color = "red">' + data.msg + '</font></div>');
			}
		});
	}
	function newStatistic(record){
		var role;
		var farm;
		if(record.get('parentId') == 'root'){
			role = 'FARM';
			farm = record.get('id');
		}
		else{
			if(record.parentNode.get('parentId') == 'root')
				farm = record.get('parentId');
			else
				farm = record.parentNode.get('parentId');
			role = record.get('id');
		}
		panel.add({
			xtype: 'panel',
			farm: farm,
			role: role,
			itemId: record.get('text') + record.get('id'),
			layout: panel.down('#compareMode').checked ?
				{ type: 'anchor' } :
				{ type: 'table', columns: 2 },
			defaults: {
				width: 550,
				bodyCls: 'x-panel-body-frame',
				margin: 5
			},
			items:[{
				xtype: 'panel',
				height: 410,
				title: record.get('text') + ' / Memory Usage',
				itemId: 'MEMSNMP' + farm + role,
				html: '<div style="position: relative; top: 48%; text-align: center; width: 100%; height: 50%;"><img src = "/ui2/images/icons/anim/loading_16x16.gif">&nbsp;Loading...</div>'
			},{
				xtype: 'panel', 
				height: 362,
				title: record.get('text') + ' / CPU Utilization',
				itemId: 'CPUSNMP' + farm + role,
				html: '<div style="position: relative; top: 48%; text-align: center; width: 100%; height: 50%;"><img src = "/ui2/images/icons/anim/loading_16x16.gif">&nbsp;Loading...</div>'
			},{
				xtype: 'panel', 
				height: 274,
				title: record.get('text') + ' / Network Usage',
				itemId: 'NETSNMP' + farm + role,
				html: '<div style="position: relative; top: 48%; text-align: center; width: 100%; height: 50%;"><img src = "/ui2/images/icons/anim/loading_16x16.gif">&nbsp;Loading...</div>'
			},{
				xtype: 'panel', 
				height: 327,
				title: record.get('text') + ' / Load averages',
				itemId: 'LASNMP' + farm + role,
				html: '<div style="position: relative; top: 48%; text-align: center; width: 100%; height: 50%;"><img src = "/ui2/images/icons/anim/loading_16x16.gif">&nbsp;Loading...</div>'
			}]
		});
		
		// fix horizontal scrollbar
		/*panel.body.child('.x-box-inner').applyStyles({
			overflow: 'auto'
		});*/
		
		fillStatistic(farm, 'MEMSNMP', 'daily', role);
		fillStatistic(farm, 'CPUSNMP', 'daily', role);
		fillStatistic(farm, 'NETSNMP', 'daily', role);
		fillStatistic(farm, 'LASNMP', 'daily', role);
	}
	var panel = Ext.create('Ext.panel.Panel',{
		title: 'Farms &raquo; Monitoring',
		bodyCls: 'x-panel-body-frame',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		layout: {
			type: 'hbox',
			align: 'top'
		},
		autoScroll: true,
		dockedItems: [{
			dock: 'left',
			xtype: 'treepanel',
			headerPosition: 'left',
			itemId: 'tree',
			width: 300,
			rootVisible: false,
			store: {
				root:{
					text: 'Monitoring',
					expanded: true,
					children: moduleParams['children']
				}
			},
            dockedItems:[{
				xtype: 'toolbar',
				dock: 'top',
				layout: {
					type: 'hbox',
					pack: 'start'
				},
				items:[{
					xtype: 'button',
					text: 'Filter',
					itemId: 'filter',
					iconCls: 'scalr-ui-btn-icon-filter'
				}, ' ', {
					xtype: 'checkbox',
					boxLabel: 'Compare Mode', 
					itemId: 'compareMode',
					listeners: {
						change: function(field, newValue, oldValue, opt){
							if (newValue){
								node = panel.down('#tree').getChecked();
    							if(node.length)
								panel.remove((node[0].get('text') + node[0].get('id')));
								newStatistic(node[0]);
							}
							else{
								panel.removeAll(true);
								arr = panel.down('#tree').getChecked();
								node = arr[0];
								if(panel.down('#tree').getSelectionModel().getLastSelected().get('checked')) node = panel.down('#tree').getSelectionModel().getLastSelected();
								for(i =0; i < arr.length; i++){
									arr[i].set('checked', false);
								}
								node.set('checked', true);
								newStatistic(node);
							}
						}
					}
				}, ' ', {
					xtype: 'checkbox', 
					boxLabel: 'Auto Refresh',
					listeners: {
						change: function(field, newValue, oldValue, opt){
							if(newValue){
								taskManager = {
									run: refreshStat,
    								interval: 60000
								}
								Ext.TaskManager.start(taskManager);
							}
							else{
								Ext.TaskManager.stop(taskManager);
							}
						}
					}
				}]
			}],
            listeners: {
    			itemclick: function( view, record, item, index, e, options ){
    				if(!panel.down('#compareMode').checked){
    					panel.removeAll(true);
    					newStatistic(record);
    					node = panel.down('#tree').getChecked();
    					if(node.length)
							node[0].set('checked', false);
						record.set('checked', true);
    				}
    			},
    			checkchange: function(node, check, opt){
    				if(!panel.down('#compareMode').checked)
						node.set('checked', false);
					if(panel.down('#compareMode').checked && check) newStatistic(node);
					else if(panel.down('#compareMode').checked && !check){
						if(panel.down('#tree').getChecked().length!=0)
							panel.remove((node.get('text') + node.get('id')));
						if(panel.down('#tree').getChecked().length==0)
							node.set('checked', true);
					}
    			},
    			afterrender: function(component, opt){
    				if(loadParams['farmId']){
    					if(component.getStore().tree.getNodeById(loadParams['farmId']).getPath()){
    						component.selectPath(component.getStore().tree.getNodeById(loadParams['farmId']).getPath());
    						component.getStore().tree.getNodeById(loadParams['farmId']).set('checked', true);
    						newStatistic(component.getStore().tree.getNodeById(loadParams['farmId']));
    					}
    				}
    			}
            }
		}]
	});
	var taskManager;
	return panel;
});
