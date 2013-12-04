Ext.define('Scalr.ui.FarmBuilderSelRoles', {
	extend: 'Ext.panel.Panel',
	alias: 'widget.farmselroles',
    layout: 'fit',
    
    initComponent: function() {
        var me = this;
        me.callParent();
        me.add({
            xtype: 'dataview',
            store: me.store,
            preserveScrollOnRefresh: true,
            cls: 'scalr-ui-dataview-boxes scalr-ui-dataview-selroles',
            plugins: [{
                ptype: 'flyingbutton',
                cls: 'scalr-ui-dataview-selroles-add',
                handler: function(){
                    this.fireEvent('addrole');
                }
            },{
                ptype: 'viewdragdrop',
                pluginId: 'viewdragdrop',
                offsetY: 50
            }],
            deferInitialRefresh: false,
            tpl  : new Ext.XTemplate(
                '<tpl for=".">',
                    '<div class="x-item" title="{name}">',
                        '<div class="x-item-color-corner-role x-item-color-corner x-item-color-corner-{[Scalr.utils.getColorById(values.farm_role_id || 0)]}"></div>',
                        '<div class="x-item-inner">',
                            '<div class="name">',
                                '<tpl if="name.length &gt; 11">',
                                    '{[this.getName(values.name)]}',
                                '<tpl else>',
                                    '{name}',
                                '</tpl>',
                            '</div>',
                            '<div class="icon scalr-ui-icon-role-medium scalr-ui-icon-role-medium-{[this.getRoleCls(values)]}"></div>',
                            '<div class="platform">{platform}</div>',
                            '<div class="location" title="{cloud_location}">{cloud_location}</div>',
                        '</div>',
                        '<div class="delete-role-bg"></div>',
                        '<div class="delete-role" title="Delete role from farm"></div>',
                        '<div class="launchindex" title="Launch index">{[values.launch_index+1]}</div>',
                    '</div>',
                '</tpl>'			
            ,{
                getRoleCls: function (context) {
                    var behaviors = [
                            "cf_cchm", "cf_dea", "cf_router", "cf_service",
                            "rabbitmq", "www", 
                            "app", "tomcat", 'haproxy',
                            "mysqlproxy", 
                            "memcached", 
                            "cassandra", "mysql", "mysql2", "percona", "postgresql", "redis", "mongodb", 'mariadb'
                        ];
                    
                    if (context['behaviors']) {
                        //Handle CF all-in-one role
                        if (context['behaviors'].match("cf_router") && context['behaviors'].match("cf_cloud_controller") && context['behaviors'].match("cf_health_manager") && context['behaviors'].match("cf_dea")) {
                            return 'cf-all-in-one';
                        }
                        //Handle CF CCHM role
                        if (context['behaviors'].match("cf_cloud_controller") || context['behaviors'].match("cf_health_manager")) {
                            return 'cf-cchm';
                        }

                        var b = (context['behaviors'] || '').split(',');
                        for (var i=0, len=b.length; i < len; i++) {
                            for (var k = 0; k < behaviors.length; k++ ) {
                                if (behaviors[k] == b[i]) {
                                    return b[i].replace('_', '-');
                                }
                            }
                        }
                    }

                    return 'base';
                    
                },
                getName: function (n) {
                    return n.substr(0, 5) + '...' + n.substr(n.length - 5, 5);
                }
            }),
            emptyText: 'No roles found',
            loadingText: 'Loading roles ...',
            deferEmptyText: false,

            itemSelector: '.x-item',
            overItemCls : 'x-item-over',
            padding: '48 10 0 10',
            trackOver: true,
            overflowY: 'scroll',
            width: 120,
            onUpdate: function () {
                this.refresh();
            },
            listeners: {
                itemadd: function(record, index, node) {
                    this.scrollBy(0, 9000, true);
                    if (node.length) {
                        var box = Ext.fly(node[0]);
                        box.animate({
                            duration: 800,
                            keyframes: {
                                25: {opacity: .3},
                                50: {opacity: 1},
                                75: {opacity: .3},
                                100: {opacity: 1}
                            }
                        });
                    }
                },
                drop: function(node, data, record, position) {
                    if (data.records[0]) {
                        var newLaunchIndex = record.get('launch_index') + (position=='after' ? 1 : 0),
                            scrollTop = this.el.getScroll().top;
                        this.suspendLayouts();
                        data.records[0].store.updateLaunchIndex(data.records[0], newLaunchIndex);
                        this.resumeLayouts(true);
                        this.el.scrollTo('top', scrollTop);
                    }
                },
                beforecontainerclick: function(){//prevent deselect on container click
                    return false;
                },
                beforeitemclick: function (view, record, item, index, e) {
                    if (e.getTarget('.delete-role', 10, true)) {

                        if (record.get('is_bundle_running') == true) {
                            Scalr.message.Error('This role is locked by server snapshot creation process. Please wait till snapshot will be created.');
                            return false;
                        }

                        Scalr.Confirm({
                            type: 'delete',
                            msg: 'Delete role "' + record.get('name') + '" from farm?',
                            success: function () {
                                view.store.remove(record);
                                view.refresh();
                            }
                        });
                        return false;
                    }
                }
             }
        });
        me.addDocked([{
            xtype: 'container',
            dock: 'top',
            layout: 'hbox',
            padding: '11 0 9 10',
            margin: '1 '+ Ext.getScrollbarSize().width +' 0 0',
            cls: 'scalr-ui-selroles-filter',
            overlay: true,
            items: [{
                xtype: 'filterfield',
                itemId: 'livesearch',
                width: 100,
                hideFilterIcon: true,
                store: me.store,
                filterFields: ['name', 'platform', 'cloud_location'],
                listeners: {
                    afterfilter: function() {
                        me.down('dataview').getStore().sort();//if launch order was updated on filtered store we must to re-apply sorting
                    }
                }
            }]
  		},{
			itemId: 'farmbutton',
			dock: 'bottom',
			margin: '0 '+ Ext.getScrollbarSize().width +' 0 0',
			xtype: 'button',
			cls: 'scalr-ui-farm-settings-btn',
			text: 'Farm settings',
			overlay: true,
			pressed: false,
			enableToggle: true,
			listeners: {
				toggle: function(c, state) {
                    this.up('panel').fireEvent('farmsettings', state);
				}
			},
			height: 33,
			iconCls: 'icon'
      }]);
        
    },
    
    onRender: function() {
        var me = this;
        me.callParent(arguments);
        me.relayEvents(me.down('dataview'), ['viewready', 'addrole', 'selectionchange']);
    },

    deselectAll: function() {
        this.down('dataview').getSelectionModel().deselectAll();
    },
    
    select: function(record) {
        this.down('dataview').getSelectionModel().select(record);
    },
    
    clearFilter: function() {
        this.down('#livesearch').clearFilter();
    },
    
    toggleLaunchOrder: function(value) {//true - enable, false - disable
        var dataview = this.down('dataview');
        dataview.getPlugin('viewdragdrop').dragZone[!value?'lock':'unlock']();
        dataview[value?'addCls':'removeCls']('scalr-ui-dataview-selroles-sortable');
    },
    
    toggleFarmButton: function(pressed) {
        var b = this.down('#farmbutton');
        b.suspendEvents(false);
        b.toggle(pressed);
        b.resumeEvents();
    }
});

