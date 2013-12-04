Scalr.regPage('Scalr.ui.tools.aws.iam.serverCertificates.view', function (loadParams, moduleParams) {
	var store = Ext.create('store.store', {
		fields: [ 'name','path','arn','id','upload_date' ],
		proxy: {
			type: 'scalr.paging',
			url: '/tools/aws/iam/serverCertificates/xListCertificates/'
		},
		remoteSort: true
	});

	return Ext.create('Ext.grid.Panel', {
		title: 'Server Certificates &raquo; View',
		scalrOptions: {
			'reload': false,
			'maximize': 'all'
		},
		store: store,
		stateId: 'grid-tools-aws-iam-serverCertificates-view',
		stateful: true,
		plugins: {
			ptype: 'gridstore'
		},
		tools: [{
			xtype: 'gridcolumnstool'
		}],
		viewConfig: {
			emptyText: 'No server certificates found',
			loadingText: 'Loading certificates ...'
		},

		columns: [
			{ header: "ID", width: 250, dataIndex: 'id', sortable: false },
			{ header: "Name", flex: 1, dataIndex: 'name', sortable: false },
			{ header: "Path", flex: 1, dataIndex: 'path', sortable: false },
			{ header: "Arn", flex: 1, dataIndex: 'arn', sortable: false },
			{ header: "Upload date", width: 200, dataIndex: 'upload_date', sortable: false }
		],

		dockedItems: [{
			xtype: 'scalrpagingtoolbar',
			store: store,
			dock: 'top',
			afterItems: [{
				ui: 'paging',
				iconCls: 'x-tbar-add',
				handler: function() {
					Scalr.event.fireEvent('redirect', '#/tools/aws/iam/servercertificates/create');
				}
			}]
		}]
	});
});
