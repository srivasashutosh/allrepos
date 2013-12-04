Scalr.regPage('Scalr.ui.servers.import_step2', function (loadParams, moduleParams) {
	var waitHello = {
		flag: true,
		request: {},
		fn: function () {
			this.request = Scalr.Request({
				url: '/servers/xImportWaitHello/',
				params: { serverId: moduleParams['serverId'] },
				success: function (data) {
					Scalr.event.fireEvent('redirect', '#/bundletasks/' + data.bundleTaskId + '/logs');
				},
				failure: function () {
					if (waitHello.flag)
						Ext.Function.defer(waitHello.fn, 2000);
				}
			});
		}
	};

	var form = Ext.create('Ext.form.Panel', {
		bodyCls: 'x-panel-body-frame',
		width: 700,
		title: 'Import server - Step 2 (Establish communication)',
		fieldDefaults: {
			anchor: '100%'
		},

		items: [{
			xtype: 'fieldset',
			title: 'Install scalarizr',
			labelWidth: 130,
			items: [{
				xtype: 'displayfield',
				hideLabel: true,
				value: '<a target="_blank" href="http://wiki.scalr.com/x/RgAF">Please follow this instruction to install scalarizr on your server</a>'
			}]
		}, {
			xtype: 'fieldset',
			title: 'Launch scalarizr',
			labelWidth: 130,
			items: [{
				xtype: 'displayfield',
				hideLabel: true,
				value: 'When scalarizr installed please use the following command to launch it:'
			}, {
				xtype:'textarea',
				hideLabel: true,
				height: 100,
				value: moduleParams['cmd']
			}]
		}, {
			xtype: 'fieldset',
			title: 'Establishing communication',
			labelWidth: 130,
			items: [{
				xtype: 'displayfield',
				hideLabel: true,
				value: '<img style="vertical-align:middle;" src="/ui2/images/icons/anim/loading_16x16.gif"> Waiting for running scalarizr on server ...'
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
				text: 'Cancel server import',
				handler: function() {
					Ext.Ajax.abort(waitHello.request);
					waitHello.flag = false;
					Scalr.Request({
						processBox: {
							type: 'action'
						},
						url: '/servers/xServerCancelOperation/',
						params: { serverId: moduleParams['serverId'] },
						success: function (data) {
							Scalr.event.fireEvent('redirect', '#/servers/view');
						}
					});
				}
			}]
		}],

		listeners: {
			'destroy': function () {
				Ext.Ajax.abort(waitHello.request);
				waitHello.flag = false;
			}
		}
	});
	waitHello.fn();

	return form;
});
