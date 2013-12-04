Ext.ns('Scalr.utils');

Scalr.utils.CreateProcessBox = function (config) {
	var messages = {
		delete: 'Deleting ...',
		reboot: 'Rebooting ...',
		terminate: 'Terminating ...',
		launch: 'Launching ...',
		save: 'Saving ...'
	};
	config = config || {};
	config['msg'] = config['msg'] || messages[config['type']] || 'Processing ...';

	return Scalr.utils.Window({
		title: config['msg'],
		width: 298,
		bodyStyle: 'padding-top: 0px',
		items: [{
			xtype: 'component',
			style: 'margin-bottom: 10px; margin-top: 15px',
			html: '<span class="scalr-ui-progress-bar"></span>'
		}],
		closeOnEsc: false
	});
};

Scalr.utils.CloneObject = function (o) {
	if (o == null || typeof(o) != 'object')
		return o;

	if(o.constructor == Array)
		return [].concat(o);

	var t = {};
	for (var i in o)
		t[i] = Scalr.utils.CloneObject(o[i]);

	return t;
};

Scalr.utils.Confirm = function (config) {
	var a = '';
	switch (config['type']) {
		case 'delete':
			a = 'Delete'; break;
		case 'reboot':
			a = 'Reboot'; break;
		case 'terminate':
			a = 'Terminate'; break;
		case 'launch':
			a = 'Launch'; break;
	};

	if (config.objects) {
		config.objects.sort();
		var r = '<span style="font-weight: 700;">' + config.objects.shift() + '</span>';
		if (config.objects.length)
			r = r + ' and <span title="' + config.objects.join("\n") + '" style="font-weight: 700; border-bottom: 1px dashed #000080;">' + config.objects.length + ' others</span>';

		config.msg = config.msg.replace('%s', r);
	}

	config['ok'] = config['ok'] || a;
	config['closeOnSuccess'] = config['closeOnSuccess'] || false;
	var items = [], winConfig = {
		width: config.formWidth || 400,
		title: config.title || null,
		items: items,
		dockedItems: [{
			xtype: 'container',
			dock: 'bottom',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				xtype: 'btn',
				cls: 'x-button-text-large x-button-text-focus',
				text: config['ok'] || 'OK',
				width: 160,
				itemId: 'buttonOk',
				disabled: config['disabled'] || false,
				handler: function () {
					var values = this.up('#box').down('#form') ? this.up('#box').down('#form').getValues() : {};

					if (! config.closeOnSuccess)
						this.up('#box').close();

					if (config.success.call(config.scope || this.up('#box'), values, this.up('#box') ? this.up('#box').down('#form') : this) && config.closeOnSuccess) {
						this.up('#box').close();
					}
				}
			}, {
				xtype: 'btn',
				cls: 'x-button-text-large',
				text: 'Cancel',
				width: 160,
				margin: '0 0 0 16',
				handler: function () {
					this.up('#box').close();
				}
			}]
		}]
	};

	if (Ext.isDefined(config.type)) {
		items.push({
			xtype: 'component',
			cls: 'x-panel-confirm-message' + (config.multiline ? ' x-panel-confirm-message-multiline' : ''),
			style: Ext.isDefined(config.form) ? 'padding: 0 0 22px 0' : '',
			data: config,
			tpl: '<div class="icon icon-{type}"></div><div class="message">{msg}</div>'
		});
	}

	if (Ext.isDefined(config.form)) {
		var form = {
			layout: 'anchor',
			itemId: 'form',
			xtype: 'form',
			border: false,
			defaults: {
				anchor: '100%'
			},
			items: config.form
		};

		if (Ext.isDefined(config.formValidate)) {
			form.listeners = {
				validitychange: function (form, valid) {
					if (valid)
						this.up('#box').down('#buttonOk').enable();
					else
						this.up('#box').down('#buttonOk').disable();
				},
				boxready: function () {
					if (this.form.hasInvalidField())
						this.up('#box').down('#buttonOk').disable();
				}
			};
		}

		items.push(form);
	}

	var c = Scalr.utils.Window(winConfig);
	if (! Ext.isDefined(config.form)) {
		c.keyMap.addBinding({
			key: Ext.EventObject.ENTER,
			fn: function () {
				var btn = this.down('#buttonOk');
				btn.handler.call(btn);
			},
			scope: c
		});
	}

	return c;
};

