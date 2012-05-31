
var H			= require('fw/dom/factory'), // HTML
	S			= H.S, // SVG
	Class		= require('fw/class'),
	Component	= require('fw/component'),
	XHRRequest	= require('fw/xhrrequest');


var self = new Class({
	'extends' : Component,

	construct	: function() {
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-dashboard-tickets-mine');
	},


	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		this.NODE = H.section(
			H.header('My Tickets'),
			H.table({'border':'0', 'cellpadding':'3', 'cellspacing':'0', 'width':'100%', 'id':'ticketing', 'name':'ticketing', 'class':'reflex highlight-rows'},
				H.thead(
					H.tr(
						H.th({'class':'flex-page-dashboard-tickets-mine-id'}, 'Id'),
						H.th({'class':'flex-page-dashboard-tickets-mine-subject'}, 'Subject'),
						H.th({'class':'flex-page-dashboard-tickets-mine-last-modified'}, 'Modified'),
						H.th({'class':'flex-page-dashboard-tickets-mine-priority'}, 'Priority')
					)
				),
				this._oTicketList = H.tbody()
			),
			this._oLoading = H.div({'class':'flex-page-dashboard-loading -enabled'}),
			this._oButtons = H.div({'class':'ButtonContainer'},
				H.button({'onclick':'window.location = "/admin/reflex.php/Ticketing/Tickets/Mine/"', 'class':'flex-page-dashboard-tickets-mine-view-all'}, 'More')
			)
		);
	},


	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		try {
			if (!this._bInitialised) {
				// First call.
				this._getAll(function(oData) {
					this._buildTicketList(oData);
					this._oLoading.classList.remove('-enabled'); // Hide loading message
					this._oButtons.classList.add('-enabled'); // Enable button bar.
				}.bind(this));
			} else {
				// Every other call
			}
			this._onReady();
		} catch (oException) {
			// Fail
			this._handleException(oException);
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

	_getAll : function(fnCallback, oXHREvent) {
		if (!oXHREvent) {
			var oReq = new XHRRequest('/admin/reflex_json.php/Ticketing/getTicketsForCurrentUser', this._getAll.bind(this, fnCallback));
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


	_buildTicketList : function(oData) {
		// for(var iKey=0; iKey<self.TICKET_LIST_LIMIT; iKey++) {
		for (var iKey=0; iKey<oData.length; iKey++) {
			
			if (iKey >= self.TICKET_LIST_LIMIT) {
				break;
			}

			if (oData[iKey] && oData[iKey].hasOwnProperty('id')) {

				// Data
				var oTicket				= oData[iKey],
					oModifiedDatetime	= Date.$parseDate(oTicket.modified_datetime, 'Y-m-d h:i:s');
					
				// Build UI stuff
				this._oTicketList.appendChild(
					this._oCurrentRow = H.tr({'onclick':'window.location = "/admin/reflex.php/Ticketing/Ticket/'+oTicket.id+'/View/?"'},
						H.td(
							H.span({'onclick':'window.location = "/admin/reflex.php/Ticketing/Ticket/'+oTicket.id+'/View/?', 'title':'View Ticket Details'}, oTicket.id)
						),
						H.td({'class':'flex-page-dashboard-tickets-mine-subject'},oTicket.subject),
						H.td(
							H.time({'datetime':true, 'class':'flex-datetime-date'},oModifiedDatetime.$format('d M Y')),
							H.time({'datetime':true, 'class':'flex-datetime-time'},oModifiedDatetime.$format('H:m A'))
						),
						H.td({'class':this._getPriorityClassForConstant(oTicket.priority.constant)}, oTicket.priority.name)
					)
				);

				// Add alternating table row colours.
				if (!self._isNumberOdd(this._oTicketList.select('tr').length)) {
					this._oCurrentRow.addClassName('alt');
				}
			}
		}
	},


	_getPriorityClassForConstant : function(sConstant) {
		// Returns CSS class name for Constant value.
		switch(sConstant) {
			// Low
			case "TICKETING_PRIORITY_LOW":
				return "ticketing-priority-low";
			break;
			// Medium
			case "TICKETING_PRIORITY_MEDIUM":
				return "ticketing-priority-medium";
			break;
			// Urgent
			case "TICKETING_PRIORITY_URGENT":
				return "ticketing-priority-urgent";
			break;

		}
	},


	// ----------------------------------------------------------------------------------- //
	// Statics
	// ----------------------------------------------------------------------------------- //
	statics : {

		// How many records to show.
		TICKET_LIST_LIMIT: 7,

		_isNumberOdd : function(iNumber) {
			// This static method returns true or false.
			// The Modulus operator '%' returns the division remainder.
			return (iNumber % 2) === 1;
		}
	}


});

return self;

