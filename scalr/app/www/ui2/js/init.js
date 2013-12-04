Ext.Ajax.on('requestcomplete', function(conn, response) {
	var resp = Ext.isFunction(response.getResponseHeader);
	if (! Scalr.state['pageInterfaceVersion'] && resp) {
		Scalr.state['pageInterfaceVersion'] = response.getResponseHeader('X-Scalr-Interface-Version');
	} else if (resp && response.getResponseHeader('X-Scalr-Interface-Version') && response.getResponseHeader('X-Scalr-Interface-Version') != Scalr.state['pageInterfaceVersion']) {
		Scalr.message.Warning("New version of user interface has been released. Please save your work and refresh the page.");
	}
});

// catch server error page (404, 403, timeOut and other)
Ext.Ajax.on('requestexception', function(conn, response, options) {
	if (options.hideErrorMessage == true)
		return;

	if (response.status == 403) {
		Scalr.state.userNeedLogin = true;
		Scalr.event.fireEvent('redirect', '#/guest/login', true);
	} else if (response.status == 404) {
		Scalr.message.Error('Page not found.');
	} else if (response.timedout == true) {
		Scalr.message.Error('Server didn\'t respond in time. Please try again in a few minutes.');
	} else if (response.aborted == true) {
		Scalr.message.Error('Request was aborted by user.');
	} else {
		if (Scalr.timeoutHandler.enabled) {
			Scalr.timeoutHandler.undoSchedule();
			Scalr.timeoutHandler.run();

			//Scalr.timeoutHandler.forceCheck = true;
			//Scalr.timeoutHandler.restart();
		}
		Scalr.message.Error('Cannot proceed your request at the moment. Please try again later.');
	}
});

Scalr.storage = {
	prefix: 'scalr-',
	getStorage: function (session) {
		session = session || false;
		if (session)
			return sessionStorage;
		else
			return localStorage;
	},
	getName: function (name) {
		return this.prefix + name;
	},
	listeners: {},
	encodeValue: (new Ext.state.Provider()).encodeValue,
	decodeValue: (new Ext.state.Provider()).decodeValue,

	get: function (name, session) {
		var storage = this.getStorage(session);
		return storage ? this.decodeValue(storage.getItem(this.getName(name))) : '';
	},
	set: function (name, value, session) {
		var storage = this.getStorage(session);
		try {
			if (storage)
				storage.setItem(this.getName(name), this.encodeValue(value));
		} catch (e) {
			if (e == QUOTA_EXCEEDED_ERR) {
				Scalr.message.Error('LocalStorage overcrowded');
			}
		}
	},
	clear: function (name, session) {
		var storage = this.getStorage(session);
		if (storage)
			storage.removeItem(this.getName(name));
	},
	dump: function(encoded) {
		var storage = Scalr.storage.getStorage(), data = {};
		for (var i = 0, len = storage.length; i < len; i++) {
			var key = storage.key(i);
			data[key] = storage.getItem(key);
		}

		return encoded ? Ext.encode(data) : data;
	},
	hash: function() {
		return CryptoJS.SHA1(this.dump(true)).toString();
	}
};

window.addEventListener('storage', function (e) {
	if (e && e.key) {
		var name = e.key.replace(Scalr.storage.prefix, '');
		if (Scalr.storage.listeners[name]) {
			Scalr.storage.listeners[name](Scalr.storage.get(name));
		}
	}
}, false);


Scalr.event = new Ext.util.Observable();
/*
 * update - any content on page was changed (notify): function (type, arguments ...)
 * close - close current page and go back
 * redirect - redirect to link: function (href, keepMessages, force)
 * reload - browser page
 * refresh - current application
 * lock - lock to switch current application (override only throw redirect with force = true)
 * unlock - unlock ...
 * clear - clear application from cache (close and reload)
 */
Scalr.event.addEvents('update', 'close', 'redirect', 'reload', 'refresh', 'resize', 'lock', 'unlock', 'maximize', 'clear');

Scalr.event.on = Ext.Function.createSequence(Scalr.event.on, function (event, handler, scope) {
	if (event == 'update' && scope)
		scope.on('destroy', function () {
			this.un('update', handler, scope);
		}, this);
});

Scalr.cache = {};
Scalr.regPage = function (type, fn) {
	Scalr.cache[type] = fn;
};

Scalr.user = {};
Scalr.flags = {};
Scalr.state = {
	pageSuspend: false,
	pageSuspendForce: false,
	pageRedirectParams: {},
	userNeedLogin: false
};

Scalr.version = function (checkVersion) {
	try {
		var version = Scalr.InitParams.ui.version;
	} catch (e) {}
	return ( !version || version == checkVersion) ? true : false;
};

