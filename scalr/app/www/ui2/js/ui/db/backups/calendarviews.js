Ext.define('Scalr.ui.db.backup.DayCalendar', {
	extend: 'Ext.container.Container',
	alias: 'widget.db.backup.daycalendar',

	currentDate: new Date(),
	defaultType: 'container',
	items: [{
		layout: 'hbox',
		defaults: {
			width: '100%',
			height: 26,
			cls: 'scalr-ui-dbbackups-cell-title scalr-ui-dbbackups-cell-title-day'
		}
	}, {
		width: '100%'
	}],
	initComponent: function () {
		this.callParent();
		this.fillTheCalendar();
	},

	fillTheCalendar: function() {
		this.items.getAt(0).add({
			xtype: 'container',
			width: 60
		}, {
			xtype: 'container',
			itemId: 'currentDayName',
			html: Ext.Date.format(this.currentDate, 'j F Y')
		});
		this.fillByHours();
	},

	updateTheCalendar: function() {
		this.down('#currentDayName').el.setHTML(Ext.Date.format(this.currentDate, 'j F Y'));
		this.updateContent();
	},

	updateContent: function () {
		var calendarContainer = this.items.getAt(1);
		var i = 0;
		var j = 0;
		while ( i < 24 ) {
			var rowToFill = calendarContainer.items.getAt(i);
			rowToFill.items.getAt(1).el.setHTML(this.fillByStoreData(i));
			i++;
		}
	},

	fillByHours: function () {
		var calendarContainer = this.items.getAt(1);
		var i = 0;
		var j = 0;
		while ( i < 24 ) {
			calendarContainer.add({
				xtype: 'container',
				layout: 'hbox',
				items: {
					xtype: 'container',
					width: 60,
					height: 40,
					html: this.getHour(i),
					cls: 'scalr-ui-dbbackups-cell-time'
				}
			});
			calendarContainer.items.getAt(i).add({
				xtype: 'container',
				height: 40,
				width: '100%',
				html: this.fillByStoreData(i),
				cls: 'scalr-ui-dbbackups-cell-details'
			});
			i++;
		}
	},

	fillByStoreData: function (hour) {
		var data = '';
		var i = 0;
		var backupsTime = Ext.Object.getKeys(this.dataStore).sort();

		while ( i < backupsTime.length ) {
			if ( backupsTime[i].substr(0, 2) == hour ) {
				var itemData = this.dataStore[backupsTime[i]];
				data += '<div style="float: left;" class="scalr-ui-dbbackups-cell-content" backupId=' + itemData[ 'backup_id' ] + '>'
						+ itemData['date']
						+ itemData['farm']
						+ ' (' + itemData['role'] + ')'
					+ '</div>';
			}

			i++;
		}
		return data;
	},

	getHour: function (hour) {
		return Ext.Date.format(Ext.Date.add(new Date('06/06/2012'), Ext.Date.HOUR, hour), 'g a');
	},

	setCurrentDate: function (newDate) {
		this.currentDate = newDate;
	},

	getCurrentDate: function () {
		return this.currentDate;
	},

	setStoreData: function (data) {
		this.dataStore = data;
		this.updateTheCalendar();
	}
});

