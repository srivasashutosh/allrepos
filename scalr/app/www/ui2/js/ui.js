Ext.define('Ext.layout.container.Scalr', {
	extend: 'Ext.layout.container.Absolute',
	alias: [ 'layout.scalr' ],

	activeItem: null,
	stackItems: [],
	zIndex: 101,
	firstRun: true,

	initLayout : function() {
		if (!this.initialized) {
			var me = this;
			this.callParent();

			this.owner.on('resize', function () {
				this.onOwnResize();
			}, this);

			this.owner.items.on('add', function (index, o) {

				//c.style = c.style || {};
				//Ext.apply(c.style, { position: 'absolute' });

				//Ext.apply(c, { hidden: true });
				o.scalrOptions = o.scalrOptions || {};
				Ext.applyIf(o.scalrOptions, {
					reload: true, // close window before show other one
					modal: false, // mask prev window and show new one (false - don't mask, true - mask previous)
					maximize: '' // maximize which sides (all, (max-height - default))
				});

				if (o.scalrOptions.modal)
					o.addCls('x-panel-modal-shadow');

				o.scalrDestroy = function () {
					this.destroy();
					/*Ext.create('Ext.fx.Animator', {
					 target: this,
					 keyframes: {
					 '0%': {
					 opacity: 1
					 },
					 '100%': {
					 opacity: 0
					 }
					 },
					 duration: 1000,
					 listeners: {
					 afteranimate: function () {
					 this.destroy();
					 },
					 scope: this
					 }
					 });*/
				};
				if (o.scalrOptions.leftMenu) {
					var listeners = {
						beforeactivate: function() {
							me.owner.getPlugin('leftmenu').show(o.scalrOptions.leftMenu);
						},
						hide: function() {
							me.owner.getPlugin('leftmenu').hide();
						}
					};
					o.on(listeners);
				}
		});
		}
	},

	setActiveItem: function (newPage, param) {
		var me = this,
			oldPage = this.activeItem;
		
		if (this.firstRun) {
			Ext.get('body-container').applyStyles('visibility: visible; opacity: 0.3');

			Ext.create('Ext.fx.Anim', {
				target: Ext.get('body-container'),
				duration: 1200,
				from: {
					opacity: 0.3
				},
				to: {
					opacity: 1
				},
				callback: function () {
				}
			});

			Ext.create('Ext.fx.Anim', {
				target: Ext.get('loading'),
				duration: 900,
				from: {
					opacity: 1
				},
				to: {
					opacity: 0
				},
				callback: function () {
					Ext.get('loading').remove();
				}
			});

			this.firstRun = false;
		}

		if (newPage) {
			if (oldPage != newPage) {
				if (oldPage) {
					if (oldPage.scalrOptions.modal) {
						if (newPage.scalrOptions.modal) {
							if (
								newPage.rendered &&
								(parseInt(oldPage.el.getStyles('z-index')['z-index']) == (parseInt(newPage.el.getStyles('z-index')['z-index']) + 1)))
							{
								this.zIndex--;
								//oldPage.el.unmask();
								oldPage.scalrDestroy();
							} else {
								this.zIndex++;
								oldPage.el.mask();
								oldPage.fireEvent('deactivate');
							}
						} else {
							this.zIndex = 101;
							oldPage.scalrDestroy();
							// old window - modal, a new one - no, close all windows with reload = true
							// miss newPage
							if (! newPage.scalrOptions.modal) {
								me.owner.items.each(function () {
									if (this.rendered && !this.hidden && this != newPage && this.scalrOptions.modal != 'box') {
										if (this.scalrOptions.reload == true) {
											//this.el.unmask();
											this.scalrDestroy();
										} else {
											this.el.unmask();
											this.hide();
											this.fireEvent('deactivate');
										}
									}
								});
							}
						}
					} else {
						if (newPage.scalrOptions.modal) {
							oldPage.el.mask();
							oldPage.fireEvent('deactivate');
						} else {
							if (oldPage.scalrOptions.reload) {
								//oldPage.el.unmask();
								oldPage.scalrDestroy();
							} else {
								oldPage.hide();
								oldPage.fireEvent('deactivate');
							}
						}
					}
				}
			} else {
				if (oldPage.scalrOptions.reload) {
					//oldPage.unmask();
					oldPage.scalrDestroy();
					this.activeItem = null;
					return false;
				}
			}

			this.activeItem = newPage;
			this.setSize(this.activeItem);

			if (! newPage.scalrOptions.modal) {
				var docTitle = this.activeItem.title || (this.activeItem.scalrOptions ? (this.activeItem.scalrOptions.title || null) : null);
				document.title = ((docTitle ? (docTitle + ' - ') : '') + 'Scalr CP').replace(/&raquo;/g, '»');
			}
			if (this.activeItem.scalrReconfigureFlag && this.activeItem.scalrReconfigure)
				this.activeItem.scalrReconfigure(param || {});
			else
				this.activeItem.scalrReconfigureFlag = true;
			
			this.activeItem.fireEvent('beforeactivate');
			this.activeItem.show();
			this.activeItem.el.unmask();
			this.activeItem.fireEvent('activate');

			this.owner.doLayout();

			if (this.activeItem.scalrOptions.modal)
				this.activeItem.el.setStyle({ 'z-index': this.zIndex });
			
			return true;
		}
	},

	setSize: function (comp) {
		var r = this.getTarget().getSize();
		var top = 0, left = 0;

		comp.doAutoRender();

		if (comp.scalrOptions.modal) {
			top = top + 5;
			r.height = r.height - 5 * 2;

			if (comp.scalrOptions.maximize == 'all') {
				left = left + 5;
				r.width = r.width - 5 * 2;
			}
		}

		if (comp.scalrOptions.maximize == 'all') {
			comp.setSize(r);
			comp.removeCls('x-panel-shadow');
		} else {
			comp.setAutoScroll(true);
			comp.maxHeight = Math.max(0, r.height - 5*2);
			left = (r.width - comp.getWidth()) / 2;

			if (! comp.scalrOptions.modal)
				comp.addCls('x-panel-shadow');
		}

		comp.setPosition(left, top);

		// TODO: investigate in future, while component doesn't have method updateLayout
		if (Ext.isFunction(comp.updateLayout)) {
			comp.updateLayout();
		}
	},

	onOwnResize: function () {
		if (this.activeItem) {
			this.setSize(this.activeItem);
		}
	}
});

