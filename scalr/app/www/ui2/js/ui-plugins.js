/*
 * Messages system
 */
Ext.ns('Scalr.message');

Scalr.message = {
	queue: [],
	Add: function(message, type) {
		if (Ext.isArray(message)) {
			var s = '';
			for (var i = 0; i < message.length; i++)
				'<li>' + message[i] + '</li>'
			message = '<ul>' + s + '</ul>';
		}

		this.Flush(false, message);

		var tip = new Ext.tip.ToolTip({
			autoShow: true,
			autoHide: false,
			closable: true,
			closeAction: 'destroy',
			header: false,
			layout: {
				type: 'hbox'
			},
			minWidth: 200,
			maxWidth: 900,
			dt: Ext.Date.add(new Date(), Ext.Date.SECOND, 2),
			type: type,
			cls: 'x-tip-message x-tip-message-' + type,
			items: [{
				xtype: 'component',
				flex: 1,
				tpl: '{message}',
				data: {
					message: message
				}
			}, {
				xtype: 'tool',
				type: 'close',
				handler: function () {
					this.up('tooltip').close();
				}
			}],
			onDestroy: function () {
				Ext.Array.remove(Scalr.message.queue, this);
			}
		});

		tip.el.alignTo(Ext.getBody(), 't-t', [0, 15]);
		Scalr.message.queue.push(tip);
	},
	Error: function(message) {
		this.Add(message, 'error');
	},
	Success: function(message) {
		this.Add(message, 'success');
	},
	Warning: function(message) {
		this.Add(message, 'warning');
	},
	Flush: function(force, message) {
		var i = this.queue.length - 1, dt = new Date();

		while (i >= 0) {
			if (force || this.queue[i].dt < dt || this.queue[i].child('component').initialConfig.data.message == message) {
				this.queue[i].destroy();
			}
			i--;
		}
	}
}

/*
 * Data plugins
 */
Ext.define('Scalr.ui.DataReaderJson', {
	extend: 'Ext.data.reader.Json',
	alias : 'reader.scalr.json',

	type: 'json',
	root: 'data',
	totalProperty: 'total',
	successProperty: 'success'
});

Ext.define('Scalr.ui.DataProxyAjax', {
	extend: 'Ext.data.proxy.Ajax',
	alias: 'proxy.scalr.paging',

	reader: 'scalr.json'
});

Ext.define('Scalr.ui.StoreReaderObject', {
	extend: 'Ext.data.reader.Json',
	alias: 'reader.object',

	readRecords: function (data) {
		var me = this, result = [];

		for (var i in data) {
			if (Ext.isString(data[i]))
				result[result.length] = {id: i, name: data[i]}; // format id => name
			else
				result[result.length] = data[i];
		}

		return me.callParent([result]);
	}
});

Ext.define('Scalr.ui.StoreProxyObject', {
	extend: 'Ext.data.proxy.Memory',
	alias: 'proxy.object',

	reader: 'object',

	/**
	* Reads data from the configured {@link #data} object. Uses the Proxy's {@link #reader}, if present
	* @param {Ext.data.Operation} operation The read Operation
	* @param {Function} callback The callback to call when reading has completed
	* @param {Object} scope The scope to call the callback function in
	*/
	read: function(operation, callback, scope) {
		var me     = this,
			reader = me.getReader();

		////
		if (Ext.isDefined(operation.data))
			me.data = operation.data;
		////

		var result = reader.read(me.data);

		Ext.apply(operation, {
			resultSet: result
		});

		operation.setCompleted();
		operation.setSuccessful();
		Ext.callback(callback, scope || me, [operation]);
	}
});

/*
 * Form plugins
 */
Ext.define('Scalr.ui.ComboAddNewPlugin', {
	extend: 'Ext.AbstractPlugin',
	alias: 'plugin.comboaddnew',
	url: '',
	postUrl: '',

	init: function(comp) {
		var me = this;

		// to preserve offset for add button
		Ext.override(comp, {
			alignPicker: function() {
                this.callParent();

                var me = this,
                    picker = me.getPicker(),
                    heightAbove = me.getPosition()[1] - Ext.getBody().getScroll().top,
                    heightBelow = Ext.Element.getViewHeight() - heightAbove - me.getHeight(),
                    space = Math.max(heightAbove, heightBelow);

				if (picker.getHeight() > space - (5 + 24)) {
					picker.setHeight(space - (5 + 24)); // have some leeway so we aren't flush against
				}
			}
		});

		comp.on('render', function() {
			var picker = this.getPicker();
			picker.addCls('x-boundlist-hideemptygrid');
			picker.on('render', function() {
				this.el.createChild({
					tag: 'div',
					cls: 'x-boundlist-addnew'
				}).on('click', function() {
					comp.collapse();
					me.handler();
				});
			});
		});

		Scalr.event.on('update', function(type, element) {
			if (type == me.url) {
				this.store.add(element);
				this.setValue(element[this.valueField]);
                this.fireEvent('addnew', element);
			}
		}, comp);
	},

	handler: function() {
		Scalr.event.fireEvent('redirect', '#' + this.url + this.postUrl);
	}
});

/*
 * Grid plugins
 */
Ext.define('Scalr.ui.GridStorePlugin', {
	extend: 'Ext.AbstractPlugin',
	alias: 'plugin.gridstore',
	loadMask: false,

	init: function (client) {
		client.getView().loadMask = this.loadMask;
		client.store.proxy.view = client.getView(); // :(

		client.store.on({
			scope: client,
			beforeload: function () {
				if (this.getView().rendered)
					this.getView().clearViewEl();
				if (! this.getView().loadMask)
					this.processBox = Scalr.utils.CreateProcessBox({
						type: 'action',
						msg: client.getView().loadingText
					});
			},
			load: function (store, records, success, operation, options) {
				if (! this.getView().loadMask)
					this.processBox.destroy();
			}
		});

		client.store.proxy.on({
			exception: function (proxy, response, operation, options) {
				var message = 'Unable to load data';
				try {
					var result = Ext.decode(response.responseText, true);
					if (result && result.success === false && result.errorMessage)
						message += ' (' + result.errorMessage + ')';
					else
						throw 'Report';
				} catch (e) {
					if (response.status == 200 && Ext.isFunction(response.getAllResponseHeaders) && response.getAllResponseHeaders() && response.responseText) {
						var report = [ "Ext.JSON.decode(): You're trying to decode an invalid JSON String" ];
						report.push(Scalr.utils.VarDump(response.request.headers));
						report.push(Scalr.utils.VarDump(response.request.options));
						report.push(Scalr.utils.VarDump(response.request.options.params));
						report.push(Scalr.utils.VarDump(response.getAllResponseHeaders()));
						report.push(response.status);
						report.push(response.responseText);

						report = report.join("\n\n");

						Scalr.utils.PostError({
							message: report,
							url: document.location.href
						});
					}
				}
				
				message += '. <a href="#">Refresh</a>';

				proxy.view.update('<div class="x-grid-error">' + message + '</div>');
				proxy.view.el.down('a').on('click', function (e) {
					e.preventDefault();
					client.store.load();
				});
			}
		});
	}
});

Ext.define('Scalr.ui.SwitchViewPlugin', {
	extend: 'Ext.AbstractPlugin',
	alias: 'plugin.switchview',
	
	init: function (client) {
		client.on('beforerender', function () {
			var field = this.down('[xtype="tbswitchfield"]');
			if (field) {
				this.activeItem = field.switchValue;

				field.on('statesave', function (c, state) {
					this.getLayout().setActiveItem(state.switchValue);
				}, this);
			}
		}, client);
	}
});

