(function() {
	var handler = function (conn, response, options) {
		try {
			if (response.getResponseHeader) {
				var s = response.getResponseHeader('X-Scalr-Debug');
				if (Ext.isDefined(s)) {
					s = s.split('\n');
					for (var i = 0; i < s.length; i++) {
						try {
							console.debug(Ext.decode(s[i]));
						} catch (e) {
							console.debug(s[i]);
						}
					}
				}
			}
		} catch (e) {
			console.error(e);
		}
	};

	Ext.Ajax.on('requestcomplete', handler);
	Ext.Ajax.on('requestexception', handler);
})();

Ext.apply(Ext.EventManager, {
	_createListenerWrap: Ext.EventManager.createListenerWrap,
	createListenerWrap: function (dom, ename, fn, scope, options) {
		var wrap = this._createListenerWrap(dom, ename, fn, scope, options),
			newWrap = function () {
				try {
					return wrap.apply(this, arguments);
				} catch (e) {
					Scalr.utils.PostException(e);
				}
			};

		return newWrap;
	}
});
