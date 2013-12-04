Scalr.regPage('Scalr.ui.tools.aws.s3.distribution', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		title: 'Create new Distribution',
		width: 650,
		bodyCls: 'x-panel-body-frame',
		scalrOptions: {
			'modal': true
		},
		items: [{
			xtype: 'hidden',
			name: 'bucketName',
			value: loadParams['bucketName']
		},{
			xtype: 'fieldset',
			title: 'Distribution information',
			defaults: {
				labelWidth: 145
			},
			items: [{
				xtype: 'displayfield',
				fieldLabel: 'S3 Bucket',
				value: loadParams['bucketName']
			},{
				xtype: 'textarea',
				fieldLabel: 'Comment',
				name: 'comment',
				width: 565
			}]
		},{
			xtype: 'fieldset',
			title: 'Domain Name',
			items: [{
				xtype: 'fieldcontainer',
				layout: {
					type: 'hbox'
				},
				items: [{
					xtype: 'radiofield',
					labelWidth: 130,
					name: 'domain',
					fieldLabel: 'Local domain name',
					checked: true,
					margin: '0 2 0 0',
					listeners: {
						change: function(field, newValue, oldValue, opts){
							if(newValue)
							{
								field.next('#localDomain').enable();
								field.next('#comboZone').enable();
							}
							else{
								field.next('#localDomain').disable();
								field.next('#comboZone').disable();
							}
						}
					}
				},{
					xtype: 'textfield',
					itemId: 'localDomain',
					name: 'localDomain'
				},{
					xtype: 'displayfield',
					value: '.',
					margin: '0 2 0 2'
				},{
					xtype: 'combo',
					name: 'zone',
					itemId: 'comboZone',
					width: 303,
					editable: false,
					allowBlank: false,
					name: 'zone',
					store: {
						fields: ['zone_name'],
						proxy: {
							type: 'ajax',
							reader: {
								type: 'json',
								root: 'data'
							},
							url: '/tools/aws/s3/xListZones'
						}
					},
					valueField: 'zone_name',
					displayField: 'zone_name'
				}]
			},{
				xtype: 'fieldcontainer',
				layout: {
					type: 'hbox'
				},
				items: [{
					xtype: 'radiofield',
					name: 'domain',
					labelWidth: 130,
					fieldLabel: 'Remote domain name',
					margin: '0 2 0 0',
					listeners: {
						change: function(field, newValue, oldValue, opts){
							if(newValue)
								field.next('#remoteDomain').enable();
							else
								field.next('#remoteDomain').disable();
						}
					}
				},{
					xtype: 'textfield',
					itemId: 'remoteDomain',
					name: 'remoteDomain',
					disabled: true,
					width: 460
				}]
			}]
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
					Scalr.Request({
						processBox: {
							msg: 'Adding new distribution',
							type: 'save'
						},
						scope: this,
						url: '/tools/aws/s3/xCreateDistribution',
						form: form.getForm(),
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
	return form;
});