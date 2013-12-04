Scalr.data.add([{
	name: 'account.users',
	dataUrl: '/account/xGetData',
	dataLoaded: false,
	fields: [
		{name: 'id', type: 'string'}, 
		'status', 
		'email', 
		'fullname', 
		'dtcreated', 
		{
			name: 'dtlastlogin', 
			sortType: function(s) {
				if(!s) return 0;
				var res;
				if(Ext.isDate(s)){
					res = s.getTime();
				}
				res = Date.parse(String(s)) || 0; 
				return res;
			}
		},
		'dtlastloginhr',
		'comments', 
		'is2FaEnabled', 
		'password',
		'gravatarhash'
	],
	listeners: {
		update: function(store, record, operation, fields){
			Scalr.data.fireRefresh('account.teams');
		},
		add: function(){
			Scalr.data.fireRefresh('account.teams');
		},
		remove: function(store, record){
			var userId = record.get('id'),
				teams = Scalr.data.get('account.teams');
			if (teams) {
				teams.each(function(teamRecord){
					var teamUsers = teamRecord.get('users');
					if (teamUsers) {
						var newTeamUsers = [];
						for (var i=0, len=teamUsers.length; i<len; i++) {
							if (teamUsers[i].id != userId) {
								newTeamUsers.push(teamUsers[i]);
							}
						}
						teamRecord.set('users', newTeamUsers);
					}
				});
			}
		}
	}
},{
	name: 'account.teams',
	dataUrl: '/account/xGetData',
	dataLoaded: false,
	fields: [{name: 'id', type: 'string'}, 'name', 'users'],
	listeners: {
		update: function(){
			Scalr.data.fireRefresh(['account.users', 'account.groups', 'account.environments']);
		},
		add: function(){
			Scalr.data.fireRefresh('account.users');
		},
		remove: function(store, record){
			var teamId = record.get('id'),
				groups = Scalr.data.get('account.groups'),
				environments = Scalr.data.get('account.environments');

			//remove team groups
			if (groups) {
				groups.each(function(groupRecord){
					if (groupRecord.get('teamId') == teamId) {
						groups.remove(groupRecord);
					}
				});
			}
			//update team environments
			if (environments) {
				environments.each(function(envRecord){
					var envTeams = envRecord.get('teams');
					if (envTeams) {
						Ext.Array.remove(envTeams, teamId);
						envRecord.set('teams', envTeams);
					}
				});
			}
			Scalr.data.fireRefresh(['account.users', 'account.environments']);
		}
	}
},{
	name: 'account.groups',
	dataUrl: '/account/xGetData',
	dataLoaded: false,
	fields: [{name: 'id', type: 'string'}, 'name', 'permissions', {name: 'teamId', type: 'string'}, 'color'],
	listeners: {
		update: function(){
			Scalr.data.fireRefresh('account.teams');
		},
		add: function(){
			Scalr.data.fireRefresh('account.teams');
		},
		remove: function(store, record){
			var groupId = record.get('id'),
				team = Scalr.data.get('account.teams').getById(record.get('teamId')),
				teamUsers = team ? team.get('users') : null;
			if (teamUsers) {
				for (var i=0, len=teamUsers; i<len; i++) {
					if (teamUsers.groups) {
						Ext.Array.remove(teamUsers.groups, groupId);
					}
				}
				team.set('users', teamUsers);
			}
			Scalr.data.fireRefresh('account.teams');

		}
	}
},{
	name: 'account.environments',
	dataUrl: '/account/xGetData',
	dataLoaded: false,
	fields: [{name: 'id', type: 'string'}, 'name', 'dtAdded', 'platforms', 'status', 'teams'],
	listeners: {
		update: function(){
			Scalr.data.fireRefresh('account.teams');
		},
		add: function(){
			Scalr.data.fireRefresh('account.teams');
		},
		remove: function(store, record){
			Scalr.data.fireRefresh('account.teams');
		}
	}
	
}]);