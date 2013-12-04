Ext.define('Scalr.ui.dashboard.Column', {
	extend: 'Ext.container.Container',
	alias: 'widget.dashboard.column',

	cls: 'scalr-ui-dashboard-container',
	index: 0,
	initComponent: function () {
		this.callParent();
		this.html =
			'<div class = "editpanel">' +
				'<div class = "add" style= "height: 55px;" align="center" index=' + this.index + '>' +
				'<div class="scalr-ui-dashboard-icon-add-widget"></div>Add widget' +
				'</div>' +
				'<div class = "remove" style= "height: 55px;" align="center">' +
				'<div class="scalr-ui-dashboard-icon-remove-column"></div>Remove column' +
				'</div>' +
			'</div>';
	}

});
Ext.define('Scalr.ui.dashboard.Panel', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashpanel',

	cls: 'scalr-ui-dashboard-panel',
	defaultType: 'dashboard.column',
	autoScroll: true,
	border: false,

	layout: {
		type : 'column'
	},

	initComponent : function() {
		this.callParent();

		this.addEvents({
			validatedrop: true,
			beforedragover: true,
			dragover: true,
			beforedrop: true,
			drop: true
		});

		this.on('drop',
			function (dropObject, e) {
				dropObject.panel.setPosition(0, 0);
				//this.savePanel();
				this.doLayout();
			},
		this);
	},

	// private
	initEvents : function(){
		this.callParent();
		this.dd = Ext.create('Scalr.ui.dashboard.DropZone', this, this.dropConfig);
	},

	// private
	beforeDestroy : function() {
		if (this.dd) {
			this.dd.unreg();
		}
		Scalr.ui.dashboard.Panel.superclass.beforeDestroy.call(this);
	},

	updateColWidth: function () {
		var items = this.layout.getLayoutItems(),
			len = items.length,
			i = 0,
			j = 0,
			total = 0,
			item;
		if (items[0] && items[0].up()) {
			for (; i < len; i++) {  ///columns
				item = items[i];
				item.columnWidth = parseFloat((1 / len).toFixed(2));
				total += item.columnWidth;
			}
			if (items[i - 1]) {
				items[i - 1].margin = 0;
				if (total < 1)
					items[i - 1].columnWidth += 1 - total;
			}
		}
	},

	newCol: function (index) {
		this.add({
			layout: 'anchor',
			index: index || 0,
			margin: '0 5 0 0'
		});
	},

	newWidget: function(type, params) {
		return {
			xtype: type,
			collapsible: true,
			draggable: true,
			addTools: this.setTools,
			layout: 'fit',
			anchor: '100%',
			params: params,
			margin: '0 0 5 0'
		};
	},
	setTools: function() { //function for all moduls
		var me = this.up('dashpanel');
		if (this.showSettingsWindow)
			this.tools.push({
				xtype: 'tool',
				type: 'settings',
				handler: function () {
					this.up().up().showSettingsWindow();
				}
			});
		this.tools.push({
			xtype: 'tool',
			type: 'close',
			handler: function(e, toolEl, closePanel) {
				Scalr.Confirm({
					msg: 'Are you sure you want to remove this widget from dashboard?',
					type: 'action',
					success: function() {
						var p = closePanel.up();
						p.el.animate({
							opacity: 0,
							callback: function(){
								p.fireEvent('close', p);
								p[this.closeAction]();
								me.savePanel();
							},
							scope: p
						});
					}
				});
			}
		});
	}
});
Ext.define('Scalr.ui.dashboard.DropZone', {
	extend: 'Ext.dd.DropTarget',

	constructor: function(dash, cfg) {
		this.dash = dash;
		Ext.dd.ScrollManager.register(dash.body);
		Scalr.ui.dashboard.DropZone.superclass.constructor.call(this, dash.body, cfg);
		dash.body.ddScrollConfig = this.ddScrollConfig;
	},

	ddScrollConfig: {
		vthresh: 50,
		hthresh: -1,
		animate: true,
		increment: 200
	},

	createEvent: function(dd, e, data, col, c, pos) {
		return {
			dash: this.dash,
			panel: data.panel,
			columnIndex: col,
			column: c,
			position: pos,
			data: data,
			source: dd,
			rawEvent: e,
			status: this.dropAllowed
		};
	},

	notifyOver: function(dd, e, data) {
		var xy = e.getXY(),
			dash = this.dash,
			proxy = dd.proxy;

		// case column widths
		if (!this.grid) {
			this.grid = this.getGrid();
		}
		// handle case scroll where scrollbars appear during drag
		var cw = dash.body.dom.clientWidth;
		if (!this.lastCW) {
			// set initial client width
			this.lastCW = cw;
		} else if (this.lastCW != cw) {
			// client width has changed, so refresh layout & grid calcs
			this.lastCW = cw;
			//dash.doLayout();
			this.grid = this.getGrid();
		}

		// determine column
		var colIndex = 0,
			colRight = 0,
			cols = this.grid.columnX,
			len = cols.length,
			cmatch = false;

		for (len; colIndex < len; colIndex++) {
			colRight = cols[colIndex].x + cols[colIndex].w;
			if (xy[0] < colRight) {
				cmatch = true;
				break;
			}
		}
		// no match, fix last index
		if (!cmatch) {
			colIndex--;
		}

		// find insert position
		var overWidget, pos = 0,
			h = 0,
			match = false,
			overColumn = dash.items.getAt(colIndex),
			widgets = overColumn.items.items,
			overSelf = false;
		//overColumn.addCls('scalr-ui-dashboard-container-dd');

		len = widgets.length;

		for (len; pos < len; pos++) {
			overWidget = widgets[pos];
			h = overWidget.el.getHeight();
			if (h === 0) {
				overSelf = true;
			} else if ((overWidget.el.getY() + (h / 2)) > xy[1]) {
				match = true;
				break;
			}
		}

		pos = (match && overWidget ? pos : overColumn.items.getCount()) + (overSelf ? -1 : 0);
		var overEvent = this.createEvent(dd, e, data, colIndex, overColumn, pos);

		if (dash.fireEvent('validatedrop', overEvent) !== false && dash.fireEvent('beforedragover', overEvent) !== false) {

			// make sure proxy width is fluid in different width columns
			proxy.getProxy().setWidth('auto');

			if (overWidget) {
				dd.panelProxy.moveProxy(overWidget.el.dom.parentNode, match ? overWidget.el.dom : null);
			} else {
				dd.panelProxy.moveProxy(overColumn.el.dom, null);
			}

			this.lastPos = {
				c: overColumn,
				col: colIndex,
				p: overSelf || (match && overWidget) ? pos : false
			};
			this.scrollPos = dash.body.getScroll();

			dash.fireEvent('dragover', overEvent);
			return overEvent.status;
		} else {
			return overEvent.status;
		}
	},

	notifyOut: function() {
		delete this.grid;
	},

	notifyDrop: function(dd, e, data) {
		delete this.grid;
		if (!this.lastPos) {
			return;
		}
		var c = this.lastPos.c,
			col = this.lastPos.col,
			pos = this.lastPos.p,
			panel = dd.panel,
			dropEvent = this.createEvent(dd, e, data, col, c, pos !== false ? pos : c.items.getCount());

		if (this.dash.fireEvent('validatedrop', dropEvent) !== false &&
			this.dash.fireEvent('beforedrop', dropEvent) !== false) {

			Ext.suspendLayouts();

			// make sure panel is visible prior to inserting so that the layout doesn't ignore it
			panel.el.dom.style.display = '';
			dd.proxy.hide();
			dd.panelProxy.hide();
			var parentCol = panel.up();
			if (pos !== false) {
				c.insert(pos, panel);
			} else {
				c.add(panel);
			}

			Ext.resumeLayouts(true);
			this.dash.fireEvent('drop', dropEvent);

			// scroll position is lost on drop, fix it
			var st = this.scrollPos.top;
			if (st) {
				var d = this.dash.body.dom;
				setTimeout(function() {
						d.scrollTop = st;
					},
					10);
			}
		}
		delete this.lastPos;
		if (parentCol != c)
			panel.up('dashpanel').savePanel(0);
		return true;
	},

	// internal cache of body and column coords
	getGrid: function() {
		var box = this.dash.body.getBox();
		box.columnX = [];
		this.dash.items.each(function(c) {
			box.columnX.push({
				x: c.el.getX(),
				w: c.el.getWidth()
			});
		});
		return box;
	},

	// unregister the dropzone from ScrollManager
	unreg: function() {
		Ext.dd.ScrollManager.unregister(this.dash.body);
		Scalr.ui.dashboard.DropZone.superclass.unreg.call(this);
	}
});