Ext.define('Scalr.ui.GridSelectionModel', {
	alias: 'selection.selectedmodel',
	extend: 'Ext.selection.CheckboxModel',

	injectCheckbox: 'last',
	highlightArrow: false,
	checkOnly: true,

	constructor: function () {
		this.callParent(arguments);
		if (this.selectedMenu) {
			this.selectedMenu = Ext.create('Ext.menu.Menu', {
				items: this.selectedMenu,
				listeners: {
					scope: this,
					click: function (menu, item, e) {
						if (! Ext.isDefined(item))
							return;

						var store = this.store, records = this.selected.items, r = Scalr.utils.CloneObject(Ext.apply({}, item.request));
						r.params = r.params || {};
						r.params = Ext.apply(r.params, r.dataHandler(records));

						if (Ext.isFunction(r.success)) {
							r.success = Ext.Function.createSequence(r.success, function() {
								store.load();
							});
						} else {
							r.success = function () {
								store.load();
							};
						}
						delete r.dataHandler;

						Scalr.Request(r);
					}
				}
			});

			this.selectedMenu.doAutoRender();
		}
	},

	bindComponent: function () {
		this.callParent(arguments);
        this.view.on('viewready', function(){
            this.view.on('refresh', function() {
                this.toggleUiHeader(false);
            }, this);
        }, this);
	},

	getHeaderConfig: function() {
		var c = this.callParent();
		c.width = this.selectedMenu ? 55 : 36; // should be 36, after remove arrow
		c.minWidth = c.width;
		c.headerId = 'scalrSelectedModelCheckbox';
		if (this.selectedMenu) {
			c.text = '<div class="arrow"></div>';
		}
		return c;
	},

	// required in all cases
	getVisibility: function (record) {
		return true;
	},

	renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
		metaData.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
		metaData.style = 'margin-left: 5px';

		if (this.getVisibility(record))
			return '<div class="' + Ext.baseCSSPrefix + 'grid-row-checker">&#160;</div>';
	},

	// don't check unavailable items
	selectAll: function(suppressEvent) {
		var me = this,
			selections = [],
			i = 0,
			len,
			start = me.getSelection().length;

		Ext.each(me.store.getRange(), function (record) {
			if (this.getVisibility(record))
				selections.push(record);
		}, this);

		len = selections.length;

		me.bulkChange = true;
		for (; i < len; i++) {
			me.doSelect(selections[i], true, suppressEvent);
		}
		delete me.bulkChange;
		// fire selection change only if the number of selections differs
		me.maybeFireSelectionChange(me.getSelection().length !== start);
	},

	onSelectChange: function() {
		this.callParent(arguments);

			if (! this.highlightArrow) {
				this.highlightArrow = true;

				var view     = this.views[0],
					headerCt = view.headerCt,
					checkHd  = headerCt.child('gridcolumn[isCheckerHd]');

				Ext.create('Ext.fx.Animator', {
					target: checkHd.el.down('div.arrow'),
					duration: 3000,
					iterations: 3,
					keyframes: {
						0: {
							opacity: 1
						},
						10: {
							opacity: 0.3
						},
						20: {
							opacity: 1
						}
					}
				});
			}

			// check to see if all records are selected
			var me = this, selections = [];
			Ext.each(me.store.getRange(), function (record) {
				if (this.getVisibility(record))
					selections.push(record);
			}, this);

			var hdSelectStatus = this.selected.getCount() === selections.length;
			this.toggleUiHeader(hdSelectStatus);
	},

	onHeaderClick: function(headerCt, header, e) {
		if (header.isCheckerHd && !e.getTarget('span.x-column-header-text', 1) && this.selectedMenu) {
			// show menu only if it's not span
			var btnEl = Ext.get(e.getTarget('div.x-column-header-checkbox')), xy = btnEl.getXY();

			if (this.selected.length)
				this.selectedMenu.el.unmask();
			else
				this.selectedMenu.el.mask();

			this.selectedMenu.show();
			this.selectedMenu.setPosition([xy[0] - (this.selectedMenu.getWidth()  - btnEl.getWidth()), xy[1] + btnEl.getHeight() + 1]);
			e.stopEvent();
		} else {
			this.callParent(arguments);
			if (!header.el.hasCls(Ext.baseCSSPrefix + 'grid-hd-checker-on')) {
				this.refreshLastFocused();
			}
		}
	},

	// keyNav
	onKeyEnd: function(e) {
		var me = this,
			last = me.store.getAt(me.store.getCount() - 1);

		if (last) {
			me.setLastFocused(last);
		}
	},

	onKeyHome: function(e) {
		var me = this,
			first = me.store.getAt(0);

		if (first) {
			me.setLastFocused(first);
		}
	},

	onKeyPageUp: function(e) {
		var me = this,
			rowsVisible = me.getRowsVisible(),
			selIdx,
			prevIdx,
			prevRecord;

		if (rowsVisible) {
			selIdx = e.recordIndex;
			prevIdx = selIdx - rowsVisible;
			if (prevIdx < 0) {
				prevIdx = 0;
			}
			prevRecord = me.store.getAt(prevIdx);
			me.setLastFocused(prevRecord);
		}
	},

	onKeyPageDown: function(e) {
		var me = this,
			rowsVisible = me.getRowsVisible(),
			selIdx,
			nextIdx,
			nextRecord;

		if (rowsVisible) {
			selIdx = e.recordIndex;
			nextIdx = selIdx + rowsVisible;
			if (nextIdx >= me.store.getCount()) {
				nextIdx = me.store.getCount() - 1;
			}
			nextRecord = me.store.getAt(nextIdx);
			me.setLastFocused(nextRecord);
		}
	},

	onKeySpace: function(e) {
		var me = this,
			record = me.lastFocused;

		if (record) {
			if (me.isSelected(record)) {
				me.doDeselect(record, false);
			} else if(me.getVisibility(record)) {
				me.doSelect(record, true);
			}
		}
	},

	onKeyUp: function(e) {
		var me = this,
			idx  = me.store.indexOf(me.lastFocused),
			record;

		if (idx > 0) {
			// needs to be the filtered count as thats what
			// will be visible.
			record = me.store.getAt(idx - 1);
			me.setLastFocused(record);
		}
	},

	onKeyDown: function(e) {
		var me = this,
			idx  = me.store.indexOf(me.lastFocused),
			record;

		// needs to be the filtered count as thats what
		// will be visible.
		if (idx + 1 < me.store.getCount()) {
			record = me.store.getAt(idx + 1);
			me.setLastFocused(record);
		}
	},
	
    onRowMouseDown: function(view, record, item, index, e) {
        view.el.focus();
        var me = this,
            checker = e.getTarget('.' + Ext.baseCSSPrefix + 'grid-row-checker'),
            mode;

        if (!me.allowRightMouseSelection(e)) {
            return;
        }

        // checkOnly set, but we didn't click on a checker.
        if (me.checkOnly && !checker) {
			if (me.checkOnly) {
				me.setLastFocused(record);
				me.lastSelected = record;
			}
           return;
        }
        if (checker) {
			e.preventDefault();//prevent text selection
            mode = me.getSelectionMode();
            // dont change the mode if its single otherwise
            // we would get multiple selection
            if (mode !== 'SINGLE' && !e.shiftKey) {
                me.setSelectionMode('SIMPLE');
            }
            me.selectWithEvent(record, e);
            me.setSelectionMode(mode);
			
			me.setLastFocused(record);
			me.onLastFocusChanged(record, record);//get focus back to row after click
        } else {
            me.selectWithEvent(record, e);
        }
    },
	
	refreshLastFocused: function() {
		var record = this.getLastFocused();
		this.setLastFocused(null);
		if (record) {
			this.setLastFocused(record);
		}
	},
	
    selectWithEvent: function(record, e, keepExisting) {
        var me = this;

        switch (me.selectionMode) {
            case 'MULTI':
                if (e.ctrlKey && me.isSelected(record)) {
                    me.doDeselect(record, false);
                } else if (e.shiftKey && me.lastFocused) {
                    me.selectRange(me.lastFocused, record, true);
					if (!me.isSelected(record)) {
						me.fireEvent('selectionchange', me, me.getSelection());
					}
                } else if (e.ctrlKey) {
                    me.doSelect(record, true, false);
                } else if (me.isSelected(record) && !e.shiftKey && !e.ctrlKey && me.selected.getCount() > 1) {
                    me.doSelect(record, keepExisting, false);
                } else {
                    me.doSelect(record, false);
                }
                break;
            case 'SIMPLE':
                if (me.isSelected(record)) {
                    me.doDeselect(record);
                } else {
                    me.doSelect(record, true);
                }
                break;
            case 'SINGLE':
                // if allowDeselect is on and this record isSelected, deselect it
                if (me.allowDeselect && me.isSelected(record)) {
                    me.doDeselect(record);
                // select the record and do NOT maintain existing selections
                } else {
                    me.doSelect(record, false);
                }
                break;
        }
    }	
	
});

Ext.define('Scalr.ui.GridSelection2Model', {
	alias: 'selection.selected2model',
	extend: 'Ext.selection.CheckboxModel',

	injectCheckbox: 'last',
	checkOnly: true,
	showHeaderCheckbox: false,
	cache: {},

/*
 iconCls: 'x-menu-icon-edit',
 text: 'Edit',
 href: '#/roles/{id}/edit',
 itemId: 'option.edit',
 visibility: function (record) {
 if (record.get('origin') == 'CUSTOM') {
 if (! moduleParams['isScalrAdmin'])
 return true;
 else
 return false;
 } else {
 return moduleParams['isScalrAdmin'];
 }
 }

 */

	constructor: function () {
		this.callParent(arguments);

		/*this.selectedMenu = Ext.create('Ext.menu.Menu', {
			items: this.selectedMenu,
			listeners: {
				scope: this,
				click: function (menu, item, e) {
					if (! Ext.isDefined(item))
						return;

					var store = this.store, records = this.selected.items, r = Scalr.utils.CloneObject(Ext.apply({}, item.request));
					r.params = r.params || {};
					r.params = Ext.apply(r.params, r.dataHandler(records));

					if (Ext.isFunction(r.success)) {
						r.success = Ext.Function.createSequence(r.success, function() {
							store.load();
						});
					} else {
						r.success = function () {
							store.load();
						};
					}
					delete r.dataHandler;

					Scalr.Request(r);
				}
			}
		});

		this.selectedMenu.doAutoRender();*/
	},

	bindComponent: function () {
		this.callParent(arguments);

		/*this.view.on('refresh', function () {
			this.toggleUiHeader(false);
		}, this);*/
	},

	getHeaderConfig: function() {
		var c = this.callParent();
		c.width = 140;
		c.minWidth = c.width;
		c.headerId = 'scalrSelectedModelCheckbox';
		c.text = '';
		return c;
	},

	getVisibility: function (record) {
		return true;
	},


	// getVisibility (record) for menu
	// getOptionVisibility

	renderer: function (value, meta, record, rowIndex, colIndex) {
		//if (this.headerCt.getHeaderAtIndex(colIndex).getVisibility(record))
		return '<div class="x-grid-row-options"><div class="x-grid-row-options-checkbox x-grid-row-checker"></div>Actions<div class="x-grid-row-options-trigger"></div></div>';
	},

/*	renderer: function(value, metaData, record, rowIndex, colIndex, store, view) {
		metaData.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';
		metaData.style = 'margin-left: 5px';

		if (this.getVisibility(record))
			return '<div class="' + Ext.baseCSSPrefix + 'grid-row-checker">&#160;</div>';
	},*/

	// don't check unavailable items
	selectAll: function(suppressEvent) {
		var me = this,
			selections = [],
			i = 0,
			len,
			start = me.getSelection().length;

		Ext.each(me.store.getRange(), function (record) {
			if (this.getVisibility(record))
				selections.push(record);
		}, this);

		len = selections.length;

		me.bulkChange = true;
		for (; i < len; i++) {
			me.doSelect(selections[i], true, suppressEvent);
		}
		delete me.bulkChange;
		// fire selection change only if the number of selections differs
		me.maybeFireSelectionChange(me.getSelection().length !== start);
	},

	onSelectChange: function() {
		this.callParent(arguments);

		// check to see if all records are selected
		var me = this, selections = [];
		Ext.each(me.store.getRange(), function (record) {
			if (this.getVisibility(record))
				selections.push(record);
		}, this);

		var hdSelectStatus = this.selected.getCount() === selections.length;
		this.toggleUiHeader(hdSelectStatus);
	},

	/*onHeaderClick: function(headerCt, header, e) {
		if (header.isCheckerHd && !e.getTarget('span.x-column-header-text', 1)) {
			// show menu only if it's not span
			var btnEl = Ext.get(e.getTarget('div.x-column-header-checkbox')), xy = btnEl.getXY();

			if (this.selected.length)
				this.selectedMenu.el.unmask();
			else
				this.selectedMenu.el.mask();

			this.selectedMenu.show();
			this.selectedMenu.setPosition([xy[0] - (this.selectedMenu.getWidth()  - btnEl.getWidth()), xy[1] + btnEl.getHeight() + 1]);
			e.stopEvent();
		} else {
			this.callParent(arguments);
		}
	},*/

	// keyNav
	onKeyEnd: function(e) {
		var me = this,
			last = me.store.getAt(me.store.getCount() - 1);

		if (last) {
			me.setLastFocused(last);
		}
	},

	onKeyHome: function(e) {
		var me = this,
			first = me.store.getAt(0);

		if (first) {
			me.setLastFocused(first);
		}
	},

	onKeyPageUp: function(e) {
		var me = this,
			rowsVisible = me.getRowsVisible(),
			selIdx,
			prevIdx,
			prevRecord;

		if (rowsVisible) {
			selIdx = e.recordIndex;
			prevIdx = selIdx - rowsVisible;
			if (prevIdx < 0) {
				prevIdx = 0;
			}
			prevRecord = me.store.getAt(prevIdx);
			me.setLastFocused(prevRecord);
		}
	},

	onKeyPageDown: function(e) {
		var me = this,
			rowsVisible = me.getRowsVisible(),
			selIdx,
			nextIdx,
			nextRecord;

		if (rowsVisible) {
			selIdx = e.recordIndex;
			nextIdx = selIdx + rowsVisible;
			if (nextIdx >= me.store.getCount()) {
				nextIdx = me.store.getCount() - 1;
			}
			nextRecord = me.store.getAt(nextIdx);
			me.setLastFocused(nextRecord);
		}
	},

	onKeySpace: function(e) {
		var me = this,
			record = me.lastFocused;

		if (record) {
			if (me.isSelected(record)) {
				me.doDeselect(record, false);
			} else if(me.getVisibility(record)) {
				me.doSelect(record, true);
			}
		}
	},

	onKeyUp: function(e) {
		var me = this,
			idx  = me.store.indexOf(me.lastFocused),
			record;

		if (idx > 0) {
			// needs to be the filtered count as thats what
			// will be visible.
			record = me.store.getAt(idx - 1);
			me.setLastFocused(record);
		}
	},

	onKeyDown: function(e) {
		var me = this,
			idx  = me.store.indexOf(me.lastFocused),
			record;

		// needs to be the filtered count as thats what
		// will be visible.
		if (idx + 1 < me.store.getCount()) {
			record = me.store.getAt(idx + 1);
			me.setLastFocused(record);
		}
	}
});

