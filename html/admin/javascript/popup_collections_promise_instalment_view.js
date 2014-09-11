
var Popup_Collections_Promise_Instalment_View = Class.create(Reflex_Popup, 
{
	initialize : function($super, iPromiseInstalmentId)
	{
		$super(40);
		
		this._iPromiseInstalmentId = iPromiseInstalmentId;
		
		this._buildUI();
	},
	
	_buildUI : function(oResponse)
	{
		if (!oResponse)
		{
			// Request
			var fnResp	= this._buildUI.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Promise', 'getExtendedInstalmentDetailsForId');
			fnReq(this._iPromiseInstalmentId);
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		// Success, have got the details
		var oPromiseInstalment = oResponse.oPromiseInstalment;
		
		var oReason 	= oPromiseInstalment.collection_promise.collection_promise_reason;
		var sReason 	= oReason.description == oReason.name ? oReason.name : oReason.name + ' (' + oReason.description + ')';
		
		var oContentDiv = 	$T.div({class: 'popup-collections-promise-instalment-view'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Account'),
											$T.td(
												$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + oPromiseInstalment.collection_promise.account.Id}, 
													oPromiseInstalment.collection_promise.account.Id
												),
												$T.span(': ' + oPromiseInstalment.collection_promise.account.BusinessName)
											)
										),
										$T.tr(
											$T.th('Due Date'),
											$T.td(Popup_Collections_Promise_Instalment_View._formatDate(oPromiseInstalment.due_date))
										),
										$T.tr(
											$T.th('Amount'),
											$T.td('$' + new Number(oPromiseInstalment.amount).toFixed(2))
										),
										$T.tr(
											$T.th('Created On'),
											$T.td(Popup_Collections_Promise_Instalment_View._formatDateTime(oPromiseInstalment.created_datetime))
										),
										$T.tr(
											$T.th('Created By'),
											$T.td(oPromiseInstalment.created_employee.FirstName + ' ' + oPromiseInstalment.created_employee.LastName)
										),
										$T.tr(
											$T.th('Promise to Pay Reason'),
											$T.td(sReason)
										)
									)
								),
								$T.div({class: 'popup-collections-promise-instalment-view-buttons'},
									$T.button('OK').observe('click', this.hide.bind(this))	
								)
							);
		
		this.setTitle('Promise to Pay Instalment Details');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	}
});

Object.extend(Popup_Collections_Promise_Instalment_View, 
{
	_formatDateTime : function(sDatetime)
	{
		if (sDatetime !== null)
		{
			var oDate = Date.$parseDate(sDatetime, 'Y-m-d H:i:s');
			if (oDate)
			{
				return oDate.$format('d/m/y g:i A');
			}
		}
		return '';
	},
	
	_formatDate : function(sDatetime)
	{
		if (sDatetime !== null)
		{
			var oDate = Date.$parseDate(sDatetime, 'Y-m-d');
			if (oDate)
			{
				return oDate.$format('d/m/y');
			}
		}
		return '';
	}
});