Ext.define('Scalr.ui.dashboard.Farm', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.farm',

	title: 'Farm servers',
	bodyStyle: 'background-color: #F5F5F5; padding: 10px',
	layout: 'fit',
	defaults: {
		anchor: '100%'
	},
	items: [{
		xtype: 'dataview',
		store: {
			fields: [ 'behaviors', 'group', 'servCount', 'farmRoleId', 'farmId', 'roleId'],
			proxy: 'object'
		},
		border: true,
		deferEmptyText: false,
		emptyText: 'No servers running',
		loadMask: false,
		itemSelector: 'div.scalr-ui-dashboard-farms-servers',
		tpl: new Ext.XTemplate(
			'<ul class="scalr-ui-dashboard-farms" align="center">' +
				'<tpl for=".">' +
				'<li>' +
				'<a href="#/farms/{farmId}/roles/{farmRoleId}/view"><div class="icon" ><img src="/ui2/images/ui/dashboard/behaviors/{[this.getLocationIcon(values)]}.png" title="{behaviors}"/></div></a>' +
				'<a href="#/servers/view?farmId={farmId}&farmRoleId={farmRoleId}"><div class="count">{servCount}</div></a>' +
				'<p class="scalr-ui-dashboard-farms-text" style="margin-top: 11px;">{[this.getBehaviorName(values)]}</p>' +
				'</li>' +
				'</tpl>' +
				'</ul>',
			{
				getBehaviorName: function (values) {
					if (values['behaviors'].length < 10)
						return values['behaviors'];
					else {
						return Ext.util.Format.substr(values['behaviors'], 0, 4) + '...' + Ext.util.Format.substr(values['behaviors'], values['behaviors'].length - 3, 3);
					}

				},
				getLocationIcon: function (context) {
                    // TODO: rewrite to plugin (use the same code as in farm builder)
                    var behaviors = [
                        "cf_cchm", "cf_dea", "cf_router", "cf_service",
                        "rabbitmq", "www",
                        "app", "tomcat", 'haproxy',
                        "mysqlproxy",
                        "memcached",
                        "cassandra", "mysql", "mysql2", "percona", "postgresql", "redis", "mongodb"
                    ];

                    if (context['behaviors']) {
                        //Handle CF all-in-one role
                        if (context['behaviors'].match("cf_router") && context['behaviors'].match("cf_cloud_controller") && context['behaviors'].match("cf_health_manager") && context['behaviors'].match("cf_dea")) {
                            return 'cf_all_in_one';
                        }
                        //Handle CF CCHM role
                        if (context['behaviors'].match("cf_cloud_controller") || context['behaviors'].match("cf_health_manager")) {
                            return 'cf_cchm';
                        }

                        var b = (context['behaviors'] || '').split(',');
                        for (var i=0, len=b.length; i < len; i++) {
                            for (var k = 0; k < behaviors.length; k++ ) {
                                if (behaviors[k] == b[i]) {
                                    return b[i].replace('-', '_');
                                }
                            }
                        }
                    }

                    return 'base';
				}
			})
	}],
	widgetType: 'local',
	widgetUpdate: function (content) {
		if (content['servers'])
			this.down('dataview').store.load({
				data: content['servers']
			});
		this.title = 'Farm ' + content['name'];
	}
});

Ext.define('Scalr.ui.dashboard.Monitoring', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.monitoring',

	title: 'Monitoring',
	widgetType: 'local',
	widgetUpdate: function (content) {
		this.title = this.params['title'];

		if (this.params['height'])
			this.setHeight(this.params['height'] - 28);
		if (content && content['msg']) {
			if (content['type'] && content['type'] == 'error' || content['success'] == false) {
				if(this.body)
					this.body.update('<div style="position: relative; top: 48%; text-align: center; width: 100%; height: 50%;"><font color = "red">' + content.msg + '</font></div>');
				else
					this.html = '<div style="position: relative; top: 48%; text-align: center; width: 100%; height: 50%;"><font color = "red">' + content.msg + '</font></div>';
			}
			else {
				if (this.body)
					this.body.update('<div style="position: relative; text-align: center; width: 100%; height: 50%; padding: 3px;"><img src = "' + content.msg + '"/></div>');
				else
					this.html = '<div style="position: relative; text-align: center; width: 100%; height: 50%; padding: 3px;"><img src = "' + content.msg + '"/></div>';
			}
		}
		else {
			if (this.body)
				this.body.update('<div style="position: relative; text-align: center; width: 100%; height: 50%; padding: 3px;"><font color = "red">No info</font></div>');
			else
				this.html = '<div style="position: relative; text-align: center; width: 100%; height: 50%; padding: 3px;"><font color = "red">No info</font></div>';
		}
	}
});