/**
 * @class Ext.ux.RowExpander
 * @extends Ext.AbstractPlugin
 * Plugin (ptype = 'rowexpander') that adds the ability to have a Column in a grid which enables
 * a second row body which expands/contracts.  The expand/contract behavior is configurable to react
 * on clicking of the column, double click of the row, and/or hitting enter while a row is selected.
 *
 * @ptype rowexpander
 */
Ext.define('Ext.ux.RowExpander', {
	extend: 'Ext.AbstractPlugin',

	requires: [
		'Ext.grid.feature.RowBody',
		'Ext.grid.feature.RowWrap'
	],

	alias: 'plugin.rowexpander',

	rowBodyTpl: null,

	/**
	 * @cfg {Boolean} expandOnEnter
	 * <tt>true</tt> to toggle selected row(s) between expanded/collapsed when the enter
	 * key is pressed (defaults to <tt>true</tt>).
	 */
	expandOnEnter: true,

	/**
	 * @cfg {Boolean} expandOnDblClick
	 * <tt>true</tt> to toggle a row between expanded/collapsed when double clicked
	 * (defaults to <tt>true</tt>).
	 */
	expandOnDblClick: false,

	/**
	 * @cfg {Boolean} selectRowOnExpand
	 * <tt>true</tt> to select a row when clicking on the expander icon
	 * (defaults to <tt>false</tt>).
	 */
	selectRowOnExpand: false,

	rowBodyTrSelector: '.x-grid-rowbody-tr',
	rowBodyHiddenCls: 'x-grid-row-body-hidden',
	rowCollapsedCls: 'x-grid-row-collapsed',

	renderer: function(value, metadata, record, rowIdx, colIdx) {
		if (colIdx === 0) {
			metadata.tdCls = 'x-grid-td-expander';
		}
		return '<div class="x-grid-row-expander">&#160;</div>';
	},

	/**
	 * @event expandbody
	 * <b<Fired through the grid's View</b>
	 * @param {HTMLElement} rowNode The &lt;tr> element which owns the expanded row.
	 * @param {Ext.data.Model} record The record providing the data.
	 * @param {HTMLElement} expandRow The &lt;tr> element containing the expanded data.
	 */
	/**
	 * @event collapsebody
	 * <b<Fired through the grid's View.</b>
	 * @param {HTMLElement} rowNode The &lt;tr> element which owns the expanded row.
	 * @param {Ext.data.Model} record The record providing the data.
	 * @param {HTMLElement} expandRow The &lt;tr> element containing the expanded data.
	 */

	constructor: function() {
		this.callParent(arguments);
		var grid = this.getCmp();
		this.recordsExpanded = {};
		// <debug>
		if (!this.rowBodyTpl) {
			Ext.Error.raise("The 'rowBodyTpl' config is required and is not defined.");
		}
		// </debug>
		// TODO: if XTemplate/Template receives a template as an arg, should
		// just return it back!
		var rowBodyTpl = Ext.create('Ext.XTemplate', this.rowBodyTpl),
			features = [{
				ftype: 'rowbody',
				columnId: this.getHeaderId(),
				recordsExpanded: this.recordsExpanded,
				rowBodyHiddenCls: this.rowBodyHiddenCls,
				rowCollapsedCls: this.rowCollapsedCls,
				getAdditionalData: this.getRowBodyFeatureData,
				getRowBodyContents: function(data) {
					return rowBodyTpl.applyTemplate(data);
				}
			},{
				ftype: 'rowwrap'
			}];

		if (grid.features) {
			grid.features = features.concat(grid.features);
		} else {
			grid.features = features;
		}

		// NOTE: features have to be added before init (before Table.initComponent)
	},

	init: function(grid) {
		this.callParent(arguments);
		this.grid = grid;
		// Columns have to be added in init (after columns has been used to create the
		// headerCt). Otherwise, shared column configs get corrupted, e.g., if put in the
		// prototype.
		this.addExpander();
		grid.on('render', this.bindView, this, {single: true});
		grid.on('reconfigure', this.onReconfigure, this);
	},

	onReconfigure: function(){
		this.addExpander();
	},

	addExpander: function(){
		this.grid.headerCt.insert(0, this.getHeaderConfig());
		this.grid.headerCt.items.getAt(1).addCls('x-grid-header-special-after');
	},

	getHeaderId: function() {
		if (!this.headerId) {
			this.headerId = Ext.id();
		}
		return this.headerId;
	},

	getRowBodyFeatureData: function(data, idx, record, orig) {
		var o = Ext.grid.feature.RowBody.prototype.getAdditionalData.apply(this, arguments),
			id = this.columnId;
		o.rowBodyColspan = o.rowBodyColspan - 1;
		o.rowBody = this.getRowBodyContents(data);
		o.rowCls = this.recordsExpanded[record.internalId] ? '' : this.rowCollapsedCls;
		o.rowBodyCls = this.recordsExpanded[record.internalId] ? '' : this.rowBodyHiddenCls;
		o[id + '-tdAttr'] = ' valign="top" rowspan="2" ';
		if (orig[id+'-tdAttr']) {
			o[id+'-tdAttr'] += orig[id+'-tdAttr'];
		}
		return o;
	},

	bindView: function() {
		var view = this.getCmp().getView(),
			viewEl;

		if (!view.rendered) {
			view.on('render', this.bindView, this, {single: true});
		} else {
			viewEl = view.getEl();
			if (this.expandOnEnter) {
				this.keyNav = Ext.create('Ext.KeyNav', viewEl, {
					'enter' : this.onEnter,
					scope: this
				});
			}
			if (this.expandOnDblClick) {
				view.on('itemdblclick', this.onDblClick, this);
			}
			this.view = view;
		}
	},

	onEnter: function(e) {
		var view = this.view,
			ds   = view.store,
			sm   = view.getSelectionModel(),
			sels = sm.getSelection(),
			ln   = sels.length,
			i = 0,
			rowIdx;

		for (; i < ln; i++) {
			rowIdx = ds.indexOf(sels[i]);
			this.toggleRow(rowIdx);
		}
	},

	toggleRow: function(rowIdx) {
		var view = this.view,
			rowNode = view.getNode(rowIdx),
			row = Ext.get(rowNode),
			nextBd = Ext.get(row).down(this.rowBodyTrSelector),
			record = view.getRecord(rowNode),
			grid = this.getCmp();

		if (row.hasCls(this.rowCollapsedCls)) {
			row.removeCls(this.rowCollapsedCls);
			nextBd.removeCls(this.rowBodyHiddenCls);
			this.recordsExpanded[record.internalId] = true;
			view.refreshSize();
			view.fireEvent('expandbody', rowNode, record, nextBd.dom);
		} else {
			row.addCls(this.rowCollapsedCls);
			nextBd.addCls(this.rowBodyHiddenCls);
			this.recordsExpanded[record.internalId] = false;
			view.refreshSize();
			view.fireEvent('collapsebody', rowNode, record, nextBd.dom);
		}
	},

	onDblClick: function(view, cell, rowIdx, cellIndex, e) {
		this.toggleRow(rowIdx);
	},

	getHeaderConfig: function() {
		var me                = this,
			toggleRow         = Ext.Function.bind(me.toggleRow, me),
			selectRowOnExpand = me.selectRowOnExpand;

		return {
			id: this.getHeaderId(),
			width: 35,
			sortable: false,
			resizable: false,
			draggable: false,
			hideable: false,
			menuDisabled: true,
			cls: Ext.baseCSSPrefix + 'grid-header-special',
			renderer: function(value, metadata) {
				metadata.tdCls = Ext.baseCSSPrefix + 'grid-cell-special';

				return '<div class="' + Ext.baseCSSPrefix + 'grid-row-expander">&#160;</div>';
			},
			processEvent: function(type, view, cell, recordIndex, cellIndex, e) {
				if (type == "mousedown" && e.getTarget('.x-grid-row-expander')) {
					var row = e.getTarget('.x-grid-row');
					toggleRow(row);
					return selectRowOnExpand;
				}
			}
		};
	}
});

/**
 * Base class from Ext.ux.TabReorderer.
 */