Scalr.utils.Window = function(config) {
	config = Ext.applyIf(config, {
		xtype: 'panel',
		itemId: 'box',
		floating: true,
		modal: true,
		shadow: false,
		border: false,
		cls: 'x-panel-confirm',
		width: 400,
		maxHeight: Scalr.application.getHeight() - 10, // padding from top and bottom
		autoScroll: true,
		titleAlign: 'center',
		closeOnEsc: true
	});

	var c = Ext.widget(config);
	c.keyMap = new Ext.util.KeyMap(Ext.getBody(), [{
		key: Ext.EventObject.ESC,
		fn: function () {
			if (this.closeOnEsc)
				this.close();
		},
		scope: c
	}]);

	c.on('destroy', function () {
		this.keyMap.destroy();
	});

	c.show(config.animationTarget || null);
	c.center();
	c.toFront();

	return c;
};

Scalr.utils.Request = function (config) {
	var currentUrl = document.location.href;

	config = Ext.apply(config, {
		callback: function (options, success, response) {
			if (!options.disableAutoHideProcessBox && options.processBox)
				options.processBox.destroy();

            if (success == true && response.responseText && (Ext.isDefined(response.status) ? response.status == 200 : true)) {
				// only for HTTP Code = 200 (for fake ajax upload files doesn't exist response status)
				//try {
                var result = Ext.decode(response.responseText, config.disableHandleError);

                if (result && result.success == true) {
                    if (result.successMessage)
                        Scalr.message.Success(result.successMessage);

                    if (result.warningMessage)
                        Scalr.message.Warning(result.warningMessage);

                    options.successF.call(this, result, response, options);
                    /*try {
                        options.successF.call(this, result, response, options);
                    } catch (e) {
                        Scalr.message.Error('Success handler error:' + e);
                    }*/
                    return true;
                } else {
                    if (result && result.errorMessage)
                        Scalr.message.Error(result.errorMessage);

                    options.failureF.call(this, result, response, options);
                    /*try {
                        options.failureF.call(this, result, response, options);
                    } catch (e) {
                        Scalr.message.Error('Failure handler error:' + e);
                    }*/
                    return;
                }
			}

			if ((response.status == 500 || (!response.responseText && response.status != 0)) && !options.disableHandleError) {
				Scalr.utils.PostError({
					message: 'responseText is null in ajax request\nRequest:\n' + Scalr.utils.VarDump(response.request.options.params || {}) +
						'\nresponse headers: \n' + response && Ext.isFunction(response.getAllResponseHeaders) ? Scalr.utils.VarDump(response.getAllResponseHeaders()) : '' +
						'\nresponse text: \n' + response.responseText,
					url: document.location.href
				});
			}

			//if (!response.responseText && Ext.isDefined(response.status) ? response.status == 200 : true)

			// else nothing, global error handler used (if status code != 200)
			options.failureF.call(this, null, response, options);
		}
	});

	//config.disableFlushMessages = !!config.disableFlushMessages;
	//if (! config.disableFlushMessages)
	//	Scalr.message.Flush();

	config.disableAutoHideProcessBox = !!config.disableAutoHideProcessBox;
    config.disableHandleError = !!config.disableHandleError;
    config.hideErrorMessage = !!config.hideErrorMessage;

	config.successF = config.success || function () {};
	config.failureF = config.failure || function () {};
	config.scope = config.scope || config;
	config.params = config.params || {};

	delete config.success;
	delete config.failure;

	var pf = function (config) {
		if (config.processBox) {
			config.processBox = Scalr.utils.CreateProcessBox(config.processBox);
		}

		if (config.form) {
			config['success'] = function (form, action) {
				action.callback.call(this, action, true, action.response);
			};

			config['failure'] = function (form, action) {
				// investigate later, in extjs 4
				action.callback.call(this, action, /*(action.response.status == 200) ? true : false*/ true, action.response);
			};
			config['clientValidation'] = false;

			if (config.form.hasUpload()) {
				config.params['X-Requested-With'] = 'XMLHttpRequest';
			}

			config.form.submit(config);
		} else {
			return Ext.Ajax.request(config);
		}
	};

	if (Ext.isObject(config.confirmBox)) {
		config.confirmBox['success'] = function (params) {
			delete config.confirmBox;

			if (Ext.isDefined(params))
				Ext.applyIf(config.params, params);

			pf(config);
		};

		Scalr.Confirm(config.confirmBox);
	} else {
		return pf(config);
	}
};

Scalr.utils.UserLoadFile = function (path) {
	Ext.Function.defer(
		Ext.getBody().createChild({
			tag: 'iframe',
			src: path,
			width: 0,
			height: 0,
			frameborder: 0
		}).remove, 1000
	);
};

Scalr.utils.VarDump = function (c) {
	var d = [];
	for (var s in c) {
		if (Ext.isString(c[s]))
			d.push(s + ': ' + c[s]);
	}

	d = d.join("\n");

	return d;
};

Scalr.utils.ThrowDebug = function (c) {
	var d = [];
	for (var s in c) {
		if (Ext.isString(c[s]))
			d.push(s + ': ' + c[s]);
	}

	d = d.join("\n");
	throw d;
};

