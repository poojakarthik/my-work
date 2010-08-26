
var Component_Interim_First_Invoice_Submission	= Class.create(
{
	initialize	: function()
	{
		this._oLoading	= new Reflex_Popup.Loading('Submitting All Interim Invoices...');
		this._oLoading.display();
		
		var fnGo	=	jQuery.json.jsonFunction(
							this._finished.bind(this), 
							this._finished.bind(this), 
							'Invoice_Interim', 
							'SubmitAllEligible'
						);
		fnGo();
	},
	
	// Private
	
	_finished	: function(oResponse)
	{
		this._oLoading.hide();
		delete this._oLoading;
		
		if (oResponse.bSuccess)
		{
			// Success
			Reflex_Popup.alert('All Interim Invoices have been submitted.', {sTitle: 'Success', iWidth: 35});
		}
		else
		{
			// Error
			Reflex_Popup.alert(
				'An error occurred submitting the Interim Invoices. ' +
				(oResponse.sError ? oResponse.sError + '.' : '') + ' Please contact YBS.',
				{sTitle: 'Error', iWidth: 35}
			);
		}
	}
});