Ext.define('Scalr.ui.dashboard.Uservoice', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.uservoice',

	title: 'Uservoice feedback',
	cls: 'scalr-ui-dashboard-widgets-feedback',

	items: {
		xtype: 'grid',
		store: {
			fields: [ 'url', 'id', 'state', 'title', 'vote_count', 'status'],
			proxy: 'object',
			data: []
		},
		columns: [{
			header: 'Votes',
			width: 68,
			hideable: false,
			xtype: 'templatecolumn',
			dataIndex: 'vote_count',
			tpl: '<span style="font-size: 13px; font-weight: bold; color: #333">{vote_count}</span>'
		}, {
			header: 'Suggestion',
			flex: 3,
			hideable: false,
			xtype: 'templatecolumn',
			dataIndex: 'title',
			tpl: '<a href={url} target="_blank" style="font-size: 13px;">{title}</a>'
		}, {
			header: 'Status',
			width: 106,
			hideable: false,
			xtype: 'templatecolumn',
			dataIndex: 'state',
			tpl: new Ext.XTemplate('<div class="scalr-ui-dashboard-widgets-feedback-status scalr-ui-dashboard-widgets-feedback-status-{[this.getSugState(values)]}"><tpl if="status.name">{status.name}</tpl><tpl if="!status">no status</tpl></div>',
				{
					getSugState: function (values) {
						if (values['status'])
							return values['status']['key'];
						else
							return 'no';
					}
				})
		}],
		plugins: {
			ptype: 'gridstore'
		},
		viewConfig: {
			emptyText: 'No suggestions found',
			deferEmptyText: false,
			disableSelection: true,
			getRowClass: function(rec, rowIdx) {
				return rowIdx % 2 == 1 ? 'scalr-ui-dashboard-grid-row' : 'scalr-ui-dashboard-grid-row-alt';
			}
		}
	},
	widgetType: 'nonlocal',
	loadContent: function () {
		if (this.rendered)
			this.body.mask('Loading content ...');

		Scalr.Request({
			url: '/dashboard/widget/uservoice/xGetContent',
			scope: this,
			success: function (content) {
				if (this.isDestroyed)
					return;

				if (this.rendered)
					this.body.unmask();
				this.updateForm(content);
			},
			failure: function () {
				if (this.isDestroyed)
					return;

				if (this.rendered)
					this.body.unmask();
			}
		});
	},
	updateForm: function(content) {
		this.down('grid').store.loadData(content['sugs']);
	},
	listeners: {
		boxready: function() {
			this.feedbackPanel = this.el.createChild({
				tag: 'div',
				cls: 'scalr-ui-dashboard-widgets-feedbackpanel',
				html: '<div class="scalr-ui-dashboard-widgets-message" style="margin-left: 5px; float: left;"> ' +
					'<a href="http://scalr.uservoice.com/" target="_blank" class="simple">Feedback</a>' +
					'</div>'
			});

			if (!this.collapsed)
				this.loadContent();
		},
		beforeexpand: function() {
			if (this.rendered)
				this.body.mask();
		},
		expand: function() {
			this.loadContent();
		}
	},

/*
	widgetUpdate: function (content) {
		if (!this.params || !this.params['sugCount'])
			this.params = {'sugCount': 5};
		this.store.load({
			data: content
		});
	},
	*/


	showSettingsWindow: function () {
		if (!this.params || !this.params['sugCount'])
			this.params = {'sugCount': 5};
		Scalr.Confirm({
			form: [{
				xtype: 'combo',
				margin: 5,
				store: [5, 10],
				fieldLabel: 'Number of suggestions:',
				labelWidth: 150,
				editable: false,
				value: this.params['sugCount'],
				queryMode: 'local',
				name: 'sugCount',
				anchor: '100%'
			}],
			title: 'Settings',
			success: function (data) {
				if (data['sugCount']) {
					this.params['sugCount'] = data['sugCount'];
					this.up('dashpanel').savePanel(1);
				}
			},
			scope: this
		});
	}
});

Ext.define('Scalr.ui.dashboard.Announcement', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.announcement',

	title: 'Announcements',
	items: {
		xtype: 'dataview',
		store: {
			fields: ['time','text', 'url', 'newS'],
			proxy: 'object'
		},
		deferEmptyText: false,
		emptyText: 'No news',
		loadMask: false,
		itemSelector: 'div.scalr-ui-dashboard-widgets-div',
		tpl: new Ext.XTemplate(
			'<tpl for=".">',
			'<div class="scalr-ui-dashboard-widgets-div',
			'<tpl if="xindex%2==1"> scalr-ui-dashboard-widgets-panelcolor</tpl>',
			'">',
			'<div class="scalr-ui-dashboard-widgets-desc">{time}</div>',
			'<div>' +
				'<a href="{url}" target="_blank"><span class="scalr-ui-dashboard-widgets-message-slim">{text}</span></a>' +
				'<tpl if="newS"><span style="margin-left: 5px; cursor: pointer;" class="scalr-ui-dashboard-widgets-info">New</span></tpl>' +
				'</div>',
			'</div>',
			'</tpl>'
		)
	},
	widgetType: 'local',
	widgetUpdate: function (content) {
		if (!this.params || !this.params['newsCount'])
			this.params = {'newsCount': 5};
		this.down('dataview').store.load({
			data: content
		});
	},
	showSettingsWindow: function () {
		if (!this.params || !this.params['newsCount'])
			this.params = {'newsCount': 5};
		Scalr.Confirm({
			form: [{
				xtype: 'combo',
				margin: 5,
				store: [1, 2, 5, 10],
				fieldLabel: 'Number of news:',
				labelWidth: 120,
				editable: false,
				value: this.params['newsCount'],
				queryMode: 'local',
				name: 'newsCount',
				anchor: '100%'
			}],
			title: 'Settings',
			success: function (data) {
				if (data['newsCount']) {
					this.params['newsCount'] = data['newsCount'];
					this.up('dashpanel').savePanel(1);
				}
			},
			scope: this
		});
	}
});

