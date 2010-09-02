
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
			 	'javascript/popup_interim_first_invoice_commit.js'
			],
			function()
			{
				new Popup_Interim_First_Invoice_Commit_All();
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
			Reflex_Popup.alert(
				'All Interim First Invoices have been submitted.', 
				{
					sTitle	: 'Success', 
					iWidth	: 35,
					fnClose	: this._refreshPage.bind(this)
				}
			);
		}
		else
		{
			// Error
			Reflex_Popup.alert(
				'An error occurred submitting the Interim First Invoices. ' +
				(oResponse.sError ? oResponse.sError + '.' : '') + ' Please contact YBS if you require assistance.',
				{sTitle: 'Error', iWidth: 35}
			);
		}
	}
});