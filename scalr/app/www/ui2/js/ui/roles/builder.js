Scalr.regPage('Scalr.ui.roles.builder', function (loadParams, moduleParams) {
	var platforms = moduleParams.platforms || {},
		rootDeviceTypeFilterEnabled = true,
		chefFieldsetEnabled = true,//Scalr.flags['betaMode'],
		advancedFieldsetEnabled = Scalr.flags['betaMode'],
		result = {},
		behaviors,
		buttons = {
			platforms: [], 
			behaviors: [], 
			addons: []
		};
	
	if (Ext.Object.getSize(platforms) == 0) {
		Scalr.message.Error('Role builder doesn\'t support configured clouds. Please use import wizard to create scalr compatible role.');
		Scalr.event.fireEvent('redirect', '#/servers/import2', true);
		return false;
	}

	var beautifyBehavior = function(name) {
		var map = {
			mysql2: 'MySQL 5',
			postgresql: 'PostgreSQL',
			percona: 'Percona 5',
			app: 'Apache',
            tomcat: 'Tomcat',
			www: 'Nginx',
			memcached: 'Memcached',
			redis: 'Redis',
			rabbitmq: 'RabbitMQ',
			mongodb: 'MongoDB',
			mysqlproxy: 'MySQL Proxy',
			chef: 'Chef'
		};
		
		return map[name] || name;
	}
	
	behaviors = [
		{name: 'mysql2', disable: {behavior: ['postgresql', 'redis', 'mongodb', 'percona']}},
		{name: 'postgresql', disable: {platform: ['gce'], behavior: ['redis', 'mongodb', 'percona', 'mysql2']}},
		{name: 'percona', disable: {behavior: ['postgresql', 'redis', 'mongodb', 'mysql2']}},
		{name: 'app', disable: {behavior:['www', 'tomcat']}},
        {name: 'tomcat', disable: {behavior:['app'], os:['oel', {family: 'ubuntu', version: ['10.04']}]}},
		{name: 'www', disable: {behavior:['app']}},
		{name: 'memcached'},
		{name: 'redis', disable: {behavior: ['postgresql', 'mongodb', 'percona', 'mysql2']}},
		{name: 'rabbitmq', disable: {os: ['rhel', 'oel']}},
		{name: 'mongodb', disable: {platform: ['gce'], behavior: ['postgresql', 'redis', 'percona', 'mysql2']}},
		//{name: 'mysqlproxy', addon: true, disable: {os: ['centos', 'oel', 'rhel']}},//{family: 'ubuntu', version: ['10.04']}
		{name: 'chef', addon: true, button: {pressed: true, toggle: Ext.emptyFn}}
	];
	
	//behaviors buttons
	for (var i=0, len=behaviors.length; i<len; i++) {
		var button = {
			renderData: {
				name: behaviors[i].name,
				title: beautifyBehavior(behaviors[i].name)
			},
			behavior: behaviors[i].name
		};
		if (behaviors[i].button) {
			Ext.apply(button, behaviors[i].button);
		}
		buttons[behaviors[i].addon ? 'addons' : 'behaviors'].push(button);
	}
	
	//platform buttons
	for (var i in platforms) {
		buttons['platforms'].push({
			renderData: {
				title: platforms[i].name.replace(/^rackspace open cloud \((.+)\)$/i, '<span style="font-size:90%;position:relative;top:-.4em">Rackspace<br/>Open Cloud ($1)</span>'),
				name: i
			},
			value: i,
			toggleHandler: function () {
				if (this.pressed) {
					panel.fireEvent('selectplatform', this.value);
				}
			}
		});
	}
	
	var toggleBehaviors = function(enable) {
		panel.down('#settings-behaviors').items.each(function(){
			this[enable?'enable':'disable']();
			this.setTooltip(enable?'':'Please select operating system.');
		});
		panel.down('#settings-addons').items.each(function(){
			this[enable?'enable':'disable']();
			this.setTooltip(enable?'':'Please select operating system.');
		});
	}
	
	var onSelectBehavior = function() {
		if (this.pressed) {
			result.behaviors.push(this.behavior);
		} else {
			Ext.Array.remove(result.behaviors, this.behavior);
		}
		panel.refreshBehaviors();
        refreshSoftwareWarning();
	};
	
	var onSelectAddon = function() {
		if (this.pressed) {
			result.addons.push(this.behavior);
		} else {
			Ext.Array.remove(result.addons, this.behavior);
		}
		if (this.behavior == 'chef') {
			panel.down('#chefsettings')[this.pressed?'show':'hide']();
		}
        refreshSoftwareWarning();
	};
    
    var refreshSoftwareWarning = function() {
        panel.down('#softwarewarning').setVisible(result.behaviors.length > 0 || result.addons.length > 1);
    };

	var panel = Ext.create('Ext.panel.Panel', {
		bodyCls: 'x-panel-leftmenu-body',
		bodyStyle: 'border-radius:4px',
		cls: 'x-panel-columned',
		scalrOptions: {
			title: 'Role builder',
			maximize: 'all'
		},
		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		
		items:[{
			xtype: 'container',
			cls: 'scalr-ui-rolebuilder-panel scalr-ui-rolebuilder-panel-left',
			width: 474,
			layout: {
				type: 'vbox',
				align: 'stretch'
			},
			margin: 12,
			padding: '16 22 8 22',
			items: [{
				xtype: 'container',
				itemId: 'leftcol',
				margin: '0 10 0 0',
				layout: {
					type: 'vbox',
					align: 'stretch'
				},
				items: [{
					xtype: 'label',
					text: 'Location and operating system',
					cls: 'x-fieldset-subheader'
				},{
					xtype: 'label',
					text: 'Location:',
					margin: '4 0 0'
				},{
					xtype: 'container',
					layout: {
						type: 'hbox',
						pack: 'center'
					},
					margin: '0 0 10 0',
					items: {
						xtype: 'cloudlocationmap',
						itemId: 'locationmap',
						platforms: moduleParams['platforms'],
						size: 'large',
						listeners: {
							selectlocation: function(location){
								panel.down('#cloud_location').setValue(location);
							}
						}
					}
				},{
					xtype: 'combo',
					margin: '0 0 10 0',
					itemId: 'cloud_location',
					editable: false,
					valueField: 'id',
					displayField: 'name',
					queryMode: 'local',
					store: {
						fields: ['id', 'name']
					},
					listeners: {
						change: function(comp, value) {
							panel.fireEvent('selectlocation', value);
							var locations = [];
							this.store.data.each(function(rec){
								locations.push(rec.get('id'));
							});
							panel.down('#locationmap').selectLocation(result.platform, result.cloud_location, locations, 'world');
						}
					}
				},{
					xtype: 'buttongroupfield',
					itemId: 'architecture',
					fieldLabel: 'Architecture',
					cls: 'hideoncustomimage',
					margin: '10 0 0 0',
					labelWidth: 110,
					listeners: {
						change: function(comp, value) {
							panel.fireEvent('selectarchitecture', value);
						}
					},
					defaults: {
						width: 102
					},
					items: [{
						text: '64 bit',
						value: 'x86_64'
					},{
						text: '32 bit',
						value: 'i386'
					}]
				},{
					xtype: 'container',
					itemId: 'rootdevicetypewrap',
					cls: 'hideoncustomimage',
					hidden: true,
					layout: 'hbox',
					margin: '10 0 4 0',
					items: [{
						xtype: 'buttongroupfield',
						itemId: 'root_device_type',
						fieldLabel: 'Root device type',
						labelWidth: 110,
						listeners: {
							change: function(comp, value) {
								panel.fireEvent('selectrootdevicetype', value);
							}
						},
						defaults: {
							width: 102
						},
						items: [{
							text: 'EBS',
							value: 'ebs'
						},{
							text: 'Instance store',
							value: 'instance-store'
						}]
					},{
						xtype: 'button',
						itemId: 'hvm',
						enableToggle: true,
						text: 'HVM',
						width: 34,
						padding: '3 0',
						margin: '0 0 0 5',
						toggleHandler: function(){
							panel.fireEvent('selecthvm', this.pressed);
						}
					}]
				},{
					xtype: 'label',
					text: 'Operating system:',
					margin: '10 0',
					cls: 'hideoncustomimage'
				},{
					xtype: 'label',
					text: 'Image ID:',
					margin: '12 0 16 0',
					cls: 'showoncustomimage'
				},{
					xtype: 'textfield',
					itemId: 'imageId',
					cls: 'showoncustomimage',
					allowBlank: false
				}]
			},{
				xtype: 'container',
				itemId: 'images',
				cls: 'hideoncustomimage scalr-ui-dataview-boxes',
                margin: '0 0 0 -10'
			}]
		}, {
			xtype: 'form',
			itemId: 'rightcol',
			cls: 'scalr-ui-rolebuilder-panel scalr-ui-rolebuilder-panel-right x-panel-columned-rightcol',
			layout: 'anchor',
			defaults: {
				anchor: '100%'
			},
			autoScroll: true,
			flex: 1,
			margin: '12 12 12 0',
			padding: '16 0 16 22',
			bodyPadding: '0 22 0 0',
			items: [{
				xtype: 'label',
				text: 'General info and software',
				cls: 'x-fieldset-subheader'
			},{
				xtype: 'textfield',
				fieldLabel: 'Role name',
				maxWidth: 536,
				margin: '12 0',
				itemId: 'rolename',
				submitValue: false,
				validateOnChange: false,
				validator: function (value) {
					var r = /^[A-z0-9-]+$/, r1 = /^-/, r2 = /-$/;
					if (r.test(value) && !r1.test(value) && !r2.test(value) && value.length > 2)
						return true;
					else
						return 'Illegal name';
				}
			},{
				xtype: 'label',
				text: 'Software:'
			},{
				xtype: 'container',
				margin: '8 0 10 -10',
				itemId: 'settings-behaviors',
                cls: 'scalr-ui-dataview-boxes',
				defaults: {
					xtype: 'custombutton',
					disabled: true,
					enableToggle: true,
                    cls: 'x-item',
					renderTpl:
						'<div class="x-btn-custom" style="height:100%" id="{id}-btnEl">'+
							'<div class="x-btn-icon scalr-ui-icon-behavior-large scalr-ui-icon-behavior-large-{name}"></div><div class="x-btn-title" style="text-align:center;margin:4px 0 0">{title}</div>'+
						'</div>',
					margin: '0 0 10 10',
					tooltip: '',
					listeners: {
						toggle: onSelectBehavior
					}
				},
				items: buttons['behaviors']
			},{
				xtype: 'label',
				text: 'Addons:'
			}, {
				xtype: 'container',
				margin: '8 0 10 -10',
				itemId: 'settings-addons',
                cls: 'scalr-ui-dataview-boxes',
				defaults: {
					xtype: 'custombutton',
					enableToggle: true,
                    cls: 'x-item',
					renderTpl:
						'<div class="x-btn-custom" style="height:100%" id="{id}-btnEl">'+
							'<div class="x-btn-icon scalr-ui-icon-behavior-large scalr-ui-icon-behavior-large-{name}"></div><div class="x-btn-title" style="text-align:center;margin:4px 0 0">{title}</div>'+
						'</div>',
					margin: '0 0 10 10',
					listeners: {
						toggle: onSelectAddon
					}
				},
				items: buttons['addons']
			},{
                xtype: 'displayfield',
                itemId: 'softwarewarning',
                hidden: true,
                fieldCls: 'x-form-field-info',
                value: 'Software version is taken from the <b>official OS repositories</b>. Consult your distribution\'s package manager for exact version details.'
            },{
				xtype: 'fieldset',
				title: 'Install additional software using Chef',
				itemId: 'chefsettings',
				hidden: !chefFieldsetEnabled,
				collapsible: true,
				collapsed: true,
				margin: '8 0 0 0',
				style: 'background:#DFE4EA',
				layout: 'anchor',
				defaults: {
					maxWidth: 516,
					anchor: '100%',
					labelWidth: 120
				},
				listeners: {
					expand: function(){
						panel.down('#rightcol').body.scrollTo('top', 3000);
					}
				},
				items: [{
					xtype: 'combo',
					margin: '12 0',
					name: 'chef.server',
					fieldLabel: 'Chef server',
					valueField: 'id',
					displayField: 'url',
					editable: false,
					value: '',
					store: {
						fields: [ 'url', 'id' ],
						proxy: {
							type: 'ajax',
							reader: {
								type: 'json',
								root: 'data'
							},
							url: '/services/chef/servers/xListServers/'
						}
					},
					listeners: {
						change: function(field, value) {
							if (value) {
								var f = this.next('[name="chef.environment"]');
								f.setReadOnly(false);
								f.queryReset = true;

								// hack, to preselect _default, when none being set
								if (! f.environmentValue)
									f.setValue('_default');

								f.store.proxy.extraParams['servId'] = value;
								
								var f1 = this.next('[name="chef.role"]');
								f1.setReadOnly(false);
								f1.queryReset = true;
								f1.store.proxy.extraParams['servId'] = value;
							}
						}
					}
				}, {
					xtype: 'combo',
					name: 'chef.environment',
					fieldLabel: 'Chef environment',
					store: {
						fields: [ 'name' ],
						proxy: {
							type: 'ajax',
							reader: {
								type: 'json',
								root: 'data'
							},
							url: '/services/chef/servers/xListEnvironments/'
						}
					},
					valueField: 'name',
					displayField: 'name',
					editable: false,
					value: '',
					listeners: {
						change: function(field, value) {
							if (value) {
								var f = this.next('[name="chef.role"]');
								f.setReadOnly(false);
								f.queryReset = true;
								f.store.proxy.extraParams['chefEnv'] = value;
								f.store.proxy.extraParams['servId'] = this.store.proxy.extraParams['servId'];

								if (f.getValue()) {
									f.setValue('');
									f.prefetch();
								}
							}
						}
					}
				}, {
					xtype: 'combo',
					name: 'chef.role',
					fieldLabel: 'Chef role',
					store: {
						fields: [ 'name', 'chef_type' ],
						proxy: {
							type: 'ajax',
							reader: {
								type: 'json',
								root: 'data'
							},
							url: '/services/chef/xListRoles'
						},
						remoteSort: true
					},
					valueField: 'name',
					displayField: 'name',
					editable: false,
					value: '',
					listeners: {
						change: function(field, value) {
						}
					}
				},{
					xtype: 'textarea',
					fieldLabel: 'Attributes (json)',
					name: 'chef.attributes',
					itemId: 'chefattributes',
					hidden: true,
					grow: true,
					growMax: 300,
					anchor: '100%',
					maxWidth: null,
					validateOnChange: false,
					validator: function(value) {
						return !Ext.isEmpty(value) && Ext.typeOf(Ext.decode(value, true)) != 'object' ? 'Invalid json' : true;
					}
				}]
			},{
				xtype: 'fieldset',
				title: 'Advanced options',
				collapsible: true,
				collapsed: true,
				hidden: !advancedFieldsetEnabled,
				margin: '8 0 0 0',
				style: 'background:#DFE4EA',
				layout: 'anchor',
				defaults: {
					maxWidth: 516,
					anchor: '100%',
					labelWidth: 120
				},
				listeners: {
					expand: function(){
						panel.down('#rightcol').body.scrollTo('top', 3000);
					}
				},
				items: [{
					xtype: 'displayfield',
					fieldCls: 'x-form-field-warning',
					value: 'Please use advanced options on your own risk!',
					margin: '8 0 12 0'
				},{
					xtype: 'textfield',
					name: 'advanced.availzone',
					itemId: 'availzone',
					fieldLabel: 'Availability zone'
				},{
					xtype: 'textfield',
					name: 'advanced.servertype',
					itemId: 'servertype',
					fieldLabel: 'Server type'
				},{
					xtype: 'textfield',
					name: 'advanced.scalrbranch',
					itemId: 'scalrbranch',
					fieldLabel: 'Scalarizr branch'
				},{
					xtype: 'textfield',
					name: 'advanced.region',
					itemId: 'region',
					fieldLabel: 'Region'
				},{
					xtype: 'textfield',
					name: 'advanced.overrideImageId',
					itemId: 'overrideImageId',
					hidden: true,
					fieldLabel: 'Image ID'
				},{
					xtype: 'checkbox',
					name: 'advanced.dontterminatefailed',
					itemId: 'dontterminatefailed',
					boxLabel: 'Do not terminate if build failed'
				}]
			}],
			dockedItems:[{
				xtype: 'container',
				dock: 'bottom',
				cls: 'x-toolbar x-docked-light',
				height:36,
				padding: '6 0 0 0',
                style: 'z-index:101',//show toolbar above the mask to make "Cancel" button available
				layout: {
					type: 'hbox',
					pack: 'center'
				},
                defaults: {
                    width: 140
                },
				items: [{
					itemId: 'save',
					xtype: 'btn',
                    cls: 'x-button-text-large',
					text: 'Create role',
					disabled: true,
					margin: '0 16 0 0',
					handler: function() {
						if (chefFieldsetEnabled) {
							if (!panel.down('#chefattributes').isValid()) {
								panel.down('#chefsettings').expand();
								return;
							}
						}
						if (!platforms[result.platform].images.length) {
							if (!panel.down('#imageId').isValid()) {
								panel.down('#imageId').focus();
								return;
							}
						}
						
						if (panel.down('#rolename').isValid()) {
							var r = Scalr.utils.CloneObject(result);
							r.advanced = {};
							r.chef = {};
							
							//role name
							r['roleName'] = panel.down('#rolename').getValue();

							//collect behaviors
							if (! r.behaviors.length) {
								Ext.Array.include(r.behaviors, 'base');
							}
							r['behaviors'] = Ext.encode(Ext.Array.merge(r.behaviors, r.addons));
							delete r.addons;

							//imageId
							if (platforms[r.platform].images.length) {
								r['imageId'] = panel.getImageId();
							} else {
								r['imageId'] = panel.down('#imageId').getValue();
								delete r['architecture'];
							}
							
							//collect fieldsets options
							var extraOptions = panel.down('#rightcol').getValues();
							if (advancedFieldsetEnabled) {//collect advanced options
								Ext.Object.each(extraOptions, function(key, value){
									if (key.match(/^advanced\./ig)) {
										r.advanced[key.replace('advanced.', '')] = value;
									}
								});
								//override imageId
								if (platforms[r.platform].images.length && !Ext.isEmpty(r.advanced['overrideImageId'])) {
									r['imageId'] = r.advanced['overrideImageId'];
								}
								delete r.advanced['overrideImageId'];
							}
							if (chefFieldsetEnabled) {//collect chef options
								Ext.Object.each(extraOptions, function(key, value){
									if (key.match(/^chef./ig)) {
										r.chef[key] = value;
									}
								});
							}
							
							if (loadParams['devScalarizrBranch']) {
								r['devScalarizrBranch'] = loadParams['devScalarizrBranch'];
							}
							
							//backward compatibility
							r['mysqlServerType'] = 'mysql';
							r['location'] = r['cloud_location'];
							
							r.advanced = Ext.encode(r.advanced);
							r.chef = Ext.encode(r.chef);
							
							Scalr.Request({
								processBox: {
									type: 'action'
								},
								url: '/roles/xBuild',
								params: r,
								success: function (data) {
									Scalr.event.fireEvent('redirect', '#/bundletasks/' + data.bundleTaskId + '/view');
								}
							});
						} else {
							panel.down('#rightcol').body.scrollTo('top', 0);
							panel.down('#rolename').focus();
						}
					}
				}, {
					itemId: 'cancel',
					xtype: 'btn',
                    cls: 'x-button-text-large',
					text: 'Cancel',
					handler: function() {
						Scalr.event.fireEvent('close');
					}
				}]
			}]
		}],
		dockedItems: [{
			xtype: 'menu',
			cls: 'x-leftmenu-new',
			dock: 'left',
			floating: false,
			showSeparator: false,
			itemId: 'platforms',
			defaults: {
				xtype: 'custombutton',
				cls: 'x-leftmenu-btn',
				enableToggle: true,
				width: 134,
				height: 100,
				margin: 0,
				allowDepress: false,
				toggleGroup: 'leftmenunew',
				overCls: 'x-leftmenu-btn-over',
				pressedCls: 'x-leftmenu-btn-pressed',
				renderTpl:
					'<div class="x-btn-inner"><div class="x-btn-el" id="{id}-btnEl">'+
						'<span class="x-btn-icon scalr-ui-icon-platform-large scalr-ui-icon-platform-large-{name}"></span><span class="x-btn-title">{title}</span>'+
					'</div></div>'
			},
			items: buttons['platforms']
		}],
		
		suspendState: 0,
		platformsState: {},
		filters: {},
		
		onSelectPlatform: function(platformId) {
			var platform = platforms[platformId],
				images = platform.images,
				compImages = panel.down('#images'),
				added = {},	
				locations = {},	
				compLocationData = [],
				archs = {};
			
            panel.toggleRightColumn(images.length == 0);
			if (result) {
				this.platformsState[result.platform] = Ext.apply({}, result);
			}
			
			if (this.platformsState[platformId]) {
				this.platformsState[platformId].behaviors = result.behaviors;
				this.platformsState[platformId].addons = result.addons;
				this.platformsState[platformId].hvm = 0;
				result = Ext.apply({}, this.platformsState[platformId]);
			} else {
				result = {
					platform: platformId,
					architecture: 'x86_64',
					behaviors: result.behaviors || [], 
					addons: result.addons || ['chef']
				};
			}
			
			this.filters = ['architecture'];
			if (result.platform == 'ec2' && rootDeviceTypeFilterEnabled) {
				this.filters.push('root_device_type');
			}
			
				
			panel.suspendLayouts();
			compImages.removeAll();
			if (images.length) {
				for (var i=0, len=images.length; i<len; i++) {
					var image = images[i],
						imageOS = Scalr.utils.beautifyOsFamily(image.os_family) + ' ' + image.os_version; 
					if (!added[imageOS]) {
						compImages.add({
							xtype: 'custombutton',
                            cls: 'x-item',
							allowDepress: false,
							toggleGroup: 'scalr-ui-roles-builder-image',
							style: 'float:left',
							renderTpl:
								'<div class="x-btn-custom" style="height:100%" id="{id}-btnEl">'+
									'<div class="x-btn-icon scalr-ui-icon-osfamily-large scalr-ui-icon-osfamily-large-{osFamily}"></div><div class="x-btn-title" style="text-align:center;margin:4px 0 0">{title}</div>'+
								'</div>',
							renderData: {
								osFamily: image.os_family,
								title: imageOS
							},
							osFamily: image.os_family,
							osVersion: image.os_version,
							margin: '0 0 10 10',
							toggleHandler: function () {
								panel.fireEvent('selectname', this.osFamily, this.osVersion, this.pressed);
							}
						});

						added[imageOS] = true;
					}

					if (platform.locations && platform.locations[image.cloud_location]) {
						locations[image.cloud_location] = platform.locations[image.cloud_location];
					}
					archs[image.architecture] = true;

				}
			} else {
				locations = Ext.clone(platform.locations);
			}

			var c = panel.down('#leftcol').query('component[cls~=hideoncustomimage]');
			for (var i=0, len=c.length; i<len; i++) {
				c[i].setVisible(!!images.length);
			}

			var c = panel.down('#leftcol').query('component[cls~=showoncustomimage]');
			for (var i=0, len=c.length; i<len; i++) {
				c[i].setVisible(!images.length);
			}

			//location combobox setup
			var compLocation = panel.down('#cloud_location')
			if (result.platform == 'gce') {
				compLocation.store.loadData([{id: 'all', name: 'GCE roles are automatically available in all regions.'}]);
				compLocation.setValue('all');
				compLocation.setDisabled(true);
			} else {
				Ext.Object.each(locations, function(k, v){
					compLocationData.push({id: k, name: v});
				});
				compLocation.store.loadData(compLocationData);
				compLocation.setDisabled(false);
				compLocation.store.sort('name', 'desc');
				compLocation.setValue(result.cloud_location ? result.cloud_location : (result.platform == 'ec2' && locations['us-east-1'] ? 'us-east-1' : compLocationData[0].id));
			}
			
			panel.down('#rootdevicetypewrap')[result.platform == 'ec2' && rootDeviceTypeFilterEnabled ? 'show' : 'hide']();
			//panel.down('#architecture')[Ext.Object.getSize(archs) > 1 ? 'show' : 'hide']();
			panel.resumeLayouts(true);
			
			
		},
		
		initFilters: function() {
			var me = this;
			for (var i=0, len=this.filters.length; i<len; i++) {
				var comp = panel.down('#'+this.filters[i]),
					isItemFound = false;
				comp.items.each(function(item, index){
					item.enable();
					if (result[me.filters[i]] && result[me.filters[i]] == item.value) {
						comp.setValue(item.value);
						isItemFound = true;
						return false;
					}
				});
				if (!isItemFound) {
					comp.items.each(function(item, index){
						if (!item.disabled) {
							comp.setValue(item.value);
							return false;
						}
					});
				}
			}
		},
		
		getImageId: function() {
			var me = this,
				imageId,
				images = moduleParams.platforms[result.platform].images,
				hvm = panel.down('#hvm').pressed ? 1 : 0;
				
			for (var j=0, len=images.length; j<len; j++) {
				var image = images[j],
					match = result.cloud_location == 'all' || image.cloud_location == result.cloud_location;
				if (match) {
					for (var i=0, len1=me.filters.length; i<len1; i++) {
						match = result[me.filters[i]] ? image[me.filters[i]] == result[me.filters[i]] : true;
						if (match && result.platform == 'ec2' && me.filters[i] == 'root_device_type' && result[me.filters[i]] == 'ebs') {//hvm
							match = hvm == (image.hvm || 0);
						}
						if (!match) break;
					}
				}
				if (match && result.osfamily == image.os_family && result.osversion == image.os_version) {
					imageId = image.image_id;
					break;
				}
			}
			
			return imageId;
	
		},
		getFiltersValues: function() {
			var me = this,
				images = moduleParams.platforms[result.platform].images,
				state = {images: {}, filters: {}},
				hvm = panel.down('#hvm').pressed ? 1 : 0;
			
			for (var j=0, len=images.length; j<len; j++) {
				var image = images[j],
					matchHvm = true,
					match = result.cloud_location == 'all' || image.cloud_location == result.cloud_location;
				if (match) {
					for (var i=0, len1=me.filters.length; i<len1; i++) {
						match = result[me.filters[i]] ? image[me.filters[i]] == result[me.filters[i]] : true;
						
						if (match && result.platform == 'ec2' && me.filters[i] == 'root_device_type' && result[me.filters[i]] == 'ebs') {//hvm
							matchHvm = hvm == (image.hvm || 0);
						}
						
						if (!match || !matchHvm) break;
					}
				}
				if (match ) {
					if (matchHvm) {
						state.images[image.os_family+' '+image.os_version] = true;
					}
					if (result.platform == 'ec2' && result.root_device_type == 'ebs' && image.hvm) {
						state.hvm = 1;
					}
				}
			}
			for (var i=0, len=me.filters.length; i<len; i++) {
				for (var k=0, len1=images.length; k<len1; k++) {
					var image = images[k],
						match = result.cloud_location == 'all' || image.cloud_location == result.cloud_location;
					if (match) {
						for (var j=0, len2=me.filters.length; j<len2; j++) {
							match = result[me.filters[j]] == result[me.filters[i]] || (result[me.filters[j]] ? image[me.filters[j]] == result[me.filters[j]] : true);
							if (!match) break;
						}
					}
					if (match) {
						state.filters[me.filters[i]] = state.filters[me.filters[i]] || {};
						state.filters[me.filters[i]][image[me.filters[i]]] = true;
					}
				}
			}
			return state;
		},

		updateFiltersState: function() {
			var me = this,
				state = this.getFiltersValues(),
				compImages = panel.down('#images');

			if (state.hvm) {
				panel.down('#hvm').enable();
			} else {
				panel.down('#hvm').toggle(false);
				panel.down('#hvm').disable();
			}
	
			compImages.items.each(function() {
				if (state.images[this.osFamily+' '+this.osVersion]) {
					this.enable();
				} else {
					if (this.pressed) {
						this.toggle(false);
					}
					this.disable();
				}
			});
			
			for (var i=0, len=me.filters.length; i<len; i++) {
				if (state.filters[me.filters[i]]) {
					var comp = panel.down('#'+me.filters[i]);
					comp.items.each(function(){
						this.enable();
					});
					comp.items.each(function(){
						if (!state.filters[me.filters[i]][this.value]) {
							if (this.pressed) {
								this.toggle(false);
								var that = this;
								comp.items.each(function(){
									if (this !== that && !this.disabled) {
										this.toggle(true);
									}
								});
							}
							this.disable();
						}
					});
				}
			}
		},
		
		refreshBehaviors: function() {
			var params = {platform: result.platform, behavior: result.behaviors},
				image = panel.down('#images').down('[pressed=true]');
			if (image) {
				params.os = {
					family: image.osFamily,
					version: image.osVersion
				}
			}
			for (var i=0, len=behaviors.length; i<len; i++) {
				var item = behaviors[i],
					enabled = true,
					disableInfo;
				if (item.disable) {
					Ext.Object.each(params, function(key, value){
						if (item.disable[key]) {
							if (key == 'os') {
								for (var j=0, len1=item.disable[key].length; j<len1; j++) {
									if (Ext.isString(item.disable[key][j])) {
										if (value.family == item.disable[key][j]) {
											enabled = false;
											break;
										}
									} else {
										if (value.family == item.disable[key][j].family && Ext.Array.contains(item.disable[key][j].version, value.version)) {
											enabled = false;
											break;
										}
									}
								}
							} else {
								enabled = Ext.isArray(value) ? !Ext.Array.intersect(item.disable[key], value).length : !Ext.Array.contains(item.disable[key], value);
							}
						}
						disableInfo = {
							reason: key,
							value: item.disable[key] || null
						};
						return enabled;
					});
				}
                var btn = panel.down('[behavior="'+item.name+'"]');
				if (enabled) {
					btn.enable();
                    btn.setTooltip('');
				}  else {
					btn.toggle(false).disable();
					var message = '';
					if (disableInfo.reason == 'os') {
						message = '<b>' + beautifyBehavior(item.name) + '</b> cannot be used together with <b style="white-space:nowrap">' + Scalr.utils.beautifyOsFamily(result.osfamily) + ' ' + result.osversion + '</b>.';
					} else if (disableInfo.reason == 'platform') {
						message = '<b>' + beautifyBehavior(item.name) + '</b> is not available on <b style="white-space:nowrap">' + platforms[result.platform].name + '</b>.';
					} else if (disableInfo.reason == 'behavior') {
						message = '<b>' + beautifyBehavior(item.name) + '</b> cannot be used together with <b style="white-space:nowrap">' + (Ext.Array.map(Ext.Array.intersect(result.behaviors, disableInfo.value), beautifyBehavior)).join(', ') + '</b>.';
						//disableInfo.reason + (disableInfo.reason == 'behavior' ? ': ' + disableInfo.value.join(', ') : '')
					}
					btn.setTooltip(message);
				}
			}
		},
		toggleRightColumn: function(enable) {
            toggleBehaviors(enable);
            panel.down('#save').setDisabled(!enable);
            var rightcol = panel.down('#rightcol'), mask;
            rightcol[enable ? 'unmask' : 'mask']();
            mask = rightcol.getEl().child('.x-mask');
            if (mask) {
                mask.set({title: enable ? '' : 'Please select operating system.'});
                mask.setStyle({
                    background: '#ffffff',
                    opacity: .4
                });
            }
            
        },
		listeners: {
			afterrender: function () {
				panel.down('#platforms').child('custombutton').toggle(true);
			},
			selectplatform: function(value) {
				this.suspendState++;
				panel.onSelectPlatform(value);
				panel.initFilters();
				this.suspendState--;
				panel.updateFiltersState();
				panel.down('#availzone')[value=='ec2'?'show':'hide']();
				panel.down('#region')[value=='gce'?'show':'hide']();
				panel.down('#overrideImageId')[platforms[value].images.length && Scalr.flags['betaMode']?'show':'hide']();
			},
			selectlocation: function(value) {
				result.cloud_location = value;
				if (!this.suspendState) {
					panel.updateFiltersState();
				}
			},
			selectarchitecture: function(value) {
				result.architecture = value;
				if (!this.suspendState) {
					panel.updateFiltersState();
				}
			},
			selectrootdevicetype: function(value) {
				result.root_device_type = value;
				if (value=='ebs') {
					panel.down('#hvm').enable();
				} else {
					panel.down('#hvm').toggle(false);
					panel.down('#hvm').disable();
				}
				if (!this.suspendState) {
					panel.updateFiltersState();
				}
			},
			selecthvm: function(value) {
				result.hvm = value ? 1 : 0;
				if (!this.suspendState) {
					panel.updateFiltersState();
				}
			},
			selectname: function(osFamily, osVersion, select) {
				result.osfamily = select ? osFamily : null;
				result.osversion = select ? osVersion : null;
                panel.toggleRightColumn(select);
				if (select) {
					panel.refreshBehaviors();
				}
			}
			
		}
	});
	return panel;
});
