Scalr.regPage('Scalr.ui.account2.users.view', function (loadParams, moduleParams) {
	var storeTeams = Scalr.data.get('account.teams'),
		storeUsers = Scalr.data.get('account.users'),
		storeGroups = Scalr.data.get('account.groups'),
		isAccountOwner = Scalr.user['type'] == 'AccountOwner',
		readOnlyMode = Scalr.user['type'] != 'AccountOwner' && !Scalr.user['isTeamOwner'];
	
	var getUserTeamsList = function(userId, links) {
		var teams = [];
		if (userId) {
			storeTeams.each(function(record){
				var teamUsers = record.get('users');
				if (teamUsers) {
					for (var i=0, len=teamUsers.length; i<len; i++) {
						if (teamUsers[i].id == userId) {
							var teamName = record.get('name');
							if (teamUsers[i].permissions == 'owner') {teamName = '<span style="color:green;white-space:nowrap" title="Team lead">'+teamName+'&nbsp;<img  src="/ui2/images/ui/account/lead.png" /></span>'};
							teams.push(links ? '<a href="#/account/teams?teamId='+record.get('id')+'">'+teamName+'</a>' : teamName);
							break;
						}
					}
				}
			});
		}
		return teams.join(', ');
	};

	var reconfigurePage = function(params) {
		if (params.userId) {
			var selModel = grid.getSelectionModel();
			selModel.deselectAll()
			if (params.userId == 'new') {
				if (!readOnlyMode) {
					panel.down('#add').handler();
				}
			} else {
				panel.down('#usersLiveSearch').reset();
				var record = store.getById(params.userId);
				if (record) {
					selModel.select(record);
				}
			}
		}
	};

	var store = Ext.create('Scalr.ui.ChildStore', {
		parentStore: storeUsers,
		filterOnLoad: true,
		sortOnLoad: true
	});
	
	var storeUserTeams = Ext.create('store.store', {
		filterOnLoad: true,
		sortOnLoad: true,
		fields: [
			{name: 'id', type: 'string'}, 'name', 'permissions', 'groups', 'readonly'
		],
		loadUser: function(user) {
			var userId = user.get('id'),
				teams = [],
				canEditTeam;
			storeTeams.each(function(teamRecord){
				canEditTeam = isAccountOwner;
				var team = {
					id: teamRecord.get('id'),
					name: teamRecord.get('name'),
					permissions: null,
					groups: [],
					readonly: false
				},
				teamUsers = teamRecord.get('users');
				if (teamUsers) {
					for (var i=0, len=teamUsers.length; i<len; i++) {
						if (teamUsers[i].id == userId) {
							team.permissions = teamUsers[i].permissions;
							team.groups = teamUsers[i].groups ? teamUsers[i].groups : [];
						}
						if (Scalr.user.userId == teamUsers[i].id && teamUsers[i].permissions == 'owner') {
							canEditTeam = true;
						}
					}
				}
				team.readonly = !canEditTeam;
				teams.push(team);
			});
			this.loadData(teams);
		},
		listeners: {
			update: function() {
				var teams = [];
				this.data.each(function() {
					if (this.get('permissions') != null) {
						var teamName = this.get('name');
						if (this.get('permissions') == 'owner') {teamName = '<span style="color:green;white-space:nowrap" title="Team lead">'+teamName+'&nbsp;<img src="/ui2/images/ui/account/lead.png" /></span>'};
						teams.push('<a href="#/account/teams?teamId='+this.get('id')+'">'+teamName+'</a>');
					}
				});
				form.down('#userTeams').setValue(teams.join(', ') + ' <a href="#" class="user-teams-edit">Change</a>');
			}
		}
	});
	
	var menuUserGroups = Ext.create('Ext.menu.Menu', {
		cls: 'x-menu-account2-teams-permissions',
		setTeam: function(btnEl, teamRecord){
			var items = [],
				teamGroups = storeGroups.query('teamId', teamRecord.get('id'));
			items.push({
				xtype: 'menucheckitem',
				text: 'Full access',
				value: 'full'
			});
			if (moduleParams['permissionsManage'] && teamGroups.length) {
				items.push({xtype: 'menuseparator'});
				teamGroups.each(function(){
					items.push({
						xtype: 'menucheckitem',
						text: '<span style="color:#'+this.get('color')+'">'+this.get('name')+'</span>',
						value: this.get('id')
					});
				});
			}
			
			this.removeAll();
			this.add(items);
			
			var userGroups = teamRecord.get('groups') || [],
				userPermissions = teamRecord.get('permissions');
			this.teamRecord = teamRecord;
			this.items.each(function(item){
				if (!item.value) return true;//bypass separator
				if (item.value == 'full') {
					item.setChecked(userPermissions == 'full', true);
				} else {
					var checked = false;
					for (var i=0, len=userGroups.length; i<len; i++) {
						if (userGroups[i] == item.value) {
							checked = true;
							break;
						}
					}
					item.setChecked(checked, true);
				}
			});
			
			var xy = btnEl.getXY(), sizeX = xy[1] + btnEl.getHeight() + this.getHeight();
			if (sizeX > Scalr.application.getHeight()) {
				xy[1] -= sizeX - Scalr.application.getHeight();
			}
			this.show().setPosition([xy[0] - (this.getWidth() - btnEl.getWidth()), xy[1] + btnEl.getHeight() + 1]);
		},
		defaults: {
			listeners: {
				checkchange: function(menuitem, checked) {
					var menu = menuitem.parentMenu;
					var scrollTop = form.down('#userTeamsGrid').view.el.getScroll().top;
					if (menuitem.value == 'full') {
						if (checked) {
							menu.teamRecord.set('permissions', 'full');
							menu.teamRecord.set('groups', []);
							menu.items.each(function(item){
								if (item.value) {
									item.setChecked(item.value == 'full', true);
								}
							});
						} else {
							menu.teamRecord.set('permissions', 'groups');
						}
					} else {
						var ids = [];
						menu.teamRecord.set('permissions', 'groups');
						menu.items.each(function(item){
							if (item.value == 'full') {
								item.setChecked(false, true);
							} else if (item.checked) {
								ids.push(item.value);
							}
						});
						menu.teamRecord.set('groups', ids);
					}
					form.down('#userTeamsGrid').view.el.scrollTo('top', scrollTop);
				}
			}
		}
	});
	menuUserGroups.doAutoRender();
	
	
	var grid = Ext.create('Ext.grid.Panel', {
		cls: 'x-panel-columned-leftcol x-grid-shadow',
		padding: 9,
		flex: 1,
		multiSelect: true,
		selType: readOnlyMode ? 'rowmodel' : 'selectedmodel',
		store: store,
		stateId: 'grid-account-users-view',
		plugins: readOnlyMode?[]:['rowpointer'],
		listeners: {
			viewready: function() {
				reconfigurePage(loadParams);
			},
			selectionchange: function(selModel, selected) {
				if (!readOnlyMode) {
					this.down('#delete').setDisabled(!selected.length);
					this.down('#activate').setDisabled(!selected.length);
					this.down('#deactivate').setDisabled(!selected.length);
				}
			}
		},
		viewConfig: {
			plugins: {
				ptype: 'dynemptytext',
				emptyText: '<div class="title">No users were found to match your search.</div>Try modifying your search criteria'+ (readOnlyMode ? '' : ' or <a class="add-link" href="#">creating a new user</a>'),
				onAddItemClick: function() {
					grid.down('#add').handler();
				}
			},
			loadingText: 'Loading users ...',
			deferEmptyText: false
		},

		columns: [
			{text: 'Name', flex: 1, dataIndex: 'fullname', sortable: true},
			{text: Scalr.flags['authMode'] == 'ldap' ? 'LDAP login' : 'Email', flex: 1, dataIndex: 'email', sortable: true},
			{text: 'Teams', flex: 1, dataIndex: 'id', sortable: false, xtype: 'templatecolumn', hidden: (Scalr.flags['authMode'] == 'ldap'), tpl:
				new Ext.XTemplate(
				'{[this.getUserTeamsList(values.id)]}',
				{
					getUserTeamsList: function(userId){
						return getUserTeamsList(userId);
					}
				}			
			)},
			{text: 'Last login',  width: 150, dataIndex: 'dtlastlogin', sortable: true, xtype: 'templatecolumn', tpl: '{dtlastloginhr}'},
			{ text: 'Status', width: 90, dataIndex: 'status', sortable: true, xtype: 'templatecolumn', tpl:
				'<span ' +
				'<tpl if="status == &quot;Active&quot;">' +
				'<span style="color: green">{status}</span>' +
				'<tpl else>' +
				'<span style="color: red">Suspended</span>' +
				'</tpl>'
			}
			
		],
		dockedItems: [{
			cls: 'x-toolbar',
			dock: 'top',
			layout: 'hbox',
			defaults: {
				margin: '0 0 0 10',
				handler: function() {
					var action = this.getItemId(),
						actionMessages = {
							'delete': ['Delete selected user(s): %s ?', 'Deleting selected users(s) ...'],
							activate: ['Activate selected user(s): %s ?', 'Activating selected users(s) ...'],
							deactivate: ['Deactivate selected user(s): %s ?', 'Deactivating selected users(s) ...']
						},
						selModel = grid.getSelectionModel(),
						ids = [], 
						emails = [],
						request = {};
					for (var i=0, records = selModel.getSelection(), len=records.length; i<len; i++) {
						ids.push(records[i].get('id'));
						emails.push(records[i].get('email'));
					}
					
					request = {
						confirmBox: {
							msg: actionMessages[action][0],
							type: action,
							objects: emails
						},
						processBox: {
							msg: actionMessages[action][1],
							type: action
						},
						params: {ids: ids, action: action},
						success: function (data) {
							if (data.processed && data.processed.length) {
								switch (action) {
									case 'activate':
									case 'deactivate':
										for (var i=0,len=data.processed.length; i<len; i++) {
											var record = store.getById(data.processed[i]);
											record.set('status', action=='deactivate'?'Inactive':'Active');
											selModel.deselect(record);
										}
									break;
									case 'delete':
										var recordsToDelete = [];
										for (var i=0,len=data.processed.length; i<len; i++) {
											recordsToDelete[i] = store.getById(data.processed[i]);
											selModel.deselect(recordsToDelete[i]);
										}
										store.remove(recordsToDelete);
									break;
								}
							}
							selModel.refreshLastFocused();
						}
					};
					request.url = '/account/users/xGroupActionHandler';
					request.params.ids = Ext.encode(ids);
					
					Scalr.Request(request);
				}
			},
			items: [{
				xtype: 'filterfield',
				itemId: 'usersLiveSearch',
				margin: 0,
				filterFields: ['fullname', 'email'],
				handler: null,
				store: store
			},{
				xtype: 'tbfill' 
			},{
				itemId: 'activate',
				xtype: 'button',
				iconCls: 'x-btn-groupacton-activate',
				ui: 'action',
				disabled: true,
				tooltip: 'Activate selected users'
			},{
				itemId: 'deactivate',
				xtype: 'button',
				iconCls: 'x-btn-groupacton-deactivate',
				ui: 'action',
				disabled: true,
				tooltip: 'Deactivate selected users'
			},{
				itemId: 'delete',
				xtype: 'button',
				iconCls: 'x-btn-groupacton-delete',
				ui: 'action',
				disabled: true,
				tooltip: 'Delete selected users'
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
				disabled: readOnlyMode,
				tooltip: 'Add profile',
				handler: function() {
					grid.getSelectionModel().setLastFocused(null);
					form.loadRecord(store.createModel({status: 'Active', password: false}));
				}
			}]
		}]
	});
	
	var form = 	Ext.create('Ext.form.Panel', {
		cls: 'scalr-ui-account2-edituser-form',
		hidden: true,
		fieldDefaults: {
			anchor: '100%'
		},
		listeners: {
			hide: function() {
				grid.down('#add').setDisabled(false);
			},
			afterrender: function() {
				var me = this;
				if (!readOnlyMode) {
					grid.getSelectionModel().on('focuschange', function(gridSelModel){
						if (gridSelModel.lastFocused) {
							me.loadRecord(gridSelModel.lastFocused);
						} else {
							me.setVisible(false);
						}
					});
				}
			},
			beforeloadrecord: function(record) {
				var form = this.getForm(),
					isNewRecord = !record.get('id'),
					currentRecord = form.getRecord(),
					wasNewRecord = currentRecord ? !currentRecord.get('id') : true;

				form.reset();
				var c = this.query('component[cls~=hideoncreate], #delete');
				for (var i=0, len=c.length; i<len; i++) {
					c[i].setVisible(!isNewRecord);
				}
				this.down('#formtitle').setText(!isNewRecord?'Edit profile: &ldquo;'+record.get(!Ext.isEmpty(record.get('fullname'))?'fullname':'email')+'&rdquo;':'Add profile:', false);
				grid.down('#add').setDisabled(isNewRecord);
				
				var gridTeams = this.down('grid');
				if (isNewRecord) {
					if (this.layout.done) {
						gridTeams.expand();
					} else {
						this.on('afterlayout', function(){
							gridTeams.expand()
						}, gridTeams, {single: true})
					}
				} else if (wasNewRecord) {
					gridTeams.collapse();
				}
			},
			loadrecord: function(record) {
				if (!this.isVisible()) {
					this.setVisible(true);
				}
				this.down('#userTeams').setValue(getUserTeamsList(record.get('id'), true)+ ' <a href="#" class="user-teams-edit">Change</a>');
				storeUserTeams.loadUser(record);

				this.down('#avatar').setSrc();
				if (record.get('gravatarhash')) {
					this.down('#avatar').setSrc(Scalr.utils.getGravatarUrl(record.get('gravatarhash'), 'large'));
				}
			}
		},
		items: [{
			xtype: 'fieldset',
			items: [{
				itemId: 'formtitle',
				xtype: 'label',
				cls: 'x-fieldset-header',
				style: 'display:block;padding:0 0 5px 0;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;margin-right:50px',
				text: '&nbsp;'
			},{
				xtype: 'image',
				cls: 'hideoncreate',
				style: 'position:absolute;right:32px;top:16px;border-radius:4px',
				width: 46,
				height: 46,
				itemId: 'avatar'
			},{
				xtype: 'displayfield',
				cls: 'hideoncreate',
				fieldLabel: 'ID',
				name: 'id',
				submitValue: true
			},{
				xtype: 'textfield',
				name: 'fullname',
				fieldLabel: 'Full name'
			},{
				xtype: 'textfield',
				name: 'email',
				fieldLabel: Scalr.flags['authMode'] == 'ldap' ? 'LDAP login' : 'Email',
				allowBlank: false,
				vtype: Scalr.flags['authMode'] == 'ldap' ? '' : 'email'
			}, {
				xtype: 'passwordfield',
				name: 'password',
				fieldLabel: 'Password',
                hidden: Scalr.flags['authMode'] == 'ldap',
				emptyText: 'Leave blank to let user specify password',
				allowBlank: true
			},{
				xtype: 'displayfield',
				itemId: 'userTeams',
				fieldLabel: 'Teams',
				cls: 'scalr-ui-account-user-teams-list',
                hidden: Scalr.flags['authMode'] == 'ldap',
				listeners: {
					afterrender: {
						fn: function(){
							var me = this;
							this.mon(this.el, 'click', function(e) {
								var el = me.el.query('a.user-teams-edit');
								if (el.length && e.within(el[0])) {
									form.down('grid').toggleCollapse();
									e.preventDefault();
								}
							})
						}, opt: {single: true}
					}
				}
			},{
				xtype: 'grid',
				collapsible: true,
				collapsed: true,
				collapseMode: 'mini',
				animCollapse: false,
				header: false,
				cls: 'x-grid-shadow',
				itemId: 'userTeamsGrid',
				store: storeUserTeams,
				margin: '0 0 10 0',
				viewConfig: {
					focusedItemCls: 'x-grid-row-nofocused',
					plugins: {
						ptype: 'dynemptytext',
						emptyText: 'No teams were found.'+ (!isAccountOwner ? '' : ' Click <a href="#/account/teams?teamId=new">here</a> to create new team.')
					},
					listeners: {
						itemclick: function (view, record, item, index, e) {
							var grid = view.up('panel');
							if (record.get('readonly')) return;
							if (e.getTarget('input.team-member')) {
								var scrollTop = grid.view.el.getScroll().top;
								if (record.get('permissions') == null) {
									record.set({
										permissions: 'groups',
										groups: []
									});
								} else if (record.get('permissions') != 'owner') {
									record.set({
										permissions: null,
										groups: []
									});
								}
								grid.view.el.scrollTo('top', scrollTop);
							} else if (e.getTarget('.x-grid-row-options')) {
								var btnEl = Ext.get(item).down('div.x-grid-row-options');
								menuUserGroups.setTeam(btnEl, record);
							}
						}
					}
				},
				columns: [{
					text: 'In team',
					width: 65,
					xtype: 'templatecolumn',
					dataIndex: 'permissions',
					resizable: false,
					sortable: false,
					tpl: '<div class="<tpl if="permissions !== null">x-form-cb-checked</tpl><tpl if="permissions == \'owner\' || readonly"> x-item-disabled</tpl>" style="text-align: center"><input type="button" class="x-form-field x-form-checkbox team-member"></div>'
				},{
					text: 'Name', 
					flex: 1, 
					resizable: false,
					sortable: false,
					dataIndex: 'name'
				},{
					text: 'ACL', 
					flex: 2, 
					resizable: false,
					sortable: false,
					xtype: 'templatecolumn',
					tpl: new Ext.XTemplate(
						'<tpl if="permissions!=\'owner\' && !readonly">',
							'<div class="x-grid-row-options"><div class="x-grid-row-options-icon"></div><div class="x-grid-row-options-trigger"></div></div>',
						'</tpl>',
						'<tpl if="permissions==\'owner\'">',
							'<span class="user-permission" style="color:#000">Team lead</span>',
						'<tpl elseif="permissions==\'full\'">',
							'<span class="user-permission" style="color:#000">Full access</span>',
						'<tpl else>',
							'<tpl if="this.permissionsManage">',
								'<div data-qtip="{[Ext.htmlEncode(this.getGroupsList(values.groups))]}" style="text-overflow:ellipsis;overflow:hidden">{[this.getGroupsList(values.groups)]}&nbsp;</div>',
							'<tpl else>',
								'&nbsp;',
							'</tpl>',
						'</tpl>',
					{
						permissionsManage: moduleParams['permissionsManage'],
						getGroupsList: function(groups){
							var html = [];
							if (this.permissionsManage && groups) {
								for (var i=0, len=groups.length; i<len; i++) {
									var group = storeGroups.getById(groups[i]);
									if (group) {
										html.push('<span class="user-permission" style="color:#'+group.get('color')+'">'+group.get('name')+'</span>');
									}
								}
							}
							return html.join(', ');
						}
					})
				}],
				listeners: {
					viewready: function(){
						var me = this;
						this.setHeight('auto');
						
						var refreshUserTeams = function(){
							var record = form.getRecord();
							if (record) {
								storeUserTeams.loadUser(record);
							}
						}

						storeGroups.on({
							add: refreshUserTeams,
							remove: refreshUserTeams,
							update: refreshUserTeams,
							refresh: refreshUserTeams
						});
						
						form.on('show', function(){
							me.fireEvent('afterlayout');
						})
					},
					afterlayout: function(){//resize grid maxHeight to fit available space
						var maxHeight = this.up('#rightcol').body.getHeight()- form.getHeight() + this.getHeight();
						if (this.maxHeight != maxHeight) {
							this.maxHeight = maxHeight>80?maxHeight:80;
						}
					},
					expand: function(){
						form.down('#userTeams').addCls('hide-edit-teams-button');
						this.fireEvent('afterlayout');
					},
					collapse: function(){
						form.down('#userTeams').removeCls('hide-edit-teams-button');
					}
				},
				dockedItems: [{
					cls: 'x-toolbar',
					dock: 'top',
					overlay: true,
					layout: {
						type: 'hbox',
						pack: 'end'
					},
					margin: 0,
					padding: '6 12 6 0',
					style: 'z-index:2',
					items: {
						xtype: 'button',
						iconCls: 'x-btn-groupacton-close',
						ui: 'action',
						tooltip: 'Close',
						handler: function() {
							form.down('grid').collapse();
						}
					}
				}]
				
			
			},{
				xtype: 'displayfield',
				cls: 'hideoncreate',
				name: 'dtcreated',
				fieldLabel: 'User added'
			},{
				xtype: 'displayfield',
				cls: 'hideoncreate',
				name: 'dtlastlogin',
				fieldLabel: 'Last login'
			},{
				xtype: 'buttongroupfield',
				fieldLabel: 'Status',
				name: 'status',
				value: 'Active',
				items: [{
					text: 'Active',
					value: 'Active',
					width: 90
				}, {
					text: 'Suspended',
					value: 'Inactive',
					width: 90
				}]
			}, {
				xtype: 'textarea',
				name: 'comments',
				fieldLabel: 'Comments',
				labelAlign: 'top',
				grow: true,
				growMax: 400,
				anchor: '100%'
			}]
		}],
		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			cls: 'x-toolbar x-docked-light',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				itemId: 'save',
				xtype: 'button',
				text: 'Save',
				handler: function () {
					var frm = form.getForm(),
						record = frm.getRecord();
					if (frm.isValid()) {
						var teams = {};
						storeUserTeams.data.each(function(){
							teams[this.get('id')] = {
								permissions: this.get('permissions'),
								groups: this.get('groups')
							};
						});
						Scalr.Request({
							processBox: {
								type: 'save'
							},
							url: '/account/users/xSave',
							form: frm,
							params: {
								teams: Ext.encode(teams)
							},
							success: function (data) {
								var isNewRecord = !record.get('id'),
									scrollTop = grid.view.el.getScroll().top;
								grid.getSelectionModel().setLastFocused(null);
								form.setVisible(false);
								if (isNewRecord) {
									record = store.add(data.user)[0];
								} else {
									record.set(data.user);
								}
								storeTeams.suspendEvents();
								storeTeams.data.each(function(){
									var teamUsers = this.get('users'),
										newTeamUsers = [],
										teamId = this.get('id'),
										userId = record.get('id');
									if (teamUsers) {
										for (var i=0, len=teamUsers.length; i<len; i++) {
											if (teamUsers[i].id != userId) {
												newTeamUsers.push(teamUsers[i]);
											}
										}
									}
									if (data.teams && data.teams[teamId]) {
										newTeamUsers.push({
											id: userId,
											permissions: data.teams[teamId].permissions,
											groups: data.teams[teamId].groups
										});
									}
									this.set('users', newTeamUsers);
								});
								storeTeams.resumeEvents();
								Scalr.data.fireRefresh(['account.users', 'account.teams']);
								grid.view.el.scrollTo('top', scrollTop);
								if (isNewRecord) {
									grid.getSelectionModel().select(record);
								} else {
									grid.getSelectionModel().setLastFocused(record);
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
					grid.getSelectionModel().setLastFocused(null);
					form.setVisible(false);
					form.down('grid').collapse();
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
							msg: 'Delete user ' + record.get('email') + ' ?',
							type: 'delete'
						},
						processBox: {
							msg: 'Deleting...',
							type: 'delete'
						},
						scope: this,
						url: '/account/users/xRemove',
						params: {userId: record.get('id')},
						success: function (data) {
							record.store.remove(record);
							grid.getSelectionModel().setLastFocused(null);
						}
					});
				}
			}]
		}]
	});


	var panel = Ext.create('Ext.panel.Panel', {
		cls: 'x-panel-columned scalr-ui-account-users',
		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		scalrOptions: {
			title: 'Users',
			reload: false,
			maximize: 'all',
			leftMenu: {
				menuId: 'account',
				itemId: 'users'
			}
		},
		scalrReconfigure: function(params){
			reconfigurePage(params);
		},
		items: [
			grid
		,{
			cls: 'x-panel-columned-rightcol',
			itemId: 'rightcol',
			flex: .6,
			maxWidth: 520,
			minWidth: 400,
			items: [
				form
			]
		}]	
	});
	return panel;
});
