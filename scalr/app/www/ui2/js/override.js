// show file name in File Field
Ext.override(Ext.form.field.File, {
	onRender: function() {
		var me = this;

		me.callParent(arguments);
		me.anchor = '-30';
		me.browseButtonWrap.addCls('x-field-browsebutton');
	},

	buttonText: '',
	setValue: function(value) {
		Ext.form.field.File.superclass.setValue.call(this, value);
	}
});

// submit form on enter on any fields in form
Ext.override(Ext.form.field.Base, {
	allowChangeable: true,
	allowChangeableMsg: 'You can set this value only once',
	validateOnBlur: false,

	initComponent: function() {
		this.callParent(arguments);

		this.on('specialkey', function(field, e) {
			if (e.getKey() == e.ENTER) {
				var form = field.up('form');
				if (form) {
					var button = form.down('#buttonSubmit');
					if (button) {
						button.handler();
					}
				}
			}
		});

		if (! this.allowChangeable) {
			this.cls += ' x-form-notchangable-field';
		}
	},

	afterRender: function() {
		this.callParent(arguments);

		if (! this.allowChangeable) {
			this.changeableTip = Ext.create('Ext.tip.ToolTip', {
				target: this.inputEl,
				html: this.allowChangeableMsg
			});
		}
	},

	markInvalid: function() {
		this.callParent(arguments);
		if (! this.allowChangeable)
			this.changeableTip.setDisabled(true);
	},

	clearInvalid: function() {
		this.callParent(arguments);
		if (! this.allowChangeable)
			this.changeableTip.setDisabled(false);
	}
});

Ext.override(Ext.form.field.Checkbox, {
	setReadOnly: function(readOnly) {
		var me = this,
			inputEl = me.inputEl;
		if (inputEl) {
			// Set the button to disabled when readonly
			inputEl.dom.disabled = readOnly || me.disabled;
		}
		me[readOnly ? 'addCls' : 'removeCls'](me.readOnlyCls);
		me.readOnly = readOnly;
	}
});

Ext.override(Ext.form.field.Trigger, {
	updateEditState: function() {
		var me = this;

		me.callOverridden();
		me[me.readOnly ? 'addCls' : 'removeCls'](me.readOnlyCls);
	}
});

// TextArea should always show emptyText via value, not placeholder (scrolling and multi-line will work)
Ext.override(Ext.form.field.TextArea, {
	getSubTplData: function() {
		var me = this,
			fieldStyle = me.getFieldStyle(),
			ret = me.callParent();

		if (me.grow) {
			if (me.preventScrollbars) {
				ret.fieldStyle = (fieldStyle||'') + ';overflow:hidden;height:' + me.growMin + 'px';
			}
		}

		Ext.applyIf(ret, {
			cols: me.cols,
			rows: me.rows
		});

		/** Changed, get from Ext.form.field.Text:getSubTplData */
		var value = me.getRawValue();
		var isEmpty = me.emptyText && value.length < 1, placeholder = '';
		if (isEmpty) {
			value = me.emptyText;
			me.valueContainsPlaceholder = true;
		}

		Ext.apply(ret, {
			placeholder : placeholder,
			value       : value,
			fieldCls    : me.fieldCls + ((isEmpty && (placeholder || value)) ? ' ' + me.emptyCls : '') + (me.allowBlank ? '' :  ' ' + me.requiredCls)
		});
		/** End */

		return ret;
	},

	applyEmptyText: function() {
		var me = this,
			emptyText = me.emptyText,
			isEmpty;

		if (me.rendered && emptyText) {
			isEmpty = me.getRawValue().length < 1 && !me.hasFocus;

			/** Changed */
			if (isEmpty) {
				me.setRawValue(emptyText);
				me.valueContainsPlaceholder = true;
			}
			/** End */

			//all browsers need this because of a styling issue with chrome + placeholders.
			//the text isnt vertically aligned when empty (and using the placeholder)
			if (isEmpty) {
				me.inputEl.addCls(me.emptyCls);
			}

			me.autoSize();
		}
	},

	onFocus: function() {
		this.callParent(arguments);

		// move to beforeFocus
		var me = this,
			inputEl = me.inputEl,
			emptyText = me.emptyText,
			isEmpty;

		//debugger;
		/** Changed */
		if ((emptyText && (!Ext.supports.Placeholder || me.xtype == 'textarea')) && (inputEl.dom.value === me.emptyText && me.valueContainsPlaceholder)) {
			me.setRawValue('');
			isEmpty = true;
			inputEl.removeCls(me.emptyCls);
			me.valueContainsPlaceholder = false;
		} else if (Ext.supports.Placeholder && me.xtype != 'textarea') {
			me.inputEl.removeCls(me.emptyCls);
		}
		if (me.selectOnFocus || isEmpty) {
			inputEl.dom.select();
		}
		/** End */
	}
});