Ext.define('Scalr.ui.dashboard.LastErrors', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.lasterrors',

	title: 'Last errors',
	autoScroll: true,
	items: {
		xtype: 'dataview',
		store: {
			fields: [ 'message', 'time', 'server_id' ],
			proxy: 'object'
		},
		deferEmptyText: false,
		emptyText: 'No errors',
		loadMask: false,
		itemSelector: 'div.scalr-ui-dashboard-widgets-div',
		tpl: new Ext.XTemplate(
			'<tpl for=".">',
			'<div title = "{message}" class="scalr-ui-dashboard-widgets-div',
			'<tpl if="xindex%2==1"> scalr-ui-dashboard-widgets-panelcolor</tpl>',
			'">',
			'<div class="scalr-ui-dashboard-widgets-desc"><tpl if="server_id"><a href="#/servers/{server_id}/extendedInfo">{time}</a><tpl else>{time}</tpl></div>',
			'<div style="max-height: 60px; overflow: hidden;"><span class="scalr-ui-dashboard-widgets-message-slim">{message}</span></div>',
			'</div>',
			'</tpl>'
		)
	},
	widgetType: 'local',
	widgetUpdate: function (content) {
		if (!this.params || !this.params['errorCount'])
			this.params = {'errorCount': 10};
		this.down('dataview').store.load({
			data: content
		});
	},
	showSettingsWindow: function () {
		if (!this.params || !this.params['errorCount'])
			this.params = {errorCount: 10};
		Scalr.Confirm({
			form: [{
				xtype: 'combo',
				margin: 5,
				store: [5, 10, 15, 20, 50, 100],
				fieldLabel: 'Number of errors:',
				labelWidth: 120,
				editable: false,
				value: this.params['errorCount'],
				queryMode: 'local',
				name: 'errorCount',
				anchor: '100%'
			}],
			title: 'Settings',
			success: function (data) {
				if (data['errorCount']) {
					this.params['errorCount'] = data['errorCount'];
					this.up('dashpanel').savePanel(1);
				}
			},
			scope: this
		});
	}
});

Ext.define('Scalr.ui.dashboard.UsageLastStat', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.usagelaststat',

	title: 'Servers usage statistics',
	cls: 'scalr-ui-dashboard-widgets-usagelaststat',
	autoScroll: true,
	minHeight: 120,
	items: {
		xtype: 'gridpanel',
		border: false,
		store: {
			fields: ['farm', 'farm_id', 'current', 'recent'],
			proxy: 'object',
			data: []
		},
		features: [{
			ftype: 'summary'
		}],
		columns: [{
			header: 'Farm',
			hideable: false,
			xtype: 'templatecolumn',
			dataIndex: 'farm',
			flex: 3,
			tpl: '<a href="#/farms/{farm_id}/view">{farm}</a>',
			summaryRenderer: function(value) {
				return '<span style="color: #333;">Total spent:</span>';
			}
		}, {
			header: 'This month',
			hideable: false,
			xtype: 'templatecolumn',
			dataIndex: 'current',
			tpl: '<tpl if="current"><a href="#/statistics/serversusage?farmId={farm_id}">${current}</a></tpl><tpl if="!current"><img src="/ui2/images/icons/false.png" /></tpl>',
			summaryType: 'sum',
			summaryRenderer: function(value) {
				return Ext.String.format('<span style="color: #333;">${0}</span>', Ext.util.Format.round(value, 2));
			}
		}, {
			header: 'Last month',
			hideable: false,
			xtype: 'templatecolumn',
			dataIndex: 'recent',
			tpl: '<tpl if="recent"><a href="#/statistics/serversusage?farmId={farm_id}">${recent}</a></tpl><tpl if="!recent"><img src="/ui2/images/icons/false.png" /></tpl>',
			summaryType: 'sum',
			summaryRenderer: function(value) {
				return Ext.String.format('<span style="color: #333;">${0}</span>', Ext.util.Format.round(value, 2));
			}
		}],
		viewConfig: {
			emptyText: 'No statistics found',
			deferEmptyText: false,
			disableSelection: true,
			getRowClass: function(rec, rowIdx) {
				return rowIdx % 2 == 1 ? 'scalr-ui-dashboard-grid-row' : 'scalr-ui-dashboard-grid-row-alt';
			}
		},
		plugins: {
			ptype: 'gridstore'
		}
	},
	onBoxReady: function () {
		if (!this.params || !this.params['farmCount'])
			this.params = {'farmCount': 5};
		this.callParent();
	},
	widgetType: 'local',
	widgetUpdate: function (content) {
		if (content['farms']) {
			this.down('gridpanel').store.load({
				data: content['farms']
			});
		}
	},
	showSettingsWindow: function () {
		if (!this.params || !this.params['farmCount'])
			this.params = {'farmCount': 5};
		Scalr.Confirm({
			form: [{
				xtype: 'combo',
				margin: 5,
				store: [1, 2, 5, 10, 15, 20, 'all'],
				fieldLabel: 'Number of farms:',
				labelWidth: 120,
				editable: false,
				value: this.params['farmCount'],
				queryMode: 'local',
				name: 'farmCount',
				anchor: '100%'
			}],
			title: 'Settings',
			success: function (data) {
				if (data['farmCount']) {
					this.params['farmCount'] = data['farmCount'];
					this.up('dashpanel').savePanel(1);
				}
			},
			scope: this
		});
	}
});