Ext.define('Scalr.ui.db.backup.MonthCalendar', {
	extend: 'Ext.container.Container',
	alias: 'widget.db.backup.monthcalendar',

	currentDate: new Date(),
	dataStore: {},
	defaultType: 'container',
	items: [{
		layout: 'hbox',
		defaults: {
			width: ( 100/7.01 ) + '%',
			height: 26,
			cls: 'scalr-ui-dbbackups-cell-title'
		}
	}, {
		itemId: 'calendarInners'
	}],

	/*listeners: {
		resize: function () {
		//	this.resizeCells(this.up().up().height);
			//console.log('resize');
		}
	},*/
	fillTheCalendar: function() {
		this.fillByDayNames();
	},

	updateTheCalendar: function() {
		if(!this.items.getAt(0).items.length)
			this.fillTheCalendar();
		if (this.items.getAt(1))
			this.items.getAt(1).removeAll();
		this.fillByDays();
	},

	fillByDayNames: function() {
		var dayNames = Ext.Date.dayNames;
		var i = 0;
		while ( i < dayNames.length ) {
			this.items.getAt(0).add({
				xtype: 'container',
				html: dayNames[i]
			});
			i++;
		}
	},

	fillByDays: function () {
		var calendarContainer = this.items.getAt(1);
		var nextDay = this.getCurrentDate();
		if( this.formatDate(this.getCurrentDate(), 'j') != 1 )
			nextDay = Ext.Date.add( this.getCurrentDate(), Ext.Date.DAY, -this.formatDate(this.getCurrentDate(), 'j' ) + 1);
		var daysInMonth = Ext.Date.getDaysInMonth(nextDay);
		var countOfWeeks = this.getCountOfWeeks(this.formatDate(nextDay, 'w'), daysInMonth);
		var i = 0;
		while (i < countOfWeeks) {
			var containerForWeek = calendarContainer.add({
				xtype: 'container',
				layout: 'hbox',
				minHeight: 70,
				height: 140
			});
			var j = 0;
			while ( j < Ext.Date.dayNames.length) {
				containerForWeek.add({
					xtype: 'container',
					width: (100/7.01) + '%',
					height: '100%',
					html: this.setHtml(nextDay, j),
					cls: this.setCls(nextDay, j)
				});

				if ( this.isEqualWeekdays(nextDay, j) && this.formatDate(nextDay, 'j') < daysInMonth)
					nextDay = Ext.Date.add(nextDay, Ext.Date.DAY, 1);

				j++;
			}
			i++;
		}
	},

	/*resizeCells: function (parentHeight) {
		var calendarContainer = this.items.getAt(1);
		var height = parentHeight / calendarContainer.items.length;
	///	console.log(height);
		Ext.each(calendarContainer.items.getRange(), function (item){
			item.setHeight(height);
		});
	},*/

	getInfo: function (farm, role) {
		var info = farm + ' (' + role + ')';
		info = '<span title="' + info + '">' + info + '</span>';

		/*if(( farm + ' (' + role + ')' ).length >= 23) {
			if(farm.length > 18)
				info = '<span title= "' + farm + '">' + farm.substr(0, 15) + '...</span>' + '<span title="' + role + '"> (...)</span>';
			else
				info = farm + '<span title="' + role + '"> (...)</span>';
		} else
			info = farm + ' (' + role + ')';*/
		return info;
	},

	fillByStoreData: function ( day ) {
		if (this.dataStore && this.dataStore[this.formatDate(day, 'j F o')]) {
			var dataToShow = this.dataStore[this.formatDate( day, 'j F o')];
			var data = '';
			var i = 0;
			var allBacksByTime = Ext.Object.getKeys(dataToShow).sort();

			while ( i < allBacksByTime.length ) {
				var itemData = dataToShow[allBacksByTime[i]];
			//	console.log(this.formatDate(itemData['date'], 'h:ia'));
				data += '<div class="scalr-ui-dbbackups-cell-content" backupId=' + itemData[ 'backup_id' ] + '><a>'
					+ itemData['date']
					+ this.getInfo(itemData['farm'], itemData['role'])
					+ '</a></div>';
				i++;
			}
			return data;
		}
		else return '';
	},

	setHtml: function (dateToSet, currentWeekday) {
		if( this.formatDate(dateToSet, 'w') == currentWeekday)
			return '<div day="'+ this.formatDate(dateToSet, 'j') +'">'
						+ '<span class="day">' + this.formatDate(dateToSet, 'j M') + '</span>'
						+ '<br/>'
						+ this.fillByStoreData(dateToSet)
					+ '</div>';
		else return '';
	},

	setCls: function (dateToSet, currentWeekday) {
		return (this.formatDate(dateToSet, 'w') == currentWeekday) ? 'scalr-ui-dbbackups-cell-title-right' : 'scalr-ui-dbbackups-cell-empty';
	},

	isEqualWeekdays: function (dateToSet, currentWeekday) {
		return (this.formatDate(dateToSet, 'w') == currentWeekday);
	},

	formatDate: function (date, toString) {
		return Ext.Date.format(date, toString);
	},

	setCurrentDate: function (newDate) {
		this.currentDate = newDate;
	},

	getCurrentDate: function () {
		return this.currentDate;
	},

	setStoreData: function (data) {
		this.dataStore = data;
		this.updateTheCalendar();
	},

	getStoreData : function (date) {
		var formatedDate = Ext.Date.format(date , 'j F o');
		return this.dataStore && this.dataStore[formatedDate] ? this.dataStore[formatedDate] : {};
	},

	getCountOfWeeks: function (firstDay, daysInMonth) {
		var days = parseInt(firstDay) + daysInMonth;
		if(days == 28)
			return 4;
		if(days > 28 && days <= 35)
			return 5;
		if(days > 35)
			return 6;
	}
});

Ext.define('Ext.form.field.Month', {
	extend:'Ext.form.field.Date',
	alias: 'widget.monthfield',
	requires: 'Ext.picker.Month',

	alternateClassName: ['Ext.form.MonthField', 'Ext.form.Month'],

	selectMonth: null,
	createPicker: function() {
		var me = this,
			format = Ext.String.format;
		return Ext.create('Ext.picker.Month', {
			pickerField: me,
			ownerCt: me.ownerCt,
			renderTo: document.body,
			floating: true,
			hidden: true,
			focusOnShow: true,
			minDate: me.minValue,
			maxDate: me.maxValue,
			disabledDatesRE: me.disabledDatesRE,
			disabledDatesText: me.disabledDatesText,
			disabledDays: me.disabledDays,
			disabledDaysText: me.disabledDaysText,
			format: me.format,
			showToday: me.showToday,
			startDay: me.startDay,
			minText: format(me.minText, me.formatDate(me.minValue)),
			maxText: format(me.maxText, me.formatDate(me.maxValue)),
			listeners: {
				select:        { scope: me,   fn: me.onSelect     },
				monthdblclick: { scope: me,   fn: me.onOKClick     },
				yeardblclick:  { scope: me,   fn: me.onOKClick     },
				OkClick:       { scope: me,   fn: me.onOKClick     },
				CancelClick:   { scope: me,   fn: me.onCancelClick }
			},
			keyNavConfig: {
				esc: function() {
					me.collapse();
				}
			}
		});
	},
	onCancelClick: function() {
		var me = this;
		me.selectMonth = null;
		me.collapse();
	},
	onOKClick: function() {
		var me = this;
		if( me.selectMonth ) {
			me.setValue(me.selectMonth);
			me.fireEvent('select', me, me.selectMonth);
		}
		me.collapse();
	},
	onSelect: function(m, d) {
		var me = this;
		me.selectMonth = new Date(( d[0]+1 ) +'/1/'+d[1]);
	}
});