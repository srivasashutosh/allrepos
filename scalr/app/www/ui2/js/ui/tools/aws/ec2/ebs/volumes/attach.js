Scalr.regPage('Scalr.ui.tools.aws.ec2.ebs.volumes.attach', function (loadParams, moduleParams) {
	return Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 700,
		title: 'Tools &raquo; Amazon Web Services &raquo; EBS &raquo; Volumes &raquo; ' + loadParams['volumeId'] + ' &raquo;Attach',
		fieldDefaults: {
			anchor: '100%'
		},

		items: [{
			xtype: 'fieldset',
			title: 'Attach options',
			labelWidth: 130,
			items: [{
				fieldLabel: 'Server',
				xtype: 'combo',
				name:'serverId',
				allowBlank: false,
				editable: false,
				store: {
					fields: [ 'id', 'name' ],
					data: moduleParams.servers,
					proxy: 'object'
				},
				value: '',
				displayField: 'name',
				valueField: 'id',
				queryMode: 'local',
				listeners: {
					added: function() {
						this.setValue(this.store.getAt(0).get('id'));
					}
				}
			}]
		}, {
			xtype: 'fieldset',
			title: 'Always attach this volume to selected server',
			collapsed: true,
			checkboxName: 'attachOnBoot',
			checkboxToggle: true,
			labelWidth: 100,
			items: [{
				xtype: 'fieldcontainer',
				hideLabel: true,
				layout: 'hbox',
				items: [{
					xtype:'checkbox',
					name:'mount',
					inputValue: 1,
					checked: false
				}, {
					xtype:'displayfield',
					margin: '0 0 0 3',
					value:'Automatically mount this volume after attach to '
				}, {
					xtype:'textfield',
					name:'mountPoint',
					margin: '0 0 0 3',
					value:'/mnt/storage',
					cls: 'x-form-check-wrap'
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
				text: 'Attach',
				handler: function() {
					Scalr.Request({
						processBox: {
							type: 'action',
							msg: 'Attaching ...'
						},
						form: this.up('form').getForm(),
						url: '/tools/aws/ec2/ebs/volumes/xAttach',
						params: loadParams,
						success: function () {
							Scalr.event.fireEvent('redirect',
								'#/tools/aws/ec2/ebs/volumes/' + loadParams['volumeId'] +
								'/view?cloudLocation=' + loadParams['cloudLocation']
							);
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
});