Ext.define('Ext.ux.BoxReorderer', {
	mixins: {
		observable: 'Ext.util.Observable'
	},

	/**
	 * @cfg {String} itemSelector
	 * A {@link Ext.DomQuery DomQuery} selector which identifies the encapsulating elements of child
	 * Components which participate in reordering.
	 */
	itemSelector: '.x-box-item',

	/**
	 * @cfg {Mixed} animate
	 * If truthy, child reordering is animated so that moved boxes slide smoothly into position.
	 * If this option is numeric, it is used as the animation duration in milliseconds.
	 */
	animate: 100,

	constructor: function() {
		this.addEvents(
			/**
			 * @event StartDrag
			 * Fires when dragging of a child Component begins.
			 * @param {Ext.ux.BoxReorderer} this
			 * @param {Ext.container.Container} container The owning Container
			 * @param {Ext.Component} dragCmp The Component being dragged
			 * @param {Number} idx The start index of the Component being dragged.
			 */
			'StartDrag',
			/**
			 * @event Drag
			 * Fires during dragging of a child Component.
			 * @param {Ext.ux.BoxReorderer} this
			 * @param {Ext.container.Container} container The owning Container
			 * @param {Ext.Component} dragCmp The Component being dragged
			 * @param {Number} startIdx The index position from which the Component was initially dragged.
			 * @param {Number} idx The current closest index to which the Component would drop.
			 */
			'Drag',
			/**
			 * @event ChangeIndex
			 * Fires when dragging of a child Component causes its drop index to change.
			 * @param {Ext.ux.BoxReorderer} this
			 * @param {Ext.container.Container} container The owning Container
			 * @param {Ext.Component} dragCmp The Component being dragged
			 * @param {Number} startIdx The index position from which the Component was initially dragged.
			 * @param {Number} idx The current closest index to which the Component would drop.
			 */
			'ChangeIndex',
			/**
			 * @event Drop
			 * Fires when a child Component is dropped at a new index position.
			 * @param {Ext.ux.BoxReorderer} this
			 * @param {Ext.container.Container} container The owning Container
			 * @param {Ext.Component} dragCmp The Component being dropped
			 * @param {Number} startIdx The index position from which the Component was initially dragged.
			 * @param {Number} idx The index at which the Component is being dropped.
			 */
			'Drop'
		);
		this.mixins.observable.constructor.apply(this, arguments);
	},

	init: function(container) {
		var me = this;

		me.container = container;

		// Set our animatePolicy to animate the start position (ie x for HBox, y for VBox)
		me.animatePolicy = {};
		me.animatePolicy[container.getLayout().names.x] = true;



		// Initialize the DD on first layout, when the innerCt has been created.
		me.container.on({
			scope: me,
			boxready: me.afterFirstLayout,
			destroy: me.onContainerDestroy
		});
	},

	/**
	 * @private Clear up on Container destroy
	 */
	onContainerDestroy: function() {
		if (this.dd) {
			this.dd.unreg();
		}
	},

	afterFirstLayout: function() {
		var me = this,
			layout = me.container.getLayout(),
			names = layout.names,
			dd;

		// Create a DD instance. Poke the handlers in.
		// TODO: Ext5's DD classes should apply config to themselves.
		// TODO: Ext5's DD classes should not use init internally because it collides with use as a plugin
		// TODO: Ext5's DD classes should be Observable.
		// TODO: When all the above are trus, this plugin should extend the DD class.
		dd = me.dd = Ext.create('Ext.dd.DD', layout.innerCt, me.container.id + '-reorderer');
		Ext.apply(dd, {
			animate: me.animate,
			reorderer: me,
			container: me.container,
			getDragCmp: this.getDragCmp,
			clickValidator: Ext.Function.createInterceptor(dd.clickValidator, me.clickValidator, me, false),
			onMouseDown: me.onMouseDown,
			startDrag: me.startDrag,
			onDrag: me.onDrag,
			endDrag: me.endDrag,
			getNewIndex: me.getNewIndex,
			doSwap: me.doSwap,
			findReorderable: me.findReorderable
		});

		// Decide which dimension we are measuring, and which measurement metric defines
		// the *start* of the box depending upon orientation.
		dd.dim = names.width;
		dd.startAttr = names.left;
		dd.endAttr = names.right;
	},

	getDragCmp: function(e) {
		return this.container.getChildByElement(e.getTarget(this.itemSelector, 10));
	},

	// check if the clicked component is reorderable
	clickValidator: function(e) {
		var cmp = this.getDragCmp(e);

		// If cmp is null, this expression MUST be coerced to boolean so that createInterceptor is able to test it against false
		return !!(cmp && cmp.reorderable !== false);
	},

	onMouseDown: function(e) {
		var me = this,
			container = me.container,
			containerBox,
			cmpEl,
			cmpBox;

		// Ascertain which child Component is being mousedowned
		me.dragCmp = me.getDragCmp(e);
		if (me.dragCmp) {
			cmpEl = me.dragCmp.getEl();
			me.startIndex = me.curIndex = container.items.indexOf(me.dragCmp);

			// Start position of dragged Component
			cmpBox = cmpEl.getPageBox();

			// Last tracked start position
			me.lastPos = cmpBox[this.startAttr];

			// Calculate constraints depending upon orientation
			// Calculate offset from mouse to dragEl position
			containerBox = container.el.getPageBox();
			if (me.dim === 'width') {
				me.minX = containerBox.left;
				me.maxX = containerBox.right - cmpBox.width;
				me.minY = me.maxY = cmpBox.top;
				me.deltaX = e.getPageX() - cmpBox.left;
			} else {
				me.minY = containerBox.top;
				me.maxY = containerBox.bottom - cmpBox.height;
				me.minX = me.maxX = cmpBox.left;
				me.deltaY = e.getPageY() - cmpBox.top;
			}
			me.constrainY = me.constrainX = true;
		}
	},

	startDrag: function() {
		var me = this,
			dragCmp = me.dragCmp;

		if (dragCmp) {
			// For the entire duration of dragging the *Element*, defeat any positioning and animation of the dragged *Component*
			dragCmp.setPosition = Ext.emptyFn;
			dragCmp.animate = false;

			// Animate the BoxLayout just for the duration of the drag operation.
			if (me.animate) {
				me.container.getLayout().animatePolicy = me.reorderer.animatePolicy;
			}
			// We drag the Component element
			me.dragElId = dragCmp.getEl().id;
			me.reorderer.fireEvent('StartDrag', me, me.container, dragCmp, me.curIndex);
			// Suspend events, and set the disabled flag so that the mousedown and mouseup events
			// that are going to take place do not cause any other UI interaction.
			dragCmp.suspendEvents();
			dragCmp.disabled = true;
			dragCmp.el.setStyle('zIndex', 100);
		} else {
			me.dragElId = null;
		}
	},

	/**
	 * @private
	 * Find next or previous reorderable component index.
	 * @param {Number} newIndex The initial drop index.
	 * @return {Number} The index of the reorderable component.
	 */
	findReorderable: function(newIndex) {
		var me = this,
			items = me.container.items,
			newItem;

		if (items.getAt(newIndex).reorderable === false) {
			newItem = items.getAt(newIndex);
			if (newIndex > me.startIndex) {
				while(newItem && newItem.reorderable === false) {
					newIndex++;
					newItem = items.getAt(newIndex);
				}
			} else {
				while(newItem && newItem.reorderable === false) {
					newIndex--;
					newItem = items.getAt(newIndex);
				}
			}
		}

		newIndex = Math.min(Math.max(newIndex, 0), items.getCount() - 1);

		if (items.getAt(newIndex).reorderable === false) {
			return -1;
		}
		return newIndex;
	},

	/**
	 * @private
	 * Swap 2 components.
	 * @param {Number} newIndex The initial drop index.
	 */
	doSwap: function(newIndex) {
		var me = this,
			items = me.container.items,
			container = me.container,
			wasRoot = me.container._isLayoutRoot,
			orig, dest, tmpIndex, temp;

		newIndex = me.findReorderable(newIndex);

		if (newIndex === -1) {
			return;
		}

		me.reorderer.fireEvent('ChangeIndex', me, container, me.dragCmp, me.startIndex, newIndex);
		orig = items.getAt(me.curIndex);
		dest = items.getAt(newIndex);
		items.remove(orig);
		tmpIndex = Math.min(Math.max(newIndex, 0), items.getCount() - 1);
		items.insert(tmpIndex, orig);
		items.remove(dest);
		items.insert(me.curIndex, dest);

		// Make the Box Container the topmost layout participant during the layout.
		container._isLayoutRoot = true;
		container.updateLayout();
		container._isLayoutRoot = wasRoot;
		me.curIndex = newIndex;
	},

	onDrag: function(e) {
		var me = this,
			newIndex;

		newIndex = me.getNewIndex(e.getPoint());
		if ((newIndex !== undefined)) {
			me.reorderer.fireEvent('Drag', me, me.container, me.dragCmp, me.startIndex, me.curIndex);
			me.doSwap(newIndex);
		}

	},

	endDrag: function(e) {
		if (e) {
			e.stopEvent();
		}
		var me = this,
			layout = me.container.getLayout(),
			temp;

		if (me.dragCmp) {
			delete me.dragElId;

			// Reinstate the Component's positioning method after mouseup, and allow the layout system to animate it.
			delete me.dragCmp.setPosition;
			me.dragCmp.animate = true;

			// Ensure the lastBox is correct for the animation system to restore to when it creates the "from" animation frame
			me.dragCmp.lastBox[layout.names.x] = me.dragCmp.getPosition(true)[layout.names.widthIndex];

			// Make the Box Container the topmost layout participant during the layout.
			me.container._isLayoutRoot = true;
			me.container.updateLayout();
			me.container._isLayoutRoot = undefined;

			// Attempt to hook into the afteranimate event of the drag Component to call the cleanup
			temp = Ext.fx.Manager.getFxQueue(me.dragCmp.el.id)[0];
			if (temp) {
				temp.on({
					afteranimate: me.reorderer.afterBoxReflow,
					scope: me
				});
			}
			// If not animated, clean up after the mouseup has happened so that we don't click the thing being dragged
			else {
				Ext.Function.defer(me.reorderer.afterBoxReflow, 1, me);
			}

			if (me.animate) {
				delete layout.animatePolicy;
			}
			me.reorderer.fireEvent('drop', me, me.container, me.dragCmp, me.startIndex, me.curIndex);
		}
	},

	/**
	 * @private
	 * Called after the boxes have been reflowed after the drop.
	 * Re-enabled the dragged Component.
	 */
	afterBoxReflow: function() {
		var me = this;
		me.dragCmp.el.setStyle('zIndex', '');
		me.dragCmp.disabled = false;
		me.dragCmp.resumeEvents();
	},

	/**
	 * @private
	 * Calculate drop index based upon the dragEl's position.
	 */
	getNewIndex: function(pointerPos) {
		var me = this,
			dragEl = me.getDragEl(),
			dragBox = Ext.fly(dragEl).getPageBox(),
			targetEl,
			targetBox,
			targetMidpoint,
			i = 0,
			it = me.container.items.items,
			ln = it.length,
			lastPos = me.lastPos;

		me.lastPos = dragBox[me.startAttr];

		for (; i < ln; i++) {
			targetEl = it[i].getEl();

			// Only look for a drop point if this found item is an item according to our selector
			if (targetEl.is(me.reorderer.itemSelector)) {
				targetBox = targetEl.getPageBox();
				targetMidpoint = targetBox[me.startAttr] + (targetBox[me.dim] >> 1);
				if (i < me.curIndex) {
					if ((dragBox[me.startAttr] < lastPos) && (dragBox[me.startAttr] < (targetMidpoint - 5))) {
						return i;
					}
				} else if (i > me.curIndex) {
					if ((dragBox[me.startAttr] > lastPos) && (dragBox[me.endAttr] > (targetMidpoint + 5))) {
						return i;
					}
				}
			}
		}
	}
});

