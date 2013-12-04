Ext.define('Scalr.ui.ConfigField',{
	extend: 'Ext.form.FieldContainer',	
	mixins: {
		field: 'Ext.form.field.Field'
	},
	alias: 'widget.configfield',

	layout: {
		type: 'hbox'
	},
	hideLabel: true,
	
	params: {},
	
	submitValue: false,
	readOnly: false,

	defaults: {
		submitValue: false
	},
	items: [{
		xtype: 'textfield',
		itemId: 'key',
		emptyText: 'Name',
		width: 300,
		listeners: {
			blur: function(){
				this.up().validate();
			}
		}
	}, {
		xtype: 'textfield',
		itemId: 'value',
		emptyText: 'Value',
		width: 400,
		margin: '0 0 0 5'
	}, {
		xtype: 'displayfield',
		value: '<img src="/ui2/images/icons/remove_icon_16x16.png" style="cursor: pointer">',
		width: 20,
		margin: '0 0 0 5',
		disabled: true,
		hidden: true,
		itemId: 'remove',
		listeners: {
			afterrender: function () {
				Ext.get(this.el.down('img')).on('click', function () {
					var val = this.up('configfield').getValue();
					if (val && val.key != '') {
						val.value = '*unset*';
						this.up('configfield').setValue(val);
					} else {
						this.up('configfield').up().remove(this.up('configfield'));
					}
				}, this);
			}
		}
	}],

	plugins: {
		ptype: 'addfield',
		handler: function () {
			this.getPlugin('addfield').hide();
			this.addNewConfigfield();
		}
	},
	
	markInvalid: function (msg) {
		Ext.each(this.items.getRange(), function (item) {
			if(item.xtype != 'displayfield'){
				item.markInvalid(msg);
			}
		});
	},
	
	validate: function(){
		return true;
	},
	onBoxReady: function () {
		this.callParent();
		this.suspendLayout = true;
		this.setValue(this.value);
		this.setReadOnly(this.readOnly);
		this.suspendLayout = false;
		if(this.showRemoveButton)
			this.plugins[0].hide();
		
		if (this.notEditable)
			this.down('#key').setReadOnly(true);
	},

	addNewConfigfield: function (){
		if(this.up()) {
			this.up().add({
				xtype: 'configfield',
				configFile: this.configFile
			});
			this.down('#remove').enable().show();
		}
	},

	setReadOnly: function(readOnly) {
		if(readOnly){
			this.down('#remove').disable().hide();
			Ext.each(this.items.getRange(), function(item) {
				item.setReadOnly(readOnly);
			});
		}
		else{
			if(this.showRemoveButton)
				this.down('#remove').enable().show();
		}
	},

	isEmpty: function(){
		return this.down('#value').getValue() && this.down('#name').getValue() ? false : true;
	},

	setValue: function (value) {
		if (Ext.isObject(value)) {
			Ext.each(this.items.getRange(), function(item) {
				if(value[item.itemId])
					item.setValue(value[item.itemId]);
			});
		}
	},

	getValue: function(){
		var vals = this.params;
		this.items.each(function (item) {
			vals[item.itemId] = item.getValue();
		});

		var values = { key: vals['key'], value: vals['value'], configFile: this.configFile};
		if (values.key != '') {
			return values;
		} else {
			return null;
		}
	},

	clearStatus: function () {
		this.down('#remove').enable().show();
	},

	getName: function () {
		return this.id;
	}
});

/*
 * Add-on mask plugin
 *//*
 Ext.define('Scalr.ui.AddonMaskPlugin', {
 extend: 'Ext.AbstractPlugin',
 alias: 'plugin.addonmask',

 init: function (client) {
 var me = this;
 client.on('afterrender', function(){
 var panelContainer = Ext.DomHelper.insertFirst(client.el, {id:'addonmask-div'}, true);
 me.addonmask = Ext.DomHelper.append (panelContainer,
 '<div class="scalr-ui-add-new-panel" style="position: absolute;' + (client.getHeight() ? 'height: ' + client.getHeight() + 'px;' : '') + '">' +
 '<div class="scalr-ui-add-new-mask"></div>' +
 '<div class="scalr-ui-add-new-plus"></div>' +
 '</div>'
 , true);
 me.addonmask.on('click', me.handler, client);
 }, client);
 },
 show: function () {
 if(this.addonmask)
 this.addonmask.show();
 },
 hide: function () {
 if(this.addonmask)
 this.addonmask.hide();
 }
 });
 */