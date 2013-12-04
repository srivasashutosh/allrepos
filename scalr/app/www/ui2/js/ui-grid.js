Ext.define('Scalr.ui.PagingToolbar', {
	extend: 'Ext.PagingToolbar',
	alias: 'widget.scalrpagingtoolbar',

	pageSizes: [10, 15, 25, 50, 100],
	pageSizeMessage: '{0} items per page',
	pageSizeStorageName: 'grid-ui-page-size',
	autoRefresh: 0,
	autoRefreshTask: 0,
	height: 33,
	prependButtons: true,
	beforeItems: [],
	afterItems: [],

	checkRefreshHandler: function (item, enabled) {
		if (enabled) {
			this.autoRefresh = item.autoRefresh;
			this.gridContainer.autoRefresh = this.autoRefresh;
			this.gridContainer.saveState();
			if (this.autoRefresh) {
				clearInterval(this.autoRefreshTask);
				this.autoRefreshTask = setInterval(this.refreshHandler, this.autoRefresh * 1000);
				this.down('#refresh').setIconCls('x-tbar-autorefresh');
			} else {
				clearInterval(this.autoRefreshTask);
				this.down('#refresh').setIconCls('x-tbar-loading');
			}
		}
	},

	getPagingItems: function() {
		var me = this, items = [ '->' ];

		if (this.beforeItems.length) {
			items = Ext.Array.push(items, this.beforeItems);
		}

		items = Ext.Array.merge(items, [{
			itemId: 'refresh',
			//	tooltip: me.refreshText,
			overflowText: me.refreshText,
			iconCls: Ext.baseCSSPrefix + 'tbar-loading',
			ui: 'paging',
			handler: me.doRefresh,
			scope: me
		}, '-', {
			itemId: 'first',
			//tooltip: me.firstText,
			overflowText: me.firstText,
			iconCls: Ext.baseCSSPrefix + 'tbar-page-first',
			ui: 'paging',
			disabled: true,
			handler: me.moveFirst,
			scope: me
		},{
			itemId: 'prev',
			//tooltip: me.prevText,
			overflowText: me.prevText,
			iconCls: Ext.baseCSSPrefix + 'tbar-page-prev',
			ui: 'paging',
			disabled: true,
			handler: me.movePrevious,
			scope: me
		}, me.beforePageText, {
			xtype: 'textfield',
			itemId: 'inputItem',
			name: 'inputItem',
			cls: Ext.baseCSSPrefix + 'tbar-page-number',
			maskRe: /[0123456789]/,
			minValue: 1,
			enableKeyEvents: true,
			selectOnFocus: true,
			submitValue: false,
			// mark it as not a field so the form will not catch it when getting fields
			isFormField: false,
			width: 40,
			listeners: {
				scope: me,
				keydown: me.onPagingKeyDown,
				blur: me.onPagingBlur
			}
		},{
			xtype: 'tbtext',
			itemId: 'afterTextItem',
			text: Ext.String.format(me.afterPageText, 1)
		},
			{
				itemId: 'next',
				//tooltip: me.nextText,
				overflowText: me.nextText,
				iconCls: Ext.baseCSSPrefix + 'tbar-page-next',
				ui: 'paging',
				disabled: true,
				handler: me.moveNext,
				scope: me
			},{
				itemId: 'last',
				//	tooltip: me.lastText,
				overflowText: me.lastText,
				iconCls: Ext.baseCSSPrefix + 'tbar-page-last',
				ui: 'paging',
				disabled: true,
				handler: me.moveLast,
				scope: me
			}]);

		if (this.afterItems.length) {
			items.push({
				xtype: 'tbseparator',
				margin: '0 7 0 0'
			});
			items = Ext.Array.push(items, this.afterItems);
		}

		return items;
	},

	evaluatePageSize: function() {
		var grid = this.gridContainer, view = grid.getView();
		if (Ext.isDefined(grid.height) && view.rendered)
			return Math.floor(view.el.getHeight() / 26); // row's height
	},

	getPageSize: function() {
		var pageSize = 0;
		if (Ext.state.Manager.get(this.pageSizeStorageName, 'auto') != 'auto')
			pageSize = Ext.state.Manager.get(this.pageSizeStorageName, 'auto');
		else {
			//var panel = this.up('panel'), view = (panel.getLayout().type == 'card') ? panel.getLayout().getActiveItem().view : panel;
            var grid = this.gridContainer, view = grid.getView();
			if (Ext.isDefined(grid.height) && view && view.rendered)
				pageSize = Math.floor(view.el.getHeight() / 26); // row's height
		}
		return pageSize;
	},

	setPageSizeAndLoad2: function() {
		// TODO check this code, move to gridContainer
		var panel = this.up('panel'), view = (panel.getLayout().type == 'card') ? panel.getLayout().getActiveItem().view : panel;
		if (Ext.isDefined(panel.height) && view && view.rendered) {
			panel.store.pageSize = this.getPageSize();
			if (Ext.isObject(this.data)) {
				panel.store.loadData(this.data.data);
				panel.store.totalCount = this.data.total;
			} else
				panel.store.load();
		}
	},

    setPageSizeAndLoad: function() {
        var grid = this.gridContainer, view = grid.getView();
        if (Ext.isDefined(grid.height) && view.rendered) {
            grid.store.pageSize = this.getPageSize();
            if (Ext.isObject(this.data)) {
                grid.store.loadData(this.data.data);
                grid.store.totalCount = this.data.total;
            } else
                grid.store.load();
        }
    },

    moveNext : function(){
		var me = this,
			total = me.getPageData().pageCount,
			next = me.store.currentPage + 1;

		if (me.store.currentPage == 1 && me.store.pageSize != me.evaluatePageSize()) {
			// if page has less records, that it could include, load more records per page
			if (me.fireEvent('beforechange', me, next) !== false) {
				me.store.pageSize = me.evaluatePageSize();
				me.store.load();
			}
		} else if (next <= total) {
			if (me.fireEvent('beforechange', me, next) !== false) {
				me.store.nextPage();
			}
		}
	},

	initComponent: function () {
		this.callParent();

		this.on('added', function (comp, container) {
			this.gridContainer = container;

			this.gridContainer.scalrReconfigure = function (loadParams) {
				if (this.scalrReconfigureParams)
					Ext.applyIf(loadParams, this.scalrReconfigureParams);
				Ext.apply(this.store.proxy.extraParams, loadParams);
                if (this.scalrReconfigureParams)
                    this.fireEvent('scalrreconfigure', this.store.proxy.extraParams);
			};
			this.refreshHandler = Ext.Function.bind(function () {
				this.store.load();
			}, this.gridContainer);

			this.gridContainer.on('activate', function () {
				if (this.store.pageSize != this.getPageSize() || !this.data)
					this.setPageSizeAndLoad();
				if (this.autoRefresh)
					this.autoRefreshTask = setInterval(this.refreshHandler, this.autoRefresh * 1000);
			}, this);

			this.gridContainer.on('deactivate', function () {
				clearInterval(this.autoRefreshTask);
			}, this);

			this.gridContainer.store.on('load', function () {
				if (this.autoRefreshTask) {
					clearInterval(this.autoRefreshTask);
					if (this.autoRefresh)
						this.autoRefreshTask = setInterval(this.refreshHandler, this.autoRefresh * 1000);
				}
			}, this);

			this.gridContainer.on('staterestore', function(comp) {
				this.autoRefresh = comp.autoRefresh || 0;
				if (this.autoRefresh)
					this.down('#refresh').setIconCls('x-tbar-autorefresh');
			}, this);
		});
	}
});

