Scalr.regPage( 'Scalr.ui.db.backups.details', function ( loadParams, moduleParams ) {
	var form = Ext.create( 'Ext.form.Panel', {
		title: 'Backup details',
		scalrOptions: {
			'modal': true
		},
		width: 850,
		bodyCls: 'x-panel-body-frame',
		items: [{
			xtype: 'fieldset',
			title: 'General',
			defaults: {
				xtype: 'displayfield',
				anchor: '100%'
			},
			items: [{
				fieldLabel: 'Created At',
				name: 'date'
			}, {
				fieldLabel: 'Farm',
				name: 'farm'
			}, {
				fieldLabel: 'Type',
				name: 'type'
			}, {
			 	fieldLabel: 'Size',
				name: 'size'
			}, {
				fieldLabel: 'Cloud Storage',
				name: 'provider'
			}]
		},{
			xtype: 'fieldset',
			title: 'Download backup',
			itemId: 'downloadBackup',
			collapsible: true,
			collapsed: true
		}],
		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			cls: 'x-docked-bottom-frame',
			items: {
				xtype: 'button',
				cls: 'x-btn-default-small-red',
				text: 'Remove backup',
				handler: function () {
					Scalr.Request({
						confirmBox: {
							msg: 'Are you sure you want to remove this backup?',
							type: 'delete'
						},
						processBox: {
							type: 'delete'
						},
						url: '/db/backups/xRemoveBackup',
						params: loadParams,
						success: function ( data ) {
							Scalr.event.fireEvent( 'close' );
						}
					});
				}
			}
		}],
		tools: [{
			type: 'refresh',
			handler: function () {
				Scalr.event.fireEvent( 'refresh' );
			}
		}, {
			type: 'close',
			handler: function () {
				Scalr.event.fireEvent( 'close' );
			}
		}]
	});

	if (moduleParams['backup']) {
		form.getForm().setValues(moduleParams['backup']);

		form.down('[name="size"]').setValue(moduleParams['backup']['size'] ? moduleParams['backup']['size'] + 'Mb' : 'unknown');
		form.down('[name="farm"]').setValue(
			'<a href="#/farms/' + moduleParams['backup']['farm_id'] + '/view">'
			+ moduleParams['backup']['farmName']
			+ ' (' + moduleParams['backup']['farm_id']
			+ ')</a>'
		);
		form.down('[name="type"]').setValue(
			'<a href="#/dbmsr/status?farmId=' + moduleParams['backup']['farm_id'] + '&type=' + moduleParams['backup']['type'] + '">'
			+ moduleParams['backup']['type']
			+ '</a>'
		);
		
		var linksResult = '';
		var links = moduleParams['backup']['links'];
		
        for (var i in links) {
            var size = links[i]['size'] ? (links[i]['size'] + 'Mb') : ' unknown';
            linksResult += '<div style="padding-top: 5px;">'
                + '<a target="_blank" href="' + links[i]['link'] + '">'
                + links[i]['path']['basename']
                + '</a> ( '
                + size
                + ' )</div>';
        }

        if (linksResult)
            form.down('#downloadBackup').add({
                xtype: 'displayfield',
                anchor: '100%',
                value: linksResult
            });
        else
            form.down('#downloadBackup').hide();
	}

	return form;
});