Scalr.application = Ext.create('Ext.panel.Panel', {
	layout: 'scalr',
	border: 0,
	padding: 5,
	autoScroll: false,
	bodyCls: 'x-panel-background',
	bodyStyle: 'border-radius: 4px',
	plugins: {
		ptype: 'leftmenu',
		pluginId: 'leftmenu'
	},
	dockedItems: [{
		xtype: 'toolbar',
		plugins: Ext.create('Ext.ux.BoxReorderer', {
			listeners: {
				drop: function(pl, cont, cmp, startIdx, endIdx) {
					// first element is menu, sub it from index
					var favorites = Scalr.storage.get('system-favorites') || [], but = favorites[startIdx - 1] || {};

					if (but) {
						Ext.Array.remove(favorites, but);
						Ext.Array.insert(favorites, endIdx - 1, [but]);
					}

					Scalr.storage.set('system-favorites', favorites);
				}
			}
		}),
		dock: 'top',
		itemId: 'top',
		enableOverflow: true,
		height: 36,
		margin: '0 0 5 0',
		//items: menu,
		cls: 'x-toolbar-menu',
		layout: {
			type: 'hbox',
			align: 'stretch'
		},
		defaults: {
			border: false
		}
	}],
	disabledDockedToolbars: function (disable, hide) {
		Ext.each(this.getDockedItems(), function (item) {
			if (disable) {
				item.disable();
				if (hide)
					item.hide();
			} else {
				if (item.isDisabled())
					item.show();


				item.enable();
			}
		});
	},
	listeners: {
		add: function (cont, cmp) {
			// hack for grid, dynamic width and columns (afterrender)
			//if (cont.el && cmp.scalrOptions && cmp.scalrOptions.maximize == 'all')
			//	cmp.setWidth(cont.getWidth());
		},
		render: function () {
			this.width = Ext.Element.getViewportWidth();
			this.height = Ext.Element.getViewportHeight() - 5;
		},
		boxready: function () {
			Ext.EventManager.onWindowResize(function (width, height) {
				Scalr.application.setSize(width, height - 5); // for margin-top at body; margin-bottom doesn't work
			});
		}
	}
});

Scalr.application.add({
	xtype: 'component',
	scalrOptions: {
		'reload': false,
		'maximize': 'all'
	},
	html: '&nbsp;',
	hidden: true,
	title: '',
	itemId: 'blank'
});

Scalr.application.updateContext = function(handler) {
	Scalr.Request({
		url: '/guest/xGetContext',
		headers: {
			'X-Scalr-EnvId': null, // avoid checking variable on server side,
			'X-Scalr-UserId': null
		},
		success: function(data) {
			var win = Scalr.utils.CreateProcessBox({
				type: 'action',
				msg: 'Applying configuration ...'
			});
			Scalr.application.applyContext(data);
			if (Ext.isFunction(handler))
				handler();
			win.close();
		}
	});
};

Scalr.application.applyContext = function(context) {
	context = context || {};
	Scalr.user = context['user'] || {};
	Scalr.flags = context['flags'] || {};

	if (Ext.isDefined(Scalr.user.userId)) {
		Ext.Ajax.defaultHeaders['X-Scalr-EnvId'] = Scalr.user.envId;
		Ext.Ajax.defaultHeaders['X-Scalr-UserId'] = Scalr.user.userId;
	} else {
		delete Ext.Ajax.defaultHeaders['X-Scalr-EnvId'];
		delete Ext.Ajax.defaultHeaders['X-Scalr-UserId'];
	}

	Scalr.user['envVars'] = Ext.decode(Scalr.user['envVars'], true) || {};

	// TODO: add posibility to inverse beta via !beta in url
	Scalr.flags['betaMode'] = Scalr.user['envVars']['beta'] == 1 || document.location.search.indexOf('beta') != -1 ? true : false;
	if (Scalr.flags['betaMode']) {
		Ext.Ajax.defaultHeaders['X-Scalr-Interface-Beta'] = 1;
	} else {
		delete Ext.Ajax.defaultHeaders['X-Scalr-Interface-Beta'];
	}

	// clear cache
	Scalr.application.items.each(function() {
		// excludes
		if (this.itemId != 'blank' && this.scalrCache != '/guest/login') {
			this.destroy();
		}
	});

	// clear global store
	Scalr.data.reloadDefer('*');
    Scalr.cachedRequest.clearCache();

	if (Ext.isObject(Scalr.user)) {
		Scalr.timeoutHandler.enabled = true;
		// TODO: check
		Scalr.timeoutHandler.params = Scalr.utils.CloneObject(Scalr.user);
		Scalr.timeoutHandler.schedule();
	}

	if (Scalr.user) {
		this.suspendLayouts();
		this.createMenu(context);
		this.resumeLayouts(true);
	}

	if (Ext.isDefined(Scalr.user.userId) && Scalr.flags.needEnvConfig && !sessionStorage.getItem('needEnvConfigLater')) {
		var needConfigEnvId = Scalr.flags.needEnvConfig;
		Scalr.flags.needEnvConfig = true;
		Scalr.event.fireEvent('lock');
		Scalr.event.fireEvent('redirect', '#/account/environments/' + needConfigEnvId + '/platform/ec2', true, true);
		Scalr.event.on('update', function (type, envId, platform, enabled) {
			if (! sessionStorage.getItem('needEnvConfigDone')) {
				if (type == '/account/environments/edit' && envId == needConfigEnvId) {
					if (enabled) {
						sessionStorage.setItem('needEnvConfigDone', true);
						Scalr.event.fireEvent('unlock');
						Scalr.flags.needEnvConfig = false;
						if (platform == 'ec2') {
							Scalr.message.Success('Cloud credentials successfully configured. Now you can start to build your first farm. <a target="_blank" href="http://www.youtube.com/watch?v=6u9M-PD-_Ds&t=6s">Learn how to do this by watching video tutorial.</a>');
							Scalr.event.fireEvent('redirect', '#/farms/build', true);
						} else if (platform == 'rackspace' || platform == 'gce') {
							Scalr.message.Success('Cloud credentials successfully configured. You need to create some roles before you will be able to create your first farm.');
							Scalr.event.fireEvent('redirect', '#/roles/builder', true);
						} else {
							Scalr.message.Success('Cloud credentials successfully configured. Please create role from already running server. <a href="http://wiki.scalr.com/display/docs/Import+a+non+Scalr+server" target="_blank">More info here.</a>');
							Scalr.event.fireEvent('redirect', '#/servers/import2', true);
						}
					}
				}
			}
		});
	} else {
		window.onhashchange(true);
	}
}

