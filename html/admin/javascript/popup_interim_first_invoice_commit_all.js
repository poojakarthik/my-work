
var Popup_Interim_First_Invoice_Commit_All	= Class.create(Reflex_Popup, 
{
	initialize	: function($super)
	{
		$super(26);
		
		this._buildUI();
	},
	
	// Private
	
	_buildUI	: function(oResponse)
	{
		// Control field
		var oDateField	= new Control_Field_Select();
		oDateField.setPopulateFunction(Popup_Interim_First_Invoice_Commit_All._getBillingDates);
		oDateField.setEditable(true);
		oDateField.setVisible(true);
		oDateField.setRenderMode(Control_Field.RENDER_MODE_EDIT);
		oDateField.disableValidationStyling();
		this._oDateField	= oDateField;
		
		// Popup content
		var oContent	= 	$T.div({class: 'popup-interim-first-invoice-commit-all'},
								$T.p('Specify the date on which the invoices were generated:'),
								$T.p(
									this._oDateField.getElement()
								),
								$T.div({class: 'buttons'},
									$T.button('Commit Invoices').observe('click', this._commitAll.bind(this)),
									$T.button('Cancel').observe('click', this.hide.bind(this))
								)
							);
		
		// Configure popup
		this.setContent(oContent);
		this.addCloseButton();
		this.setTitle('Commit and Send Interim Invoices');
		this.display();
	},
	
	_commitAll	: function()
	{
		var sDate	= this._oDateField.getElementValue();
		if (sDate && sDate !== '')
		{
			new Popup_Interim_First_Invoice_Commit(sDate);
			this.hide();
		}
		else
		{
			Reflex_Popup.alert('Please choose an Invoice Date');
		}
	},
	
	_refreshPage	: function()
	{
		window.location	= window.location;
	}
});

// Static

Popup_Interim_First_Invoice_Commit_All._getBillingDates	= function(fnCallback, oResponse)
{
	if (typeof oResponse == 'undefined')
	{
		// Make request to get the billing dates
		var fnGetBillingDates	=	jQuery.json.jsonFunction(
										Popup_Interim_First_Invoice_Commit_All._getBillingDates.curry(fnCallback), 
										Popup_Interim_First_Invoice_Commit_All._getBillingDates.curry(fnCallback), 
										'Invoice_Interim', 
										'getTemporaryFirstInterimInvoiceBillingDates'
									);
		fnGetBillingDates();
	}
	else
	{
		// Create the options
		var aOptions	= [];
		if (oResponse.bSuccess)
		{
			var aDates	= $A(oResponse.aDates);
			for (var i = 0; i < aDates.length; i++)
			{
				aOptions.push(
					$T.option({value: aDates[i]}, 
						Date.$parseDate(aDates[i], 'Y-m-d').$format('jS F Y')
					)
				);
			}
		}
		fnCallback(aOptions);
	}
}

