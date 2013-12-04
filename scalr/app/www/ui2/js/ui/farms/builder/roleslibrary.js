Ext.define('Scalr.ui.RolesLibrary', {
	extend: 'Ext.container.Container',
	alias: 'widget.roleslibrary',
    
    cls: 'x-panel-body-frame-dark scalr-ui-roleslibrary',
    padding: 0,
    vpc: null,
    mode: null, //shared|custom
    layout: 'fit',
    initComponent: function() {
        this.callParent(arguments);
        this.on({
            activate: function() {
                var newVpcRegion, oldVpcRegion, leftcol, defaultCatId = 'shared';
                oldVpcRegion = this.vpc ? this.vpc.region : false;
                this.vpc = this.up('#fbcard').down('#farm').getVpcSettings();
                newVpcRegion = this.vpc ? this.vpc.region : false;
                if (!this.mode) {
                    this.getComponent('tabspanel').getDockedComponent('tabs').down('[catId="'+defaultCatId+'"]').toggle(true);
                } else if (newVpcRegion !== oldVpcRegion) {
                    leftcol = this.down('#leftcol');
                    leftcol.deselectCurrent();
                    leftcol.refreshStoreFilter();
                }
                
            }
        });
    },
    
    items: {
        xtype: 'panel',
        flex: 1,
        layout: {
            type: 'hbox',
            align: 'stretch'
        },
        itemId: 'tabspanel',
        bodyStyle: 'background: #DFE4EA',
        dockedItems: [{
            dock: 'left',
            width: 170,
            border: false,
            cls: 'scalr-ui-farmbuilder-tabs',
            autoScroll: true,
            itemId: 'tabs',
            margin: '12 0 12 0',
            listeners: {
                boxready: function(){
                    var me = this, index = 0;
                    me.suspendLayouts();
                    me.addCategoryBtn({
                        name: 'Quick start',
                        catId: 'shared',
                        cls: 'scalr-ui-farmbuilder-button-quickstart'
                    });
                    Ext.Object.each(me.up('roleslibrary').moduleParams.categories, function(key, value) {
                        me.addCategoryBtn({
                            name: value.name,
                            catId: value.id,
                            total: value.total,
                            cls: index ? '' : 'scalr-ui-farmbuilder-button-first'
                        });
                        index++;
                    });
                    me.resumeLayouts(true);
                },
                add: function(panel, btn) {
                    if (btn.catId === 'search') {
                        var buttons = panel.query('[catId="search"]');
                        if (buttons.length > 3) {
                            this.up('#farmbuilder').cache.removeCache({
                                url: '/roles/xGetList2',
                                params: {catId: 'search', keyword: buttons[0].keyword}
                            })
                            panel.remove(buttons[0]);
                        }
                    }
                    panel.refreshButtonsCls();
                },
                remove: function(panel, btn) {
                    panel.refreshButtonsCls();
                    if (btn.pressed) {
                        var last = panel.items.last();
                        if (last) {
                            last.toggle(true);
                        }
                    }
                }
            },
            refreshButtonsCls: function(){
                var last = this.items.getAt(this.items.length-2);
                if (last) {
                    last.removeCls('scalr-ui-farmbuilder-button-last');
                }
                last = this.items.last();
                if (last) {
                    last.addCls('scalr-ui-farmbuilder-button-last');
                }
            },
            addCategoryBtn: function(data) {
                var btn = {
                    xtype: 'btn',
                    allowDepress: false,
                    disableMouseDownPressed: true,
                    text: data.name  + (data.total == 0 ? '<span class="deprecated">empty</span>' : ''),
                    cls: (data.total == 0 ? 'scalr-ui-farmbuilder-button-deprecated ' : '') + (data.cls || ''),
                    catId: data.catId,
                    keyword: data.keyword,
                    toggleGroup: 'tabs' + this.id,
                    toggleHandler: this.toggleHandler,
                    scope: this
                };
                //button remove search
                if (data.catId === 'search') {
                    btn.listeners = {
                        boxready: function(){
                            var me = this;
                            this.btnEl.on({
                                mouseenter: function(){
                                    me.btnDeleteEl = Ext.DomHelper.append(me.btnEl.dom, '<div class="delete-search" title="Remove search"></div>', true)
                                    me.btnDeleteEl.on('click', function(){
                                        me.up('#farmbuilder').cache.removeCache({
                                            url: '/roles/xGetList2',
                                            params: {catId: 'search', keyword: me.keyword}
                                        })
                                        me.ownerCt.remove(me);
                                    });
                                },
                                mouseleave: function(){
                                    me.btnDeleteEl.remove();
                                    delete me.btnDeleteEl;
                                    
                                }
                            });
                        }
                    };

                }
                return this.add(btn);
            },
            toggleHandler: function(btn, pressed) {
                if (pressed) {
                    this.ownerCt.getComponent('leftcol').selectCategory(btn.catId, btn.keyword);
                }
            }
        }],
        items: [{
            itemId: 'leftcol',
            cls: 'x-panel-columned-leftcol',
            layout: 'card',
            items:[{
                xtype: 'component',
                itemId: 'blank'
            },{
                xtype: 'component',
                itemId: 'emptytext',
                cls: 'emptytext',
                listeners: {
                    boxready: function() {
                        var me = this;
                        this.on({
                            click: {
                                fn: function(e, el) {
                                    var links = this.query('a.add-link');
                                    if (links.length) {
                                        for (var i=0, len=links.length; i<len; i++) {
                                            if (e.within(links[i])) {
                                                switch (links[i].getAttribute('data-action')) {
                                                    case 'server-search':
                                                        me.up('#leftcol').createSearch();
                                                    break;
                                                    case 'migrate':
                                                        Scalr.event.fireEvent('redirect', '#/servers/import2', true);
                                                    break;
                                                    case 'builder':
                                                        Scalr.event.fireEvent('redirect', '#/roles/builder', true);
                                                    break;
                                                }
                                                break;
                                            }
                                        }
                                        e.preventDefault();
                                    }
                                },
                                element: 'el'
                            }
                        });
                    }
                }
            }],
            listeners: {
                boxready: function() {
                    this.fillPlatformFilter();
                }
            },
            createSearch: function() {
                var searchField = this.down('#search'),
                    keyword = searchField.getValue();
                if (keyword) {
                    var tabs = this.up('#tabspanel').getDockedComponent('tabs'),
                        res = tabs.query('[keyword="'+keyword+'"]');
                    if (res.length === 0) {
                        (tabs.addCategoryBtn({
                            name: 'Search "<b>' + keyword + '</b>"',
                            catId: 'search',
                            keyword: keyword,
                            cls: 'scalr-ui-farmbuilder-button-search'
                        })).toggle(true);
                    } else {
                        res[0].toggle(true);
                        searchField.setValue(null);
                    }
                }
            },
            selectCategory: function(catId, keyword){
                var me = this,
                    mode = catId === 'shared' ? 'shared' : 'custom',
                    searchField = me.down('#search');
                this.catId = catId;
                this.deselectCurrent();
                me.up('roleslibrary').mode = mode;
                if (mode === 'shared') {
                    me.loadRoles({catId: 'shared'}, function(data, status){
                        var view = this.down('#sharedroles');
                        if (!view) {
                            //create fake Chef category - copy of base
                            if (data) {
                                Ext.Array.each(data['software'], function(item){
                                    if (item.name === 'base') {
                                        data['software'].push({
                                            name: 'chef',
                                            ordering: item.ordering + 1,
                                            roles: item.roles
                                        });
                                    }
                                });
                            }
                            view = me.add(me.sharedRolesViewConfig);
                            view.store.loadData(data ? data['software'] : []);
                        }
                        me.fillOsFilter(view.store);
                        me.refreshStoreFilter();
                        if (view.store.getCount() > 0) {
                            me.getLayout().setActiveItem(view);
                        }
                    });
                } else {
                    var view = this.down('#customroles'),
                        params = {catId: catId};
                    if (catId === 'search') {
                        params.keyword = keyword;
                    }
                    me.loadRoles(params, function(data, status){
                        var view = this.down('#customroles');
                        if (!view) {
                            view = me.add(me.customRolesViewConfig);
                        }
                        view.store.loadData(data ? data['roles'] : []);
                        me.fillOsFilter(view.store);
                        if (catId === 'search') {
                            searchField.setValue(null);
                        }
                        me.refreshStoreFilter();
                        if (view.store.getCount() > 0) {
                            me.getLayout().setActiveItem(view);
                            searchField.focus();
                        }
                    });
                }

                searchField.setVisible(mode !== 'shared');
            },
            deselectCurrent: function() {
                var view = this.down(this.up('roleslibrary').mode === 'shared' ? '#sharedroles' : '#customroles');
                if (view) {
                    view.getSelectionModel().deselectAll();
                }
            },

            loadRoles: function(params, cb) {
                this.up('#farmbuilder').cache.load(
                    {
                        url: '/roles/xGetList2',
                        params: params
                    },
                    cb,
                    this,
                    0
                );
            },

            getFilterValue: function(name) {
                var filter = this.down('#filters').down('#' + name),
                    value = filter ? filter.getValue() : null;
                value = name === 'search' ? Ext.String.trim(value) : value;
                return value;
            },

            refreshStoreFilter: function() {
                var rl = this.up('roleslibrary'),
                    vpc = rl.vpc,
                    mode = rl.mode,
                    view = this.down(mode === 'shared' ? '#sharedroles' : '#customroles'),
                    store;
                store = view.store;
                store.clearFilter(true);
                store.filter([{filterFn: Ext.Function.bind(this.storeFilterFn, this, [
                    mode,
                    this.getFilterValue('platform'),
                    this.getFilterValue('os'),
                    this.getFilterValue('search'),
                    vpc !== false ? vpc.region : null
                ], true)}]);
                this.refreshEmptyText(view);        
            },

            refreshEmptyText: function(view) {
                var emptyText = this.getComponent('emptytext');

                if (view.getStore().getCount() === 0) {
                    var rl = this.up('roleslibrary'),
                        filterCategory = this.catId,
                        filterPlatform = this.getFilterValue('platform'),
                        category,
                        platform = rl.moduleParams.platforms[filterPlatform] ? rl.moduleParams.platforms[filterPlatform].name : filterPlatform,
                        filterOs = this.getFilterValue('os'),
                        text;
                    if (filterCategory === 'shared') {
                        category = 'Shared';
                    } else if (rl.moduleParams.categories[filterCategory]) {
                        category = rl.moduleParams.categories[filterCategory].name;
                    }
                    
                    if (filterCategory !== 'shared' && !Ext.isEmpty(this.getFilterValue('search'))) {
                        text = '<div class="title">No roles were found to match your search' + (category ? ' in category <span style="white-space:nowrap">&laquo;' + category + '&raquo;</span>' : '') + '.</div>' +
                               '<a class="add-link" data-action="server-search" href="#">Click here</a> to search across all categories.';
                    } else {
                        if (filterCategory === 'search'){
                            text = '<div class="title">No roles were found to match your search.</div>';
                        } else  if (filterPlatform || filterOs) {
                            text = '<div class="title">No' + (category ? ' <span style="white-space:nowrap">&laquo;' + category + '&raquo;</span>' : '') + ' roles found ';
                            if (filterPlatform) {
                                text += 'on <span style="color:#555;white-space:nowrap">' + platform + '</span> cloud';
                            }
                            if (filterOs) {
                                text += ' with <span style="color:#555">' + Scalr.utils.beautifyOsFamily(filterOs) + '</span> operating system'; 
                            }
                            text += '.</div>';
                        } else {
                            text = '<div class="title">No roles were found in the selected category.</div>';
                        }
                        
                        text = this.addEmptyTextExtraButtons(text, filterPlatform);
                    }
                    
                    emptyText.getEl().setHTML(text);
                    this.getLayout().setActiveItem(emptyText);
                } else {
                    this.getLayout().setActiveItem(view);
                }
            },
            
            addEmptyTextExtraButtons: function(text, filterPlatform) {
                if (this.isRoleBuilderIconVisible(filterPlatform)) {
                    text += '<div class="x-items-extra"><a class="add-link x-item-extra" href="#" data-action="builder">'+
                            '<span class="x-item-inner">'+
                                '<span class="icon scalr-ui-icon-behavior-large scalr-ui-icon-behavior-large-mixed"></span>'+
                                '<span class="title">Build role</span>'+
                            '</span>'+
                            '</a><span class="title x-item-extra-delimiter">or</span>';
                } else {
                    text += '<div class="x-items-extra single">';
                }

                text += '<a class="add-link x-item-extra" href="#" data-action="migrate">'+
                        '<span class="x-item-inner">'+
                            '<span class="icon scalr-ui-icon-behavior-large scalr-ui-icon-behavior-large-wizard"></span>'+
                            '<span class="title">Migrate server</span>'+
                        '</span>'+
                        '</a>';
                text += '</div>';
                return text;
            },
            
            isRoleBuilderIconVisible: function(platform) {
                var result = false,
                    enabledPlatforms = this.up('roleslibrary').moduleParams.platforms;
                if (platform) {
                    result = Ext.Array.contains(['ec2', 'gce', 'rackspacengus', 'rackspacenguk'], platform);
                } else {
                    Ext.Array.each(['ec2', 'gce', 'rackspacengus', 'rackspacenguk'], function(platform){
                        if (enabledPlatforms[platform] !== undefined) {
                            result = true;
                            return false;
                        }
                    });
                }
                return result;
            },
            
            
            storeFilterFn: function(record, mode, platform, os, keyword, vpcRegion) {
                var res = false, roles, images;
                if (mode === 'shared') {
                    if (!vpcRegion && record.get('name') === 'vpcrouter') {
                        return false;
                    }
                    if (os || platform || vpcRegion) {
                        roles = record.get('roles');
                        for (var i=0, len=roles.length; i<len; i++) {
                            if (!os || (roles[i].os_family || 'unknown') === os) {
                                images = roles[i].images;
                                if (platform) {
                                    Ext.Object.each(images, function(key) {
                                        if (key === platform) {
                                            res = true;
                                            return false;
                                        }
                                    });
                                } else {
                                    res = true;
                                }
                            }
                            if (vpcRegion && res === true) {
                                if (images['ec2'] && (Ext.Object.getSize(images) === 1 || platform === 'ec2') && images['ec2'][vpcRegion] === undefined) {
                                    res = false;
                                }
                            }
                            if (res) break;
                        }
                    } else {
                        res = true;
                    }
                    return res;
                } else {
                    if (!vpcRegion && Ext.Array.contains(record.get('behaviors'), 'router')) {
                        return false;
                    }
                    if (os || platform || keyword || vpcRegion) {
                        if ((!os || (record.get('os_family') || 'unknown')  === os ) && (!keyword || (record.get('name')+'').match(RegExp(Ext.String.escapeRegex(keyword), 'i')))) {
                            images = record.get('images');
                            if (platform) {
                                res = images[platform] !== undefined;
                            } else {
                                res = true;
                            }
                            if (vpcRegion && res === true) {
                                if (images['ec2'] && (Ext.Object.getSize(images) === 1 || platform === 'ec2') && images['ec2'][vpcRegion] === undefined) {
                                    res = false;
                                }
                            }
                        }
                    } else {
                        return true;
                    }
                    return res;
                }

            },

            sharedRolesViewConfig: {
                xtype: 'dataview',
                itemId: 'sharedroles',
                cls: 'scalr-ui-dataview-boxes scalr-ui-dataview-sharedroles',
                itemSelector: '.x-item',
                overItemCls : 'x-item-over',
                padding: '4 0 10 10',
                trackOver: true,
                overflowY: 'scroll',
                margin: '0 -' + Ext.getScrollbarSize().width+ ' 0 0',
                store: {
                    fields: ['name', 'roles', {name: 'ordering', type: 'int'}, 'description'],
                    proxy: 'object',
                    sortOnLoad: true,
                    sortOnFilter: true,
                    sorters: [{
                        property: 'ordering'
                    }]
                },
                tpl  : new Ext.XTemplate(
                    '<tpl for=".">',
                        '<div class="x-item">',
                            '<div class="x-item-inner">',
                                '<div class="scalr-ui-icon-behavior-large scalr-ui-icon-behavior-large-{name}"></div>',
                                '<div class="name">',
                                    '{[Scalr.utils.beautifySoftware(values.name)]}',
                                '</div>',
                            '</div>',
                        '</div>',
                    '</tpl>'			
                ),
                listeners: {
                    beforecontainerclick: function(comp, e){//prevent deselect on container click
                        var result = false,
                            el = comp.el.query('a.add-link');
                        if (el.length) {
                            for (var i=0, len=el.length; i<len; i++) {
                                if (e.within(el[i])) {
                                    result = true;
                                    break;
                                }
                            }
                        }
                        return result;
                    },
                    selectionchange: function(comp, selection){
                        var form = this.up('roleslibrary').down('form');
                        if (selection.length) {
                            form.currentRole = selection[0];
                            form.loadRecord(Ext.create('Scalr.FarmRoleModel'));
                        } else {
                            form.hide();
                        }
                    }
                }
            },
            customRolesViewConfig: {
                xtype: 'grid',
                cls: 'x-grid-shadow scalr-ui-roleslist x-grid-dark-focus',
                itemId: 'customroles',
                hideHeaders: true,
                padding: '0 9 9',
                plugins: [{
                    ptype: 'rowpointer',
                    thresholdOffset: 0,
                    addCls: 'x-panel-columned-row-pointer-light'
                }],
                store: {
                    fields: [
                        { name: 'role_id', type: 'int' }, 'cat_id', 'name', 'images', 'behaviors', 'os_name', 'os_family', 'tags', 'roles', 'variables', 'shared', 'description', 'os_generation'
                    ],
                    proxy: 'object',
                    filterOnLoad: true,
                    sortOnLoad: true,
                    sortOnFilter: true,
                    sorters: [{
                        property: 'name',
                        transform: function(value){
                            return value.toLowerCase();
                        }
                    }]
                },
                columns: [{
                    xtype: 'templatecolumn',
                    width: 28,
                    align: 'center',
                    tpl  : new Ext.XTemplate(
                        '<img src="' + Ext.BLANK_IMAGE_URL + '" class="scalr-ui-icon-role-small scalr-ui-icon-role-small-{[this.getRoleCls(values)]}"/>'
                    ,{
                        getRoleCls: function (context) {
                            var b = context['behaviors'],
                                behaviors = [
                                    "cf_cchm", "cf_dea", "cf_router", "cf_service",
                                    "rabbitmq", "www", 
                                    "app", "tomcat", 'haproxy',
                                    "mysqlproxy", 
                                    "memcached", 
                                    "cassandra", "mysql", "mysql2", "percona", "postgresql", "redis", "mongodb", 'mariadb'
                                ];

                            if (b) {
                                //Handle CF all-in-one role
                                if (Ext.Array.difference(['cf_router', 'cf_cloud_controller', 'cf_health_manager', 'cf_dea'], b).length === 0) {
                                    return 'cf-all-in-one';
                                }
                                //Handle CF CCHM role
                                if (Ext.Array.contains(b, 'cf_cloud_controller') || Ext.Array.contains(b, 'cf_health_manager')) {
                                    return 'cf-cchm';
                                }

                                for (var i=0, len=b.length; i < len; i++) {
                                    for (var k = 0; k < behaviors.length; k++ ) {
                                        if (behaviors[k] == b[i]) {
                                            return b[i].replace('_', '-');
                                        }
                                    }
                                }
                            }
                            return 'base';
                        }
                    })
                },{
                    xtype: 'templatecolumn',
                    dataIndex: 'name',
                    flex: 1,
                    tpl: '<tpl if="shared"><span class="shared" title="Pre-made role.">{name}</span><tpl else>{name}</tpl>'
                },{
                    xtype: 'templatecolumn',
                    width: 86,
                    align: 'right',
                    tpl  : new Ext.XTemplate('{[this.renderPlatforms(values.images)]}',{
                        renderPlatforms: function(images) {
                            var res = '';
                            Ext.Object.each(images, function(key){
                                res += '<img src="' + Ext.BLANK_IMAGE_URL + '" class="scalr-ui-icon-platform-small scalr-ui-icon-platform-small-' + key + '"/>';
                            });
                            return res;
                        }
                    })
                },{
                    xtype: 'templatecolumn',
                    width: 30,
                    align: 'right',
                    tpl  : '<img src="' + Ext.BLANK_IMAGE_URL + '" class="scalr-ui-icon-osfamily-small scalr-ui-icon-osfamily-small-{os_family}"/>'
                }],
                listeners: {
                    selectionchange: function(e, selection) {
                        if (selection.length) {
                            var form = this.up('roleslibrary').down('form');
                            form.currentRole = selection[0];
                            form.loadRecord(Ext.create('Scalr.FarmRoleModel'));
                            //this.getView().focus();
                        } else {
                            this.up('roleslibrary').down('form').hide();
                        }
                    }
                }
            },
            fillPlatformFilter: function() {
                var platformFilter = this.down('#platform'),
                    item;

                item = platformFilter.add({
                    text: 'All clouds',
                    value: null,
                    iconCls: 'scalr-ui-icon-osfamily-small'                
                });
                Ext.Object.each(this.up('roleslibrary').moduleParams.platforms, function(key, value) {
                    if (key === 'rds') return;
                    platformFilter.add({
                        text: value.name,
                        value: key,
                        iconCls: 'scalr-ui-icon-platform-small scalr-ui-icon-platform-small-' + key
                    });
                });
                platformFilter.suspendEvents(false);
                platformFilter.setActiveItem(item);
                platformFilter.resumeEvents();
            },

            fillOsFilter: function(store) {
                var me = this,
                    mode = this.up('roleslibrary').mode,
                    os = [],
                    osField = me.down('#filters').down('#os'),
                    currentOsItem = osField.getActiveItem(),
                    menuItem;

                (store.snapshot || store.data).each(function(rec) {
                    if (mode === 'shared') {
                        Ext.Array.each(rec.get('roles'), function(role){
                            os.push(role.os_family || 'unknown');
                        });
                    } else {
                        os.push(rec.get('os_family') || 'unknown');
                    }
                });

                os = Ext.Array.unique(os);
                os = Ext.Array.sort(os)
                osField.removeAll();
                menuItem = osField.add({
                    text: 'All operating systems',
                    value: null,
                    iconCls: 'scalr-ui-icon-osfamily-small'                
                });
                for (var i=0, len=os.length; i<len; i++) {
                    var tmpMenuItem = osField.add({
                        text: Scalr.utils.beautifyOsFamily(os[i]),
                        value: os[i],
                        iconCls: 'scalr-ui-icon-osfamily-small scalr-ui-icon-osfamily-small-' + os[i]
                    });
                    if (currentOsItem !== undefined && tmpMenuItem.value === currentOsItem.value) {
                        menuItem = tmpMenuItem;
                    }
                }
                osField.suspendEvents(false);
                osField.setActiveItem(menuItem);
                osField.resumeEvents();            
            },
            dockedItems: {
                 xtype: 'container',
                 itemId: 'filters',
                 dock: 'top',
                 padding: '16 10 10',
                 cls: 'scalr-ui-roleslibrary-filter',
                 layout: 'hbox',
                 items: [{
                     xtype: 'cyclebtn',
                     itemId: 'platform',
                     maskOnDisable: true,
                     cls: 'x-button-text-dark',
                     icon: true,
                     prependText: 'Cloud: ',
                     text: 'Cloud: All',
                     width: 100,
                     margin: '0 10 0 0',
                     padding: '0 16 0 8',
                     changeHandler: function(comp, item) {
                         this.up('#leftcol').refreshStoreFilter();
                     },
                     getItemText: function(item) {
                         return item.value ? '<img src="' + Ext.BLANK_IMAGE_URL + '" class="' + item.iconCls + '" title="' + item.text + '" />' : 'All';
                     },
                     menu: {
                         cls: 'x-menu-light x-menu-role-cycle-filter',
                         minWidth: 200,
                         items: []
                     }
                 },{
                     xtype: 'cyclebtn',
                     itemId: 'os',
                     cls: 'x-button-text-dark',
                     icon: true,
                     prependText: 'OS: ',
                     text: 'OS: All',
                     width: 100,
                     margin: '0 10 0 0',
                     padding: '0 16 0 8',
                     changeHandler: function(comp, item) {
                         this.up('#leftcol').refreshStoreFilter();
                     },
                     getItemText: function(item) {
                         return item.value ? '<img src="' + Ext.BLANK_IMAGE_URL + '" class="' + item.iconCls + '" title="' + item.text + '"/>' : 'All';
                     },
                     menu: {
                         cls: 'x-menu-light x-menu-role-cycle-filter',
                         minWidth: 200,
                         items: []
                     }
                 },{
                     xtype: 'tbfill'
                 },{
                    xtype: 'filterfield',
                    itemId: 'search',
                    minWidth: 100,
                    maxWidth: 210,
                    flex: 100,
                    filterId: 'keyword',
                    emptyText: 'Filter',
                    hideFilterIcon: true,
                    filterFn: Ext.emptyFn,
                    listeners: {
                        boxready: function() {
                            this.btnSearchAll = this.bodyEl.up('tr').createChild({
                                tag: 'td',
                                style: 'width: 24px',
                                html: '<div class="x-filterfield-btn" title="Search across all categories"><div class="x-filterfield-btn-inner"></div></div>'
                            }).down('div');
                            this.triggerWrap.applyStyles('border-radius: 3px 0 0 3px');
                            this.btnSearchAll.addCls('disabled');
                            this.btnSearchAll.on('click', function(){
                                if (!this.btnSearchAll.hasCls('disabled')) {
                                    this.up('#leftcol').createSearch();
                                }
                            }, this);
                        },
                        afterfilter: function () {
                            this.up('#leftcol').refreshStoreFilter();
                        },
                        change: {
                            fn: function(comp, value) {
                                this.btnSearchAll[Ext.String.trim(value) !== '' ? 'removeCls' : 'addCls']('disabled');
                                this.up('#leftcol').refreshStoreFilter();
                            },
                            buffer: 300
                        }
                    }
                 }]
             }            
        },{
            xtype: 'container',
            itemId: 'rightcol',
            cls: 'x-panel-columned-rightcol',
            style: 'background:#f0f1f4',
            padding: 0,
            overflowY: 'scroll',
            flex: 1,
            plugins: [{
                ptype: 'adjustwidth'
            }],
            items: {
                xtype: 'form',
                itemId: 'addrole',
                hidden: true,
                padding: '12 0',
                bodyStyle: 'padding-bottom: 12px',
                minWidth: 530,
                state: {},
                defaults: {
                    margin: 0,
                    padding: '0 24'
                },
                items: [{
                    xtype: 'displayfield',
                    name: 'name',
                    fieldCls: 'x-fieldset-header',
                    fieldStyle: 'padding:0',
                    renderer: function(value) {
                        return Ext.String.capitalize(value);
                    },
                    margin: 0
                },{
                    xtype: 'displayfield',
                    name: 'description',
                    margin: '0 0 10 0'
                },{
                    xtype: 'component',
                    cls: 'x-fieldset-delimiter',
                    margin: '6 0 1 0'
                },{
                    xtype: 'container',
                    itemId: 'imageinfo',
                    maxWidth: 760,
                    hidden: true,
                    layout: 'column',
                    defaults: {
                        columnWidth: .5
                    },
                    items: [{
                        xtype: 'container',
                        padding: '18 18 12 0',
                        defaults: {
                            labelWidth: 60,
                            margin: '0 0 12 0'
                        },
                        items: [{
                            xtype: 'displayfield',
                            fieldLabel: 'Cloud',
                            name: 'display_platform'
                        },{
                            xtype: 'displayfield',
                            fieldLabel: 'Location',
                            name: 'display_location'
                        }]
                    },{
                        xtype: 'container',
                        padding: '18 0 12 18',
                        defaults: {
                            labelWidth:70,
                            margin: '0 0 12 0'
                        },
                        items: [{
                            xtype: 'displayfield',
                            fieldLabel: 'OS',
                            name: 'display_os_name'
                        },{
                            xtype: 'displayfield',
                            fieldLabel: 'Behaviors',
                            name: 'display_behaviors'
                        },{
                            xtype: 'displayfield',
                            fieldLabel: 'Root device type',
                            name: 'display_root_device_type',
                            hidden: true
                        }]
                    }]
                },{
                    xtype: 'container',
                    itemId: 'imageoptions',
                    maxWidth: 760,
                    hidden: true,
                    layout: {
                        type: 'hbox',
                        align: 'stretch'
                    },
                    items: [{
                        xtype: 'container',
                        itemId: 'leftcol',
                        flex: 1,
                        layout: 'anchor',
                        defaults: {
                            anchor: '100%'
                        },
                        cls: 'x-delimiter-vertical',
                        padding: '18 18 12 0',
                        items: [{
                            xtype: 'label',
                            text: 'Cloud:'
                        },{
                            xtype: 'buttongroupfield',
                            name: 'platform',
                            margin: '8 0',
                            defaults: {
                                xtype: 'btn',
                                baseCls: 'x-button-icon',
                                margin: '0 6 6 0'
                            },
                            listeners: {
                                change: function(comp, value){
                                    this.up('form').fireEvent('selectplatform', value);
                                }
                            }
                        },{
                            layout: {
                                type: 'hbox',
                                pack: 'center'
                            },
                            margin: '0 0 10 0',
                            items: {
                                xtype: 'cloudlocationmap',
                                itemId: 'locationmap',
                                listeners: {
                                    selectlocation: function(location, state){
                                        var form = this.up('form').getForm(),
                                            record = form.getRecord();
                                        if (record.get('platform') === 'gce') {
                                            var field = form.findField('gce.cloud-location'),
                                                value;
                                            if (field) {
                                                value = Ext.clone(field.getValue());
                                                if (state) {
                                                    if (!Ext.Array.contains(value, location)) {
                                                        value.push(location);
                                                    }
                                                } else {
                                                    if (value.length === 1) {
                                                        Scalr.message.Warning('At least one cloud location must be selected!');
                                                    } else {
                                                        Ext.Array.remove(value, location);
                                                    }
                                                }
                                                field.setValue(value);
                                            }
                                        } else {
                                            form.findField('cloud_location').setValue(location);
                                        }
                                    }
                                }
                            }
                        },{
                            xtype: 'combo',
                            name: 'cloud_location',
                            editable: false,
                            fieldLabel: 'Location',
                            labelWidth: 70,
                            anchor: '100%',
                            valueField: 'id',
                            displayField: 'name',
                            queryMode: 'local',
                            store: {
                                fields: ['id', 'name'],
                                sorters: {
                                    property: 'name'
                                }
                            },
                            listeners: {
                                change: function(comp, value) {
                                    this.up('form').fireEvent('selectlocation', value);
                                }
                            }
                        }]
                    },{
                        xtype: 'container',
                        itemId: 'rightcol',
                        padding: '18 0 12 18',
                        flex: 1,
                        layout: {
                            type: 'vbox',
                            align: 'stretch'
                        },
                        defaults: {
                            labelWidth: 80,
                            margin: '0 0 12 0'
                        },
                        items: [{
                            xtype: 'container',
                            itemId: 'osfilters',
                            layout: 'anchor',
                            margin: '0 0 18 0',
                            defaults: {
                                anchor: '100%',
                                labelWidth: 80
                            },
                            items: [{
                                xtype: 'label',
                                text: 'Operating system:'
                            },{
                                xtype: 'buttongroupfield',
                                name: 'osfamily',
                                margin: '8 0',
                                defaults: {
                                    xtype: 'btn',
                                    baseCls: 'x-button-icon',
                                    margin: '0 6 6 0'
                                },
                                listeners: {
                                    change: function(comp, value){
                                        this.up('form').fireEvent('selectosfamily', value);
                                    }
                                }
                            },{
                                xtype: 'combo',
                                name: 'osname',
                                editable: false,
                                valueField: 'id',
                                displayField: 'name',
                                queryMode: 'local',
                                store: {
                                    fields: ['id', 'name']
                                },
                                listeners: {
                                    change: function(comp, value) {
                                        this.up('form').fireEvent('selectosname', value);
                                    }
                                }
                            },{
                                xtype: 'container',
                                margin: '14 0 0 0',
                                layout: 'hbox',
                                items: [{
                                    xtype: 'buttongroupfield',
                                    name: 'arch',
                                    maxWidth: 160,
                                    margin: '0 12 0 0',
                                    //fieldLabel: 'Architecture',
                                    flex: 1,
                                    layout: 'hbox',
                                    defaults: {
                                        flex: 1
                                    },
                                    items: [{
                                        value: 'x86_64',
                                        text: '64 bit',
                                        margin: '0 0 0 3'
                                    },{
                                        value: 'i386',
                                        text: '32 bit',
                                        margin: '0 3 0 0'
                                    }],
                                    listeners: {
                                        change: function(comp, value) {
                                            this.up('form').fireEvent('selectarch', value);
                                        }
                                    }
                                },{
                                    xtype: 'btnfield',
                                    name: 'hvm',
                                    hidden: true,
                                    text: 'HVM',
                                    enableToggle: true,
                                    toggleHandler: function() {
                                        var form = this.up('form');
                                        if (form.mode === 'shared') {
                                            form.fireEvent('selecthvm', this.pressed ? 1 : 0);
                                        }
                                    }
                                }]
                            },{
                                xtype: 'combo',
                                name: 'roleid',
                                editable: false,
                                fieldLabel: 'Role',
                                valueField: 'id',
                                displayField: 'name',
                                queryMode: 'local',
                                hidden: true,
                                margin: '14 0 0 0',
                                store: {
                                    fields: ['id', 'name']
                                },
                                listeners: {
                                    change: function(comp, value) {
                                        this.up('form').fireEvent('selectroleid', value);
                                    }
                                }
                            }]
                        },{
                            xtype: 'displayfield',
                            fieldLabel: 'OS',
                            name: 'display_os_name'
                        },{
                            xtype: 'displayfield',
                            fieldLabel: 'Behaviors',
                            name: 'display_behaviors'
                        },{
                            xtype: 'displayfield',
                            fieldLabel: 'Root device type',
                            name: 'display_root_device_type',
                            hidden: true
                        }]
                    }]
                }],
                dockedItems: [{
                    xtype: 'container',
                    dock: 'bottom',
                    cls: 'x-toolbar',
                    maxWidth: 760,
                    layout: {
                        type: 'hbox',
                        pack: 'center'
                    },
                    defaults: {
                        xtype: 'btn',
                        width: 140,
                        cls: 'x-button-text-large x-button-text-dark'
                    },
                    items: [{
                        itemId: 'save',
                        text: 'Add to farm',
                        handler: function () {
                            var formPanel = this.up('form'),
                                form = formPanel.getForm(),
                                rolesLibrary = this.up('roleslibrary'),
                                values = {},
                                record = form.getRecord(),
                                role = formPanel.getCurrentRole(),
                                image;
                                
                            if (!formPanel.isExtraSettingsValid(record)) {
                                return;
                            }
                            
                            values.platform = form.findField('platform').getValue();
                            values.cloud_location = form.findField('cloud_location').getValue();

                            image = role.images[values.platform][values.platform === 'gce' ? '' : values.cloud_location]
                            if (formPanel.mode === 'shared') {
                                Ext.apply(values, {
                                    behaviors: role.behaviors,
                                    role_id: role.role_id,
                                    generation: role.generation,
                                    os: role.os_name,
                                    os_name: role.os_name,
                                    os_family: role.os_family,
                                    os_generation: role.os_generation,
                                    os_version: role.os_version,
                                    name: role.name,
                                    cat_id: role.cat_id
                                });
                            }

                            Ext.apply(values, {
                                image_id: image['image_id'],
                                arch: image.architecture,
                                settings: formPanel.getExtraSettings(record) || {}
                            });
                            //console.log(values.settings)
                            record.set(values);
                            if (rolesLibrary.fireEvent('addrole', record)) {
                                rolesLibrary.down('#leftcol').deselectCurrent();
                            }
                        }
                    }, {
                        itemId: 'cancel',
                        text: 'Cancel',
                        margin: '0 0 0 20',
                        handler: function() {
                            this.up('roleslibrary').down('#leftcol').deselectCurrent();
                        }
                    }]
                }],
                getAvailablePlatforms: function() {
                    var images, 
                        roles, 
                        platforms, 
                        vpc = this.up('roleslibrary').vpc,
                        removeEc2 = false;//we must remove ec2 if there is no   location === vpc.region

                    if (this.mode === 'custom') {
                        images = this.currentRole.get('images');
                        platforms = Ext.Object.getKeys(images);
                        removeEc2 = vpc !== false && images['ec2'] !== undefined && images['ec2'][vpc.region] === undefined;
                    } else {
                        platforms = [];
                        roles = this.currentRole.get('roles');
                        removeEc2 = vpc !== false;
                        for (var i=0, len=roles.length; i<len; i++) {
                            images = roles[i].images;
                            platforms = Ext.Array.merge(platforms, Ext.Object.getKeys(images));
                            if (removeEc2 && vpc !== false) {
                                if (images['ec2'] !== undefined && images['ec2'][vpc.region] !== undefined)
                                    removeEc2 = false;
                            }
                        }
                    }

                    if (removeEc2) {
                        Ext.Array.remove(platforms, 'ec2');
                    }

                    return platforms;
                },

                getAvailableImagesCount: function() {
                    var count = 0;
                    if (this.mode === 'custom') {
                        Ext.Object.each(this.currentRole.get('images'), function(key, value){
                            count += Ext.Object.getSize(value);
                        });
                    } else {
                        Ext.Array.each(this.currentRole.get('roles'), function(role){
                            Ext.Object.each(role.images, function(platform, locations){
                                count += Ext.Object.getSize(locations);
                            });
                        });
                    }
                    return count;
                },

                isExtraSettingsValid: function(record) {
                    var res = true;
                    this.items.each(function(item){
                        if (item.isExtraSettings === true && item.isVisible()) {
                            res = item.isValid(record);
                        }
                        return res;
                    });
                    return res;
                },

                getExtraSettings: function(record) {
                    var settings = {};
                    this.items.each(function(item){
                        if (item.isExtraSettings === true && item.isVisible()) {
                            Ext.apply(settings, item.getSettings(record));
                        }
                    });
                    return settings;
                },

                getCurrentRole: function() {
                    var me = this,
                        role;
                    if (this.mode === 'custom') {
                        role = me.currentRole.getData();
                    } else if (me.state.roleid) {
                        Ext.Array.each(me.currentRole.get('roles'), function(r){
                            if (r.role_id === me.state.roleid) {
                                role = r;
                                return false;
                            }
                        });
                    }
                    return role;
                },
                
                updateRecordSettings: function(name, value) {
                    var me = this,
                        record = me.getForm().getRecord(),
                        settings = record.get('settings', true) || {};
                    settings[name] = value;
                    record.set('settings', settings);
                    if (!me.isLoading) {
                        Ext.Array.each(me.extraSettings, function(module){
                            if (module.onSettingsUpdate !== undefined && module.isVisible()) {
                                module.onSettingsUpdate(record, name, value);
                            }
                        });
                    }
                },
                
                listeners: {
                    beforerender: function() {
                        var me = this;
                        me.extraSettings = [];
                        Ext.Array.each(['ec2', 'vpc', 'rackspace', 'openstack', 'cloudstack', 'gce', 'mongodb', 'dbmsr', 'haproxy'], function(name){//, 'proxy'
                            me.extraSettings.push(me.add(Scalr.cache['Scalr.ui.farms.builder.addrole.' + name]()));
                        });
                    },
                    selectplatform: function(value) {
                        if (Ext.isEmpty(value)) return;

                        var me = this,
                            form = me.getForm(),
                            locations = [],
                            defaultLocation,
                            fieldLocation = form.findField('cloud_location'),
                            vpc = this.up('roleslibrary').vpc;

                        me.state.platform = value;

                        fieldLocation.setVisible(value !== 'gce');
                        form.findField('display_location').setVisible(value !== 'gce');

                        if (this.mode === 'custom') {
                            Ext.Object.each(me.currentRole.get('images')[value] || {}, function(location){
                                locations.push({id: location, name: location});
                                defaultLocation = defaultLocation || location;
                                if (location === 'us-east-1') {
                                   defaultLocation = location;
                                }
                                if (location === me.state.location) {
                                    defaultLocation = me.state.location;
                                }
                            });
                        } else {
                            var uniqueLocations = [];
                            Ext.Array.each(this.currentRole.get('roles'), function(role){
                                Ext.Object.each(role.images[value] || {}, function(location){
                                    Ext.Array.include(uniqueLocations, location);
                                    defaultLocation = defaultLocation || location;
                                    if (location === 'us-east-1') {
                                       defaultLocation = location;
                                    }
                                    if (location === me.state.location) {
                                        defaultLocation = me.state.location;
                                    }
                                });
                            });
                            for (var i=0, len=uniqueLocations.length; i<len; i++) {
                                locations.push({id: uniqueLocations[i], name: uniqueLocations[i]});
                            }
                        }

                        if (value === 'ec2' && vpc !== false) {
                           defaultLocation = vpc.region;
                           locations = [{id: vpc.region, name: vpc.region}];
                        }

                        var cloudLocationReadonly = locations.length < 2;                    
                        fieldLocation.setReadOnly(cloudLocationReadonly);
                        fieldLocation[cloudLocationReadonly ? 'addCls' : 'removeCls']('scalr-ui-cb-readonly');

                        fieldLocation.store.loadData(locations);
                        fieldLocation.setValue(defaultLocation);
                    },

                    selectlocation: function(value) {
                        if (value === null || value === undefined) return;
                        this.suspendLayouts();
                        var me = this,
                            form = me.getForm(),
                            locations = [],
                            osFamilyField,
                            osFamilies,
                            defaultOsFamily;

                        me.state.location = value;
                        if (me.state.platform !== 'gce') {
                            form.findField('cloud_location').store.data.each(function(){locations.push(this.get('id'))});
                            me.down('#locationmap').selectLocation(me.state.platform, me.state.location, locations, 'world');
                        }

                        if (me.mode === 'shared') {
                            //fill os families
                            osFamilyField = form.findField('osfamily');
                            osFamilies = [];
                            Ext.Array.each(me.currentRole.get('roles'), function(role){
                                if (role.images[me.state.platform] && role.images[me.state.platform][me.state.location]) {
                                    Ext.Array.include(osFamilies, (role.os_family || 'unknown'));
                                    defaultOsFamily = defaultOsFamily || (role.os_family || 'unknown');
                                    if ((role.os_family || 'unknown') === 'ubuntu') {
                                        defaultOsFamily = 'ubuntu';
                                    }

                                }
                            });
                            osFamilyField.reset();
                            osFamilyField.removeAll();
                            for (var i=0, len=osFamilies.length; i<len; i++) {
                                osFamilyField.add({
                                    value: osFamilies[i] || 'unknown',
                                    innerCls: 'scalr-ui-icon-osfamily scalr-ui-icon-osfamily-' + (osFamilies[i] || 'unknown'),
                                    cls: 'x-button-icon-medium',
                                    tooltip: Scalr.utils.beautifyOsFamily(osFamilies[i]) || 'Unknown',
                                    tooltipType: 'title'
                                });
                            }
                            osFamilyField.setValue(defaultOsFamily);

                        } else {
                            this.fireEvent('roleimagechange');
                        }
                        this.resumeLayouts(true);
                    },

                    selectosfamily: function(value) {
                        if (value === null || value === undefined) return;
                        var me = this,
                            form = me.getForm(),
                            osNameField,
                            osNames,
                            selectedRole;
                        this.suspendLayouts();
                        me.state.osfamily = value;

                        osNameField = form.findField('osname');
                        osNames = [];
                        Ext.Array.each(me.currentRole.get('roles'), function(role){
                            if (role.images[me.state.platform] && role.images[me.state.platform][me.state.location] && (role.os_family || 'unknown') === me.state.osfamily) {
                                Ext.Array.include(osNames, role.os_name);
                                if (selectedRole === undefined || (parseFloat(selectedRole.os_version) || 0) <  (parseFloat(role.os_version) || 0) && selectedRole.hvm >= role.hvm) {
                                    selectedRole = role;
                                }

                            }
                        });
                        osNames = Ext.Array.map(osNames, function(osname) {
                            return {id: osname, name: osname};
                        });
                        osNameField.reset();
                        osNameField.store.loadData(osNames);
                        osNameField.setValue(selectedRole !== undefined ? selectedRole.os_name : null);
                        osNameField.setReadOnly(osNames.length < 2);
                        osNameField[osNames.length < 2 ? 'addCls' : 'removeCls']('scalr-ui-cb-readonly');
                        this.resumeLayouts(true);
                    },

                    selectosname: function(value) {
                        if (value === null || value === undefined) return;
                        var me = this,
                            form = me.getForm(),
                            archField = form.findField('arch'),
                            archs = {},
                            defaultArch;
                        this.suspendLayouts();
                        me.state.osname = value;

                        Ext.Array.each(me.currentRole.get('roles'), function(role){
                            if (role.images[me.state.platform] && role.images[me.state.platform][me.state.location] && 
                               (role.os_family || 'unknown') === me.state.osfamily && role.os_name === me.state.osname) {
                                var arch = role.images[me.state.platform][me.state.location].architecture;
                                archs[arch] = 1;
                                defaultArch = defaultArch || arch;
                                if (arch === 'x86_64') {
                                    defaultArch = 'x86_64';
                                }
                            }
                        });
                        archField.reset();
                        archField.down('[value="i386"]').setDisabled(archs['i386'] === undefined);
                        archField.down('[value="x86_64"]').setDisabled(archs['x86_64'] === undefined);
                        archField.setValue(defaultArch);
                        this.resumeLayouts(true);
                    },

                    selectarch: function(value) {
                        if (Ext.isEmpty(value)) return;
                        var me = this,
                            form = me.getForm(),
                            hvmField = form.findField('hvm'),
                            hvms = {};
                        this.suspendLayouts();
                        me.state.arch = value;
                        Ext.Array.each(me.currentRole.get('roles'), function(role){
                            if (    
                                role.images[me.state.platform] && role.images[me.state.platform][me.state.location] && 
                                role.images[me.state.platform][me.state.location]['architecture'] === me.state.arch && 
                                (role.os_family || 'unknown') === me.state.osfamily && role.os_name === me.state.osname
                            ) {
                               hvms[role.hvm === 1 ? 1 : 0] = 1;
                            }
                        });

                        if (hvms[1] === 1 && hvms[0] === 1) {
                            hvmField.enable().toggle(0, false);
                        } else {
                            hvmField.disable().toggle(hvms[1] === 1 ? 1 : 0, false);
                        }
                        me.fireEvent('selecthvm', hvmField.pressed ? 1 : 0);
                        hvmField.setVisible(me.state.platform === 'ec2' && hvms[1] !== undefined);
                        this.resumeLayouts(true);
                    },

                    selecthvm: function(value) {
                        var me = this,
                            form = me.getForm();
                            
                        if (form.getRecord().store !== undefined) return;//btnfield doesn't work like normal form field - here is workaround
                        
                        var roleField = form.findField('roleid'),
                            roles = [],
                            defaultRole;
                        this.suspendLayouts();
                        me.state.hvm = value;

                        Ext.Array.each(me.currentRole.get('roles'), function(role){
                            if (
                                role.images[me.state.platform] && role.images[me.state.platform][me.state.location] && 
                                role.images[me.state.platform][me.state.location]['architecture'] === me.state.arch &&
                                (role.os_family || 'unknown') === me.state.osfamily && role.os_name === me.state.osname &&
                                role.hvm == me.state.hvm
                            ) {
                                roles.push({id: role.role_id, name: role.name});
                                defaultRole = defaultRole || role.role_id;
                                if (role.role_id === me.state.roleid) {
                                    defaultRole = me.state.roleid;
                                }

                            }
                        });
                        roleField.reset();
                        roleField.store.loadData(roles);
                        roleField.setValue(defaultRole);
                        roleField.setVisible(roles.length > 1);
                        this.resumeLayouts(true);
                    },

                    selectroleid: function(value) {
                        if (value === null || value === undefined) return;
                        var me = this,
                            imageoptions = me.down('#imageoptions'),
                            behaviorsNames = me.up('roleslibrary').moduleParams.behaviors,
                            role,
                            behaviors = [];
                        this.suspendLayouts();
                        me.state.roleid = value;

                        role = me.getCurrentRole();
                        if (role.behaviors) {
                            Ext.Array.each(role.behaviors, function(b) {
                               behaviors.push(behaviorsNames[b] || b); 
                            });
                        }
                        imageoptions.down('[name="display_behaviors"]').setValue(behaviors.join(', '));

                        //hbox height bug workaround
                       // imageoptions.down('#rightcol').body.setHeight('auto');
                        //imageoptions.down('#leftcol').body.setHeight('auto');

                        this.fireEvent('roleimagechange');
                        this.resumeLayouts(true);
                    },

                    roleimagechange: function() {
                        var form = this.getForm(),
                            record = form.getRecord(),
                            role = this.getCurrentRole(),
                            image = role.images[this.state.platform][this.state.location],
                            values = {
                                platform: this.state.platform,
                                cloud_location: this.state.location,
                                settings: {}
                            },
                            tags = [],
                            description = role.description;

                        if (image.architecture) {
                            values.arch = image.architecture;
                        }

                        if (this.mode === 'shared') {
                            values.os = role.os_name;//bw compatibility
                            values.os_name = role.os_name;
                            values.os_family = role.os_family;
                            values.os_generation = role.os_generation;
                            values.behaviors = role.behaviors;
                        } else {
                            tags = role.tags;
                        }
                        values.tags = tags;
                        record.set(values);
                        
                        this.suspendLayouts();
                        form.findField('description').setVisible(!Ext.isEmpty(description)).setValue(description);
                        if (this.mode === 'custom') {
                            var arch = record.get('arch');
                            this.down(this.down('#imageoptions').isVisible() ? '#imageoptions' : '#imageinfo').setFieldValues({
                                'display_os_name': '<div style="float:left;" class="scalr-ui-icon-osfamily-small scalr-ui-icon-osfamily-small-' + (record.get('os_family') || 'unknown') + '"></div><div style="margin-left:26px">' + record.get('os_name') + (arch ? '&nbsp;(' + (arch === 'i386' ? '32' : '64') + 'bit)' : '') + '</div>'
                            });
                        }

                        //toggle extra settings fieldsets
                        this.items.each(function(item){
                            if (item.isExtraSettings === true) {
                                item.onSelectImage(record);
                            }
                        });
                        this.resumeLayouts(true);
                    },

                    beforeloadrecord: function(record) {
                        this.isLoading = true;
                        this.mode = this.up('roleslibrary').mode;
                        var form = this.getForm(),
                            platformField = form.findField('platform'),
                            rolePlatforms = this.getAvailablePlatforms(),
                            platforms = this.up('roleslibrary').moduleParams.platforms;

                        this.imagesCount = this.getAvailableImagesCount();

                        form.reset();
                        this.suspendLayouts();

                        if (this.imagesCount > 1 || this.mode === 'shared' || Ext.Array.contains(rolePlatforms, 'gce')) {
                            var imageOptions = this.down('#imageoptions');
                            this.down('#imageinfo').hide();
                            imageOptions.down('#osfilters').setVisible(this.mode === 'shared');
                            imageOptions.down('[name="display_os_name"]').setVisible(this.mode !== 'shared');
                            imageOptions.show();
                        } else {
                            this.down('#imageoptions').hide();
                            this.down('#imageinfo').show();
                        }
                        //fill platforms
                        platformField.removeAll();
                        for (var i=0, len=rolePlatforms.length; i<len; i++) {
                            platformField.add({
                                value: rolePlatforms[i],
                                innerCls: 'scalr-ui-icon-platform scalr-ui-icon-platform-' + rolePlatforms[i],
                                cls: 'x-button-icon-medium',
                                tooltip: platforms[rolePlatforms[i]] ? platforms[rolePlatforms[i]].name : rolePlatforms[i],
                                tooltipType: 'title'
                            });
                        }
                        
                        record.set({
                            cloud_location: null,
                            behaviors: this.currentRole.get('behaviors'),
                            role_id: this.currentRole.get('role_id'),
                            generation: this.currentRole.get('generation'),
                            os: this.currentRole.get('os_name'),
                            os_name: this.currentRole.get('os_name'),
                            os_family: this.currentRole.get('os_family'),
                            os_generation: this.currentRole.get('os_generation'),
                            os_version: this.currentRole.get('os_version'),
                            name: this.currentRole.get('name'),
                            cat_id: this.currentRole.get('cat_id'),
                            tags: this.currentRole.get('tags'),
                            variables: this.currentRole.get('variables')
                        });
                        this.resumeLayouts(true);
                    },

                    loadrecord: function(record) {
                        var form = this.getForm(),
                            rolePlatforms = this.getAvailablePlatforms(),
                            platforms = this.up('roleslibrary').moduleParams.platforms,
                            behaviorsNames = this.up('roleslibrary').moduleParams.behaviors,
                            behaviors = [],
                            platform,
                            leftcol = this.up('roleslibrary').down('#leftcol'),
                            platformFilterValue = leftcol.getFilterValue('platform'),
                            osFilterValue = leftcol.getFilterValue('os');

                        if (osFilterValue) {
                            this.state.osfamily = osFilterValue;
                        }

                        if (platformFilterValue) {
                            platform = platformFilterValue;
                        } else if (this.state.platform && Ext.Array.contains(rolePlatforms, this.state.platform)) {
                            platform = this.state.platform;
                        } else if (rolePlatforms[0]) {
                            platform = rolePlatforms[0];
                        }

                        form.findField('platform').setValue(platform);

                        Ext.Array.each(record.get('behaviors'), function(b) {
                           behaviors.push(behaviorsNames[b] || b); 
                        });

                        if (this.mode === 'custom') {
                            this.down(this.down('#imageoptions').isVisible() ? '#imageoptions' : '#imageinfo').setFieldValues({
                                //'display_os_name': '<div style="float:left;" class="scalr-ui-icon-osfamily-small scalr-ui-icon-osfamily-small-' + (record.get('os_family') || 'unknown') + '"></div><div style="margin-left:26px">' + record.get('os_name') + '</div>',
                                'display_behaviors': behaviors.join(', '),
                                'display_platform': '<div style="float:left" class="scalr-ui-icon-platform-small scalr-ui-icon-platform-small-' + rolePlatforms[0] + '"></div><div style="margin-left:26px">' + (platforms[rolePlatforms[0]] ? platforms[rolePlatforms[0]].name : rolePlatforms[0]) + '</div>',
                                'display_location': Ext.Object.getKeys(this.currentRole.get('images')[rolePlatforms[0]]).join(', ')
                            });
                        }

                        this.show();
                        this.isLoading = false;
                    }
                }
            }
        }]
    }
});

Ext.define('Scalr.ui.RolesLibraryAdjustWidth', {
    extend: 'Ext.AbstractPlugin',
    alias: 'plugin.adjustwidth',
	
    resizeInProgress: false,
    
	init: function(client) {
		var me = this;
		me.client = client;
		client.on({
			boxready: function(){
				this.on({
					resize: function(){
                        if (!me.resizeInProgress) {
                            me.adjustWidth();
                        }
					}
				});
			}
		})
	},
	
	adjustWidth: function(){
		var rightcol = this.client,
			leftcol = rightcol.prev(),
            container = leftcol.ownerCt,
            rightColMinWidth = 640,
            extraWidth = 11,
			rowLength = Math.floor((container.getWidth() - rightColMinWidth - extraWidth - container.getDockedComponent('tabs').getWidth())/110);
            
        if (rowLength > 6) {
            rowLength = 6;
        } else if (rowLength < 3) {
            rowLength = 3;
        }
        
        this.resizeInProgress = true;
        leftcol.setWidth(rowLength*110 + extraWidth);
        this.resizeInProgress = false;
	}
	
});