Ext.define('Scalr.ui.dashboard.Billing', {
	extend: 'Ext.form.Panel',
	alias: 'widget.dashboard.billing',
	cls: 'scalr-ui-dashboard-widgets-billing',
	bodyCls: 'x-panel-body-frame',
	bodyStyle: 'background-color: whiteSmoke; padding: 21px 12px 16px 12px;',

	title: 'Billing',
	items: [{
		xtype: 'container',
		defaults: {
			labelWidth: 130,
			anchor: '100%'
		},
		items: [{
			xtype: 'displayfield',
			name: 'plan',
			fieldLabel: 'Plan'
		}, {
			xtype: 'displayfield',
			fieldLabel: 'Status',
			name: 'status'

		}, {
			xtype: 'displayfield',
			fieldLabel: 'Next charge',
			name: 'nextCharge'

		}, {
			xtype: 'displayfield',
			fieldLabel: '<a href="http://scalr.net/emergency_support/" target="_blank">Emergency support</a>',
			name: 'support',
			listeners: {
				boxready: function() {
					this.inputEl.on('click', function(e, el) {
						if (e.getTarget('a.dashed')) {
							var action = el.getAttribute('type');
							Scalr.Request({
								confirmBox: {
									type: 'action',
									msg: (action == 'subscribe') ? 'Are you sure want to subscribe to Emergency Support for $300 / month?' : 'Are you sure want to unsubscribe from Emergency Support?'
								},
								processBox: {
									type: 'action'
								},
								params: { action: action },
								scope: this,
								url: '/billing/xSetEmergSupport/',
								success: function () {
									Scalr.message.Success((action == 'subscribe') ? "You've successfully subscribed to Emergency support" : "You've successfully unsubscribed from emergency support");
									this.up('form').loadContent();
								}
							});
						}
					}, this);
				}
			}
		}]
	}],
	widgetType: 'nonlocal',
	updateForm: function(data) {
		var values = {};
		this.data = data;
		values['plan'] = data['productName'] + ' ( ' + data['productPrice'] + ' / month ) [<a href = "#/billing/changePlan">Change Plan</a>]';

		switch (data['state']) {
			case 'Subscribed':
				values['status'] = '<span style="color:green;font-weight:bold;">Subscribed</span>'; break;
			case 'Trial':
				values['status'] = '<span style="color:green;font-weight:bold;">Trial</span> (<b>' + data['trialDaysLeft'] + '</b> days left)'; break;
			case 'Unsubscribed':
				values['status'] = '<span style="color:red;font-weight:bold;">Unsubscribed</span> [<a href="#/billing/reactivate">Re-activate</a>]'; break;
			case 'Behind on payment':
				values['status'] = '<span style="color:red;font-weight:bold;">Behind on payment</span>'; break;
			default:
				values['status'] = data['state']; break;
		}

		if (data['ccType'])
			values['nextCharge'] = '$' + data['nextAmount'] + ' on ' + data['nextAssessmentAt'] + ' on ' + data['ccType'] + ' ' + data['ccNumber'] + ' [<a href="#/billing/updateCreditCard">Change card</a>]';
		else
			values['nextCharge'] = '$' + data['nextAmount'] + ' on ' + (data['nextAssessmentAt'] ? data['nextAssessmentAt'] : 'unknown') + ' [<a href="#/billing/updateCreditCard" class="dashed">Set credit card</a>]';


		if (data['emergSupport'] == 'included')
			values['support'] = '<span style="color:green;">Subscribed as part of ' + data['productName'] + ' package</span><a type="" style="display:none;"></a> '+ data['emergPhone'];
		else if (data['emergSupport'] == "enabled")
			values['support'] = '<span style="color:green;">Subscribed</span> ($300 / month) [<a type="unsubscribe" class="dashed">Unsubscribe</a>] ' + data['emergPhone'];
		else
			values['support'] = 'Not subscribed [<a type="subscribe" class="dashed">Subscribe for $300 / month</a>]';


		this.getForm().setValues(values);
	},
	listeners: {
		boxready: function() {
			if (!this.collapsed)
				this.loadContent();
		},
		beforeexpand: function() {
			if (this.rendered)
				this.body.mask();
		},
		expand: function() {
			this.loadContent();
		}
	},
	loadContent: function () {
		if (this.rendered)
			this.body.mask('Loading content ...');

		Scalr.Request({
			url: '/dashboard/widget/billing/xGetContent',
			scope: this,
			success: function (content) {
				if (this.rendered)
					this.body.unmask();
				this.updateForm(content);
			},
			failure: function () {
				if (this.rendered)
					this.body.unmask();
			}
		});
	}
});

