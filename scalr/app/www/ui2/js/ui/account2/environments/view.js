Scalr.regPage('Scalr.ui.account2.environments.view', function (loadParams, moduleParams) {
	var isAccountOwner = Scalr.user['type'] == 'AccountOwner',
		storeTeams = Scalr.data.get('account.teams'),
		storeEnvironments = Scalr.data.get('account.environments');
		
	var getTeamNames = function(teams, links) {
		var list = [];
		if (teams) {
            if (Scalr.flags['authMode'] == 'ldap') {
                list = teams;
                var len = list.length, maxLen = 2;
                if (len > maxLen) {
                    list = list.slice(0, maxLen);
                    list.push('and ' + (len - list.length) + ' more');
                }
            } else {
                for (var i=0, len=teams.length; i<len; i++) {
                    var record = storeTeams.getById(teams[i]);
                    if (record) {
                        list.push(links?'<a href="#/account/teams?teamId='+record.get('id')+'">'+record.get('name')+'</a>':record.get('name'));
                    }
                }
            }
		}
		return list.join(', ');
	};
	var store = Ext.create('Scalr.ui.ChildStore', {
		parentStore: storeEnvironments,
		filterOnLoad: true,
		sortOnLoad: true,
		sorters: [{
			property: 'name',
			transform: function(value){
				return value.toLowerCase();
			}
		}]
	});
	
	var envTeamsStore = Ext.create('Scalr.ui.ChildStore', {
		parentStore: storeTeams,
		sortOnLoad: true,
		sorters: [{
			property: 'name',
			transform: function(value){
				return value.toLowerCase();
			}
		}]
	});
	var reconfigurePage = function(params) {
		if (params.envId) {
			dataview.deselect(form.getForm().getRecord());
			if (params.envId == 'new') {
				if (isAccountOwner) {
					panel.down('#add').handler();
				}
			} else {
				panel.down('#envLiveSearch').reset();
				var record =  store.getById(params.envId);
				if (record) {
					dataview.select(record);
				}
			}
		}
	};
	
    var dataview = Ext.create('Ext.view.View', {
        deferInitialRefresh: false,
        store: store,
		listeners: {
			boxready: function(){
				reconfigurePage(loadParams);
			}
		},
		cls: 'x-dataview-columned',
        tpl  : new Ext.XTemplate(
            '<tpl for=".">',
                '<div class="x-item">',
					'<div class="x-item-inner">',
						'<table>',
							'<tr>',
								'<td>',
									'<div class="x-item-id">id:&nbsp;{id}</div>',
									'<div class="x-item-title">{name} </div>',
									'{[values.teams && values.teams.length  ? \'<span class="x-item-param-title">Teams: </span><br/>\' : \'\']}',
									'{[this.getTeamNames(values.teams)]}',
								'</td>',
								'<td>',
									'<div class="x-item-status{[values.status == "Active" ? "" : " x-item-status-inactive"]}">{[values.status == \'Active\' ? \'Managed\' : \'Suspended\']}</div>',
									'<span class="x-item-param-title">Enabled clouds: </span><br/>{[values.platforms && values.platforms.length ? "" : "no clouds enabled"]}',
									'<div>{[this.getPlatformNames(values.platforms)]}<div class="x-clear"></div></div>',
								'</td>',
							'</tr>',
						'</table>',
					'</div>',
                '</div>',
            '</tpl>',
			{
				getPlatformNames: function(platforms){
					var list = [];
					if (platforms && platforms.length) {
						for (var i=0, len=platforms.length; i<len; i++) {
							if (moduleParams.platforms[platforms[i]]) {
								list.push(
									'<div class="scalr-ui-icon-platform scalr-ui-icon-platform-' + platforms[i] + ' "title="'+(moduleParams.platforms[platforms[i]])+'"></div>'
								);
							}
						}
						return list.join('');
					}
				},
				getTeamNames: function(teams){
					return getTeamNames(teams);
				}
			}			
			
        ),
		plugins: {
			ptype: 'dynemptytext',
			emptyText: '<div class="title">No environments were found<br/> to match your search.</div>Try modifying your search criteria'+ (!isAccountOwner ? '.' : '<br/>or <a class="add-link" href="#">creating a new environment</a>.'),
			onAddItemClick: function() {
				panel.down('#add').handler();
			}
		},
		loadingText: 'Loading environments ...',
		deferEmptyText: true,

        itemSelector: '.x-item',
        overItemCls : 'x-item-over',
		trackOver: true
    });

	var form = 	Ext.create('Ext.form.Panel', {
		hidden: true,
		minWidth: 680,
		fieldDefaults: {
			anchor: '100%'
		},
		listeners: {
			hide: function() {
				if (isAccountOwner) {
					dataview.up('panel').down('#add').setDisabled(false);
				}
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
				var c = this.query('component[cls~=hideoncreate], #delete');
				for (var i=0, len=c.length; i<len; i++) {
					c[i].setVisible(!isNewRecord);
				}
				this.down('#settings').setTitle(!isNewRecord?'Edit &ldquo;'+record.get('name')+'&rdquo;':'Add environment');
				if (isAccountOwner) {
					dataview.up('panel').down('#add').setDisabled(isNewRecord);
				}
				this.down('#delete').setDisabled(storeEnvironments.getCount()>1?false:true);
				if (this.down('#envTeamNames')) {
					this.down('#envTeamNames').setValue(getTeamNames(record.get('teams'), true));
				}
			},
			loadrecord: function(record) {
				envTeamsStore.loadData(storeTeams.getRange());
				if (record.get('id')) {
					var platforms = record.get('platforms');
					form.down('#platforms').items.each(function(){
						var platformEnabled = Ext.Array.contains(platforms, this.platform);
						this[(platformEnabled ? 'addCls' : 'removeCls')]('scalr-ui-environment-cloud-btn-pressed');
						this.icon.update('<img src="' + '/ui2/images/icons/platform/' + this.platform + (platformEnabled ? '' : '_disabled') + '_89x64.png' + '" />');
					});
				}
				if (!this.isVisible()) {
					this.setVisible(true);
				}
			}
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
				xtype: isAccountOwner ? 'textfield' : 'displayfield',
				name: 'name',
				fieldLabel: 'Environment',
				allowBlank: false
			}, {
				xtype: 'buttongroupfield',
				fieldLabel: 'Status',
				margin: '0 0 0 40',
				labelWidth: 60,
				name: 'status',
				value: 'Active',
				items: [{
					text: 'Managed',
					value: 'Active',
					width: 90
				}, {
					text: 'Suspended',
					value: 'Inactive',
					width: 90
				}]
			}]
		}, {
			xtype: 'fieldset',
			title: 'Clouds',
			itemId: 'platforms',
			cls: 'hideoncreate'
		},{
			xtype: 'fieldset',
			title: Scalr.flags['authMode'] == 'ldap' ? 'Accessible by LDAP groups (comma separated)' : 'Accessible by',
			items: [isAccountOwner ? Scalr.flags['authMode'] == 'ldap' ? {
                xtype: 'accountauthldapfield',
                name: 'teams'
            } : {
				xtype: 'gridfield',
				name: 'teams',
				flex: 1,
				cls: 'x-grid-shadow x-grid-norowselect',
				maxWidth: 1100,
				disabled: !isAccountOwner,
				listeners: {
					viewready: function(){
						var me = this;
						this.reconfigure(envTeamsStore);
						this.setHeight('auto');
						form.on('show', function(){
							me.fireEvent('afterlayout');
						})
					},
					afterlayout: function(){//resize grid maxHeight to fit available space
						var maxHeight = this.up('#rightcol').body.getHeight()- form.getHeight() + this.getHeight();
						if (this.maxHeight != maxHeight) {
							this.maxHeight = maxHeight>90?maxHeight:90;
						}
					}
				},
				viewConfig: {
					focusedItemCls: '',
					overItemCls: '',
					plugins: {
						ptype: 'dynemptytext',
						emptyText: 'No teams were found.'+ (!isAccountOwner ? '' : ' Click <a href="#/account/teams?teamId=new">here</a> to create new team.')
					}
				},
				columns: [
					{text: 'Team name', flex: 1, dataIndex: 'name', sortable: true},
					{text: 'Users', width: 120, dataIndex: 'users', sortable: false, xtype: 'templatecolumn', tpl: '<tpl if="users.length"><a href="#/account/teams?teamId={id}">{users.length}</a></tpl>'},
					{
						text: 'Other environments',
						flex: 1,
						sortable: false,
						xtype: 'templatecolumn',
						tpl: new Ext.XTemplate(
							'{[this.getOtherEnvList(values.id)]}',
						{
							getOtherEnvList: function(teamId){
								var envs = [],
									envId = form.getRecord().get('id');
								storeEnvironments.each(function(){
									var envTeams = this.get('teams');
									if (envTeams && envId != this.get('id')) {
										for (var i=0, len=envTeams.length; i<len; i++) {
											if (teamId == envTeams[i]) {
												envs.push(this.get('name'));
												break;
											}

										}
									}
								});
								return envs.join(', ');
							}
						})
					}
				]
			} : {
				xtype: 'displayfield',
				itemId: 'envTeamNames'
			}]
		}],
		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			cls: 'x-toolbar x-docked-light',
			maxWidth: 1100,
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				itemId: 'save',
				xtype: 'button',
				text: 'Save',
				handler: function() {
					var frm = form.getForm();
					if (frm.isValid()) {
						var record = frm.getRecord();
						Scalr.Request({
							processBox: {
								type: 'save'
							},
							url: '/account/environments/xSave',
							form: frm,
							params: !record.get('id')?{}:{envId: record.get('id')},
							success: function (data) {
								if (!record.get('id')) {
									record = store.add(data.env)[0];
									dataview.getSelectionModel().select(record);
									Scalr.event.fireEvent('update', '/account/environments/create', data.env);
								} else {
									record.set(data.env);
									form.loadRecord(record);
									Scalr.event.fireEvent('update', '/account/environments/rename', data.env);
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
				disabled: !isAccountOwner,
				handler: function() {
					var record = form.getForm().getRecord();
					Scalr.Request({
						confirmBox: {
							msg: 'Delete environment ' + record.get('name') + ' ?',
							type: 'delete'
						},
						processBox: {
							msg: 'Deleting...',
							type: 'delete'
						},
						scope: this,
						url: '/account/environments/xRemove',
						params: {envId: record.get('id')},
						success: function (data) {
							Scalr.event.fireEvent('update', '/account/environments/delete', {id: record.get('id')});
							record.store.remove(record);
							if (data['flagReload'])
								Scalr.application.updateContext();
						}
					});
				}
			}]
		}]
	});

	for (var i in moduleParams['platforms']) {
		form.down('#platforms').add({
			xtype: 'custombutton',
			width: 90,
			height: 85,
			cls: 'scalr-ui-environment-cloud-btn',
			childEls: [ 'icon' ],
			renderTpl:
				'<div class="x-btn-inner" id="{id}-btnEl" title="{title}">' +
					'<div id="{id}-icon" class="x-btn-icon"><img src="{icon}"></div>' +
					'<div class="x-btn-name">{name}</div>' +
				'</div>',
			renderData: {
				title: moduleParams['platforms'][i],
				name: moduleParams['platforms'][i].replace(/^rackspace open cloud \((.+)\)$/i, '<div style="margin-top:-5px;line-height:11px">Rackspace <br/> Open Cloud ($1)</div>'),
				icon: '/ui2/images/icons/platform/' + i + '_disabled_89x64.png'
			},
			platform: i,
			handler: function () {
				Scalr.event.fireEvent('redirect', '#/account/environments/' + form.getForm().getRecord().get('id') + '/platform/' + this.platform, true);
			}
		});
	};
	
	Scalr.event.on('update', function (type, envId, platform, enabled) {
		if (type == '/account/environments/edit') {
			if (form.isVisible()) {
				if (envId == form.getForm().getRecord().get('id')) {
					var b = form.down('#platforms').down('[platform="' + platform + '"]');
					if (b) {
						b[(enabled ? 'addCls' : 'removeCls')]('scalr-ui-environment-cloud-btn-pressed');
						b.icon.update('<img src="' + '/ui2/images/icons/platform/' + platform + (enabled ? '' : '_disabled') + '_89x64.png' + '" />');
					}
				}
			}
			var record = store.getById(envId);
			if (record) {
				var platforms = record.get('platforms') || [];
				if (!enabled){
					Ext.Array.remove(platforms, platform);
				} else if (!Ext.Array.contains(platforms, platform)) {
					platforms.push(platform);
				}
				record.set('platforms', platforms);
				store.fireEvent('refresh');
			}
		}
	}, form);
	
	
	var panel = Ext.create('Ext.panel.Panel', {
		cls: 'x-panel-columned scalr-ui-account-env',
		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		scalrOptions: {
			title: 'Environments',
			reload: false,
			maximize: 'all',
			leftMenu: {
				menuId: 'account',
				itemId: 'environments'
			}
		},
		scalrReconfigure: function(params){
			reconfigurePage(params);
		},
		items: [
			Ext.create('Ext.panel.Panel', {
				cls: 'x-panel-columned-leftcol',
				width: 440,
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
						itemId: 'envLiveSearch',
						margin: 0,
						filterFields: ['name'],
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
						tooltip: 'Add environment',
						disabled: !isAccountOwner,
						handler: function(){
							dataview.deselect(form.getForm().getRecord());
							form.loadRecord(store.createModel({status: 'Active'}));
						}
					}]
				}]				
			})			
		,{
			cls: 'x-panel-columned-rightcol',
			itemId: 'rightcol',
			flex: 1,
			items: [
				form
			],
			autoScroll: true
		}]	
	});
	return panel;
});

Ext.define('Scalr.ui.AccountEnvironmentAuthLdap', {
    extend: 'Ext.form.field.TextArea',
    alias: 'widget.accountauthldapfield',

    setValue: function(value) {
        return this.callParent([ Ext.isArray(value) ? value.join(', ') : value ]);
    },

    getValue: function() {
        var value = this.callParent(arguments);
        return value.split(',');
    },

    getSubmitData: function() {
        var me = this,
            data = null;
        if (!me.disabled && me.submitValue) {
            data = {};
            data[me.getName()] = Ext.encode(me.getValue());
        }
        return data;
    }
});
