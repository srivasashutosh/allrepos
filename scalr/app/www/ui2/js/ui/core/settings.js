Scalr.regPage('Scalr.ui.core.settings', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 800,
		title: 'Settings',
		items: [{
			xtype: 'container',
			layout: 'hbox',
			items: [{
				xtype: 'fieldset',
				flex: 1,
				title: 'Profile information',
				defaults: {
					labelWidth: 80
				},
				items: [{
					xtype: 'displayfield',
					name: 'user_email',
					fieldLabel: 'Email',
					readOnly: true
				},{
					xtype: 'textfield',
					name: 'user_fullname',
					fieldLabel: 'Full name',
					width: 280
				}]
			},{
				xtype: 'fieldset',
				flex: 1,
				title: 'Avatar settings',
				margin: '0 0 0 12',
				items: [{
					xtype: 'textfield',
					name: 'gravatar_email',
					fieldLabel: 'Gravatar email',
					vtype: 'email',
					width: 280
				},{
					xtype: 'displayfield',
					itemId: 'gravatar'
				}]
			}]
		}, {
			xtype: 'fieldset',
			title: 'RSS feed',
			defaults: {
				labelWidth: 80
			},
			items: [{
				xtype: 'displayfield',
				fieldCls: 'x-form-field-info',
				value: 'Each farm has an events and notifications page. You can get these events outside of Scalr on an RSS reader with the below credentials.'
			}, {
				xtype: 'textfield',
				name: 'rss_login',
				width: 285,
				fieldLabel: 'Login'
			}, {
				xtype: 'fieldcontainer',
				fieldLabel: 'Password',
				layout: 'hbox',
				items: [{
					xtype: 'textfield',
					name: 'rss_pass',
					width: 200,
					hideLabel: true
				}, {
					xtype: 'button',
					text: 'Generate',
					margin: '0 0 0 10',
					handler: function() {
						function getRandomNum() {
							var rndNum = Math.random();
							rndNum = parseInt(rndNum * 1000);
							rndNum = (rndNum % 94) + 33;
							return rndNum;
						};

						function checkPunc(num) {
							if ((num >=33) && (num <=47)) { return true; }
							if ((num >=58) && (num <=64)) { return true; }
							if ((num >=91) && (num <=96)) { return true; }
							if ((num >=123) && (num <=126)) { return true; }
							return false;
						};

						var length=16;
						var sPassword = "";

						for (var i=0; i < length; i++) {
							var numI = getRandomNum();
							while (checkPunc(numI)) { numI = getRandomNum(); }
							sPassword = sPassword + String.fromCharCode(numI);
						}

						this.prev().setValue(sPassword);
					}
				}]
			}]
		}, {
			xtype: 'fieldset',
			title: 'User interface',
			defaults: {
				labelWidth: 80
			},
			items: [{
				xtype: 'combo',
				fieldLabel: 'Timezone',
				store: moduleParams['timezones_list'],
				allowBlank: false,
				forceSelection: true,
				editable: true,
				name: 'timezone',
				queryMode: 'local',
				width: 400,
				anyMatch: true
			}]
		}, {
			xtype: 'container',
			layout: 'hbox',
			items: [{
				xtype: 'fieldset',
				title: 'Dashboard',
				flex: 1,
				defaults: {
					labelWidth: 80
				},
				items: [{
					xtype: 'buttongroupfield',
					fieldLabel: 'Columns',
					name: 'dashboard_columns',
					value: moduleParams['dashboard_columns'],
					items: [{
						text: '1',
						value: '1',
						width: 42
					}, {
						text: '2',
						value: '2',
						width: 42
					}, {
						text: '3',
						value: '3',
						width: 42
					}, {
						text: '4',
						value: '4',
						width: 42
					}, {
						text: '5',
						value: '5',
						width: 42
					}]
				}]
			}, {
				xtype: 'fieldset',
				title: 'Grids',
				flex: 1,
				margin: '0 0 0 12',
				items: [{
					xtype: 'combo',
					anchor: '100%',
					store: ['auto', 10, 15, 25, 50, 100],
					valueField: 'id',
					displayField: 'name',
					value: Ext.state.Manager.get('grid-ui-page-size', 'auto'),
					fieldLabel: 'Items per page',
					queryMode: 'local',
					editable: false,
					name: 'items_per_page',
					submitValue: false,
					listeners: {
						change: function(component, newValue) {
							Ext.state.Manager.set('grid-ui-page-size', newValue);
						}
					}
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
				itemId: 'buttonSubmit',
				handler: function() {
					if (form.getForm().isValid())
						Scalr.Request({
							processBox: {
								type: 'save'
							},
							url: '/core/xSaveSettings/',
							form: this.up('form').getForm(),
							scope: this,
							success: function (data, response, options) {
								if (this.up('form').down('[name="dashboard_columns"]') != moduleParams['dashboard_columns']) {
									Scalr.event.fireEvent('update', '/dashboard', data.panel);
								}
								Scalr.event.fireEvent('update', '/account/user/gravatar', data['gravatarHash'] ||'');
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

	form.getForm().setValues(moduleParams);
	form.down('#gravatar').setValue('<img style="position:absolute;width:23px;height:23px;margin-top:-5px" src="'+Scalr.utils.getGravatarUrl(moduleParams['gravatar_hash'])+'" /><a  style="margin-left:30px" href="http://gravatar.com/" target="blank">Change your avatar at Gravatar.com.</a>')
	return form;
});