Ext.override(Ext.slider.Single, {
    showValue: false,
	onRender: function() {
		var me = this;
		me.callParent(arguments);
        
        Ext.DomHelper.append(this.thumbs[0].el, '<div class="x-slider-thumb-inner"></div>', true);
        if (me.showValue) {
            this.sliderValue = Ext.DomHelper.append(this.thumbs[0].el, '<div class="x-slider-value">'+this.getValue()+'</div>', true);
            this.on('change', function(comp, value){
                if (this.sliderValue !== undefined) {
                    this.sliderValue.setHTML(value);
                }
            });
        }
	}
});


Ext.override(Ext.form.Panel, {
	initComponent: function() {
		this.callParent(arguments);
		this.relayEvents(this.form, ['beforeloadrecord', 'loadrecord', 'updaterecord' ]);
	},
	loadRecord: function(record) {
		var arg = [];

		for (var i = 0; i < arguments.length; i++)
			arg.push(arguments[i]);
		this.suspendLayouts();
		
		if (this.fireEvent.apply(this, ['beforeloadrecord'].concat(arg)) === false) {
			this.resumeLayouts(true);
			return false;
		}
		var ret = this.getForm().loadRecord(record);
		
		this.fireEvent.apply(this, ['loadrecord'].concat(arg));
		this.resumeLayouts(true);
		
		return ret;
	}
});

Ext.override(Ext.form.Basic, {
	constructor: function() {
		this.callParent(arguments);
		this.addEvents('beforeloadrecord', 'loadrecord', 'updaterecord');
	},
	initialize: function () {
		this.callParent(arguments);

		//scroll to error fields
		this.on('actionfailed', function (basicForm) {
			basicForm.getFields().each(function (field) {
				if (field.isFieldLabelable && field.getActiveError()) {
					field.el.scrollIntoView(basicForm.owner.body);
					return false;
				}
			});
		});
	},

	updateRecord: function(record) {
		record = record || this._record;
		var ret = this.callParent(arguments);
		this.fireEvent('updaterecord', record);
		return ret;
	}
});

Ext.override(Ext.form.action.Action, {
	submitEmptyText: false
});

// save & restore all sort params
Ext.override(Ext.panel.Table, {
	getState: function() {
		var state = this.callParent(arguments), me = this, sorters = me.store.sorters;

		if (sorters) {
			var s = [];
			sorters.each(function (item) {
				s.push({
					direction: item.direction,
					property: item.property,
					root: item.root
				});
			});

			state = me.addPropertyToState(state, 'sort', s);
		}
		state = this.addPropertyToState(state, 'autoRefresh', this.autoRefresh);

		return state;
	},
	applyState: function(state) {
		var sorter = state.sort, me = this, store = me.store;
		if (sorter) {
			store.sort(sorter, null, false);
			delete state.sort;
		}

		this.callParent(arguments);
	}
});

Ext.override(Ext.view.Table, {
	enableTextSelection: true
});

Ext.override(Ext.view.AbstractView, {
	loadingText: 'Loading data...',
	emptyTextPrepare: true,
	//disableSelection: true,
	// TODO: apply and check errors (role/edit for example, selected plugin for grid

	initComponent: function() {
		this.callParent(arguments);
		if (this.emptyTextPrepare)
			this.emptyText = '<div class="x-grid-empty">' + this.emptyText + '</div>';
	}
});

Ext.override(Ext.view.BoundList, {
	afterRender: function() {
		this.callParent(arguments);

		if (this.minWidth)
			this.el.applyStyles('min-width: ' + this.minWidth + 'px');
	}
})

