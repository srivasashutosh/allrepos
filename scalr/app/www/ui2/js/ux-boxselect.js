//http://www.sencha.com/forum/showthread.php?134751-Ext.ux.form.field.BoxSelect-Intuitive-Multi-Select-ComboBox
Ext.define('Ext.ux.form.field.BoxSelect', {
	extend:'Ext.form.field.ComboBox',
	alias: ['widget.comboboxselect', 'widget.boxselect'],
	requires: ['Ext.selection.Model', 'Ext.data.Store'],

	multiSelect: true,

	forceSelection: true,

	createNewOnEnter: false,

	createNewOnBlur: false,

	encodeSubmitValue: false,

	triggerOnClick: true,

	stacked: false,

	pinList: true,

	filterPickList: false,

	selectOnFocus: true,

	grow: true,

	growMin: false,

	growMax: false,

	fieldSubTpl: [
		'<div id="{cmpId}-listWrapper" class="x-boxselect {fieldCls} {typeCls}">',
		'<ul id="{cmpId}-itemList" class="x-boxselect-list">',
		'<li id="{cmpId}-inputElCt" class="x-boxselect-input">',
		'<input id="{cmpId}-inputEl" type="{type}" ',
		'<tpl if="name">name="{name}" </tpl>',
		'<tpl if="value"> value="{[Ext.util.Format.htmlEncode(values.value)]}"</tpl>',
		'<tpl if="size">size="{size}" </tpl>',
		'<tpl if="tabIdx">tabIndex="{tabIdx}" </tpl>',
		'<tpl if="disabled"> disabled="disabled"</tpl>',
		'class="x-boxselect-input-field {inputElCls}" autocomplete="off">',
		'</li>',
		'</ul>',
		'</div>',
		{
			compiled: true,
			disableFormats: true
		}
	],

	childEls: [ 'listWrapper', 'itemList', 'inputEl', 'inputElCt' ],

	componentLayout: 'boxselectfield',

	initComponent: function() {
		var me = this,
		typeAhead = me.typeAhead;

		if (typeAhead && !me.editable) {
			Ext.Error.raise('If typeAhead is enabled the combo must be editable: true -- please change one of those settings.');
		}

		Ext.apply(me, {
			typeAhead: false
		});

		me.callParent();

		me.typeAhead = typeAhead;

		me.selectionModel = new Ext.selection.Model({
			store: me.valueStore,
			mode: 'MULTI',
			lastFocused: null,
			onSelectChange: function(record, isSelected, suppressEvent, commitFn) {
				commitFn();
			}
		});

		if (!Ext.isEmpty(me.delimiter) && me.multiSelect) {
			me.delimiterRegexp = new RegExp(String(me.delimiter).replace(/[$%()*+.?\[\\\]{|}]/g, "\\$&"));
		}
	},

	initEvents: function() {
		var me = this;

		me.callParent(arguments);

		if (!me.enableKeyEvents) {
			me.mon(me.inputEl, 'keydown', me.onKeyDown, me);
		}
		me.mon(me.inputEl, 'paste', me.onPaste, me);
		me.mon(me.listWrapper, 'click', me.onItemListClick, me);

		// I would prefer to use relayEvents here to forward these events on, but I want
		// to pass the field instead of exposing the underlying selection model
		me.mon(me.selectionModel, {
			'selectionchange': function(selModel, selectedRecs) {
				me.applyMultiselectItemMarkup();
				me.fireEvent('valueselectionchange', me, selectedRecs);
			},
			'focuschange': function(selectionModel, oldFocused, newFocused) {
				me.fireEvent('valuefocuschange', me, oldFocused, newFocused);
			},
			scope: me
		});
	},

	onBindStore: function(store, initial) {
		var me = this;

		if (store) {
			me.valueStore = new Ext.data.Store({
				model: store.model,
				proxy: {
					type: 'memory'
				}
			});
			me.mon(me.valueStore, 'datachanged', me.applyMultiselectItemMarkup, me);
			if (me.selectionModel) {
				me.selectionModel.bindStore(me.valueStore);
			}
		}
	},

	onUnbindStore: function(store) {
		var me = this,
		valueStore = me.valueStore;

		if (valueStore) {
			if (me.selectionModel) {
				me.selectionModel.setLastFocused(null);
				me.selectionModel.deselectAll();
				me.selectionModel.bindStore(null);
			}
			me.mun(valueStore, 'datachanged', me.applyMultiselectItemMarkup, me);
			valueStore.destroy();
			me.valueStore = null;
		}

		me.callParent(arguments);
	},

	createPicker: function() {
		var me = this,
		picker = me.callParent(arguments);

		me.mon(picker, {
			'beforerefresh': me.onBeforeListRefresh,
			scope: me
		});

		if (me.filterPickList) {
			picker.addCls('x-boxselect-hideselections');
		}

		return picker;
	},

	onDestroy: function() {
		var me = this;

		Ext.destroyMembers(me, 'valueStore', 'selectionModel');

		me.callParent(arguments);
	},

	getSubTplData: function() {
		var me = this,
			data = me.callParent(),
			isEmpty = me.emptyText && data.value.length < 1;

		if (isEmpty) {
			data.value = me.emptyText;
		} else {
			data.value = '';
		}
		data.inputElCls = data.fieldCls.match(me.emptyCls) ? me.emptyCls : '';

		return data;
	},

	afterRender: function() {
		var me = this;

		if (Ext.supports.Placeholder && me.inputEl && me.emptyText) {
			delete me.inputEl.dom.placeholder;
		}

		me.bodyEl.applyStyles('vertical-align:top');

		if (me.grow) {
			if (Ext.isNumber(me.growMin) && (me.growMin > 0)) {
				me.listWrapper.applyStyles('min-height:'+me.growMin+'px');
			}
			if (Ext.isNumber(me.growMax) && (me.growMax > 0)) {
				me.listWrapper.applyStyles('max-height:'+me.growMax+'px');
			}
		}

		if (me.stacked === true) {
			me.itemList.addCls('x-boxselect-stacked');
		}

		if (!me.multiSelect) {
			me.itemList.addCls('x-boxselect-singleselect');
		}

		me.applyMultiselectItemMarkup();

		me.callParent(arguments);
	},

	findRecord: function(field, value) {
		var ds = this.store,
		matches;

		if (!ds) {
			return false;
		}

		matches = ds.queryBy(function(rec, id) {
			return rec.isEqual(rec.get(field), value);
		});

		return (matches.getCount() > 0) ? matches.first() : false;
	},

	onLoad: function() {
		var me = this,
		valueField = me.valueField,
		valueStore = me.valueStore,
		changed = false;

		if (valueStore) {
			if (!Ext.isEmpty(me.value) && (valueStore.getCount() == 0)) {
				me.setValue(me.value, false, true);
			}

			valueStore.suspendEvents();
			valueStore.each(function(rec) {
				var r = me.findRecord(valueField, rec.get(valueField)),
				i = r ? valueStore.indexOf(rec) : -1;
				if (i >= 0) {
					valueStore.removeAt(i);
					valueStore.insert(i, r);
					changed = true;
				}
			});
			valueStore.resumeEvents();
			if (changed) {
				valueStore.fireEvent('datachanged', valueStore);
			}
		}

		me.callParent(arguments);
	},

	isFilteredRecord: function(record) {
		var me = this,
		store = me.store,
		valueField = me.valueField,
		storeRecord,
		filtered = false;

		storeRecord = store.findExact(valueField, record.get(valueField));

		filtered = ((storeRecord === -1) && (!store.snapshot || (me.findRecord(valueField, record.get(valueField)) !== false)));

		filtered = filtered || (!filtered && (storeRecord === -1) && (me.forceSelection !== true) &&
			(me.valueStore.findExact(valueField, record.get(valueField)) >= 0));

		return filtered;
	},

	doRawQuery: function() {
		var me = this,
		rawValue = me.inputEl.dom.value;

		if (me.multiSelect) {
			rawValue = rawValue.split(me.delimiter).pop();
		}

		this.doQuery(rawValue, false, true);
	},

	onBeforeListRefresh: function() {
		this.ignoreSelection++;
	},

	onListRefresh: function() {
		this.callParent(arguments);
		if (this.ignoreSelection > 0) {
			--this.ignoreSelection;
		}
	},

	onListSelectionChange: function(list, selectedRecords) {
		var me = this,
		valueStore = me.valueStore,
		mergedRecords = [],
		i;

		// Only react to selection if it is not called from setValue, and if our list is
		// expanded (ignores changes to the selection model triggered elsewhere)
		if ((me.ignoreSelection <= 0) && me.isExpanded) {
			// Pull forward records that were already selected or are now filtered out of the store
			valueStore.each(function(rec) {
				if (Ext.Array.contains(selectedRecords, rec) || me.isFilteredRecord(rec)) {
					mergedRecords.push(rec);
				}
			});
			mergedRecords = Ext.Array.merge(mergedRecords, selectedRecords);

			i = Ext.Array.intersect(mergedRecords, valueStore.getRange()).length;
			if ((i != mergedRecords.length) || (i != me.valueStore.getCount())) {
				me.setValue(mergedRecords, false);
				if (!me.multiSelect || !me.pinList) {
					Ext.defer(me.collapse, 1, me);
				}
				if (valueStore.getCount() > 0) {
					me.fireEvent('select', me, valueStore.getRange());
				}
			}
			me.inputEl.focus();
			if (!me.pinList) {
				me.inputEl.dom.value = '';
			}
			if (me.selectOnFocus) {
				me.inputEl.dom.select();
			}
		}
	},

	syncSelection: function() {
		var me = this,
		picker = me.picker,
		valueField = me.valueField,
		pickStore, selection, selModel;

		if (picker) {
			pickStore = picker.store;

			// From the value, find the Models that are in the store's current data
			selection = [];
			if (me.valueStore) {
				me.valueStore.each(function(rec) {
					var i = pickStore.findExact(valueField, rec.get(valueField));
					if (i >= 0) {
						selection.push(pickStore.getAt(i));
					}
				});
			}

			// Update the selection to match
			me.ignoreSelection++;
			selModel = picker.getSelectionModel();
			selModel.deselectAll();
			if (selection.length > 0) {
				selModel.select(selection);
			}
			if (me.ignoreSelection > 0) {
				--me.ignoreSelection;
			}
		}
	},

	doAlign: function(){
		var me = this,
			picker = me.picker,
			aboveSfx = '-above',
			isAbove;

		me.picker.alignTo(me.listWrapper, me.pickerAlign, me.pickerOffset);
		// add the {openCls}-above class if the picker was aligned above
		// the field due to hitting the bottom of the viewport
		isAbove = picker.el.getY() < me.inputEl.getY();
		me.bodyEl[isAbove ? 'addCls' : 'removeCls'](me.openCls + aboveSfx);
		picker[isAbove ? 'addCls' : 'removeCls'](picker.baseCls + aboveSfx);
	},

	alignPicker: function() {
		var me = this,
			picker = me.picker,
			pickerScrollPos = picker.getTargetEl().dom.scrollTop;

		me.callParent(arguments);

		if (me.isExpanded) {
			if (me.matchFieldWidth) {
				// Auto the height (it will be constrained by min and max width) unless there are no records to display.
				picker.setWidth(me.listWrapper.getWidth());
			}

			picker.getTargetEl().dom.scrollTop = pickerScrollPos;
		}
	},

	getCursorPosition: function() {
		var cursorPos;
		if (Ext.isIE) {
			cursorPos = document.selection.createRange();
			cursorPos.collapse(true);
			cursorPos.moveStart("character", -this.inputEl.dom.value.length);
			cursorPos = cursorPos.text.length;
		} else {
			cursorPos = this.inputEl.dom.selectionStart;
		}
		return cursorPos;
	},

	hasSelectedText: function() {
		var sel, range;
		if (Ext.isIE) {
			sel = document.selection;
			range = sel.createRange();
			return (range.parentElement() == this.inputEl.dom);
		} else {
			return this.inputEl.dom.selectionStart != this.inputEl.dom.selectionEnd;
		}
	},

	onKeyDown: function(e, t) {
		var me = this,
		key = e.getKey(),
		rawValue = me.inputEl.dom.value,
		valueStore = me.valueStore,
		selModel = me.selectionModel,
		stopEvent = false;

		if (me.readOnly || me.disabled || !me.editable) {
			return;
		}

		if (me.isExpanded && (key == e.A && e.ctrlKey)) {
			// CTRL-A when picker is expanded - add all items in current picker store page to current value
			me.select(me.getStore().getRange());
			selModel.setLastFocused(null);
			selModel.deselectAll();
			me.collapse();
			me.inputEl.focus();
			stopEvent = true;
		} else if ((valueStore.getCount() > 0) &&
				((rawValue == '') || ((me.getCursorPosition() === 0) && !me.hasSelectedText()))) {
			// Keyboard navigation of current values
			var lastSelectionIndex = (selModel.getCount() > 0) ? valueStore.indexOf(selModel.getLastSelected() || selModel.getLastFocused()) : -1;

			if ((key == e.BACKSPACE) || (key == e.DELETE)) {
				if (lastSelectionIndex > -1) {
					if (selModel.getCount() > 1) {
						lastSelectionIndex = -1;
					}
					me.valueStore.remove(selModel.getSelection());
				} else {
					me.valueStore.remove(me.valueStore.last());
				}
				selModel.clearSelections();
				me.setValue(me.valueStore.getRange());
				if (lastSelectionIndex > 0) {
					selModel.select(lastSelectionIndex - 1);
				}
				stopEvent = true;
			} else if ((key == e.RIGHT) || (key == e.LEFT)) {
				if ((lastSelectionIndex == -1) && (key == e.LEFT)) {
					selModel.select(valueStore.last());
					stopEvent = true;
				} else if (lastSelectionIndex > -1) {
					if (key == e.RIGHT) {
						if (lastSelectionIndex < (valueStore.getCount() - 1)) {
							selModel.select(lastSelectionIndex + 1, e.shiftKey);
							stopEvent = true;
						} else if (!e.shiftKey) {
							selModel.setLastFocused(null);
							selModel.deselectAll();
							stopEvent = true;
						}
					} else if ((key == e.LEFT) && (lastSelectionIndex > 0)) {
						selModel.select(lastSelectionIndex - 1, e.shiftKey);
						stopEvent = true;
					}
				}
			} else if (key == e.A && e.ctrlKey) {
				selModel.selectAll();
				stopEvent = e.A;
			}
			me.inputEl.focus();
		}

		if (stopEvent) {
			me.preventKeyUpEvent = stopEvent;
			e.stopEvent();
			return;
		}

		// Prevent key up processing for enter if it is being handled by the picker
		if (me.isExpanded && (key == e.ENTER) && me.picker.highlightedItem) {
			me.preventKeyUpEvent = true;
		}

		if (me.enableKeyEvents) {
			me.callParent(arguments);
		}

		if (!e.isSpecialKey() && !e.hasModifier()) {
			me.selectionModel.setLastFocused(null);
			me.selectionModel.deselectAll();
			me.inputEl.focus();
		}
	},

	onKeyUp: function(e, t) {
		var me = this,
		rawValue = me.inputEl.dom.value;

		if (me.preventKeyUpEvent) {
			e.stopEvent();
			if ((me.preventKeyUpEvent === true) || (e.getKey() === me.preventKeyUpEvent)) {
				delete me.preventKeyUpEvent;
			}
			return;
		}

		if (me.multiSelect && (me.delimiterRegexp && me.delimiterRegexp.test(rawValue)) ||
				((me.createNewOnEnter === true) && e.getKey() == e.ENTER)) {
			rawValue = Ext.Array.clean(rawValue.split(me.delimiterRegexp));
			me.inputEl.dom.value = '';
			me.setValue(me.valueStore.getRange().concat(rawValue));
			me.inputEl.focus();
		}

		me.callParent([e,t]);
	},

	onPaste: function(e, t) {
		var me = this,
			rawValue = me.inputEl.dom.value,
			clipboard = (e && e.browserEvent && e.browserEvent.clipboardData) ? e.browserEvent.clipboardData : false;

		if (me.multiSelect && (me.delimiterRegexp && me.delimiterRegexp.test(rawValue))) {
			if (clipboard && clipboard.getData) {
				if (/text\/plain/.test(clipboard.types)) {
					rawValue = clipboard.getData('text/plain');
				} else if (/text\/html/.test(clipboard.types)) {
					rawValue = clipboard.getData('text/html');
				}
			}

			rawValue = Ext.Array.clean(rawValue.split(me.delimiterRegexp));
			me.inputEl.dom.value = '';
			me.setValue(me.valueStore.getRange().concat(rawValue));
			me.inputEl.focus();
		}
	},

	onExpand: function() {
		var me = this,
			keyNav = me.listKeyNav;

		me.callParent(arguments);

		if (keyNav || !me.filterPickList) {
			return;
		}
		keyNav = me.listKeyNav;
		keyNav.highlightAt = function(index) {
			var boundList = this.boundList,
				item = boundList.all.item(index),
				len = boundList.all.getCount(),
				direction;

			if (item && item.hasCls('x-boundlist-selected')) {
				if ((index == 0) || !boundList.highlightedItem || (boundList.indexOf(boundList.highlightedItem) < index)) {
					direction = 1;
				} else {
					direction = -1;
				}
				do {
					index = index + direction;
					item = boundList.all.item(index);
				} while ((index > 0) && (index < len) && item.hasCls('x-boundlist-selected'));

				if (item.hasCls('x-boundlist-selected')) {
					return;
				}
			}

			if (item) {
				item = item.dom;
				boundList.highlightItem(item);
				boundList.getTargetEl().scrollChildIntoView(item, false);
			}
		};
	},

	onTypeAhead: function() {
		var me = this,
		displayField = me.displayField,
		inputElDom = me.inputEl.dom,
		valueStore = me.valueStore,
		boundList = me.getPicker(),
		record, newValue, len, selStart;

		if (me.filterPickList) {
			var fn = this.createFilterFn(displayField, inputElDom.value);
			record = me.store.findBy(function(rec) {
				return ((valueStore.indexOfId(rec.getId()) === -1) && fn(rec));
			});
			record = (record === -1) ? false : me.store.getAt(record);
		} else {
			record = me.store.findRecord(displayField, inputElDom.value);
		}

		if (record) {
			newValue = record.get(displayField);
			len = newValue.length;
			selStart = inputElDom.value.length;
			boundList.highlightItem(boundList.getNode(record));
			if (selStart !== 0 && selStart !== len) {
				inputElDom.value = newValue;
				me.selectText(selStart, newValue.length);
			}
		}
	},

	onItemListClick: function(evt, el, o) {
		var me = this,
		itemEl = evt.getTarget('.x-boxselect-item'),
		closeEl = itemEl ? evt.getTarget('.x-boxselect-item-close') : false;

		if (me.readOnly || me.disabled) {
			return;
		}

		evt.stopPropagation();

		if (itemEl) {
			if (closeEl) {
				me.removeByListItemNode(itemEl);
				if (me.valueStore.getCount() > 0) {
					me.fireEvent('select', me, me.valueStore.getRange());
				}
			} else {
				me.toggleSelectionByListItemNode(itemEl, evt.shiftKey);
			}
			me.inputEl.focus();
		} else {
			if (me.selectionModel.getCount() > 0) {
				me.selectionModel.setLastFocused(null);
				me.selectionModel.deselectAll();
			}
			if (me.triggerOnClick) {
				me.onTriggerClick();
			}
		}
	},

	getMultiSelectItemMarkup: function() {
		var me = this;

		if (!me.multiSelectItemTpl) {
			if (!me.labelTpl) {
				me.labelTpl = Ext.create('Ext.XTemplate',
					'{[values.' + me.displayField + ']}'
				);
			} else if (Ext.isString(me.labelTpl) || Ext.isArray(me.labelTpl)) {
				me.labelTpl = Ext.create('Ext.XTemplate', me.labelTpl);
			}

			me.multiSelectItemTpl = [
			'<tpl for=".">',
			'<li class="x-boxselect-item ',
			'<tpl if="this.isSelected(values.'+ me.valueField + ')">',
			' selected',
			'</tpl>',
			'" qtip="{[typeof values === "string" ? values : values.' + me.displayField + ']}">' ,
			'<div class="x-boxselect-item-text">{[typeof values === "string" ? values : this.getItemLabel(values)]}</div>',
			'<div class="x-tab-close-btn x-boxselect-item-close"></div>' ,
			'</li>' ,
			'</tpl>',
			{
				compile: true,
				disableFormats: true,
				isSelected: function(value) {
					var i = me.valueStore.findExact(me.valueField, value);
					if (i >= 0) {
						return me.selectionModel.isSelected(me.valueStore.getAt(i));
					}
					return false;
				},
				getItemLabel: function(values) {
					return me.getTpl('labelTpl').apply(values);
				}
			}
			];
		}

		return this.getTpl('multiSelectItemTpl').apply(Ext.Array.pluck(this.valueStore.getRange(), 'data'));
	},

	applyMultiselectItemMarkup: function() {
		var me = this,
		itemList = me.itemList,
		item;

		if (itemList) {
			while ((item = me.inputElCt.prev()) != null) {
				item.remove();
			}
			me.inputElCt.insertHtml('beforeBegin', me.getMultiSelectItemMarkup());
		}

		Ext.Function.defer(function() {
			if (me.picker && me.isExpanded) {
				me.alignPicker();
			}
			if (me.hasFocus) {
				me.inputElCt.scrollIntoView(me.listWrapper);
			}
		}, 15);
	},

	getRecordByListItemNode: function(itemEl) {
		var me = this,
		itemIdx = 0,
		searchEl = me.itemList.dom.firstChild;

		while (searchEl && searchEl.nextSibling) {
			if (searchEl == itemEl) {
				break;
			}
			itemIdx++;
			searchEl = searchEl.nextSibling;
		}
		itemIdx = (searchEl == itemEl) ? itemIdx : false;

		if (itemIdx === false) {
			return false;
		}

		return me.valueStore.getAt(itemIdx);
	},

	toggleSelectionByListItemNode: function(itemEl, keepExisting) {
		var me = this,
		rec = me.getRecordByListItemNode(itemEl),
		selModel = me.selectionModel;

		if (rec) {
			if (selModel.isSelected(rec)) {
				if (selModel.isFocused(rec)) {
					selModel.setLastFocused(null);
				}
				selModel.deselect(rec);
			} else {
				selModel.select(rec, keepExisting);
			}
		}
	},

	removeByListItemNode: function(itemEl) {
		var me = this,
		rec = me.getRecordByListItemNode(itemEl);

		if (rec) {
			me.valueStore.remove(rec);
			me.setValue(me.valueStore.getRange());
		}
	},

	getRawValue: function() {
		var me = this,
		inputEl = me.inputEl,
		result;
		me.inputEl = false;
		result = me.callParent(arguments);
		me.inputEl = inputEl;
		return result;
	},

	setRawValue: function(value) {
		var me = this,
		inputEl = me.inputEl,
		result;

		me.inputEl = false;
		result = me.callParent([value]);
		me.inputEl = inputEl;

		return result;
	},

	addValue: function(value) {
		var me = this;
		if (value) {
			me.setValue(Ext.Array.merge(me.value, Ext.Array.from(value)));
		}
	},

	removeValue: function(value) {
		var me = this;

		if (value) {
			me.setValue(Ext.Array.difference(me.value, Ext.Array.from(value)));
		}
	},

	setValue: function(value, doSelect, skipLoad) {
		var me = this,
		valueStore = me.valueStore,
		valueField = me.valueField,
		record, len, i, valueRecord, h,
		unknownValues = [];

		if (Ext.isEmpty(value)) {
			value = null;
		}
		if (Ext.isString(value) && me.multiSelect) {
			value = value.split(me.delimiter);
		}
		value = Ext.Array.from(value, true);

		for (i = 0, len = value.length; i < len; i++) {
			record = value[i];
			if (!record || !record.isModel) {
				valueRecord = valueStore.findExact(valueField, record);
				if (valueRecord >= 0) {
					value[i] = valueStore.getAt(valueRecord);
				} else {
					valueRecord = me.findRecord(valueField, record);
					if (!valueRecord) {
						if (me.forceSelection) {
							unknownValues.push(record);
						} else {
							valueRecord = {};
							valueRecord[me.valueField] = record;
							valueRecord[me.displayField] = record;
							valueRecord = new me.valueStore.model(valueRecord);
						}
					}
					if (valueRecord) {
						value[i] = valueRecord;
					}
				}
			}
		}

		if ((skipLoad !== true) && (unknownValues.length > 0) && (me.queryMode === 'remote')) {
			var params = {};
			params[me.valueField] = unknownValues.join(me.delimiter);
			me.store.load({
				params: params,
				callback: function() {
					if (me.itemList) {
						me.itemList.unmask();
					}
					me.setValue(value, doSelect, true);
					me.autoSize();
				}
			});
			return false;
		}

		// For single-select boxes, use the last good (formal record) value if possible
		if (!me.multiSelect && (value.length > 0)) {
			for (i = value.length - 1; i >= 0; i--) {
				if (value[i].isModel) {
					value = value[i];
					break;
				}
			}
			if (Ext.isArray(value)) {
				value = value[value.length - 1];
			}
		}

		return me.callParent([value, doSelect]);
	},

	getValueRecords: function() {
		return this.valueStore.getRange();
	},

	getSubmitData: function() {
		var me = this,
		val = me.callParent(arguments);

		if (me.multiSelect && me.encodeSubmitValue && val && val[me.name]) {
			val[me.name] = Ext.encode(val[me.name]);
		}

		return val;
	},

	mimicBlur: function() {
		var me = this;

		if (me.selectOnTab && me.picker && me.picker.highlightedItem) {
			me.inputEl.dom.value = '';
		}

		me.callParent(arguments);
	},

	assertValue: function() {
		var me = this,
		rawValue = me.inputEl.dom.value,
		rec = !Ext.isEmpty(rawValue) ? me.findRecordByDisplay(rawValue) : false,
		value = false;

		if (!rec && !me.forceSelection && me.createNewOnBlur && !Ext.isEmpty(rawValue)) {
			value = rawValue;
		} else if (rec) {
			value = rec;
		}

		if (value) {
			me.addValue(value);
		}

		me.inputEl.dom.value = '';

		me.collapse();
	},

	checkChange: function() {
		if (!this.suspendCheckChange && !this.isDestroyed) {
			var me = this,
			valueStore = me.valueStore,
			lastValue = me.lastValue,
			valueField = me.valueField,
			newValue = Ext.Array.map(Ext.Array.from(me.value), function(val) {
				if (val.isModel) {
					return val.get(valueField);
				}
				return val;
			}, this).join(this.delimiter),
			isEqual = me.isEqual(newValue, lastValue);

			if (!isEqual || ((newValue.length > 0 && valueStore.getCount() < newValue.length))) {
				valueStore.suspendEvents();
				valueStore.removeAll();
				if (Ext.isArray(me.valueModels)) {
					valueStore.add(me.valueModels);
				}
				valueStore.resumeEvents();
				valueStore.fireEvent('datachanged', valueStore);

				if (!isEqual) {
					me.lastValue = newValue;
					me.fireEvent('change', me, newValue, lastValue);
					me.onChange(newValue, lastValue);
				}
			}
		}
	},

	isEqual: function(v1, v2) {
		var fromArray = Ext.Array.from,
			valueField = this.valueField,
			i, len, t1, t2;

		v1 = fromArray(v1);
		v2 = fromArray(v2);
		len = v1.length;

		if (len !== v2.length) {
			return false;
		}

		for(i = 0; i < len; i++) {
			t1 = v1[i].isModel ? v1[i].get(valueField) : v1[i];
			t2 = v2[i].isModel ? v2[i].get(valueField) : v2[i];
			if (t1 !== t2) {
				return false;
			}
		}

		return true;
	},

	applyEmptyText : function() {
		var me = this,
		emptyText = me.emptyText,
		inputEl, isEmpty;

		if (me.rendered && emptyText) {
			isEmpty = Ext.isEmpty(me.value) && !me.hasFocus;
			inputEl = me.inputEl;
			if (isEmpty) {
				inputEl.dom.value = emptyText;
				inputEl.addCls(me.emptyCls);
				me.listWrapper.addCls(me.emptyCls);
			} else {
				if (inputEl.dom.value === emptyText) {
					inputEl.dom.value = '';
				}
				me.listWrapper.removeCls(me.emptyCls);
				inputEl.removeCls(me.emptyCls);
			}
			me.autoSize();
		}
	},

	preFocus : function(){
		var me = this,
		inputEl = me.inputEl,
		emptyText = me.emptyText,
		isEmpty;

		if (emptyText && inputEl.dom.value === emptyText) {
			inputEl.dom.value = '';
			isEmpty = true;
			inputEl.removeCls(me.emptyCls);
			me.listWrapper.removeCls(me.emptyCls);
		}
		if (me.selectOnFocus || isEmpty) {
			inputEl.dom.select();
		}
	},

	onFocus: function() {
		var me = this,
		focusCls = me.focusCls,
		itemList = me.itemList;

		if (focusCls && itemList) {
			itemList.addCls(focusCls);
		}

		me.callParent(arguments);
	},

	onBlur: function() {
		var me = this,
		focusCls = me.focusCls,
		itemList = me.itemList;

		if (focusCls && itemList) {
			itemList.removeCls(focusCls);
		}

		me.callParent(arguments);
	},

	renderActiveError: function() {
		var me = this,
		invalidCls = me.invalidCls,
		itemList = me.itemList,
		hasError = me.hasActiveError();

		if (invalidCls && itemList) {
			itemList[hasError ? 'addCls' : 'removeCls'](me.invalidCls + '-field');
		}

		me.callParent(arguments);
	},

	autoSize: function() {
		var me = this,
		height;

		if (me.grow && me.rendered) {
			me.autoSizing = true;
			me.updateLayout();
		}

		return me;
	},

	afterComponentLayout: function() {
		var me = this,
			height;

		if (me.autoSizing) {
			height = me.getHeight();
			if (height !== me.lastInputHeight) {
				if (me.isExpanded) {
					me.alignPicker();
				}
				me.fireEvent('autosize', me, height);
				me.lastInputHeight = height;
				delete me.autoSizing;
			}
		}
	}
});

