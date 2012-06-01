
var H					= require('fw/dom/factory'), // HTML
	S					= H.S, // SVG
	Class				= require('fw/class'),
	Calendar			= require('./dashboard/calendar'),
	Component			= require('fw/component'),
	CustomersRecent		= require('./dashboard/customers/recent'),
	TicketsMine			= require('./dashboard/tickets/mine'),
	TicketStatistics	= require('./dashboard/tickets/statistics'),
	NoticesTechnical	= require('./dashboard/notices/technical'),
	NoticesGeneral		= require('./dashboard/notices/general'),
	XHRRequest			= require('fw/xhrrequest');


var self = new Class({
	'extends' : Component,

	construct	: function() {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-dashboard');
	},

	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		
		$$('.maximum-area-body')[0].addClassName('flex-page-dashboard-container');
		
		this.NODE = H.section(
			H.header(
				H.hgroup(
					H.h1('Dashboard'),
					this._oWelcomeMessage = H.h2()
				)
			),
			new CustomersRecent(),
			new TicketsMine(),
			new TicketStatistics(),
			H.section({'class':'flex-page-dashboard-updates'},
				H.header('Information and Updates'),
				H.div(
					new NoticesTechnical(),
					H.div({'class':'flex-page-dashboard-updates-separator'}),
					new NoticesGeneral(),
					H.div({'class':'flex-page-dashboard-updates-separator'}),
					H.section({'class':'flex-page-dashboard-time-and-date'},
						H.header(
							H.img({'src':'/admin/img/template/calendar_view_month.png'}),
							H.h3('Calendar')
						),
						H.form({'id':'flex-page-dashboard-time-and-date-picker', 'class':'flex-page-dashboard-time-and-date-picker'}),
						H.input({'class':'flex-page-dashboard-time-and-date-selected-value','id':'flex-page-dashboard-time-and-date-selected-value'})
					)
				)
			)
		);
		// Add to DOM
		$$('.flex-page')[0].appendChild(this.NODE);
	},


	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		try {		
			if (!this._bInitialised) {
				// Add Calendar
				new Calendar();
				// Set the welcome message
				this._getCurrentEmployee(this._setWelcomeMessageForCurrentEmployee.bind(this));
			} else {
				// Every other call
			}
			this._onReady();
		} catch (oException) {
			// Fail
			this._handleException(oException);
		}
	},

	_setWelcomeMessageForCurrentEmployee : function(oEmployee) {
		var sWelcomeMessage = (oEmployee && oEmployee.FirstName && oEmployee.LastName) ? 'Welcome '+oEmployee.FirstName : 'Welcome';
		this._oWelcomeMessage.update(sWelcomeMessage);
	},

	_getCurrentEmployee : function(fnCallback, oXHREvent) {
		if (!oXHREvent) {
			var oReq = new XHRRequest('/admin/reflex_json.php/Employee/getCurrentEmployee', this._getCurrentEmployee.bind(this, fnCallback));
			oReq.send();
		} else {
			var oResponse = oXHREvent.getData();
			// Success
			var oData = oResponse.getData();
			if (fnCallback) {
				fnCallback(oData);
			} else {
				return (oData) ? oData : {};
			}
		}
	},

	_handleException : function(oException) {
		if (oException && oException.message) {
			console.log('An exception has occurred with the message: "' + oException.message + '"');
			console.log('Exception: "' + oException + '"');
		} else {
			console.log('An unknown error has occurred.');
		}
	},

	// ----------------------------------------------------------------------------------- //
	// Statics
	// ----------------------------------------------------------------------------------- //
	statics : {
		STATIC_DEFINITION : null,
		staticMethod : function() {
			// Sample
		}
	}

});

return self;
