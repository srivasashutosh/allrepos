Scalr.regPage('Scalr.ui.farms.builder.tabs.variables', function (moduleTabParams) {
	return Ext.create('Scalr.ui.FarmsBuilderTab', {
		tabTitle: 'Global variables',
        itemId: 'variables',
		labelWidth: 200,
        
        layout: 'anchor',
		isEnabled: function (record) {
			return true;
		},

		showTab: function (record) {
			this.down('variablefield').setValue(record.get('variables'));
		},

		hideTab: function (record) {
			record.set('variables', this.down('variablefield').getValue());
		},

		items: [{
			xtype: 'fieldset',
			autoScroll: true,
			items: [{
				xtype: 'variablefield',
				name: 'variables',
				currentScope: 'farmrole',
				maxWidth: 1200
			}]
		}]
	});
});