Scalr.utils.PostException = function(e) {
	Scalr.utils.PostError({
		message: e.message + "\nstack: " + e.stack + "\ntype: " + e.type + "\nname: " + e.name,
		url: document.location.href
	});
};

Scalr.utils.PostError = function(params) {
    if (params['file'] && params['file'] == 'runtime')
        return;

    if (params['message']) {
        if (params['message'] == 'Script error.')
            return;
    } else
        return;

	Scalr.Request({
		url: '/guest/xPostError',
        hideErrorMessage: true,
        disableHandleError: true,
		params: params
	});

	//Scalr.message.Warning("Whoops! Something went wrong, and we have been notified. Try reloading the page if things don't work.");
};

Scalr.utils.IsEqualValues = function (obj1, obj2) {
	for (var i in obj1) {
		if (! Ext.isDefined(obj2[i]) && obj1[i] == obj2[i])
			return false;
	}

	return true;
}

Scalr.utils.getGravatarUrl = function (emailHash, size) {
	size = size || 'small';
	var sizes = {small: 23, large: 46},
		defaultIcon = window.location.protocol + '//' + window.location.hostname + '/ui2/images/ui/account/avatar_default_' + size + '.png';
	return emailHash ? 'https://gravatar.com/avatar/' + emailHash + '?d=' + encodeURIComponent(defaultIcon) + '&s=' + sizes[size] : defaultIcon;
}

Scalr.utils.getColorById = function(id) {
    if (Ext.isNumeric(id) && id > 0) {
        var goldenRatio = 0.618033988749895,
            colors = [
                'D90000', '00B3D9', 'EC8200', '839A01', 'E916E3', '0000B9', 'B08500', '006C1C', '000000', '8B02F0'
            ],
            colorIndex = Math.floor((id * goldenRatio - Math.floor(id * goldenRatio)) * (colors.length - 1));
        return colors[colorIndex] ? colors[colorIndex] : colors[0];
    } else {
        return 'transparent';
    }
}

Scalr.utils.beautifyOsFamily = function(osfamily) {
    var map = {
        unknown: 'Unknown',
        ubuntu: 'Ubuntu',
        centos: 'CentOS',
        gcel: 'GCEL',
        rhel: 'RHEL',
        redhat: 'RedHat',
        oel: 'OEL',
        debian: 'Debian',
        amazon: 'Amazon Linux',
        windows: 'Windows'
    };

    return map[osfamily] || osfamily;
}

Scalr.utils.beautifySoftware = function(name) {
    var map = {
        apache: 'Apache',
        base: 'Base',
        lamp: 'LAMP',
        memcached: 'Memcached',
        mongodb: 'MongoDB',
        nginx: 'Ngnix',
        postgresql: 'PostgreSQL',
        rabbitmq: 'RabbitMQ',
        redis: 'Redis',
        tomcat: 'Tomcat',
        vpcrouter: 'VPC Router',
        percona: 'Percona',
        mariadb: 'MariaDB',
        mysql: 'MySQL',
        chef: 'Chef',
        haproxy: 'HAProxy'
    };

    return map[name] || name;
}

/*
 CryptoJS v3.1.2
 code.google.com/p/crypto-js
 (c) 2009-2013 by Jeff Mott. All rights reserved.
 code.google.com/p/crypto-js/wiki/License
 http://crypto-js.googlecode.com/svn/tags/3.1.2/build/rollups/sha1.js
 */
