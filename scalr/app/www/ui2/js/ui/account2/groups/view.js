Scalr.regPage('Scalr.ui.account2.groups.view', function (loadParams, moduleParams) {
	var storeTeams = Scalr.data.get('account.teams'),
		storeGroups = Scalr.data.get('account.groups'),
		isAccountOwner = Scalr.user['type'] == 'AccountOwner';

	var store = Ext.create('Scalr.ui.ChildStore', {
		parentStore: storeGroups,
		filterOnLoad: true,
		sortOnLoad: true,
		sorters: [{
			property: 'name',
			transform: function(value){
				return value.toLowerCase();
			}
		}]
	})

	var comboboxTeams = Ext.create('Scalr.ui.ChildStore', {
		parentStore: storeTeams,
		filterOnLoad: true,
		sortOnLoad: true,
		filters: [{
			filterFn: function(record) {
				var res = false;
				res = isAccountOwner;
				if (!res) {
					var teamUsers = record.get('users');
					if (teamUsers) {
						for (var i=0, len=teamUsers.length; i<len; i++) {
							if (teamUsers[i].id == Scalr.user.userId) {
								res = teamUsers[i].permissions == 'owner';
								break;
							}
						}
					}
				}
				return res;
			}
		}],
		sorters: [{
			property: 'name',
			transform: function(value){
				return value.toLowerCase();
			}
		}]
	})


	var getPermissionItems = function() {
		var columns = [], index = 0, columnIndex;
		Ext.Object.each(moduleParams['permissions'], function(permName, permOptions){
			columnIndex = index%3;
			var column = columns[columnIndex] || (columns[columnIndex] = {
				xtype: 'panel',
				columnWidth: .33,
				maxWidth: 360,
				flex:1,
				layout: {
					type: 'vbox',
					align : 'stretch',
					pack  : 'start'
				},		
				cls: 'scalr-ui-permissions-wrapper',
				items: []
			});

			var permTitle = permName.replace(/_/g, '/'), replaceTitles = {
				'Bundletasks': 'Bundle tasks',
				'Dbmsr': 'Database manager',
				'Dm': 'Deployments manager',
				'Dnszones': 'DNS Zones',
				'Schedulertasks': 'Task scheduler'
			};
			permTitle = replaceTitles[permTitle] || permTitle;
			
			var permItems = [{
					xtype: 'checkbox',
					boxLabel:  permTitle,
					name: 'controller[' + permName + ']',
					listeners: {
						change: function(me, value) {
							var next = me.next(),
								panel = this.up('panel');
							panel.suspendLayouts();
							if (next) {
								next[value?'show':'hide']();
								next = next.next();
								if (next) {
									next[value?'show':'hide']();
								}
								
							} else {
								this.boxLabelEl.setHTML(permTitle + (value? ' &nbsp;<span style="color:green">Full access</span>' : ''));
							}
							panel[value?'addCls':'removeCls']('scalr-ui-permission-expanded');
							panel.resumeLayouts(true);
						}
					}
			}];
		
			if (permOptions.length) {
				var access = [];
				for (var i=0, len=permOptions.length; i<len; i++) {
					access.push({
						boxLabel: permOptions[i],
						name: 'permission[' + permName + '][' + permOptions[i] + ']'
					});
				}
				permItems.push.apply(permItems, [{
					xtype: 'buttongroupfield',
					name: 'access['+permName+']',
					value: 'VIEW',
					hidden: true,
					listeners: {
						change: function() {
							var cb = this.next();
							if (this.getValue() == 'FULL') {
								cb.suspendLayouts();
								cb.disable();
								cb.items.each(function (item) {
									item.setValue(true);
								});
								cb.resumeLayouts(true);
							} else {
								cb.enable();
							}
						}
					},
					items: [{
						text: 'Full access',
						value: 'FULL'
					}, {
						text: 'View only with ...',
						value: 'VIEW'
					}]
				}, {
					xtype: 'checkboxgroup',
					hidden: true,
					items: access
				}]);
			}
			column.items.push({
				itemId: permTitle,
				cls: 'scalr-ui-permission',
				margin: 4,
				padding: '6 6 0 6',
				defaults: {
					width: '100%'
				},
				items: permItems
			});

			index++;
		});
		return columns;
	}

    var dataview = Ext.create('Ext.view.View', {
        deferInitialRefresh: false,
        store: store,
		cls: 'x-dataview-columned',
        tpl  : new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="x-item"><div class="x-item-color-corner x-item-color-corner-{[values.color?values.color:\'333333\']}" ></div>',
					'<div class="x-item-inner">',
						'<table>',
							'<tr>',
								'<td>',
									'<div class="x-item-title">{name}</div>',
									'<table>',
									'<tr><td class="x-item-param-title">Team:</td><td class="x-item-param-value">{[this.getTeamName(values.teamId)]}</td></tr> ',
									'</table>',
								'</td>',
							'</tr>',
						'</table>',
					'</div>',
                '</div>',
            '</tpl>',
			{
				getTeamName: function(teamId){
					var team = storeTeams.getById(teamId);
					return team ? team.get('name') : '';
				}
			}			
        ),
		plugins: {
			ptype: 'dynemptytext',
			emptyText: '<div class="title">No ACL were found<br/> to match your search.</div>Try modifying your search criteria <br/>or <a class="add-link" href="#">creating a new ACL</a>',
			emptyTextNoItems:	'<div class="title">You have no ACLs<br/> under your account.</div>'+
								'Access Control Lists let you define exactly what your co-workers have or don\'t have access to.<br/>' +
								'Click "<b>+</b>" button to create one.',
			onAddItemClick: function() {
				panel.down('#add').handler();
			}
		},
		loadingText: 'Loading ACLs ...',
		deferEmptyText: false,

        itemSelector: '.x-item',
        overItemCls : 'x-item-over',
		trackOver: true
    });

	var form = 	Ext.create('Ext.form.Panel', {
		hidden: true,
		fieldDefaults: {
			anchor: '100%'
		},
		listeners: {
			hide: function() {
				dataview.up('panel').down('#add').setDisabled(false);
			},
			afterrender: function() {
				var me = this;
				dataview.on('selectionchange', function(dataview, selection){
					if (selection.length) {
						me.loadRecord(selection[0]);
					} else {
						me.setVisible(false);
					}
				});
			},
			beforeloadrecord: function(record) {
				var form = this.getForm(),
					isNewRecord = !record.get('id');
				form.reset();

				this.down('#delete').setVisible(!isNewRecord);
				if (isNewRecord) {
					this.down('#groupteamid').show();
					this.down('#groupteamname').hide();
				} else {
					this.down('#groupteamid').hide();
					this.down('#groupteamname').show();
					var team = storeTeams.getById(record.get('teamId'));
					this.down('#groupteamname').setValue(team?'<a href="#/account/teams?teamId='+team.get('id')+'">'+team.get('name')+'</a>':'');
				}
				
				this.down('#settings').setTitle(!isNewRecord?'Edit &ldquo;<span class="acl-name-color">'+record.get('name')+'</span>&rdquo;':'Add <span class="acl-name-color">ACL</span>');
				dataview.up('panel').down('#add').setDisabled(isNewRecord);
			},
			loadrecord: function(record) {
				var me = this,
					frm = me.getForm(),
					permissions = record.get('permissions');
				Ext.Object.each(permissions, function(permName, permOptions){
					if (permOptions.length) {
						me.suspendLayouts();
						frm.findField('controller['+permName+']').setValue(true);
						if (moduleParams['permissions'][permName].length) {
							if (permOptions[0] == 'FULL') {
								frm.findField('access['+permName+']').setValue('FULL');
							} else {
								for (var i=0, len=permOptions.length; i<len; i++) {
									var field = frm.findField('permission['+permName+']['+permOptions[i]+']');
									if (field) {
										field.setValue(true);
									}
								}
							}
						}
						me.resumeLayouts(true);
					}
				});
				frm.clearInvalid();
				if (!this.isVisible()) {
					this.setVisible(true);
				}			
			}
		},
		layout: {
			type: 'vbox',
			align : 'stretch',
			pack  : 'start'
		},		
		items: [{
			itemId: 'settings',
			xtype: 'fieldset',
			title: 'Settings',
			defaults: {
				border: false,
				xtype: 'panel',
				flex: 1,
				layout: 'anchor',
				maxWidth: 370
			},

			layout: 'hbox',
			items: [{
				defaults:{
					labelWidth: 40
				},
				items: [{
					xtype: 'hiddenfield',
					name: 'id'
				},{
					xtype: 'textfield',
					name: 'name',
					fieldLabel: 'ACL',
					allowBlank: false
				},{
					xtype: 'combo',
					itemId: 'groupteamid',
					fieldLabel: 'Team',
					store: comboboxTeams,
					allowBlank: false,
					editable: false,
					displayField: 'name',
					valueField: 'id',
					name: 'teamId',
					queryMode: 'local'
				},{
					xtype: 'displayfield',
					itemId: 'groupteamname',
					fieldLabel: 'Team',
					hidden: true
				}]
			},{
				defaults:{
					labelStyle: 'padding-left:20px',
					labelWidth: 60
				},
				items: [{
					xtype: 'colorfield',
					name: 'color',
					fieldLabel: 'Color',
					allowBlank: false,
					listeners: {
						change: function(comp, value) {
							if (form) {
								form.el.select('.acl-name-color').setStyle('color', '#'+value);
							}
						}
					}

				}]
			}]
		}, {
			xtype: 'fieldset',
			title: 'Permissions',
			items: {
				overflowX: 'hidden',
				overflowY: 'auto',
				bodyStyle:'padding-right:20px',
				listeners: {
					boxready: function(){
						Scalr.event.fireEvent('resize');
					},
					afterlayout: function(){//resize grid maxHeight to fit available space
						var maxHeight = this.up('#rightcol').body.getHeight()- form.getHeight() + this.getHeight();
						if (this.maxHeight != maxHeight) {
							this.maxHeight = maxHeight>200?maxHeight:200;
						}
					}
				},
				layout: 'hbox',
				dockedItems: [{
					dock: 'top',
					layout: 'hbox',
					margin: '0 0 4 4',
					items: [{
						xtype: 'triggerfield',
						submitValue: false,
						fieldCls: 'x-form-field x-form-field-livesearch',
						triggerCls: 'x-form-trigger-reset',
						emptyText: 'Filter',
						listeners: {
							boxready: function(){
								this.triggerEl.hide();
								this.on('change', function(me){
									me.applyFilter();
								}, this, {buffer: 300});
							}
						},
						applyFilter: function(){
							var value = this.getValue(),
								r = new RegExp(Ext.String.escapeRegex(value), 'i'),
								items = this.up('fieldset').query('panel');
							this.triggerEl[value != ''?'show':'hide']();
							for (var i=0, len=items.length; i<len; i++) {
								if (value == '') {
									items[i].removeCls('scalr-ui-permission-dimmed scalr-ui-permission-exposed');
								} else {
									if (items[i].getItemId().match(r)) {
										items[i].removeCls('scalr-ui-permission-dimmed');
										items[i].addCls('scalr-ui-permission-exposed');
									} else {
										items[i].addCls('scalr-ui-permission-dimmed');
										items[i].removeCls('scalr-ui-permission-exposed');
									}
								}
							}
						},
						reset: function() {
							this.suspendEvents(false);
							this.setValue('');
							this.resumeEvents();
							this.applyFilter();
						},
						onTriggerClick: function() {
							this.reset();
						}
					}]
				}],	
				items: getPermissionItems()
			}
		}],
		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			cls: 'x-toolbar x-docked-light',
			maxWidth: 1200,
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				itemId: 'save',
				xtype: 'button',
				text: 'Save',
				handler: function() {
					var frm = form.getForm(),
						record = frm.getRecord();
					if (frm.isValid()) {
						Scalr.Request({
							processBox: {
								type: 'save'
							},
							url: '/account/groups/xSave',
							form: frm,
							success: function (data) {
								if (!record.get('id')) {
									record = store.add(data.group)[0];
									dataview.getSelectionModel().select(record);
								} else {
									record.set(data.group);
									form.loadRecord(record);
								}
							}
						});
					}

				}
			}, {
				itemId: 'cancel',
				xtype: 'button',
				text: 'Cancel',
				handler: function() {
					dataview.deselect(form.getForm().getRecord());
					form.setVisible(false);
				}
			}, {
				itemId: 'delete',
				xtype: 'button',
				cls: 'x-btn-default-small-red',
				text: 'Delete',
				handler: function() {
					var record = form.getForm().getRecord();
					Scalr.Request({
						confirmBox: {
							msg: 'Delete ACL ' + record.get('name') + ' ?',
							type: 'delete'
						},
						processBox: {
							msg: 'Deleting...',
							type: 'delete'
						},
						scope: this,
						url: '/account/groups/xRemove',
						params: {
							groupId: record.get('id'),
							teamId: record.get('teamId')
						},
						success: function (data) {
							record.store.remove(record);
						}
					});
				}
			}]
		}]
	});

	var panel = Ext.create('Ext.panel.Panel', {
		cls: 'x-panel-columned scalr-ui-account-groups',
		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		scalrOptions: {
			title: 'Access control',
			reload: false,
			maximize: 'all',
			leftMenu: {
				menuId: 'account',
				itemId: 'groups'
			}
		},
		items:[
			Ext.create('Ext.panel.Panel', {
				cls: 'x-panel-columned-leftcol',
				width: 300,
				items: dataview,
				autoScroll: true,
				dockedItems: [{
					cls: 'x-toolbar',
					dock: 'top',
					layout: 'hbox',
					defaults: {
						margin: '0 0 0 10'
					},
					margin: 12,
					items: [{
						xtype: 'filterfield',
						margin: 0,
						filterFields: ['name', function(record){
							var team = storeTeams.getById(record.get('teamId'))
							return team ? team.get('name') : '';
						}],
						width: 200,
						store: store
					},{
						xtype: 'tbfill' 
					},{
						itemId: 'refresh',
						xtype: 'button',
						iconCls: 'x-btn-groupacton-refresh',
						ui: 'action',
						tooltip: 'Refresh',
						handler: function() {
							Scalr.data.reload('account.*');
						}
					},{
						itemId: 'add',
						xtype: 'button',
						iconCls: 'x-btn-groupacton-add',
						ui: 'action',
						tooltip: 'Add ACL',
						handler: function(){
							dataview.deselect(form.getForm().getRecord());
							form.loadRecord(store.createModel({}));
						}
					}]
				}]				
			})			
		,{
			cls: 'x-panel-columned-rightcol',
			itemId: 'rightcol',
			flex: 1,
			minWidth:800,
			items: [
				form
			]
		}]

	});
	
	return panel;
});