Ext.override(Ext.form.field.ComboBox, {
	matchFieldWidth: false,
	autoSetValue: false,
	queryReset: false, // set true to refresh store forcibly

	initComponent: function() {
		var me = this;
		me.callParent(arguments);

		if (!me.value && me.autoSetValue && me.store.getCount() > 0) {
			me.setValue(me.store.first().get(me.valueField));
		}
	},

	alignPicker: function() {
		var me = this,
			picker = me.getPicker();

		if (me.isExpanded) {
			if (! me.matchFieldWidth) {
				// set minWidth
				picker.el.applyStyles('min-width: ' + me.bodyEl.getWidth() + 'px');
			}
		}

		this.callParent(arguments);
	},

	onBeforeLoad: function() {
		if (this.queryMode == 'remote')
			this.addCls('x-field-trigger-loading');

		this.removeCls('x-field-trigger-error');
		if (this.rendered)
			this.triggerEl.elements[0].dom.title = '';
	},
	onLoad: function(store, records, successful) {
		if (this.queryMode == 'remote')
			this.removeCls('x-field-trigger-loading');

		if (!successful && store.proxy.reader.rawData && store.proxy.reader.rawData.errorMessage) {
			this.addCls('x-field-trigger-error');
			this.triggerEl.elements[0].dom.title = 'Error loading data: ' + store.proxy.reader.rawData.errorMessage + "\nClick to try once more.";
			this.queryReset = true;
			this.collapse();
		}
	},
	onException: function() {
		if (this.queryMode == 'remote')
			this.removeCls('x-field-trigger-loading');
	},
	// based onTriggerClick
	prefetch: function() {
		var me = this;
		if (!me.readOnly && !me.disabled) {
			if (me.triggerAction === 'all') {
				me.doQuery(me.allQuery, true);
			} else {
				me.doQuery(me.getRawValue(), false, true);
			}
			me.collapse();
		}
	},

	doQuery: function(queryString, forceAll, rawQuery) {
		/* Changed */
		if (this.queryReset && this.queryCaching)
			this.queryCaching = false;
		/* End */

        queryString = queryString || '';

        // store in object and pass by reference in 'beforequery'
        // so that client code can modify values.
        var me = this,
            qe = {
                query: queryString,
                forceAll: forceAll,
                combo: me,
                cancel: false
            },
            store = me.store,
            isLocalMode = me.queryMode === 'local',
            needsRefresh;

        if (me.fireEvent('beforequery', qe) === false || qe.cancel) {
            return false;
        }

        // get back out possibly modified values
        queryString = qe.query;
        forceAll = qe.forceAll;

        // query permitted to run
        if (forceAll || (queryString.length >= me.minChars)) {
            // expand before starting query so LoadMask can position itself correctly
            me.expand();

            // make sure they aren't querying the same thing
            if (!me.queryCaching || me.lastQuery !== queryString) {
                me.lastQuery = queryString;

                if (isLocalMode) {
                    // forceAll means no filtering - show whole dataset.
                    store.suspendEvents();
                    needsRefresh = me.clearFilter();
                    if (queryString || !forceAll) {
						/* Changed */
						if (this.filterFn) {
							me.activeFilter = new Ext.util.Filter({
								root: 'data',
								filterFn: function(item) {
									return me.filterFn(queryString, item)
								}
							});
						} else {
							me.activeFilter = new Ext.util.Filter({
								root: 'data',
								property: me.displayField,
								value: queryString,
								anyMatch: this.anyMatch || false
							});
						}
						/* End */
						store.filter(me.activeFilter);
						needsRefresh = true;
                    } else {
                        delete me.activeFilter;
                    }
                    store.resumeEvents();
                    if (me.rendered && needsRefresh) {
                        me.getPicker().refresh();
                    }
                } else {
                    // Set flag for onLoad handling to know how the Store was loaded
                    me.rawQuery = rawQuery;

                    // In queryMode: 'remote', we assume Store filters are added by the developer as remote filters,
                    // and these are automatically passed as params with every load call, so we do *not* call clearFilter.
                    if (me.pageSize) {
                        // if we're paging, we've changed the query so start at page 1.
                        me.loadPage(1);
                    } else {
                        store.load({
                            params: me.getParams(queryString)
                        });
                    }
                }
            }

            // Clear current selection if it does not match the current value in the field
            if (me.getRawValue() !== me.getDisplayValue()) {
                me.ignoreSelection++;
                me.picker.getSelectionModel().deselectAll();
                me.ignoreSelection--;
            }

            if (isLocalMode) {
                me.doAutoSelect();
            }
            if (me.typeAhead) {
                me.doTypeAhead();
            }
        }		
		
		/* Changed */
		if (this.queryReset) {
			this.queryReset = false;
			this.queryCaching = true
		}
		/* End */
		return true;
	},

	defaultListConfig: {
		shadow: false // disable shadow in combobox
	},
	shadow: false
});

