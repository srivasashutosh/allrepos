Scalr.regPage('Scalr.ui.billing.details', function (loadParams, moduleParams) {
	var applyLimit = function () {
		var limit = this.limit['limit'], usage = this.limit['usage'];
		var color = 'green';
		if (limit != -1) {
			if (usage > limit)
				color = 'red';
			else if ((limit - usage) <= Math.ceil(limit * 0.1))
				color = 'yellow';
		}

		this.setValue("<span style='color:green;'>" + usage + "</span> of "+ ((limit == -1) ? "Unlimited" : limit));

		this.inputEl.applyStyles("padding-bottom: 3px; padding-left: 5px");
		this.el.applyStyles("background: -webkit-gradient(linear, left top, left bottom, from(#C8D6E5), to(#DAE5F4));");
		this.el.applyStyles("background: -moz-linear-gradient(top, #C8D6E5, #DAE5F4);");

		if (color == 'red') {
			this.bodyEl.applyStyles("background: -webkit-gradient(linear, left top, left bottom, from(#F4CDCC), to(#E78B84))");
			this.bodyEl.applyStyles("background: -moz-linear-gradient(top, #F4CDCC, #E78B84)");
		} else if (color == 'yellow') {
			this.bodyEl.applyStyles("background: -webkit-gradient(linear, left top, left bottom, from(#FCFACB), to(#F3C472))");
			this.bodyEl.applyStyles("background: -moz-linear-gradient(top, #FCFACB, #F3C472)");
		} else {
			this.bodyEl.applyStyles("background: -webkit-gradient(linear, left top, left bottom, from(#C5E1D9), to(#96CFAF))");
			this.bodyEl.applyStyles("background: -moz-linear-gradient(top, #C5E1D9, #96CFAF)");
		}
		if (limit != -1) {
			this.bodyEl.applyStyles("background-size: " + Math.ceil(usage * 100 / limit) + "% 100%; background-repeat: no-repeat");
		}
	};

	var getNextCharge = function()
	{
		if (moduleParams['billing']['nextAssessmentAt']) {
			if (moduleParams['billing']['ccType'])
				return '$'+moduleParams['billing']['nextAmount']+' on '+moduleParams['billing']['nextAssessmentAt']+' on '+moduleParams['billing']['ccType']+' '+moduleParams['billing']['ccNumber']+' [<a href="#/billing/updateCreditCard">Change card</a>]'
			else
				return '$'+moduleParams['billing']['nextAmount']+' on '+moduleParams['billing']['nextAssessmentAt']+' [<a href="#/billing/updateCreditCard">Set credit card</a>]'
		} else {
			return "";
		}
	}
	
	var getEmergSupportStatus = function()
	{
		if (!moduleParams['billing']['emergPhone'])
			moduleParams['billing']['emergPhone'] = "Registration pending, make take up to 2 business days"
		
		if (moduleParams['billing']['emergSupport'] == 'included')
			return '<span style="color:green;">Subscribed as part of ' + moduleParams['billing']['productName'] + ' package</span><a href="#" type="" style="display:none;"></a> '+ moduleParams['billing']['emergPhone'];
		else if (moduleParams['billing']['emergSupport'] == "enabled")
			return '<span style="color:green;">Subscribed</span> ($300 / month) [<a href="#" type="unsubscribe">Unsubscribe</a>] '+moduleParams['billing']['emergPhone'];
		else
			return 'Not subscribed [<a href="#" type="subscribe">Subscribe for $300 / month</a>]';
	}

	var getState = function()
	{
		if (moduleParams['billing']['state'] == 'Subscribed')
			return '<span style="color:green;font-weight:bold;">Subscribed</span>';
		else if (moduleParams['billing']['state'] == 'Trial')
			return '<span style="color:green;font-weight:bold;">Trial</span> (<b>' + moduleParams['billing']['trialDaysLeft'] + '</b> days left)';
		else if (moduleParams['billing']['state'] == 'Unsubscribed' && moduleParams['billing']['ccType'])
			return '<span style="color:red;font-weight:bold;">Unsubscribed</span> [<a href="#/billing/reactivate">Re-activate</a>]';
		else if (moduleParams['billing']['state'] == 'Unsubscribed' && !moduleParams['billing']['ccType'])
			return '<span style="color:red;font-weight:bold;">Unsubscribed</span> [<a href="#/billing/updateCreditCard">Set credit card & Re-activate</a>]';
		else if (moduleParams['billing']['state'] == 'Behind on payment')
			return '<span style="color:red;font-weight:bold;">Behind on payment</span>'
	}
	
	var getPlanDetails = function()
	{
		if (moduleParams['billing']['state'] == 'Unsubscribed' && !moduleParams['billing']['ccType'])
			var plan = moduleParams['billing']['productName'] + " ( $"+moduleParams['billing']['productPrice']+" / month)";
		else {
			if (!moduleParams['billing']['scu'])
				var plan = moduleParams['billing']['productName'] + " ( $"+moduleParams['billing']['productPrice']+" / month) [<a href='#/billing/changePlan'>Change plan / Cancel subscription</a>]"
			else
				var plan = moduleParams['billing']['productName'] + " ( $"+moduleParams['billing']['productPrice']+" / month) [<a href='#/billing/changePlan'>Cancel subscription</a>]"
		}
		
		return plan;
	}
	
	var couponString = (moduleParams['billing']['couponCode']) ? moduleParams['billing']['couponDiscount'] + ' (Used coupon: ' + moduleParams['billing']['couponCode']+')' : "No discount [<a href='#/billing/applyCouponCode'>enter coupon code</a>]";
	
	var panel = Ext.create('Ext.form.Panel', {
		width: 700,
		title: 'Subscription details',
		bodyCls:'x-panel-body-frame',
		fieldDefaults: {
			anchor: '100%',
			labelWidth: 130
		},
		items: [{
			hidden: (moduleParams['billing']['type'] == 'paypal' || (!moduleParams['billing']['isLegacyPlan'] && !!moduleParams['billing']['id'])),
			xtype: 'displayfield',
			fieldCls: 'x-form-field-warning',
			value: "You're under an old plan that doesn't allow for metered billing. If you want to get access to the new features we recently announced, <a href='#/billing/changePlan'>please upgrade your subscription</a>.",
		}, {
			hidden: (moduleParams['billing']['type'] != 'paypal'),
			fieldCls: 'x-form-field-warning',
			xtype: 'displayfield',
			value: "Hey mate, I see that you are using Paypal for your subscription. Unfortunately paypal hasn't been working too well for us, so we've discontinued its use."+
				  "<br/><a href='#/billing/changePlan'>Click here to switch to direct CC billing</a>, and have your subscription to paypal canceled.",
		}, {
			xtype: 'displayfield',
			fieldLabel: 'Plan',
			value: getPlanDetails()
		}, {
			xtype: 'displayfield',
			hidden: (!!moduleParams['billing']['type']),
			fieldLabel: 'Status',
			value: getState()
		}, {
			xtype: 'displayfield',
			hidden: (!!moduleParams['billing']['type']),
			fieldLabel: 'Balance',
			value: "$"+moduleParams['billing']['balance']
		}, {
			xtype: 'displayfield',
			hidden: (!!moduleParams['billing']['type']),
			fieldLabel: 'Discount',
			value: couponString
		}, {
			xtype: 'fieldset',
			title: '"Pay As You Go" usage',
			defaults: {
				labelWidth: 120
			},
			items: [{
				xtype: 'fieldcontainer',
				fieldLabel: 'Pre-paid SCUs',
				layout: 'hbox',
				hidden: moduleParams['billing']['scu']['limit'] == -1,
				items: [{
					xtype: 'displayfield',
					width: 145,
					limit: moduleParams['billing']['scu'],
					listeners: {
						boxready: applyLimit
					}
				}]
			}, {
                xtype: 'fieldcontainer',
                fieldLabel: 'Current SCU usage',
                layout: 'hbox',
                items: [{
                    xtype: 'displayfield',
                    width: 345,
                    value: moduleParams['billing']['scu']['current_usage'] + " / hour"
                }]
            }, {
				xtype: 'fieldcontainer',
				fieldLabel: 'Additional SCUs',
				hidden: moduleParams['billing']['scu']['limit'] == -1,
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					width: 345,
					value: moduleParams['billing']['scu'] ? (moduleParams['billing']['scu']['paid'] + "") : ''
				}]
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: 'Price per SCU',
				hidden: moduleParams['billing']['scu']['limit'] == -1,
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					width: 145,
					value: moduleParams['billing']['scu'] ? ("$" + moduleParams['billing']['scu']['price']) : ''
				}]
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: 'Usage',
				hidden: moduleParams['billing']['scu']['limit'] == -1,
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					width: 145,
					value: moduleParams['billing']['scu'] ? ("$" + moduleParams['billing']['scu']['cost']) : ''
				}]
			}]
		}, {
			xtype: 'fieldset',
			title: 'Account usage',
			hidden: (moduleParams['billing']['type'] == 'paypal' || moduleParams['billing']['isLegacyPlan'] || !!moduleParams['billing']['scu']),
			defaults: {
				labelWidth: 120
			},
			items: [{
				hidden:true,
				xtype: 'displayfield',
				fieldCls: 'x-form-field-warning',
				value: "You're using more servers than allowed by your plan. <a href='#/billing/changePlan'>Click here to upgrade your subscription</a>.",
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: 'Servers',
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					width: 145,
					limit: moduleParams['limits']['account.servers'],
					listeners: {
						boxready: applyLimit
					}
				}, {
					xtype: 'displayfield',
					hidden: (moduleParams['billing']['state'] == 'Unsubscribed' && !moduleParams['billing']['ccType']),
					margin: '0 0 0 3',
					value: '[<a href="#/billing/changePlan">Increase limit</a>]'
				}]
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: 'Farms',
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					width: 145,
					limit: moduleParams['limits']['account.farms'],
					listeners: {
						boxready: applyLimit
					}
				}]
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: 'Environments',
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					width: 145,
					limit: moduleParams['limits']['account.environments'],
					listeners: {
						boxready: applyLimit
					}
				}/*, {
					xtype: 'displayfield',
					margin: '0 0 0 3',
					value: '[<a href="#/billing/buyEnvironments">Buy more for $99 / environment / month</a>]'
				}*/]
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: 'User accounts',
				layout: 'hbox',
				items: [{
					xtype: 'displayfield',
					width: 145,
					limit: moduleParams['limits']['account.users'],
					listeners: {
						boxready: applyLimit
					}
				}]
			}]
		}, {
			xtype: 'fieldset',
			title: 'Features',
			hidden: (moduleParams['billing']['type'] == 'paypal' || moduleParams['billing']['isLegacyPlan'] || !!moduleParams['billing']['scu']),
			items: [{
				itemId: 'featuresFieldSet',
				xtype: 'displayfield',
				hideLabel: true
			}]
		}, {
			xtype: 'fieldset',
			title: 'Next charge',
			hidden: (moduleParams['billing']['type'] == 'paypal'  || !moduleParams['billing']['id'] || !moduleParams['billing']['nextAssessmentAt']),
			//padding: 10,
			items:[{
				xtype:'component',
				padding: 5,
				html: getNextCharge()	
			}]
		}, {
			xtype: 'fieldset',
			title: '<a href="http://scalr.net/emergency_support/" target="_blank">Emergency support</a>',
			hidden: (moduleParams['billing']['type'] == 'paypal'  || moduleParams['billing']['isLegacyPlan']),
			//padding: 10,
			items:[{
				xtype:'component',
				padding: 5,
				afterRenderFunc: function(e) {
					this.el.down("a").on('click', function(e){
						e.preventDefault();
					
						var action = this.getAttribute('type');
						
						Scalr.Request({
							confirmBox: {
								type: 'action',
								msg: (action == 'subscribe') ? 'Are you sure want to subscribe to Emergency Support for $300 / month?' : 'Are you sure want to unsubscribe from Emergency Support?',
							},
							processBox: {
								type: 'action'
							},
							params:{action: action},
							url: '/billing/xSetEmergSupport/',
							success: function () {
								moduleParams['billing']['emergSupport'] = (action == 'subscribe') ? 'enabled' : 'disabled';
								panel.down("#emergSupport").update(getEmergSupportStatus());
								panel.down("#emergSupport").afterRenderFunc();
								Scalr.message.Success((action == 'subscribe') ? "You've successfully subscribed to Emergency support" : "You've successfully unsubscribed from emergency support");
							}
						});
					});
				},
				itemId: 'emergSupport',
				html: getEmergSupportStatus(),
				listeners:{
					boxready: function() {
						this.afterRenderFunc();
					}
				}
			}]
		}, {
			xtype: 'fieldset',
			title: 'Invoices',
			hidden: (moduleParams['billing']['type'] == 'paypal' || !moduleParams['billing']['ccType']),
			//padding: 10,
			/*style: {
				fontSize: '12px'
			},*/
			items: [{
				xtype: 'button',
				text: 'Compile invoices',
				handler: function() {
					Scalr.event.fireEvent('redirect', '#/billing/invoicesList');
				}
			}]
		}]
	});

	var featuresText = "";
	for (var name in moduleParams['features']) {
		var isEnabled = moduleParams['features'][name];
		
		if (isEnabled)
			featuresText += "<span style='color:green'>[" + name + "]</span> ";
		else
			featuresText += "<span style='color:gray'>[" + name + "]</span> ";
	}
	
	panel.down("#featuresFieldSet").setValue(featuresText);
	
	return panel;
});
