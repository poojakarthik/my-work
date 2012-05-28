
var H			= require('fw/dom/factory'), // HTML
	S			= H.S, // SVG
	Class		= require('fw/class'),
	Component	= require('fw/component'),
	XHRRequest	= require('fw/xhrrequest');


var self = new Class({

	'extends' : Component,

	construct	: function() {
		this.CONFIG = Object.extend({}, this.CONFIG || {});
		this._super.apply(this, arguments);
		this.NODE.addClassName('flex-page-dashboard-customers-recent');
	},


	// ----------------------------------------------------------------------------------- //
	// Build UI
	// ----------------------------------------------------------------------------------- //
	_buildUI	: function() {
		this.NODE = H.section(
			H.header('Recent Customers'),
			H.table({'border':'0', 'cellpadding':'3', 'cellspacing':'0', 'width':'100%', 'class':'flex-page-dashboard-customers-recent-list', 'class':'reflex highlight-rows'},
				H.thead(
					H.tr(
						H.th({'class':'flex-page-dashboard-customers-recent-account',width:'100'}, 'Account'),
						H.th({'class':'flex-page-dashboard-customers-recent-business-name'},'Business Name'),
						H.th({'class':'flex-page-dashboard-customers-recent-viewed-on',width:'100'}, 'Viewed On')
					)
				),
				this._oRecentCustomerList = H.tbody()
			),
			H.div({'class':'ButtonContainer'},
				H.button({"onclick":"javascript:Vixen.Popup.ShowAjaxPopup('ViewRecentCustomersId', 'extralarge', 'Recent Customers', 'Employee', 'ViewRecentCustomers')", 'class':'flex-page-dashboard-customers-recent-view-all'},'More')
			)

		);
	},


	// ----------------------------------------------------------------------------------- //
	// Sync UI
	// ----------------------------------------------------------------------------------- //
	_syncUI	: function() {
		if (!this._bInitialised) {
			// First call.
			this._getAll(this._buildRecentList.bind(this));
		} else {
			// Every other call
		}
		this._onReady();
	},

	
	_getAll : function(fnCallback, oXHREvent) {
		if (!oXHREvent) {
			var oReq = new XHRRequest('/admin/reflex_json.php/Customer_RecentList/getRecentAccounts', this._getAll.bind(this, fnCallback));
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


	_buildRecentList : function(oData) {
		//for(var iKey=0; iKey<self.CUSTOMER_LIST_LIMIT; iKey++) {
		for (var iKey=0; iKey<oData.length; iKey++) {
			
			if (iKey >= self.CUSTOMER_LIST_LIMIT) {
				break;
			}

			if (oData[iKey] && oData[iKey].hasOwnProperty('account_id')) {
				
				// Data
				var oAccount			= oData[iKey],
					oViewedOnDatetime	= Date.$parseDate(oAccount.viewed_on, 'Y-m-d H:i:s');

				// Build UI Stuff
				this._oRecentCustomerList.appendChild(
					this._oCurrentRow = H.tr(
						H.td(H.span({'onclick':'window.location = "/admin/flex.php/Account/Overview/?Account.Id='+oAccount.account_id+'"', 'title':'View Account Details'}, oAccount.account_id)),
						H.td(H.a({'href':'../admin/flex.php/Account/Overview/?Account.Id='+oAccount.account_id, 'title':'View Account Details', 'style':'color:black'},oAccount.business_name)),
						H.td(
							H.time({'datetime':true, 'class':'flex-datetime-date'},oViewedOnDatetime.$format('d M Y')),
							H.time({'datetime':true, 'class':'flex-datetime-time'},oViewedOnDatetime.$format('H:m A'))
						)
					)
				);

				// Add alternating table row colours.
				if (!self._isNumberOdd(this._oRecentCustomerList.select('tr').length)) {
					this._oCurrentRow.addClassName('alt');
				}
			}
		}
	},


	// ----------------------------------------------------------------------------------- //
	// Statics
	// ----------------------------------------------------------------------------------- //
	statics : {		

		// How many records to show.
		CUSTOMER_LIST_LIMIT: 7,

		_isNumberOdd : function(iNumber) {
			// This static method returns true or false.
			// The Modulus operator '%' returns the division remainder.
			return (iNumber % 2) === 1;
		}
	}


});

return self;

