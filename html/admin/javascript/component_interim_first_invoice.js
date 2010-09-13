
var Component_Interim_First_Invoice	= Class.create(
{
	initialize	: function()
	{
		// Nothing needed
	},

	submitAll	: function()
	{
		Reflex_Popup.yesNoCancel(
			'Are you sure you want to submit all Interim First Invoices?', 
			{fnOnYes: this._submitAll.bind(this)}
		);
	},
	
	commitAll	: function()
	{
		JsAutoLoader.loadScript(
			[
			 	'../ui/javascript/control_field.js', 
			 	'../ui/javascript/control_field_select.js',
			 	'javascript/popup_interim_first_invoice_commit_and_send.js'
			],
			function()
			{
				new Popup_Interim_First_Invoice_Commit_And_Send();
			}
		);
	},
	
	// Private
	
	_submitAll	: function()
	{
		this._oLoading	= new Reflex_Popup.Loading('Submitting All Interim Invoices...');
		this._oLoading.display();
		
		var fnGo	=	jQuery.json.jsonFunction(
							this._finishedSubmit.bind(this), 
							this._finishedSubmit.bind(this), 
							'Invoice_Interim', 
							'SubmitAllEligible'
						);
		fnGo();
	},
	
	_refreshPage	: function()
	{
		window.location	= window.location;
	},
	
	_finishedSubmit	: function(oResponse)
	{
		this._oLoading.hide();
		delete this._oLoading;
		
		if (oResponse.bSuccess)
		{
			// Success
			Reflex_Popup.yesNoCancel(
				'All Interim First Invoices have been submitted.', 
				{
					sYesLabel	: 'OK',
					sNoLabel	: 'View Log',
					sTitle		: 'Success', 
					iWidth		: 35,
					fnOnYes		: this._refreshPage.bind(this),
					fnOnNo		: this._viewLog.bind(this, oResponse.sDebug)
				}
			);
		}
		else
		{
			// Error
			Reflex_Popup.yesNoCancel(
				'An error occurred submitting the Interim First Invoices. ' +
				(oResponse.sError ? oResponse.sError + '.' : '') + ' Please contact YBS if you require assistance.',
				{
					sYesLabel	: 'OK',
					sNoLabel	: 'View Log',
					sTitle		: 'Error', 
					iWidth		: 35,
					fnOnNo		: this._viewLog.bind(this, oResponse.sDebug)
				}
			);
		}
	},
	
	_viewLog	: function(sLog)
	{
		if (sLog)
		{
			var oTextArea	=	$T.textarea({class: 'log-text'},
									sLog
								);
			Reflex_Popup.alert(oTextArea, {sTitle: 'Log', iWidth: 61});
		}
	}
});