//button Add role
Ext.define('Scalr.ui.FarmRolesFlyingButton', {
	extend: 'Ext.AbstractPlugin',
	alias: 'plugin.flyingbutton',
	handler: Ext.emptyFn,

	init: function(client) {
		var me = this;
		me.client = client;
		me.client.on({
			beforerender: function(){
				this.width += Ext.getScrollbarSize().width;
			},
			afterrender: function(){
				me.button = Ext.DomHelper.append(this.up('panel').el.dom, '<div class="'+me.cls+'" title="Add role to farm"><div class="x-item-inner"><span>Add <br/>new role</span></div></div>', true);
				me.button.on('click', function(){
					me.handler.apply(me.client);
				});
			},
			resize: {
                fn:me.updatePosition,
                scope: me
            },
			itemremove: {
                fn:me.updatePosition,
                scope: me
            },
			itemadd: {
                fn:me.updatePosition,
                scope: me
            },
			refresh: {
                fn:me.updatePosition,
                scope: me
            }
		})
	},

	updatePosition: function() {
		if (this.button) {
            var buttonTop = '';
			if (this.client.el.dom.scrollHeight <= this.client.el.getHeight()) {
                buttonTop = (this.client.getStore().getCount()*112+42)+'px';
			}
            if (buttonTop !== this.buttonTop) {
                this.button.setStyle('top', buttonTop);
                this.buttonTop = buttonTop;
            }
		}
	}

});

