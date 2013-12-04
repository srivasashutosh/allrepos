Scalr.regPage('Scalr.ui.farms.events.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [
			'id','dtadded', 'type', 'message', 'event_id', 'scripts',
			
			'event_server_id', 
			'event_farm_name', 
			'event_farm_id', 
			'event_role_id', 
			'event_farm_roleid',
			'event_role_name',
			'event_server_index'
		],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/farms/events/xListEvents'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Farms &raquo; ' + moduleParams['farmName'] + ' &raquo; Events',
		scalrOptions: {
			maximize: 'all'
		},
		scalrReconfigureParams: { farmId: '' },
		store: store,
		stateId: 'grid-farms-events-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}],

		viewConfig: {
			emptyText: "No events found",
			loadingText: 'Loading events ...'
		},

		columns: [
			{ header: "Date", width: 150, dataIndex: 'dtadded', sortable: false },
			{ header: "Event", width: 200, dataIndex: 'type', sortable: false },
			{ header: 'Fired by', flex: 1, dataIndex: 'event_server_id', sortable: false, xtype: 'templatecolumn', tpl:
				'<tpl if="event_farm_id">' +
					'<a href="#/farms/{event_farm_id}/view" title="Farm {event_farm_name}">{event_farm_name}</a>' +
					'<tpl if="event_role_name">' +
						'&nbsp;&rarr;&nbsp;<a href="#/farms/{event_farm_id}/roles/{event_farm_roleid}/view" title="Role {event_role_name}">{event_role_name}</a> ' +
					'</tpl>' +
					'<tpl if="!event_role_name">' +
						'&nbsp;&rarr;&nbsp;*removed role*&nbsp;' +
					'</tpl>' +
					'#<a href="#/servers/{event_server_id}/view">{event_server_index}</a>'+
				'<tpl else><img src="/ui2/images/icons/false.png"></tpl>'
			},
			{ header: "Executed scripts", width: 150, dataIndex: 'scripts', sortable: false, xtype: 'templatecolumn', tpl:
				'<tpl if="scripts &gt; 0">'+
				'{scripts} [<a href="#/logs/scripting?eventId={event_id}">Logs</a>]'+
				'<tpl else><img src="/ui2/images/icons/false.png"></tpl>'
			},
			{ header: "Details", flex: 3, dataIndex: 'message', sortable: false }
		],

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			items: [{
				xtype: 'filterfield',
				store: store
			}, ' ', {
				xtype: 'button',
				text: 'Configure event notifications',
				handler: function () {
					document.location.href = '#/farms/events/configure?farmId=' + store.proxy.extraParams.farmId;
				}
			}]
		}]
	});
});
