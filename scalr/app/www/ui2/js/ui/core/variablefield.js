Ext.define('Scalr.ui.VariableField', {
	extend: 'Ext.container.Container',
	mixins: {
		field: 'Ext.form.field.Field'
	},
	alias: 'widget.variablefield',

	currentScope: 'env',

	initComponent : function() {
		var me = this;
		me.callParent();
		me.initField();
	},

	getValue: function() {
		var fields = this.query('variablevaluefield'), variables = [];
		for (var i = 0; i < fields.length; i++) {
			var values = fields[i].getFieldValues();
			Ext.applyIf(values, fields[i].originalValues || {});
			if (values['newValue'] == 'true') {
				if (! (values['name'] && fields[i].down('[name="newName"]').isValid()))
					continue;
			}
			delete values['newValue'];
			delete values['newName'];
			if (values['name'])
				variables.push(values);
		}

		return Ext.encode(variables);
	},

	setValue: function(value) {
		value = Ext.decode(value, true) || {};
		var ct = this.down('#ct'), currentScope = this.currentScope;

		ct.suspendLayouts();
		ct.removeAll();

		if (value.length < 10)
			this.down('filterfield').hide();
		else
			this.down('filterfield').show();

		for (var i in value) {
			var f = ct.add({
				currentScope: currentScope,
				xtype: 'variablevaluefield',
				originalValues: value[i]
			});
			value[i]['newValue'] = false;
			value[i]['defaultScope'] = value[i]['defaultScope'] || currentScope;
			f.setFieldValues(value[i]);

			f.down('[name="value"]').emptyText = value[i]['defaultValue'] || '';

			if (value[i]['flagRequiredGlobal'] == 1) {
				if (! f.down('[name="value"]').emptyText)
					f.down('[name="value"]').allowBlank = false;
				f.down('[name="value"]').isValid();
				f.down('[name="flagRequired"]').setValue(1);
				f.down('[name="flagRequired"]').disable();
			}

			if (value[i]['flagFinalGlobal'] == 1) {
				f.down('[name="value"]').setReadOnly(true);
				f.down('[name="value"]').setDisabled(true);
				f.down('[name="flagFinal"]').disable();
				f.down('[name="flagFinal"]').setValue(1);
				f.down('[name="flagRequired"]').disable();

				if (value[i]['scope'] != currentScope)
					f.down('#delete').disable();
			}

			// not to change required and final for high-scope variables or for last scope (farmrole)
			if (value[i]['defaultScope'] != currentScope || currentScope == 'farmrole') {
				f.down('[name="flagFinal"]').disable();
				f.down('[name="flagRequired"]').disable();
			}

			// not to remove variables from higher scopes
			if (value[i]['defaultScope'] != currentScope)
				f.down('#delete').disable();

			if (value[i]['flagDelete'] == 1)
				f.hide();
		}

		var handler = function() {
			// check, if last new variable was filled
			var items = ct.items.items, names = [];
			for (var i = 0; i < items.length; i++) {
				if (items[i].xtype == 'variablevaluefield') {
					if (items[i].down('[name="newValue"]').getValue() == 'true') {
						var f = items[i].down('[name="newName"]');
						if (f.isValid()) {
							if (! f.getValue()) {
								f.markInvalid('Name is required');
								return;
							}

							items[i].down('[name="newValue"]').setValue(false);
						} else {
							return;
						}
					}
					names.push(items[i].down('[name="name"]').getValue());
				}
			}

			this.getPlugin('addfield').hide();
			ct.suspendLayouts();
			var f = ct.add({
				xtype: 'variablevaluefield',
				currentScope: currentScope,
				plugins: {
					ptype: 'addfield',
					handler: handler
				}
			});
			f.setFieldValues({
				defaultScope: currentScope,
				scope: currentScope,
				newValue: true
			});
			f.down('[name="newName"]').validatorNames = names;
			if (currentScope == 'farmrole') {
				f.down('[name="flagFinal"]').disable();
				f.down('[name="flagRequired"]').disable();
			}
			ct.resumeLayouts(true);
		};

		var f = ct.add({
			xtype: 'variablevaluefield',
			currentScope: currentScope,
			plugins: {
				ptype: 'addfield',
				handler: handler
			}
		});
		f.setFieldValues({
			defaultScope: currentScope,
			scope: currentScope,
			newValue: true
		});
		if (currentScope == 'farmrole') {
			f.down('[name="flagFinal"]').disable();
			f.down('[name="flagRequired"]').disable();
		}

		ct.resumeLayouts(true);
	},

	layout: {
		type: 'hbox',
		align: 'top'
	},
	items: [{
		xtype: 'container',
		flex: 1,
		items: [{
			xtype: 'filterfield',
			width: 176,
			handler: function(field, value) {
				this.up('variablefield').down('#ct').items.each(function() {
					var f = this.child('[name="name"]');
					if (f.isVisible() && f.getValue().indexOf(value) == -1)
						f.addCls('x-form-display-field-mark');
					else
						f.removeCls('x-form-display-field-mark');
				});
			}
		}, {
			xtype: 'container',
            layout: 'hbox',
            margin: '0 0 8 0',
            defaults: {
                style: 'font-weight:bold;text-shadow: 0 1px #fff;'
            },
            items: [{
                xtype: 'label',
                text: 'Scope',
                width: 52
            },{
                xtype: 'label',
                text: 'Name',
                width: 154
            },{
                xtype: 'label',
                text: 'Value',
                flex: 1
            },{
                xtype: 'label',
                text: 'Options',
                width: 75
            }]
		}, {
			xtype: 'container',
			itemId: 'ct',
			layout: 'anchor'
		}]
	}, {
		xtype: 'fieldset',
		width: 300,
		collapsible: true,
		collapsed: true,
		margin: '0 0 0 32',
		title: '<img src="/ui2/images/icons/info_icon_16x16.png" style="cursor: help; height: 16px; line-height: 20px;"> Usage',
		items: [{
			xtype: 'displayfield',
			value: '<span style="font-weight: bold">Scope:</span>'
		}, {
			xtype: 'displayfield',
			cls: 'scalr-ui-variablefield-scope-env',
			value: '<div class="icon"></div> Environment'
		}, {
			xtype: 'displayfield',
			cls: 'scalr-ui-variablefield-scope-role',
			value: '<div class="icon"></div> Role'
		}, {
			xtype: 'displayfield',
			cls: 'scalr-ui-variablefield-scope-farm',
			value: '<div class="icon"></div> Farm'
		}, {
			xtype: 'displayfield',
			cls: 'scalr-ui-variablefield-scope-farmrole',
			value: '<div class="icon"></div> FarmRole'
		}, {
			xtype: 'component',
			cls: 'x-fieldset-delimiter'
		}, {
			xtype: 'displayfield',
			value: '<span style="font-weight: bold">Types:</span>'
		}, {
			xtype: 'displayfield',
			cls: 'scalr-ui-variablefield scalr-ui-variablefield-flag-required',
			value: '<div class="x-btn-inner"></div> Shall be set on a lower level'
		}, {
			xtype: 'displayfield',
			cls: 'scalr-ui-variablefield scalr-ui-variablefield-flag-final',
			value: '<div class="x-btn-inner"></div> Cannot be changed on a lower level'
		}, {
			xtype: 'component',
			cls: 'x-fieldset-delimiter'
		}, {
			xtype: 'displayfield',
			value: '<span style="font-weight: bold">Description:</span>'
		}, {
			xtype: 'displayfield',
			value: 'You can access those variables:<br />'+
			'&bull; As OS environment variables when you\'re executing script<br />'+
			'&bull; CLI command: <i>szradm -q list-global-variables</i><br />'+
			'&bull; GlobalVariablesList(ServerID) API call<br />'
		}]
	}]
});