var CryptoJS=CryptoJS||function(e,m){var p={},j=p.lib={},l=function(){},f=j.Base={extend:function(a){l.prototype=this;var c=new l;a&&c.mixIn(a);c.hasOwnProperty("init")||(c.init=function(){c.$super.init.apply(this,arguments)});c.init.prototype=c;c.$super=this;return c},create:function(){var a=this.extend();a.init.apply(a,arguments);return a},init:function(){},mixIn:function(a){for(var c in a)a.hasOwnProperty(c)&&(this[c]=a[c]);a.hasOwnProperty("toString")&&(this.toString=a.toString)},clone:function(){return this.init.prototype.extend(this)}},
	n=j.WordArray=f.extend({init:function(a,c){a=this.words=a||[];this.sigBytes=c!=m?c:4*a.length},toString:function(a){return(a||h).stringify(this)},concat:function(a){var c=this.words,q=a.words,d=this.sigBytes;a=a.sigBytes;this.clamp();if(d%4)for(var b=0;b<a;b++)c[d+b>>>2]|=(q[b>>>2]>>>24-8*(b%4)&255)<<24-8*((d+b)%4);else if(65535<q.length)for(b=0;b<a;b+=4)c[d+b>>>2]=q[b>>>2];else c.push.apply(c,q);this.sigBytes+=a;return this},clamp:function(){var a=this.words,c=this.sigBytes;a[c>>>2]&=4294967295<<
		32-8*(c%4);a.length=e.ceil(c/4)},clone:function(){var a=f.clone.call(this);a.words=this.words.slice(0);return a},random:function(a){for(var c=[],b=0;b<a;b+=4)c.push(4294967296*e.random()|0);return new n.init(c,a)}}),b=p.enc={},h=b.Hex={stringify:function(a){var c=a.words;a=a.sigBytes;for(var b=[],d=0;d<a;d++){var f=c[d>>>2]>>>24-8*(d%4)&255;b.push((f>>>4).toString(16));b.push((f&15).toString(16))}return b.join("")},parse:function(a){for(var c=a.length,b=[],d=0;d<c;d+=2)b[d>>>3]|=parseInt(a.substr(d,
		2),16)<<24-4*(d%8);return new n.init(b,c/2)}},g=b.Latin1={stringify:function(a){var c=a.words;a=a.sigBytes;for(var b=[],d=0;d<a;d++)b.push(String.fromCharCode(c[d>>>2]>>>24-8*(d%4)&255));return b.join("")},parse:function(a){for(var c=a.length,b=[],d=0;d<c;d++)b[d>>>2]|=(a.charCodeAt(d)&255)<<24-8*(d%4);return new n.init(b,c)}},r=b.Utf8={stringify:function(a){try{return decodeURIComponent(escape(g.stringify(a)))}catch(c){throw Error("Malformed UTF-8 data");}},parse:function(a){return g.parse(unescape(encodeURIComponent(a)))}},
	k=j.BufferedBlockAlgorithm=f.extend({reset:function(){this._data=new n.init;this._nDataBytes=0},_append:function(a){"string"==typeof a&&(a=r.parse(a));this._data.concat(a);this._nDataBytes+=a.sigBytes},_process:function(a){var c=this._data,b=c.words,d=c.sigBytes,f=this.blockSize,h=d/(4*f),h=a?e.ceil(h):e.max((h|0)-this._minBufferSize,0);a=h*f;d=e.min(4*a,d);if(a){for(var g=0;g<a;g+=f)this._doProcessBlock(b,g);g=b.splice(0,a);c.sigBytes-=d}return new n.init(g,d)},clone:function(){var a=f.clone.call(this);
		a._data=this._data.clone();return a},_minBufferSize:0});j.Hasher=k.extend({cfg:f.extend(),init:function(a){this.cfg=this.cfg.extend(a);this.reset()},reset:function(){k.reset.call(this);this._doReset()},update:function(a){this._append(a);this._process();return this},finalize:function(a){a&&this._append(a);return this._doFinalize()},blockSize:16,_createHelper:function(a){return function(c,b){return(new a.init(b)).finalize(c)}},_createHmacHelper:function(a){return function(b,f){return(new s.HMAC.init(a,
	f)).finalize(b)}}});var s=p.algo={};return p}(Math);
(function(){var e=CryptoJS,m=e.lib,p=m.WordArray,j=m.Hasher,l=[],m=e.algo.SHA1=j.extend({_doReset:function(){this._hash=new p.init([1732584193,4023233417,2562383102,271733878,3285377520])},_doProcessBlock:function(f,n){for(var b=this._hash.words,h=b[0],g=b[1],e=b[2],k=b[3],j=b[4],a=0;80>a;a++){if(16>a)l[a]=f[n+a]|0;else{var c=l[a-3]^l[a-8]^l[a-14]^l[a-16];l[a]=c<<1|c>>>31}c=(h<<5|h>>>27)+j+l[a];c=20>a?c+((g&e|~g&k)+1518500249):40>a?c+((g^e^k)+1859775393):60>a?c+((g&e|g&k|e&k)-1894007588):c+((g^e^
	k)-899497514);j=k;k=e;e=g<<30|g>>>2;g=h;h=c}b[0]=b[0]+h|0;b[1]=b[1]+g|0;b[2]=b[2]+e|0;b[3]=b[3]+k|0;b[4]=b[4]+j|0},_doFinalize:function(){var f=this._data,e=f.words,b=8*this._nDataBytes,h=8*f.sigBytes;e[h>>>5]|=128<<24-h%32;e[(h+64>>>9<<4)+14]=Math.floor(b/4294967296);e[(h+64>>>9<<4)+15]=b;f.sigBytes=4*e.length;this._process();return this._hash},clone:function(){var e=j.clone.call(this);e._hash=this._hash.clone();return e}});e.SHA1=j._createHelper(m);e.HmacSHA1=j._createHmacHelper(m)})();

// shorter name
Scalr.Confirm = Scalr.utils.Confirm;
Scalr.Request = Scalr.utils.Request;