Ext.define('Ext.ux.layout.component.field.BoxSelectField', {
	/* Begin Definitions */
	alias: ['layout.boxselectfield'],
	extend: 'Ext.layout.component.field.Trigger',

	/* End Definitions */

	type: 'boxselectfield',

	/*For proper calculations we need our field to be sized.*/
	waitForOuterWidthInDom:true,

	beginLayout: function(ownerContext) {
		var me = this,
			owner = me.owner;

		me.callParent(arguments);

		ownerContext.inputElCtContext = ownerContext.getEl('inputElCt');
		owner.inputElCt.setStyle('width',0);

		me.skipInputGrowth = !owner.grow || !owner.multiSelect;
	},

	beginLayoutFixed: function(ownerContext, width, suffix) {
		var me = this,
			owner = ownerContext.target;

		owner.triggerEl.setStyle('height', '22px');

		me.callParent(arguments);

		if (ownerContext.heightModel.fixed && ownerContext.lastBox) {
			owner.listWrapper.setStyle('height', ownerContext.lastBox.height+'px');
			owner.itemList.setStyle('height', '100%');
		}
		/*No inputElCt calculations here!*/
	},

	/*Calculate and cache value of input container.*/
	publishInnerWidth:function(ownerContext) {
		var me = this,
			owner = me.owner,
			width = owner.itemList.getWidth(true) - 10,
			lastEntry = owner.inputElCt.prev(null, true);

		if (lastEntry && !owner.stacked) {
			lastEntry = Ext.fly(lastEntry);
			width = width - lastEntry.getOffsetsTo(lastEntry.up(''))[0] - lastEntry.getWidth();
		}

		if (!me.skipInputGrowth && (width < 35)) {
			width = width - 10;
		} else if (width < 1) {
			width = 1;
		}
		ownerContext.inputElCtContext.setWidth(width);
	}
});