Ext.override(Ext.form.field.Picker, {
	pickerOffset: [0, 2]
});

Ext.override(Ext.picker.Date, {
	shadow: false
});

Ext.override(Ext.picker.Month, {
	shadow: false,
	initComponent: function() {
		this.callParent(arguments);

		// buttons have extra padding, low it
		if (this.showButtons) {
			this.okBtn.padding = 3;
			this.cancelBtn.padding = 3;
		}
	}
});

Ext.override(Ext.container.Container, {
	setFieldValues: function(values) {
		for (var i in values) {
			var f = this.down('[name="' + i + '"]');
			if (f)
				f.setValue(values[i]);
		}
	},

	getFieldValues: function() {
		var fields = this.query('[isFormField]'), values = {};
		for (var i = 0, len = fields.length; i < len; i++) {
			values[fields[i].getName()] = fields[i].getValue();
		}

		return values;
	}
});

Ext.override(Ext.tip.Tip, {
	shadow: false
});

Ext.override(Ext.panel.Tool, {
	width: 21,
	height: 16
});

// override to save scope, WTF? field doesn't forward =((
Ext.override(Ext.grid.feature.AbstractSummary, {
	getSummary: function(store, type, field, group){
		if (type) {
			if (Ext.isFunction(type)) {
				return store.aggregate(type, null, group, [field]);
			}

			switch (type) {
				case 'count':
					return store.count(group);
				case 'min':
					return store.min(field, group);
				case 'max':
					return store.max(field, group);
				case 'sum':
					return store.sum(field, group);
				case 'average':
					return store.average(field, group);
				default:
					return group ? {} : '';

			}
		}
	}
});

Ext.override(Ext.grid.column.Column, {
	// hide control menu
	menuDisabled: true,

	// mark sortable columns
	beforeRender: function() {
		this.callParent();
		if (this.sortable)
			this.addCls('x-column-header-sortable');
	}
});

Ext.override(Ext.grid.Panel, {
	enableColumnMove: false
});

// fieldset's title is not legend (simple div)
Ext.override(Ext.form.FieldSet, {
	createLegendCt: function() {
		var me = this,
			items = [],
			legend = {
				xtype: 'container',
				baseCls: me.baseCls + '-header',
				id: me.id + '-legend',
				//autoEl: 'legend',
				items: items,
				ownerCt: me,
				ownerLayout: me.componentLayout
			};

		// Checkbox
		if (me.checkboxToggle) {
			items.push(me.createCheckboxCmp());
		} else if (me.collapsible) {
			// Toggle button
			items.push(me.createToggleCmp());
		}

        if (me.collapsible && me.toggleOnTitleClick && !me.checkboxToggle) {
            legend.listeners = {
                click : {
                    element: 'el',
                    scope : me,
                    fn : function(e, el){
                        if(Ext.fly(el).hasCls(me.baseCls + '-header')) {
                            me.toggle(arguments);
                        }
                    }
                }
            };
        }
        
		// Title
		items.push(me.createTitleCmp());

		return legend;
	},
    
	createToggleCmp: function() {
		var me = this;
		me.addCls('x-fieldset-with-toggle')
		me.toggleCmp = Ext.widget({
			xtype: 'tool',
			type: me.collapsed ? 'collapse' : 'expand',
			handler: me.toggle,
			id: me.id + '-legendToggle',
			scope: me
		});
		return me.toggleCmp;
	},
	setExpanded: function() {
		this.callParent(arguments);

		if (this.toggleCmp) {
			if (this.collapsed)
				this.toggleCmp.setType('collapse');
			else
				this.toggleCmp.setType('expand');
		}
	}
    
});