Scalr.application.createMenu = function(context) {
	var ct = this.down('#top'), menu = [];

	if (Scalr.user.type != 'ScalrAdmin') {
		var farms = [];
		Ext.each(context['farms'], function (item) {
			farms.push({
				text: item.name,
				href: '#/farms/' + item.id + '/view'
			});
		});

		menu.push({
			cls: 'x-scalr-icon',
			hideOnClick: false,
			width: 65,
			style: 'margin-right: 15px; border-top-left-radius: 3px; border-bottom-left-radius: 3px;',
			reorderable: false,
			listeners: {
				boxready: function () {
					// fix height of vertical separator
					Ext.override(this.menu, {
						afterComponentLayout: function(width, height, oldWidth, oldHeight) {
							var me = this;
							me.callOverridden();

							if (me.showSeparator && me.items.getAt(1)) {
								var y = me.items.getAt(1).el.getTop(true); // top coordinate for first separator after textfield
								me.iconSepEl.setTop(y);
								me.iconSepEl.setHeight(me.componentLayout.lastComponentSize.contentHeight - y);
							}
						}
					});
				},
				afterrender: function () {
					this.btnInnerEl.applyStyles('padding: 0px 0px 0px 4px');
				},
				menushow: function () {
					this.menu.down('textfield').focus(true, true);
				}
			},
			menu: [{
				xtype: 'textfield',
				emptyText: 'Menu filter',
				cls: 'x-menu-item-cmp-search',
				listeners: {
					change: function (field, value) {
						var items = field.up().items.items, cls = 'x-menu-item-blur';

						if (value.length < 2)
							value = '';
						else
							value = value.toLowerCase();

						var search = function (ct) {
							var flag = false;

							if (ct.menu) {
								for (var j = 0; j < ct.menu.items.items.length; j++) {
									var t = search(ct.menu.items.items[j]);
									flag = flag || t;
								}
							}

							if (ct.text && value && ct.text.toLowerCase().indexOf(value) != -1) {
								if (!flag && ct.menu) {
									// found only root menu item, so highlight all childrens
									for (var j = 0; j < ct.menu.items.items.length; j++) {
										ct.menu.items.items[j].removeCls(cls);
									}
								}
								flag = true;
							}

							if (flag || !value)
								ct.removeCls(cls);
							else
								ct.addCls(cls);

							return flag;
						};

						for (var i = 0; i < items.length; i++)
							search(items[i]);
					}
				}
			}, {
				xtype: 'menuseparator'
			}, {
				text: 'Dashboard',
				iconCls: 'x-topmenu-icon-dashboard',
				href: '#/dashboard'
			}, {
				xtype: 'menuseparator'
			}, {
				xtype: 'menuitemtop',
				text: 'Farms',
				href: '#/farms/view',
				iconCls: 'x-topmenu-icon-farms',
				links: [{
					cls: 'list',
					href: '#/farms/view'
				}, {
					cls: 'create',
					href: '#/farms/build'
				}],
				menu: farms
			}, {
				xtype: 'menuitemtop',
				text: 'Roles',
				href: '#/roles/view',
				iconCls: 'x-topmenu-icon-roles',
				links: [{
					cls: 'list',
					href: '#/roles/view'
				}, {
					cls: 'create',
					href: '#/roles/builder'
				}]
			}, {
				text: 'Servers',
				href: '#/servers/view',
				iconCls: 'x-topmenu-icon-servers'
			}, {
				xtype: 'menuitemtop',
				text: 'Scripts',
				href: '#/scripts/view',
				iconCls: 'x-topmenu-icon-scripts',
				links: [{
					cls: 'list',
					href: '#/scripts/view'
				}, {
					cls: 'create',
					href: '#/scripts/create'
				}]
			}, {
				text: 'Logs',
				iconCls: 'x-topmenu-icon-logs',
				menu: [{
					text: 'System',
					href: '#/logs/system'
				}, {
					text: 'Scripting',
					href: '#/logs/scripting'
				}, {
					text: 'API',
					href: '#/logs/api'
				}]
			}, {
				xtype: 'menuseparator'
			}, {
				xtype: 'menuitemtop',
				text: 'DNS zones',
				href: '#/dnszones/view',
				iconCls: 'x-topmenu-icon-dnszones',
				links: [{
					cls: 'list',
					href: '#/dnszones/view'
				}, {
					cls: 'create',
					href: '#/dnszones/create2'
				}]
			}, {
				xtype: 'menuitemtop',
				text: 'Apache virtual hosts',
				href: '#/services/apache/vhosts/view',
				iconCls: 'x-topmenu-icon-apachevhosts',
				links: [{
					cls: 'list',
					href: '#/services/apache/vhosts/view'
				}, {
					cls: 'create',
					href: '#/services/apache/vhosts/create'
				}]
			}, {
				text: 'Deployments',
				iconCls: 'x-topmenu-icon-deployments',
				menu: [{
					text: 'Deployments',
					href: '#/dm/tasks/view'
				}, {
					text: 'Sources',
					href: '#/dm/sources/view'
				}, {
					text: 'Applications',
					href: '#/dm/applications/view'
				}]
			}, {
				text: 'Create role from non-Scalr server',
				href: '#/servers/import2',
				iconCls: 'x-topmenu-icon-import'
			}, {
				text: 'DB backups',
				href: '#/db/backups',
				iconCls: 'x-topmenu-icon-dbbackup'
			}, {
				text: 'Tasks scheduler',
				href: '#/schedulertasks/view',
				iconCls: 'x-topmenu-icon-scheduler'
			}, {
				text: 'SSH keys',
				href: '#/sshkeys/view',
				iconCls: 'x-topmenu-icon-sshkeys'
			}, {
				text: 'Bundle tasks',
				href: '#/bundletasks/view',
				iconCls: 'x-topmenu-icon-bundletasks'
			}, {
				text: 'Server config presets',
				href: '#/services/configurations/presets/view',
				iconCls: 'x-topmenu-icon-presets'
			}, {
				text: 'Custom scaling metrics',
				href: '#/scaling/metrics/view',
				iconCls: 'x-topmenu-icon-metrics'
			}, {
				text: 'Custom events',
				href: '#/scripts/events',
				iconCls: 'x-topmenu-icon-events'
			}, {
				text: 'Global variables',
				href: '#/core/variables',
				iconCls: 'x-topmenu-icon-variables'
			}, {
				text: 'SSL certificates',
				href: '#/services/ssl/certificates/view',
				iconCls: 'x-topmenu-icon-sslcertificates'
			}, {
				text: 'Chef',
				hideOnClick: false,
				iconCls: 'x-topmenu-icon-chef',
				menu: [{
					text: 'Servers',
					href: '#/services/chef/servers/view'
				}, {
					text: 'Runlists',
					href: '#/services/chef/runlists/view'
				}]
			}, {
				xtype: 'menuseparator',
				hidden: !Scalr.flags['platformEc2Enabled']
			}, {
				text: 'AWS',
				hideOnClick: false,
				iconCls: 'x-topmenu-icon-aws',
				hidden: !Scalr.flags['platformEc2Enabled'],
				menu: [{
					text: 'S3 & Cloudfront',
					href: '#/tools/aws/s3/manageBuckets'
				}, {
					text: 'IAM SSL Certificates',
					href: '#/tools/aws/iam/servercertificates/view'
				}, {
					text: 'Security groups',
					href: '#/security/groups/view?platform=ec2'
				}, {
					text: 'Elastic IPs',
					href: '#/tools/aws/ec2/eips'
				}, {
					text: 'Elastic LB',
					href: '#/tools/aws/ec2/elb'
				}, {
					text: 'EBS Volumes',
					href: '#/tools/aws/ec2/ebs/volumes'
				}, {
					text: 'EBS Snapshots',
					href: '#/tools/aws/ec2/ebs/snapshots'
				}]
			}, {
				xtype: 'menuseparator'
			}, {
				text: 'RDS',
				hideOnClick: false,
				iconCls: 'x-topmenu-icon-rds',
				menu: [{
					text: 'Instances',
					href: '#/tools/aws/rds/instances'
				}, {
					text: 'Security groups',
					href: '#/tools/aws/rds/sg/view'
				}, {
					text: 'Parameter groups',
					href: '#/tools/aws/rds/pg/view'
				}, {
					text: 'DB Snapshots',
					href: '#/tools/aws/rds/snapshots'
				}]
			}, {
				xtype: 'menuseparator',
				hidden: !Scalr.flags['platformCloudstackEnabled']
			}, {
				text: 'Cloudstack',
				hideOnClick: false,
				iconCls: 'x-topmenu-icon-cloudstack',
				hidden: !Scalr.flags['platformCloudstackEnabled'],
				menu: [{
					text: 'Volumes',
					href: '#/tools/cloudstack/volumes'
				}, {
					text: 'Snapshots',
					href: '#/tools/cloudstack/snapshots'
				}]
			}, {
				text: 'IDCF',
				hideOnClick: false,
				iconCls: 'x-topmenu-icon-idcf',
				hidden: !Scalr.flags['platformIdcfEnabled'],
				menu: [{
					text: 'Volumes',
					href: '#/tools/cloudstack/volumes?platform=idcf'
				}, {
					text: 'Snapshots',
					href: '#/tools/cloudstack/snapshots?platform=idcf'
				}]
			}, {
				text: 'uCloud',
				hideOnClick: false,
				iconCls: 'x-topmenu-icon-ucloud',
				hidden: !Scalr.flags['platformUcloudEnabled'],
				menu: [{
					text: 'Volumes',
					href: '#/tools/cloudstack/volumes?platform=ucloud'
				}, {
					text: 'Snapshots',
					href: '#/tools/cloudstack/snapshots?platform=ucloud'
				}]
			}, {
				xtype: 'menuseparator',
				hidden: !Scalr.flags['platformRackspaceEnabled']
			}, {
				text: 'Rackspace',
				hideOnClick: false,
				iconCls: 'x-topmenu-icon-rackspace',
				hidden: !Scalr.flags['platformRackspaceEnabled'],
				menu: [{
					text: 'Limits Status',
					href: '#/tools/rackspace/limits'
				}]
			}]
		});

		if (!Scalr.storage.get('system-favorites') && !Scalr.storage.get('system-favorites-created')) {
			Scalr.storage.set('system-favorites', [{
				href: '#/farms/view',
				text: 'Farms'
			}, {
				href: '#/roles/view',
				text: 'Roles'
			}, {
				href: '#/servers/view',
				text: 'Servers'
			}, {
				href: '#/scripts/view',
				text: 'Scripts'
			}, {
				href: '#/logs/system',
				text: 'System Log'
			}]);
			Scalr.storage.set('system-favorites-created', true);
		}

		Ext.each(Scalr.storage.get('system-favorites'), function(item) {
			if (item.text) {
				item['hrefTarget'] = '_self';
				item['reorderable'] = true;
				item['cls'] = 'x-btn-favorite';
				item['overCls'] = 'btn-favorite-over';
				item['pressedCls'] = 'btn-favorite-pressed';
				menu.push(item);
			}
		}, this);

		menu.push({
			xtype: 'tbfill',
			reorderable: false
		});

		if (Scalr.user['userIsTrial'])
			menu.push({
				text: 'Live Chat',
				reorderable: 'false',
				iconCls: 'scalr-menu-icon-supportchat',
				listeners: {
					afterrender: function() {
						var me = this;
						Ext.Loader.injectScriptElement('https://snapabug.appspot.com/snapabug.js', function () {
							Ext.getBody().createChild({
								tag: 'div',
								id: 'SnapABug_W'
							});

							Ext.getBody().createChild({
								tag: 'div',
								id: 'SnapABug_WP'
							});

							Ext.getBody().createChild({
								tag: 'div',
								id: 'SnapABug_Applet'
							});

							SnapABug.initAsync('1ddc18b2-03c6-49a3-a858-4e1b34d41dec');
							SnapABug.setDomain('scalr.net');
							SnapABug.setUserEmail(Scalr.user['userName'], true);

							me.on('click', function() {
								SnapABug.setSecureConnexion();
								SnapABug.allowOffline(false);
								SnapABug.startChat('Hello, how can I help you today?');
							});
						});
					}
				}
			});

		menu.push({
			text: Scalr.user['userName'],
			reorderable: false,
			cls: 'x-icon-avatar',
			itemId: 'gravatar',
			icon: Scalr.utils.getGravatarUrl(Scalr.user['gravatarHash']),
			listeners: {
				boxready: function() {
					Scalr.event.on('update', function (type, hash) {
						Scalr.user['gravatarHash'] = hash;
						if (type == '/account/user/gravatar') {
							this.setIcon(Scalr.utils.getGravatarUrl(hash));
						}
					}, this);
				}
			},
			menu: [{
				text: 'API access',
				href: '#/core/api',
				iconCls: 'x-topmenu-icon-api'
			}, '-', {
				text: 'Security',
				href: '#/core/security',
				iconCls: 'x-topmenu-icon-security'
			}, {
				text: 'Settings',
				href: '#/core/settings',
				iconCls: 'x-topmenu-icon-settings'
			}, '-', {
				text: 'Logout',
				href: '/guest/logout',
				iconCls: 'x-topmenu-icon-logout'
			}]
		});

		var envs = [];
		if (Scalr.user['type'] == 'AccountOwner' || Scalr.user['isTeamOwner']) {
			envs.push({
				href: '#/account/environments',
				iconCls: 'x-topmenu-icon-settings',
				text: 'Manage'
			}, {
				xtype: 'menuseparator'
			});
		}

		Ext.each(context['environments'], function(item) {
			envs.push({
				text: item.name,
				group: 'environment',
				envId: item.id,
				checked: item.id == Scalr.user.envId
			});
		});

		menu.push({
			cls: 'x-icon-environment',
			reorderable: false,
			text: Scalr.user['envName'],
			menu: envs,
			tooltip: 'Environment',
			listeners: {
				boxready: function() {
					var handler = function() {
						if (this.envId && Scalr.user['envId'] != this.envId)
							Scalr.Request({
								processBox: {
									type: 'action',
									msg: 'Changing environment ...'
								},
								url: '/core/xChangeEnvironment/',
								params: { envId: this.envId },
								success: function() {
									Scalr.application.updateContext(Ext.emptyFn);
								}
							});
					};

					this.menu.items.each(function(it) {
						it.on('click', handler);
					});

					Scalr.event.on('update', function (type, env) {
						if (type == '/account/environments/create') {
							this.menu.add({
								text: env.name,
								checked: false,
								group: 'environment',
								envId: env.id
							}).on('click', handler);
						} else if (type == '/account/environments/rename') {
							var el = this.menu.child('[envId="' + env.id + '"]');
							if (el) {
								el.setText(env.name);
							}

							if (Scalr.user['envId'] == env.id) {
								this.setText(env.name);
							}
						} else if (type == '/account/environments/delete') {
							var el = this.menu.child('[envId="' + env.id + '"]');
							if (el) {
								this.menu.remove(el);
							}
						}
					}, this);
				}
			}
		});

		if (Scalr.user['type'] == 'AccountOwner' && Scalr.flags['billingExists'])
			menu.push({
				text: 'Billing',
				href: '#/billing',
				reorderable: false,
				hrefTarget: '_self',
				cls: 'x-icon-billing'
			});

		/*var account = {
			cls: 'x-icon-account',
			tooltip: 'Accounting',
			reorderable: false,
			menu: [{
				text: 'Teams',
				href: '#/account/teams/view',
				iconCls: 'x-topmenu-icon-teams'
			}, {
				text: 'Users',
				href: '#/account/users/view',
				iconCls: 'x-topmenu-icon-users'
			}]
		};

		menu.push(account);*/

		menu.push({
			cls: 'x-icon-help',
			tooltip: 'Help',
			reorderable: false,
			menu: [{
				text: 'Wiki',
				href: Scalr.flags['wikiUrl'],
				iconCls: 'x-topmenu-icon-wiki',
				hrefTarget: '_blank'
			}, {
				text: 'Support',
				href: Scalr.flags['supportUrl'],
				iconCls: 'x-topmenu-icon-support',
				hrefTarget: '_blank'
			}]

		})
	} else {
		menu.push({
			text: '<img src="/ui2/js/extjs-4.1/theme/images/topmenu/scalr_logo_icon_36x27.png">',
			width: 65,
			style: 'border-top-left-radius: 3px; border-bottom-left-radius: 3px;'
		}, {
			text: 'Accounts',
			href: '#/admin/accounts',
			hrefTarget: '_self'
		}, {
			text: 'Admins',
			href: '#/admin/users',
			hrefTarget: '_self'
		}, {
			text: 'Logs',
			href: '#/admin/logs/view',
			hrefTarget: '_self'
		}, {
			text: 'Roles',
			menu: [{
				text: 'View all',
				href: '#/roles/view'
			}, {
				text: 'View shared roles',
				href: '#/roles/view?origin=Shared'
			}, {
				text: 'Add new',
				href: '#/roles/edit'
			}]
		}, {
			text: 'Scripts',
			href: '#/scripts/view',
			hrefTarget: '_self'
		}, {
			text: 'Settings',
			menu: [{
				text: 'Default DNS records',
				href: '#/dnszones/defaultRecords2'
			}]
		});

		menu.push('->');
		menu.push({
			text: Scalr.user['userName'],
			reorderable: false,
			cls: 'x-icon-login',
			menu: [{
				text: 'Logout',
				href: '/guest/logout',
				iconCls: 'x-topmenu-icon-logout'
			}]
		});
	}

	ct.removeAll();
	ct.add(menu);
}