/*
 * Toolbar fields
 */

Ext.define('Scalr.ui.ToolbarFieldSwitch', {
	extend: 'Ext.toolbar.TextItem',
	alias: 'widget.tbswitchfield',

	cls: 'scalr-ui-btn-icon-viewswitch',
	text: '<div class="grid"></div><div class="view"></div>',
	
	getState: function () {
		return {
			switchValue: this.switchValue
		};
	},
	
	changeSwitch: function (value) {
		this.switchValue = value;
		this.onStateChange();
	},
	
	onRender: function () {
		this.callParent(arguments);
		
		if (this.switchValue == 'view')
			this.addCls('scalr-ui-btn-icon-viewswitch-view');
		else
			this.addCls('scalr-ui-btn-icon-viewswitch-grid');
		
		this.el.down('.grid').on('click', function () {
			this.removeCls('scalr-ui-btn-icon-viewswitch-view');
			this.addCls('scalr-ui-btn-icon-viewswitch-grid');
			this.changeSwitch('grid');
		}, this);
		
		this.el.down('.view').on('click', function () {
			this.removeCls('scalr-ui-btn-icon-viewswitch-grid');
			this.addCls('scalr-ui-btn-icon-viewswitch-view');
			this.changeSwitch('view');
		}, this);
	}
});

Ext.define('Scalr.ui.CustomButton', {
	alias: 'widget.custombutton',
	extend: 'Ext.Component',

	hidden: false,
	disabled: false,
	pressed: false,
	enableToggle: false,
	maskOnDisable: false,

	childEls: [ 'btnEl' ],

	overCls: 'x-btn-custom-over',
	pressedCls: 'x-btn-custom-pressed',
	disabledCls: 'x-btn-custom-disabled',
	tooltipType: 'qtip',
	
	initComponent: function() {
		var me = this;
		me.callParent(arguments);

		me.addEvents('click', 'toggle');

		if (Ext.isString(me.toggleGroup)) {
			me.enableToggle = true;
		}

		me.renderData['disabled'] = me.disabled;
	},

	onRender: function () {
		var me = this;

		me.callParent(arguments);

		me.mon(me.btnEl, {
			click: me.onClick,
			scope: me
		});

		if (me.pressed)
			me.addCls(me.pressedCls);

		Ext.ButtonToggleManager.register(me);
		
        if (me.tooltip) {
            me.setTooltip(me.tooltip, true);
        }
		
	},

	onDestroy: function() {
		var me = this;
		if (me.rendered) {
			Ext.ButtonToggleManager.unregister(me);
			me.clearTip();
		}
		me.callParent();
	},

	toggle: function(state, suppressEvent) {
		var me = this;
		state = state === undefined ? !me.pressed : !!state;
		if (state !== me.pressed) {
			if (me.rendered) {
				me[state ? 'addCls': 'removeCls'](me.pressedCls);
			}
			me.pressed = state;
			if (!suppressEvent) {
				me.fireEvent('toggle', me, state);
				Ext.callback(me.toggleHandler, me.scope || me, [me, state]);
			}
		}
		return me;
	},

	onClick: function(e) {
		var me = this;
		if (! me.disabled) {
			me.doToggle();
			me.fireHandler(e);
		}
	},

	fireHandler: function(e){
		var me = this,
		handler = me.handler;

		me.fireEvent('click', me, e);
		if (handler) {
			handler.call(me.scope || me, me, e);
		}
	},

	doToggle: function(){
		var me = this;
		if (me.enableToggle && (me.allowDepress !== false || !me.pressed)) {
			me.toggle();
		}
	},
	
	setTooltip: function(tooltip, initial) {
		var me = this;

		if (me.rendered) {
			if (!initial) {
				me.clearTip();
			}
			if (Ext.isObject(tooltip)) {
				Ext.tip.QuickTipManager.register(Ext.apply({
					target: me.btnEl.id
				},
				tooltip));
				me.tooltip = tooltip;
			} else {
				me.btnEl.dom.setAttribute(me.getTipAttr(), tooltip);
			}
		} else {
			me.tooltip = tooltip;
		}
		return me;
	},
	
	getTipAttr: function(){
		return this.tooltipType == 'qtip' ? 'data-qtip' : 'title';
	},
	
    clearTip: function() {
        if (Ext.isObject(this.tooltip)) {
            Ext.tip.QuickTipManager.unregister(this.btnEl);
        }
    }
});

Ext.define('Scalr.ui.GridPanelTool', {
	extend: 'Ext.panel.Tool',
	alias: 'widget.gridcolumnstool',

	initComponent: function () {
		this.type = 'settings';
		this.callParent();
	},

	gridSettingsForm: function () {
		var columnsFieldset = new Ext.form.FieldSet({
			title: 'Grid columns to show'
		});
		var checkboxGroup = columnsFieldset.add({
			xtype: 'checkboxgroup',
				columns: 2,
				vertical: true
		});
		var grid = this.up('panel'),
			columns = grid.columns;
			
		for(var i in columns) {
			if(columns[i].hideable) {
				checkboxGroup.add({
					xtype: 'checkbox',
					boxLabel: columns[i].text,
					checked: !columns[i].hidden,
					name: columns[i].text,
					inputValue: 1
				});
			}
		}
		var autorefreshFieldset = new Ext.form.FieldSet({
			title: 'Autorefresh',
			items: {
				xtype: 'checkbox',
				boxLabel: 'Enable',
				inputValue: 60,
				checked: this.up('panel').down('scalrpagingtoolbar').autoRefresh,
				name: 'autoRefresh'
			}
		});
		
		var resetColumnsWidthFieldset = new Ext.form.FieldSet({
			items: {
				xtype: 'button',
				text: '<span style="font-weight:normal">Reset columns width</span>',
				handler: function(){
					grid.suspendLayouts();
					for (var i=0, len=columns.length; i<len; i++) {
						if (columns[i].initialConfig.flex) {
							columns[i].flex = columns[i].initialConfig.flex;
							if (columns[i].width) {
								delete columns[i].width;
							}
						} else if (columns[i].initialConfig.width) {
							columns[i].width = columns[i].initialConfig.width;
						}
					}
					grid.resumeLayouts(true);
				}
			}
		});
		return [columnsFieldset, resetColumnsWidthFieldset, autorefreshFieldset];
	},

	handler: function () {
		var me = this;
		var grid = me.up('panel'),
			columns = grid.columns;
		Scalr.Confirm({
			title: 'Grid settings',
			form: me.gridSettingsForm(),
			success: function (data) {
				for(var i in columns) {
					if(data[columns[i].text])
						columns[i].show();
					if(!data[columns[i].text] && columns[i].hideable)
						columns[i].hide();
				}
				grid.fireEvent('resize');
				if(data['autoRefresh'])
					this.up('panel').down('scalrpagingtoolbar').checkRefreshHandler({'autoRefresh': data['autoRefresh']}, true);
				else
					this.up('panel').down('scalrpagingtoolbar').checkRefreshHandler({'autoRefresh': 0}, true);
			},
			scope: this
		});
	}
});

Ext.define('Scalr.ui.PanelTool', {
	extend: 'Ext.panel.Tool',
	alias: 'widget.favoritetool',

	/** Example:
	 *
	 favorite: {
	    text: 'Farms',
	    href: '#/farms/view'
	 }
	 */
	favorite: {},

	initComponent: function () {
		this.type = 'favorite';
		this.favorite.hrefTarget = '_self';
		var favorites = Scalr.storage.get('system-favorites');

		Ext.each(favorites, function (item) {
			if (item.href == this.favorite['href']) {
				this.type = 'favorite-checked';
				return false;
			}
		}, this);

		this.callParent();
	},

	handler: function () {
		var favorites = Scalr.storage.get('system-favorites') || [], enabled = this.type == 'favorite-checked', href = this.favorite.href, menu = Scalr.application.getDockedComponent('top');

		if (enabled) {
			var index = menu.items.findIndex('href', this.favorite.href);
			menu.remove(menu.items.getAt(index));

			Ext.Array.each(favorites, function(item) {
				if (item.href == href) {
					Ext.Array.remove(favorites, item);
					return false;
				}
			});
			this.setType('favorite');
		} else {
			var index = menu.items.findIndex('xtype', 'tbfill'), fav = Scalr.utils.CloneObject(this.favorite);
			Ext.apply(fav, {
				hrefTarget: '_self',
				reorderable: true,
				cls: 'x-btn-favorite',
				overCls: 'btn-favorite-over',
				pressedCls: 'btn-favorite-pressed'
			});
			menu.insert(index, fav);
			favorites.push(this.favorite);
			this.setType('favorite-checked');
		}

		Scalr.storage.set('system-favorites', favorites);
	}
});