/*roles drag and drop*/
Ext.define('Scalr.ui.FarmRolesDragZone', {
    extend: 'Ext.view.DragZone',
    onInitDrag: function(x, y) {
        var me = this,
            data = me.dragData,
            view = data.view,
            selectionModel = view.getSelectionModel(),
            record = view.getRecord(data.item),
            e = data.event;

        // Update the selection to match what would have been selected if the user had
        // done a full click on the target node rather than starting a drag from it
		/* Changed */
        /*if (!selectionModel.isSelected(record)) {
            selectionModel.select(record, true);
        }*/
        data.records = [record];//selectionModel.getSelection();
		/* End */
        me.ddel.update(me.getDragText());
        me.proxy.update(me.ddel.dom);
        me.onStartDrag(x, y);
        return true;
    }
});

Ext.define('Scalr.ui.FarmRolesDropZone', {
    extend: 'Ext.view.DropZone',
	/* Changed */
	offsetY: 0,
	/* End */
    positionIndicator: function(node, data, e) {
        var me = this,
            view = me.view,
            pos = me.getPosition(e, node),
            overRecord = view.getRecord(node),
            draggingRecords = data.records,
            indicatorY;
        if (!Ext.Array.contains(draggingRecords, overRecord) && (
            pos == 'before' && !me.containsRecordAtOffset(draggingRecords, overRecord, -1) ||
            pos == 'after' && !me.containsRecordAtOffset(draggingRecords, overRecord, 1)
        )) {
            me.valid = true;
            
            if (me.overRecord != overRecord || me.currentPosition != pos || me.getIndicator().hidden) {
				/* Changed */
                indicatorY = Ext.fly(node).getY() - view.el.getY() - 1 - this.offsetY;
                if (pos == 'after') {
                    indicatorY += Ext.fly(node).getHeight() + 9;
                } else {
					indicatorY -= 3;
				}

				me.getIndicator().setWidth(Ext.fly(node).getWidth()+5).showAt(0, indicatorY);
				/* End */

                // Cache the overRecord and the 'before' or 'after' indicator.
                me.overRecord = overRecord;
                me.currentPosition = pos;
            }
        } else {
            me.invalidateDrop();
        }
    },
	handleNodeDrop : Ext.emptyFn

});

Ext.define('Scalr.ui.FarmRolesDragDrop', {
    extend: 'Ext.AbstractPlugin',
    alias: 'plugin.viewdragdrop',

    uses: [
        'Ext.view.ViewDragZone',
        'Ext.view.ViewDropZone'
    ],

    dragText : 'move role to the new launch position',
    ddGroup : "ViewDD",
    enableDrop: true,

    enableDrag: true,
	offsetY: 0,
	handleNodeDrop: Ext.emptyFn,
	
    init : function(view) {
        view.on('render', this.onViewRender, this, {single: true});
    },

    destroy: function() {
        Ext.destroy(this.dragZone, this.dropZone);
    },

    enable: function() {
        var me = this;
        if (me.dragZone) {
            me.dragZone.unlock();
        }
        if (me.dropZone) {
            me.dropZone.unlock();
        }
        me.callParent();
    },

    disable: function() {
        var me = this;
        if (me.dragZone) {
            me.dragZone.lock();
        }
        if (me.dropZone) {
            me.dropZone.lock();
        }
        me.callParent();
    },

    onViewRender : function(view) {
        var me = this;

        if (me.enableDrag) {
            me.dragZone = new Scalr.ui.FarmRolesDragZone({
                view: view,
                ddGroup: me.dragGroup || me.ddGroup,
                dragText: me.dragText
            });
        }

        if (me.enableDrop) {
            me.dropZone = new Scalr.ui.FarmRolesDropZone({
                view: view,
                ddGroup: me.dropGroup || me.ddGroup,
				offsetY: me.offsetY
            });
        }
    }
});