Ext.define('Scalr.ui.dashboard.Status', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.status',

	title: 'AWS health status',
	params: {},

	items: [{
		xtype: 'gridpanel',
		store: {
			fields: ['img', 'status', 'name', 'message', 'locations', 'EC2', 'RDS', "S3"],
			proxy: 'object',
			remoteSort: true
		},
		columns: [{
			text: 'Location',
			flex: 2,
			xtype: 'templatecolumn',
			tpl: '<span style="font-size: 13px; color: #333">{locations}</span>'
		}, {
			text: 'EC2',
			xtype: 'templatecolumn',
			tpl: '<img src="/ui2/images/ui/dashboard/{EC2.img}" title="{EC2.status}">',
			flex: 1
		}, {
			text: 'RDS',
			xtype: 'templatecolumn',
			tpl: '<img src="/ui2/images/ui/dashboard/{RDS.img}" title="{RDS.status}">',
			flex: 1
		}, {
			text: 'S3',
			xtype: 'templatecolumn',
			tpl: '<img src="/ui2/images/ui/dashboard/{S3.img}" title="{S3.status}">',
			flex: 1
		}],
		viewConfig: {
			emptyText: 'No info found',
			deferEmptyText: false,
			disableSelection: true,
			getRowClass: function(rec, rowIdx) {
				return rowIdx % 2 == 1 ? 'scalr-ui-dashboard-grid-row' : 'scalr-ui-dashboard-grid-row-alt';
			}
		},
		plugins: {
			ptype: 'gridstore'
		}
	}],
	widgetType: 'nonlocal',
	loadContent: function () {
		var me = this;

		if (this.rendered) {
			this.minHeight = 120;
			this.updateLayout();
			this.body.mask('Loading content ...');
		}

		Scalr.Request({
			url: '/dashboard/widget/status/xGetContent',
			scope: this,
			params: { locations: this.params['locations'] },
			success: function (content) {
				if (this.isDestroyed)
					return;

				this.child('grid').store.load({
					data: content['data'] ? content['data'] : []
				});

				if (content.locations)
					me.params['locations'] = content.locations;

				if (this.rendered) {
					this.minHeight = 0;
					this.updateLayout();
					this.body.unmask();
				}

			},
			failure: function () {
				if (this.rendered) {
					this.minHeight = 0;
					this.updateLayout();
					this.body.unmask();
				}
			}
		});
	},

	listeners: {
		boxready: function() {
			this.params = this.params || {};
			if (! this.collapsed)
				this.loadContent();
		},
		beforeexpand: function() {
			if (this.rendered)
				this.body.mask();
		},
		expand: function () {
			this.loadContent();
		}
	},

	addSettingsForm: function () {
		var settingsForm = new Ext.form.FieldSet({
			title: 'Choose location(s) to show',
			items: {
				xtype: 'checkboxgroup',
				columns: 3,
				vertical: true
			}
		});

		var locations = this.params['locations'];
		for (var i in this.locations) {
			settingsForm.down('checkboxgroup').add({
				xtype: 'checkbox',
				boxLabel: i,
				name: 'locations',
				inputValue: i,
				checked: locations.indexOf(i)!=-1 ? true: false
			});
		}
		return settingsForm;
	},

	showSettingsWindow: function () {
		Scalr.Request({
			url: '/dashboard/widget/status/xGetLocations',
			scope: this,
			success: function (locationData) {
				if (locationData['locations']) {
					this.locations = locationData['locations'];
					Scalr.Confirm({
						form: this.locations ? this.addSettingsForm() : {xtype: 'displayfield', value: 'No locations to select'},
						formWidth: 450,
						title: 'Settings',
						padding: 5,
						success: function (formValues) {
							if(formValues.locations){
								var locations = [];
								if (Ext.isArray(formValues.locations)) {
									for(var i = 0; i < formValues.locations.length; i++) {
										locations.push(formValues.locations[i]);
									}
								} else
									locations.push(formValues.locations);
								this.params = {'locations': Ext.encode(locations)};
								this.up('dashpanel').savePanel(0);
								if (!this.collapsed)
									this.loadContent();
							}
						},
						scope: this
					});
				}
				else {
					Scalr.Confirm({
						title: 'No locations',
						msg: 'No locations to select',
						type: 'action'
					});
				}
			},
			failure: function() {
				Scalr.Confirm({
					title: 'No locations',
					msg: 'No locations to select',
					type: 'action'
				});
			}
		});
	}
});
Ext.define('Scalr.ui.dashboard.tutorFarm', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.tutorfarm',

	title: 'Farms',
	items: [{
		xtype: 'panel',
		border: false,
		html:
			'<div style="float: left; width: 55%; padding: 30px 0px 25px 25px; height: 150px;">' +
				'<span class="scalr-ui-dashboard-tutor-message" style="margin-left: 17px;">New to Scalr?</span>' +
				'<br/><br/><span class="scalr-ui-dashboard-tutor-message-big">Create a farm</span>' +
				'</div>' +
				'<a href="#/farms/build"><div style="float: left; width: 40%; margin-top: 10px; height: 115px; background: url(\'/ui2/images/ui/dashboard/create_farm.png\') no-repeat;" align="center">' +
				'</div></a>' +
				'<div style="width: 5%; float: left; height: 100%; padding-left: 5px;">' +
				'<div class="x-menu-icon-help" style="cursor: pointer; position: absolute; top: 115px;" align="right"></div>' +
				'</div>'
	}, {
		xtype: 'panel',
		margin: '10 0 0 0',
		itemId: 'tutorFarmInfo',
		hidden: true,
		autoScroll: true,
		border: false,
		height: 230,
		html:
			'<div class="scalr-ui-dashboard-tutor-desc"><span class="scalr-ui-dashboard-tutor-title">Farms</span><br/>' +
				'<br/>To create a farm, simply click on this widget or go to <a href="#/farms/build"> Server Farms > Build New</a>.<br/><br/>' +
				'In Scalr, farms are logical unit that allow you to group a set of configurati on and behavior according to which your servers should behave. With Scalr\'s terminology, farms are simply set of roles.' +
				'<br/><br/><span class="scalr-ui-dashboard-tutor-title">Roles</span><br/>' +
				'Roles are core concepts in Scalr and fundamental components of your architecture.They are images that define the behavior of your servers. As in object-oriented programming, a role is used as a blueprint to create instances of itself.' +
				'<br/><br/><a href="#/farms/build"><span class="scalr-ui-dashboard-tutor-title">Farm Builder</span></a><br/>' +
				'Start by naming your farm and click on the Role tab. Here, you will be asked to add roles. If you are getting started with Scalr, you should still have a list of pre-made roles ready to be added to your farm. Let us take the example of a classic three-tier web stack. In Scalr, each tier corresponds to a separate role. First comes the load balancing tier that can be added to a farm by clicking the *Add* button on the NGINX load-balancer role. Then comes the application tier. Simply add an Apache+Ubuntu 64bit role to the farm. The same can be done for the database tier by adding a MySQL on Ubuntu 64bit role. In this example a role comprises the operating system and the software that will give the role its specific behavior.' +
				'<br/><br/>Once you’ve added all your roles you will need to configure them. To do so, simply click on the role icon. For more information on all the configurations, please visit our wiki.' +
				'<br/><br/>You might wonder: what exactly does adding these roles to the farm do? Well it does not actually do anything. It simply creates the blueprint from which your farm will be launched. To launch it, simply hit Save at the bottom of the page and Launch in the drop down Options menu.' +
				'</div>'
	}],
	onBoxReady: function () {
		var tutorpanel = this;
		this.body.on('click', function(e, el, obj) {
			if (e.getTarget('div.x-menu-icon-help'))
			{
				if (tutorpanel.down('#tutorFarmInfo').hidden) {
					tutorpanel.down('#tutorFarmInfo').el.slideIn('t');
					tutorpanel.down('#tutorFarmInfo').show();
					tutorpanel.down('#tutorFarmInfo').setHeight(380);
				} else {
					tutorpanel.down('#tutorFarmInfo').el.slideOut('t', {easing: 'easeOut'});
					tutorpanel.down('#tutorFarmInfo').hide();
				}
				tutorpanel.up('dashpanel').doLayout();
			}
		});
		this.doLayout();
		this.callParent();
	}
});
Ext.define('Scalr.ui.dashboard.tutorApp', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.tutorapp',

	title: 'Applications',
	items: [{
		xtype: 'panel',
		border: false,
		html:
			'<div style="float: left; width: 58%; padding: 30px 0px 25px 25px; height: 150px;">' +
				'<span class="scalr-ui-dashboard-tutor-message" style="margin-left: 17px;">No app running?</span>' +
				'<br/><br/><span class="scalr-ui-dashboard-tutor-message-big">Deploy your code</span>' +
			'</div>' +
			'<a href="#/dm/applications/view"><div style=" float: left; width: 37%; margin-top: 10px; height: 115px; background: url(\'/ui2/images/ui/dashboard/deploy_code.png\') no-repeat;" align="center">' +
			'</div></a>' +
			'<div style="width: 5%; float: left; height: 100%; padding-left: 5px;">' +
				'<div class="x-menu-icon-help" style="cursor: pointer; position: absolute; top: 115px;" align="right"></div>' +
				'</div>'
	}, {
		xtype: 'panel',
		margin: '10 0 0 0',
		itemId: 'tutorAppInfo',
		height: 230,
		hidden: true,
		autoScroll: true,
		border: false,
		html:
			'<div class="scalr-ui-dashboard-tutor-desc"><span class="scalr-ui-dashboard-tutor-title">Application</span><br/>' +
				'<br/>You can use Scalr\'s deployment functionality to orchestrate code deployments to your farms. To do so, simply go to <a href="#/dm/tasks/view">Websites > Deployments</a>.' +
				'<br/>Within Scalr, Deployments are implemented through Sources and Applications.' +
				'<br/><br/><a href="#/dm/sources/view"><span class="scalr-ui-dashboard-tutor-title">Sources</span></a>' +
				'<br/>A source in Scalr is a path to your application’s source code. This can be Git, SVN, or simply HTTP. When you add a source, you have the option of providing authentication if your source is protected. You can have multiple sources for the testing or stable branches of your code.' +
				'<br/><br/>Depending on the type of source you chose, your code will be deployed:' +
				'<br/>- with a simple download (http);' +
				'<br/>- with svn checkout the first time, then svn update (svn);' +
				'<br/>- with git clone the first time, then git pull (git).' +
				'<br/><br/>To automatically deploy code when you push to your repository, you can set post-commit hooks in svn and git that trigger a new deployment.' +
				'<br/><br/><a href="#/dm/applications/view"><span class="scalr-ui-dashboard-tutor-title">Applications</span></a>' +
				'<br/>We assume that everyone is familiar with the concept of application: this is simply the' +
				'software that you want to run on your servers. In Scalr, an application is an object that has one or several *sources* attached to it and to which you can apply pre and post deploy scripts. This object can then be deployed on the instances of a specific role in a given farm. ' +
				'</div>'
	}],
	onBoxReady: function () {
		var tutorpanel = this;
		this.body.on('click', function(e, el, obj) {
			if (e.getTarget('div.x-menu-icon-help'))
			{
				if (tutorpanel.down('#tutorAppInfo').hidden) {
					tutorpanel.down('#tutorAppInfo').show();
					tutorpanel.down('#tutorAppInfo').el.slideIn('t');
					tutorpanel.doLayout();
					tutorpanel.down('#tutorAppInfo').setHeight(380);
				} else {
					tutorpanel.down('#tutorAppInfo').el.slideOut('t', {easing: 'easeOut'});
					tutorpanel.down('#tutorAppInfo').hide();
					tutorpanel.doLayout();
				}
			}
		});
		this.doLayout();
		this.callParent();
	}
});

