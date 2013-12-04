Ext.define('Scalr.ui.FormFilterField', {
	extend:'Ext.form.field.Picker',
	alias: 'widget.filterfield',

	hideTrigger: true,
	separatedParams: [],
	hideFilterIcon: false,
	hideTriggerButton: false,
	hideSearchButton: false,
	cls: 'x-filterfield',
    filterId: 'filterfield',
    
	initComponent: function() {
		var me = this;
		this.callParent(arguments);

		if (! this.form) {
			this.hideTriggerButton = true;
		} else {
			this.on({
				expand: function() {
					var picker = this.getPicker(), values = this.getParseValue();
					picker.getForm().reset();
					picker.getForm().setValues(values);
					this.triggerButton.addCls('x-filterfield-trigger-pressed');
				},
				collapse: function() {
					var picker = this.getPicker(), values = this.getParseValue();
					Ext.Object.merge(values, picker.getForm().getValues());
					this.setParseValue(values);
					this.triggerButton.removeCls('x-filterfield-trigger-pressed');
				}
			});
		}

		if (this.form && this.form.items) {
			Ext.each(this.form.items, function(item) {
				if (item.name)
					me.separatedParams.push(item.name);
			});
		}

		if (this.store && this.store.remoteSort) {
			this.emptyText = 'Search';

			if (this.store.proxy.extraParams['query'] != '')
				this.value = this.store.proxy.extraParams['query'];
		} else {
			this.emptyText = this.emptyText || 'Filter';
			this.hideSearchButton = true;
			if (! this.hideFilterIcon)
				this.fieldCls = this.fieldCls + ' x-form-field-livesearch';
		}

		if (Ext.isFunction(this.handler)) {
			this.on('change', this.handler, this, { buffer: 300 });
		}
	},

	clearFilter: function() {
		this.collapse();
		this.reset();
		if (! this.hideSearchButton)
			this.storeHandler();
		this.focus();
	},

	applyFilter: function(field, value) {
		var me = this;
		value = Ext.String.trim(value);

		if (this.hideSearchButton)
			me.clearButton[value != '' ? 'show' : 'hide' ]();

		if (this.store !== undefined && (me.filterFn || me.filterFields)) {
            var filters = [];
            this.store.filters.each(function(filter){
                if (filter.id !== me.filterId) {
                    filters.push(filter);
                }
            });
            this.store.clearFilter();
			var filterFn = function(record) {
				var result = false,
					r = new RegExp(Ext.String.escapeRegex(value), 'i');
				for (var i = 0, length = me.filterFields.length; i < length; i++) {
					var fieldValue = Ext.isFunction(me.filterFields[i]) ? me.filterFields[i](record) : record.get(me.filterFields[i]);
					result = (fieldValue+'').match(r);
					if (result) {
						break;
					}
				}
				return result;
			}

			if (value != '') {
				filters.push({
                    id: this.filterId,
					filterFn: me.filterFn || filterFn
				});
            }
            this.fireEvent('beforefilter');
            this.store.filter(filters);
			this.fireEvent('afterfilter');
		}
	},

	onRender: function() {
		this.callParent(arguments);

		this.clearButton = this.bodyEl.down('tr').createChild({
			tag: 'td',
			width: 22,
			html: '<div class="x-filterfield-reset"></div>'
		});
		this.clearButton[ this.getValue() != '' ? 'show' : 'hide' ]();
		this.applyFilter(this, this.getValue());
		this.clearButton.on('click', this.clearFilter, this);
		this.on('change', this.applyFilter, this, { buffer: 300 });

		this.on('specialkey', function(f, e) {
			if(e.getKey() == e.ESC){
				e.stopEvent();
				this.clearFilter();
			}
		}, this);

		if (! this.hideTriggerButton) {
			this.triggerButton = this.bodyEl.down('tr').createChild({
				tag: 'td',
				width: 29,
				html: '<div class="x-filterfield-trigger"><div class="x-filterfield-trigger-inner"></div></div>'
			}).down('div');
			this.triggerButton.on('click', this.onTriggerClick, this);

			if (this.hideSearchButton) {
				this.triggerButton.addCls('x-filterfield-trigger-alone');
			}
		}

		if (! this.hideSearchButton) {
			this.searchButton = this.bodyEl.up('tr').createChild({
				tag: 'td',
				width: 44,
				html: '<div class="x-filterfield-btn"><div class="x-filterfield-btn-inner"></div></div>'
			}).down('div');
			this.searchButton.on('click', this.storeHandler, this);
			this.on('specialkey', function(f, e) {
				if(e.getKey() == e.ENTER){
					e.stopEvent();
					this.storeHandler();
				}
			}, this);
			this.triggerWrap.applyStyles('border-radius: 3px 0 0 3px');
			if (this.hideTriggerButton) {
				this.searchButton.addCls('x-filterfield-btn-alone');
			}
		}
	},

	createPicker: function() {
		var me = this,
			formDefaults = {
				style: 'background:#F0F1F4; border-radius: 3px; box-shadow: 0 1px 3px #708098; margin-top:1px',
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

		/*if (!this.form.dockedItems) {
			this.form.dockedItems = {
				xtype: 'container',
				layout: {
					type: 'hbox',
					pack: 'left'
				},
				dock: 'bottom',
				items: [{
					xtype: 'button',
					text: '<img src="/ui2/images/icons/search_icon_13x13.png">',
					handler: function() {
						me.focus();
						me.collapse();
						me.storeHandler();
					}
				}]
			}
		}*/
		/*if (this.form.items) {
		 this.form.items.unshift({
		 xtype: 'textfield',
		 name: 'keywords',
		 fieldLabel: 'Has words',
		 labelAlign: 'top'
		 });
		 }*/
		var form = Ext.create('Ext.form.Panel', Ext.apply(formDefaults, this.form));
		form.getForm().getFields().each(function(){
			if (this.xtype == 'combo') {
				this.on('expand', function(){
					this.picker.el.on('mousedown', function(e){
						me.keepVisible = true;
					});
				}, this, {single: true})
			} else if (this.xtype == 'textfield') {
				this.on('specialkey', function(f, e) {
					if(e.getKey() == e.ENTER){
						e.stopEvent();
						me.collapse();
						me.storeHandler();
					}
				});
			}
		})
		return form;
	},

	getParseValue: function() {
		var v = this.getValue(), res = {};
		if (this.separatedParams.length) {
			var params = v.trim().split(' '), paramsQuery = [], paramsSeparated = {};
			for (var i = 0; i < params.length; i++) {
				var paramsSplited = params[i].trim().split(':');
				if (paramsSplited.length == 1) {
					paramsQuery.push(params[i]);
				} else {
					if (this.separatedParams.indexOf(paramsSplited[0]) != -1)
						paramsSeparated[paramsSplited[0]] = paramsSplited[1];
					else
						paramsQuery.push(params[i]);
				}
			}

			res['query'] = paramsQuery.join(' ');
			Ext.Object.merge(res, paramsSeparated);
		} else {
			res['query'] = v;
		}

		return res;
	},

	setParseValue: function(params) {
		var s = params['query'] || '';
		delete params['query'];
		for (var i in params) {
			if (params[i])
				s += ' ' + i + ':' + params[i];
		}

		this.setValue(s);
	},

	collapseIf: function(e) {
		var me = this;
		if (!me.keepVisible && !me.isDestroyed && !e.within(me.bodyEl, false, true) && !e.within(me.picker.el, false, true) && !me.isEventWithinPickerLoadMask(e)) {
			me.collapse();
		}
		me.keepVisible = false;
	},

	storeHandler: function() {
		this.clearButton[this.getValue() != '' ? 'show' : 'hide' ]();

		for (var i = 0; i < this.separatedParams.length; i++) {
			delete this.store.proxy.extraParams[this.separatedParams[i]];
		}

		Ext.apply(this.store.proxy.extraParams, this.getParseValue());
		this.store.load();
	},
    
    bindStore: function(store) {
        this.store = store;
        this.applyFilter(this, this.getValue());
    }
});

Ext.define('Scalr.ui.FormFieldButtonGroup', {
	extend: 'Ext.form.FieldContainer',
	alias: 'widget.buttongroupfield',
	
	mixins: {
		field: 'Ext.form.field.Field'
	},
	
	baseCls: 'x-container x-form-buttongroupfield',
	allowBlank: false,
	
	initComponent: function() {
		var me = this, defaults;
		defaults = {
			xtype: 'button',
			enableToggle: true,
			//toggleGroup: me.getInputId(),
			allowDepress: me.allowBlank,
			scope: me,
			doToggle: function(){
				/* Changed */
				if (this.enableToggle && this.allowDepress !== false || !this.pressed && this.ownerCt.fireEvent('beforetoggle', this, this.value) !== false) {
					this.toggle();
				}
				/* End */
			},
			toggleHandler: function(button, state){
				button.ownerCt.setValue(state ? button.value : null);
			},
			onMouseDown: function(e) {
				var me = this;
				if (!me.disabled && e.button === 0) {
					/* Changed */
					//me.addClsWithUI(me.pressedCls);
					/* End */
					me.doc.on('mouseup', me.onMouseUp, me);
				}
			}
		};
		me.defaults = me.initialConfig.defaults ? Ext.clone(me.initialConfig.defaults) : {};
		Ext.applyIf(me.defaults, defaults);
		
		me.callParent();
		me.initField();
		if (!me.name) {
			me.name = me.getInputId();
		}
	},

	getValue: function() {
		var me = this,
			val = me.getRawValue();
		me.value = val;
		return val;
	},

	setValue: function(value) {
		var me = this;
		me.setRawValue(value);
		return me.mixins.field.setValue.call(me, value);
	},
	
	getRawValue: function() {
		var me = this, v, b;
		me.items.each(function(){
			if (this.pressed === true) {
                b = this;
			}
		});
        
		if (b) {
			v = b.value;
			me.rawValue = v;
		} else {
			v = me.rawValue;
		}
		return v;
	},
	
	setRawValue: function(value) {
		var me = this;
		me.rawValue = value;
		me.items.each(function(){
			if (me.rendered) {
				this.toggle(this.value == value, this.value != value);
			} else if (this.value == value){
				this.pressed = true;
			}
		});
		return value;
	},
	
	onAdd: function(item, pos) {
	   var me = this;
	   me.setFirstLastCls();
	   me.callParent();
	},

	onRemove: function(item) {
	   var me = this;
	   me.setFirstLastCls();
	   me.callParent();
	},

	setFirstLastCls: function() {
		this.items.each(function(item, index, len){
			item.removeCls('x-btn-default-small-combo-first x-btn-default-small-combo-last');
			if (index == 0) {
				item.addCls('x-btn-default-small-combo-first');
			}
			if (index + 1 == len) {
				item.addCls('x-btn-default-small-combo-last');
			}
		});
	},
	
	getInputId: function() {
		return this.inputId || (this.inputId = this.id + '-inputEl');
	},
	
    setReadOnly: function(readOnly) {
        var me = this;
        readOnly = !!readOnly;
        me.readOnly = readOnly;
		me.items.each(function(){
			if (me.rendered) {
				this.setDisabled(readOnly);
			}
		});
        me.fireEvent('writeablechange', me, readOnly);
    }

});

Ext.define('Scalr.ui.FormCustomButton', {
	alias: 'widget.btn',
	extend: 'Ext.Component',

	hidden: false,
	disabled: false,
	pressed: false,
	enableToggle: false,
	maskOnDisable: false,

	childEls: [ 'btnEl', 'btnTextEl', 'iconEl' ],

	baseCls: 'x-button-text',
	overCls: 'over',
	pressedCls: 'pressed',
	disabledCls: 'disabled',
	expandBaseCls: true,
	text: '',
	type: 'base',
	tooltipType: 'qtip',

    menuAlign: 'tl-bl?',
    menuActiveCls: 'menu-active',
    arrowAlign: 'right',
    arrowCls: 'arrow',
    split: false,

	renderTpl: '<div class="x-btn-inner {innerCls}" id="{id}-btnEl"><div class="x-button-text-wrap" id="{id}-btnTextEl">{text}</div></div>',

	initComponent: function() {
		var me = this;

		me.callParent(arguments);
		me.addEvents('click', 'toggle');

        if (me.menu) {
            me.split = true;
            me.menu = Ext.menu.Manager.get(me.menu);
            me.menu.ownerButton = me;
        }

		if (Ext.isString(me.toggleGroup)) {
			me.enableToggle = true;
		}

		if (! me.baseCls) {
			me.baseCls = 'x-btn-' + me.type;
		}

		if (me.expandBaseCls) {
			me.overCls = me.baseCls + '-' + me.overCls;
			me.pressedCls = me.baseCls + '-' + me.pressedCls;
			me.disabledCls = me.baseCls + '-' + me.disabledCls;
		}
        
		me.renderData['id'] = me.getId();
		me.renderData['disabled'] = me.disabled;
		me.renderData['text'] = me.text;
        me.renderData['innerCls'] = me.innerCls || '';
	},

	onRender: function () {
		var me = this;

		me.doc = Ext.getDoc();
		me.callParent(arguments);

		if (me.el) {
			me.mon(me.el, {
				click: me.onClick,
				mouseup: me.onMouseUp,
				mousedown: me.onMouseDown,
				scope: me
			});

			me.mon()
		}

		if (me.pressed)
			me.addCls(me.pressedCls);

		Ext.ButtonToggleManager.register(me);
		
        if (me.tooltip) {
            me.setTooltip(me.tooltip, true);
        }
        
        if (me.menu) {
            var btnListeners = {
                scope: me,
                mouseover: me.onMouseOver,
                mouseout: me.onMouseOut,
                mousedown: me.onMouseDown
            };
            if (me.split) {
                btnListeners.mousemove = me.onMouseMove;
            }
            me.mon(me.el, btnListeners);
            
            me.mon(me.menu, {
                scope: me,
                show: me.onMenuShow,
                hide: me.onMenuHide
            });

            me.keyMap = new Ext.util.KeyMap({
                target: me.el,
                key: Ext.EventObject.DOWN,
                handler: me.onDownKey,
                scope: me
            });
        }
        
        me.el.unselectable();
		
	},

	// @private
	onMouseDown: function(e) {
		var me = this;
		if (!me.disabled && e.button === 0) {
            if (me.disableMouseDownPressed !== true) {
                me.addCls(me.pressedCls);
            }
			me.doc.on('mouseup', me.onMouseUp, me);
		}
	},
	// @private
	onMouseUp: function(e) {
		var me = this;
		if (e.button === 0) {
			if (!me.pressed) {
				me.removeCls(me.pressedCls);
			}
			me.doc.un('mouseup', me.onMouseUp, me);
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
            me.maybeShowMenu();
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
    },
    
    setText: function(text) {
        var me = this;
        me.text = text;
        if (me.rendered) {
            me.btnTextEl.update(text || '&#160;');
            me.updateLayout();
        }
        return me;
    },
    
    //all methods below are button menu related
    onMouseOver: function(e) {
        var me = this;
        if (!me.disabled && !e.within(me.el, true, true)) {
            me.onMouseEnter(e);
        }
    },

    onMouseOut: function(e) {
        var me = this;
        if (!e.within(me.el, true, true)) {
            if (me.overMenuTrigger) {
                me.onMenuTriggerOut(e);
            }
            me.onMouseLeave(e);
        }
    },
    
    onMouseEnter: function(e) {
        var me = this;
        me.addClsWithUI(me.overCls);
        me.fireEvent('mouseover', me, e);
    },

    onMouseLeave: function(e) {
        var me = this;
        me.removeClsWithUI(me.overCls);
        me.fireEvent('mouseout', me, e);
    },

    getRefItems: function(deep){
        var menu = this.menu,
            items;
        
        if (menu) {
            items = menu.getRefItems(deep);
            items.unshift(menu);
        }
        return items || [];
    },
    
    beforeDestroy: function() {
        var me = this;
        if (me.menu && me.destroyMenu !== false) {
            Ext.destroy(me.menu);
        }
        me.callParent();
    },
    
    maybeShowMenu: function(){
        var me = this;
        if (me.menu && !me.hasVisibleMenu() && !me.ignoreNextClick) {
            me.showMenu();
        }
    },

    showMenu: function() {
        var me = this;
        if (me.rendered && me.menu) {
            if (me.tooltip && me.getTipAttr() != 'title') {
                Ext.tip.QuickTipManager.getQuickTip().cancelShow(me.btnEl);
            }
            if (me.menu.isVisible()) {
                me.menu.hide();
            }

            me.menu.showBy(me.el, me.menuAlign, ((!Ext.isStrict && Ext.isIE) || Ext.isIE6) ? [-2, -2] : undefined);
        }
        return me;
    },

    hideMenu: function() {
        if (this.hasVisibleMenu()) {
            this.menu.hide();
        }
        return this;
    },

    hasVisibleMenu: function() {
        var menu = this.menu;
        return menu && menu.rendered && menu.isVisible();
    },
    
    onMenuShow: function(e) {
        var me = this;
        me.ignoreNextClick = 0;
        me.addClsWithUI(me.menuActiveCls);
        me.fireEvent('menushow', me, me.menu);
    },

    onMenuHide: function(e) {
        var me = this;
        me.removeClsWithUI(me.menuActiveCls);
        me.ignoreNextClick = Ext.defer(me.restoreClick, 250, me);
        me.fireEvent('menuhide', me, me.menu);
    },
    
    onMouseMove: function(e) {
        var me = this,
            el = me.el,
            over = me.overMenuTrigger,
            overlap, btnSize;

        if (me.split) {
            if (me.arrowAlign === 'right') {
                overlap = e.getX() - el.getX();
                btnSize = el.getWidth();
            } else {
                overlap = e.getY() - el.getY();
                btnSize = el.getHeight();
            }

            if (overlap > (btnSize - me.getTriggerSize())) {
                if (!over) {
                    me.onMenuTriggerOver(e);
                }
            } else {
                if (over) {
                    me.onMenuTriggerOut(e);
                }
            }
        }
    },

    getTriggerSize: function() {
        var me = this,
            size = me.triggerSize,
            side, sideFirstLetter, undef;

        if (size === undef) {
            side = me.arrowAlign;
            sideFirstLetter = side.charAt(0);
            size = me.triggerSize = me.el.getFrameWidth(sideFirstLetter) + me.btnEl.getFrameWidth(sideFirstLetter) + me.frameSize[side];
        }
        return size;
    },
    
    onMenuTriggerOver: function(e) {
        var me = this;
        me.overMenuTrigger = true;
        me.fireEvent('menutriggerover', me, me.menu, e);
    },

    onMenuTriggerOut: function(e) {
        var me = this;
        delete me.overMenuTrigger;
        me.fireEvent('menutriggerout', me, me.menu, e);
    },
    
    restoreClick: function() {
        this.ignoreNextClick = 0;
    },
    
    onDownKey: function() {
        var me = this;

        if (!me.disabled) {
            if (me.menu) {
                me.showMenu();
            }
        }
    },
    
    getSplitCls: function() {
        var me = this;
        return me.split ? (me.baseCls + '-' + me.arrowCls) + ' ' + (me.baseCls + '-' + me.arrowCls + '-' + me.arrowAlign) : '';
    },
    
    setIconCls: function(cls) {
        var me = this,
            btnIconEl = me.btnIconEl,
            oldCls = me.iconCls;
            
        me.iconCls = cls;
        if (btnIconEl) {
            // Remove the previous iconCls from the button
            btnIconEl.removeCls(oldCls);
            btnIconEl.addCls(cls || '');
            //me.setComponentCls();
            if (me.didIconStateChange(oldCls, cls)) {
                me.updateLayout();
            }
        }
        return me;
    },
    
     didIconStateChange: function(old, current) {
        var currentEmpty = Ext.isEmpty(current);
        return Ext.isEmpty(old) ? !currentEmpty : currentEmpty;
    }
   
});

Ext.define('Scalr.ui.FormCustomButtonSplit', {
    alias: 'widget.splitbtn',
    extend: 'Scalr.ui.FormCustomButton',
    arrowCls      : 'split',
    split         : true,

    setArrowHandler : function(handler, scope){
        this.arrowHandler = handler;
        this.scope = scope;
    },

    onRender: function() {
        this.callParent();
        this.btnEl.addCls(this.getSplitCls());
    },
    
    onClick : function(e, t) {
        var me = this;
        
        e.preventDefault();
        if (!me.disabled) {
            if (me.overMenuTrigger) {
                me.maybeShowMenu();
                me.fireEvent("arrowclick", me, e);
                if (me.arrowHandler) {
                    me.arrowHandler.call(me.scope || me, me, e);
                }
            } else {
                me.doToggle();
                me.fireHandler(e);
            }
        }
    }
});

Ext.define('Scalr.ui.FormCustomButtonCycle', {
    alias: 'widget.cyclebtn',
    extend: 'Scalr.ui.FormCustomButtonSplit',
    
    showText: true,
    deferChangeEvent: true,
    suspendChangeEvent: 0,
    
    getButtonText: function(item) {
        var me = this,
            text = '';
        
        if (item && me.showText === true) {
            if (me.prependText) {
                text += me.prependText;
            }
            text += Ext.isDefined(me.getItemText) ? me.getItemText(item) : item.text;
            return text;
        }
        return me.text;
    },

    setActiveItem: function(item, suppressEvent) {
        var me = this;
        
        if (!Ext.isObject(item)) {
            item = me.menu.getComponent(item);
        }
        if (item) {
            me.suspendChangeEvent++;
            if (!me.rendered) {
                me.text = me.getButtonText(item);
                me.iconCls = item.iconCls;
            } else {
                me.setText(me.getButtonText(item));
                me.setIconCls(item.iconCls);
            }
            me.activeItem = item;
            if (!item.checked) {
                item.setChecked(true, false);
            }
            if (me.forceIcon) {
                me.setIconCls(me.forceIcon);
            }
            me.suspendChangeEvent--;
            if (!suppressEvent && me.suspendChangeEvent === 0) {
                me.fireEvent('change', me, item);
            }
        }
        
    },

    getActiveItem: function() {
        return this.activeItem;
    },
    
    getValue: function() {
       var item = this.getActiveItem();
       return item ? item.value : null;
    },

    initComponent: function() {
        var me      = this,
            checked = 0,
            items,
            i, iLen, item;

        if (me.changeHandler) {
            me.on('change', me.changeHandler, me.scope || me);
            delete me.changeHandler;
        }

        items = (me.menu.items || []).concat(me.items || []);
        me.menu = Ext.applyIf({
            //cls: Ext.baseCSSPrefix + 'cycle-menu',
            items: []
        }, me.menu);

        iLen = items.length;

        // Convert all items to CheckItems
        for (i = 0; i < iLen; i++) {
            item = items[i];

            item = Ext.applyIf({
                group        : me.id,
                itemIndex    : i,
                checkHandler : me.checkHandler,
                scope        : me,
                checked      : item.checked || false
            }, item);

            me.menu.items.push(item);

            if (item.checked) {
                checked = i;
            }
        }

        me.itemCount = me.menu.items.length;
        me.callParent(arguments);
        me.on('click', me.toggleSelected, me);
        me.setActiveItem(checked, me.deferChangeEvent);

        // If configured with a fixed width, the cycling will center a different child item's text each click. Prevent this.
        if (me.width && me.showText) {
            me.addCls(Ext.baseCSSPrefix + 'cycle-fixed-width');
        }
    },

    // private
    checkHandler: function(item, pressed) {
        if (pressed) {
            this.setActiveItem(item);
        }
    },

    toggleSelected: function() {
        var me = this,
            m = me.menu,
            checkItem;

        checkItem = me.activeItem.next(':not([disabled])') || m.items.getAt(0);
        checkItem.setChecked(true);
    },
    
    add: function(item){
        var me = this;
        me.itemCount++;
        return me.menu.add(Ext.applyIf({
            group        : me.id,
            itemIndex    : me.itemCount,
            checkHandler : me.checkHandler,
            scope        : me,
            checked      : item.checked || false
        }, item));
    },
    
    removeAll: function() {
        var me = this;
        me.activeItem = null;
        me.menu.removeAll();
    }
});

Ext.define('Scalr.ui.FormCustomButtonField', {
	alias: 'widget.btnfield',
	extend: 'Scalr.ui.FormCustomButton',

	mixins: {
		field: 'Ext.form.field.Field'
	},
	inputValue: true,

	initComponent : function() {
		var me = this;
		me.callParent();
		me.initField();
	},

	getValue: function() {
		return this.pressed ? this.inputValue : '';
	},

	setValue: function(value) {
		this.toggle(value == this.inputValue ? true : false);
	}
});

Ext.define('Scalr.ui.FormFieldInfoTooltip', {
	extend: 'Ext.form.DisplayField',
	alias: 'widget.displayinfofield',
	initComponent: function () {
		// should use value for message
		var info = this.value || this.info;
		this.value = '<img class="tipHelp" src="/ui2/images/icons/info_icon_16x16.png" data-qtip=\'' + info + '\' style="cursor: help; height: 16px;">';

		this.callParent(arguments);
	}
});

Ext.define('Scalr.ui.FormFieldFarmRoles', {
	extend: 'Ext.form.FieldSet',
	alias: 'widget.farmroles',

	layout: 'column',

	initComponent: function() {
		this.callParent(arguments);
		this.params = this.params || {};
		this.params.options = this.params.options || [];

		var farmField = this.down('[name="farmId"]'), farmRoleField = this.down('[name="farmRoleId"]'), serverField = this.down('[name="serverId"]');
		farmField.store.loadData(this.params['dataFarms'] || []);
		farmField.setValue(this.params['farmId'] || '');

		if (this.params.options.indexOf('requiredFarm') != -1)
			farmField.allowBlank = false;

		if (this.params.options.indexOf('requiredFarmRole') != -1)
			farmRoleField.allowBlank = false;

		if (this.params.options.indexOf('requiredServer') != -1)
			serverField.allowBlank = false;

		delete this.params['farmId'];
		delete this.params['farmRoleId'];
		delete this.params['serverId'];
		this.fixWidth();
	},

	fixWidth: function() {
		var farmField = this.down('[name="farmId"]'), farmRoleField = this.down('[name="farmRoleId"]'), serverField = this.down('[name="serverId"]');

		if (this.params.options.indexOf('disabledServer') != -1) {
			farmField.columnWidth = 0.5;
			farmRoleField.columnWidth = 0.5;
		} else if (this.params.options.indexOf('disabledFarmRole') != -1) {
			farmField.columnWidth = 1;
		} else {
			farmField.columnWidth = 1/3;
			farmRoleField.columnWidth = 1/3;
			serverField.columnWidth = 1/3;
		}
	},

	items: [{
		xtype: 'combo',
		hideLabel: true,
		name: 'farmId',
		store: {
			fields: [ 'id', 'name' ],
			proxy: 'object'
		},
		valueField: 'id',
		displayField: 'name',
		emptyText: 'Select a farm',
		editable: false,
		queryMode: 'local',
		listeners: {
			change: function () {
				var me = this, fieldset = this.up('fieldset');

				if (fieldset.params.options.indexOf('disabledFarmRole') != -1)
					return;

				if (fieldset.params.options.indexOf('disabledServer') == -1)
					fieldset.down('[name="serverId"]').hide();

				if (!this.getValue() || this.getValue() == '0') {
					fieldset.down('[name="farmRoleId"]').hide();
					return;
				}

				var successHandler = function(data) {
					var field = fieldset.down('[name="farmRoleId"]');
					field.show();
					if (data['dataFarmRoles']) {
						field.emptyText = 'Select a role';
						field.reset();
						field.store.loadData(data['dataFarmRoles']);

						if (fieldset.params['farmRoleId']) {
							field.setValue(fieldset.params['farmRoleId']);
							delete fieldset.params['farmRoleId'];
						} else {
							if (fieldset.params.options.indexOf('addAll') != -1) {
								field.setValue('0');
							} else {
								if (field.store.getCount() == 1)
									field.setValue(field.store.first()); // preselect single element
								else
									field.setValue('');
							}
						}

						field.enable();
						field.clearInvalid();
					} else {
						field.store.removeAll();
						field.emptyText = 'No roles';
						field.reset();
						field.disable();
						if (field.allowBlank == false)
							field.markInvalid('This field is required');
					}
				};

				if (fieldset.params['dataFarmRoles']) {
					successHandler(fieldset.params);
					delete fieldset.params['dataFarmRoles'];
				} else
					Scalr.Request({
						url: '/farms/xGetFarmWidgetRoles/',
						params: {farmId: me.getValue(), options: fieldset.params['options'].join(',')},
						processBox: {
							type: 'load',
							msg: 'Loading farm roles ...'
						},
						success: successHandler
					});
			}
		}
	}, {
		xtype: 'combo',
		hideLabel: true,
		hidden: true,
		name: 'farmRoleId',
		store: {
			fields: [ 'id', 'name', 'platform', 'role_id' ],
			proxy: 'object'
		},
		valueField: 'id',
		displayField: 'name',
		emptyText: 'Select a role',
		margin: '0 0 0 5',
		editable: false,
		queryMode: 'local',
		listeners: {
			change: function () {
				var me = this, fieldset = this.up('fieldset');

				if (fieldset.params.options.indexOf('disabledServer') != -1) {
					fieldset.down('[name="serverId"]').hide();
					return;
				}

				if (! me.getValue() || me.getValue() == '0') {
					fieldset.down('[name="serverId"]').hide();
					return;
				}

				var successHandler = function (data) {
					var field = fieldset.down('[name="serverId"]');
					field.show();
					if (data['dataServers']) {
						field.emptyText = 'Select a server';
						field.reset();
						field.store.load({data: data['dataServers']});

						if (fieldset.params['serverId']) {
							field.setValue(fieldset.params['serverId']);
							delete fieldset.params['serverId'];
						} else {
							field.setValue(0);
						}

						field.enable();
					} else {
						field.emptyText = 'No running servers';
						field.reset();
						field.disable();
					}
				};

				if (fieldset.params['dataServers']) {
					successHandler(fieldset.params);
					delete fieldset.params['dataServers'];
				} else
					Scalr.Request({
						url: '/farms/xGetFarmWidgetServers',
						params: {farmRoleId: me.getValue(), options: fieldset.params['options'].join(',')},
						processBox: {
							type: 'load',
							msg: 'Loading servers ...'
						},
						success: successHandler
					});
			}
		}
	}, {
		xtype: 'combo',
		hideLabel: true,
		hidden: true,
		name: 'serverId',
		store: {
			fields: [ 'id', 'name' ],
			proxy: 'object'
		},
		valueField: 'id',
		displayField: 'name',
		margin: '0 0 0 5',
		editable: false,
		queryMode: 'local'
	}],

	optionChange: function(action, key) {
		var index = this.params.options.indexOf(key);

		if (action == 'remove' && index != -1 || action == 'add' && index == -1) {
			if (action == 'remove') {
				this.params.options.splice(index, index);
			} else {
				this.params.options.push(key);
			}

			switch(key) {
				case 'disabledFarmRole':
					if (action == 'add') {
						this.down('[name="farmRoleId"]').hide();
						this.down('[name="serverId"]').hide();
					} else {
						this.down('[name="farmId"]').fireEvent('change');
					}
					break;

				case 'disabledServer':
					if (action == 'add') {
						this.down('[name="serverId"]').hide();
					} else {
						this.down('[name="farmRoleId"]').fireEvent('change');
					}
					break;
			}
		}

		this.fixWidth();
		this.updateLayout();
	},

	syncItems: function () {
		/*if (this.enableFarmRoleId && this.down('[name="farmId"]').getValue()) {
		 this.down('[name="farmId"]').fireEvent('change');
		 } else
		 this.down('[name="farmRoleId"]').hide();

		 if (! this.enableServerId)
		 this.down('[name="serverId"]').hide();*/
	}
});

Ext.define('Scalr.ui.FormFieldProgress', {
	extend: 'Ext.form.field.Display',
	alias: 'widget.progressfield',
	
    fieldSubTpl: [
        '<div id="{id}"',
        '<tpl if="fieldStyle"> style="{fieldStyle}"</tpl>', 
        ' class="{fieldCls}"><div class="x-form-progress-bar"></div><span class="x-form-progress-text">{value}</span></div>',
        {
            compiled: true,
            disableFormats: true
        }
    ],
	
	fieldCls: Ext.baseCSSPrefix + 'form-progress-field',
	
	progressTextCls: 'x-form-progress-text',
	progressBarCls: 'x-form-progress-bar',
	warningPercentage: 60,
	alertPercentage: 80,
	warningCls: 'x-form-progress-bar-warning',
	alertCls: 'x-form-progress-bar-alert',
	
	valueField: 'value',
	emptyText: '',
	units: '%',
	
	setRawValue: function(value) {
		var me = this, 
			percentage;
		me.rawValue = Ext.isObject(value) ? Ext.clone(value) : value;
		percentage = this.getProgressBarPercentage()*1;
		if (me.rendered) {
			var progressbar = me.inputEl.down('.'+me.progressBarCls);
			progressbar.stopAnimation();
			progressbar.setWidth(0).removeCls(me.warningCls + ' ' + me.alertCls);

			if (percentage > me.alertPercentage) {
				progressbar.addCls(me.alertCls);
			} else if (percentage > me.warningPercentage) {
				progressbar.addCls(me.warningCls);
			}
			progressbar.animate({
				duration: 500,
				from: {
					width: 0
				},
				to: {
					width: percentage+ '%'
				}
			});
			me.inputEl.down('.'+me.progressTextCls).dom.innerHTML = me.getDisplayValue();
			//me.updateLayout();
		}
		return value;
	},
	
	getProgressBarPercentage: function() {
        var value = this.getRawValue(),
            size = 0;
		if (Ext.isNumeric(value)) {
			size = value*100;
		} else if (Ext.isObject(value)) {
			size = Math.round(value[this.valueField]*100/value.total);
		}
		return size;
	},
	
    getDisplayValue: function() {
        var value = this.getRawValue(),
            display;
		if (Ext.isObject(value)) {
			if (this.units == '%') {
				display = Math.round(value[this.valueField]*100/value.total);
			} else {
				display = value[this.valueField] + ' of ' + value.total;
			}
		} else if (Ext.isNumeric(value)) {
			display = Math.round(value*100);
		}
        if (display !== undefined) {
			display += ' ' + this.units;
		} else if (value){
            display = value;
        }
		
        return display !== undefined ? display : this.emptyText;
    },
	
	setText: function(text) {
		var me = this;
		if (me.rendered) {
			me.inputEl.down('.'+me.progressTextCls).dom.innerHTML = text;
		}
	},
	
    valueToRaw: function(value) {
        return value;
    }
});

Ext.define('Scalr.ui.CloudLocationMap', {
	extend: 'Ext.Component',
	alias: 'widget.cloudlocationmap',
	baseCls: 'scalr-ui-cloudlocationmap',
	
	settings: {
		common: {
			size: {
				width: 210,
				height: 100
			},
			style: 'background: url(/ui2/images/widget/cloudlocationmap/maps.png?1.1) 0 -400px no-repeat;'
		},
		large: {
			size: {
				width: 320,
				height: 140
			},
			style: 'background: url(/ui2/images/widget/cloudlocationmap/maps_large.png?1.1) 0 -560px no-repeat;'
		}
	},
	size: 'common',
	
	mode: 'multi',
	platforms: {},
	regions: {
		us: 0,
		sa: 3,
		eu: 2,
		ap: 1,
		unknown: 4,
		all: 5,
        jp: 6
	},
	locations: {
		ec2: {
			'ap-northeast-1': {region: 'ap', x: {common: 125, large: 183}, y: {common:27, large: 40}},
			'ap-southeast-1': {region: 'ap', x: {common: 95, large: 142}, y: {common:58, large: 81}},
			'ap-southeast-2': {region: 'ap', x: {common: 133, large: 193}, y: {common:88, large: 118}},
			'eu-west-1': {region: 'eu', x: {common: 75, large: 112}, y: {common:14, large: 18}},
			'sa-east-1': {region: 'sa', x: {common: 114, large: 170}, y: {common:51, large: 71}},
			'us-east-1': {region: 'us', x: {common: 145, large: 212}, y: {common:53, large: 74}},
			'us-west-1': {region: 'us', x: {common: 43, large: 78}, y: {common:50, large: 66}},
			'us-west-2': {region: 'us', x: {common: 35, large: 72}, y: {common:26, large: 40}}
		},
		ec2_world: {//all locations on a single map
			'ap-northeast-1': {region: 'all', x: {common: 182, large: 274}, y: {common:32, large: 46}},
			'ap-southeast-1': {region: 'all', x: {common: 156, large: 244}, y: {common:54, large: 78}},
			'ap-southeast-2': {region: 'all', x: {common: 186, large: 286}, y: {common:76, large: 110}},
			'eu-west-1': {region: 'all', x: {common: 88, large: 140}, y: {common:24, large: 32}},
			'sa-east-1': {region: 'all', x: {common: 68, large: 104}, y: {common:70, large: 100}},
			'us-east-1': {region: 'all', x: {common: 48, large: 78}, y: {common:34, large: 48}},
			'us-west-1': {region: 'all', x: {common: 28, large: 42}, y: {common:40, large: 50}},
			'us-west-2': {region: 'all', x: {common: 22, large: 40}, y: {common:30, large: 40}}
		},
		rackspace: {
			'rs-LONx': {region: 'eu', x: {common: 75, large: 120}, y: {common:14, large: 18}},
			'rs-ORD1': {region: 'us', x: {common: 120, large: 180}, y: {common:32, large: 44}}
		},
		rackspace_world: {
			'rs-LONx': {region: 'all', x: {common: 88, large: 0}, y: {common:24, large: 0}},
			'rs-ORD1': {region: 'all', x: {common: 40, large: 0}, y: {common:28, large: 0}}
		},
		rackspacengus: {
			'DFW': {region: 'us', x: {common: 100, large: 154}, y: {common:60, large: 78}},
			'ORD': {region: 'us', x: {common: 120, large: 180}, y: {common:32, large: 44}}
		},
		rackspacenguk: {
			'LON': {region: 'eu', x: {common: 75, large: 120}, y: {common:14, large: 18}}
		},
        idcf: {
            'jp-east-t1v': {region: 'jp', x: {common: 114, large: 168}, y: {common:66, large: 88}},//Tokyo
            'jp-east-f2v': {region: 'jp', x: {common: 116, large: 176}, y: {common:46, large: 68}}//Shirakawa
        },
        gce: {
            'us-central1-a': {region: 'all', x: {common: 30, large: 46}, y: {common:32, large: 48}},
            'us-central1-b': {region: 'all', x: {common: 36, large: 56}, y: {common:30, large: 46}},
            'us-central2-a': {region: 'all', x: {common: 42, large: 66}, y: {common:34, large: 48}},
            'europe-west1-a': {region: 'all', x: {common: 95, large: 150}, y: {common:28, large: 38}},
            'europe-west1-b': {region: 'all', x: {common: 100, large: 160}, y: {common:26, large: 36}}
        }
	},
	renderTpl: [
		'<div class="map" style="{mapStyle}"><div class="title"></div></div>'
	],
	renderSelectors: {
		titleEl: '.title',
		mapEl: '.map'
	},
    renderData: {},
    
	constructor: function(config) {
		this.callParent([config]);
		this.locations.rds = this.locations.ec2;
		this.settings[this.size].style += 'width:' + this.settings[this.size].size.width + 'px;';
		this.settings[this.size].style += 'height:' + this.settings[this.size].size.height + 'px;';
		this.mapSize = this.settings[this.size].size;
        this.renderData.mapStyle = this.settings[this.size].style
	},
	
	selectLocation: function(platform, selectedLocations, allLocations, map){
        var me = this,
            locationFound = false,
            platformMap = me.locations[platform + '_' + map] !== undefined ? platform + '_' + map : platform;
        me.suspendLayouts();
		allLocations = allLocations || [];
		me.reset();
		if (selectedLocations === 'all') {
			me.mapEl.setStyle('background-position', this.getRegionPosition('all'));
            locationFound = true;
            if (platform === 'gce') {
                Ext.Object.each(me.locations[platformMap], function(key, value) {
                    me.addLocation(platform, key, value, true, true);
                });
            }
		} else if (me.locations[platformMap]) {
            selectedLocations = Ext.isArray(selectedLocations) ? selectedLocations : [selectedLocations];
            var selectedLocation = this.locations[platformMap][selectedLocations[0]];
            if (selectedLocation) {
                me.mapEl.setStyle('background-position', me.getRegionPosition(selectedLocation.region));
                if (selectedLocation.region != 'unknown') {
                    locationFound = true;
                    Ext.Object.each(me.locations[platformMap], function(key, value) {
                        var selected = Ext.Array.contains(selectedLocations, key);
                        if (selected || Ext.Array.contains(allLocations, key)) {
                            me.addLocation(platform, key, value, selected);
                        }
                    });
                }
            }
		}
        if (!locationFound) {
			me.mapEl.setStyle('background-position', me.getRegionPosition('unknown'));
		}
        me.resumeLayouts(true);
	},
    
    addLocation: function(platform, name, data, selected, silent) {
        var me = this,
            title = name;
        if (me.platforms[platform] && me.platforms[platform].locations[name]) {
            title = me.platforms[platform].locations[name];
        }
        var el = Ext.DomHelper.append(me.mapEl.dom, '<div data-location="'+Ext.util.Format.htmlEncode(name)+'" style="top:'+data.y[me.size]+'px;left:'+data.x[me.size]+'px" class="location'+(selected ? ' selected' : '')+'" title="'+Ext.util.Format.htmlEncode(title)+'"></div>', true)
        if (!silent) {
            el.on('click', function(){
                me.fireEvent('selectlocation', this.getAttribute('data-location'), !this.hasCls('selected'));
            });
        }
        if (me.mode == 'single' && platform === 'ec2' && selected) {
            me.titleEl.setHTML(title);
            me.fixTitlePosition();
        }
    },
	
	fixTitlePosition: function() {
		var loc = this.mapEl.query('.location');
		if (loc[0]) {
			var el = Ext.fly(loc[0]);
			//we are trying to avoid overlapping between title and location div
			if (el.getTop(true) > this.mapSize.height/2) {
				this.titleEl.setTop(el.getTop(true)-35);
			} else {
				this.titleEl.setTop(el.getTop(true)+20);
			}
		}
	},
	
	reset: function() {
		var loc = this.mapEl.query('.location');
		for (var i=0, len=loc.length; i<len; i++) {
			Ext.removeNode(loc[i]);
		}
        this.titleEl.setHTML('');
	},
	
	getRegionPosition: function(region) {
		return '0 -' + (this.regions[region]*this.mapSize.height) + 'px';
	}
	
});