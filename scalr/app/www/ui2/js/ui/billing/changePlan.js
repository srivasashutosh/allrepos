Scalr.regPage('Scalr.ui.billing.changePlan', function (loadParams, moduleParams) {
	var pkg = moduleParams['package'];

	var setPackage = function(item) {
		if (item.package != 'cancel' && !moduleParams['subscriptionId']) {
			form.down('#ccInfo').show();
		} else {
			form.down('#ccInfo').hide();
		}

		pkg = item.package;
	}

	var form = Ext.create('Ext.form.Panel', {
		tools: [{
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent('close');
			}
		}],
		bodyCls: 'x-panel-body-frame',
		width: !moduleParams['subscriptionId'] ? 780 : 900,
		title: 'Billing &raquo; Change Pricing Plan',

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
				text: 'Proceed',
				handler: function() {
					Scalr.Request({
						processBox: {
							type: 'save'
						},
						url: '/billing/xChangePlan/',
						params: {
							'package': pkg
						},
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

	form.add({
		xtype: 'fieldset',
		title: 'Choose your plan',
		align: 'center',
		layout: {
			type: 'hbox'
		},
		defaults: {
			xtype: 'custombutton',
			width: 138,
			height: 158,
			handler: setPackage,
			allowDepress: false,
			toggleGroup: 'scalr-ui-billing-changePlan',
			renderTpl:
				'<div class="{prefix}-wrap">' +
					'<div class="{prefix}-wrap-title"><span>Current</span></div>' +
					'<div class="{prefix}-btn" id="{id}-btnEl">' +
						'<div class="{prefix}-btn-name">{name}</div>' +
						'<div class="{prefix}-btn-icon"><img src="/ui2/images/ui/billing/plans/{icon}<tpl if="disabled">_disabled</tpl>.png"></div>' +
						'<div class="{prefix}-btn-price">{price}</div>' +
					'</div>' +
				'</div>'
		},
		items: [{
			cls: moduleParams['currentPackage'] == 'pay-as-you-go' ? 'scalr-ui-billing-changePlan-current': '',
			pressed: moduleParams['currentPackage'] == 'pay-as-you-go' ? true : false,
			hidden: false,
			package: 'pay-as-you-go',
			renderData: {
				icon: 'monopoly',
				name: 'Pay As You Go',
				price: 'From $99',
				prefix: 'scalr-ui-billing-changePlan'
			}
		}, {
            package: 'cancel',
            pressed: moduleParams['currentPackage'] == 'cancel' ? true : false,
            hidden: !moduleParams['subscriptionId'] ? true : false,
            renderData: {
                icon: 'stop_sign',
                name: 'Unsubscribe',
                prefix: 'scalr-ui-billing-changePlan'
            }
        }]
	});

	form.add({
		xtype: 'fieldset',
		itemId: 'ccInfo',
		hidden: true,
		title: 'Credit card information',
		padding: 15,
		items:[{
			xtype: 'displayfield',
			fieldCls: 'x-form-field-info',
			value: 'Your card will be pre-authorized for $1. <a href="http://en.wikipedia.org/wiki/Authorization_hold" target="_blank">What does this mean?</a>'
		}, {
			xtype: 'fieldcontainer',
			fieldLabel: 'Card number',
			heigth: 24,
			labelWidth: 80,
			layout: 'hbox',
			items: [{
				xtype: 'textfield',
				name: 'ccNumber',
				emptyText: '',
				height: 23,
				value: ''
			},
			{ xtype: 'component', height: 23, width: 37, margin: '0 0 0 5', html: '<img src="/ui2/images/ui/billing/cc_visa.png" />'},
			{ xtype: 'component', height: 23, width: 37, margin: '0 0 0 5', html: '<img src="/ui2/images/ui/billing/cc_mc.png" />'},
			{ xtype: 'component', height: 23, width: 37, margin: '0 0 0 5', html: '<img src="/ui2/images/ui/billing/cc_amex.png" />'},
			{ xtype: 'component', height: 23, width: 37, margin: '0 0 0 5', html: '<img src="/ui2/images/ui/billing/cc_discover.png" />'}
			]
		}, {
			xtype: 'fieldcontainer',
			fieldLabel: 'CVV code',
			heigth: 24,
			labelWidth: 80,
			layout: 'hbox',
			items: [{
				xtype: 'textfield',
				name: 'ccCvv',
				height: 23,
				width: 40,
				value: ''
			},
			{ xtype: 'displayfield', value:'Exp. date:', margin: '0 0 0 10' },
			{ 
				xtype: 'combo',
				name: 'ccExpMonth',
				margin: '0 0 0 5',
				hideLabel: true,
				editable: false,
				value:'01',
				store: {
					fields: [ 'name', 'description' ],
					proxy: 'object',
					data:[
						{name:'01', description:'01 - January'},
						{name:'02', description:'02 - February'},
						{name:'03', description:'03 - March'},
						{name:'04', description:'04 - April'},
						{name:'05', description:'05 - May'},
						{name:'06', description:'06 - June'},
						{name:'07', description:'07 - July'},
						{name:'08', description:'08 - August'},
						{name:'09', description:'09 - September'},
						{name:'10', description:'10 - October'},
						{name:'11', description:'11 - November'},
						{name:'12', description:'12 - December'}
					]
				},
				valueField: 'name',
				displayField: 'description',
				queryMode: 'local'
			}, { 
				xtype: 'combo',
				name: 'ccExpYear',
				margin: '0 0 0 5',
				width: 65,
				value:'2012',
				hideLabel: true,
				editable: false,
				store: {
					fields: [ 'name', 'description' ],
					proxy: 'object',
					data:[
						{name:'2011', description:'2011'},
						{name:'2012', description:'2012'},
						{name:'2013', description:'2013'},
						{name:'2014', description:'2014'},
						{name:'2015', description:'2015'},
						{name:'2016', description:'2016'},
						{name:'2017', description:'2017'},
						{name:'2018', description:'2018'},
						{name:'2019', description:'2019'},
						{name:'2020', description:'2020'},
						{name:'2021', description:'2021'},
						{name:'2022', description:'2022'}
					]
				},
				valueField: 'name',
				displayField: 'description',
				queryMode: 'local'
			}]
		}, {
			xtype: 'textfield',
			labelWidth: 80,
			name:'firstName',
			fieldLabel: 'First name',
			value: moduleParams['firstName']
		}, {
			xtype: 'textfield',
			labelWidth: 80,
			name:'lastName',
			fieldLabel: 'Last name',
			value: moduleParams['lastName']
		}, {
			xtype: 'textfield',
			labelWidth: 80,
			name:'postalCode',
			fieldLabel: 'Postal code',
			value: ''	
		}]
	});

	return form;
});