Ext.define('Scalr.ui.dashboard.tutorDnsZones', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.tutordns',

	title: 'DNS Zones',
	items: [{
		xtype: 'panel',
		border: false,
		html:
			'<div style="float: left; width: 55%; padding: 30px 0px 25px 25px; height: 150px;">' +
				'<span class="scalr-ui-dashboard-tutor-message">Let us manage your</span>' +
				'<br/><br/><span class="scalr-ui-dashboard-tutor-message-big" style="margin-left: 30px;">DNS zones</span>' +
			'</div>' +
			'<a href="#/dnszones/view"><div style="float: left; width: 40%; margin-top: 10px; height: 115px; background: url(\'/ui2/images/ui/dashboard/dns_zone.png\') no-repeat;" align="center">' +
			'</div></a>'+
			'<div style="width: 5%; float: left; height: 100%; padding-left: 5px;">' +
				'<div class="x-menu-icon-help" style="cursor: pointer; position: absolute; top: 115px;" align="right"></div>' +
				'</div>'
	}, {
		xtype: 'panel',
		margin: '10 0 0 0',
		itemId: 'tutorDnsInfo',
		hidden: true,
		autoScroll: true,
		border: false,
		html:
			'<div class="scalr-ui-dashboard-tutor-desc"><span class="scalr-ui-dashboard-tutor-title">DNS Management</span><br/>' +
				'<br/>Scalr provides an out-of-the-box DNS Management tool. To use it, you\'ll need to log in to your registrar and point your domain to Scalr\'s name servers.' +
				'<br/><br/>Create \'IN NS\' records on nameservers authoritative for your root domain:' +
				'<br/>- beta.yourdomain.com. IN NS ns1.scalr.net.' +
				'<br/>- beta.yourdomain.com. IN NS ns2.scalr.net.' +
				'<br/>- beta.yourdomain.com. IN NS ns3.scalr.net.' +
				'<br/>- beta.yourdomain.com. IN NS ns4.scalr.net.' +
				'<br/>Create \'beta.yourdomain.com\' DNS zone in Scalr and point it to desired farm/role.' +
				'<br/>Wait for DNS cache TTL to expire' +
				'<br/><br/>DNS zones are automatically updated by Scalr to reflect the instances you are currently running.' +
				'</div>'
	}],
	onBoxReady: function () {
		var tutorpanel = this;
		this.body.on('click', function(e, el, obj) {
			if (e.getTarget('div.x-menu-icon-help'))
			{
				if (tutorpanel.down('#tutorDnsInfo').hidden) {
					tutorpanel.down('#tutorDnsInfo').show();
					tutorpanel.down('#tutorDnsInfo').el.slideIn('t');
					tutorpanel.down('#tutorDnsInfo').setHeight(420);
				} else {
					tutorpanel.down('#tutorDnsInfo').el.slideOut('t', {easing: 'easeOut'});
					tutorpanel.down('#tutorDnsInfo').hide();
				}
				tutorpanel.up('dashpanel').doLayout();
			}
		});
		this.doLayout();
		this.callParent();
	}
});

