Scalr.regPage('Scalr.ui.tools.aws.vpc.create', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		title: 'Create VPC',
		fieldDefaults: {
			anchor: '100%'
		},
		scalrOptions: {
			modal: true
		},
		width: 500,
        defaults: {
            labelWidth: 120
        },
        bodyPadding: '18 18 8',
		items: [{
            xtype: 'displayfield',
            name: 'cloudLocation',
            submitValue: true,
            fieldLabel: 'VPC region'
        },{
            xtype: 'textfield',
            name: 'cidr_block',
            fieldLabel: 'Cidr block'
        },{
            xtype: 'buttongroupfield',
            name: 'tenancy',
            fieldLabel: 'Instance tenancy',
            defaults: {
                width: 90
            },
            items: [{
                text: 'Default',
                value: 'default'
            },{
                text: 'Dedicated',
                value: 'dedicated'
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
				text: 'Create',
				handler: function() {
					if (form.getForm().isValid()) {
						Scalr.Request({
							processBox: {
								type: 'save'
							},
							params: form.getValues(),
							form: form.getForm(),
							url: '/tools/aws/vpc/xCreate',
							success: function (data) {
								if (data['vpc']) {
									Scalr.event.fireEvent('update', '/tools/aws/vpc/create', data['vpc']);
								}
								Scalr.event.fireEvent('close');
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

	form.getForm().setValues({
        cloudLocation: loadParams['cloudLocation'],
		cidr_block: '10.0.0.0/16',
		tenancy: 'default'
	});

	return form;
});
