Ext.define('Scalr.ui.DnsZonesField',{
	extend: 'Ext.form.FieldContainer',	
	mixins: {
		field: 'Ext.form.field.Field'
	},
	alias: 'widget.dnsfield',

	layout: {
		type: 'hbox'
	},
	hideLabel: true,
	
	params: {},
	showRemoveButton: false,
	
	submitValue: false,
	readOnly: false,

	defaults: {
		submitValue: false
	},
	items: [{
		xtype: 'textfield',
		itemId: 'name',
		emptyText: 'Domain',
		width: 260,
		listeners: {
			blur: function(){
				this.up().validate();
			}
		}
	}, {
		xtype: 'textfield',
		itemId: 'ttl',
		emptyText: 'TTL',
		width: 60,
		margin: '0 0 0 5'
	}, {
		xtype: 'combo',
		width: 80,
		listWidth: 80,
		store: [ 'A', 'CNAME', 'MX', 'TXT', 'NS', 'SRV'],
		editable: false,
		emptyText: 'Type',
		itemId: 'type',
		margin: '0 0 0 5',
		listeners: {
			change: function () {
				var field = this.up('fieldcontainer'), value = this.getValue();
				field.child('#port').hide();
				field.child('#weight').hide();
				field.child('#priority').hide();

				if (value == 'MX' || value == 'SRV')
					field.child('#priority').show();

				if (value == 'SRV') {
					field.child('#weight').show();
					field.child('#port').show();
				}
				this.up().validate();
			}
		}
	}, {
		xtype: 'textfield',
		itemId: 'priority',
		emptyText: 'priority',
		hidden: true,
		flex: 1,
		margin: '0 0 0 5'
	}, {
		xtype: 'textfield' ,
		itemId: 'weight',
		emptyText: 'weight',
		hidden: true,
		flex: 1,
		margin: '0 0 0 5'
	}, {
		xtype: 'textfield',
		itemId: 'port',
		emptyText: 'port',
		hidden: true,
		flex: 1,
		margin: '0 0 0 5'
	}, {
		xtype: 'textfield',
		itemId: 'value',
		emptyText: 'Record value',
		flex: 4,
		margin: '0 0 0 5',
		listeners: {
			blur: function() {
				this.up().validate();
			}
		}
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
					var panel = this.up('panel');
					if (panel.fixScrollTop) {
						panel.fixScrollTop();
					}
					this.up('dnsfield').up().remove(this.up('dnsfield'));
				}, this);
			}
		}
	}],

	plugins: {
		ptype: 'addfield',
		handler: function () {
			var panel = this.up('panel');
			if (panel.fixScrollTop) {
				panel.fixScrollTop();
			}
			panel.suspendLayouts();
			
			this.getPlugin('addfield').hide();
			this.addNewDnsfield();
			
			panel.resumeLayouts(true);
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
		var uniq = {};
		var me = this;
		var valid = true;
		Ext.each(me.up().items.getRange(), function (item) {
			var rec = item.getValue();
			if(rec){
				var n = (rec.name == '' || rec.name == '@') ? item.zone + '.' : rec.name;
	
				if (rec.type == 'CNAME') {
					if (uniq[n] != undefined)
						uniq[n] = 'conflict';
					else
						uniq[n] = 'cname';
				} else {
					if (uniq[n] == 'cname' || uniq[n] == 'conflict')
						uniq[n] = 'conflict';
					else
						uniq[n] = 'exist';
				}
			}
		});
		for (var name in uniq) {
			if (uniq[name] == 'conflict') {
				valid = false;
				Ext.each(me.up().items.getRange(), function (item) {
					var rec = item.getValue();
					if(rec){
						if (rec.name == name || (rec.name == '' || rec.name == '@') && (name == item.zone + '.'))
							item.child('#name').markInvalid('Conflict name ' + name);
					}
				});
			}
		}
		return valid;
	},
	onBoxReady: function () {
		this.callParent();
		this.suspendLayout = true;
		this.setValue(this.value);
		this.setReadOnly(this.readOnly);
		this.suspendLayout = false;

		if (this.showRemoveButton)
			this.getPlugin('addfield').hide();
	},

	addNewDnsfield: function (){
		if(this.up()) {
			this.up().add({
				xtype: 'dnsfield',
				zone: this.zone
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
			this.params = {issystem: value.issystem};
		} else {
			this.child('#type').setValue('A');
			this.params = {issystem: 0};
		}
	},

	getValue: function(){
		var vals = this.params;
		this.items.each(function (item) {
			vals[item.itemId] = item.getValue();
		});

		var values = {name: vals['name'], value: vals['value'], issystem: vals['issystem']};
		if (values.value != '') {
			values['ttl'] = vals['ttl'];
			values['type'] = vals['type'];

			if (values['type'] == 'MX' || values['type'] == 'SRV')
				values['priority'] = vals['priority'];

			if (values['type'] == 'SRV') {
				values['weight'] = vals['weight'];
				values['port'] = vals['port'];
			}

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