Scalr.data = {
	stores: {},
	add: function(stores){
		if (!Ext.isArray(stores)) {
			stores = [stores];
		}
		for (var i=0, len=stores.length; i<len; i++) {
			if (!this.stores[stores[i].name]) {
				this.stores[stores[i].name] = new Ext.data.Store(stores[i]);
			}
		}
	},
	get: function(name) {
		return this.stores[name];
	},
	query: function(names) {
		var stores = [];
		if (!Ext.isArray(names)) {
			names = [names];
		}
		for (var i=0, len=names.length; i<len; i++) {
			if (names[i].indexOf('*') != -1) {
				var q = names[i].replace('*', '');
				Ext.Object.each(this.stores, function(name, store){
					if (name.indexOf(q) !== -1) {
						stores.push(store);
					}
				});
			} else if (this.stores[names[i]]) {
				stores.push(this.stores[names[i]]);
			}
		}
		return stores;
	},
	fireRefresh: function(names){
		var stores = this.query(names);
		for (var i=0, len=stores.length; i<len; i++) {
			stores[i].fireEvent('refresh');
		}
	},
	load: function(names, callback, reload, lock) {
		var me = this,
			stores = this.query(names),
			requests = [], requestsMap = {};
		for (var i=0, len=stores.length; i<len; i++) {
			if ((reload && stores[i].dataLoaded) || !reload && !stores[i].dataLoaded) {
				if (requestsMap[stores[i].dataUrl] === undefined) {
					requests.push({
						url: stores[i].dataUrl,
						stores: []
					})
					requestsMap[stores[i].dataUrl] = requests.length - 1;
				}
				requests[requestsMap[stores[i].dataUrl]].stores.push(stores[i].name);
			}
		}

		var resumeEventsList = [],
			firstRun = true,
			runRequest = function() {
				if (requests.length) {
					var request = requests.shift();
					var r = {
						url: request.url,
						params: {
							stores: Ext.encode(request.stores)
						},
						success: function (data, response, options) {
							Ext.Object.each(data.stores, function(name, data){
								me.stores[name].suspendEvents(true);
								resumeEventsList.push(name);
								me.stores[name].loadData(data);
								me.stores[name].dataLoaded = true;
							});
							firstRun = false;
							runRequest();
						}
					};
					if (firstRun && lock) {
						r.processBox = {type: 'action'};
					} else {
						r.disableFlushMessages = true;
						r.disableAutoHideProcessBox =true;
					}
					Scalr.Request(r);
				} else {
					for (var i=0, len=resumeEventsList.length; i<len; i++) {
						me.stores[resumeEventsList[i]].resumeEvents();
					}
					callback ? callback() : null;
				}
			}
		runRequest();
	},
	reload: function(names, lock, callback) {
		lock = lock === undefined ? true : lock;
		this.load(names, callback, true, lock);
	},
	reloadDefer: function(names) {
		var stores = this.query(names);
		for (var i=0, len=stores.length; i<len; i++) {
			stores[i].dataLoaded = false;
		}
	}
};

Ext.getBody().setStyle('overflow', 'hidden');
Ext.tip.QuickTipManager.init();

Ext.state.Manager.setProvider(new Ext.state.LocalStorageProvider({ prefix: 'scalr-' }));

Scalr.event.on('close', function(force) {
	Scalr.state.pageSuspendForce = Ext.isBoolean(force) ? force : false;

	if (history.length > 1)
		history.back();
	else
		document.location.href = "#/dashboard";
});

Scalr.event.on('redirect', function(href, force, params) {
	Scalr.state.pageSuspendForce = Ext.isBoolean(force) ? force : false;
	Scalr.state.pageRedirectParams = params || {};
	document.location.href = href;
});

Scalr.event.on('lock', function(hide) {
	Scalr.state.pageSuspend = true;
	Scalr.application.disabledDockedToolbars(true, hide);
});

Scalr.event.on('unlock', function() {
	Scalr.state.pageSuspend = false;
	Scalr.application.disabledDockedToolbars(false);
});

Scalr.event.on('reload', function () {
	document.location.reload();
});

Scalr.event.on('refresh', function (forceReload) {
	// @TODO: forceReload
	window.onhashchange(true);
});

Scalr.event.on('resize', function () {
	Scalr.application.getLayout().onOwnResize();
});

Scalr.event.on('maximize', function () {
	var item = Scalr.application.getLayout().activeItem;
	if (item.scalrOptions.maximize == '') {
		if (item.width)
			item.savedWidth = item.width;
		item.scalrOptions.maximize = 'all';
	} else {
		if (item.savedWidth)
			item.width = item.savedWidth;
		item.scalrOptions.maximize = '';
	}

	Scalr.application.getLayout().onOwnResize();
});

Scalr.event.on('clear', function (url) {
	var hashchange = false;

	Scalr.application.items.each(function () {
		if (this.scalrRegExp && this.scalrRegExp.test(url)) {
			if (Scalr.application.getLayout().activeItem == this)
				hashchange = true;

			this.close();
			return false;
		}
	});

	if (hashchange)
		window.onhashchange(true);
});

Ext.Ajax.timeout = 60000;

(function preload(){
	var url = [
		'/ui2/images/icons/loading.gif',
		'/ui2/js/extjs-4.1/theme/images/topmenu/scalr_logo_icon_36x27.png'
	];

	for (var i = 0; i < url.length; i++) {
		var image = new Image();
		image.src = url[i];
	}
})();