Ext.define('Scalr.ui.VariableValueField', {
	extend: 'Ext.form.FieldContainer',
	alias: 'widget.variablevaluefield',
	layout: 'hbox',
	hideLabel: true,
	plugins: [],

	items: [{
		xtype: 'hidden',
		name: 'newValue',
		submitValue: false,
		listeners: {
			change: function(field, value) {
				var me = this.up('variablevaluefield'), flagNew = value == 'true';
				me.suspendLayouts();
				me.down('[name="newName"]').setVisible(flagNew);
				me.down('[name="newName"]').setDisabled(!flagNew);
				me.down('[name="name"]').setVisible(!flagNew);
				me.down('#delete').setDisabled(flagNew);
				me.resumeLayouts(true);
			}
		}
	}, {
		xtype: 'displayfield',
		name: 'scope',
        width: 46,
		fieldSubTpl: '<div class="icon"><div id="{id}" style="display: none"></div></div>',
		updateTitle: function(value) {
			var names = {
				env: 'Environment',
				role: 'Role',
				farm: 'Farm',
				farmrole: 'FarmRole'
			};
			this.bodyEl.down('div.icon').set({ title: names[value] || value });
		},
		listeners: {
			change: function(field, value, prev) {
				this.removeCls('scalr-ui-variablefield-scope-' + prev);
				this.addCls('scalr-ui-variablefield-scope-' + value);

				if (this.rendered)
					this.updateTitle(value);
			},
			afterrender: function() {
				this.updateTitle(this.getValue());
			}
		}
	}, {
		xtype: 'hidden',
		name: 'defaultScope',
		submitValue: false
	}, {
		xtype: 'displayfield',
		name: 'name',
		fieldCls: 'x-form-display-field x-form-display-field-as-label',
		margin: '0 0 0 5',
		width: 150
	}, {
		xtype: 'textfield',
		name: 'newName',
		fieldCls: 'x-form-field',
		submitValue: false,
		allowChangeable: false,
		allowChangeableMsg: 'Variable name, cannot be changed',
		margin: '0 0 0 5',
		width: 150,
		validatorNames: [],
		validator: function(value) {
			if (! value)
				return true;
			if (/^[A-Za-z]{1,1}[A-Za-z0-9_]{1,49}$/.test(value)) {
				if (this.validatorNames.indexOf(value) == -1)
					return true;
				else
					return 'Such name already defined';
			} else
				return 'Name should contain only alpha and numbers. Length should be from 2 chars to 50.';
		},
		listeners: {
			blur: function() {
				this.prev().setValue(this.getValue());
			}
		}
	}, {
		xtype: 'container',
		flex: 1,
		layout: 'fit',
		margin: '0 0 0 5',
		items: {
			xtype: 'textarea',
			name: 'value',
			submitValue: false,
			enableKeyEvents: true,
			height: 22,
			resizable: {
				handles: 's',
				heightIncrement: 22,
				pinned: true
			},

			mode: null,
			
			setMode: function(mode, resize) {
				if (mode !== this.mode) {
					if (mode == 'multi') {
						this.mode = 'multi';
						this.toggleWrap('off');
						this.inputEl.setStyle('overflow', 'auto');
						if (resize) {
							this.setHeight(66);
						}
					} else {
						this.mode = 'single';
						this.inputEl.setStyle('overflow', 'hidden');
						this.toggleWrap(null);
					}
				}
			},
			
			toggleWrap: function(wrap) {
				if (!wrap) {
					wrap = this.inputEl.getAttribute('wrap') == 'off' ? null : 'off';
				}
				this.inputEl.set({wrap: wrap});
			},
			
			listeners: {
				keyup: function(comp, e) {
					if (e.getKey() === e.ENTER) {
						this.setMode('multi', true);
					}
				},

				focus: function() {
					var c = this.up('container');
					c.prev('[name="scope"]').setValue(c.up('variablevaluefield').currentScope);
				},
				
				blur: function() {
					var c = this.up('container');
					if (this.isDirty()) {
						c.prev('[name="scope"]').setValue(c.up('variablevaluefield').currentScope);
					} else {
						c.prev('[name="scope"]').setValue(c.prev('[name="defaultScope"]').getValue());
					}
				},
				
				resize: function(comp, width, height) {
					comp.el.parent().setSize(comp.el.getSize());//fix resize wrapper for flex element
					comp.setMode(comp.inputEl.getHeight() > 22 ? 'multi' : 'single', false);
				},
				
				boxready: function(comp) {
					comp.el.next().addCls('x-resizable-handle-custom');//custom resizable handle style
					this.setMode(this.getValue().match(/\n/g) ? 'multi' : 'single', true);
				}
			}
			
		}
	}, {
		xtype: 'btnfield',
		cls: 'scalr-ui-variablefield scalr-ui-variablefield-flag-final',
		baseCls: 'x-btn-base-image-background',
		margin: '0 0 0 5',
		name: 'flagFinal',
		tooltip: 'Cannot be changed on a lower level',
		inputValue: 1,
		enableToggle: true,
		submitValue: false
	}, {
		xtype: 'btnfield',
		cls: 'scalr-ui-variablefield scalr-ui-variablefield-flag-required',
		baseCls: 'x-btn-base-image-background',
		margin: '0 0 0 5',
		name: 'flagRequired',
		tooltip: 'Shall be set on a lower level',
		inputValue: 1,
		enableToggle: true,
		submitValue: false
	}, {
		xtype: 'btn',
		itemId: 'delete',
		margin: '0 0 0 8',
		cls: 'scalr-ui-btn-delete',
		baseCls: 'x-btn-base',
		handler: function() {
			this.up('variablevaluefield').hide();
			this.next().setValue(1);
		}
	}, {
		xtype: 'hidden',
		name: 'flagDelete',
		submitValue: false,
		value: ''
	}]
});