Ext.define('Scalr.ui.MenuItemTop', {
	extend: 'Ext.menu.Item',
	alias: 'widget.menuitemtop',

	renderTpl: [
		'<div id="{id}-itemEl" class="' + Ext.baseCSSPrefix + 'menu-item-link">',
			'<img id="{id}-iconEl" src="{icon}" class="' + Ext.baseCSSPrefix + 'menu-item-icon {iconCls}" />',
			'<tpl if="links"><div class="x-menu-item-links">',
				'<tpl for="links"><a href="{href}" target="_self" class="{cls}">{text}</a></tpl>',
			'</div></tpl>',
			'<img id="{id}-arrowEl" src="{blank}" class="{arrowCls}" />',
			'<a id="{id}-textEl" class="' + Ext.baseCSSPrefix + 'menu-item-text" <tpl if="arrowCls">style="margin-right: 17px;" </tpl><tpl if="href">href="{href}" </tpl> ><span>{text}</span></a>',
			'<div style="clear: both"></div>',
		'</div>',
	],

	beforeRender: function () {
		var me = this;
		me.callParent();

		if (me.href)
			Ext.applyIf(me.renderData, {
				href: me.href
			});

		if (me.links)
			Ext.applyIf(me.renderData, {
				links: me.links
			});
	},

	onClick: function(e) {
		var me = this;

		//if (!me.href) {
		//	e.stopEvent();
		//}

		if (me.disabled) {
			return;
		}

		if (me.hideOnClick) {
			me.deferHideParentMenusTimer = Ext.defer(me.deferHideParentMenus, me.clickHideDelay, me);
		}

		Ext.callback(me.handler, me.scope || me, [me, e]);
		me.fireEvent('click', me, e);

		if (!me.hideOnClick) {
			me.focus();
		}
	}
});

// DEPRECATED, remove with old farm builder
Ext.define('Scalr.ui.FormComboButton', {
	extend: 'Ext.container.Container',
	alias: 'widget.combobutton',

	cls: 'x-form-combobutton',
	handler: Ext.emptyFn,
	privateHandler: function (btn) {
		this.handler(btn.value, btn);
	},

	initComponent: function () {
		var me = this, groupName = this.getId() + '-button-group';

		for (var i = 0; i < me.items.length; i++) {
			Ext.apply(me.items[i], {
				enableToggle: true,
				toggleGroup: groupName,
				allowDepress: false,
				handler: me.privateHandler,
				scope: me
			});
		}

		me.callParent();
	},

	afterRender: function () {
		this.callParent(arguments);

		this.items.first().addCls('x-btn-default-small-combo-first');
		this.items.last().addCls('x-btn-default-small-combo-last');
	},

	getValue: function () {
		var b = Ext.ButtonToggleManager.getPressed(this.getId() + '-button-group');
		if (b)
			return b.value;
	}
});

Ext.define('Scalr.ui.AddFieldPlugin', {
	extend: 'Ext.AbstractPlugin',
	alias: 'plugin.addfield',
	pluginId: 'addfield',

	init: function (client) {
		var me = this;
        me.client = client;
		client.on('afterrender', function() {
			me.panelContainer = Ext.DomHelper.insertAfter(client.el.down('tbody'), {style: {height: '32px'}}, true);
			var addmask = Ext.DomHelper.append(me.panelContainer,
				'<div style="position: absolute; width: 95%; height: 31px;">' +
					'<div class="x-form-addfield-plus"></div>' +
					'</div>'
				, true);
			addmask.down('div.x-form-addfield-plus').on('click', me.handler, client);
		}, client);
	},
    run: function() {
        this.handler.call(this.client);
    },
	hide: function () {
		if (this.panelContainer)
			this.panelContainer.remove();
	}
});

Ext.define('Scalr.ui.FormTextCodeMirror', {
	extend: 'Ext.form.field.Base',
	alias: 'widget.codemirror',
	
	readOnly: false,
	addResizeable: false,
	
	fieldSubTpl: '<div id="{id}"></div>',
	enterIsSpecial: false,
	mode: '', // set to prevent mode recognition
	
	setMode: function (cm) {
		if (this.mode) {
			cm.setOption('mode', this.mode);
			return;
		}

		// #! ... /bin/(language)
		var value = cm.getValue(), mode = /^#!.*\/bin\/(.*)$/.exec(Ext.String.trim(value.split("\n")[0]));
		mode = mode && mode.length == 2 ? mode[1] : '';

		switch (mode) {
			case 'python':
				cm.setOption('mode', 'python');
				break;

			case 'bash': case 'sh':
				cm.setOption('mode', 'shell');
			break;

			case 'php':
				cm.setOption('mode', 'php');
				break;

			case 'python':
				cm.setOption('mode', 'python');
				break;

			default:
				cm.setOption('mode', 'text/plain');
				break;
		}
	},
	afterRender: function () {
		this.callParent(arguments);
		this.codeMirror = CodeMirror(this.inputEl, {
			value: this.getRawValue(),
			readOnly: this.readOnly,
			onChange: Ext.Function.bind(function (editor, changes) {
				if (changes.from.line == 0)
					this.setMode(editor);

				var value = editor.getValue();
				this.setRawValue(value);
				this.mixins.field.setValue.call(this, value);

				/*var el = Ext.fly(this.codeMirror.getWrapperElement()).down('.CodeMirror-lines').child('div');
				console.log(el.getHeight());
				this.setHeight(el.getHeight() + 14); // padding
				//this.setSize();
				this.updateLayout();

				//console.log(editor.get)*/
			}, this)
		});

		this.setMode(this.codeMirror);

		//this.codeMirror.setSize('100%', '100%');

		this.on('resize', function (comp, width, height) {
			//debugger;
			Ext.fly(this.codeMirror.getWrapperElement()).setSize(width, height);
			this.codeMirror.refresh();
		});
		
		if (this.addResizeable) {
			Ext.fly(this.codeMirror.getWrapperElement()).addCls('codemirror-resizeable');
			new Ext.Resizable(this.codeMirror.getWrapperElement(), {
				minHeight:this.minHeight,
				handles: 's',
				pinned: true,
				listeners: {
					resizedrag: function(){
						this.target.up('.x-panel-body-frame').dom.scrollTop = 99999;
					}
				}
			});
		}
		
	},
	getRawValue: function () {
		var me = this,
			v = (me.codeMirror ? me.codeMirror.getValue() : Ext.value(me.rawValue, ''));
		me.rawValue = v;
		return v;
	},
	setRawValue: function (value) {
		var me = this;
		value = Ext.value(me.transformRawValue(value), '');
		me.rawValue = value;

		return value;
	},
	setValue: function(value) {
		var me = this;
		me.setRawValue(me.valueToRaw(value));

		if (me.codeMirror)
			me.codeMirror.setValue(value);

		return me.mixins.field.setValue.call(me, value);
	}
});

Ext.define('Scalr.ui.FormFieldPassword', {
    extend: 'Ext.form.field.Text',
	alias: 'widget.passwordfield',
	
	inputType:'password',
	allowBlank: false,
	placeholder: '******',
	initialValue: null,
	
	isPasswordEmpty: function(password) {
		return Ext.isEmpty(password) || password === false;
	},

    getSubmitData: function() {
		if (!this.isPasswordEmpty(this.initialValue) && this.getValue() == this.placeholder ||
			this.isPasswordEmpty(this.initialValue) && this.getValue() == '') {
			return null;
		} else {
			return this.callParent(arguments);
		}
    },

    getModelData: function() {
		var data = {};
        data[this.getName()] = this.getValue() != '' ? true : false;
		return data;
    },

    setValue: function(value) {
		this.initialValue = value;
		if (!this.isPasswordEmpty(value)) {
			value = this.placeholder;
		} else {
			value = '';
		}
		this.callParent(arguments);
	}

});

Ext.define('Scalr.ui.PanelScrollFixPlugin', {
    extend: 'Ext.AbstractPlugin',
    alias: 'plugin.panelscrollfix',
	
    disabled: false,
	client: null,
	
	lastScrollTop: null,
	lastScrollHeight: null,
	
	init: function(client) {
		var me = this;
		me.client = client;
		client.fixScrollTop = function(){
			me.fixScrollTop();
		};
		client.on('render', function(){
			this.items.each(function(i){
				if (i.collapsible) {
					i.on('beforeexpand', function(){
						me.fixScrollTop(true);
					});
					i.on('beforecollapse', function(){
						me.fixScrollTop(true);
					});
				}
			});
		});
	},
	
	fixScrollTop: function(exact) {
		var me = this;
		me.saveScrollPosition();
		me.client.on('afterlayout', function() {
			me.restoreScrollPosition(exact);
		}, me.client, {single: true});
	},
	
	saveScrollPosition: function() {
		this.lastScrollTop = this.client.body.getScroll().top;
		this.lastScrollHeight = this.client.body.getAttribute('scrollHeight');
	},
	
	restoreScrollPosition: function(exact) {
		var scrollHeight = this.client.body.getAttribute('scrollHeight');
		if (this.lastScrollTop !== null) {
			this.client.body.scrollTo( 'top', exact?this.lastScrollTop:(this.lastScrollTop + scrollHeight - this.lastScrollHeight));
		}
	}
});

