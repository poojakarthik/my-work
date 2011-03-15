
var Popup_Account_Severity_Warning = Class.create(
{
	initialize : function(iAccountId)
	{
		this._iAccountId = iAccountId;
		this._showWarnings();
	},
	
	_showWarnings : function(oResponse)
	{
		if (!oResponse)
		{
			// Request
			this._oLoading = new Reflex_Popup.Loading();
			this._oLoading.display();
			
			var fnResp	= this._showWarnings.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Employee', 'getSeverityWarnings');
			fnReq(this._iAccountId);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Severity_Warning._ajaxError(oResponse);
			return;
		}
		
		var aWarnings = oResponse.aWarnings;
		if (Object.isArray(aWarnings) && (aWarnings.length == 0))
		{
			// No warnings, shouldn't get here but checked anyway.
			return;
		}
		
		// Build warning alert
		var oUL = $T.ul();
		for (var i in aWarnings)
		{
			oUL.appendChild($T.li(aWarnings[i].message));
		}
		
		Reflex_Popup.yesNoCancel(
			$T.div({class: "popup-account-severity-warning"},
				$T.div("This Account is currently at the Collections Severity Level: "),
				$T.div({class: "popup-account-severity-warning-severity-level"},
					oResponse.oSeverity.name
				),
				$T.div("The following warnings apply:"),
				oUL,
				$T.div('Do you accept these warnings?')
			), 
			{
				sTitle			: "Account Warnings", 
				iWidth			: 30, 
				fnOnYes			: this._acceptWarnings.bind(this), 
				fnOnNo			: this._declineWarnings.bind(this),
				bOverrideStyle	: true
			}
		);
	},
	
	_acceptWarnings : function(oResponse)
	{
		if (!oResponse)
		{
			// Request
			this._oLoading = new Reflex_Popup.Loading();
			this._oLoading.display();
			
			var fnResp	= this._acceptWarnings.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Employee', 'acceptSeverityWarnings');
			fnReq(this._iAccountId);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Severity_Warning._ajaxError(oResponse);
			return;
		}

		// All good, proceed
	},
	
	_declineWarnings : function(oResponse)
	{
		if (!oResponse)
		{
			// Request
			this._oLoading = new Reflex_Popup.Loading();
			this._oLoading.display();
			
			var fnResp	= this._declineWarnings.bind(this);
			var fnReq	= jQuery.json.jsonFunction(fnResp, fnResp, 'Employee', 'declineSeverityWarnings');
			fnReq(this._iAccountId);
			return;
		}
		
		this._oLoading.hide();
		delete this._oLoading;
		
		if (!oResponse.bSuccess)
		{
			// Error
			Popup_Account_Severity_Warning._ajaxError(oResponse);
			return;
		}

		// Redirect to console
		window.location = 'reflex.php/Console/View/';
	}
});

Object.extend(Popup_Account_Severity_Warning, 
{
	_ajaxError : function(oResponse, sMessage)
	{
		Reflex_Popup.alert(
			(sMessage ? sMessage + '. ' : '') + oResponse.sMessage ? oResponse.sMessage : 'There was an error accessing the database. Please contact YBS for assistance.', 
			{sTitle: 'Error', sDebugContent: oResponse.sDebug}
		);
	}
});