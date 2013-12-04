Scalr.regPage('Scalr.ui.services.apache.vhosts.create', function (loadParams, moduleParams) {
	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		title: moduleParams.vhost.vhostId ? 'Services &raquo; Apache &raquo; Vhosts &raquo; Edit' : 'Services &raquo; Apache &raquo; Vhosts &raquo; Create',
		fieldDefaults: {
			anchor: '100%'
		},
		width: 900,

		items: [{
			xtype: 'hidden',
			name: 'vhostId'
		}, {
			xtype: 'fieldset',
			title: 'General',
			items: [{
				xtype: 'textfield',
				name: 'domainName',
				fieldLabel: 'Domain name',
				allowBlank: false
			}]
		}, {
			xtype: 'farmroles',
			title: 'Create virtualhost on',
			itemId: 'vhostTarget',
			params: moduleParams['farmWidget']
		}, {
			xtype: 'fieldset',
			title: 'SSL',
			checkboxToggle:  true,
			collapsed: !moduleParams['vhost']['isSslEnabled'],
			checkboxName: 'isSslEnabled',
			inputValue: 1,
			items: [{
				xtype: 'combo',
				store: {
					fields: [ 'id', 'name' ],
					data: moduleParams['sslCertificates']
				},
				fieldLabel: 'SSL certificate',
				valueField: 'id',
				displayField: 'name',
				forceSelection: true,
				name: 'sslCertId',
				allowBlank: false,
				disabled: !moduleParams['vhost']['isSslEnabled'],
				plugins: [{
					ptype: 'comboaddnew',
					url: '/services/ssl/certificates/create'
				}]
			}],
			listeners: {
				boxready:function() {
					this.checkboxCmp.on('change', function(){
						if (this.getValue()) {
							form.down('[name="sslTemplate"]').show();
						} else {
							form.down('[name="sslTemplate"]').hide();
						}
					});
				},
				collapse: function() {
					this.down('[name="sslCertId"]').disable();
				},
				expand: function() {
					this.down('[name="sslCertId"]').enable();
				}
			}
		}, {
			xtype: 'fieldset',
			title: 'Settings',
			defaults:{
				labelWidth: 200
			},
			items: [{
				xtype: 'textfield',
				name: 'documentRoot',
				fieldLabel: 'Document root',
				allowBlank: false
			}, {
				xtype: 'textfield',
				name: 'logsDir',
				fieldLabel: 'Logs directory',
				allowBlank: false
			}, {
				xtype: 'textfield',
				name: 'serverAdmin',
				allowBlank: false,
				vtype: 'email',
				fieldLabel: 'Server admin\'s email'
			}, {
				xtype: 'textfield',
				name: 'serverAlias',
				fieldLabel: 'Server alias (space separated)'
			}, {
				xtype: 'textarea',
				name: 'nonSslTemplate',
				fieldLabel: 'Server non-SSL template',
				grow: true,
				growMax: 400
			}, {
				xtype: 'textarea',
				name: 'sslTemplate',
				hidden: !moduleParams['vhost']['isSslEnabled'],
				fieldLabel: 'Server SSL template',
				grow: true,
				growMax: 400
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
					if (form.getForm().isValid())
						Scalr.Request({
							processBox: {
								type: 'save'
							},
							form: form.getForm(),
							url: '/services/apache/vhosts/xSave/',
							params: {
								//'vhostId': moduleParams['vhostId']
							},
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

	form.getForm().setValues(moduleParams.vhost);

	return form;
});