Ext.define('Scalr.ui.LeftMenu', {
    extend: 'Ext.AbstractPlugin',
    alias: 'plugin.leftmenu',
	
    disabled: false,
	client:null,
	menu: null,
	menuVisible: false,
	
	currentMenuId: null,
	currentItemId: null,
	itemIdPrefix: 'leftmenu-',
	
	componentBodyCls: 'x-panel-leftmenu-body',
	bodyCls: 'x-leftmenu',
	itemCls: 'x-leftmenu-btn',
	itemOverCls: 'x-leftmenu-btn-over',
	itemPressedCls: 'x-leftmenu-btn-pressed',
	
	getMenus: function(menuId){
		var menus = [];
		switch (menuId) {
			case 'account':
				if (Scalr.user['type'] == 'AccountOwner' || Scalr.user['isTeamOwner']) {
					menus.push({
						itemId:'environments',
						renderData: {
							link: '#/account/environments',
							icon: 'environments',
							name: 'Environments'
						}
					});
				}
                if (Scalr.flags['authMode'] == 'scalr') {
                    menus.push({
                        itemId:'teams',
                        renderData: {
                            link: '#/account/teams',
                            icon: 'teams',
                            name: 'Teams'
                        }
                    });
                }

                menus.push({
                    itemId:'users',
                    renderData: {
                        link: '#/account/users',
                        icon: 'users',
                        name: 'Users'
                    }
                });

                if (Scalr.flags['authMode'] == 'scalr' && (Scalr.user['type'] == 'AccountOwner' || Scalr.user['isTeamOwner'])) {
                    menus.push({
                        itemId:'groups',
                        onClick: function(e) {
                            if (!Scalr.flags['featureUsersPermissions']) {
                                Scalr.message.Warning('&laquo;Access control&raquo; feature is not available under your current billing plan.');
                                e.preventDefault();
                            }
                        },
                        renderData: {
                            link: '#/account/groups',
                            icon: 'access-control',
                            name: 'Access control'
                        }
                    });
                }
			    break;
		}
		return menus;
	},
	
	init: function(client) {
		var me = this;
		me.client = client;
	},
	
	create: function() {
		var me = this;
		this.menu = Ext.create('Ext.panel.Panel', {
			hidden: true,
			layout: 'vbox',
			bodyCls: this.bodyCls,
			dock: 'left',
			defaults: {
				xtype: 'custombutton',
				cls: this.itemCls,
				enableToggle: true,
				toggleGroup: 'leftmenu',
				onClick: Ext.emptyFn,
				overCls: this.itemOverCls,
				pressedCls: this.itemPressedCls,
				renderTpl:
					'<div class="x-btn-inner"><a id="{id}-btnEl" href="{link}">'+
						'<span class="x-btn-icon x-btn-icon-{icon}"></span><span class="x-btn-title">{name}</span>'+
					'</a></div>'
			},
			listeners: {
				beforeshow: function(){
					if (me.menuVisible) {
						me.client.body.addCls(me.componentBodyCls);
					} else {
						return false;
					}
				},
				hide: function(){
					me.client.body.removeCls(me.componentBodyCls);
				}
			}
		});
		this.client.addDocked(this.menu);
	},
	
	set: function(options) {
		var me = this;
		if (options.menuId !== this.currentMenuId) {
			this.menu.removeAll();
			this.menu.addCls(this.bodyCls+'-'+options.menuId);
			this.menu.add(Ext.Array.map(this.getMenus(options.menuId), function(item){
				item.itemId = me.itemIdPrefix + item.itemId;
				return item;
			}));
			this.currentMenuId = options.menuId;
			this.currentItemId = null;
		}
		if (options.itemId !== this.currentItemId) {
			this.menu.getComponent(me.itemIdPrefix + options.itemId).doToggle();
			this.currentItemId = options.itemId;
		}
	},
	
	show: function(options) {
		if (this.menu === null) {
			this.create();
		}
		this.set(options);
		this.menuVisible = true;
		this.menu.show();
	},
	
	hide: function() {
		this.menuVisible = false;
		this.menu.hide();
	}
	
});

Ext.define('Scalr.ui.RowPointer', {
    extend: 'Ext.AbstractPlugin',
    alias: 'plugin.rowpointer',
	
    disabled: false,
	client:null,

	cls: 'x-panel-columned-row-pointer',
	addCls: null,
	addOffset: 0,
	thresholdOffset: 50,
	hiddenOffset: -20,
	throttle: 100,
	
	init: function(client) {
		var me = this;
		me.client = client;
		me.throttledUpdatePointerPosition = Ext.Function.createThrottled(me.updatePointerPosition, me.throttle, me);
		
		client.on('afterrender', function() {
			this.addCls(me.cls + (me.addCls ? ' ' + me.addCls  : ''));
			
			this.on('afterlayout', me.throttledUpdatePointerPosition, me);
			this.getSelectionModel().on('focuschange', me.throttledUpdatePointerPosition, me);
			this.view.el.on('scroll', me.throttledUpdatePointerPosition, me)

			client.on('beforedestroy',  function() {
				this.un('afterlayout', me.throttledUpdatePointerPosition, me);
				this.getSelectionModel().un('focuschange', me.throttledUpdatePointerPosition, me);
				this.view.el.un('scroll', me.throttledUpdatePointerPosition, me);
			});
			
		});
	},
	
	updatePointerPosition: function() {
		var record = this.client.getSelectionModel().lastFocused,
			offset;
		if (record) {
			offset = Ext.get(this.client.view.getNode(record)).getOffsetsTo(this.client.el)[1] + this.addOffset;
			offset = offset < this.thresholdOffset ? this.hiddenOffset : offset;
		} else {
			offset = this.hiddenOffset;
		}
		this.client.el.setStyle('background-position','100% '+offset+'px');
	}
	
});

Ext.define('Scalr.ui.LiveSearch', {
    extend: 'Ext.form.field.Trigger',
    alias: 'widget.livesearch',
	
	fieldCls: 'x-form-field x-form-field-livesearch',
	triggerCls: 'x-form-trigger-reset',

	width: 200,
	
	fields: null,
	store: null,
	
	name: 'livesearch',
	hideLabel: true,
	emptyText: 'Filter',
	
	initComponent: function(){
		this.callParent(arguments);
		this.on('change', function(me){
			me.applyFilter();
		}, this, {buffer: 300});
	},
	
	afterRender: function() {
		this.callParent(arguments);
		this.triggerEl.hide();
	},
	
    applyFilter: function() {
		var me = this,
			value= Ext.String.trim(this.getValue());
		me.triggerEl[value != ''?'show':'hide']();
		me.store.clearFilter();
		if (value != '') {
			me.store.filter({filterFn: function(record) {
				var result = false
					,r = new RegExp(Ext.String.escapeRegex(value), 'i');
				for (var i=0, length=me.fields.length; i<length; i++) {
					var fieldValue = Ext.isFunction(me.fields[i]) ? me.fields[i](record) : record.get(me.fields[i]);
					result = (fieldValue+'').match(r);
					if (result) {
						break;
					}
				}
				return result;
			}});
		}
		this.fireEvent('afterfilter');
    },
	
	reset: function() {
		this.suspendEvents(false);
		this.setValue('');
		this.resumeEvents();
		this.applyFilter();
	},
	
	onTriggerClick: function() {
		this.reset();
	}
	
	
});

Ext.define('Scalr.ui.GridField', {
    extend: 'Ext.grid.Panel',
    mixins: {
        //labelable: 'Ext.form.Labelable',
        field: 'Ext.form.field.Field'
    },
    alias: 'widget.gridfield',
	
	multiSelect: true,
	selModel: {
		selType: 'selectedmodel',
		injectCheckbox: 'first'
	},
	fieldReady: false,

	allowBlank: true,

	initComponent : function() {
        var me = this;
 		me.callParent();
		this.initField();
        if (!me.name) {
            me.name = me.getInputId();
        }
		
		this.on('viewready', function(){
			this.fieldReady = true;
			this.setRawValue(this.value);
		});
		this.on('selectionchange', function(selModel, selected){
			this.checkChange();
		});
		this.getStore().on('refresh', function(){
			me.setRawValue(me.value);
		});
	},
  
	setValue: function(value) {
		this.setRawValue(value);
		return this.mixins.field.setValue.call(this, value);
	},

	setRawValue: function(value) {
		if (this.fieldReady) {
			var store = this.getStore(),
				records = [];

			value = value || [];
			for (var i=0, len=value.length; i<len; i++) {
				var record = store.getById(value[i]);
				if (record) {
					records.push(record);
				}
			}
			this.getSelectionModel().select(records, false, true);
		}
	},

    getInputId: function() {
        return this.inputId || (this.inputId = this.id + '-inputEl');
    },
	
    getRawValue: function() {
		var ids = [];
		this.getSelectionModel().selected.each(function(record){
			ids.push(record.get('id'));
		});
		return ids;
    },

	getValue: function() {
		return this.getRawValue();
	},

    getActiveError : function() {
        return this.activeError || '';
    },
	
    getSubmitData: function() {
        var me = this,
            data = null;
        if (!me.disabled && me.submitValue) {
            data = {};
            data[me.getName()] = Ext.encode(me.getValue());
        }
        return data;
    }
	
});

Ext.define('Scalr.ui.FormFieldColor', {
    extend: 'Ext.Component',
    mixins: {
        labelable: 'Ext.form.Labelable',
        field: 'Ext.form.field.Field'
    },
    alias: 'widget.colorfield',
	cls: 'x-form-colorpicker',
    fieldSubTpl: [],

	allowBlank: false,
	componentLayout: 'field',
	button:null,
	backgroundColor: '#000000',
	
    initComponent : function() {
        var me = this;
		me.callParent();
        me.initLabelable();
        me.initField();
        if (!me.name) {
            me.name = me.getInputId();
        }
    },
	
    beforeRender: function(){
        var me = this;
        me.callParent(arguments);
        me.beforeLabelableRender();
    },
	
	afterRender: function() {
		var me = this;
		me.button = Ext.create('Ext.Button', {
			renderTo: this.bodyEl,
			height: 22,
			padding: 0,
			pressedCls: '',
			menuActiveCls: '',
			menu: {
				xtype: 'colormenu', 
				cls: 'x-form-colorpicker-menu',
				allowReselect: true,
				colors: ['333333', 'DF2200', 'AA00AA', '1A4D99', '3D690C', '006666', '6F8A02', '0C82C0', 'CA4B00', '671F92'],
				listeners: {
					select: function(picker, color){
						me.setValue(color);
					}
				}
			}
		});
		this.fieldReady = true;
	},
	
    getInputId: function() {
        return this.inputId || (this.inputId = this.id + '-inputEl');
    },

    initRenderTpl: function() {
        var me = this;
        me.renderTpl = me.getTpl('labelableRenderTpl');
        return me.callParent();
    },

    initRenderData: function() {
        return Ext.applyIf(this.callParent(), this.getLabelableRenderData());
    },

	setRawValue: function(value) {
		if (this.fieldReady) {
			var color = this.backgroundColor;
			if (!Ext.isEmpty(value)) {
				color = '#'+value;
			}
			this.button.el.down('.x-btn-inner').setStyle('background', color);
		}
	},
 
	setValue: function(value) {
		this.value = value;
		this.setRawValue(value);
		this.fireEvent('change', this, this.value);
	},
	
    getRawValue: function() {
		return this.value || '';
    },

	getValue: function() {
		return this.getRawValue();
	},
	
    setReadOnly: function(readOnly) {
        var me = this;
        readOnly = !!readOnly;
        me.readOnly = readOnly;
		if (this.fieldReady) {
		}
    }
	
});

