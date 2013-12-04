Scalr.regPage('Scalr.ui.account2.teams.view', function (loadParams, moduleParams) {
	var UNASSIGNED_TEAM_ID = 9999999, 
		NEW_TEAM_ID = 0,
		isAccountOwner = Scalr.user['type'] == 'AccountOwner',
		storeTeams = Scalr.data.get('account.teams'),
		storeUsers = Scalr.data.get('account.users'),
		storeGroups = Scalr.data.get('account.groups'),
		storeEnvironments = Scalr.data.get('account.environments');
		
	var canEditTeam = function(team) {
		var res = isAccountOwner;
		if (!Ext.isObject(team)) {
			team = storeTeams.getById(team);
		}
		if (!res) {
			var teamUsers = team.get('users');
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
	
	var reconfigurePage = function(params) {
		if (params.teamId) {
			dataview.deselect(form.getForm().getRecord());
			if (params.teamId == 'new') {
				if (isAccountOwner) {
					panel.down('#add').handler();
				}
			} else {
				panel.down('#teamsLiveSearch').reset();
				var record = store.getById(params.teamId);
				if (record) {
					dataview.select(record);
				}
			}
		}
	};
	
	var store = Ext.create('Scalr.ui.ChildStore', {
		parentStore: storeTeams,
		filterOnLoad: true,
		sortOnLoad: true,
		sorters: [{
			property: 'name',
			transform: function(value){
				return value.toLowerCase();
			}
		}]
	});
	
	var storeTeamUsers = Ext.create('store.store', {
		filterOnLoad: true,
		sortOnLoad: true,
		fields: [
			'id', 'fullname', 'email', 'teamId', 'groups', 'permissions'
		],
		groupField: 'teamId',
		loadTeam: function(team) {
			var users = [],
				teamUsers = team.get('users');
			if (Ext.isEmpty(teamUsers)) {
				teamUsers = null;
			}
			storeUsers.each(function(userRecord){
				var user = {
					id: userRecord.get('id'),
					fullname: userRecord.get('fullname'),
					email: userRecord.get('email')
				};
				user.teamId = UNASSIGNED_TEAM_ID;
				if (teamUsers) {
					for (var j=0, len=teamUsers.length; j<len; j++) {
						if (teamUsers[j].id == user.id) {
							user.teamId = team.get('id');
							user.permissions = teamUsers[j].permissions;
							user.groups = teamUsers[j].groups;
							break;
						}
					}
				}
				users.push(user);
			});
			this.loadData(users);
		}
	});
	
    var dataview = Ext.create('Ext.view.View', {
        deferInitialRefresh: false,
        store: store,
		listeners: {
			boxready: function(){
				reconfigurePage(loadParams);
			}
		},
		cls: 'x-dataview-columned',
        tpl  : Ext.create('Ext.XTemplate',
            '<tpl for=".">',
                '<div class="x-item{[this.canEditTeam(values.id)?\'\': \' x-item-disabled\']}">',
					'<div class="x-item-inner">',
						'<table>',
							'<tr>',
								'<td>',
									'<div class="x-item-title">{name}</div>',
									'<table>',
									'<tr><td class="x-item-param-title">Users:</td><td class="x-item-param-value">{[values.users ? values.users.length : 0]}</td></tr> ',
									'<tr><td class="x-item-param-title">Lead:</td><td class="x-item-param-value">{[this.getTeamOwnerName(values.users)]}</td></tr>',
									'</table>',
								'</td>',
								'<td style="padding-left:10px">',
									'<span class="x-item-param-title">Access to: </span><br/>',
									'{[this.getTeamEnvironmentsList(values.id)]}',
								'</td>',
							'</tr>',
						'</table>',
					'</div>',
                '</div>',
            '</tpl>',
			{
				permissionsManage: moduleParams['permissionsManage'],

				canEditTeam: function(teamId) {
					return canEditTeam(teamId);
				},
				getTeamOwnerName: function(teamUsers){
					var name = '';
					for (var i=0, len=teamUsers.length; i<len; i++) {
						if (teamUsers[i].permissions == 'owner') {
							var userRecord = storeUsers.getById(teamUsers[i].id);
							if (userRecord) {
								name = userRecord.get('fullname');
								if (Ext.isEmpty(name)) {
									name = userRecord.get('email');
								}
							}
							break;
						}
					}
					return name;
				},
				getTeamEnvironmentsList: function(teamId){
					var list = [];
					storeEnvironments.each(function(){
						if (Ext.Array.contains(this.get('teams'), teamId)) {
							list.push(this.get('name'));
						}
					});

					return list.join(', ');
				},
				getTeamGroupsList: function(teamId){
					var list = [];
					storeGroups.query('teamId', teamId).each(function(){
						list.push('<span style="color:#'+this.get('color')+'">'+this.get('name')+'</span>');
					});

					return list.join(', ');
				}
			}			
        ),
		plugins: {
			ptype: 'dynemptytext',
			emptyText: '<div class="title">No teams were found to match your search.</div> Try modifying your search criteria' + (!isAccountOwner ? '.' : ' or <a class="add-link" href="#">creating a new team</a>.'),
			emptyTextNoItems:	'<div class="title">You have no teams under your account.</div>'+
								'Teams let you organize your co-workers\' access to different parts of your infrastructure.<br/>' +
								'Click "<b>+</b>" button to create one.',
			onAddItemClick: function() {
				panel.down('#add').handler();
			}
		},
		loadingText: 'Loading teams ...',
		deferEmptyText: false,

        itemSelector: '.x-item',
        overItemCls : 'x-item-over',
		trackOver: true
    });

	var menuUserGroups = Ext.create('Ext.menu.Menu', {
		cls: 'x-menu-account2-teams-permissions',
		setTeam: function(teamRecord){
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
		},
		setUser: function(btnEl, userRecord) {
			var userGroups = userRecord.get('groups') || [],
				userPermissions = userRecord.get('permissions');
			this.userRecord = userRecord;
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
					if (menuitem.value == 'full') {
						if (checked) {
							menu.userRecord.set('permissions', 'full');
							menu.userRecord.set('groups', null);
							menu.items.each(function(item){
								if (item.value) {
									item.setChecked(item.value == 'full', true);
								}
							});
						} else {
							menu.userRecord.set('permissions', 'groups');
						}
					} else {
						var ids = [];
						menu.userRecord.set('permissions', 'groups');
						menu.items.each(function(item){
							if (item.value == 'full') {
								item.setChecked(false, true);
							} else if (item.checked) {
								ids.push(item.value);
							}
						});
						menu.userRecord.set('groups', ids);
					}
				}
			}
		}
	});
	menuUserGroups.doAutoRender();

	var gridTeamMembers = Ext.create('Ext.grid.Panel', {
		cls: 'x-grid-shadow',
		flex: 1,
		maxWidth: 1100,
		store: storeTeamUsers,
		listeners: {
			viewready: function(){
				var me = this;
				this.setHeight('auto');
				var refreshTeamMembers = function(){
					var record = form.getRecord();
					if (record) {
						menuUserGroups.setTeam(record);
						storeTeamUsers.loadTeam(record);
					}
				}

				storeUsers.on({
					add: function(store, records, index) {
						for (var i=0, len=records.length; i<len; i++) {
							storeTeamUsers.add({
								id: records[i].get('id'),
								fullname: records[i].get('fullname'),
								email: records[i].get('email'),
								teamId: UNASSIGNED_TEAM_ID
							});
						}
					},
					remove: function(store, record){
						storeTeamUsers.remove(storeTeamUsers.getById(record.get('id')));
					},
					update: function(store, record, operation, fields){
						if (operation == Ext.data.Model.EDIT) {
							var teamUser = storeTeamUsers.getById(record.get('id'));
							if (teamUser) {
								teamUser.beginEdit();
								for (var i=0, len=fields.length; i<len; i++) {
									teamUser.set(fields[i], record.get(fields[i]));
								}
								teamUser.endEdit();
							}
						}
					},
					refresh: refreshTeamMembers
				});

				storeGroups.on({
					add: refreshTeamMembers,
					remove: refreshTeamMembers,
					update: refreshTeamMembers,
					refresh: refreshTeamMembers
				});
				form.on('show', function(){
					me.fireEvent('afterlayout');
				})
			},
			afterlayout: function(){//resize grid maxHeight to fit available space
				var maxHeight = this.up('#rightcol').body.getHeight()- form.getHeight() + this.getHeight();
				if (this.maxHeight != maxHeight) {
					this.maxHeight = maxHeight>120?maxHeight:120;
				}
			}
		},
		features: [{
			id:'grouping',
			ftype:'grouping',
			groupHeaderTpl: '{[values.name=='+UNASSIGNED_TEAM_ID+'?"Not in team":"In team"]}'
		}],
		dockedItems: [{
			dock: 'top',
			layout: 'hbox',
			items: [{
				xtype: 'livesearch',
				margin: 0,
				fields: ['fullname', 'email'],
				store: storeTeamUsers,
				listeners: {
					afterfilter: function(){
						//workaround of the extjs grouped store/grid bug
						var grouping = gridTeamMembers.getView().getFeature('grouping');
						gridTeamMembers.suspendLayouts();
						grouping.disable();
						grouping.enable();
						gridTeamMembers.resumeLayouts(true);
					}
				}
			}]
		}],	
		viewConfig: {
			plugins: {
				ptype: 'dynemptytext',
				emptyText: '<div class="title">No users were found to match your search.</div>Try modifying your search criteria or <a href="#/account/users?userId=new">creating a new user</a>'
			},
			loadingText: 'Loading users ...',
			deferEmptyText: false,
			focusedItemCls: 'x-grid-row-nofocused',
			listeners: {
				itemclick: function (view, record, item, index, e) {
					var grid = view.up('panel');
					if (isAccountOwner && e.getTarget('input.team-owner')) {//lead radio button
						if (record.get('permissions') != 'owner') {
							(grid.store.snapshot || grid.store.data).each(function(record) {
								if (record.get('permissions') == 'owner') {
									record.set('permissions', 'groups');
								}
							});
							record.set('permissions', 'owner');
							record.set('groups', null);
						}
					} else if (e.getTarget('img.team-add-remove')) {//user add/remove buttons
						var scrollTop = view.el.getScroll().top;
						grid.suspendLayouts();
						view.getFeature('grouping').disable();//workaround of the extjs grouped store/grid bug
						if (record.get('teamId') != UNASSIGNED_TEAM_ID) {
							record.set('teamId', UNASSIGNED_TEAM_ID);
							record.set('groups', null);
						} else {
							record.set('teamId', form.getForm().getRecord().get('id'));
							record.set('permissions', 'groups');
						}
						view.getFeature('grouping').enable();//workaround of the extjs grouped store/grid bug
						grid.getSelectionModel().deselect(record);
						grid.resumeLayouts(true);
						view.el.scrollTo('top', scrollTop);
					} else if (e.getTarget('.x-grid-row-options')) {
						var btnEl = Ext.get(item).down('div.x-grid-row-options');
						menuUserGroups.setUser(btnEl, record);
					}
				}
			}
		},
		columns: [{
			text: 'Lead',
			width: 65,
			xtype: 'templatecolumn',
			disabled: !isAccountOwner,
			dataIndex: 'permissions',
			resizable: false,
			tpl: '<tpl if="teamId!='+UNASSIGNED_TEAM_ID+'"><div class="<tpl if="permissions == \'owner\'">x-form-cb-checked</tpl>" style="text-align: center"><input type="button" class="x-form-field x-form-radio team-owner"></div></tpl>'
		},{
			text: 'Name', 
			flex: 1, 
			dataIndex: 'fullname', 
			sortable: true
		},{
			text: 'Email', 
			flex: 1, 
			dataIndex: 'email', 
			sortable: true
		},{
			text: 'Access control list', 
			flex: 2, 
			sortable: false,
			xtype: 'templatecolumn',
			tpl: new Ext.XTemplate(
				'<tpl if="values.teamId!='+UNASSIGNED_TEAM_ID+' && (permissions==\'full\' || permissions==\'groups\')">',
					'<div class="x-grid-row-options"><div class="x-grid-row-options-icon"></div><div class="x-grid-row-options-trigger"></div></div>',
				'</tpl>',
				'<tpl if="permissions==\'owner\' || permissions==\'full\'">',
					'<span class="user-permission" style="color:#000">Full access</span>',
				'<tpl elseif="values.teamId!='+UNASSIGNED_TEAM_ID+'">',
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
						var teamGroups = storeGroups.query('teamId', form.getForm().getRecord().get('id'))||[];
						teamGroups.each(function(){
							if (Ext.Array.contains(groups, this.get('id'))) {
								html.push('<span class="user-permission" style="color:#'+this.get('color')+'">'+this.get('name')+'</span>');
							}
						});
					}
					return html.join(', ');
				}
			})
		},{
			width: 40,
			xtype: 'templatecolumn',
			tpl: '<tpl if="permissions != \'owner\'"><img class="team-add-remove" title="{[values.teamId!='+UNASSIGNED_TEAM_ID+'?"Remove "+values.fullname+" from team":"Add "+values.fullname+" to team"]}" src="/ui2/images/icons/{[values.teamId!='+UNASSIGNED_TEAM_ID+' ? "remove_icon_16x16.png" : "add_icon_16x16.png"]}"/></tpl>',
			resizable: false,
			sortable: false
		}]
		
	});
	
	var form = 	Ext.create('Ext.form.Panel', {
		hidden: true,
		fieldDefaults: {
			anchor: '100%'
		},
		listeners: {
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
			hide: function() {
				if (isAccountOwner) {
					dataview.up('panel').down('#add').setDisabled(false);
				}
			},
			beforeloadrecord: function(record) {
				if (!canEditTeam(record)) {
					this.setVisible(false);
					dataview.deselect(record);
					Scalr.message.Error('You do not have sufficient permissions to edit team &ldquo;'+record.get('name')+'&rdquo;');
					return false;
				}
				var form = this.getForm(),
					isNewRecord = record.get('id') == NEW_TEAM_ID;
				form.reset();
				var c = this.query('component[cls~=hideoncreate], #delete');
				for (var i=0, len=c.length; i<len; i++) {
					c[i].setVisible(!isNewRecord);
				}
				this.down('#settings').setTitle(!isNewRecord?'Edit &ldquo;'+record.get('name')+'&rdquo;':'Add team');
				if (isAccountOwner) {
					dataview.up('panel').down('#add').setDisabled(isNewRecord);
				}
			},
			loadrecord: function(record) {
				storeTeamUsers.loadTeam(record);
				menuUserGroups.setTeam(record);
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
			xtype: 'hiddenfield',
			name: 'id'
		},{
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
				items: [{
					xtype: 'textfield',
					hidden: !isAccountOwner,
					name: 'name',
					fieldLabel: 'Name',
					allowBlank: false

				}]
			},{
				items: []
			}]
		}, {
			xtype: 'fieldset',
			title: 'Members',
			items: gridTeamMembers
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
						team = frm.getValues(),
						users = [],
						ownerSelected = false;
					if (frm.isValid()) { 
						storeTeamUsers.queryBy(function(user){
							if (user.get('teamId') == team['id']) {
								ownerSelected = !ownerSelected ? user.get('permissions') == 'owner' : ownerSelected;
								users.push({
									id: user.get('id'),
									permissions: user.get('permissions'),
									groups: user.get('groups')
								})
							}
						});
						if (!ownerSelected) {
							Scalr.message.Error('Select team lead');
							return;
						}
						Scalr.Request({
							url: '/account/teams/xSave',
							processBox: {type: 'save'},
							params: {
								teamId: team['id'],
								teamName: team['name'],
								users: Ext.encode(users)
							},
							success: function (data) {
								var record = frm.getRecord();
								if (team['id'] != NEW_TEAM_ID) {
									record.set(data.team);
									form.loadRecord(record);
								} else {
									record = store.add(data.team)[0];
									dataview.getSelectionModel().select(record);
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
				disabled: !isAccountOwner,
				text: 'Delete',
				handler: function() {
					var record = form.getForm().getRecord();
					Scalr.Request({
						confirmBox: {
							msg: 'Delete team ' + record.get('name') + ' ?',
							type: 'delete'
						},
						processBox: {
							msg: 'Deleting...',
							type: 'delete'
						},
						scope: this,
						url: '/account/teams/xRemove',
						params: {teamId: record.get('id')},
						success: function (data) {
							store.remove(record);
						}
					});
				}
			}]
		}]
	});

	var panel = Ext.create('Ext.panel.Panel', {
		cls: 'x-panel-columned scalr-ui-account-teams',
		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		scalrOptions: {
			title: 'Teams',
			reload: false,
			maximize: 'all',
			leftMenu: {
				menuId: 'account',
				itemId: 'teams'
			}
		},
		scalrReconfigure: function(params){
			reconfigurePage(params);
		},
		items: [{
			cls: 'x-panel-columned-leftcol',
			flex:1,
			maxWidth: 440,
			minWidth: 360,
			items: dataview,
			autoScroll: true,
			dockedItems: [{
				cls: 'x-toolbar',
				dock: 'top',
				layout: 'hbox',
				margin: 12,
				defaults: {
					margin: '0 0 0 10'
				},
				items: [{
					xtype: 'filterfield',
					itemId: 'teamsLiveSearch',
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
					disabled: !isAccountOwner,
					tooltip: 'Add team',
					handler: function(){
						dataview.deselect(form.getForm().getRecord());
						form.loadRecord(store.createModel({id: NEW_TEAM_ID}));
					}
				}]
			}]				
		},{
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