Ext.define('Scalr.ui.dashboard.Cloudyn', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.dashboard.cloudyn',

	itemId: 'cloudyn',
	title: 'Cloud cost efficiency',
	widgetType: 'nonlocal',
	cls: 'scalr-ui-dashboard-widgets-cloudyn',
	items: [{
		xtype: 'panel',
		itemId: 'setup',
		layout: 'anchor',
		bodyStyle: 'padding: 10px',
		style: 'background-color: #F2F7EB; background-image: url(/ui2/images/icons/new.png); background-position: top right; background-repeat: no-repeat;',
		defaults: {
			anchor: '100%'
		},
		items: [{
			xtype: 'component',
			style: 'margin-bottom: 10px',
			html:
				'<div style="text-align: center; height: 44px; width: 100%; margin-bottom: 10px; margin-top: 8px"><img src="/ui2/images/ui/dashboard/cloudyn_logo.png" width=100 height=44 /></div>' +
				'<span style="font-size: 13px; font-weight: bold; line-height: 23px; color: #333">Optimize your cloud spend with actionable reports, and more &mdash; all from within Scalr. <a href="http://www.cloudyn.com/home" target="_blank" class="dashed">Learn more about Cloudyn</a></span>'
		}, {
			xtype: 'checkbox',
			name: 'owner',
			boxLabel: '&nbsp;I agree to Cloudyn\'s <a href="https://app.cloudyn.com/pages/terms.html" target="_blank">terms of service</a>, and for Scalr to share read-only access on my behalf.',
			style: 'margin-bottom: 20px',
			listeners: {
				change: function(field, value) {
					if (value)
						this.next('fieldcontainer').child('button').enable();
					else
						this.next('fieldcontainer').child('button').disable();
				}
			}
		}, {
			xtype: 'fieldcontainer',
			name: 'owner',
			layout: {
				type: 'vbox',
				align: 'center'
			},
			style: 'margin-bottom: 20px',
			height: 28,
			items: [{
				xtype: 'button',
				disabled: true,
				flex: 1,
				width: 220,
				text: 'Start saving now *',
				handler: function() {
					Scalr.Request({
						processBox: {
							type: 'action'
						},
						url: '/dashboard/widget/cloudyn/xSetup',
						scope: this.up('#cloudyn'),
						success: function(data) {
							this.updateForm(data);
						}
					});
				}
			}]
		}, {
			xtype: 'displayfield',
			name: 'owner',
			anchor: '100%',
			value: '<div style="text-align: center; width: 100%;">*AWS Read-Only API Credentials will be shared with Cloudyn.</div>'
		}]
	}, {
		xtype: 'grid',
		itemId: 'info',
		hidden: true,
		store: {
			fields: [ 'Metric', 'MetricName', 'DataIsReady', 'CompletionDateTz', 'estimate', 'SpaceBeforeUnit', 'IsPrefixUnit', 'UnitOfMeasurement' ],
			proxy: 'object'
		},
		columns: [{
			header: 'Metric name',
			flex: 1,
			hideable: false,
			sortable: false,
			xtype: 'templatecolumn',
			dataIndex: 'MetricName',
			tpl: '{MetricName}'
		}, {
			header: 'Data',
			flex: 1,
			hideable: false,
			sortable: false,
			dataIndex: 'Metric',
			renderer: function(value, metaData, record) {
				if (record.get('DataIsReady') == 'true') {
				    var s = '<span style="font-weight: bold">';
					if (record.get('IsPrefixUnit')) {
						s += record.get('UnitOfMeasurement') + record.get('Metric') + '</span>';
					} else {
						s += record.get('Metric') + '</span>' + (record.get('SpaceBeforeUnit') == 1 ? ' ' : '') + record.get('UnitOfMeasurement');
					}
					return s;
				} else {
					metaData.tdCls = 'border';
					if (record.get('estimate'))
						return 'Available ' + record.get('estimate');
					else
						return 'Data is not ready';
				}
			}
		}],
		viewConfig: {
			emptyText: 'No metrics found',
			deferEmptyText: false,
			disableSelection: true,
			listeners: {
				render: function() {
					var comp = this.up('grid').down('#icon');
					this.doStripeRows = Ext.Function.createSequence(this.doStripeRows, function() {
						if (this.all.last() && comp.el.down('tr.x-grid-row')) {
							if (this.all.last().hasCls('x-grid-row-alt')) {
								comp.el.down('tr.x-grid-row').removeCls('x-grid-row-alt');
							} else {
								comp.el.down('tr.x-grid-row').addCls('x-grid-row-alt');
							}
						}
					});
				}
			}
		},
		dockedItems: [{
			xtype: 'component',
			dock: 'bottom',
			style: 'line-height: 40px; text-align: center;',
			height: 40,
			itemId: 'icon',
			html: '&nbsp;'
		}]
	}],
	updateForm: function(content) {
		var setup = this.down('#setup'), info = this.down('#info');
		if (content.enabled) {
			setup.hide();
			info.show();
			if (content.owner) {
				info.down('#icon').update('<table class="x-grid-table" style="width:100%"><tr class="x-grid-row"><td class="x-grid-cell"><img src="/ui2/images/ui/dashboard/cloudyn_icon.png" style="float:center; vertical-align: middle; padding-right: 10px;"><a target="_blank" style="font-weight: bold;" href="' + content['consoleUrl'] + '">See more details ...</a></td></tr></table>');
				info.down('#icon').show();
			} else {
				info.down('#icon').hide();
			}
			info.store.loadData(content.metrics);
		} else {
			info.hide();
			setup.show();
			Ext.each(setup.query('[name="owner"]'), function() {
				if (content.owner)
					this.show();
				else
					this.hide();
			});
		}
	},
	listeners: {
		boxready: function() {
			if (!this.collapsed)
				this.loadContent();
		},
		beforeexpand: function() {
			if (this.rendered)
				this.body.mask();
		},
		expand: function() {
			this.loadContent();
		}
	},
	loadContent: function () {
		if (this.rendered)
			this.body.mask('Loading content ...');

		Scalr.Request({
			url: '/dashboard/widget/cloudyn/xGetContent',
			scope: this,
			success: function (content) {
				if (this.rendered)
					this.body.unmask();
				this.updateForm(content);
			},
			failure: function () {
				if (this.rendered)
					this.body.unmask();
			}
		});
	}
});


Ext.define('Scalr.ui.dashboard.Loading', {
	extend: 'Ext.container.Container',
	alias: 'widget.dashboard.loading',

	width: '100%',
	html: '<div align="center" class="scalr-ui-dashboard-body-loading2" style="height: 100px; vertical-align: middle; text-align: center;"><img src="/ui2/images/ui/dashboard/load_widget.gif"/><br/><br/>Loading content...</div>'
});