
var Popup_Collections_Suspension_View = Class.create(Reflex_Popup, 
{
	initialize : function($super, iSuspensionId)
	{
		$super(40);
		
		this._iSuspensionId = iSuspensionId;
		
		this._buildUI();
	},
	
	_buildUI : function(oSuspensionResponse, oAccount)
	{
		if (!oSuspensionResponse)
		{
			// Request
			var fnResp	= this._buildUI.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Collection_Suspension', 'getExtendedDetailsForId');
			fnReq(this._iSuspensionId);
			return;
		}
		
		if (!oSuspensionResponse.bSuccess)
		{
			// Error
			jQuery.json.errorPopup(oResponse);
			return;
		}
		
		// Success, have got the details
		var oSuspension = oSuspensionResponse.oSuspension;
		
		var oReason 	= oSuspension.collection_suspension_reason;
		var sReason 	= oReason.description == oReason.name ? oReason.name : oReason.name + ' (' + oReason.description + ')';
		var oEndReason	= oSuspension.collection_suspension_end_reason;
		var sEndReason 	= '';
		if (oEndReason)
		{
			sEndReason = oEndReason.description == oEndReason.name ? oEndReason.name : oEndReason.name + ' (' + oEndReason.description + ')';
		}
		
		var oContentDiv = 	$T.div({class: 'popup-collections-suspension-view'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Account'),
											$T.td(
												$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + oSuspension.account.Id}, 
													oSuspension.account.Id
												),
												$T.span(': ' + oSuspension.account.BusinessName)
											)
										),
										$T.tr(
											$T.th('Started On'),
											$T.td(Popup_Collections_Suspension_View._formatDateTime(oSuspension.start_datetime))
										),
										$T.tr(
											$T.th('Started By'),
											$T.td(oSuspension.start_employee.FirstName + ' ' + oSuspension.start_employee.LastName)
										),
										$T.tr(
											$T.th('Reason'),
											$T.td(sReason)
										),
										$T.tr(
											$T.th('Proposed End'),
											$T.td(Popup_Collections_Suspension_View._formatDateTime(oSuspension.proposed_end_datetime))
										),
										$T.tr(
											$T.th('Ended On'),
											$T.td(Popup_Collections_Suspension_View._formatDateTime(oSuspension.effective_end_datetime))
										),
										$T.tr(
											$T.th('Ended By'),
											$T.td(
												oSuspension.end_employee ? oSuspension.end_employee.FirstName + ' ' + oSuspension.end_employee.LastName : null 
											)
										),
										$T.tr(
											$T.th('End Reason'),
											$T.td(
												sEndReason
											)
										)
									)
								),
								$T.div({class: 'popup-collections-suspension-view-buttons'},
									$T.button('OK').observe('click', this.hide.bind(this))	
								)
							);
		
		this.setTitle('Collections Suspension Details');
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	}
});

Object.extend(Popup_Collections_Suspension_View, 
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
	}
});