Ext.override(Ext.menu.Menu, {
	childMenuOffset: [2, 0],
	menuOffset: [0, 1],
	shadow: false,
	showBy: function(cmp, pos, off) {
		var me = this;

		if (cmp.isMenuItem)
			off = this.childMenuOffset; // menu is showed from menu item
		else if (me.isMenu)
			off = this.menuOffset;

		if (me.floating && cmp) {
			me.show();

			// Align to Component or Element using setPagePosition because normal show
			// methods are container-relative, and we must align to the requested element
			// or Component:
			me.setPagePosition(me.el.getAlignToXY(cmp.el || cmp, pos || me.defaultAlign, off));
			me.setVerticalPosition();
		}
		return me;
	},
	afterLayout: function() {
		this.callParent(arguments);

		var first = null, last = null;

		this.items.each(function (item) {
			item.removeCls('x-menu-item-first');
			item.removeCls('x-menu-item-last');

			if (!first && !item.isHidden())
				first = item;

			if (!item.isHidden())
				last = item;
		});

		if (first)
			first.addCls('x-menu-item-first');

		if (last)
			last.addCls('x-menu-item-last');
	}
});

Ext.override(Ext.menu.Item, {
	renderTpl: [
		'<tpl if="plain">',
			'{text}',
		'<tpl else>',
			'<a id="{id}-itemEl" class="' + Ext.baseCSSPrefix + 'menu-item-link" href="{href}" <tpl if="hrefTarget">target="{hrefTarget}"</tpl> hidefocus="true" unselectable="on">',
				'<img id="{id}-iconEl" src="{icon}" class="' + Ext.baseCSSPrefix + 'menu-item-icon {iconCls}" />',
				'<span id="{id}-textEl" class="' + Ext.baseCSSPrefix + 'menu-item-text" <tpl if="arrowCls">style="margin-right: 17px;"</tpl> >{text}</span>',
				'<img id="{id}-arrowEl" src="{blank}" class="{arrowCls}" />',
				'<div style="clear: both"></div>',
			'</a>',
		'</tpl>'
	]
});

// fix from 4.1.2
// TODO: remove after update
Ext.view.Table.override({
	onUpdate: function(store, record) {
		var index = store.indexOf(record);
		this.callParent(arguments);

		if (this.getSelectionModel().isSelected(record))
			Ext.fly(this.getNodeByRecord(record)).addCls('x-grid-row-selected');

		this.doStripeRows(index, index);
	}
});

// remove strip div
Ext.override(Ext.tab.Bar, {
	afterRender: function() {
		this.callParent(arguments);
		this.strip.applyStyles('height: 0px; display: none;')
	}
});

Ext.override(Ext.grid.plugin.CellEditing, {
	getEditor: function() {
		var editor = this.callParent(arguments);

		if (editor.field.getXType() == 'combobox') {
			editor.field.on('focus', function() {
				this.expand();
			});

			editor.field.on('collapse', function() {
				editor.completeEdit();
			});
		}

		return editor;
	}
});

Ext.override(Ext.data.Model, {
	hasStore: function() {
		return this.stores.length ? true : false;
	}
});

Ext.Error.handle = function(err) {
	var err = new Ext.Error(err);

	Scalr.utils.PostError({
		message: err.toString(),
		url: document.location.href
	});

	return true;
};

Ext.override(Ext.grid.plugin.HeaderResizer, {
	resizeColumnsToFitPanelWidth: function(currentColumn) {
		var headerCt = this.headerCt,
			grid = headerCt.ownerCt || null;

		if (!grid) return;
		
		var columnsWidth = headerCt.getFullWidth(),
			panelWidth = grid.body.getWidth();
			
		if (panelWidth > columnsWidth) {
			var columns = [];
			Ext.Array.each(this.headerCt.getVisibleGridColumns(), function(col){
				if (col.initialConfig.flex && col != currentColumn) {
					columns.push(col);
				}
			});
			if (columns.length) {
				var scrollWidth = grid.getView().el.dom.scrollHeight == grid.getView().el.getHeight() ? 0 : Ext.getScrollbarSize().width,
					deltaWidth = Math.floor((panelWidth - columnsWidth - scrollWidth)/columns.length);
				grid.suspendLayouts();
				for(var i=0, len=columns.length; i<len; i++) {
					var flex = columns[i].flex || null;
					columns[i].setWidth(columns[i].getWidth() + deltaWidth);
					if (flex) {
						columns[i].flex = flex;
						delete columns[i].width;
					}
				}
				grid.resumeLayouts(true);
			}
		}
	},
	
	afterHeaderRender: function() {
		this.callParent(arguments);
	  
		var me = this;
		this.headerCt.ownerCt.on('resize', me.resizeColumnsToFitPanelWidth, me);
		this.headerCt.ownerCt.store.on('refresh', me.resizeColumnsToFitPanelWidth, me);
		
		this.headerCt.ownerCt.on('beforedestroy', function(){
			me.headerCt.ownerCt.un('resize', me.resizeColumnsToFitPanelWidth, me);
			me.headerCt.ownerCt.store.un('refresh', me.resizeColumnsToFitPanelWidth, me);
		}, me);
	},
  
	onEnd: function(e){
		this.callParent(arguments);
		this.resizeColumnsToFitPanelWidth(this.dragHd);
	}
});