Ext.define('Scalr.ui.ToolbarCloudLocation', {
	extend: 'Ext.form.field.ComboBox',
	alias: 'widget.fieldcloudlocation',

	localParamName: 'grid-ui-default-cloud-location',
	fieldLabel: 'Location',
	labelWidth: 53,
	width: 358,
	matchFieldWidth: false,
	listConfig: {
		width: 'auto',
		minWidth: 300
	},
	iconCls: 'no-icon',
	displayField: 'name',
	valueField: 'id',
	editable: false,
	queryMode: 'local',
	setCloudLocation: function () {
		if (this.cloudLocation) {
			this.setValue(this.cloudLocation);
		} else {
			var cloudLocation = Ext.state.Manager.get(this.localParamName);
			if (cloudLocation) {
				var ind = this.store.find('id', cloudLocation);
				if (ind != -1)
					this.setValue(cloudLocation);
				else
					this.setValue(this.store.getAt(0).get('id'));
			} else {
				this.setValue(this.store.getAt(0).get('id'));
			}
		}
		this.gridStore.proxy.extraParams.cloudLocation = this.getValue();
	},
	listeners: {
		change: function () {
			if (! this.getValue())
				this.setCloudLocation();
		},
		select: function () {
			Ext.state.Manager.set(this.localParamName, this.getValue());
			this.gridStore.proxy.extraParams.cloudLocation = this.getValue();
			this.gridStore.loadPage(1);
		},
		added: function () {
			this.setCloudLocation();
		}
	}
});

