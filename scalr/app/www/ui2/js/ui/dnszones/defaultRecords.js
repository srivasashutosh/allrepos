Scalr.regPage('Scalr.ui.dnszones.defaultRecords', function (loadParams, moduleParams) {
	var records = moduleParams.records;
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		title: 'Default DNS records',
		plugins: [{
			ptype: 'panelscrollfix'
		}],
		items: [{
			xtype: 'displayfield',
			fieldCls: 'x-form-field-info',
			anchor: '100%',
			value: 'Default DNS records will be automatically added to all your <b>new</b> DNS Zones - If you want to edit existing zone, you should go to Websites -> DNS Zones and choose the Edit DNS zone option. You can use the %hostname% tag, which will be replaced with full zone hostname.'
		},{
			xtype: 'fieldset',
			title: 'DNS records',
			itemId: 'dnsRecords'
		}],
		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			cls: 'x-docked-bottom-frame',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				xtype: 'button',
				text: 'Save',
				handler: function() {
					if (form.getForm().isValid()) {
						var results = {};
						form.child('#dnsRecords').items.each(function (item) {
							if (item.isEmpty())
								form.child('#dnsRecords').remove(item);
							else{
								results[item.getName()] = item.getValue();
								item.clearStatus();
							}
						});
						Scalr.Request({
							processBox: {
								type: 'save'
							},
							form: form.getForm(),
							url: '/dnszones/xSaveDefaultRecords/',
							scope: this,
							params: {
								records: Ext.encode(results)
							},
							success: function () {
								Scalr.event.fireEvent('close');
							},
							failure: function() {
								this.up('form').down('#dnsRecords').add({
									xtype: 'dnsfield',
									showAddButton: true
								});
							}
						});
					}
				}
			}, {
				xtype: 'button',
				margin: '0 0 0 5',
				text: 'Cancel',
				handler: function() {
					Scalr.event.fireEvent('close');
				}
			}]
		}]
	});
	Ext.each(records, function(item) {
		form.down('#dnsRecords').add({
			showRemoveButton: true,
			xtype: 'dnsfield',
			value: item,
			zone: '',
			readOnly: false
		});
	});
	form.down('#dnsRecords').add({
		xtype: 'dnsfield',
		zone: '',
		showAddButton: true
	});
	return form;
});