Ext.apply(Ext.Loader, {
	loadScripts: function(sc, handler) {
		var scope = {
			queue: Scalr.utils.CloneObject(sc),
			handler: handler
		}, me = this;

		for (var i = 0; i < sc.length; i++) {
			(function(script){
				me.injectScriptElement(script, function() {
					var ind = this.queue.indexOf(script);
					this.queue.splice(ind, 1);
					if (! this.queue.length)
						this.handler();
				}, Ext.emptyFn, scope);
			})(sc[i]);
		}
	}
});

Ext.override(Ext.grid.RowEditor, {
	reposition: function(animateConfig) {
		var me = this,
			context = me.context,
			row = context && Ext.get(context.row),
			btns = me.getFloatingButtons(),
			btnEl = btns.el,
			grid = me.editingPlugin.grid,
			viewEl = grid.view.el,

			// always get data from ColumnModel as its what drives
			// the GridView's sizing
			mainBodyWidth = grid.headerCt.getFullWidth(),
			scrollerWidth = grid.getWidth(),

			// use the minimum as the columns may not fill up the entire grid
			// width
			width = Math.min(mainBodyWidth, scrollerWidth),
			scrollLeft = grid.view.el.dom.scrollLeft,
			btnWidth = btns.getWidth(),
			left = (width - btnWidth) / 2 + scrollLeft,
			y, rowH, newHeight,

			invalidateScroller = function() {
				btnEl.scrollIntoView(viewEl, false);
				if (animateConfig && animateConfig.callback) {
					animateConfig.callback.call(animateConfig.scope || me);
				}
			},
			
			animObj;

		// need to set both top/left
		if (row && Ext.isElement(row.dom)) {
			// Bring our row into view if necessary, so a row editor that's already
			// visible and animated to the row will appear smooth
			row.scrollIntoView(viewEl, false);

			// Get the y position of the row relative to its top-most static parent.
			// offsetTop will be relative to the table, and is incorrect
			// when mixed with certain grid features (e.g., grouping).
			y = row.getXY()[1] - 5;
			rowH = row.getHeight();
			newHeight = rowH + (me.editingPlugin.grid.rowLines ? 9 : 10);

			// Set editor height to match the row height
			/* Changed */
			newHeight -= 5;
			y += row.parent().last('.x-grid-row').dom === row.dom && row.parent().first('.x-grid-row').dom !== row.dom ? 0 : 3;
			/* End */
			if (me.getHeight() != newHeight) {
				me.setHeight(newHeight);
				me.el.setLeft(0);
			}
			/* Changed */
			if (false) {
			/* End */
				animObj = {
					to: {
						y: y
					},
					duration: animateConfig.duration || 125,
					listeners: {
						afteranimate: function() {
							invalidateScroller();
							y = row.getXY()[1] - 5;
						}
					}
				};
				me.el.animate(animObj);
			} else {
				me.el.setY(y);
				invalidateScroller();
			}
		}
		if (me.getWidth() != mainBodyWidth) {
			me.setWidth(mainBodyWidth);
		}
		btnEl.setLeft(left);
	}
	
});

/*required for new left menu(rolebuilder), allows absolute positioning for scrollers*/
Ext.override(Ext.layout.container.boxOverflow.Scroller, {
    handleOverflow: function(ownerContext) {
        var me = this,
            layout = me.layout,
            names = layout.getNames(),
            methodName = 'get' + names.widthCap;

        me.captureChildElements();
        me.showScrollers();

        return {
			/* Changed */
            reservedSpace: (me.beforeCt.getStyle('position') == 'absolute' ? 0 : me.beforeCt[methodName]()) + (me.afterCt.getStyle('position') == 'absolute' ? 0 : me.afterCt[methodName]())
			/* End */
        };
    }
});