Ext.define('Scalr.ui.ChildStore', {
	extend: 'Ext.data.Store',
	parentStore: null,
	parentUpdateInProgress: false,
	selfUpdateInProgress: false,
	constructor: function(){
		var me = this;
		if (arguments[0].parentStore) {
			arguments[0].model = arguments[0].parentStore.getProxy().getModel().modelName;
		}
		me.callParent(arguments);
		if (me.parentStore) {
			this.loadRecords(this.parentStore.getRange());
			
			this.parentStore.on({
				refresh: function(){
					me.loadRecords(me.parentStore.getRange());
				},
				remove: function(store, record){
					if (!me.parentUpdateInProgress) {
						me.remove(record);
					}
				},
				add: function(store, records, index){
					if (!me.parentUpdateInProgress) {
						me.selfUpdateInProgress = true;
						me.insert(index, records);
						me.selfUpdateInProgress = false;
					}
				}
			});
			
			this.on({
				remove: function(store, record){
					me.parentUpdateInProgress = true;
					me.parentStore.remove(record);
					me.parentUpdateInProgress = false;
				},
				add: function(store, records, index){
					if (!me.selfUpdateInProgress) {
						me.parentUpdateInProgress = true;
						me.parentStore.insert(index, records);
						me.parentUpdateInProgress = false;
					}
				}
			});
		}
	}
});	

Ext.define('Scalr.ui.FormPicker', {
	extend:'Ext.form.field.Picker',
	alias: 'widget.formpicker',
	
	onBoxReady: function() {
		this.callParent(arguments);
		this.on({
			expand: function() {
				//this.parseSearchString();
			}
		});
	},
	
	createPicker: function() {
		var me = this,
			formDefaults = {
				style: 'background:#F0F1F4;border-radius:4px;box-shadow: 0 1px 3px #7B8BA1;margin-top:1px',
				fieldDefaults: {
					anchor: '100%'
				},
				focusOnToFront: false,
				padding: 12,
				pickerField: me,
				floating: true,
				hidden: true,
				ownerCt: this.ownerCt
			};
			
		if (!this.form.dockedItems) {
			this.form.dockedItems = {
				xtype: 'container',
				layout: {
					type: 'hbox',
					pack: 'center'
				},
				dock: 'bottom',
				items: [{
					xtype: 'button',
					text: 'Search',
					handler: function() {me.focus();
						me.collapse();
						//me.fireEvent('search');
					}
				}]
			}
		}
		if (this.form.items) {
			this.form.items.unshift({
				xtype: 'textfield',
				name: 'keywords',
				fieldLabel: 'Has words',
				labelAlign: 'top'
			});
		}
		var form = Ext.create('Ext.form.Panel', Ext.apply(formDefaults, this.form));
		form.getForm().getFields().each(function(){
			if (this.xtype == 'combo') {
				this.on('expand', function(){
					this.picker.el.on('mousedown', function(e){
						me.keepVisible = true;
					});
				}, this, {single: true})
			}
		})
		return form;
	},
	
	collapseIf: function(e) {
		var me = this;
		if (!me.keepVisible && !me.isDestroyed && !e.within(me.bodyEl, false, true) && !e.within(me.picker.el, false, true) && !me.isEventWithinPickerLoadMask(e)) {
			me.collapse();
		}
		me.keepVisible = false;
	}
	
});

Ext.define('Scalr.ui.data.View.DynEmptyText', {
    extend: 'Ext.AbstractPlugin',
    alias: 'plugin.dynemptytext',
	
    disabled: false,
	client:null,
	
	onAddItemClick: Ext.emptyFn,
	itemsTotal: null,
	emptyText: 'No items were found to match your search.<p>Try modifying your search criteria or <a class="add-link" href="#">adding a new item</a></p>',
	emptyTextNoItems: null,
    showArrow: true,
    forceRefresh: false,
	
	init: function(client) {
		var me = this;
		me.client = client;
		client.on({
			containerclick: {
				fn: me.onContainerClick,
				scope: me
			},
			boxready: function() {
				client.store.on({
					refresh: {
						fn: me.updateEmptyText,
						scope: me
					},
					add: {
						fn: me.updateEmptyText,
						scope: me
					},
					remove: {
						fn: me.updateEmptyText,
						scope: me
					}
				});
				me.updateEmptyText();
			},
			beforedestroy: function() {
				this.un('containerclick', me.onContainerClick, me);
				this.store.un('refresh', me.updateEmptyText, me);
				this.store.un('add', me.updateEmptyText, me);
				this.store.un('remove', me.updateEmptyText, me);
			}
		});
	},
	
	onContainerClick: function(comp, e){
		var el = comp.el.query('a.add-link');
		if (el.length) {
            for (var i=0, len=el.length; i<len; i++) {
                if (e.within(el[i])) {
                    this.onAddItemClick(el[i]);
                    break;
                }
            }
			e.preventDefault();
		}
	},
	
	updateEmptyText: function() {
		var client = this.client,
			itemsTotal = client.store.snapshot ?  client.store.snapshot.length : client.store.data.length;

		if (this.forceRefresh || itemsTotal !== this.itemsTotal) {
			var text = this.emptyText;
			if (itemsTotal < 1) {
                if (this.showArrow) {
                    text = '<div class="x-grid-empty-inner x-grid-empty-arrow"><div class="x-grid-empty-arrow2"></div><div class="x-grid-empty-text">' + (this.emptyTextNoItems || this.emptyText) + '</div></div>';
                } else {
                    text = '<div class="x-grid-empty-inner"><div class="x-grid-empty-text">' + (this.emptyTextNoItems || this.emptyText) + '</div></div>';
                }
			} else {
				text = '<div class="x-grid-empty-inner">' + text + '</div>';
			}
			client.emptyText = '<div class="' + Ext.baseCSSPrefix + 'grid-empty">' + text + '</div>';
			var emptyDiv = client.el.query('.' + Ext.baseCSSPrefix + 'grid-empty');
			if (emptyDiv.length) {
				Ext.fly(emptyDiv[0]).setHTML(text);
                client.refreshSize();
			}
			this.itemsTotal = itemsTotal;
            this.forceRefresh = false;
		}
	},
    
    setEmptyText: function(text, alt) {
        this['emptyText' + (alt ? 'NoItems' : '')] = text;
        this.forceRefresh = true;
    }
});

Ext.define('Ext.ux.form.ToolFieldSet', {
    extend: 'Ext.form.FieldSet',
    alias: 'widget.toolfieldset',
    tools: [],

    createLegendCt: function () {
		var me = this,
			legend = me.callParent(arguments);

		if (Ext.isArray(me.tools)) {
            for(var i = 0; i < me.tools.length; i++) {
                legend.items.push(me.createToolCmp(me.tools[i]));
            }
        }
		
		return legend;
    },

    createToolCmp: function(toolCfg) {
        var me = this;
        Ext.apply(toolCfg, {
            xtype:  'tool',
			cls: 'x-tool-extra',
            width:  15,
            height: 15,
            id:     me.id + '-tool-' + toolCfg.type,
            scope:  me
        });
        return Ext.widget(toolCfg);
    }

});

Ext.define('Scalr.CachedRequest', {
    
    defaultTtl: 3600,//seconds
    
    constructor: function() {
        this.cache = {};
        this.queue = {};
    },
    
    getCacheId: function(config) {
        var cacheId = [],
            paramNames;
        if (config) {
            cacheId.push(config.url);
            if (Ext.isObject(config.params)) {
                paramNames = Ext.Array.sort(Ext.Object.getKeys(config.params));
                Ext.Array.each(paramNames, function(value){
                    cacheId.push(config.params[value]);
                });
            }
        }
        return cacheId.join('.');
    },
    
    clearCache: function() {
        delete this.queue;
        delete this.cache;
        this.cache = {};
        this.queue = {};
    },
    
    removeCache: function(params) {
        var cacheId = this.getCacheId(params);
        delete this.cache[cacheId];
    },
    
    load: function(params, cb, scope, ttl) {
        var me = this,
            cacheId = me.getCacheId(params);
        ttl = ttl === undefined ? me.defaultTtl : ttl;
        
        if (me.queue[cacheId] !== undefined) {
            me.queue[cacheId].callbacks.push({fn: cb, scope: scope || me});
        } else if (me.cache[cacheId] !== undefined && !me.isExpired(me.cache[cacheId], ttl)) {
            if (cb !== undefined) cb.call(scope || me, me.cache[cacheId].data, 'exists', cacheId);
        } else {
            delete me.cache[cacheId];
            me.queue[cacheId] = {
                callbacks: [{fn: cb, scope: scope || me}]
            };
            Scalr.Request({
                processBox: {
                    type: 'action',
                    msg: 'Loading ...'
                },
                url:  params.url,
                params: params.params || {},
                scope: me,
                success: function (response) {
                    if (me.queue[cacheId] !== undefined) {
                        var callbacks = me.queue[cacheId].callbacks;
                        me.cache[cacheId] = {
                            data: response.data !== undefined ? response.data : response,
                            time: me.getTime()
                        };
                        Ext.Array.each(callbacks, function(callback){
                            if (callback.fn !== undefined && !callback.scope.isDestroyed) callback.fn.call(callback.scope, me.cache[cacheId].data, 'success', cacheId);
                        });
                        delete me.queue[cacheId];
                    }
                },
                failure: function (response) {
                    if (me.queue[cacheId] !== undefined) {
                        var callbacks = me.queue[cacheId].callbacks;
                        Ext.Array.each(callbacks, function(callback){
                            if (callback.fn !== undefined && !callback.scope.isDestroyed) callback.fn.call(callback.scope, null, false, cacheId);
                        });
                        delete me.queue[cacheId];
                    }
                }
            });
        }
        return cacheId;
    },
    
    get: function(params) {
        var cacheId = Ext.isString(params) ? params : this.getCacheId(params);
        return this.cache[cacheId] || undefined;
    },
    
    getTime: function() {
        return Math.floor(new Date().getTime() / 1000);
    },
    
    isExpired: function(data, ttl) {
        return data.expired ? true : (ttl !== 0 ? this.getTime() - data.time > ttl : false);
    },
    
    setExpired: function(params) {
        var cacheId = Ext.isString(params) ? params : this.getCacheId(params);
        if (this.cache[cacheId] !== undefined) {
            this.cache[cacheId].expired = true;
        }
    },
    
    isLoaded: function(params) {
        var cacheId = Ext.isString(params) ? params : this.getCacheId(params);
        return this.cache[cacheId] !== undefined;
    }
    
    
});
