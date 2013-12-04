Scalr.regPage('Scalr.ui.roles.migrate', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 900,
		title: 'Roles &raquo; Migration',
		items: [{
			xtype: 'combo',
			name: 'sourceLocation',
			itemId: 'sourceLocation',
			fieldLabel: 'Source location',
			labelWidth: 150,
			width: 450,
			queryMode: 'local',
			editable: false,
			store: {
				fields: [ 'name', 'location' ],
				proxy: 'object'
			}, 
			valueField: 'location',
			displayField: 'name',
		}, {
			xtype: 'combo',
			name: 'destLocation',
			itemId: 'destLocation',
			labelWidth: 150,
			width: 450,
			fieldLabel: 'Destination location',
			queryMode: 'local',
			editable: false,
			store: {
				fields: [ 'name', 'location' ],
				proxy: 'object'
			}, 
			valueField: 'location',
			displayField: 'name',
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
				text: 'Migrate',
				handler: function () {
					Scalr.Request({
						processBox: {
							type: 'save'
						},
						url: '/roles/' + loadParams['roleId'] + '/xMigrate',
						form: form.getForm(),
						success: function (data) {
							//TODO:
						}
					});
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

	var sLoc = form.down('#sourceLocation');
	sLoc.store.load({data: moduleParams['source']});
	sLoc.setValue(sLoc.store.getAt(0).get('location'));
	
	var dLoc = form.down('#destLocation');
	dLoc.store.load({data: moduleParams['destination']});
	dLoc.setValue(dLoc.store.getAt(0).get('location'));
	
	return form;
});