window.onhashchange = function (e) {
	if (Scalr.state.pageSuspendForce) {
		Scalr.state.pageSuspendForce = false;
	} else {
		if (Scalr.state.pageSuspend)
			return;
	}

	/*if (Scalr.state.pageChangeInProgress) {
		Scalr.state.pageChangeInProgressInvalid = true; // User changes link while loading page
		Scalr.message.Warning('Please wait');
	}*/

	Scalr.state.pageChangeInProgress = true;
	Scalr.message.Flush();

	if (Ext.WindowManager.getActive()) {
		Ext.WindowManager.eachTopDown(function () {
			if (this.itemId == 'box')
				this.destroy();
		});
	}

	var h = window.location.hash.substring(1).split('?'), link = '', param = {}, loaded = false, defaultPage = false;
	if (window.location.hash) {
		// only if hash not null
		if (h[0])
			link = h[0];
		// cut ended /  (/logs/view'/')

		if (h[1])
			param = Ext.urlDecode(h[1]);

		if (link == '' || link == '/') {
			defaultPage = true;
			return; // TODO: check why ?
		}
	} else {
		defaultPage = true;
	}

	if (defaultPage) {
		if (Scalr.user.userId)
			document.location.href = "#/dashboard";
		else
			document.location.href = "#/guest/login";
		return;
	}

    var addStatisticGa = function(link) {
        // Google analytics
        if (typeof _gaq != 'undefined') {
            _gaq.push(['_trackPageview', link]);
        }
    }

	var cacheLink = function (link, cache) {
        var cacheOriginal = cache.trim();
		var re = cache.replace(/\/\{[^\}]+\}/g, '/([^\\}\\/]+)').replace(/\//g, '\\/'), fieldsRe = /\/\{([^\}]+)\}/g, fields = [];

		while ((elem = fieldsRe.exec(cache)) != null) {
			fields[fields.length] = elem[1];
		}

		return {
			scalrReconfigureFlag: false,
			scalrRegExp: new RegExp('^' + re + '$'),
            scalrCacheStr: cacheOriginal,
			scalrCache: cache,
			scalrParamFields: fields,
			scalrParamGets: function (link) {
				var pars = {}, reg = new RegExp(this.scalrRegExp), params = reg.exec(link);
				if (Ext.isArray(params))
					params.shift(); // delete first element

				for (var i = 0; i < this.scalrParamFields.length; i++)
					pars[this.scalrParamFields[i]] = Ext.isArray(params) ? params.shift() : '';

				return pars;
			}
		};
	};

	// check in cache
	Scalr.application.items.each(function () {
		if (this.scalrRegExp && this.scalrRegExp.test(link)) {

			//TODO: Investigate in Safari
			this.scalrParamGets(link);

			Ext.apply(param, this.scalrParamGets(link));

            addStatisticGa(this.scalrCacheStr);
			loaded = Scalr.application.layout.setActiveItem(this, param);
			return false;
		}
	});

	if (loaded) {
		// update statistic
		var stats = Ext.state.Manager.get('system-link-statistic') || {}, link = Scalr.application.layout.activeItem.scalrCache;

		if (! Ext.isDefined(stats[link]))
			stats[link] = { cnt: 1 };

		stats[link]['cnt']++;
		Ext.state.Manager.set('system-link-statistic', stats);
		return;
	}

	Ext.apply(param, Scalr.state.pageRedirectParams);
	Scalr.state.pageRedirectParams = {};

	var finishChange = function () {
		if (Scalr.state.pageChangeInProgressInvalid) {
			Scalr.state.pageChangeInProgressInvalid = false;
			Scalr.state.pageChangeInProgress = false;
			window.onhashchange(true);
		} else {
			Scalr.state.pageChangeInProgress = false;
		}
	};

	var r = {
		disableFlushMessages: true,
		disableAutoHideProcessBox: true,
		url: link,
		params: param,
		success: function (data, response, options) {
			try {
				// TODO: replace ui2 -> ui
				var c = 'Scalr.' + data.moduleName.replace('/ui2/js/', '').replace(/-[0-9]+.js/, '').replace(/\//g, '.'), cacheId = response.getResponseHeader('X-Scalr-Cache-Id'), cache = cacheLink(link, cacheId);
				var initComponent = function (c) {
					if (Ext.isObject(c)) {

						Ext.apply(c, cache);
						Scalr.application.add(c);
                        addStatisticGa(c.scalrCacheStr);

						if (Scalr.state.pageChangeInProgressInvalid) {
							if (options.processBox)
								options.processBox.destroy();

							finishChange();
						} else {
							Scalr.application.layout.setActiveItem(c, param);
							if (options.processBox)
								options.processBox.destroy();
						}
					} else {
						if (options.processBox)
							options.processBox.destroy();

						Scalr.application.layout.setActiveItem(Scalr.application.getComponent('blank'));
						finishChange();
					}
				};
				var loadModuleData = function(c, param, data) {
					if (data.moduleRequiresData) {
						Scalr.data.load(data.moduleRequiresData, function(){
							initComponent(Scalr.cache[c](param, data.moduleParams));
						});
					} else {
						initComponent(Scalr.cache[c](param, data.moduleParams));
					}
				};
				
				Ext.apply(param, cache.scalrParamGets(link));
				if (Ext.isDefined(Scalr.cache[c]))
					loadModuleData(c, param, data);
				else {
					var head = Ext.getHead();
					if (data.moduleRequiresCss) {
						for (var i = 0; i < data.moduleRequiresCss.length; i++) {
							var el = document.createElement('link');
							el.type = 'text/css';
							el.rel = 'stylesheet';
							el.href = data.moduleRequiresCss[i];

							head.appendChild(el);
						}
					}

					var sc = [ data.moduleName ];
					if (data.moduleRequires)
						sc = sc.concat(data.moduleRequires);

					Ext.Loader.loadScripts(sc, function() {
						loadModuleData(c, param, data);
					})

					/*var load = function () {
						if (sc.length)
							Ext.Loader.injectScriptElement(sc.shift(), load);
						else {
							loadModuleData(c, param, data);
						}
					};

					load();*/
				}
			} catch (e) {
				Scalr.utils.PostException(e);
			}
		},
		failure: function (data, response, options) {
			if (options.processBox)
				options.processBox.destroy();

			Scalr.application.layout.setActiveItem(Scalr.application.getComponent('blank'));
			finishChange();
		}
	};

	if (e)
		r['processBox'] = {
			type: 'action',
			msg: 'Loading page ...'
		};

	Scalr.Request(r);
};

Scalr.timeoutHandler = {
	defaultTimeout: 60000,
	timeoutRun: 60000,
	timeoutRequest: 5000,
	params: {},
	enabled: false,
	locked: false,
	clearDom: function () {
		if (Ext.get('body-timeout-mask'))
			Ext.get('body-timeout-mask').remove();

		if (Ext.get('body-timeout-container'))
			Ext.get('body-timeout-container').remove();
	},
	schedule: function () {
		this.timeoutId = Ext.Function.defer(this.run, this.timeoutRun, this);
	},
	createTimer: function (cont) {
		clearInterval(this.timerId);
		var f = Ext.Function.bind(function (cont) {
			var el = cont.child('span');
			if (el) {
				var s = parseInt(el.dom.innerHTML);
				s -= 1;
				if (s < 0)
					s = 0;
				el.update(s.toString());
			} else {
				clearInterval(this.timerId);
			}
		}, this, [ cont ]);

		this.timerId = setInterval(f, 1000);
	},
	undoSchedule: function () {
		clearTimeout(this.timeoutId);
		clearInterval(this.timerId);
	},
	restart: function () {
		this.undoSchedule();
		this.run();
	},
	run: function () {
		//this.params['uiStorage'] = Scalr.storage.dump(true);
		Ext.Ajax.request({
			url: '/guest/xPerpetuumMobile',
			params: this.params,
			timeout: this.timeoutRequest,
			scope: this,
			hideErrorMessage: true,
			callback: function (options, success, response) {
				if (success) {
					try {
						var response = Ext.decode(response.responseText);

						if (response.success != true) {
							if (response.success == false && response.errorMessage != '') {
								Scalr.message.Error(response.errorMessage);
							} else {
								throw 'False';
							}
						}

						this.clearDom();
						this.timeoutRun = this.defaultTimeout;

						if (! response.isAuthenticated) {
							Scalr.application.MaiWindow.layout.setActiveItem(Scalr.application.MainWindow.getComponent('loginForm'));
							this.schedule();
							return;
						} else if (! response.equal) {
							document.location.reload();
							return;
						} else {
							if (this.locked) {
								this.locked = false;
								Scalr.event.fireEvent('unlock');
								// TODO: проверить, нужно ли совместить в unlock
								window.onhashchange(true);
							}

							Scalr.event.fireEvent('update', 'lifeCycle', response);

							this.schedule();
							return;
						}
					} catch (e) {
						this.schedule();
						return;
					}
				}

				if (response.aborted == true) {
					this.schedule();
					return;
				}

				if (response.timedout == true) {
					this.schedule();
					return;
				}

				Scalr.event.fireEvent('lock');
				this.locked = true;

				var mask = Ext.get('body-timeout-mask') || Ext.getBody().createChild({
					id: 'body-timeout-mask',
					tag: 'div',
					style: {
						position: 'absolute',
						top: 0,
						left: 0,
						width: '100%',
						height: '100%',
						background: '#CCC',
						opacity: '0.5',
						'z-index': 300000
					}
				});

				this.timeoutRun += 6000;
				if (this.timeoutRun > 60000)
					this.timeoutRun = 60000;

				if (! Ext.get('body-timeout-container'))
					this.timeoutRun = 5000;

				var cont = Ext.get('body-timeout-container') || Ext.getBody().createChild({
					id: 'body-timeout-container',
					tag: 'div',
					style: {
						position: 'absolute',
						top: '5px',
						left: '5px',
						right: '5px',
						'z-index': 300001,
						background: '#F6CBBA',
						border: '1px solid #BC7D7A',
						'box-shadow': '0 1px #FEECE2 inset',
						font: 'bold 13px arial',
						color: '#420404',
						padding: '10px',
						'text-align': 'center'
					}
				}).applyStyles({ background: '-webkit-gradient(linear, left top, left bottom, from(#FCD9C5), to(#F0BCAC))'
				}).applyStyles({ background: '-moz-linear-gradient(top, #FCD9C5, #F0BCAC)' });

				this.schedule();

				cont.update('Not connected. Connecting in <span>' + this.timeoutRun/1000 + '</span>s. <a href="#">Try now</a> ');
				cont.child('a').on('click', function (e) {
					e.preventDefault();
					cont.update('Not connected. Trying now');
					this.undoSchedule();
					this.run();
				}, this);
				this.createTimer(cont);
			}
		});
	}
};

Scalr.timeoutHandler22 = {
	defaultTimeout: 60000,
	timeoutRun: 60000,
	timeoutRequest: 5000,
	params: {},
	enabled: false,
	forceCheck: false,
	locked: false,
	lockedCheck: true,
	clearDom: function () {
		if (Ext.get('body-timeout-mask'))
			Ext.get('body-timeout-mask').remove();

		if (Ext.get('body-timeout-container'))
			Ext.get('body-timeout-container').remove();
	},
	schedule: function () {
		this.timeoutId = Ext.Function.defer(this.run, this.timeoutRun, this);
	},
	createTimer: function (cont) {
		clearInterval(this.timerId);
		var f = Ext.Function.bind(function (cont) {
			var el = cont.child('span');
			if (el) {
				var s = parseInt(el.dom.innerHTML);
				s -= 1;
				if (s < 0)
					s = 0;
				el.update(s.toString());
			} else {
				clearInterval(this.timerId);
			}
		}, this, [ cont ]);

		this.timerId = setInterval(f, 1000);
	},
	undoSchedule: function () {
		clearTimeout(this.timeoutId);
		clearInterval(this.timerId);
	},
	restart: function () {
		this.undoSchedule();
		this.run();
	},
	run: function () {
		if (!this.locked && !this.forceCheck) {
			var cur = new Date(), tm = Scalr.storage.get('system-pm-updater');
			if (cur < tm) {
				this.schedule();
				return;
			}

			Scalr.storage.set('system-pm-updater', Ext.Date.add(cur, Ext.Date.SECOND, this.timeoutRun/1000));
		}

		Ext.Ajax.request({
			url: this.forceCheck || this.locked && this.lockedCheck ? '/ui/js/connection.js?r=' + new Date().getTime() : '/guest/xPerpetuumMobile',
			params: this.params,
			method: 'GET',
			timeout: this.timeoutRequest,
			scope: this,
			hideErrorMessage: true,
			callback: function (options, success, response) {
				if (success) {
					try {
						if (this.locked && this.lockedCheck) {
							this.lockedCheck = false;
							this.run();
							return;
						} else if (this.forceCheck) {
							this.forceCheck = false;
							this.schedule();
							return;
						} else {
							var response = Ext.decode(response.responseText);
						}

						if (response.success != true)
							throw 'False';

						this.clearDom();
						this.timeoutRun = this.defaultTimeout;

						if (! response.isAuthenticated) {
							Scalr.state.userNeedLogin = true;
							Scalr.event.fireEvent('redirect', '#/guest/login', true);
							this.schedule();
							return;
						} else if (! response.equal) {
							document.location.reload();
							return;
						} else {
							if (this.locked) {
								this.locked = false;
								this.lockedCheck = true;
								Scalr.event.fireEvent('unlock');
								Scalr.storage.set('system-pm-updater-status', this.locked);
								// TODO: проверить, нужно ли совместить в unlock
								window.onhashchange(true);
							}

							this.schedule();
							return;
						}
					} catch (e) {
						this.schedule();
						return;
					}
				}

				if (response.aborted == true) {
					this.schedule();
					return;
				}

				if (response.timedout == true) {
					this.schedule();
					return;
				}

				Scalr.event.fireEvent('lock');
				this.locked = true;
				Scalr.storage.set('system-pm-updater-status', this.locked);

				var mask = Ext.get('body-timeout-mask') || Ext.getBody().createChild({
					id: 'body-timeout-mask',
					tag: 'div',
					style: {
						position: 'absolute',
						top: 0,
						left: 0,
						width: '100%',
						height: '100%',
						background: '#CCC',
						opacity: '0.5',
						'z-index': 300000
					}
				});

				this.timeoutRun += 6000;
				if (this.timeoutRun > 60000)
					this.timeoutRun = 60000;

				if (! Ext.get('body-timeout-container'))
					this.timeoutRun = 5000;

				var cont = Ext.get('body-timeout-container') || Ext.getBody().createChild({
					id: 'body-timeout-container',
					tag: 'div',
					style: {
						position: 'absolute',
						top: '5px',
						left: '5px',
						right: '5px',
						'z-index': 300001,
						background: '#F6CBBA',
						border: '1px solid #BC7D7A',
						'box-shadow': '0 1px #FEECE2 inset',
						font: 'bold 13px arial',
						color: '#420404',
						padding: '10px',
						'text-align': 'center'
					}
				}).applyStyles({ background: '-webkit-gradient(linear, left top, left bottom, from(#FCD9C5), to(#F0BCAC))'
					}).applyStyles({ background: '-moz-linear-gradient(top, #FCD9C5, #F0BCAC)' });

				this.schedule();

				cont.update('Not connected to Scalr. Connecting in <span>' + this.timeoutRun/1000 + '</span>s. <a href="#">Try now</a> ');
				cont.child('a').on('click', function (e) {
					e.preventDefault();
					cont.update('Not connected to Scalr. Trying now');
					this.undoSchedule();
					this.run();
				}, this);
				this.createTimer(cont);
			}
		});
	}
};


Scalr.Init = function (context) {
	Ext.get('loading-div-child').applyStyles('-webkit-animation: pulse 1.5s infinite;');

	new Ext.util.KeyMap(Ext.getBody(), [{
		key: Ext.EventObject.ESC,
		fn: function () {
			if (Scalr.flags['suspendPage'] == false && Scalr.application.layout.activeItem.scalrOptions.modal == true) {
				Scalr.event.fireEvent('close');
			}
		}
	}]);

	window.onunload = function () {
		Scalr.timeoutHandler.enabled = false;
		Scalr.timeoutHandler.undoSchedule();
		Scalr.timeoutHandler.clearDom();

		Ext.getBody().createChild({
			tag: 'div',
			style: {
				opacity: '0.8',
				background: '#EEE',
				'z-index': 400000,
				position: 'absolute',
				top: 0,
				left: 0,
				width: '100%',
				height: '100%'
			}
		});
	};

	/*window.onbeforeunload = function (e) {
		var message = "Where are you gone?";
		e = e || window.event;

		if (e)
			e.returnValue = message;

		return message;
	};*/

	window.onerror = function (message, file, lineno) {
		Scalr.utils.PostError({
			message: message,
			file: file,
			lineno: lineno,
			url: document.location.href
		});

		return false;
	};

    Scalr.cachedRequest = Ext.create('Scalr.CachedRequest');
	Scalr.application.render('body-container');
	Scalr.application.applyContext(context);
};
