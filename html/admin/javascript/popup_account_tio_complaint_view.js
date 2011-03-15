
var Popup_Account_TIO_Complaint_View = Class.create(Reflex_Popup,
{
	initialize : function($super, iAccountId, fnOnComplaintEnd)
	{
		$super(40);
		
		this._iAccountId		= iAccountId;
		this._fnOnComplaintEnd	= fnOnComplaintEnd;
		
		this._buildUI();
	},
	
	_buildUI : function(oResponse)
	{
		if (!oResponse)
		{
			var fnResp	= this._buildUI.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Account_TIO_Complaint', 'getExtendedComplaintDetailsForAccount');
			fnReq(this._iAccountId)
			return;
		}
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_TIO_Complaint_View._ajaxError(oResponse);
			return;
		}
		
		var oComplaint = oResponse.oComplaint;
		
		// Create ui content
		var oContentDiv = 	$T.div({class: 'popup-account-tio-complaint-view'},
								$T.table({class: 'reflex input'},
									$T.tbody(
										$T.tr(
											$T.th('Account'),
											$T.td(
												$T.a({href: 'flex.php/Account/Overview/?Account.Id=' + oComplaint.account.Id}, 
													oComplaint.account.Id
												),
												$T.span(': ' + oComplaint.account.BusinessName)
											)
										),
										$T.tr(
											$T.th('Created'),
											$T.td(Date.$parseDate(oComplaint.collection_suspension.start_datetime, 'Y-m-d H:i:s').$format('d/m/y g:i A') + ' by ' + oComplaint.collection_suspension.start_employee_name)
										),
										$T.tr(
											$T.th({class: 'popup-account-tio-complaint-view-reference-number-label'},
												'TIO Reference Number'
											),
											$T.td(oComplaint.tio_reference_number)
										)
									)
								),
								$T.div({class: 'popup-account-tio-complaint-view-buttons'},
									$T.button('Close Complaint').observe('click', this._closeComplaint.bind(this))
								)
							);
		
		this.setTitle('TIO Complaint Details for Account ' + this._iAccountId);
		this.addCloseButton();
		this.setContent(oContentDiv);
		this.display();
	},
	
	_closeComplaint : function()
	{
		new Popup_Account_TIO_Complaint_Close(this._iAccountId, this._complaintClosed.bind(this));
	},
	
	_complaintClosed : function()
	{
		this.hide();
		if (this._fnOnComplaintEnd)
		{
			this._fnOnComplaintEnd();
		}
	}
});

// Static

Object.extend(Popup_Account_TIO_Complaint_View, 
{
	_ajaxError : function(oResponse, sMessage)
   	{
		// Exception
		Reflex_Popup.alert(
			(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
			{sTitle: 'Error'}
		);
	}
});
