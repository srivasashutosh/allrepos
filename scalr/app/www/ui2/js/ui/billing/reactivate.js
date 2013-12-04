Scalr.regPage('Scalr.ui.billing.reactivate', function (loadParams, moduleParams) {
	
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 500,
		title: 'Billing &raquo; Reactivate subscription',
		fieldDefaults: {
			anchor: '100%'
		},
		scalrOptions: {
			'modal': false
		},
		items: [{xtype:'component', html:"<b>What's going to happen?</b></br></br>"+
			"&bull;&nbsp;You'll immediately regain access to Scalr</br>"+
			"&bull;&nbsp;The card on record will be charged $"+moduleParams['billing']['productPrice']+" for the "+moduleParams['billing']['productName']+"</br>"+
			"&bull;&nbsp;Your next billing date will be set to one month from today</br></br>"+
			"The Scalr team welcomes you back!"
		}],
		tools: [{
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent('close');
			}
		}],
		
		dockedItems: [{
			xtype: 'container',
			cls: 'x-docked-bottom-frame',
			dock: 'bottom',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				xtype: 'button',
				text: 'Reactivate Subscription',
				handler: function() {
					Scalr.Request({
						processBox: {
							type: 'save'
						},
						url: '/billing/xReactivate/',
						form: this.up('form').getForm(),
						success: function () {
							Scalr.event.fireEvent('close');
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

	return form;
});
