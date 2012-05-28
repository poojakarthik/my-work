
var H			= require('fw/dom/factory'), // HTML
	S			= H.S, // SVG
	Class		= require('fw/class'),
	Component	= require('fw/component');


var self = new Class({
	'extends' : Component,

	construct	: function() {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-dashboard-calendar');
	},


	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		this.NODE = H.section();
	},


	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		try {		
			if (!this._bInitialised) {
				//new Calendar();
				// Date picker
				JsAutoLoader.loadScript(['../ui/javascript/date_time_picker.js'], function() {

					var oCurrentDate	= new Date(),
						iCurrentDay		= oCurrentDate.getDate(),
						iCurrentMonth	= oCurrentDate.getMonth(),
						iCurrentYear	= oCurrentDate.getFullYear(),
						iStartYear		= oCurrentDate.getFullYear()-1,
						iEndYear		= oCurrentDate.getFullYear()+1;

					showChooser("myForm", "flex-page-dashboard-time-and-date-selected-value", "flex-page-dashboard-time-and-date-picker", iStartYear, iEndYear, "d/m/Y H:i:s", false, true, true, iCurrentYear, iCurrentMonth, iCurrentDay);
					
					// Remove all events from calendar, the date_time_picker adds some undesired default events.
					this._removeAllEventsForNodeList($$('.flex-page-dashboard-time-and-date-picker td'));
					
					// Add events to handle the change of the month and year selection.
					this._createEventListeners();

					// Style the current day differently.
					this._addClassnameToCurrentDayElement();

				}.bind(this), null, null, null);
			} else {
				// Every other call
			}
			this._onReady();
		} catch (oException) {
			// Fail
			this._handleException(oException);
		}
	},

	// CHANGEME: this has a very generic name but is doing some very specific things.
	_createEventListeners : function() {
		$$('.flex-page-dashboard-time-and-date-picker select').each(function(oElement) {
			oElement.addEventListener('change', function(){
				this._createEventListeners();
				// Style the current day differently.
				this._addClassnameToCurrentDayElement();
				// We dont want the days to be clickable on this calendar.
				this._removeAllEventsForNodeList($$('.flex-page-dashboard-time-and-date-picker td'));
			}.bind(this));
		}.bind(this));
	},

	// This allows us to style today differently.
	_addClassnameToCurrentDayElement : function() {
		
		var oDate			= new Date(); // Current date.
		var iSelectedMonth	= this._getSelectedMonth(); // Selected Month
		var iSelectedYear	= this._getSelectedYear(); // Selected Year

		// Its the current month and year, so select today.
		if (oDate.getMonth() == iSelectedMonth && oDate.getFullYear() == iSelectedYear) {
			var aCalendarDays = this._getAllDayElements();
			// Current day.
			for (var i =0; i<aCalendarDays.length; i++) {
				if (aCalendarDays[i].innerHTML == oDate.getDate()) {
					// Add class
					aCalendarDays[i].addClassName('flex-page-dashboard-time-and-date-picker-today');
					break;
				}
			}
		}
	},

	_getAllDayElements : function() {
		return $$('.flex-page-dashboard-time-and-date-picker td');
	},
	_getSelectedMonth : function() {
		var oMonthElement = $$('.flex-page-dashboard-time-and-date-picker').first().select('select')[0];
		return oMonthElement.options[oMonthElement.selectedIndex].value;
	},

	_getSelectedYear : function() {
		var oYearElement = $$('.flex-page-dashboard-time-and-date-picker').first().select('select')[1];
		return oYearElement.options[oYearElement.selectedIndex].value;
	},

	_removeAllEventsForNodeList : function(aData) {
		aData.each(function(oElement) {
			// Add more events here.
			oElement.onblur		= null;
			oElement.onchange	= null;
			oElement.onclick	= null;
		});
	},

	_handleException : function(oException) {
		if (oException && oException.message) {
			console.log('An exception has occurred with the message: "' + oException.message + '"');
			console.log('Exception: "' + oException + '"');
		} else {
			console.log('An unknown error has occurred.');
		}
	}

});

return self;
