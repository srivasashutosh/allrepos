Scalr.regPage('Scalr.ui.farms.events.configure', function (loadParams, moduleParams) {
	function fillObserver(observer, name) {
		Ext.each (observer, function(observerItem){
			switch (observerItem.FieldType){
				case 'checkbox':
					type = 'checkbox';
					break;
				case 'text':
					type = 'textfield';
					break;
				case 'separator':
					type = 'displayfield';
					break;
			}
			if(observerItem.Name != 'IsEnabled'){
				form.down(name).add({
					xtype: 'fieldcontainer',
					layout: {
						type: 'hbox'
					},
					defaults: {
						labelWidth: 200
					},
					items: [{
						xtype: type,
						fieldLabel: observerItem.Title,
						checked: observerItem.Value,
						name: observerItem.Name,
						value: observerItem.Value,
						flex: 1
					},
					observerItem.Hint ? 
					{
						xtype: 'displayinfofield',
						margin: '0 0 0 5',
						info: observerItem.Hint
					} : {
						xtype: 'displayfield'
					}]
				});
			}
			else {
				if(!observerItem.Value)
					form.down(name).collapse();
			}
		});
	}
	var form = Ext.create('Ext.form.Panel',{
		bodyCls: 'x-panel-body-frame',
		title: 'Farms &raquo; ' + moduleParams['farmName'] + ' &raquo; Events &raquo; Configure Notifications ',
		width: 710,
		items: [{
			xtype: 'fieldset',
			title: 'RSS feed settings',
			items: [{
				xtype: 'displayfield',
				fieldLabel: 'RSS feed URL',
				labelWidth: 200,
				value: '<img src="/ui2/images/icons/feed_icon_14x14.png" style="float: left;">&nbsp;<a href = "https://my.scalr.net/storage/events/' + loadParams['farmId'] + '/rss.xml">https://my.scalr.net/storage/events/' + loadParams['farmId'] + '/rss.xml</a>'
			},{
				xtype: 'displayfield',
				fieldCls: 'x-form-field-warning',
				value: 'Your RSS reader must support basic HTTP authentication. The login and password for RSS feeds can be found in <a href = "#/core/settings">Settings -> System settings</a>'
			}]
		},{
			xtype: 'fieldset',
			title: 'MailEventObserver observer settings',
			checkboxToggle: true,
			checkboxName: 'MailEventObserverEnabled',
			itemId: 'MailEventObserver'
		},{
			xtype: 'fieldset',
			title: 'RESTEventObserver observer settings',
			checkboxToggle: true,
			checkboxName: 'RESTEventObserverEnabled',
			itemId: 'RESTEventObserver'
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
					var restStore = new Ext.create(Ext.data.Store,{
						fields: ['name', 'value']
					});
					var mailStore = new Ext.create(Ext.data.Store,{
						fields: ['name', 'value']
					});
					var rest = [];
					var mail = [];
					
					Ext.each (form.down('#RESTEventObserver').items.items, function(restItem) {
						if(restItem.items.getAt(0).xtype != 'displayfield')
							restStore.add({'name': restItem.items.getAt(0).name, 'value': restItem.items.getAt(0).value});
					});

					Ext.each (form.down('#MailEventObserver').items.items, function(mailItem) {
						if(mailItem.items.getAt(0).xtype != 'displayfield')
							mailStore.add({'name': mailItem.items.getAt(0).name, 'value': mailItem.items.getAt(0).value});
					});

					Ext.each (mailStore.getRange(), function (item) {
						mail.push(item.data);
					});
					Ext.each (restStore.getRange(), function (item) {
						rest.push(item.data);
					});
					Scalr.Request({
						processBox: {
							type: 'save'
						},
						form: form.getForm(),
						url: '/farms/events/xSaveNotifications',
						params: Ext.applyIf(loadParams, {'MailEventObserver': Ext.encode(mail), 'RESTEventObserver': Ext.encode(rest)}),
						success: function (data) {
							Scalr.event.fireEvent('close');
						}
					});
				}
			},{
				xtype: 'button',
				margin: '0 0 0 5',
				text: 'Cancel',
				handler: function() {
					Scalr.event.fireEvent('close');
				}
			}]
		}]
	});
	fillObserver(moduleParams.form['MailEventObserver'], '#MailEventObserver');
	fillObserver(moduleParams.form['RESTEventObserver'], '#RESTEventObserver');
	return form;
});