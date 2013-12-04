Scalr.regPage('Scalr.ui.core.disaster', function (loadParams, moduleParams) {
	
	var pbar2 = Ext.create('Ext.ProgressBar', {
        text:'Executing random scripts on your servers...',
        id:'pbar2',
        cls:'left-align',
        style: {
        	margin: 20
        }
    });
	
	var eCnt = 0;
	
	var panel = Ext.create('Ext.form.Panel', {
		width: 700,
		bodyCls: 'x-panel-body-frame',
		title: 'Infrastructure disaster status',
		bodyPadding: 5,
		fieldDefaults: {
			anchor: '100%',
			labelWidth: 130
		},
		items: [{
			xtype: 'displayfield',
			hideLabel: true,
			value: '<br /><br />'
		}, pbar2, {
			xtype: 'displayfield',
			hideLabel: true,
			value: '<br /><br />'
		}],

		dockedItems: [{
			xtype: 'container',
			cls: 'x-docked-bottom-frame',
			dock: 'bottom',
			layout: {
				type: 'hbox',
				pack: 'center'
			},
			items: [{
				xtype: 'button',
				text: 'Abort',
				handler: function() {
					eCnt++;
					
					if (eCnt > 2)
						Scalr.message.Error('Not yet implemented // Guys, I know this is a joke for April Fool\'s, but this is actually dangerous code and can screw things up badly for people.');
					else if (eCnt == 2)
						Scalr.message.Error('Service temporary unavailable. Please try again.');
					else
						Scalr.message.Error('Unable to process your request at the moment. Please try again.');
				}
			}]
		}]
	});
	
	var items = [
	             'Removing backups',
	             'Corrupting volumes',
	             'Removing row(s) from user tables',
	             'Reticulating splines',
	             'Publishing SSH keys on 4chan',
	             'Redirecting traffic to bit.ly/amazing_horse'
	];
	
	var Runner = function(){
        var f = function(v, pbar, btn, count, cb){
            return function(){
                if(v > count){
                    cb();
                }else{
                	
                	var n = parseInt(v / 50);
                	
                	pbar.updateProgress(v/count, items[n]);
                }
           };
        };
        return {
            run : function(pbar, btn, count, cb) {
                var ms = 50;
                for(var i = 1; i < (count+2); i++){
                   setTimeout(f(i, pbar, btn, count, cb), i*ms);
                }
            }
        };
    }();
    
    Runner.run(pbar2, null, parseInt(items.length)*50, function() {
        pbar2.reset();
        pbar2.updateText('$899 has been charged to your card ending in **** 6001. Thank you for your business!');
    });
	
	return panel;
});
