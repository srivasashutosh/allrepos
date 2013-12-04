Scalr.regPage('Scalr.ui.tools.aws.s3.buckets', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'name' , 'farmId', 'farmName', 'cfid', 'cfurl', 'cname', 'status', 'enabled'],
		proxy: {
			type: 'scalr.paging',
			extraParams: loadParams,
			url: '/tools/aws/s3/xListBuckets/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Tools &raquo; Amazon Web Services &raquo; S3 &raquo; Buckets &amp; Cloudfront',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		store: store,
		stateId: 'grid-tools-aws-s3-buckets',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}],
		viewConfig: {
			emptyText: "No buckets found",
			loadingText: 'Loading buckets ...'
		},
		columns: [
			{ header: "Bucket name", flex: 2, dataIndex: 'name', sortable: false },
			{ header: "Used by", flex: 1, dataIndex: 'farmId', xtype: 'templatecolumn', sortable: true, tpl:
				'<tpl if="farmId"><a href="#/farms/{farmId}/view">{farmName}</a></tpl>' +
				'<tpl if="! farmId"><img src="/ui2/images/icons/false.png"></tpl>'
			},
			{ header: "Cloudfront ID", flex: 2, dataIndex: 'cfid', sortable: false},
			{ header: "Cloudfront URL", flex: 2, dataIndex: 'cfurl', sortable: false},
			{ header: "CNAME", flex: 3, dataIndex: 'cname', sortable: false},
			{ header: "Status", width: 80, dataIndex: 'status', sortable: false},
			{ header: "Enabled", width: 80, dataIndex: 'enabled', xtype: 'templatecolumn', sortable: false, tpl:
				'<tpl if="enabled == \'true\'"><img src="/ui2/images/icons/true.png"></tpl>' +
				'<tpl if="enabled == \'false\' || !enabled"><img src="/ui2/images/icons/false.png"></tpl>'
		}, {
			xtype: 'optionscolumn',
			width: 120,
			getOptionVisibility: function (item, record) {
				switch (item.itemId) {
					case "option.disable_dist":
						return ( ( record.data.enabled == "true") && record.data[ 'cfid' ] );
	
					case  "option.enable_dist":
						return ( (record.data.enabled == "false") && record.data[ 'cfid' ] );
	
					case "option.delete_dist":
						return ( record.data[ 'cfid' ] && record.get('status') == 'Deployed' && record.get('enabled') == 'false' );
	
					case "option.create_dist":
							return ( !record.data[ 'cfid' ] );
	
					default:
						return true;
				}
			},
			optionsMenu: [{
				itemId: "option.create_dist",
				text: 'Create distribution',
				iconCls: 'x-menu-icon-create',
				href: "#/tools/aws/s3/manageDistribution?bucketName={name}"
			}, {
				itemId: "option.delete_dist",
				iconCls: 'x-menu-icon-delete',
				text: 'Remove distribution',
				menuHandler: function(item) {
					Scalr.Request({
						confirmBox: {
							msg: 'Remove distribution ?',
							type: 'delete'
						},
						processBox: {
							msg: 'Removing distribution ...',
							type: 'delete'
						},
						scope: this,
						url: '/tools/aws/s3/xDeleteDistribution',
						params: {id: item.record.get('cfid'), cfurl: item.record.get('cfurl'), cname: item.record.get('cname')},
						success: function (data, response, options){
							store.load();
						}
					});
				}
			},
			{ itemId: "option.disable_dist", text: 'Disable distribution',
				menuHandler: function(item) {
					Scalr.Request({
						processBox: {
							type: 'action'
						},
						scope: this,
						url: '/tools/aws/s3/xUpdateDistribution',
						params: {id: item.record.get('cfid'), enabled: false},
						success: function (data, response, options){
							store.load();
						}
					});
				}
			},
			{ itemId: "option.enable_dist", text: 'Enable distribution',
				menuHandler: function(item) {
					Scalr.Request({
						processBox: {
							type: 'action'
						},
						scope: this,
						url: '/tools/aws/s3/xUpdateDistribution',
						params: {id: item.record.get('cfid'), enabled: true},
						success: function (data, response, options){
							store.load();
						}
					});
				}
			},
				new Ext.menu.Separator({itemId: "option.editSep"}),
			{ itemId: "option.delete_backet", iconCls: 'x-menu-icon-delete', text: 'Delete bucket',
				menuHandler: function(item) {
					if(item.record.get('cfid')) {
						Scalr.message.Warning('Remove distribution before deleting');
					} else {
						Scalr.Request({
							confirmBox: {
								msg: 'Remove selected bucket ?',
								type: 'delete'
							},
							processBox: {
								msg: 'Removing bucket ...',
								type: 'delete'
							},
							scope: this,
							url: '/tools/aws/s3/xDeleteBucket',
							params: { buckets: Ext.encode([ item.record.get('name') ]) },
							success: function (data, response, options){
								store.load();
							}
						});
					}
				}
			}]
		}],

        multiSelect: true,
        selModel: {
            selType: 'selectedmodel'
        },

        listeners: {
            selectionchange: function(selModel, selections) {
                this.down('scalrpagingtoolbar').down('#delete').setDisabled(!selections.length);
            }
        },

        dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
            beforeItems: [{
                ui: 'paging',
                iconCls: 'x-tbar-add',
                handler: function() {
                    Scalr.Request({
                        confirmBox: {
                            title: 'Create new Bucket',
                            form: [{
                                xtype: 'combo',
                                name: 'location',
                                fieldLabel: 'Select location',
                                width: 303,
                                editable: false,
                                allowBlank: false,
                                queryMode: 'local',
                                store: {
                                    fields: [ 'id', 'name' ],
                                    data: moduleParams.locations,
                                    proxy: 'object'
                                },
                                valueField: 'id',
                                displayField: 'name'
                            },{
                                xtype: 'textfield',
                                name: 'bucketName',
                                fieldLabel: 'Bucket Name',
                                allowBlank: false,
                                width: 303
                            }],
                            formValidate: true,
                            ok: 'Add'
                        },
                        processBox: {
                            msg: 'Creating new Bucket...',
                            type: 'save'
                        },
                        scope: this,
                        url: '/tools/aws/s3/xCreateBucket',
                        success: function (data, response, options){
                            store.load();
                        }
                    });
                }
            }],
			afterItems: [{
                ui: 'paging',
                itemId: 'delete',
                iconCls: 'x-tbar-delete',
                tooltip: 'Select one or more buckets to delete them',
                disabled: true,
                handler: function() {
                    var request = {
                        confirmBox: {
                            type: 'delete',
                            msg: 'Delete selected bucket(s): %s ?'
                        },
                        processBox: {
                            type: 'delete',
                            msg: 'Deleting bucket(s) ...'
                        },
                        url: '/tools/aws/s3/xDeleteBucket',
                        success: function() {
                            store.load();
                        }
                    }, records = this.up('grid').getSelectionModel().getSelection(), objects = [];

                    request.confirmBox.objects = [];
                    for (var i = 0, len = records.length; i < len; i++) {
                        objects.push(records[i].get('name'));
                        request.confirmBox.objects.push(records[i].get('name'));
                    }
                    request.params = { buckets: Ext.encode(objects) };
                    Scalr.Request(request);
                }
			}],
			items: [{
				xtype: 'filterfield',
				store: store
			}]
		}]
	});
});