Ext.define('Scalr.ui.GridRadioColumn', {
	extend: 'Ext.grid.column.Column',
	alias: ['widget.radiocolumn'],

	initComponent: function(){
		var me = this;
		me.hasCustomRenderer = true;
		me.callParent(arguments);
	},
	width: 35,

	processEvent: function(type, view, cell, recordIndex, cellIndex, e, record) {
		var me = this;
		if (type == 'click' && e.getTarget('input.x-form-radio')) {
			view.store.each(function(r) {
				r.set(me.dataIndex, false);
			})
			record.set(me.dataIndex, true);
		}
		return this.callParent(arguments);
	},

	defaultRenderer: function(value, meta, record) {
		var result = '<div ';
		if (value)
			result += 'class="x-form-cb-checked" '
		result += 'style="text-align: center" ><input type="button" class="x-form-field x-form-radio" /></div>';

		return result;
	}
});

Ext.define('Scalr.ui.GridOptionsColumn', {
	extend: 'Ext.grid.column.Column',
	alias: 'widget.optionscolumn',

	text: '&nbsp;',
	hideable: false,
	width: 116,
	fixed: true,
	align: 'center',
	tdCls: 'x-grid-row-options-cell',

	constructor: function () {
		this.callParent(arguments);

		this.sortable = false;
		this.optionsMenu = Ext.create('Ext.menu.Menu', {
			items: this.optionsMenu,
			listeners: {
				click: function (menu, item, e) {
					if (item) {
						if (Ext.isFunction (item.menuHandler)) {
							item.menuHandler(item);
							e.preventDefault();
						} else if (Ext.isObject(item.request)) {
							var r = Scalr.utils.CloneObject(item.request);
							r.params = r.params || {};

							if (Ext.isObject(r.confirmBox))
								r.confirmBox.msg = new Ext.Template(r.confirmBox.msg).applyTemplate(item.record.data);

							if (Ext.isFunction(r.dataHandler)) {
								r.params = Ext.apply(r.params, r.dataHandler(item.record));
								delete r.dataHandler;
							}

							Scalr.Request(r);
							e.preventDefault();
						}
					}
				}
			}
		});

		this.optionsMenu.doAutoRender();
	},

	showOptionsMenu: function (view, record) {
		this.optionsMenu.suspendLayouts();
		this.beforeShowOptions(record, this.optionsMenu);
		this.optionsMenu.show();

		this.optionsMenu.items.each(function (item) {
			var display = this.getOptionVisibility(item, record);
			item.record = record;
			item[display ? "show" : "hide"]();
			if (display && item.href) {
				// Update item link
				if (! this.linkTplsCache[item.id]) {
					this.linkTplsCache[item.id] = new Ext.Template(item.href).compile();
				}
				var tpl = this.linkTplsCache[item.id];
				if (item.rendered)
					item.el.down('a').dom.href = tpl.apply(record.data);
			}
		}, this);

		this.optionsMenu.resumeLayouts();
		this.optionsMenu.doLayout();

		var btnEl = Ext.get(view.getNode(record)).down('div.x-grid-row-options'), xy = btnEl.getXY(), sizeX = xy[1] + btnEl.getHeight() + this.optionsMenu.getHeight();
		// menu shouldn't overflow window size
		if (sizeX > Scalr.application.getHeight()) {
			xy[1] -= sizeX - Scalr.application.getHeight();
		}

		this.optionsMenu.setPosition([xy[0] - (this.optionsMenu.getWidth() - btnEl.getWidth()), xy[1] + btnEl.getHeight() + 1]);
	},

	initComponent: function () {
		this.callParent(arguments);

		this.on('boxready', function () {
			this.up('panel').on('itemclick', function (view, record, item, index, e) {
				var btnEl = Ext.get(e.getTarget('div.x-grid-row-options'));
				if (! btnEl)
					return;

				this.showOptionsMenu(view, record);
			}, this);
		});
	},

	renderer: function (value, meta, record, rowIndex, colIndex) {
		if (this.headerCt.getHeaderAtIndex(colIndex).getVisibility(record))
			return '<div class="x-grid-row-options">Actions<div class="x-grid-row-options-trigger"></div></div>';
	},

	linkTplsCache: {},

	getVisibility: function (record) {
		return true;
	},

	getOptionVisibility: function (item, record) {
		return true;
	},

	beforeShowOptions: function (record, menu) {

	}
});
